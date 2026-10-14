<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Page d'accueil publique du concours (front-office, visiteurs).
 */
class AccueilC extends Public_Controller
{
    public function index()
    {
        $c = $this->concoursActif;

        // Aucun concours publié : page d'attente
        if (!$c) {
            return $this->render('front/components/bientot', ['title' => 'Concours d\'architecture SATRACO']);
        }

        $this->load->model('DatesClesM', 'datesM');
        $this->load->model('PrixM', 'prixM');

        $prix = $this->prixM->get_par_concours($c->id, true);

        $data = [
            'title'      => $c->titre,
            'calendrier' => $this->calendrier($c),
            'prix'       => $prix,
            'totauxPrix' => $this->prixM->totaux($prix),
            'documents'  => array_filter([
                'reglement' => fichier_public($c->reglement_pdf),
                'guide'     => fichier_public($c->guide_pdf),
            ]),
            'banniere'   => fichier_public($c->banniere),
            'logo'       => fichier_public($c->logo),
        ];

        $this->render('front/components/accueil', $data);
    }

    /**
     * Calendrier public : dates réglementaires du concours + étapes publiques
     * de l'agenda, triées chronologiquement, avec leur état par rapport à
     * l'heure du serveur (passe, en_cours, a_venir) et la prochaine étape.
     */
    private function calendrier($c)
    {
        $maintenant = time();
        $etapes     = [];

        foreach (ConcoursM::DATES_CLES as $champ => $def) {
            if (!empty($c->$champ)) {
                $etapes[] = [
                    'libelle'       => $def['label'],
                    'description'   => null,
                    'debut'         => $c->$champ,
                    'fin'           => null,
                    'icone'         => $def['icone'],
                    'reglementaire' => true,
                ];
            }
        }

        foreach ($this->datesM->get_par_concours($c->id, true) as $d) {
            $etapes[] = [
                'libelle'       => $d->libelle,
                'description'   => $d->description,
                'debut'         => $d->date_debut,
                'fin'           => $d->date_fin,
                'icone'         => $d->icone,
                'reglementaire' => false,
            ];
        }

        usort($etapes, function ($a, $b) {
            return strtotime($a['debut']) <=> strtotime($b['debut']);
        });

        $prochaineTrouvee = false;
        foreach ($etapes as &$e) {
            $debut = strtotime($e['debut']);
            $fin   = $e['fin'] ? strtotime($e['fin']) : $debut;

            if ($fin < $maintenant) {
                $e['etat'] = 'passe';
            } elseif ($debut <= $maintenant) {
                $e['etat'] = 'en_cours';
            } else {
                $e['etat'] = 'a_venir';
            }

            $e['prochaine'] = !$prochaineTrouvee && $e['etat'] === 'a_venir';
            $prochaineTrouvee = $prochaineTrouvee || $e['prochaine'];
        }
        unset($e);

        return $etapes;
    }
}
