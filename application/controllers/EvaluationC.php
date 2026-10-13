<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Évaluation — back-office.
 *
 * Page « Critères de notation » :
 *   GET  evaluation-criteres                       → page
 *   GET  evaluation-criteres/liste                 → JSON (critères + état de la grille)
 *   POST evaluation-criteres/enregistrer           → JSON (id?, nom, description, poids, note_max, actif)
 *   POST evaluation-criteres/supprimer             → JSON (id)
 *   POST evaluation-criteres/actif                 → JSON (id, actif)
 *   POST evaluation-criteres/ordre                 → JSON (ids[])
 *   POST evaluation-criteres/repartir              → JSON
 *   POST evaluation-criteres/modele                → JSON
 *   POST evaluation-criteres/verrou                → JSON (action = verrouiller | deverrouiller)
 *
 * Page « Classement » :
 *   GET  evaluation-classement                     → page
 *   GET  evaluation-classement/donnees             → JSON (classement + indicateurs)
 *   GET  evaluation-classement/detail/{id}         → JSON (notes par juré et par critère)
 *   POST evaluation-classement/figer               → JSON (action = figer | rouvrir)
 *   GET  evaluation-classement/export              → fichier .xlsx
 */
class EvaluationC extends Back_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('EvaluationM', 'evaluation');
    }

    public function index()
    {
        redirect('evaluation-criteres');
    }

    /* ================================================================
     *  CRITÈRES DE NOTATION
     * ================================================================ */

    public function criteres()
    {
        $data = [
            'title'    => 'Critères de notation',
            'etat'     => $this->evaluation->etat(),
            'criteres' => $this->evaluation->criteres(),
            'csrf'     => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ],
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/evaluation/criteres', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    public function criteres_liste()
    {
        $this->_exiger_ajax();
        $this->_json(['ok' => TRUE] + $this->_donnees());
    }

    public function criteres_enregistrer()
    {
        $this->_exiger_ajax();

        $id   = (int) $this->input->post('id');
        $nom  = trim((string) $this->input->post('nom', TRUE));
        $desc = trim((string) $this->input->post('description', TRUE));
        $poids    = $this->input->post('poids');
        $note_max = (int) $this->input->post('note_max');

        $erreurs = [];
        if ($nom === '' || mb_strlen($nom) > 120) {
            $erreurs['nom'] = 'L\'intitulé est obligatoire (120 caractères maximum).';
        }
        if (mb_strlen($desc) > 2000) {
            $erreurs['description'] = '2 000 caractères maximum.';
        }
        if (!is_numeric($poids) || (int) $poids != $poids || $poids < 0 || $poids > 100) {
            $erreurs['poids'] = 'La pondération doit être un entier entre 0 et 100.';
        }
        if (!in_array($note_max, EvaluationM::NOTE_MAX_PERMIS, TRUE)) {
            $erreurs['note_max'] = 'Barème non autorisé.';
        }
        if ($erreurs) {
            $this->_json(['ok' => FALSE, 'message' => reset($erreurs), 'erreurs' => $erreurs], 422);
            return;
        }

        $r = $this->evaluation->enregistrer($id, [
            'nom'         => $nom,
            'description' => $desc,
            'poids'       => (int) $poids,
            'note_max'    => $note_max,
            'actif'       => $this->input->post('actif') ? 1 : 0,
        ]);
        $this->_resultat($r, $id ? 'Critère modifié.' : 'Critère ajouté.');
    }

    public function criteres_supprimer()
    {
        $this->_exiger_ajax();
        $this->_resultat($this->evaluation->supprimer((int) $this->input->post('id')), 'Critère supprimé.');
    }

    public function criteres_actif()
    {
        $this->_exiger_ajax();
        $actif = (bool) $this->input->post('actif');
        $this->_resultat(
            $this->evaluation->basculer_actif((int) $this->input->post('id'), $actif),
            $actif ? 'Critère activé.' : 'Critère désactivé.'
        );
    }

    public function criteres_ordre()
    {
        $this->_exiger_ajax();
        $this->_resultat($this->evaluation->reordonner((array) $this->input->post('ids')), 'Ordre enregistré.');
    }

    public function criteres_repartir()
    {
        $this->_exiger_ajax();
        $this->_resultat($this->evaluation->repartir_equitablement(), 'Pondérations réparties à parts égales.');
    }

    public function criteres_modele()
    {
        $this->_exiger_ajax();
        $this->_resultat($this->evaluation->charger_modele(), 'Modèle de grille chargé : ajustez-le si besoin, puis validez.');
    }

    public function criteres_verrou()
    {
        $this->_exiger_ajax();
        if ($this->input->post('action') === 'deverrouiller') {
            $this->_resultat($this->evaluation->deverrouiller(), 'Grille déverrouillée : elle est de nouveau modifiable.');
        } else {
            $this->_resultat($this->evaluation->verrouiller($this->_admin_id()), 'Grille validée et verrouillée. Le jury peut commencer à noter.');
        }
    }


    /* ================================================================
     *  CLASSEMENT
     *
     *  L'identité des candidats n'est renvoyée qu'une fois le classement figé :
     *  tant que les délibérations sont ouvertes, le back-office reste anonyme.
     * ================================================================ */

    public function classement()
    {
        $data = [
            'title'      => 'Classement',
            'classement' => $this->evaluation->classement($this->evaluation->classement_fige()),
            'csrf'       => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ],
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/evaluation/classement', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    public function classement_donnees()
    {
        $this->_exiger_ajax();
        $this->_json(['ok' => TRUE] + $this->evaluation->classement($this->evaluation->classement_fige()));
    }

    public function classement_detail($id = 0)
    {
        $this->_exiger_ajax();
        $d = $this->evaluation->detail_classement((int) $id, $this->evaluation->classement_fige());
        if (!$d) {
            $this->_json(['ok' => FALSE, 'message' => 'Projet introuvable.'], 404);
            return;
        }
        $this->_json(['ok' => TRUE, 'projet' => $d]);
    }

    public function classement_figer()
    {
        $this->_exiger_ajax();
        $figer = $this->input->post('action') !== 'rouvrir';
        $r = $this->evaluation->figer_classement($figer, $this->_admin_id());
        if ($r !== TRUE) {
            $this->_json(['ok' => FALSE, 'message' => $r], 409);
            return;
        }
        $this->_json([
            'ok'      => TRUE,
            'message' => $figer ? 'Classement figé : l\'anonymat est levé dans le back-office.'
                : 'Classement rouvert : les projets sont de nouveau anonymes.',
        ] + $this->evaluation->classement($this->evaluation->classement_fige()));
    }

    /** Export Excel du classement (identités incluses seulement si figé) */
    public function classement_export()
    {
        $fige = $this->evaluation->classement_fige();
        $c    = $this->evaluation->classement($fige);

        $entetes = ['Rang', 'Code projet', 'Titre', 'Score /100', 'Écart entre jurés', 'Évaluations complètes', 'Statut'];
        foreach ($c['criteres'] as $cr) {
            $entetes[] = $cr['nom'] . ' (moy. /' . $cr['note_max'] . ')';
        }
        if ($fige) {
            $entetes = array_merge($entetes, ['Candidat / équipe', 'Responsable', 'Organisation', 'Référence']);
        }

        $statuts = ['complet' => 'Évaluation complète', 'en_cours' => 'En cours', 'non_evalue' => 'Non évalué'];
        $lignes = [];
        foreach ($c['rows'] as $r) {
            $l = [
                $r['rang'] !== NULL ? ($r['ex_aequo'] ? $r['rang'] . ' ex aequo' : (int) $r['rang']) : '—',
                $r['code_anonyme'],
                $r['titre'],
                $r['score'],
                $r['ecart'],
                $r['jures_complets'] . ' / ' . $r['jures_total'],
                $statuts[$r['statut']] ?? $r['statut'],
            ];
            foreach ($c['criteres'] as $cr) {
                $l[] = $r['par_critere'][$cr['id']]['moyenne'] ?? NULL;
            }
            if ($fige) {
                $l = array_merge($l, [$r['candidat'], $r['responsable'], $r['organisation'], $r['reference']]);
            }
            $lignes[] = $l;
        }

        $this->load->library('xlsx_simple');
        $tmp = $this->xlsx_simple->generer('Classement', $entetes, $lignes);
        $nom = 'satraco-classement-' . ($fige ? 'final' : 'provisoire-anonyme') . '-' . date('Y-m-d-Hi') . '.xlsx';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: private, no-store');
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    private function _donnees()
    {
        return ['criteres' => $this->evaluation->criteres(), 'etat' => $this->evaluation->etat()];
    }

    /** $r : TRUE ou message d'erreur métier */
    private function _resultat($r, $message_ok)
    {
        if ($r === TRUE) {
            $this->_json(['ok' => TRUE, 'message' => $message_ok] + $this->_donnees());
        } else {
            $this->_json(['ok' => FALSE, 'message' => $r], 409);
        }
    }

    /** ⚠ Même clé de session que dans CandidatsC::_admin_id() */
    private function _admin_id()
    {
        foreach (['admin_id', 'id_admin', 'user_id', 'id'] as $cle) {
            $v = $this->session->userdata($cle);
            if ($v) {
                return (int) $v;
            }
        }
        return NULL;
    }

    private function _exiger_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
    }

    private function _json(array $payload, $code = 200)
    {
        $payload['csrf'] = $this->security->get_csrf_hash();
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}
