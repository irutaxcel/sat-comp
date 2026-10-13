<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Projets soumis — accès aux données des soumissions.
 *
 * Tables : soumissions, soumission_fichiers, soumission_historique
 *          (+ candidats, candidat_membres du module Candidatures)
 *
 * Statut de la soumission (côté candidat) : brouillon → soumis
 * Contrôle de conformité (côté SATRACO)   : a_verifier → conforme | non_conforme
 */
class ProjetsM extends CI_Model
{
    const T_SOUMISSIONS = 'soumissions';
    const T_FICHIERS    = 'soumission_fichiers';
    const T_HISTORIQUE  = 'soumission_historique';
    const T_CANDIDATS   = 'candidats';
    const T_MEMBRES     = 'candidat_membres';

    /** Onglets de la page (liste blanche) */
    const ONGLETS = ['soumis', 'a_verifier', 'conforme', 'non_conforme', 'brouillon', 'non_soumis'];

    const CONFORMITES = ['a_verifier', 'conforme', 'non_conforme'];

    const CATEGORIES = [
        'plan' => 'Plans',
        'coupe' => 'Coupes',
        'elevation' => 'Élévations',
        'rendu_3d' => 'Rendus 3D',
        'note' => 'Note / notice',
        'autre' => 'Autres pièces',
    ];

    /**
     * Dossier de stockage des fichiers déposés (hors de la racine web).
     * ⚠ Aligne-le sur le dossier utilisé par le formulaire de dépôt côté candidat.
     */
    public function dossier_stockage()
    {
        return rtrim($this->config->item('dossier_soumissions') ?: APPPATH . 'storage/soumissions', '/\\') . DIRECTORY_SEPARATOR;
    }

    private $tris = [
        'recent' => ['s.derniere_soumission_le', 'DESC'],
        'ancien' => ['s.derniere_soumission_le', 'ASC'],
        'titre'  => ['s.titre', 'ASC'],
        'nom'    => ['c.nom', 'ASC'],
    ];

    /* ================================================================
     *  LISTE
     * ================================================================ */

    /**
     * @param array $f  onglet, q, type, categorie, tri
     * @return array    ['rows' => [...], 'total' => int]
     */
    public function liste(array $f, $limit = 15, $offset = 0)
    {
        $onglet = in_array($f['onglet'] ?? '', self::ONGLETS, TRUE) ? $f['onglet'] : 'soumis';

        return $onglet === 'non_soumis'
            ? $this->_liste_non_soumis($f, $limit, $offset)
            : $this->_liste_soumissions($onglet, $f, $limit, $offset);
    }

