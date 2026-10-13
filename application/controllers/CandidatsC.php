<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Candidatures — back-office.
 *
 * Page « Comptes à valider » :
 *   GET  candidats-en-attente                 → page
 *   GET  candidats-en-attente/liste           → JSON (liste paginée + stats)
 *   GET  candidats-en-attente/detail/{id}     → JSON (fiche candidat)
 *   POST candidats-en-attente/valider         → JSON (ids[])
 *   POST candidats-en-attente/rejeter         → JSON (ids[], motif)
 *
 * Page « Candidats & équipes » :
 *   GET  candidats                            → page
 *   GET  candidats/liste                      → JSON (liste paginée + compteurs)
 *   GET  candidats/detail/{id}                → JSON (fiche + historique)
 *   POST candidats/statut                     → JSON (ids[], action, motif)
 */
class CandidatsC extends Back_Controller
{
    const PAR_PAGE  = 15;
    const MOTIF_MIN = 10;
    const MASSE_MAX = 100;   // garde-fou sur les actions groupées

    public function __construct()
    {
        parent::__construct();
        $this->load->model('CandidatsM');
    }

    public function index()
    {
        redirect('candidats');
    }

    /* ================================================================
     *  PAGES
     * ================================================================ */

    public function en_attente()
    {
        $data = [
            'title' => 'Comptes à valider',
            'stats' => $this->CandidatsM->stats_en_attente(),
            'csrf'  => $this->_csrf(),
        ];
        $this->_page('back/components/app/candidats/en_attente', $data);
    }

    public function candidats()
    {
        $data = [
            'title'          => 'Candidats & équipes',
            'stats'          => $this->CandidatsM->stats_globales(),
            // Onglet ouvert au chargement : candidats?statut=valide (lien depuis le tableau de bord)
            'statut_initial' => in_array($this->input->get('statut'), CandidatsM::STATUTS, TRUE)
                ? $this->input->get('statut') : '',
            'csrf'           => $this->_csrf(),
        ];
        $this->_page('back/components/app/candidats/candidats', $data);
    }

    /* ================================================================
     *  API AJAX — LISTES
     * ================================================================ */

    public function liste_en_attente()
    {
        $this->_exiger_ajax();

        $page = max(1, (int) $this->input->get('page'));
        $res  = $this->CandidatsM->liste_en_attente($this->_filtres(), self::PAR_PAGE, ($page - 1) * self::PAR_PAGE);

        $this->_json($this->_paginer($res, $page) + [
            'stats' => $this->CandidatsM->stats_en_attente(),
        ]);
    }

    public function liste_candidats()
    {
        $this->_exiger_ajax();

        $page    = max(1, (int) $this->input->get('page'));
        $filtres = $this->_filtres() + ['statut' => (string) $this->input->get('statut', TRUE)];
        $res     = $this->CandidatsM->liste($filtres, self::PAR_PAGE, ($page - 1) * self::PAR_PAGE);

        $this->_json($this->_paginer($res, $page) + [
            'stats' => $this->CandidatsM->stats_globales(),
        ]);
    }

    public function detail($id = 0)
    {
        $this->_exiger_ajax();

        $candidat = $this->CandidatsM->detail((int) $id);
        if (!$candidat) {
            $this->_json(['ok' => FALSE, 'message' => 'Candidat introuvable.'], 404);
            return;
        }

        $this->_json(['ok' => TRUE, 'candidat' => $candidat]);
    }

    /* ================================================================
     *  API AJAX — CHANGEMENTS DE STATUT
     * ================================================================ */

    /** « Comptes à valider » */
    public function valider()
    {
        $this->_exiger_ajax();
        $this->_transition('valider');
    }

    /** « Comptes à valider » */
    public function rejeter()
    {
        $this->_exiger_ajax();
        $this->_transition('rejeter');
    }

