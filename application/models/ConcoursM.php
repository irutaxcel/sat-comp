<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ConcoursM extends CI_Model
{
    protected $table = 'concours';

    /** Listes de valeurs autorisées (source unique pour la validation et la vue). */
    const PUBLICS      = ['professionnels' => 'Professionnels', 'etudiants' => 'Étudiants', 'mixte' => 'Professionnels & étudiants'];
    const MODES        = ['individuel' => 'Individuel uniquement', 'equipe' => 'Équipe uniquement', 'les_deux' => 'Individuel ou en équipe'];
    const VALIDATIONS  = ['email' => "Vérification de l'adresse e-mail", 'manuelle' => "Validation manuelle par l'administrateur", 'les_deux' => 'E-mail puis validation manuelle'];
    const STATUTS      = ['brouillon' => 'Brouillon', 'publie' => 'Publié', 'cloture' => 'Clôturé', 'archive' => 'Archivé'];
    const EXTENSIONS   = ['pdf', 'zip', 'jpg', 'jpeg', 'png', 'rar', 'dwg'];

    /** Dates réglementaires stockées dans `concours` (pilotent le verrouillage). */
    const DATES_CLES = [
        'ouverture_inscriptions' => ['label' => 'Ouverture des inscriptions', 'icone' => 'fas fa-door-open',    'couleur' => 'success', 'requis' => true],
        'cloture_inscriptions'   => ['label' => 'Clôture des inscriptions',   'icone' => 'fas fa-user-lock',    'couleur' => 'warning', 'requis' => true],
        'date_limite_soumission' => ['label' => 'Date limite de soumission',  'icone' => 'fas fa-hourglass-end', 'couleur' => 'danger',  'requis' => true],
        'date_resultats'         => ['label' => 'Proclamation des résultats', 'icone' => 'fas fa-trophy',       'couleur' => 'primary', 'requis' => false],
    ];

    /** Concours actif (un seul à la fois), ou null si aucun n'est configuré. */
    public function get_actif()
    {
        return $this->db->where('est_actif', 1)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get($this->table)
            ->row();
    }

    public function get($id)
    {
        return $this->db->get_where($this->table, ['id' => (int) $id])->row();
    }

    /**
     * Crée ou met à jour le concours.
     * À la création, le nouveau concours devient l'unique concours actif.
     */
    public function enregistrer($id, array $data, $user_id = null)
    {
        $this->db->trans_start();

        if ($id) {
            $data['updated_by'] = $user_id;
            $this->db->where('id', (int) $id)->update($this->table, $data);
        } else {
            $this->db->update($this->table, ['est_actif' => 0], ['est_actif' => 1]);
            $data['est_actif']  = 1;
            $data['created_by'] = $user_id;
            $this->db->insert($this->table, $data);
            $id = $this->db->insert_id();
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? (int) $id : false;
    }

    /** Met à jour uniquement les dates réglementaires. */
    public function enregistrer_dates($id, array $dates, $user_id = null)
    {
        $data = array_intersect_key($dates, self::DATES_CLES);
        $data['updated_by'] = $user_id;
        return $this->db->where('id', (int) $id)->update($this->table, $data);
    }

    /* -----------------------------------------------------------------
     *  Contrôles temporels — TOUJOURS basés sur l'heure du serveur.
     *  À réutiliser pour verrouiller inscriptions et soumissions.
     * ----------------------------------------------------------------- */

    private function _ts($concours, $champ)
    {
        return ($concours && !empty($concours->$champ)) ? strtotime($concours->$champ) : null;
    }

    public function inscriptions_ouvertes($concours, $maintenant = null)
    {
        $t = $maintenant ?: time();
        $debut = $this->_ts($concours, 'ouverture_inscriptions');
        $fin   = $this->_ts($concours, 'cloture_inscriptions');
        return $concours && $concours->statut === 'publie' && $debut && $fin && $t >= $debut && $t < $fin;
    }

    public function soumissions_ouvertes($concours, $maintenant = null)
    {
        $t = $maintenant ?: time();
        $debut = $this->_ts($concours, 'ouverture_inscriptions');
        $fin   = $this->_ts($concours, 'date_limite_soumission');
        return $concours && $concours->statut === 'publie' && $debut && $fin && $t >= $debut && $t < $fin;
    }

    /**
     * Phase actuelle du concours et prochaine échéance.
     * @return array|null [code, label, couleur, prochaine => [label, date, secondes] | null]
     */
    public function phase($concours, $maintenant = null)
    {
        if (!$concours) {
            return null;
        }

        $t   = $maintenant ?: time();
        $ouv = $this->_ts($concours, 'ouverture_inscriptions');
        $clo = $this->_ts($concours, 'cloture_inscriptions');
        $lim = $this->_ts($concours, 'date_limite_soumission');
        $res = $this->_ts($concours, 'date_resultats');

        $echeance = function ($champ, $ts) use ($t) {
            return $ts ? ['label' => self::DATES_CLES[$champ]['label'], 'date' => date('Y-m-d H:i:s', $ts), 'secondes' => $ts - $t] : null;
        };

        if (!$ouv || !$clo || !$lim) {
            return ['code' => 'non_planifie', 'label' => 'Calendrier incomplet', 'couleur' => 'secondary', 'prochaine' => null];
        }
        if ($t < $ouv) {
            return ['code' => 'a_venir', 'label' => 'Inscriptions pas encore ouvertes', 'couleur' => 'info', 'prochaine' => $echeance('ouverture_inscriptions', $ouv)];
        }
        if ($t < $clo) {
            return ['code' => 'inscriptions', 'label' => 'Inscriptions et soumissions ouvertes', 'couleur' => 'success', 'prochaine' => $echeance('cloture_inscriptions', $clo)];
        }
        if ($t < $lim) {
            return ['code' => 'soumissions', 'label' => 'Inscriptions closes — soumissions ouvertes', 'couleur' => 'warning', 'prochaine' => $echeance('date_limite_soumission', $lim)];
        }
        if (!$res || $t < $res) {
            return ['code' => 'deliberation', 'label' => 'Soumissions closes — évaluation en cours', 'couleur' => 'danger', 'prochaine' => $echeance('date_resultats', $res)];
        }
        return ['code' => 'termine', 'label' => 'Résultats proclamés', 'couleur' => 'primary', 'prochaine' => null];
    }

    /** Éléments nécessaires avant publication, avec leur état. */
    public function completude($concours)
    {
        $items = [
            'titre'                => 'Titre du concours',
            'description'          => 'Description',
            'objectifs'            => 'Objectifs',
            'criteres_eligibilite' => "Critères d'éligibilité",
            'reglement_pdf'        => 'Règlement (PDF)',
            'guide_pdf'            => 'Guide de participation (PDF)',
            'logo'                 => 'Logo',
            'banniere'             => 'Bannière',
            'email_contact'        => 'E-mail de contact',
        ];

        $liste = [];
        $faits = 0;
        foreach ($items as $champ => $label) {
            $ok = $concours && !empty($concours->$champ);
            $faits += $ok ? 1 : 0;
            $liste[] = ['label' => $label, 'ok' => $ok];
        }

        return [
            'items'       => $liste,
            'pourcentage' => (int) round($faits * 100 / count($items)),
        ];
    }
}
