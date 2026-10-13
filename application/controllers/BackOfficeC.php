<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentification du back-office (pages publiques : connexion,
 * mot de passe oublié, réinitialisation, déconnexion).
 */
class BackOfficeC extends MY_Controller
{
    /** Rôles autorisés à se connecter au back-office */
    const BACK_OFFICE_ROLES = ['admin', 'jury'];

    /** Durée de validité d'un lien de réinitialisation (minutes) */
    const RESET_TOKEN_TTL = 60;

    /** Messages affichés après une déconnexion forcée (voir Back_Controller::forceLogout) */
    const NOTICES = [
        'expired'    => 'Votre session a expiré après une période d\'inactivité. Veuillez vous reconnecter.',
        'revoked'    => 'Votre accès au back-office n\'est plus autorisé.',
        'logged_out' => 'Vous avez été déconnecté.',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('BackOfficeM', 'backoffice');
        $this->load->model('ConcoursM', 'concours');
        $this->load->vars(['concours_actif' => $this->concours->get_actif()]);
    }

    public function index()
    {
        redirect($this->isLoggedIn() ? 'dashboard' : 'sign-in');
    }

    /* =================================================================
       CONNEXION
       ================================================================= */

    public function signIn()
    {
        if ($this->isLoggedIn()) {
            redirect('dashboard');
        }

        $notice = $this->input->get('notice', true);

        $data = [
            'title'  => 'Connexion administrateur',
            'notice' => isset(self::NOTICES[$notice]) ? self::NOTICES[$notice] : null,
        ];

        $this->load->view('back/layouts/auth/header', $data);
        $this->load->view('back/components/auth/signin', $data);
        $this->load->view('back/layouts/auth/footer', $data);
    }

