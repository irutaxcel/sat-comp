<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Administrateurs & jury — s'appuie sur les tables existantes :
 *   utilisateurs (role_id, statut en_attente|actif|suspendu, tentatives_echouees, bloque_jusqua…)
 *   roles        (code : admin | jury | candidat)
 *   journal_activites, password_resets
 */
class AdminM extends CI_Model
{
    const T_USERS   = 'utilisateurs';
    const T_ROLES   = 'roles';
    const T_JOURNAL = 'journal_activites';
    const T_RESETS  = 'password_resets';

    /** Rôles gérés par cette page (les candidats ont leurs propres écrans) */
    const ROLES_GERES = ['admin', 'jury'];

    const COLONNES = 'u.id, u.nom, u.prenom, u.email, u.telephone, u.organisation, u.statut,
                      u.email_verifie_le, u.valide_le, u.derniere_connexion, u.tentatives_echouees,
                      u.bloque_jusqua, u.created_at, u.updated_at, r.code AS role, r.libelle AS role_libelle';

    /* ------------------------------------------------------------------ */
    /*  Rôles                                                             */
    /* ------------------------------------------------------------------ */

    /** ['admin' => ['id' => 1, 'libelle' => '…'], 'jury' => [...]] */
    public function roles()
    {
        $rows = $this->db->select('id, code, libelle')
            ->where_in('code', self::ROLES_GERES)
            ->order_by('id', 'ASC')
            ->get(self::T_ROLES)->result_array();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['code']] = ['id' => (int) $r['id'], 'libelle' => $r['libelle']];
        }
        return $out;
    }

    /* ------------------------------------------------------------------ */
    /*  Lecture                                                           */
    /* ------------------------------------------------------------------ */

    private function _base()
    {
        $this->db->select(self::COLONNES)
            ->from(self::T_USERS . ' u')
            ->join(self::T_ROLES . ' r', 'r.id = u.role_id')
            ->where_in('r.code', self::ROLES_GERES);
    }

    public function lister(array $f = [])
    {
        $this->_base();

        if (in_array($f['role'] ?? '', self::ROLES_GERES, true)) {
            $this->db->where('r.code', $f['role']);
        }
        if (in_array($f['statut'] ?? '', ['en_attente', 'actif', 'suspendu'], true)) {
            $this->db->where('u.statut', $f['statut']);
        }
        if (!empty($f['q'])) {
            $this->db->group_start()
                ->like('u.nom', $f['q'])
                ->or_like('u.prenom', $f['q'])
                ->or_like('u.email', $f['q'])
                ->or_like('u.telephone', $f['q'])
                ->or_like('u.organisation', $f['q'])
                ->group_end();
        }

        return $this->db->order_by('r.id', 'ASC')
            ->order_by('u.nom', 'ASC')
            ->order_by('u.prenom', 'ASC')
            ->get()->result_array();
    }

    /** Uniquement un admin ou un membre du jury (un candidat n'est pas gérable ici). */
    public function trouver($id)
    {
        $this->_base();
        return $this->db->where('u.id', (int) $id)->get()->row_array();
    }

    /** Vérifie l'unicité sur toute la table (candidats compris). */
    public function email_existe($email, $exclure_id = 0)
    {
        $this->db->where('email', $email);
        if ($exclure_id) {
            $this->db->where('id !=', (int) $exclure_id);
        }
        return $this->db->count_all_results(self::T_USERS) > 0;
    }

    public function compter_admins_actifs($exclure_id = 0)
    {
        $this->db->from(self::T_USERS . ' u')
            ->join(self::T_ROLES . ' r', 'r.id = u.role_id')
            ->where('r.code', 'admin')
            ->where('u.statut', 'actif');
        if ($exclure_id) {
            $this->db->where('u.id !=', (int) $exclure_id);
        }
        return $this->db->count_all_results();
    }

    public function stats()
    {
        $sql = "SELECT COUNT(*)                                     AS total,
                       COALESCE(SUM(r.code = 'admin'), 0)           AS admins,
                       COALESCE(SUM(r.code = 'jury'), 0)            AS jury,
                       COALESCE(SUM(u.statut = 'actif'), 0)         AS actifs,
                       COALESCE(SUM(u.statut <> 'actif'), 0)        AS inactifs
                FROM " . self::T_USERS . " u
                JOIN " . self::T_ROLES . " r ON r.id = u.role_id
                WHERE r.code IN ('admin','jury')";
        return array_map('intval', $this->db->query($sql)->row_array());
    }

    /**
     * Données rattachées à l'utilisateur et qui empêchent une suppression définitive
     * (on propose alors de suspendre le compte).
     */
    public function dependances($id)
    {
        $id = (int) $id;
        $controles = [
            'evaluation_notes'       => ['jury_id',        'note(s) attribuée(s)'],
            'evaluations'            => ['jury_id',        'évaluation(s)'],
            'candidatures'           => ['utilisateur_id', 'candidature(s)'],
            'historique_soumissions' => ['utilisateur_id', 'action(s) sur des soumissions'],
        ];
        $trouve = [];
        foreach ($controles as $table => [$col, $libelle]) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            $n = $this->db->where($col, $id)->count_all_results($table);
            if ($n > 0) {
                $trouve[] = $n . ' ' . $libelle;
            }
        }
        return $trouve;
    }

    /* ------------------------------------------------------------------ */
    /*  Écriture                                                          */
    /* ------------------------------------------------------------------ */

    public function creer(array $data)
    {
        $this->db->insert(self::T_USERS, $data);
        return (int) $this->db->insert_id();
    }

    public function modifier($id, array $data)
    {
        return $this->db->where('id', (int) $id)->update(self::T_USERS, $data);
    }

    /** password_resets est en ON DELETE CASCADE, journal_activites en SET NULL. */
    public function supprimer($id)
    {
        $this->db->where('id', (int) $id)->delete(self::T_USERS);
        return $this->db->affected_rows() > 0;
    }

    /** Invalide les liens « mot de passe oublié » encore valides. */
    public function invalider_liens_reset($id)
    {
        $this->db->set('used_at', date('Y-m-d H:i:s'))
            ->where('utilisateur_id', (int) $id)
            ->where('used_at IS NULL', null, false)
            ->update(self::T_RESETS);
    }

    /* ------------------------------------------------------------------ */
    /*  Journal                                                           */
    /* ------------------------------------------------------------------ */

    public function journaliser($utilisateur_id, $action, $cible_type = null, $cible_id = null, $details = null)
    {
        if (is_array($details)) {
            $details = json_encode($details, JSON_UNESCAPED_UNICODE);
        }
        $this->db->insert(self::T_JOURNAL, [
            'utilisateur_id' => $utilisateur_id ?: null,
            'action'         => substr($action, 0, 50),
            'cible_type'     => $cible_type,
            'cible_id'       => $cible_id,
            'details'        => $details !== null ? mb_substr($details, 0, 255) : null,
            'ip'             => $this->input->ip_address(),
            'user_agent'     => substr((string) $this->input->user_agent(), 0, 255),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Journal — consultation                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Filtres acceptés : date_debut, date_fin (Y-m-d), utilisateur_id, action, categorie (liste d'actions), q
     */
    private function _journal_requete(array $f, $avec_select = true)
    {
        if ($avec_select) {
            $this->db->select("j.id, j.utilisateur_id, j.action, j.cible_type, j.cible_id, j.details,
                               j.ip, j.user_agent, j.created_at,
                               u.nom, u.prenom, u.email, r.code AS role, r.libelle AS role_libelle,
                               CONCAT(c.prenom, ' ', c.nom) AS cible_nom", false);
        }
        $this->db->from(self::T_JOURNAL . ' j')
            ->join(self::T_USERS . ' u', 'u.id = j.utilisateur_id', 'left')
            ->join(self::T_ROLES . ' r', 'r.id = u.role_id', 'left')
            ->join(self::T_USERS . ' c', "c.id = j.cible_id AND j.cible_type = 'utilisateur'", 'left', false);

        if (!empty($f['date_debut'])) {
            $this->db->where('j.created_at >=', $f['date_debut'] . ' 00:00:00');
        }
        if (!empty($f['date_fin'])) {
            $this->db->where('j.created_at <=', $f['date_fin'] . ' 23:59:59');
        }
        if (!empty($f['utilisateur_id'])) {
            $this->db->where('j.utilisateur_id', (int) $f['utilisateur_id']);
        }
        if (!empty($f['action'])) {
            $this->db->where('j.action', $f['action']);
        } elseif (!empty($f['actions'])) {
            $this->db->where_in('j.action', $f['actions']);
        }
        if (!empty($f['q'])) {
            $this->db->group_start()
                ->like('j.details', $f['q'])
                ->or_like('j.ip', $f['q'])
                ->or_like('j.action', $f['q'])
                ->or_like('u.email', $f['q'])
                ->or_like('u.nom', $f['q'])
                ->or_like('u.prenom', $f['q'])
                ->or_like('c.email', $f['q'])
                ->group_end();
        }
    }

    public function journal_lister(array $f, $limite = 25, $offset = 0)
    {
        $this->_journal_requete($f);
        return $this->db->order_by('j.created_at', 'DESC')
            ->order_by('j.id', 'DESC')
            ->limit((int) $limite, (int) $offset)
            ->get()->result_array();
    }

    public function journal_compter(array $f)
    {
        $this->_journal_requete($f, false);
        return (int) $this->db->count_all_results();
    }

    /** Actions présentes dans le journal (pour le filtre), avec leur nombre d'occurrences */
    public function journal_actions()
    {
        return $this->db->select('action, COUNT(*) AS nb', false)
            ->group_by('action')
            ->order_by('action', 'ASC')
            ->get(self::T_JOURNAL)->result_array();
    }

    /** Utilisateurs ayant au moins une entrée dans le journal */
    public function journal_utilisateurs()
    {
        return $this->db->select('u.id, u.nom, u.prenom, u.email, r.code AS role')
            ->from(self::T_USERS . ' u')
            ->join(self::T_ROLES . ' r', 'r.id = u.role_id')
            ->where('u.id IN (SELECT DISTINCT utilisateur_id FROM ' . self::T_JOURNAL
                . ' WHERE utilisateur_id IS NOT NULL)', null, false)
            ->order_by('r.id', 'ASC')
            ->order_by('u.nom', 'ASC')
            ->get()->result_array();
    }

    public function journal_stats()
    {
        $sql = "SELECT COUNT(*) AS total,
                       COALESCE(SUM(created_at >= CURDATE()), 0) AS evenements_jour,
                       COALESCE(SUM(action = 'connexion' AND created_at >= CURDATE()), 0) AS connexions_jour,
                       COALESCE(SUM(action = 'echec_connexion' AND created_at >= NOW() - INTERVAL 1 DAY), 0) AS echecs_24h,
                       COUNT(DISTINCT CASE WHEN created_at >= CURDATE() THEN utilisateur_id END) AS utilisateurs_jour
                FROM " . self::T_JOURNAL;
        return array_map('intval', $this->db->query($sql)->row_array());
    }

    /* ------------------------------------------------------------------ */
    /*  Profil                                                            */
    /* ------------------------------------------------------------------ */

    /** Hash du mot de passe (jamais exposé par trouver() / lister()). */
    public function hash_mot_de_passe($id)
    {
        $r = $this->db->select('mot_de_passe')->where('id', (int) $id)->get(self::T_USERS)->row_array();
        return $r ? (string) $r['mot_de_passe'] : '';
    }
}
