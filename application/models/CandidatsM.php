<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Candidatures — accès aux données des candidats.
 *
 * Tables : candidats, candidat_membres, candidat_decisions
 *
 * Cycle de vie d'un compte :
 *
 *    en_attente ──valider──▶ valide ──suspendre──▶ suspendu
 *        │  ▲                  ▲                      │
 *  rejeter│  │rouvrir          └──────réactiver───────┘
 *        ▼  │
 *      rejete
 */
class CandidatsM extends CI_Model
{
    const T_CANDIDATS = 'candidats';
    const T_MEMBRES   = 'candidat_membres';
    const T_DECISIONS = 'candidat_decisions';

    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_VALIDE     = 'valide';
    const STATUT_REJETE     = 'rejete';
    const STATUT_SUSPENDU   = 'suspendu';

    const STATUTS = ['en_attente', 'valide', 'rejete', 'suspendu'];

    /**
     * Transitions autorisées.
     * action => [statut de départ, statut d'arrivée, décision journalisée, motif obligatoire]
     */
    const TRANSITIONS = [
        'valider'   => ['en_attente', 'valide',     'valide',   FALSE],
        'rejeter'   => ['en_attente', 'rejete',     'rejete',   TRUE],
        'suspendre' => ['valide',     'suspendu',   'suspendu', TRUE],
        'reactiver' => ['suspendu',   'valide',     'valide',   FALSE],
        'rouvrir'   => ['rejete',     'en_attente', 'reouvert', FALSE],
    ];

    /** Colonnes autorisées pour le tri (liste blanche → pas d'injection via ORDER BY) */
    private $tris = [
        'recent'       => ['c.created_at', 'DESC'],
        'ancien'       => ['c.created_at', 'ASC'],
        'nom'          => ['c.nom', 'ASC'],
        'organisation' => ['c.organisation', 'ASC'],
        'decision'     => ['c.decide_le', 'DESC'],
    ];

    /* ================================================================
     *  LISTES
     * ================================================================ */