    private function _liste_soumissions($onglet, array $f, $limit, $offset)
    {
        $tf = $this->db->dbprefix(self::T_FICHIERS);

        $this->db->from(self::T_SOUMISSIONS . ' s')
            ->join(self::T_CANDIDATS . ' c', 'c.id = s.candidat_id');

        if ($onglet === 'brouillon') {
            $this->db->where('s.statut', 'brouillon');
        } else {
            $this->db->where('s.statut', 'soumis');
            if (in_array($onglet, self::CONFORMITES, TRUE)) {
                $this->db->where('s.conformite', $onglet);
            }
        }
        $this->_filtres_candidat($f, TRUE);

        $total = (int) $this->db->count_all_results('', FALSE);

        $tri = $this->tris[$f['tri'] ?? ''] ?? ($onglet === 'brouillon' ? ['s.created_at', 'DESC'] : $this->tris['recent']);

        $rows = $this->db
            ->select('s.id, s.code_anonyme, s.titre, s.statut, s.conformite, s.version,
                      s.premiere_soumission_le, s.derniere_soumission_le, s.created_at,
                      c.id AS candidat_id, c.reference, c.nom, c.prenom, c.email,
                      c.type_participation, c.nom_equipe, c.categorie, c.organisation')
            ->select("(SELECT COUNT(*) FROM {$tf} f WHERE f.soumission_id = s.id AND f.actif = 1) AS nb_fichiers", FALSE)
            ->select("(SELECT COALESCE(SUM(f.taille_octets),0) FROM {$tf} f WHERE f.soumission_id = s.id AND f.actif = 1) AS taille", FALSE)
            ->order_by($tri[0], $tri[1])
            ->order_by('s.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result_array();

        return ['rows' => $rows, 'total' => $total];
    }

    /** Candidats validés n'ayant encore rien déposé (pour relance) */
    private function _liste_non_soumis(array $f, $limit, $offset)
    {
        $ts = $this->db->dbprefix(self::T_SOUMISSIONS);

        $this->db->from(self::T_CANDIDATS . ' c')
            ->where('c.statut', 'valide')
            ->where("NOT EXISTS (SELECT 1 FROM {$ts} sx WHERE sx.candidat_id = c.id)", NULL, FALSE);
        $this->_filtres_candidat($f, FALSE);

        $total = (int) $this->db->count_all_results('', FALSE);

        $rows = $this->db
            ->select('c.id AS candidat_id, c.reference, c.nom, c.prenom, c.email, c.telephone,
                      c.type_participation, c.nom_equipe, c.categorie, c.organisation, c.decide_le')
            ->order_by('c.decide_le', 'ASC')
            ->order_by('c.id', 'ASC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result_array();

        return ['rows' => $rows, 'total' => $total];
    }

    /* ================================================================
     *  STATISTIQUES
     * ================================================================ */

    public function stats()
    {
        $ts = $this->db->dbprefix(self::T_SOUMISSIONS);
        $tc = $this->db->dbprefix(self::T_CANDIDATS);
        $tf = $this->db->dbprefix(self::T_FICHIERS);

        $s = $this->db->query(
            "SELECT
                SUM(statut = 'soumis')                                  AS soumis,
                SUM(statut = 'brouillon')                               AS brouillon,
                SUM(statut = 'soumis' AND conformite = 'a_verifier')    AS a_verifier,
                SUM(statut = 'soumis' AND conformite = 'conforme')      AS conforme,
                SUM(statut = 'soumis' AND conformite = 'non_conforme')  AS non_conforme,
                SUM(statut = 'soumis' AND derniere_soumission_le >= NOW() - INTERVAL 24 HOUR) AS dernieres_24h
             FROM {$ts}"
        )->row_array() ?: [];

        $c = $this->db->query(
            "SELECT
                COUNT(*) AS valides,
                SUM(NOT EXISTS (SELECT 1 FROM {$ts} sx WHERE sx.candidat_id = c.id)) AS non_soumis
             FROM {$tc} c WHERE c.statut = 'valide'"
        )->row_array() ?: [];

        $v = $this->db->query(
            "SELECT COUNT(*) AS fichiers, COALESCE(SUM(f.taille_octets), 0) AS volume
               FROM {$tf} f JOIN {$ts} s ON s.id = f.soumission_id
              WHERE f.actif = 1 AND s.statut = 'soumis'"
        )->row_array() ?: [];

        $stats = array_map('intval', $s + $c + ['fichiers' => $v['fichiers'] ?? 0]);
        $stats['volume'] = (float) ($v['volume'] ?? 0);   // peut dépasser 2 Go → pas d'intval
        $stats['taux']   = $stats['valides'] > 0 ? (int) round(100 * $stats['soumis'] / $stats['valides']) : 0;

        return $stats;
    }

    /* ================================================================
     *  DÉTAIL
     * ================================================================ */

    public function detail($id)
    {
        $s = $this->db
            ->select('s.*, c.reference, c.nom, c.prenom, c.email, c.telephone, c.type_participation,
                      c.nom_equipe, c.categorie, c.organisation, c.statut AS statut_candidat')
            ->from(self::T_SOUMISSIONS . ' s')
            ->join(self::T_CANDIDATS . ' c', 'c.id = s.candidat_id')
            ->where('s.id', (int) $id)
            ->get()
            ->row_array();

        if (!$s) {
            return NULL;
        }
        unset($s['controle_par']);

        $s['membres'] = $this->db
            ->select('nom, prenom, role')
            ->where('candidat_id', (int) $s['candidat_id'])
            ->order_by('id', 'ASC')
            ->get(self::T_MEMBRES)
            ->result_array();

        // Fichiers actifs, dans l'ordre des catégories
        $s['fichiers'] = $this->db
            ->select('id, categorie, nom_original, extension, mime, taille_octets, version, created_at')
            ->where('soumission_id', (int) $id)
            ->where('actif', 1)
            ->order_by("FIELD(categorie, 'plan','coupe','elevation','rendu_3d','note','autre')", '', FALSE)
            ->order_by('id', 'ASC')
            ->get(self::T_FICHIERS)
            ->result_array();

        $s['nb_versions_remplacees'] = (int) $this->db
            ->where('soumission_id', (int) $id)->where('actif', 0)
            ->count_all_results(self::T_FICHIERS);

        $s['historique'] = $this->db
            ->select('action, detail, auteur_type, created_at')
            ->where('soumission_id', (int) $id)
            ->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC')
            ->limit(30)
            ->get(self::T_HISTORIQUE)
            ->result_array();

        return $s;
    }

    /* ================================================================
     *  FICHIERS
     * ================================================================ */

    /**
     * Fichier téléchargeable + chemin absolu vérifié.
     * Retourne NULL si l'enregistrement n'existe pas ou si le chemin sort du dossier de stockage.
     */
    public function fichier($id)
    {
        $f = $this->db
            ->select('f.id, f.soumission_id, f.nom_original, f.chemin, f.extension, f.mime, f.taille_octets, s.code_anonyme')
            ->from(self::T_FICHIERS . ' f')
            ->join(self::T_SOUMISSIONS . ' s', 's.id = f.soumission_id')
            ->where('f.id', (int) $id)
            ->get()
            ->row_array();

        if (!$f) {
            return NULL;
        }
        $f['absolu'] = $this->_chemin_sur($f['chemin']);
        return $f;
    }

    /** Fichiers actifs d'une soumission, avec chemins vérifiés (pour l'archive ZIP) */
    public function fichiers_actifs($soumission_id)
    {
        $rows = $this->db
            ->select('id, categorie, nom_original, chemin')
            ->where('soumission_id', (int) $soumission_id)
            ->where('actif', 1)
            ->order_by('id', 'ASC')
            ->get(self::T_FICHIERS)
            ->result_array();

        foreach ($rows as &$r) {
            $r['absolu'] = $this->_chemin_sur($r['chemin']);
        }
        return $rows;
    }

    public function soumission_courte($id)
    {
        return $this->db->select('id, code_anonyme, titre, statut')
            ->where('id', (int) $id)
            ->get(self::T_SOUMISSIONS)->row_array();
    }

    /* ================================================================
     *  CONTRÔLE DE CONFORMITÉ
     * ================================================================ */

    /**
     * Applique un verdict de conformité aux soumissions « soumis ».
     * @return array|false  Soumissions traitées (+ coordonnées du candidat), FALSE si erreur
     */
    public function definir_conformite(array $ids, $verdict, $motif, $admin_id, $ip)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids) || !in_array($verdict, self::CONFORMITES, TRUE)) {
            return [];
        }

        $ts = $this->db->dbprefix(self::T_SOUMISSIONS);
        $tc = $this->db->dbprefix(self::T_CANDIDATS);
        $ph = implode(',', array_fill(0, count($ids), '?'));

        $this->db->trans_start();

        $cibles = $this->db->query(
            "SELECT s.id, s.code_anonyme, s.titre, c.reference, c.nom, c.prenom, c.email,
                    c.type_participation, c.nom_equipe
               FROM {$ts} s JOIN {$tc} c ON c.id = s.candidat_id
              WHERE s.statut = 'soumis' AND s.conformite <> ? AND s.id IN ({$ph})
              FOR UPDATE",
            array_merge([$verdict], $ids)
        )->result_array();

        if (!empty($cibles)) {
            $ids_cibles = array_column($cibles, 'id');
            $maintenant = date('Y-m-d H:i:s');
            $reinit     = $verdict === 'a_verifier';

            $this->db->where_in('id', $ids_cibles)->update(self::T_SOUMISSIONS, [
                'conformite'       => $verdict,
                'motif_conformite' => $verdict === 'non_conforme' ? $motif : NULL,
                'controle_par'     => $reinit ? NULL : ($admin_id ?: NULL),
                'controle_le'      => $reinit ? NULL : $maintenant,
            ]);

            $hist = [];
            foreach ($ids_cibles as $id) {
                $hist[] = [
                    'soumission_id' => $id,
                    'action'        => $verdict,
                    'detail'        => $motif ?: NULL,
                    'auteur_type'   => 'admin',
                    'auteur_id'     => $admin_id ?: NULL,
                    'ip'            => $ip,
                    'created_at'    => $maintenant,
                ];
            }
            $this->db->insert_batch(self::T_HISTORIQUE, $hist);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $cibles : FALSE;
    }

    /** Candidats validés sans soumission parmi les ids donnés (relance par e-mail) */
    public function candidats_a_relancer(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }
        $ts = $this->db->dbprefix(self::T_SOUMISSIONS);

        return $this->db
            ->select('c.id, c.reference, c.nom, c.prenom, c.email, c.type_participation, c.nom_equipe')
            ->from(self::T_CANDIDATS . ' c')
            ->where('c.statut', 'valide')
            ->where_in('c.id', $ids)
            ->where("NOT EXISTS (SELECT 1 FROM {$ts} sx WHERE sx.candidat_id = c.id)", NULL, FALSE)
            ->get()
            ->result_array();
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    private function _filtres_candidat(array $f, $avec_projet)
    {
        $q = trim($f['q'] ?? '');
        if ($q !== '') {
            $this->db->group_start()
                ->like('c.nom', $q)
                ->or_like('c.prenom', $q)
                ->or_like('c.email', $q)
                ->or_like('c.nom_equipe', $q)
                ->or_like('c.organisation', $q)
                ->or_like('c.reference', $q)
                ->or_like("CONCAT(c.prenom, ' ', c.nom)", $q);
            if ($avec_projet) {
                $this->db->or_like('s.titre', $q)->or_like('s.code_anonyme', $q);
            }
            $this->db->group_end();
        }

        if (in_array($f['type'] ?? '', ['individuel', 'equipe'], TRUE)) {
            $this->db->where('c.type_participation', $f['type']);
        }
        if (in_array($f['categorie'] ?? '', ['professionnel', 'etudiant'], TRUE)) {
            $this->db->where('c.categorie', $f['categorie']);
        }
    }

    /**
     * Résout un chemin relatif stocké en base vers un chemin absolu,
     * en refusant toute sortie du dossier de stockage (../, liens…).
     */
    private function _chemin_sur($relatif)
    {
        $base   = realpath($this->dossier_stockage());
        $absolu = realpath($this->dossier_stockage() . ltrim((string) $relatif, '/\\'));

        if ($base === FALSE || $absolu === FALSE || !is_file($absolu)) {
            return NULL;
        }
        return strpos($absolu, $base . DIRECTORY_SEPARATOR) === 0 ? $absolu : NULL;
    }
}