    public function authenticate()
    {
        if ($this->input->method() !== 'post') {
            redirect('sign-in');
        }

        $this->form_validation->set_rules('email', 'adresse e-mail', 'required|trim|valid_email|max_length[150]', [
            'required'    => 'Veuillez saisir votre %s.',
            'valid_email' => 'Veuillez saisir une %s valide.',
        ]);
        $this->form_validation->set_rules('password', 'mot de passe', 'required|max_length[255]', [
            'required' => 'Veuillez saisir votre %s.',
        ]);

        if ($this->form_validation->run() === false) {
            return $this->signIn();
        }

        $email    = strtolower($this->input->post('email', true));
        $password = (string) $this->input->post('password'); // pas de filtre XSS sur un mot de passe
        $genericError = 'Adresse e-mail ou mot de passe incorrect.';

        $user = $this->backoffice->getUserByEmail($email);

        // 1. Compte inexistant : on vérifie quand même un hash factice pour
        //    garder un temps de réponse identique (évite de deviner les e-mails)
        if (!$user) {
            password_verify($password, '$2y$12$8yz06xYZY7chTgkLBwX9OOaOPufmrjzrGLrvJGWjhZU0kpTRkagKS');
            $this->backoffice->logActivity(null, 'echec_connexion', 'E-mail inconnu : ' . $email);
            return $this->failLogin($genericError);
        }

        // 2. Compte temporairement bloqué après trop d'échecs
        if ($user->bloque_jusqua && strtotime($user->bloque_jusqua) > time()) {
            $minutes = (int) ceil((strtotime($user->bloque_jusqua) - time()) / 60);
            return $this->failLogin(
                'Trop de tentatives échouées. Réessayez dans ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . '.'
            );
        }

        // 3. Mot de passe incorrect
        if (!password_verify($password, $user->mot_de_passe)) {
            $this->backoffice->registerFailedLogin($user->id);
            $this->backoffice->logActivity($user->id, 'echec_connexion', 'Mot de passe incorrect');
            return $this->failLogin($genericError);
        }

        // À partir d'ici le mot de passe est correct : on peut donner un motif précis

        // 4. Rôle autorisé dans le back-office ?
        if (!in_array($user->role_code, self::BACK_OFFICE_ROLES, true)) {
            $this->backoffice->logActivity($user->id, 'acces_refuse', 'Rôle ' . $user->role_code . ' sur le back-office');
            return $this->failLogin('Cet espace est réservé à l\'équipe SATRACO et au jury. Utilisez l\'espace candidat.');
        }

        // 5. Statut du compte
        if ($user->statut === 'en_attente') {
            return $this->failLogin('Votre compte n\'a pas encore été validé par un administrateur.');
        }
        if ($user->statut === 'suspendu') {
            return $this->failLogin('Votre compte est suspendu. Contactez l\'équipe SATRACO.');
        }

        // 6. Re-hachage si l'algorithme par défaut de PHP a évolué
        if (password_needs_rehash($user->mot_de_passe, PASSWORD_DEFAULT)) {
            $this->backoffice->updatePasswordHash($user->id, password_hash($password, PASSWORD_DEFAULT));
        }

        // 7. Ouverture de la session (nouvel identifiant contre la fixation de session)
        $this->session->sess_regenerate(true);
        $this->session->set_userdata([
            'logged_in'     => true,
            'user_id'       => (int) $user->id,
            'user_nom'      => $user->nom,
            'user_prenom'   => $user->prenom,
            'user_email'    => $user->email,
            'user_role'     => $user->role_code,
            'remember'      => (bool) $this->input->post('remember'),
            'last_activity' => time(),
        ]);

        $this->backoffice->registerSuccessfulLogin($user->id);
        $this->backoffice->logActivity($user->id, 'connexion');

        redirect('dashboard');
    }

    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->backoffice->logActivity($this->session->userdata('user_id'), 'deconnexion');
        }

        $this->session->sess_destroy();
        redirect('sign-in?notice=logged_out');
    }

    /* =================================================================
       MOT DE PASSE OUBLIÉ
       ================================================================= */

    public function forgotPassword()
    {
        $data = ['title' => 'Mot de passe oublié'];

        $this->load->view('back/layouts/auth/header', $data);
        $this->load->view('back/components/auth/forgot_password', $data);
        $this->load->view('back/layouts/auth/footer', $data);
    }

    public function sendResetLink()
    {
        if ($this->input->method() !== 'post') {
            redirect('forgot-password');
        }

        $this->form_validation->set_rules('email', 'adresse e-mail', 'required|trim|valid_email|max_length[150]', [
            'required'    => 'Veuillez saisir votre %s.',
            'valid_email' => 'Veuillez saisir une %s valide.',
        ]);

        if ($this->form_validation->run() === false) {
            return $this->forgotPassword();
        }

        $email = strtolower($this->input->post('email', true));
        $user  = $this->backoffice->getUserByEmail($email);

        if ($user && $user->statut === 'actif' && in_array($user->role_code, self::BACK_OFFICE_ROLES, true)) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::RESET_TOKEN_TTL . ' minutes'));

            $this->backoffice->createResetToken($user->id, hash('sha256', $token), $expiresAt, $this->input->ip_address());
            $this->backoffice->logActivity($user->id, 'demande_reinitialisation');

            $link = site_url('reset-password/' . $token);

            if (!$this->sendResetEmail($user, $link)) {
                log_message('error', 'Échec d\'envoi du lien de réinitialisation à ' . $user->email);
            }

            // En développement uniquement : toujours afficher le lien à l'écran.
            // (Sur macOS, mail() renvoie souvent TRUE même si aucun e-mail ne part réellement.)
            if (ENVIRONMENT === 'development') {
                $this->session->set_flashdata('dev_link', $link);
            }
        }

        // Même message dans tous les cas (ne révèle pas si l'adresse existe)
        $this->session->set_flashdata(
            'success',
            'Si un compte correspond à cette adresse, un lien de réinitialisation vient de vous être envoyé. Il est valable ' . self::RESET_TOKEN_TTL . ' minutes.'
        );
        redirect('forgot-password');
    }

    /* =================================================================
       RÉINITIALISATION
       ================================================================= */

    public function resetPassword($token = null)
    {
        $reset = $this->findResetToken($token);

        if (!$reset) {
            $this->session->set_flashdata('error', 'Ce lien de réinitialisation est invalide, a expiré ou a déjà été utilisé.');
            redirect('forgot-password');
        }

        $data = [
            'title' => 'Nouveau mot de passe',
            'token' => $token,
        ];

        $this->load->view('back/layouts/auth/header', $data);
        $this->load->view('back/components/auth/reset_password', $data);
        $this->load->view('back/layouts/auth/footer', $data);
    }

    public function updatePassword()
    {
        if ($this->input->method() !== 'post') {
            redirect('sign-in');
        }

        $token = (string) $this->input->post('token', true);
        $reset = $this->findResetToken($token);

        if (!$reset) {
            $this->session->set_flashdata('error', 'Ce lien de réinitialisation est invalide, a expiré ou a déjà été utilisé.');
            redirect('forgot-password');
        }

        $this->form_validation->set_rules(
            'password',
            'nouveau mot de passe',
            'required|min_length[8]|max_length[72]|regex_match[/[A-Z]/]|regex_match[/[0-9]/]',
            [
                'required'    => 'Veuillez saisir un %s.',
                'min_length'  => 'Le %s doit contenir au moins 8 caractères.',
                'max_length'  => 'Le %s ne doit pas dépasser 72 caractères.',
                'regex_match' => 'Le %s doit contenir au moins une majuscule et un chiffre.',
            ]
        );
        $this->form_validation->set_rules('password_confirm', 'confirmation', 'required|matches[password]', [
            'required' => 'Veuillez confirmer le mot de passe.',
            'matches'  => 'Les deux mots de passe ne correspondent pas.',
        ]);

        if ($this->form_validation->run() === false) {
            return $this->resetPassword($token);
        }

        $hash = password_hash((string) $this->input->post('password'), PASSWORD_DEFAULT);

        if (!$this->backoffice->resetPassword($reset->token_id, $reset->utilisateur_id, $hash)) {
            $this->session->set_flashdata('error', 'Une erreur est survenue. Veuillez réessayer.');
            redirect('reset-password/' . $token);
        }

        $this->backoffice->logActivity($reset->utilisateur_id, 'reinitialisation_mot_de_passe');

        $this->session->set_flashdata('success', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');
        redirect('sign-in');
    }

    /* =================================================================
       OUTILS
       ================================================================= */

    private function isLoggedIn()
    {
        return (bool) $this->session->userdata('logged_in');
    }

    private function failLogin($message)
    {
        $this->session->set_flashdata('error', $message);
        $this->session->set_flashdata('old_email', strtolower((string) $this->input->post('email', true)));
        redirect('sign-in');
    }

    private function findResetToken($token)
    {
        if (!is_string($token) || strlen($token) !== 64 || !ctype_xdigit($token)) {
            return null;
        }

        return $this->backoffice->getValidResetToken(hash('sha256', $token));
    }

    private function sendResetEmail($user, $link)
    {
        $this->load->library('email');

        $body = "Bonjour " . $user->prenom . ",\n\n"
            . "Une demande de réinitialisation du mot de passe a été faite pour votre compte "
            . "sur la plateforme du concours d'architecture SATRACO.\n\n"
            . "Pour choisir un nouveau mot de passe, ouvrez ce lien (valable " . self::RESET_TOKEN_TTL . " minutes) :\n"
            . $link . "\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail : "
            . "votre mot de passe actuel reste inchangé.\n\n"
            . "L'équipe SATRACO Construction";

        $this->email->from($this->config->item('mail_from') ?: 'no-reply@satraco.bi', 'Concours SATRACO');
        $this->email->to($user->email);
        $this->email->subject('Réinitialisation de votre mot de passe');
        $this->email->message($body);

        return @$this->email->send(false);
    }
}