    /** « Candidats & équipes » : suspendre, réactiver, rouvrir (et valider/rejeter) */
    public function changer_statut()
    {
        $this->_exiger_ajax();

        $action = (string) $this->input->post('action', TRUE);
        if (!isset(CandidatsM::TRANSITIONS[$action])) {
            $this->_json(['ok' => FALSE, 'message' => 'Action inconnue.'], 422);
            return;
        }
        $this->_transition($action);
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    private function _transition($action)
    {
        $motif_requis = CandidatsM::TRANSITIONS[$action][3];

        $motif = trim((string) $this->input->post('motif', TRUE));
        if ($motif_requis && mb_strlen($motif) < self::MOTIF_MIN) {
            $this->_json([
                'ok'      => FALSE,
                'message' => 'Le motif est obligatoire (' . self::MOTIF_MIN . ' caractères minimum).',
            ], 422);
            return;
        }
        $motif = $motif !== '' ? mb_substr($motif, 0, 1000) : NULL;

        $ids = (array) $this->input->post('ids');
        $ids = array_slice(array_filter(array_map('intval', $ids)), 0, self::MASSE_MAX);
        if (empty($ids)) {
            $this->_json(['ok' => FALSE, 'message' => 'Aucun candidat sélectionné.'], 422);
            return;
        }

        $traites = $this->CandidatsM->appliquer_transition(
            $ids,
            $action,
            $motif,
            $this->_admin_id(),
            $this->input->ip_address()
        );

        if ($traites === FALSE) {
            $this->_json(['ok' => FALSE, 'message' => 'Erreur lors de l\'enregistrement. Réessayez.'], 500);
            return;
        }

        // Notification e-mail (n'interrompt jamais la décision si l'envoi échoue)
        $echecs_mail = 0;
        foreach ($traites as $c) {
            if (!$this->_notifier($c, $action, $motif)) {
                $echecs_mail++;
            }
        }

        $participes = [
            'valider' => 'validé',
            'rejeter' => 'rejeté',
            'suspendre' => 'suspendu',
            'reactiver' => 'réactivé',
            'rouvrir' => 'remis en attente',
        ];
        $n       = count($traites);
        $ignores = count($ids) - $n;

        if ($n === 0) {
            $message = 'Aucun changement : le statut de ces comptes a déjà été modifié entre-temps.';
        } else {
            $message = $n . ' compte' . ($n > 1 ? 's ' : ' ') . $participes[$action]
                . ($n > 1 && $action !== 'rouvrir' ? 's' : '') . '.';
            if ($ignores > 0) {
                $message .= ' ' . $ignores . ' ignoré(s) (statut incompatible).';
            }
        }
        if ($echecs_mail > 0) {
            $message .= ' ⚠ ' . $echecs_mail . ' e-mail(s) non envoyé(s).';
        }

        $this->_json([
            'ok'       => TRUE,
            'message'  => $message,
            'traites'  => array_map('intval', array_column($traites, 'id')),
            'restants' => $this->CandidatsM->compter_en_attente(),
        ]);
    }

    /**
     * E-mail au candidat après un changement de statut.
     * « rouvrir » n'envoie rien : le dossier repasse simplement en revue.
     */
    private function _notifier(array $c, $action, $motif)
    {
        $nom_concours = 'Concours d\'architecture SATRACO';
        $nom          = html_escape($c['prenom'] . ' ' . $c['nom']);
        $equipe       = $c['type_participation'] === 'equipe' && $c['nom_equipe']
            ? html_escape(' (équipe « ' . $c['nom_equipe'] . ' »)') : '';
        $motif_html   = '<p><strong>Motif :</strong><br>' . nl2br(html_escape((string) $motif)) . '</p>';
        $lien         = '<p><a href="' . base_url() . '">Accéder à mon espace candidat</a></p>';

        switch ($action) {
            case 'valider':
                $sujet = 'Votre inscription est validée — ' . $nom_concours;
                $corps = '<p>Votre inscription' . $equipe . ' au ' . $nom_concours . ' a été <strong>validée</strong>.</p>'
                    . '<p>Vous pouvez dès maintenant télécharger le brief et préparer votre soumission.</p>' . $lien;
                break;
            case 'rejeter':
                $sujet = 'Votre inscription — ' . $nom_concours;
                $corps = '<p>Après examen, votre inscription' . $equipe . ' n\'a pas pu être validée.</p>' . $motif_html
                    . '<p>Pour toute question, répondez simplement à cet e-mail.</p>';
                break;
            case 'suspendre':
                $sujet = 'Votre accès est suspendu — ' . $nom_concours;
                $corps = '<p>L\'accès à votre espace candidat' . $equipe . ' a été <strong>temporairement suspendu</strong>.</p>'
                    . $motif_html . '<p>Contactez l\'équipe SATRACO en répondant à cet e-mail pour régulariser votre situation.</p>';
                break;
            case 'reactiver':
                $sujet = 'Votre accès est rétabli — ' . $nom_concours;
                $corps = '<p>L\'accès à votre espace candidat' . $equipe . ' a été <strong>rétabli</strong>.</p>' . $lien;
                break;
            default:
                return TRUE; // pas de notification
        }

        $corps = '<p>Bonjour ' . $nom . ',</p>' . $corps
            . '<p>— L\'équipe SATRACO<br><small>Réf. ' . html_escape($c['reference']) . '</small></p>';

        $this->load->library('email');
        $expediteur = $this->config->item('email_expediteur') ?: $this->config->item('smtp_user');

        $this->email->clear();
        $this->email->set_mailtype('html');
        $this->email->from($expediteur, 'SATRACO — Concours');
        $this->email->to($c['email']);
        $this->email->subject($sujet);
        $this->email->message($corps);

        $ok = @$this->email->send(FALSE);
        if (!$ok) {
            log_message('error', 'Notification candidat #' . $c['id'] . ' (' . $action . ') : '
                . $this->email->print_debugger(['headers']));
        }
        return $ok;
    }

    /** Filtres communs aux deux listes */
    private function _filtres()
    {
        $f = [];
        foreach (['q', 'type', 'categorie', 'email', 'anciennete', 'tri'] as $cle) {
            $f[$cle] = (string) $this->input->get($cle, TRUE);
        }
        return $f;
    }

    private function _paginer(array $res, $page)
    {
        $pages = max(1, (int) ceil($res['total'] / self::PAR_PAGE));
        return [
            'ok'      => TRUE,
            'rows'    => $res['rows'],
            'total'   => $res['total'],
            'page'    => min($page, $pages),
            'pages'   => $pages,
            'par_page' => self::PAR_PAGE,
        ];
    }

    private function _page($vue, array $data)
    {
        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view($vue, $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    private function _csrf()
    {
        return [
            'name' => $this->security->get_csrf_token_name(),
            'hash' => $this->security->get_csrf_hash(),
        ];
    }

    /**
     * Id de l'administrateur connecté.
     * ⚠ Adapte la clé de session à celle définie dans BackOfficeC::authenticate().
     */
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
        // Renvoie le nouveau jeton CSRF (utile si csrf_regenerate = TRUE)
        $payload['csrf'] = $this->security->get_csrf_hash();

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}
