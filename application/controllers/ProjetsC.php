<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Projets soumis — back-office.
 *
 *   GET  projets                        → page
 *   GET  projets/liste                  → JSON (liste paginée + stats)
 *   GET  projets/detail/{id}            → JSON (fiche projet, fichiers, historique)
 *   POST projets/conformite             → JSON (ids[], verdict, motif)
 *   POST projets/relancer               → JSON (ids[] de candidats validés sans projet)
 *   GET  projets/fichier/{id}           → téléchargement d'un fichier
 *   GET  projets/apercu/{id}            → affichage inline (images, PDF)
 *   GET  projets/zip/{id}               → archive ZIP de tous les fichiers d'un projet
 */
class ProjetsC extends Back_Controller
{
    const PAR_PAGE  = 15;
    const MOTIF_MIN = 10;
    const MASSE_MAX = 100;

    /** Types affichables directement dans le navigateur */
    const APERCU_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ProjetsM');
    }

    /* ================================================================
     *  PAGE
     * ================================================================ */

    public function index()
    {
        $onglet = (string) $this->input->get('onglet');

        $data = [
            'title'          => 'Projets soumis',
            'stats'          => $this->ProjetsM->stats(),
            'onglet_initial' => in_array($onglet, ProjetsM::ONGLETS, TRUE) ? $onglet : 'soumis',
            'csrf'           => [
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash(),
            ],
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/candidats/projets', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /* ================================================================
     *  API AJAX
     * ================================================================ */

    public function liste()
    {
        $this->_exiger_ajax();

        $page = max(1, (int) $this->input->get('page'));
        $f    = [];
        foreach (['onglet', 'q', 'type', 'categorie', 'tri'] as $cle) {
            $f[$cle] = (string) $this->input->get($cle, TRUE);
        }

        $res   = $this->ProjetsM->liste($f, self::PAR_PAGE, ($page - 1) * self::PAR_PAGE);
        $pages = max(1, (int) ceil($res['total'] / self::PAR_PAGE));

        $this->_json([
            'ok'       => TRUE,
            'rows'     => $res['rows'],
            'total'    => $res['total'],
            'page'     => min($page, $pages),
            'pages'    => $pages,
            'par_page' => self::PAR_PAGE,
            'stats'    => $this->ProjetsM->stats(),
        ]);
    }

    public function detail($id = 0)
    {
        $this->_exiger_ajax();

        $projet = $this->ProjetsM->detail((int) $id);
        if (!$projet) {
            $this->_json(['ok' => FALSE, 'message' => 'Projet introuvable.'], 404);
            return;
        }

        // Indique au front quels fichiers sont réellement présents sur le disque
        foreach ($projet['fichiers'] as &$fic) {
            $complet         = $this->ProjetsM->fichier($fic['id']);
            $fic['present']  = !empty($complet['absolu']);
            $fic['apercu']   = $fic['present'] && in_array($fic['mime'], self::APERCU_MIMES, TRUE);
        }
        unset($fic);

        $this->_json(['ok' => TRUE, 'projet' => $projet, 'categories' => ProjetsM::CATEGORIES]);
    }

    public function conformite()
    {
        $this->_exiger_ajax();

        $verdict = (string) $this->input->post('verdict', TRUE);
        if (!in_array($verdict, ProjetsM::CONFORMITES, TRUE)) {
            $this->_json(['ok' => FALSE, 'message' => 'Verdict inconnu.'], 422);
            return;
        }

        $motif = trim((string) $this->input->post('motif', TRUE));
        if ($verdict === 'non_conforme' && mb_strlen($motif) < self::MOTIF_MIN) {
            $this->_json(['ok' => FALSE, 'message' => 'Le motif de non-conformité est obligatoire (' . self::MOTIF_MIN . ' caractères minimum).'], 422);
            return;
        }
        $motif = $motif !== '' ? mb_substr($motif, 0, 1000) : NULL;

        $ids = array_slice(array_filter(array_map('intval', (array) $this->input->post('ids'))), 0, self::MASSE_MAX);
        if (empty($ids)) {
            $this->_json(['ok' => FALSE, 'message' => 'Aucun projet sélectionné.'], 422);
            return;
        }

        $traites = $this->ProjetsM->definir_conformite($ids, $verdict, $motif, $this->_admin_id(), $this->input->ip_address());
        if ($traites === FALSE) {
            $this->_json(['ok' => FALSE, 'message' => 'Erreur lors de l\'enregistrement. Réessayez.'], 500);
            return;
        }

        // Seule la non-conformité est notifiée : le candidat peut corriger avant la clôture
        $echecs = 0;
        if ($verdict === 'non_conforme') {
            foreach ($traites as $p) {
                $ok = $this->_envoyer(
                    $p['email'],
                    'Votre projet nécessite une correction — Concours SATRACO',
                    '<p>Votre projet <strong>« ' . html_escape($p['titre']) . ' »</strong> a été examiné par l\'équipe SATRACO '
                        . 'et ne peut pas être accepté en l\'état.</p>'
                        . '<p><strong>Motif :</strong><br>' . nl2br(html_escape($motif)) . '</p>'
                        . '<p>Vous pouvez remplacer vos fichiers depuis votre espace candidat <strong>jusqu\'à la date limite de soumission</strong>.</p>'
                        . '<p><a href="' . base_url() . '">Accéder à mon espace</a></p>',
                    $p
                );
                $echecs += $ok ? 0 : 1;
            }
        }

        $libelles = ['conforme' => 'marqué(s) conforme(s)', 'non_conforme' => 'marqué(s) non conforme(s)', 'a_verifier' => 'remis à vérifier'];
        $n = count($traites);
        $message = $n === 0
            ? 'Aucun changement : ces projets avaient déjà ce statut.'
            : $n . ' projet(s) ' . $libelles[$verdict] . '.';
        if ($n > 0 && count($ids) > $n) {
            $message .= ' ' . (count($ids) - $n) . ' ignoré(s).';
        }
        if ($echecs) {
            $message .= ' ⚠ ' . $echecs . ' e-mail(s) non envoyé(s).';
        }

        $this->_json(['ok' => TRUE, 'message' => $message, 'traites' => array_map('intval', array_column($traites, 'id'))]);
    }

    /** Rappel aux candidats validés qui n'ont encore rien déposé */
    public function relancer()
    {
        $this->_exiger_ajax();

        $ids = array_slice(array_filter(array_map('intval', (array) $this->input->post('ids'))), 0, self::MASSE_MAX);
        $candidats = $this->ProjetsM->candidats_a_relancer($ids);

        if (empty($candidats)) {
            $this->_json(['ok' => FALSE, 'message' => 'Aucun candidat à relancer (projet déjà commencé ou compte non validé).'], 422);
            return;
        }

        $envoyes = 0;
        foreach ($candidats as $c) {
            $envoyes += $this->_envoyer(
                $c['email'],
                'Rappel : soumettez votre projet — Concours SATRACO',
                '<p>Votre inscription au concours d\'architecture SATRACO est validée, mais nous n\'avons pas encore reçu votre projet.</p>'
                    . '<p>Le brief et les fichiers de base sont disponibles dans votre espace candidat. '
                    . 'Pensez à déposer vos pièces <strong>avant la date limite de soumission</strong> : la plateforme se verrouille automatiquement.</p>'
                    . '<p><a href="' . base_url() . '">Accéder à mon espace</a></p>',
                $c
            ) ? 1 : 0;
        }

        $echecs  = count($candidats) - $envoyes;
        $message = $envoyes . ' rappel(s) envoyé(s).' . ($echecs ? ' ⚠ ' . $echecs . ' échec(s) d\'envoi.' : '');

        $this->_json(['ok' => $envoyes > 0, 'message' => $message, 'envoyes' => $envoyes]);
    }

    /* ================================================================
     *  FICHIERS
     * ================================================================ */

    public function telecharger($id = 0)
    {
        $this->_servir_fichier((int) $id, FALSE);
    }

    public function apercu($id = 0)
    {
        $this->_servir_fichier((int) $id, TRUE);
    }

    /** Archive ZIP des fichiers actifs, rangés par catégorie */
    public function zip($id = 0)
    {
        $projet = $this->ProjetsM->soumission_courte((int) $id);
        if (!$projet) {
            show_404();
        }
        if (!class_exists('ZipArchive')) {
            show_error('L\'extension PHP « zip » n\'est pas activée sur ce serveur.', 500);
        }

        $fichiers = array_filter($this->ProjetsM->fichiers_actifs($projet['id']), function ($f) {
            return !empty($f['absolu']);
        });
        if (empty($fichiers)) {
            show_error('Aucun fichier disponible sur le serveur pour ce projet.', 404, 'Archive vide');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'prj');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== TRUE) {
            show_error('Impossible de créer l\'archive.', 500);
        }

        $noms = [];
        foreach ($fichiers as $f) {
            $dossier = ProjetsM::CATEGORIES[$f['categorie']] ?? 'Autres pièces';
            $nom     = $dossier . '/' . $this->_nom_sur($f['nom_original']);
            // Évite d'écraser deux fichiers portant le même nom
            for ($i = 2; isset($noms[$nom]); $i++) {
                $nom = $dossier . '/' . pathinfo($f['nom_original'], PATHINFO_FILENAME) . " ({$i})."
                    . pathinfo($f['nom_original'], PATHINFO_EXTENSION);
            }
            $noms[$nom] = TRUE;
            // Fichiers déjà compressés (zip, jpg, pdf) : stockage sans recompression, bien plus rapide
            $zip->addFile($f['absolu'], $nom);
            if (method_exists($zip, 'setCompressionName')) {
                $zip->setCompressionName($nom, ZipArchive::CM_STORE);
            }
        }
        $zip->close();

        $this->_streamer($tmp, $projet['code_anonyme'] . '.zip', 'application/zip', FALSE);
        @unlink($tmp);
        exit;
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    private function _servir_fichier($id, $inline)
    {
        $f = $this->ProjetsM->fichier($id);
        if (!$f) {
            show_404();
        }
        if (empty($f['absolu'])) {
            show_error('Ce fichier est référencé mais introuvable sur le serveur.', 404, 'Fichier introuvable');
        }
        // L'aperçu n'est autorisé que pour les types sûrs ; le reste est forcé en téléchargement
        $inline = $inline && in_array($f['mime'], self::APERCU_MIMES, TRUE);

        $this->_streamer($f['absolu'], $f['nom_original'], $f['mime'], $inline);
        exit;
    }

    /** Envoi par blocs : supporte des fichiers de 50 Mo sans saturer la mémoire PHP */
    private function _streamer($chemin, $nom, $mime, $inline)
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nom_ascii = $this->_nom_sur($nom);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($chemin));
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
            . '; filename="' . $nom_ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nom));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        $h = fopen($chemin, 'rb');
        while (!feof($h)) {
            echo fread($h, 1048576);
            flush();
        }
        fclose($h);
    }

    private function _nom_sur($nom)
    {
        $nom = preg_replace('/[^\w.\- ()]+/u', '_', basename((string) $nom));
        return trim($nom, '._ ') ?: 'fichier';
    }

    private function _envoyer($email, $sujet, $corps, array $c)
    {
        $this->load->library('email');
        $expediteur = $this->config->item('email_expediteur') ?: $this->config->item('smtp_user');

        $corps = '<p>Bonjour ' . html_escape($c['prenom'] . ' ' . $c['nom']) . ',</p>' . $corps
            . '<p>— L\'équipe SATRACO<br><small>Réf. ' . html_escape($c['reference']) . '</small></p>';

        $this->email->clear();
        $this->email->set_mailtype('html');
        $this->email->from($expediteur, 'SATRACO — Concours');
        $this->email->to($email);
        $this->email->subject($sujet);
        $this->email->message($corps);

        $ok = @$this->email->send(FALSE);
        if (!$ok) {
            log_message('error', 'ProjetsC e-mail vers ' . $email . ' : ' . $this->email->print_debugger(['headers']));
        }
        return $ok;
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
