<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * application/controllers/Compte.php
 * Connexion / inscription des participants (front-office)
 */
class Compte extends Public_Controller
{
    /**
     * Dossier où se trouvent header.php et footer.php du layout public.
     * ⚠ À remplacer par le chemin réel (voir explication).
     */
    const LAYOUT = 'front/layouts/';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'form']);
        $this->load->model('Participant_model', 'participants');
        $this->form_validation->set_error_delimiters('<span class="champ-erreur">', '</span>');
    }

    /* ---------- GET/POST /connexion ---------- */
    public function connexion()
    {
        if ($this->_est_connecte()) {
            redirect('mon-espace');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('email', 'E-mail', 'required|trim|valid_email');
            $this->form_validation->set_rules('mot_de_passe', 'Mot de passe', 'required');

            if ($this->form_validation->run()) {
                $p = $this->participants->verifier_identifiants(
                    $this->input->post('email', true),
                    $this->input->post('mot_de_passe')   // pas de xss_clean sur un mot de passe
                );

                if ($p && $p->statut === 'actif') {
                    $this->_ouvrir_session($p);
                    $retour = $this->session->userdata('retour_apres_connexion') ?: 'mon-espace';
                    $this->session->unset_userdata('retour_apres_connexion');
                    redirect($retour);
                }

                // Message volontairement générique (ne révèle pas si l'e-mail existe)
                $this->session->set_flashdata(
                    'erreur_connexion',
                    $p ? 'Votre compte est suspendu. Contactez l\'organisation.'
                        : 'E-mail ou mot de passe incorrect.'
                );
                redirect('connexion');
            }
        }

        $this->_afficher('connexion');
    }

    /* ---------- GET/POST /inscription ---------- */
    public function inscription()
    {
        if ($this->_est_connecte()) {
            redirect('mon-espace');
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('prenom', 'Prénom', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('nom', 'Nom', 'required|trim|max_length[100]');
            $this->form_validation->set_rules(
                'email',
                'E-mail',
                'required|trim|valid_email|max_length[190]|is_unique[participants.email]',
                ['is_unique' => 'Un compte existe déjà avec cet e-mail. Connectez-vous.']
            );
            $this->form_validation->set_rules('telephone', 'Téléphone', 'trim|max_length[30]');
            $this->form_validation->set_rules('universite', 'Université', 'required|trim|max_length[190]');
            $this->form_validation->set_rules('mot_de_passe', 'Mot de passe', 'required|min_length[8]');
            $this->form_validation->set_rules(
                'confirmation',
                'Confirmation',
                'required|matches[mot_de_passe]',
                ['matches' => 'Les deux mots de passe ne correspondent pas.']
            );
            $this->form_validation->set_rules(
                'cgu',
                'Règlement',
                'required',
                ['required' => 'Vous devez accepter le règlement du concours.']
            );

            if ($this->form_validation->run()) {
                $data = $this->input->post(['prenom', 'nom', 'email', 'telephone', 'universite'], true);
                $data['mot_de_passe'] = $this->input->post('mot_de_passe');

                $id = $this->participants->creer($data);
                $this->_ouvrir_session($this->participants->trouver($id));
                $this->session->set_flashdata('succes', 'Bienvenue ! Votre compte a bien été créé.');
                redirect('mon-espace');
            }
        }

        $this->_afficher('inscription');
    }

    /* ---------- /deconnexion ---------- */
    public function deconnexion()
    {
        $this->session->unset_userdata(['participant_id', 'participant_nom']);
        $this->session->sess_regenerate(true);
        redirect('connexion');
    }

    /* ---------- /mon-espace (page protégée, à enrichir) ---------- */
    public function espace()
    {
        if (!$this->_est_connecte()) {
            $this->session->set_userdata('retour_apres_connexion', 'mon-espace');
            redirect('connexion');
        }
        $this->_vue('front/compte/espace', [
            'title'       => 'Mon espace',
            'participant' => $this->participants->trouver($this->session->userdata('participant_id')),
        ]);
    }

    /* ================= Privé ================= */

    private function _afficher($onglet)
    {
        $this->_vue('front/compte/acces', [
            'title'  => $onglet === 'inscription' ? 'Créer un compte' : 'Se connecter',
            'onglet' => $onglet, // 'connexion' ou 'inscription'
        ]);
    }

    /**
     * Rendu avec le layout public.
     * $concours, $phase, $inscriptionsOuvertes viennent de Public_Controller.
     * Si ton contrôleur d'accueil utilise déjà une méthode de rendu
     * (ex. $this->render(...)), remplace le corps par cet appel.
     */
    private function _vue($vue, array $data = [])
    {
        $this->load->view(self::LAYOUT . 'header', $data);
        $this->load->view($vue, $data);
        $this->load->view(self::LAYOUT . 'footer', $data);
    }

    private function _ouvrir_session($p)
    {
        $this->session->sess_regenerate(true); // anti fixation de session
        $this->session->set_userdata([
            'participant_id'  => (int) $p->id,
            'participant_nom' => $p->prenom . ' ' . $p->nom,
        ]);
        $this->participants->marquer_connexion($p->id);
    }

    private function _est_connecte()
    {
        return (bool) $this->session->userdata('participant_id');
    }
}
