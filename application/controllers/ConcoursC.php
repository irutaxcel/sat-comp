<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ConcoursC extends Back_Controller
{
    /** Fichiers téléversables sur la page Paramètres. */
    private $fichiers = [
        'logo'          => ['label' => 'Logo',      'types' => 'jpg|jpeg|png|svg', 'max_ko' => 2048],
        'banniere'      => ['label' => 'Bannière',  'types' => 'jpg|jpeg|png',     'max_ko' => 5120],
        'reglement_pdf' => ['label' => 'Règlement', 'types' => 'pdf',              'max_ko' => 20480],
        'guide_pdf'     => ['label' => 'Guide de participation', 'types' => 'pdf', 'max_ko' => 20480],
    ];

    private $dossier_upload = 'uploads/concours/';
    private $erreur_upload  = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ConcoursM', 'concours');
        $this->load->helper(['form', 'url']);
        $this->load->library('form_validation');
    }

    public function index()
    {
        redirect('concours-parametres');
    }

    /* =================================================================
     *  PARAMÈTRES DU CONCOURS
     * ================================================================= */

    public function parametres()
    {
        $concours = $this->concours->get_actif();

        $data = [
            'title'        => 'Paramètres du concours',
            'concours'     => $concours,
            'completude'   => $this->concours->completude($concours),
            'publics'      => ConcoursM::PUBLICS,
            'modes'        => ConcoursM::MODES,
            'validations'  => ConcoursM::VALIDATIONS,
            'statuts'      => ConcoursM::STATUTS,
            'extensions'   => ConcoursM::EXTENSIONS,
            'limite_php'   => $this->_limite_upload_php_mo(),
            'erreur_upload' => $this->erreur_upload,
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/concours/parametres', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    public function enregistrer_parametres()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-parametres');
        }

        $concours = $this->concours->get_actif();

        $this->_regles_validation();
        if (!$this->form_validation->run()) {
            return $this->parametres(); // ré-affiche le formulaire avec les erreurs
        }

        $data = $this->_donnees_formulaire();

        // Téléversements (logo, bannière, règlement, guide)
        $anciens  = []; // fichiers remplacés, à supprimer après succès
        $nouveaux = []; // fichiers reçus dans cette requête, à nettoyer en cas d'échec
        foreach ($this->fichiers as $champ => $cfg) {
            $ancien = $concours->$champ ?? null;

            if (!empty($_FILES[$champ]['name'])) {
                $chemin = $this->_televerser($champ, $cfg);
                if ($chemin === false) {
                    array_map([$this, '_supprimer_fichier'], $nouveaux);
                    return $this->parametres();
                }
                $data[$champ] = $chemin;
                $nouveaux[]   = $chemin;
                if ($ancien) $anciens[] = $ancien;
            } elseif ($this->input->post('supprimer_' . $champ) && $ancien) {
                $data[$champ] = null;
                $anciens[] = $ancien;
            }
        }

        // Règle métier : publication possible seulement si le dossier est complet.
        // On n'interrompt PAS l'enregistrement (le navigateur ne renverrait pas les
        // fichiers) : on enregistre en brouillon et on signale ce qui manque.
        $avertissement = '';
        if ($data['statut'] === 'publie') {
            $manquants = [];
            if (empty($data['description'])) {
                $manquants[] = 'la description';
            }
            $reglement = array_key_exists('reglement_pdf', $data) ? $data['reglement_pdf'] : ($concours->reglement_pdf ?? null);
            if (empty($reglement)) {
                $manquants[] = 'le règlement (PDF)';
            }
            if ($manquants) {
                $data['statut'] = 'brouillon';
                $avertissement  = 'Concours enregistré en brouillon : pour le publier, ajoutez ' . implode(' et ', $manquants) . '.';
            }
        }

        $id = $this->concours->enregistrer($concours ? $concours->id : null, $data, $this->session->userdata('user_id'));

        if ($id === false) {
            array_map([$this, '_supprimer_fichier'], $nouveaux);
            $this->session->set_flashdata('error', "L'enregistrement a échoué. Veuillez réessayer.");
            redirect('concours-parametres');
        }

        if ($avertissement) {
            $this->session->set_flashdata('warning', $avertissement);
        }

        // Suppression physique des fichiers remplacés, seulement après succès en base
        foreach ($anciens as $fichier) {
            $this->_supprimer_fichier($fichier);
        }

        $this->session->set_flashdata('success', $concours ? 'Paramètres du concours mis à jour.' : 'Concours créé avec succès.');
        redirect('concours-parametres');
    }

    /* =================================================================
     *  VALIDATION
     * ================================================================= */

    private function _regles_validation()
    {
        $fv = $this->form_validation;
        $fv->set_error_delimiters('<span class="invalid-feedback d-block">', '</span>');

        $fv->set_rules('titre', 'Titre', 'trim|required|max_length[200]');
        $fv->set_rules('sous_titre', 'Sous-titre', 'trim|max_length[255]');
        $fv->set_rules('edition', 'Édition', 'trim|max_length[20]');
        $fv->set_rules('description', 'Description', 'trim');
        $fv->set_rules('objectifs', 'Objectifs', 'trim');
        $fv->set_rules('criteres_eligibilite', "Critères d'éligibilité", 'trim');

        $fv->set_rules('public_cible', 'Public cible', 'required|in_list[' . implode(',', array_keys(ConcoursM::PUBLICS)) . ']');
        $fv->set_rules('mode_participation', 'Mode de participation', 'required|in_list[' . implode(',', array_keys(ConcoursM::MODES)) . ']');
        $fv->set_rules('taille_equipe_min', 'Taille minimale', 'required|is_natural_no_zero|less_than_equal_to[20]');
        $fv->set_rules('taille_equipe_max', 'Taille maximale', 'required|is_natural_no_zero|less_than_equal_to[20]|callback__taille_equipe');
        $fv->set_rules('mode_validation_compte', 'Validation des comptes', 'required|in_list[' . implode(',', array_keys(ConcoursM::VALIDATIONS)) . ']');

        $fv->set_rules('extensions[]', 'Extensions autorisées', 'required|in_list[' . implode(',', ConcoursM::EXTENSIONS) . ']');
        $fv->set_rules('taille_max_fichier_mo', 'Taille max. par fichier', 'required|is_natural_no_zero|less_than_equal_to[500]');
        $fv->set_rules('nb_max_fichiers', 'Nombre max. de fichiers', 'required|is_natural_no_zero|less_than_equal_to[50]');

        $fv->set_rules('email_contact', 'E-mail de contact', 'trim|valid_email|max_length[150]');
        $fv->set_rules('telephone_contact', 'Téléphone', 'trim|max_length[30]|regex_match[/^[0-9 +().-]*$/]');
        $fv->set_rules('statut', 'Statut', 'required|in_list[' . implode(',', array_keys(ConcoursM::STATUTS)) . ']');
    }

    public function _taille_equipe($max)
    {
        if ($this->input->post('mode_participation') === 'individuel') {
            return true;
        }
        if ((int) $max < (int) $this->input->post('taille_equipe_min')) {
            $this->form_validation->set_message('_taille_equipe', 'La taille maximale doit être supérieure ou égale à la taille minimale.');
            return false;
        }
        return true;
    }

    private function _donnees_formulaire()
    {
        $p = $this->input;

        return [
            'titre'                      => $p->post('titre', true),
            'sous_titre'                 => $p->post('sous_titre', true) ?: null,
            'edition'                    => $p->post('edition', true) ?: null,
            'description'                => $p->post('description', true) ?: null,
            'objectifs'                  => $p->post('objectifs', true) ?: null,
            'criteres_eligibilite'       => $p->post('criteres_eligibilite', true) ?: null,
            'public_cible'               => $p->post('public_cible'),
            'mode_participation'         => $p->post('mode_participation'),
            'taille_equipe_min'          => (int) $p->post('taille_equipe_min'),
            'taille_equipe_max'          => (int) $p->post('taille_equipe_max'),
            'mode_validation_compte'     => $p->post('mode_validation_compte'),
            'extensions_autorisees'      => implode(',', array_intersect(ConcoursM::EXTENSIONS, (array) $p->post('extensions'))),
            'taille_max_fichier_mo'      => (int) $p->post('taille_max_fichier_mo'),
            'nb_max_fichiers'            => (int) $p->post('nb_max_fichiers'),
            'modification_avant_cloture' => $p->post('modification_avant_cloture') ? 1 : 0,
            'anonymisation_jury'         => $p->post('anonymisation_jury') ? 1 : 0,
            'email_contact'              => $p->post('email_contact', true) ?: null,
            'telephone_contact'          => $p->post('telephone_contact', true) ?: null,
            'statut'                     => $p->post('statut'),
        ];
    }

    /* =================================================================
     *  FICHIERS
     * ================================================================= */

    private function _televerser($champ, array $cfg)
    {
        $dossier = FCPATH . $this->dossier_upload;
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        $this->load->library('upload');
        $this->upload->initialize([
            'upload_path'      => $dossier,
            'allowed_types'    => $cfg['types'],
            'max_size'         => $cfg['max_ko'],
            'encrypt_name'     => true,
            'file_ext_tolower' => true,
            'remove_spaces'    => true,
        ]);

        if (!$this->upload->do_upload($champ)) {
            $this->erreur_upload = $cfg['label'] . ' : ' . strip_tags($this->upload->display_errors('', ''));
            return false;
        }

        return $this->dossier_upload . $this->upload->data('file_name');
    }

    public function _supprimer_fichier($chemin) // public pour array_map, non routable grâce au "_"
    {
        // Sécurité : on ne supprime que dans le dossier du module
        if (strpos($chemin, $this->dossier_upload) === 0 && is_file(FCPATH . $chemin)) {
            @unlink(FCPATH . $chemin);
        }
    }

    /** Plus petite des limites PHP (upload_max_filesize / post_max_size), en Mo. */
    private function _limite_upload_php_mo()
    {
        $convertir = function ($val) {
            $val  = trim($val);
            $unit = strtolower(substr($val, -1));
            $num  = (float) $val;
            switch ($unit) {
                case 'g':
                    return $num * 1024;
                case 'm':
                    return $num;
                case 'k':
                    return $num / 1024;
                default:
                    return $num / 1048576;
            }
        };

        return (int) min($convertir(ini_get('upload_max_filesize')), $convertir(ini_get('post_max_size')));
    }

    /* =================================================================
     *  DATES CLÉS (AGENDA)
     * ================================================================= */

    public function agenda()
    {
        $this->load->model('DatesClesM', 'dates_cles');

        $concours   = $this->concours->get_actif();
        $evenements = $concours ? $this->dates_cles->get_par_concours($concours->id) : [];

        $data = [
            'title'       => 'Dates clés du concours',
            'concours'    => $concours,
            'phase'       => $this->concours->phase($concours),
            'dates_cles'  => ConcoursM::DATES_CLES,
            'evenements'  => $evenements,
            'icones'      => DatesClesM::ICONES,
            'chronologie' => $this->_chronologie($concours, $evenements),
            'maintenant'  => time(),
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/concours/agenda', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /** Enregistre les 4 dates réglementaires (stockées dans `concours`). */
    public function enregistrer_dates()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-agenda');
        }

        $concours = $this->_concours_requis();

        $fv = $this->form_validation;
        $fv->set_error_delimiters('<span class="invalid-feedback d-block">', '</span>');
        $fv->set_rules('ouverture_inscriptions', 'Ouverture des inscriptions', 'required|callback__datetime');
        $fv->set_rules('cloture_inscriptions', 'Clôture des inscriptions', 'required|callback__datetime|callback__apres[ouverture_inscriptions]');
        $fv->set_rules('date_limite_soumission', 'Date limite de soumission', 'required|callback__datetime|callback__apres_ou_egal[cloture_inscriptions]');
        $fv->set_rules('date_resultats', 'Proclamation des résultats', 'callback__datetime|callback__apres[date_limite_soumission]');

        if (!$fv->run()) {
            return $this->agenda();
        }

        $dates = [];
        foreach (array_keys(ConcoursM::DATES_CLES) as $champ) {
            $dates[$champ] = $this->_vers_sql($this->input->post($champ));
        }

        if ($this->concours->enregistrer_dates($concours->id, $dates, $this->session->userdata('user_id'))) {
            $this->session->set_flashdata('success', 'Dates clés enregistrées.');
        } else {
            $this->session->set_flashdata('error', "L'enregistrement des dates a échoué.");
        }
        redirect('concours-agenda');
    }

    /** Ajout ou modification d'une étape complémentaire de l'agenda. */
    public function enregistrer_evenement()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-agenda');
        }

        $concours = $this->_concours_requis();
        $this->load->model('DatesClesM', 'dates_cles');

        $id = (int) $this->input->post('id');
        if ($id && !$this->dates_cles->get($id, $concours->id)) {
            show_404();
        }

        $fv = $this->form_validation;
        $fv->set_rules('libelle', 'Libellé', 'trim|required|max_length[150]');
        $fv->set_rules('description', 'Description', 'trim|max_length[255]');
        $fv->set_rules('date_debut', 'Date de début', 'required|callback__datetime');
        $fv->set_rules('date_fin', 'Date de fin', 'callback__datetime|callback__apres_ou_egal[date_debut]');
        $fv->set_rules('icone', 'Type', 'required|in_list[' . implode(',', array_keys(DatesClesM::ICONES)) . ']');

        if (!$fv->run()) {
            $this->session->set_flashdata('error', "L'étape n'a pas été enregistrée : " . trim(strip_tags(validation_errors(' ', ' '))));
            redirect('concours-agenda');
        }

        $data = [
            'concours_id' => (int) $concours->id,
            'libelle'     => $this->input->post('libelle', true),
            'description' => $this->input->post('description', true) ?: null,
            'date_debut'  => $this->_vers_sql($this->input->post('date_debut')),
            'date_fin'    => $this->_vers_sql($this->input->post('date_fin')),
            'icone'       => $this->input->post('icone'),
            'est_public'  => $this->input->post('est_public') ? 1 : 0,
        ];
        if (!$id) {
            $data['created_by'] = $this->session->userdata('user_id');
        }

        $ok = $this->dates_cles->enregistrer($id ?: null, $data);
        if ($ok) {
            $this->session->set_flashdata('success', $id ? 'Étape modifiée.' : "Étape ajoutée à l'agenda.");
        } else {
            $this->session->set_flashdata('error', "L'enregistrement de l'étape a échoué.");
        }
        redirect('concours-agenda');
    }

    public function supprimer_evenement()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-agenda');
        }

        $concours = $this->_concours_requis();
        $this->load->model('DatesClesM', 'dates_cles');

        $ok = $this->dates_cles->supprimer((int) $this->input->post('id'), $concours->id);
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Étape supprimée.' : 'Étape introuvable.');
        redirect('concours-agenda');
    }

    /* ---------- Outils de la page Dates clés ---------- */

    /** Redirige vers Paramètres si aucun concours n'existe encore. */
    private function _concours_requis()
    {
        $concours = $this->concours->get_actif();
        if (!$concours) {
            $this->session->set_flashdata('error', "Créez d'abord le concours dans Paramètres.");
            redirect('concours-parametres');
        }
        return $concours;
    }

    /** Fusionne dates réglementaires et étapes, triées chronologiquement. */
    private function _chronologie($concours, array $evenements)
    {
        $items = [];
        if (!$concours) {
            return $items;
        }

        foreach (ConcoursM::DATES_CLES as $champ => $def) {
            if (!empty($concours->$champ)) {
                $items[] = [
                    'date'          => $concours->$champ,
                    'date_fin'      => null,
                    'libelle'       => $def['label'],
                    'description'   => null,
                    'icone'         => $def['icone'],
                    'couleur'       => $def['couleur'],
                    'reglementaire' => true,
                    'est_public'    => true,
                ];
            }
        }
        foreach ($evenements as $e) {
            $items[] = [
                'date'          => $e->date_debut,
                'date_fin'      => $e->date_fin,
                'libelle'       => $e->libelle,
                'description'   => $e->description,
                'icone'         => $e->icone,
                'couleur'       => 'secondary',
                'reglementaire' => false,
                'est_public'    => (bool) $e->est_public,
            ];
        }

        usort($items, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $items;
    }

    /** "2026-11-01T08:00" (champ datetime-local) → "2026-11-01 08:00:00" */
    private function _vers_sql($valeur)
    {
        $valeur = trim((string) $valeur);
        return $valeur === '' ? null : str_replace('T', ' ', $valeur) . ':00';
    }

    public function _datetime($valeur)
    {
        if ($valeur === null || $valeur === '') {
            return true;
        }
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $valeur);
        if (!$d || $d->format('Y-m-d\TH:i') !== $valeur) {
            $this->form_validation->set_message('_datetime', 'Le champ {field} doit être une date et une heure valides.');
            return false;
        }
        return true;
    }

    public function _apres($valeur, $champ_reference)
    {
        return $this->_comparer_dates($valeur, $champ_reference, true, '_apres');
    }

    public function _apres_ou_egal($valeur, $champ_reference)
    {
        return $this->_comparer_dates($valeur, $champ_reference, false, '_apres_ou_egal');
    }

    private function _comparer_dates($valeur, $champ_reference, $strict, $regle)
    {
        $reference = $this->input->post($champ_reference);
        if ($valeur === '' || $valeur === null || $reference === '' || $reference === null) {
            return true;
        }

        $a = strtotime(str_replace('T', ' ', $valeur));
        $b = strtotime(str_replace('T', ' ', $reference));
        if ($a === false || $b === false) {
            return true; // le format est déjà contrôlé par _datetime
        }

        $ok = $strict ? $a > $b : $a >= $b;
        if (!$ok) {
            $libelles = array_map(function ($d) {
                return $d['label'];
            }, ConcoursM::DATES_CLES) + ['date_debut' => 'Date de début'];
            $ref = $libelles[$champ_reference] ?? $champ_reference;
            $this->form_validation->set_message($regle, 'Le champ {field} doit être ' . ($strict ? 'postérieur' : 'postérieur ou égal') . ' à « ' . $ref . ' ».');
        }
        return $ok;
    }

    /* =================================================================
     *  PRIX À GAGNER
     * ================================================================= */

    public function prix()
    {
        $this->load->model('PrixM', 'prix');

        $concours = $this->concours->get_actif();
        $prix     = $concours ? $this->prix->get_par_concours($concours->id) : [];

        $data = [
            'title'      => 'Prix à gagner',
            'concours'   => $concours,
            'prix'       => $prix,
            'totaux'     => $this->prix->totaux($prix),
            'categories' => PrixM::CATEGORIES,
            'devises'    => PrixM::DEVISES,
            'phase'      => $this->concours->phase($concours),
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/concours/prix', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /** Ajout ou modification d'un prix (formulaire de la fenêtre modale). */
    public function enregistrer_prix()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-prix');
        }

        $concours = $this->_concours_requis();
        $this->load->model('PrixM', 'prix');

        $id     = (int) $this->input->post('id');
        $ancien = $id ? $this->prix->get($id, $concours->id) : null;
        if ($id && !$ancien) {
            show_404();
        }

        $fv = $this->form_validation;
        $fv->set_rules('categorie', 'Catégorie', 'required|in_list[' . implode(',', array_keys(PrixM::CATEGORIES)) . ']');
        $fv->set_rules('rang', 'Rang', 'callback__rang_prix[' . (int) $concours->id . ',' . $id . ']');
        $fv->set_rules('libelle', 'Libellé', 'trim|required|max_length[150]');
        $fv->set_rules('montant', 'Montant', 'trim|numeric|greater_than_equal_to[0]|less_than[10000000000000]');
        $fv->set_rules('devise', 'Devise', 'required|in_list[' . implode(',', array_keys(PrixM::DEVISES)) . ']');
        $fv->set_rules('nombre_laureats', 'Nombre de lauréats', 'required|is_natural_no_zero|less_than_equal_to[50]');
        $fv->set_rules('description', 'Description', 'trim|max_length[2000]');

        if (!$fv->run()) {
            $this->session->set_flashdata('error', "Le prix n'a pas été enregistré : " . trim(strip_tags(validation_errors(' ', ' '))));
            redirect('concours-prix');
        }

        $categorie = $this->input->post('categorie');
        $montant   = trim((string) $this->input->post('montant'));

        $data = [
            'concours_id'     => (int) $concours->id,
            'categorie'       => $categorie,
            'rang'            => $categorie === 'principal' ? (int) $this->input->post('rang') : null,
            'libelle'         => $this->input->post('libelle', true),
            'montant'         => $montant === '' ? null : $montant,
            'devise'          => $this->input->post('devise'),
            'nombre_laureats' => (int) $this->input->post('nombre_laureats'),
            'description'     => $this->input->post('description', true) ?: null,
            'est_public'      => $this->input->post('est_public') ? 1 : 0,
        ];

        // Image facultative (réutilise le téléversement de la page Paramètres)
        $nouvelle_image = null;
        if (!empty($_FILES['image']['name'])) {
            $nouvelle_image = $this->_televerser('image', ['label' => 'Image', 'types' => 'jpg|jpeg|png|webp', 'max_ko' => 2048]);
            if ($nouvelle_image === false) {
                $this->session->set_flashdata('error', "Le prix n'a pas été enregistré. " . $this->erreur_upload);
                redirect('concours-prix');
            }
            $data['image'] = $nouvelle_image;
        } elseif ($ancien && $this->input->post('supprimer_image')) {
            $data['image'] = null;
        }

        if (!$id) {
            $data['created_by'] = $this->session->userdata('user_id');
        }

        $ok = $this->prix->enregistrer($id ?: null, $data);

        if (!$ok) {
            if ($nouvelle_image) {
                $this->_supprimer_fichier($nouvelle_image);
            }
            $this->session->set_flashdata('error', "L'enregistrement du prix a échoué.");
            redirect('concours-prix');
        }

        // Ancienne image remplacée ou retirée : suppression après succès en base
        if ($ancien && $ancien->image && array_key_exists('image', $data)) {
            $this->_supprimer_fichier($ancien->image);
        }

        $this->session->set_flashdata('success', $id ? 'Prix modifié.' : 'Prix ajouté.');
        redirect('concours-prix');
    }

    public function supprimer_prix()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-prix');
        }

        $concours = $this->_concours_requis();
        $this->load->model('PrixM', 'prix');

        $prix = $this->prix->get((int) $this->input->post('id'), $concours->id);
        if ($prix && $this->prix->supprimer($prix->id, $concours->id)) {
            if ($prix->image) {
                $this->_supprimer_fichier($prix->image);
            }
            $this->session->set_flashdata('success', 'Prix supprimé.');
        } else {
            $this->session->set_flashdata('error', 'Prix introuvable.');
        }
        redirect('concours-prix');
    }

    /**
     * Rang obligatoire pour un prix principal, entre 1 et 20, et unique dans le concours.
     * Paramètre : "concours_id,id_a_exclure"
     */
    public function _rang_prix($rang, $params)
    {
        if ($this->input->post('categorie') !== 'principal') {
            return true;
        }

        list($concours_id, $exclure_id) = array_map('intval', explode(',', $params));

        if (!ctype_digit((string) $rang) || (int) $rang < 1 || (int) $rang > 20) {
            $this->form_validation->set_message('_rang_prix', 'Le rang d\'un prix principal doit être un nombre entre 1 et 20.');
            return false;
        }
        if ($this->prix->rang_existe($concours_id, (int) $rang, $exclure_id)) {
            $this->form_validation->set_message('_rang_prix', 'Un prix principal occupe déjà le rang ' . (int) $rang . '.');
            return false;
        }
        return true;
    }

    /* =================================================================
     *  BRIEF & RESSOURCES
     *  Fichiers stockés HORS du dossier public (application/storage/…),
     *  servis uniquement par le contrôleur après contrôle d'accès.
     * ================================================================= */

    private $dossier_ressources = 'storage/ressources/'; // relatif à APPPATH

    public function ressources()
    {
        $this->load->model('RessourcesM', 'ressource');

        $concours = $this->concours->get_actif();
        $liste    = $concours ? $this->ressource->get_par_concours($concours->id) : [];

        $groupes = array_fill_keys(array_keys(RessourcesM::CATEGORIES), []);
        foreach ($liste as $r) {
            $groupes[$r->categorie][] = $r;
        }

        $data = [
            'title'       => 'Brief & ressources',
            'concours'    => $concours,
            'groupes'     => $groupes,
            'stats'       => $this->ressource->stats($liste),
            'categories'  => RessourcesM::CATEGORIES,
            'visibilites' => RessourcesM::VISIBILITES,
            'extensions'  => RessourcesM::EXTENSIONS,
            'taille_max'  => $this->_taille_max_ressource_mo(),
        ];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/concours/ressources', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /** Ajout d'une ressource, ou modification (avec remplacement éventuel du fichier). */
    public function enregistrer_ressource()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-ressources');
        }

        // Envoi plus gros que post_max_size : PHP vide $_POST et $_FILES sans prévenir
        if (empty($_POST) && (int) $this->input->server('CONTENT_LENGTH') > 0) {
            $this->session->set_flashdata('error', 'Fichier trop volumineux : le serveur accepte au plus ' . $this->_limite_upload_php_mo() . ' Mo par envoi.');
            redirect('concours-ressources');
        }

        $concours = $this->_concours_requis();
        $this->load->model('RessourcesM', 'ressource');

        $id     = (int) $this->input->post('id');
        $ancien = $id ? $this->ressource->get($id, $concours->id) : null;
        if ($id && !$ancien) {
            show_404();
        }

        $fv = $this->form_validation;
        $fv->set_rules('titre', 'Titre', 'trim|required|max_length[150]');
        $fv->set_rules('description', 'Description', 'trim|max_length[1000]');
        $fv->set_rules('categorie', 'Catégorie', 'required|in_list[' . implode(',', array_keys(RessourcesM::CATEGORIES)) . ']');
        $fv->set_rules('visibilite', 'Visibilité', 'required|in_list[' . implode(',', array_keys(RessourcesM::VISIBILITES)) . ']');

        if (!$fv->run()) {
            $this->session->set_flashdata('error', "La ressource n'a pas été enregistrée : " . trim(strip_tags(validation_errors(' ', ' '))));
            redirect('concours-ressources');
        }

        $categorie = $this->input->post('categorie');
        $data = [
            'concours_id' => (int) $concours->id,
            'categorie'   => $categorie,
            'titre'       => $this->input->post('titre', true),
            'description' => $this->input->post('description', true) ?: null,
            'visibilite'  => $this->input->post('visibilite'),
            'est_publie'  => $this->input->post('est_publie') ? 1 : 0,
        ];

        // Fichier : obligatoire à la création, facultatif en modification
        $nouveau = null;
        if (!empty($_FILES['fichier']['name'])) {
            $nouveau = $this->_stocker_ressource('fichier');
            if ($nouveau === false) {
                $this->session->set_flashdata('error', "La ressource n'a pas été enregistrée. " . $this->erreur_upload);
                redirect('concours-ressources');
            }
            $data += $nouveau;
            if ($ancien) {
                $data['version'] = (int) $ancien->version + 1;
            }
        } elseif (!$ancien) {
            $this->session->set_flashdata('error', 'Le fichier est obligatoire pour une nouvelle ressource.');
            redirect('concours-ressources');
        }

        if (!$ancien) {
            $data['created_by'] = $this->session->userdata('user_id');
            $data['ordre']      = $this->ressource->prochain_ordre($concours->id, $categorie);
        } elseif ($ancien->categorie !== $categorie) {
            $data['ordre'] = $this->ressource->prochain_ordre($concours->id, $categorie);
        }

        $ok = $this->ressource->enregistrer($id ?: null, $data);

        if (!$ok) {
            if ($nouveau) {
                $this->_supprimer_fichier_ressource($nouveau['fichier_chemin']);
            }
            $this->session->set_flashdata('error', "L'enregistrement de la ressource a échoué.");
            redirect('concours-ressources');
        }

        // Ancien fichier remplacé : suppression seulement après succès en base
        if ($nouveau && $ancien) {
            $this->_supprimer_fichier_ressource($ancien->fichier_chemin);
        }

        $message = !$ancien ? 'Ressource ajoutée.' : ($nouveau ? 'Fichier remplacé (version ' . $data['version'] . ').' : 'Ressource modifiée.');
        $this->session->set_flashdata('success', $message);
        redirect('concours-ressources');
    }

    public function supprimer_ressource()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-ressources');
        }

        $concours = $this->_concours_requis();
        $this->load->model('RessourcesM', 'ressource');

        $r = $this->ressource->get((int) $this->input->post('id'), $concours->id);
        if ($r && $this->ressource->supprimer($r->id, $concours->id)) {
            $this->_supprimer_fichier_ressource($r->fichier_chemin);
            $this->session->set_flashdata('success', 'Ressource supprimée.');
        } else {
            $this->session->set_flashdata('error', 'Ressource introuvable.');
        }
        redirect('concours-ressources');
    }

    public function deplacer_ressource()
    {
        if ($this->input->method() !== 'post') {
            redirect('concours-ressources');
        }

        $concours = $this->_concours_requis();
        $this->load->model('RessourcesM', 'ressource');

        $sens = $this->input->post('sens') === 'haut' ? 'haut' : 'bas';
        $this->ressource->deplacer((int) $this->input->post('id'), $concours->id, $sens);
        redirect('concours-ressources');
    }

    /** Téléchargement côté administration (ne compte pas dans les statistiques). */
    public function telecharger_ressource($id = 0)
    {
        $concours = $this->_concours_requis();
        $this->load->model('RessourcesM', 'ressource');

        $r = $this->ressource->get((int) $id, $concours->id);
        if (!$r) {
            show_404();
        }
        $this->_envoyer_fichier_ressource($r);
    }

    /* ---------- Outils de la page Brief & ressources ---------- */

    /** Taille max réellement acceptée : plus petite valeur entre la règle métier et PHP. */
    private function _taille_max_ressource_mo()
    {
        return max(1, min(RessourcesM::TAILLE_MAX_MO, $this->_limite_upload_php_mo()));
    }

    private function _chemin_ressources()
    {
        $dossier = APPPATH . $this->dossier_ressources;
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
            // Double protection si le dossier application/ n'était pas bloqué par le serveur
            file_put_contents($dossier . '.htaccess', "Require all denied\nDeny from all\n");
            file_put_contents($dossier . 'index.html', '');
        }
        return $dossier;
    }

    /**
     * Contrôle et range le fichier envoyé. Retourne les colonnes fichier_* ou false.
     */
    private function _stocker_ressource($champ)
    {
        $f = $_FILES[$champ];
        $taille_max = $this->_taille_max_ressource_mo();

        if ($f['error'] !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE  => "Fichier trop volumineux ($taille_max Mo maximum).",
                UPLOAD_ERR_FORM_SIZE => "Fichier trop volumineux ($taille_max Mo maximum).",
                UPLOAD_ERR_PARTIAL   => "L'envoi du fichier a été interrompu. Réessayez.",
                UPLOAD_ERR_NO_FILE   => 'Aucun fichier reçu.',
            ];
            $this->erreur_upload = $messages[$f['error']] ?? "Erreur serveur lors de l'envoi (code {$f['error']}).";
            return false;
        }

        $extension = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, RessourcesM::EXTENSIONS, true)) {
            $this->erreur_upload = 'Format non autorisé (.' . html_escape($extension) . '). Formats acceptés : ' . implode(', ', RessourcesM::EXTENSIONS) . '.';
            return false;
        }
        if ($f['size'] > $taille_max * 1048576) {
            $this->erreur_upload = "Fichier trop volumineux ($taille_max Mo maximum).";
            return false;
        }
        if (!is_uploaded_file($f['tmp_name'])) {
            $this->erreur_upload = 'Fichier invalide.';
            return false;
        }

        $nom_disque  = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->_chemin_ressources() . $nom_disque;

        if (!move_uploaded_file($f['tmp_name'], $destination)) {
            $this->erreur_upload = "Impossible d'enregistrer le fichier : vérifiez les droits d'écriture sur application/storage/.";
            return false;
        }

        $mime = $f['type'];
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $destination) ?: $mime;
            finfo_close($finfo);
        }

        // Nom d'origine nettoyé (affiché au téléchargement)
        $nom_original = preg_replace('/[^\pL\pN ._()\-]+/u', '_', basename($f['name']));
        $nom_original = mb_substr(trim($nom_original), 0, 200) ?: ('ressource.' . $extension);

        return [
            'fichier_chemin'       => $nom_disque,
            'fichier_nom_original' => $nom_original,
            'fichier_taille'       => (int) filesize($destination),
            'fichier_mime'         => mb_substr((string) $mime, 0, 100),
            'fichier_extension'    => $extension,
            'hash_sha256'          => hash_file('sha256', $destination),
        ];
    }

    private function _supprimer_fichier_ressource($nom_disque)
    {
        // Seuls les noms générés par _stocker_ressource sont acceptés (pas de "../")
        if (preg_match('/^[a-f0-9]{32}\.[a-z0-9]{1,10}$/', (string) $nom_disque)) {
            $chemin = APPPATH . $this->dossier_ressources . $nom_disque;
            if (is_file($chemin)) {
                @unlink($chemin);
            }
        }
    }

    /** Envoie le fichier au navigateur sous son nom d'origine. Réutilisable côté candidat. */
    private function _envoyer_fichier_ressource($r)
    {
        $chemin = APPPATH . $this->dossier_ressources . $r->fichier_chemin;
        if (!preg_match('/^[a-f0-9]{32}\.[a-z0-9]{1,10}$/', $r->fichier_chemin) || !is_file($chemin)) {
            show_error('Le fichier de cette ressource est introuvable sur le serveur.', 404);
        }

        $nom   = $r->fichier_nom_original;
        $ascii = preg_replace('/[^\x20-\x7E]/', '_', str_replace(['"', '\\'], '', $nom));

        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: ' . ($r->fichier_mime ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($chemin));
        header('Content-Disposition: attachment; filename="' . $ascii . '"; filename*=UTF-8\'\'' . rawurlencode($nom));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($chemin);
        exit;
    }
}