    /**
     * Liste paginée de tous les candidats (page « Candidats & équipes »).
     *
     * @param array $f  Filtres : statut, q, type, categorie, email, anciennete, tri
     * @return array    ['rows' => [...], 'total' => int]
     */
    public function liste(array $f, $limit = 15, $offset = 0, $tri_defaut = 'recent')
    {
        $this->db->from(self::T_CANDIDATS . ' c');

        if (in_array($f['statut'] ?? '', self::STATUTS, TRUE)) {
            $this->db->where('c.statut', $f['statut']);
        }
        $this->_appliquer_filtres($f);

        // Comptage sans réinitialiser la requête
        $total = (int) $this->db->count_all_results('', FALSE);

        $cle = isset($this->tris[$f['tri'] ?? '']) ? $f['tri'] : $tri_defaut;
        $tri = $this->tris[$cle];

        $rows = $this->db
            ->select('c.id, c.reference, c.type_participation, c.nom_equipe, c.categorie,
                      c.nom, c.prenom, c.email, c.telephone, c.organisation, c.numero_ordre,
                      c.email_verifie_le, c.statut, c.decide_le, c.created_at')
            ->select('(SELECT COUNT(*) FROM ' . $this->db->dbprefix(self::T_MEMBRES) . ' m WHERE m.candidat_id = c.id) AS nb_membres', FALSE)
            ->order_by($tri[0], $tri[1])
            ->order_by('c.id', 'DESC')
            ->limit((int) $limit, (int) $offset)
            ->get()
            ->result_array();

        return ['rows' => $rows, 'total' => $total];
    }

    /** Liste des comptes en attente (page « Comptes à valider ») */
    public function liste_en_attente(array $f, $limit = 15, $offset = 0)
    {
        $f['statut'] = self::STATUT_EN_ATTENTE;
        return $this->liste($f, $limit, $offset, 'ancien');
    }

    /* ================================================================
     *  STATISTIQUES
     * ================================================================ */

    /** Indicateurs de la page « Comptes à valider » */
    public function stats_en_attente()
    {
        $t = $this->db->dbprefix(self::T_CANDIDATS);

        $row = $this->db->query(
            "SELECT
                COUNT(*)                                     AS total,
                SUM(created_at >= CURDATE())                 AS aujourdhui,
                SUM(email_verifie_le IS NULL)                AS email_non_verifie,
                SUM(created_at < (NOW() - INTERVAL 48 HOUR)) AS plus_48h
             FROM {$t}
             WHERE statut = ?",
            [self::STATUT_EN_ATTENTE]
        )->row_array();

        return array_map('intval', $row ?: []);
    }

    /** Indicateurs de la page « Candidats & équipes » */
    public function stats_globales()
    {
        $t = $this->db->dbprefix(self::T_CANDIDATS);
        $m = $this->db->dbprefix(self::T_MEMBRES);

        $row = $this->db->query(
            "SELECT
                COUNT(*)                                                     AS total,
                SUM(statut = 'valide')                                       AS valide,
                SUM(statut = 'en_attente')                                   AS en_attente,
                SUM(statut = 'rejete')                                       AS rejete,
                SUM(statut = 'suspendu')                                     AS suspendu,
                SUM(statut = 'valide' AND type_participation = 'equipe')     AS equipes_validees,
                SUM(statut = 'valide' AND type_participation = 'individuel') AS individuels_valides,
                SUM(statut = 'valide' AND categorie = 'etudiant')            AS etudiants_valides,
                (SELECT COUNT(*) FROM {$m} mb JOIN {$t} cc ON cc.id = mb.candidat_id
                  WHERE cc.statut = 'valide')                                AS membres_valides
             FROM {$t}"
        )->row_array();

        $s = array_map('intval', $row ?: []);
        // Personnes physiques engagées dans des candidatures validées (responsables + membres)
        $s['participants'] = ($s['valide'] ?? 0) + ($s['membres_valides'] ?? 0);

        return $s;
    }

    /** Utilisé aussi pour le badge du menu latéral */
    public function compter_en_attente()
    {
        return (int) $this->db->where('statut', self::STATUT_EN_ATTENTE)
            ->count_all_results(self::T_CANDIDATS);
    }

    /* ================================================================
     *  DÉTAIL
     * ================================================================ */

    public function detail($id)
    {
        $candidat = $this->db
            ->select('id, reference, type_participation, nom_equipe, categorie, nom, prenom, email,
                      telephone, organisation, numero_ordre, email_verifie_le, statut,
                      motif_rejet, decide_le, created_at')
            ->where('id', (int) $id)
            ->get(self::T_CANDIDATS)
            ->row_array();

        if (!$candidat) {
            return NULL;
        }

        $candidat['membres'] = $this->db
            ->select('nom, prenom, email, telephone, role')
            ->where('candidat_id', (int) $id)
            ->order_by('id', 'ASC')
            ->get(self::T_MEMBRES)
            ->result_array();

        $candidat['historique'] = $this->db
            ->select('decision, motif, created_at')
            ->where('candidat_id', (int) $id)
            ->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC')
            ->get(self::T_DECISIONS)
            ->result_array();

        // Doublons potentiels : même téléphone ou même n° d'ordre sur un autre compte
        $candidat['doublons'] = $this->_doublons_potentiels($candidat);

        return $candidat;
    }

    /* ================================================================
     *  CHANGEMENTS DE STATUT — unitaires ou en masse
     * ================================================================ */

    /**
     * Applique une transition (voir TRANSITIONS) aux candidats qui sont
     * encore dans le statut de départ. Les autres sont ignorés.
     *
     * @return array|false  Candidats effectivement traités (pour notification), FALSE si erreur
     */
    public function appliquer_transition(array $ids, $action, $motif, $admin_id, $ip)
    {
        if (!isset(self::TRANSITIONS[$action])) {
            return [];
        }
        list($de, $vers, $decision) = self::TRANSITIONS[$action];

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $this->db->trans_start();

        // Verrouille les lignes ciblées le temps de la transaction (évite la double décision)
        $t = $this->db->dbprefix(self::T_CANDIDATS);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $cibles = $this->db->query(
            "SELECT id, reference, nom, prenom, email, type_participation, nom_equipe
               FROM {$t}
              WHERE statut = ? AND id IN ({$placeholders})
              FOR UPDATE",
            array_merge([$de], $ids)
        )->result_array();

        if (!empty($cibles)) {
            $ids_cibles = array_column($cibles, 'id');
            $maintenant = date('Y-m-d H:i:s');
            $reouvert   = $vers === self::STATUT_EN_ATTENTE;

            $this->db->where_in('id', $ids_cibles)
                ->where('statut', $de)
                ->update(self::T_CANDIDATS, [
                    'statut'      => $vers,
                    'motif_rejet' => $vers === self::STATUT_REJETE ? $motif : NULL,
                    // Un dossier rouvert redevient « non décidé »
                    'decide_par'  => $reouvert ? NULL : ($admin_id ?: NULL),
                    'decide_le'   => $reouvert ? NULL : $maintenant,
                ]);

            $historique = [];
            foreach ($ids_cibles as $id) {
                $historique[] = [
                    'candidat_id' => $id,
                    'decision'    => $decision,
                    'motif'       => $motif ?: NULL,
                    'admin_id'    => $admin_id ?: NULL,
                    'ip'          => $ip,
                    'created_at'  => $maintenant,
                ];
            }
            $this->db->insert_batch(self::T_DECISIONS, $historique);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() ? $cibles : FALSE;
    }

    /** Raccourci conservé pour « Comptes à valider » (valide / rejete) */
    public function decider(array $ids, $decision, $motif, $admin_id, $ip)
    {
        $action = $decision === self::STATUT_VALIDE ? 'valider'
            : ($decision === self::STATUT_REJETE ? 'rejeter' : NULL);

        return $action ? $this->appliquer_transition($ids, $action, $motif, $admin_id, $ip) : [];
    }

    /* ================================================================
     *  PRIVÉ
     * ================================================================ */

    private function _appliquer_filtres(array $f)
    {
        $q = trim($f['q'] ?? '');
        if ($q !== '') {
            // Motif LIKE échappé (caractère d'échappement « ! », comme le Query Builder de CI3)
            $motif_like = $this->db->escape('%' . $this->db->escape_like_str($q) . '%') . " ESCAPE '!'";

            $this->db->group_start()
                ->like('c.nom', $q)
                ->or_like('c.prenom', $q)
                ->or_like('c.email', $q)
                ->or_like('c.organisation', $q)
                ->or_like('c.nom_equipe', $q)
                ->or_like('c.reference', $q)
                ->or_like("CONCAT(c.prenom, ' ', c.nom)", $q)
                // Recherche aussi parmi les membres d'équipe
                ->or_where('EXISTS (SELECT 1 FROM ' . $this->db->dbprefix(self::T_MEMBRES) . ' mq
                                         WHERE mq.candidat_id = c.id
                                           AND (mq.nom LIKE ' . $motif_like . '
                                                OR mq.prenom LIKE ' . $motif_like . '
                                                OR mq.email LIKE ' . $motif_like . '))', NULL, FALSE)
                ->group_end();
        }

        if (in_array($f['type'] ?? '', ['individuel', 'equipe'], TRUE)) {
            $this->db->where('c.type_participation', $f['type']);
        }

        if (in_array($f['categorie'] ?? '', ['professionnel', 'etudiant'], TRUE)) {
            $this->db->where('c.categorie', $f['categorie']);
        }

        if (($f['email'] ?? '') === 'verifie') {
            $this->db->where('c.email_verifie_le IS NOT NULL', NULL, FALSE);
        } elseif (($f['email'] ?? '') === 'non_verifie') {
            $this->db->where('c.email_verifie_le IS NULL', NULL, FALSE);
        }

        if (($f['anciennete'] ?? '') === 'aujourdhui') {
            $this->db->where('c.created_at >= CURDATE()', NULL, FALSE);
        } elseif (($f['anciennete'] ?? '') === 'plus_48h') {
            $this->db->where('c.created_at < (NOW() - INTERVAL 48 HOUR)', NULL, FALSE);
        }
    }

    private function _doublons_potentiels(array $c)
    {
        $criteres = array_filter([
            'telephone'    => $c['telephone'],
            'numero_ordre' => $c['numero_ordre'],
        ]);
        if (empty($criteres)) {
            return [];
        }

        $this->db->select('id, reference, nom, prenom, statut')
            ->from(self::T_CANDIDATS)
            ->where('id !=', (int) $c['id'])
            ->group_start();
        $premier = TRUE;
        foreach ($criteres as $col => $val) {
            $premier ? $this->db->where($col, $val) : $this->db->or_where($col, $val);
            $premier = FALSE;
        }
        $this->db->group_end()->limit(5);

        return $this->db->get()->result_array();
    }
}
