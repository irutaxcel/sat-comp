<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Exports — back-office.
 *
 *   GET exports                         → page
 *   GET exports/compter                 → JSON (nombre de lignes pour type + filtres)
 *   GET exports/historique              → JSON (derniers exports réalisés)
 *   GET exports/telecharger             → fichier xlsx / csv (type, format, filtres)
 *   GET exports/archive                 → ZIP des dossiers projets (anonymisés par code)
 */
class ExportsC extends Back_Controller
{
    /** Au-delà, l'export est refusé (protection mémoire) — largement suffisant pour un concours */
    const LIGNES_MAX = 50000;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ExportsM');
    }

    /* ================================================================
     *  PAGE
     * ================================================================ */

    public function index()
    {
        $data = [
            'title'      => 'Exports',
            'compteurs'  => $this->ExportsM->compteurs(),
            'historique' => $this->ExportsM->derniers_exports(10),
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/candidats/exports', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /* ================================================================
     *  API AJAX
     * ================================================================ */

    public function compter()
    {
        $this->_exiger_ajax();

        $type = (string) $this->input->get('type', TRUE);
        if (!isset(ExportsM::TYPES[$type])) {
            $this->_json(['ok' => FALSE, 'message' => 'Type d\'export inconnu.'], 422);
            return;
        }
        $f = $this->ExportsM->filtres_valides($type, (array) $this->input->get(NULL, TRUE));

        try {
            $this->_json(['ok' => TRUE, 'type' => $type, 'nb' => $this->ExportsM->compter($type, $f)]);
        } catch (Throwable $e) {
            log_message('error', 'ExportsC::compter ' . $e->getMessage());
            $this->_json(['ok' => FALSE, 'message' => 'Données indisponibles (tables manquantes ?).'], 500);
        }
    }

    public function historique()
    {
        $this->_exiger_ajax();
        $this->_json(['ok' => TRUE, 'rows' => $this->ExportsM->derniers_exports(10)]);
    }

    /* ================================================================
     *  TÉLÉCHARGEMENTS
     * ================================================================ */

    public function telecharger()
    {
        $type   = (string) $this->input->get('type', TRUE);
        $format = (string) $this->input->get('format', TRUE) === 'csv' ? 'csv' : 'xlsx';

        if (!isset(ExportsM::TYPES[$type]) || $type === 'archive') {
            show_404();
        }
        $f = $this->ExportsM->filtres_valides($type, (array) $this->input->get(NULL, TRUE));

        list($entetes, $lignes) = $this->ExportsM->donnees($type, $f);
        if (count($lignes) > self::LIGNES_MAX) {
            show_error('Export trop volumineux (' . count($lignes) . ' lignes). Affinez les filtres.', 413);
        }

        list($libelle, $base) = ExportsM::TYPES[$type];
        $suffixe = !empty($f['anonymise']) ? '-anonymise' : '';
        $nom     = 'satraco-' . $base . $suffixe . '-' . date('Y-m-d-Hi') . '.' . $format;

        $this->ExportsM->journaliser($type, $format, $f, count($lignes), $this->_admin_id(), $this->_admin_libelle(), $this->input->ip_address());

        if ($format === 'csv') {
            $this->_envoyer_csv($nom, $entetes, $lignes);
        } else {
            $this->load->library('xlsx_simple');
            $tmp = $this->xlsx_simple->generer($libelle, $entetes, $lignes);
            $this->_envoyer_fichier($tmp, $nom, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            @unlink($tmp);
        }
        exit;
    }

    /**
     * Archive ZIP des dossiers projets, prête à transmettre au jury :
     *   PRJ-XXXX/
     *     fiche-projet.txt     (titre, note descriptive, version, date)
     *     Plans/…, Coupes/…, Rendus 3D/…
     *   index.csv              (récapitulatif)
     * Aucun nom de candidat n'y figure : les dossiers sont nommés par code anonyme.
     */
    public function archive()
    {
        if (!class_exists('ZipArchive')) {
            show_error('L\'extension PHP « zip » n\'est pas activée sur ce serveur.', 500);
        }
        $this->load->model('ProjetsM');

        $f = $this->ExportsM->filtres_valides('archive', (array) $this->input->get(NULL, TRUE));
        $projets = $this->ExportsM->projets_archive($f);
        if (empty($projets)) {
            show_error('Aucun projet ne correspond à ces critères.', 404, 'Archive vide');
        }

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'arc');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $index = [['Code projet', 'Titre', 'Version', 'Conformité', 'Dernière soumission', 'Fichiers inclus', 'Fichiers manquants']];
        $total_fichiers = 0;

        foreach ($projets as $p) {
            $dossier = preg_replace('/[^A-Za-z0-9\-]/', '', $p['code_anonyme']);
            $fiche = "Code projet : {$p['code_anonyme']}\r\nTitre : {$p['titre']}\r\nVersion : {$p['version']}\r\n"
                . 'Soumis le : ' . ($p['derniere_soumission_le'] ? date('d/m/Y H:i', strtotime($p['derniere_soumission_le'])) : '—') . "\r\n\r\n"
                . "Note descriptive :\r\n" . ($p['note_descriptive'] ?: '(aucune)') . "\r\n";
            $zip->addFromString($dossier . '/fiche-projet.txt', $fiche);

            $inclus = 0;
            $manquants = 0;
            $noms = [];
            foreach ($this->ProjetsM->fichiers_actifs($p['id']) as $fic) {
                if (empty($fic['absolu'])) {
                    $manquants++;
                    continue;
                }
                $sous = ProjetsM::CATEGORIES[$fic['categorie']] ?? 'Autres pièces';
                $nom  = $dossier . '/' . $sous . '/' . $this->_nom_sur($fic['nom_original']);
                for ($i = 2; isset($noms[$nom]); $i++) {
                    $nom = $dossier . '/' . $sous . '/' . $i . '-' . $this->_nom_sur($fic['nom_original']);
                }
                $noms[$nom] = TRUE;
                $zip->addFile($fic['absolu'], $nom);
                if (method_exists($zip, 'setCompressionName')) {
                    $zip->setCompressionName($nom, ZipArchive::CM_STORE);   // déjà compressés (pdf, jpg, zip)
                }
                $inclus++;
            }
            $total_fichiers += $inclus;
            $index[] = [
                $p['code_anonyme'],
                $p['titre'],
                $p['version'],
                ExportsM::CONFORMITES[$p['conformite']] ?? '',
                $p['derniere_soumission_le'] ? date('d/m/Y H:i', strtotime($p['derniere_soumission_le'])) : '',
                $inclus,
                $manquants,
            ];
        }

        $zip->addFromString('index.csv', $this->_csv_chaine($index));
        $zip->close();

        $this->ExportsM->journaliser('archive', 'zip', $f, count($projets), $this->_admin_id(), $this->_admin_libelle(), $this->input->ip_address());

        $nom = 'satraco-dossiers-' . (isset($f['conformite']) ? $f['conformite'] . '-' : '') . date('Y-m-d-Hi') . '.zip';
        $this->_envoyer_fichier($tmp, $nom, 'application/zip');
        @unlink($tmp);
        exit;
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    /** CSV compatible Excel FR : UTF-8 avec BOM, séparateur « ; » */
    private function _envoyer_csv($nom, array $entetes, array $lignes)
    {
        $this->_entetes_http($nom, 'text/csv; charset=UTF-8');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $entetes, ';');
        foreach ($lignes as $l) {
            fputcsv($out, array_map([$this, '_cellule_csv'], $l), ';');
        }
        fclose($out);
    }

    private function _csv_chaine(array $lignes)
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF");
        foreach ($lignes as $l) {
            fputcsv($h, array_map([$this, '_cellule_csv'], $l), ';');
        }
        rewind($h);
        $s = stream_get_contents($h);
        fclose($h);
        return $s;
    }

    /**
     * Neutralise l'injection de formules (CSV injection) : une cellule commençant
     * par = + - @ ou une tabulation serait exécutée par Excel à l'ouverture.
     */
    public function _cellule_csv($v)
    {
        if ($v === NULL) {
            return '';
        }
        if (is_int($v) || is_float($v)) {
            return str_replace('.', ',', (string) $v);   // décimale FR
        }
        $v = (string) $v;
        return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== FALSE) ? "'" . $v : $v;
    }

    private function _envoyer_fichier($chemin, $nom, $mime)
    {
        $this->_entetes_http($nom, $mime, filesize($chemin));
        $h = fopen($chemin, 'rb');
        while (!feof($h)) {
            echo fread($h, 1048576);
            flush();
        }
        fclose($h);
    }

    private function _entetes_http($nom, $mime, $taille = NULL)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        if ($taille !== NULL) {
            header('Content-Length: ' . $taille);
        }
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
    }

    private function _nom_sur($nom)
    {
        $nom = preg_replace('/[^\w.\- ()]+/u', '_', basename((string) $nom));
        return trim($nom, '._ ') ?: 'fichier';
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

    /** Nom lisible de l'admin pour le journal (adapte les clés à ta session) */
    private function _admin_libelle()
    {
        foreach (['admin_nom', 'nom_complet', 'nom', 'username', 'email'] as $cle) {
            $v = $this->session->userdata($cle);
            if ($v && is_string($v)) {
                return $v;
            }
        }
        return 'Administrateur';
    }

    private function _exiger_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
    }

    private function _json(array $payload, $code = 200)
    {
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($payload, JSON_UNESCAPED_UNICODE));
    }
}
