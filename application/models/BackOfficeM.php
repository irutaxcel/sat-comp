<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modèle d'authentification du back-office
 * Tables : utilisateurs, roles, password_resets, journal_activites
 */
class BackOfficeM extends CI_Model
{
    /** Nombre d'échecs avant blocage temporaire */
    const MAX_TENTATIVES = 5;

    /** Durée du blocage (minutes) */
    const DUREE_BLOCAGE = 15;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /* =================================================================
       UTILISATEURS
       ================================================================= */

    public function getUserByEmail($email)
    {
        return $this->db
            ->select('u.*, r.code AS role_code, r.libelle AS role_libelle')
            ->from('utilisateurs u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.email', $email)
            ->limit(1)
            ->get()
            ->row();
    }

    public function getUserById($id)
    {
        return $this->db
            ->select('u.*, r.code AS role_code, r.libelle AS role_libelle')
            ->from('utilisateurs u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.id', (int) $id)
            ->limit(1)
            ->get()
            ->row();
    }

    /**
     * Échec de connexion : incrémente le compteur et bloque le compte
     * temporairement après MAX_TENTATIVES échecs.
     */
    public function registerFailedLogin($userId)
    {
        $user = $this->db->select('tentatives_echouees')
            ->where('id', (int) $userId)
            ->get('utilisateurs')
            ->row();

        if (!$user) {
            return;
        }

        $tentatives = (int) $user->tentatives_echouees + 1;
        $data = ['tentatives_echouees' => $tentatives];

        if ($tentatives >= self::MAX_TENTATIVES) {
            $data['tentatives_echouees'] = 0;
            $data['bloque_jusqua'] = date('Y-m-d H:i:s', strtotime('+' . self::DUREE_BLOCAGE . ' minutes'));
        }

        $this->db->where('id', (int) $userId)->update('utilisateurs', $data);
    }

    /** Connexion réussie : remise à zéro des compteurs */
    public function registerSuccessfulLogin($userId)
    {
        $this->db->where('id', (int) $userId)->update('utilisateurs', [
            'tentatives_echouees' => 0,
            'bloque_jusqua'       => null,
            'derniere_connexion'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** Re-hachage transparent si l'algorithme ou le coût a changé */
    public function updatePasswordHash($userId, $hash)
    {
        $this->db->where('id', (int) $userId)->update('utilisateurs', ['mot_de_passe' => $hash]);
    }

    /* =================================================================
       RÉINITIALISATION DU MOT DE PASSE
       ================================================================= */

    /** Enregistre un nouveau jeton et invalide les précédents non utilisés */
    public function createResetToken($userId, $tokenHash, $expiresAt, $ip)
    {
        $this->db->where('utilisateur_id', (int) $userId)
            ->where('used_at IS NULL', null, false)
            ->update('password_resets', ['used_at' => date('Y-m-d H:i:s')]);

        return $this->db->insert('password_resets', [
            'utilisateur_id' => (int) $userId,
            'token_hash'     => $tokenHash,
            'expires_at'     => $expiresAt,
            'ip'             => $ip,
        ]);
    }

    /** Jeton existant, non expiré, non utilisé, compte toujours actif */
    public function getValidResetToken($tokenHash)
    {
        return $this->db
            ->select('pr.id AS token_id, pr.utilisateur_id, u.email, u.prenom, u.nom')
            ->from('password_resets pr')
            ->join('utilisateurs u', 'u.id = pr.utilisateur_id')
            ->where('pr.token_hash', $tokenHash)
            ->where('pr.used_at IS NULL', null, false)
            ->where('pr.expires_at >', date('Y-m-d H:i:s'))
            ->where('u.statut', 'actif')
            ->limit(1)
            ->get()
            ->row();
    }

    /** Change le mot de passe et consomme le jeton (transaction) */
    public function resetPassword($tokenId, $userId, $passwordHash)
    {
        $this->db->trans_start();

        $this->db->where('id', (int) $userId)->update('utilisateurs', [
            'mot_de_passe'        => $passwordHash,
            'tentatives_echouees' => 0,
            'bloque_jusqua'       => null,
        ]);

        $this->db->where('id', (int) $tokenId)
            ->update('password_resets', ['used_at' => date('Y-m-d H:i:s')]);

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /* =================================================================
       DONNÉES DU LAYOUT (barre du haut / menu)
       ================================================================= */

    /** Concours en cours : le dernier publié, sinon le dernier brouillon */
    public function getCurrentConcours()
    {
        return $this->db
            ->select('id, titre, statut, cloture_inscriptions, date_limite_soumission')
            ->where_in('statut', ['publie', 'brouillon'])
            ->order_by("FIELD(statut, 'publie', 'brouillon')", '', false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('concours')
            ->row();
    }

    /** Nombre de comptes candidats en attente de validation */
    public function countPendingAccounts()
    {
        return (int) $this->db
            ->from('utilisateurs u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.code', 'candidat')
            ->where('u.statut', 'en_attente')
            ->count_all_results();
    }

    /* =================================================================
       JOURNAL
       ================================================================= */

    public function logActivity($userId, $action, $details = null)
    {
        $this->db->insert('journal_activites', [
            'utilisateur_id' => $userId ? (int) $userId : null,
            'action'         => $action,
            'details'        => $details ? mb_substr($details, 0, 255) : null,
            'ip'             => $this->input->ip_address(),
            'user_agent'     => mb_substr((string) $this->input->user_agent(), 0, 255),
        ]);
    }
}
