<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Contrôleur de base de l'application
 */
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }
}

/**
 * Contrôleur de base des pages protégées du back-office.
 * Tout contrôleur qui en hérite exige une session valide.
 *
 *   class DashboardC extends Back_Controller { ... }
 */
class Back_Controller extends MY_Controller
{
    /** Rôles autorisés dans le back-office (surcharger dans un contrôleur si besoin) */
    protected $allowedRoles = ['admin', 'jury'];

    /** Déconnexion après inactivité si « Se souvenir de moi » n'est pas coché (secondes) */
    const IDLE_TIMEOUT = 7200; // 2 heures

    /** Utilisateur connecté (rechargé depuis la base à chaque requête) */
    protected $currentUser;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('BackOfficeM', 'backoffice');
        $this->requireLogin();
    }

    protected function requireLogin()
    {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Veuillez vous connecter pour accéder à cette page.');
            redirect('sign-in');
        }

        // Expiration pour inactivité (session non mémorisée)
        $last = (int) $this->session->userdata('last_activity');
        if (!$this->session->userdata('remember') && $last && (time() - $last) > self::IDLE_TIMEOUT) {
            $this->forceLogout('expired');
        }

        // Le compte peut avoir été suspendu ou son rôle modifié depuis la connexion
        $user = $this->backoffice->getUserById($this->session->userdata('user_id'));
        if (!$user || $user->statut !== 'actif' || !in_array($user->role_code, $this->allowedRoles, true)) {
            $this->forceLogout('revoked');
        }

        $this->currentUser = $user;
        $this->session->set_userdata('last_activity', time());

        // Variables disponibles dans toutes les vues du back-office (header, sidebar…)
        $this->load->helper('nav');
        $this->load->vars([
            'currentUser'  => $user,
            'isAdmin'      => $user->role_code === 'admin',
            'concours'     => $this->backoffice->getCurrentConcours(),
            'pendingCount' => $user->role_code === 'admin' ? $this->backoffice->countPendingAccounts() : 0,
        ]);
    }

    /** Restreindre une méthode à certains rôles : $this->requireRole('admin'); */
    protected function requireRole($roles)
    {
        if (!in_array($this->currentUser->role_code, (array) $roles, true)) {
            show_error('Vous n\'avez pas les droits nécessaires pour accéder à cette page.', 403, 'Accès refusé');
        }
    }

    /**
     * sess_destroy() supprime aussi les flashdata : le motif est donc passé
     * dans l'URL sous forme de code (traduit en message dans BackOfficeC::signIn).
     */
    protected function forceLogout($code)
    {
        $this->session->sess_destroy();
        redirect('sign-in?notice=' . $code);
    }
}
