<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminC extends Back_Controller
{
    /**
     * Clés de session possibles pour l'id de l'utilisateur connecté.
     * Mettez en premier celle que BackOfficeC::authenticate() enregistre réellement.
     */
    const CLES_SESSION_ID = ['user_id', 'utilisateur_id', 'id'];

    /** @var array rôles gérés : code => [id, libelle] (lus dans la table roles) */
    private $roles = [];

    /** @var array|null utilisateur connecté (rechargé depuis la base) */
    private $moi = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('AdminM', 'admin');
        $this->load->library('form_validation');
        $this->roles = $this->admin->roles();
    }

    public function index()
    {
        redirect('utilisateurs');
    }

    /* ================================================================== */
    /*  Page                                                              */
    /* ================================================================== */

    public function utilisateurs()
    {
        $this->_exiger_admin();

        $data['title'] = 'Administrateurs & jury';
        $data['roles'] = $this->roles;
        $data['moi']   = $this->moi;

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/admin/utilisateurs', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /* ================================================================== */
    /*  AJAX                                                              */
    /* ================================================================== */

    public function liste()
    {
        $this->_exiger_admin(true);

        $filtres = [
            'role'   => (string) $this->input->get('role', true),
            'statut' => (string) $this->input->get('statut', true),
            'q'      => trim((string) $this->input->get('q', true)),
        ];

        $this->_json([
            'ok'     => true,
            'lignes' => $this->admin->lister($filtres),
            'stats'  => $this->admin->stats(),
            'now'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function detail($id = 0)
    {
        $this->_exiger_admin(true);

        $u = $this->admin->trouver($id);
        if (!$u) {
            return $this->_json(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }
        $this->_json(['ok' => true, 'utilisateur' => $u]);
    }

    public function enregistrer()
    {
        $this->_exiger_admin(true);

        $id    = (int) $this->input->post('id');
        $role  = (string) $this->input->post('role');
        $mdp   = (string) $this->input->post('mot_de_passe');
        $email = strtolower(trim((string) $this->input->post('email', true)));

        // --- Validation ---
        $this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('email', 'E-mail', 'trim|required|valid_email|max_length[150]');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'trim|max_length[30]');
        $this->form_validation->set_rules('organisation', 'Organisation', 'trim|max_length[150]');
        $this->form_validation->set_rules('role', 'Rôle', 'required|in_list[' . implode(',', array_keys($this->roles)) . ']');
        if ($mdp !== '') {
            $this->form_validation->set_rules('mot_de_passe', 'Mot de passe', 'min_length[8]|max_length[72]');
        }

        if (!$this->form_validation->run()) {
            return $this->_json([
                'ok'      => false,
                'message' => 'Veuillez corriger les champs indiqués.',
                'erreurs' => $this->form_validation->error_array(),
            ], 422);
        }

        if ($this->admin->email_existe($email, $id)) {
            return $this->_json([
                'ok'      => false,
                'message' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
                'erreurs' => ['email' => 'Cette adresse e-mail est déjà utilisée par un autre compte.'],
            ], 422);
        }

        // --- Règles métier ---
        $existant = null;
        if ($id) {
            $existant = $this->admin->trouver($id);
            if (!$existant) {
                return $this->_json(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
            }
            if ($id === (int) $this->moi['id'] && $role !== $existant['role']) {
                return $this->_json(['ok' => false, 'message' => 'Vous ne pouvez pas modifier votre propre rôle.'], 403);
            }
            if (
                $existant['role'] === 'admin' && $role !== 'admin' && $existant['statut'] === 'actif'
                && $this->admin->compter_admins_actifs($id) === 0
            ) {
                return $this->_json(['ok' => false, 'message' => 'Il doit rester au moins un administrateur actif.'], 409);
            }
        }

        // --- Données ---
        $data = [
            'role_id'      => $this->roles[$role]['id'],
            'nom'          => $this->input->post('nom', true),
            'prenom'       => $this->input->post('prenom', true),
            'email'        => $email,
            'telephone'    => $this->input->post('telephone', true) ?: null,
            'organisation' => $this->input->post('organisation', true) ?: null,
        ];

        $mdp_genere = null;
        $maintenant = date('Y-m-d H:i:s');

        if (!$id) {
            if ($mdp === '') {
                $mdp = $mdp_genere = $this->_mot_de_passe_aleatoire();
            }
            // Un compte créé par un administrateur est considéré comme vérifié et validé
            $data += [
                'mot_de_passe'     => password_hash($mdp, PASSWORD_DEFAULT),
                'statut'           => $this->input->post('actif') ? 'actif' : 'suspendu',
                'email_verifie_le' => $maintenant,
                'valide_par'       => (int) $this->moi['id'],
                'valide_le'        => $maintenant,
            ];
            $id = $this->admin->creer($data);
            $this->admin->journaliser(
                $this->moi['id'],
                'creation_utilisateur',
                'utilisateur',
                $id,
                ['email' => $email, 'role' => $role]
            );
            $message = 'Compte créé avec succès.';
        } else {
            if ($mdp !== '') {
                $data['mot_de_passe']        = password_hash($mdp, PASSWORD_DEFAULT);
                $data['tentatives_echouees'] = 0;
                $data['bloque_jusqua']       = null;
                $this->admin->invalider_liens_reset($id);
            }
            $this->admin->modifier($id, $data);
            $this->admin->journaliser(
                $this->moi['id'],
                'modification_utilisateur',
                'utilisateur',
                $id,
                ['email' => $email, 'role' => $role, 'mdp_change' => $mdp !== '']
            );
            $message = 'Compte mis à jour.';
        }

        // --- Envoi des identifiants ---
        $email_envoye = null;
        if ($this->input->post('envoyer_email') && $mdp !== '') {
            $email_envoye = $this->_envoyer_identifiants($data + ['role' => $role], $mdp, $existant ? 'maj' : 'creation');
        }

        $this->_json([
            'ok'           => true,
            'message'      => $message,
            'id'           => $id,
            'identifiants' => $mdp_genere ? ['email' => $email, 'mot_de_passe' => $mdp_genere] : null,
            'email_envoye' => $email_envoye,
        ]);
    }

    /** Active / suspend un compte. Réactiver lève aussi un éventuel blocage temporaire. */
    public function statut()
    {
        $this->_exiger_admin(true);

        $id     = (int) $this->input->post('id');
        $statut = $this->input->post('statut') === 'actif' ? 'actif' : 'suspendu';
        $u      = $this->admin->trouver($id);

        if (!$u) {
            return $this->_json(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }
        if ($id === (int) $this->moi['id']) {
            return $this->_json(['ok' => false, 'message' => 'Vous ne pouvez pas suspendre votre propre compte.'], 403);
        }
        if ($statut === 'suspendu' && $u['role'] === 'admin' && $this->admin->compter_admins_actifs($id) === 0) {
            return $this->_json(['ok' => false, 'message' => 'Il doit rester au moins un administrateur actif.'], 409);
        }

        $data = ['statut' => $statut];
        if ($statut === 'actif') {
            $data['tentatives_echouees'] = 0;
            $data['bloque_jusqua']       = null;
            if (empty($u['valide_le'])) {
                $data['valide_par'] = (int) $this->moi['id'];
                $data['valide_le']  = date('Y-m-d H:i:s');
            }
        }
        $this->admin->modifier($id, $data);
        $this->admin->journaliser(
            $this->moi['id'],
            $statut === 'actif' ? 'activation_utilisateur' : 'suspension_utilisateur',
            'utilisateur',
            $id,
            ['email' => $u['email']]
        );

        $this->_json(['ok' => true, 'message' => $statut === 'actif' ? 'Compte activé.' : 'Compte suspendu.']);
    }

    public function reinitialiser()
    {
        $this->_exiger_admin(true);

        $id = (int) $this->input->post('id');
        $u  = $this->admin->trouver($id);

        if (!$u) {
            return $this->_json(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }

        $mdp = $this->_mot_de_passe_aleatoire();
        $this->admin->modifier($id, [
            'mot_de_passe'        => password_hash($mdp, PASSWORD_DEFAULT),
            'tentatives_echouees' => 0,
            'bloque_jusqua'       => null,
        ]);
        $this->admin->invalider_liens_reset($id);
        $this->admin->journaliser(
            $this->moi['id'],
            'reinitialisation_mdp_admin',
            'utilisateur',
            $id,
            ['email' => $u['email']]
        );

        $email_envoye = $this->input->post('envoyer_email')
            ? $this->_envoyer_identifiants($u, $mdp, 'reinitialisation')
            : null;

        $this->_json([
            'ok'           => true,
            'message'      => 'Mot de passe réinitialisé.',
            'identifiants' => ['email' => $u['email'], 'mot_de_passe' => $mdp],
            'email_envoye' => $email_envoye,
        ]);
    }

    /** Suppression définitive, refusée si le compte a déjà un historique (notes, candidatures…). */
    public function supprimer()
    {
        $this->_exiger_admin(true);

        $id = (int) $this->input->post('id');
        $u  = $this->admin->trouver($id);

        if (!$u) {
            return $this->_json(['ok' => false, 'message' => 'Utilisateur introuvable.'], 404);
        }
        if ($id === (int) $this->moi['id']) {
            return $this->_json(['ok' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }
        if ($u['role'] === 'admin' && $u['statut'] === 'actif' && $this->admin->compter_admins_actifs($id) === 0) {
            return $this->_json(['ok' => false, 'message' => 'Il doit rester au moins un administrateur actif.'], 409);
        }

        $liens = $this->admin->dependances($id);
        if ($liens) {
            return $this->_json([
                'ok'      => false,
                'message' => 'Ce compte a déjà un historique (' . implode(', ', $liens) . '). '
                    . 'Suspendez-le plutôt pour conserver la traçabilité.',
            ], 409);
        }

        $this->admin->supprimer($id);
        $this->admin->journaliser(
            $this->moi['id'],
            'suppression_utilisateur',
            'utilisateur',
            $id,
            ['email' => $u['email'], 'role' => $u['role']]
        );

        $this->_json(['ok' => true, 'message' => 'Compte supprimé.']);
    }

    /* ================================================================== */
    /*  Outils internes                                                   */
    /* ================================================================== */

    /** Seuls les administrateurs actifs accèdent à ce module. */
    private function _exiger_admin($ajax = false)
    {
        $this->_exiger_roles(['admin'], $ajax);
    }

    /** Tout utilisateur du back-office (admin ou jury) dont le compte est actif. */
    private function _exiger_connecte($ajax = false)
    {
        $this->_exiger_roles(['admin', 'jury'], $ajax);
    }

    private function _exiger_roles(array $roles, $ajax = false)
    {
        $id = 0;
        foreach (self::CLES_SESSION_ID as $cle) {
            if ($id = (int) $this->session->userdata($cle)) {
                break;
            }
        }
        $this->moi = $id ? $this->admin->trouver($id) : null;

        if ($this->moi && in_array($this->moi['role'], $roles, true) && $this->moi['statut'] === 'actif') {
            return;
        }

        if ($ajax || $this->input->is_ajax_request()) {
            $this->_json(['ok' => false, 'message' => 'Accès refusé.'], 403);
            $this->output->_display();
            exit;
        }
        show_error('Vous n\'avez pas accès à cette page.', 403, 'Accès refusé');
    }

    private function _json(array $data, $code = 200)
    {
        $data['csrf'] = $this->security->get_csrf_hash();   // utile si csrf_regenerate = TRUE
        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function _mot_de_passe_aleatoire($longueur = 12)
    {
        $groupes = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '@#$%&*?!'];
        $tous    = implode('', $groupes);
        $car     = [];
        foreach ($groupes as $g) {
            $car[] = $g[random_int(0, strlen($g) - 1)];
        }
        while (count($car) < $longueur) {
            $car[] = $tous[random_int(0, strlen($tous) - 1)];
        }
        for ($i = count($car) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$car[$i], $car[$j]] = [$car[$j], $car[$i]];
        }
        return implode('', $car);
    }

    private function _envoyer_identifiants(array $u, $mdp, $contexte)
    {
        $this->load->library('email');

        $intro = [
            'creation'         => 'Un compte vient d\'être créé pour vous sur la plateforme du concours SATRACO.',
            'maj'              => 'Vos identifiants sur la plateforme du concours SATRACO ont été mis à jour.',
            'reinitialisation' => 'Votre mot de passe sur la plateforme du concours SATRACO a été réinitialisé.',
        ][$contexte];

        $role = $this->roles[$u['role']]['libelle'] ?? $u['role'];
        $lien = site_url('sign-in');
        $html = '<p>Bonjour ' . html_escape($u['prenom'] . ' ' . $u['nom']) . ',</p>'
            . '<p>' . $intro . '</p>'
            . '<p><strong>Rôle :</strong> ' . html_escape($role) . '<br>'
            . '<strong>Identifiant :</strong> ' . html_escape($u['email']) . '<br>'
            . '<strong>Mot de passe :</strong> ' . html_escape($mdp) . '</p>'
            . '<p><a href="' . $lien . '">Se connecter</a></p>'
            . '<p>Nous vous recommandons de changer ce mot de passe dès votre première connexion (menu « Mon profil »).</p>'
            . '<p>— L\'équipe SATRACO</p>';

        $this->email->clear();
        $this->email->set_mailtype('html');
        $this->email->from($this->config->item('smtp_user') ?: 'no-reply@satraco.bi', 'Concours SATRACO');
        $this->email->to($u['email']);
        $this->email->subject('Vos accès — Concours SATRACO');
        $this->email->message($html);

        $ok = $this->email->send(false);
        if (!$ok) {
            log_message('error', 'AdminC: échec envoi identifiants à ' . $u['email'] . ' — ' . $this->email->print_debugger(['headers']));
        }
        return $ok;
    }

    /* ================================================================== */
    /*  Journal d'activités                                               */
    /* ================================================================== */

    /** Nombre maximum de lignes par export CSV */
    const JOURNAL_EXPORT_MAX = 20000;

    public function journal()
    {
        $this->_exiger_admin();

        $data['title']        = 'Journal d\'activités';
        $data['actions']      = $this->_journal_actions_connues();
        $data['utilisateurs'] = $this->admin->journal_utilisateurs();

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/admin/journal', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    public function journal_liste()
    {
        $this->_exiger_admin(true);

        $f        = $this->_journal_filtres();
        $par_page = (int) $this->input->get('par_page');
        $par_page = in_array($par_page, [25, 50, 100], true) ? $par_page : 25;
        $total    = $this->admin->journal_compter($f);
        $pages    = max(1, (int) ceil($total / $par_page));
        $page     = min(max(1, (int) $this->input->get('page')), $pages);

        $this->_json([
            'ok'       => true,
            'lignes'   => $this->admin->journal_lister($f, $par_page, ($page - 1) * $par_page),
            'total'    => $total,
            'page'     => $page,
            'pages'    => $pages,
            'par_page' => $par_page,
            'stats'    => $this->admin->journal_stats(),
        ]);
    }

    /** Export CSV (séparateur « ; », UTF-8 avec BOM pour Excel) des entrées filtrées. */
    public function journal_export()
    {
        $this->_exiger_admin();

        $f      = $this->_journal_filtres();
        $total  = $this->admin->journal_compter($f);
        $lignes = $this->admin->journal_lister($f, self::JOURNAL_EXPORT_MAX, 0);
        $libs   = $this->_journal_actions_connues();

        $this->admin->journaliser(
            $this->moi['id'],
            'export_journal',
            null,
            null,
            array_filter($f + ['lignes' => count($lignes)])
        );

        $nom = 'journal-activites-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nom . '"');
        header('Cache-Control: no-store, no-cache');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, [
            'Date',
            'Utilisateur',
            'E-mail',
            'Rôle',
            'Action',
            'Code action',
            'Cible',
            'Détails',
            'Adresse IP',
            'Navigateur'
        ], ';');

        foreach ($lignes as $l) {
            $cible = $l['cible_type']
                ? $l['cible_type'] . ($l['cible_id'] ? ' #' . $l['cible_id'] : '') . ($l['cible_nom'] ? ' (' . $l['cible_nom'] . ')' : '')
                : '';
            fputcsv($out, [
                $l['created_at'],
                $l['utilisateur_id'] ? trim($l['prenom'] . ' ' . $l['nom']) : 'Système / inconnu',
                $l['email'],
                $l['role_libelle'],
                $libs[$l['action']]['libelle'] ?? $l['action'],
                $l['action'],
                $cible,
                $this->_csv_sur($l['details']),
                $l['ip'],
                $l['user_agent'],
            ], ';');
        }
        if ($total > self::JOURNAL_EXPORT_MAX) {
            fputcsv($out, ['… export limité aux ' . self::JOURNAL_EXPORT_MAX . ' entrées les plus récentes sur ' . $total
                . '. Affinez les filtres pour exporter le reste.'], ';');
        }
        fclose($out);
        exit;
    }

    /** Filtres de la requête, nettoyés (dates au format Y-m-d uniquement). */
    private function _journal_filtres()
    {
        $date = function ($v) {
            $v = (string) $v;
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null;
        };
        $f = [
            'date_debut'     => $date($this->input->get('date_debut')),
            'date_fin'       => $date($this->input->get('date_fin')),
            'utilisateur_id' => (int) $this->input->get('utilisateur_id') ?: null,
            'action'         => preg_replace('/[^a-z0-9_]/', '', (string) $this->input->get('action')) ?: null,
            'q'              => trim((string) $this->input->get('q', true)) ?: null,
        ];

        // Filtre par catégorie (authentification, comptes…) → liste d'actions
        $categorie = (string) $this->input->get('categorie');
        if (!$f['action'] && $categorie) {
            $actions = [];
            foreach ($this->_journal_actions_connues() as $code => $a) {
                if ($a['categorie'] === $categorie) {
                    $actions[] = $code;
                }
            }
            $f['actions'] = $actions ?: ['__aucune__'];
        }
        return $f;
    }

    /** Neutralise les formules Excel (=, +, -, @) dans une cellule CSV. */
    private function _csv_sur($v)
    {
        $v = (string) $v;
        return ($v !== '' && strpos('=+-@', $v[0]) !== false) ? "'" . $v : $v;
    }

    /**
     * Libellés des actions connues, regroupés par catégorie.
     * Les actions absentes de cette liste restent affichées (libellé généré à partir du code).
     */
    private function _journal_actions_connues()
    {
        $a = function ($libelle, $categorie, $icone, $couleur) {
            return compact('libelle', 'categorie', 'icone', 'couleur');
        };
        return [
            // Authentification
            'connexion'                     => $a('Connexion', 'auth', 'fa-sign-in-alt', 'success'),
            'deconnexion'                   => $a('Déconnexion', 'auth', 'fa-sign-out-alt', 'secondary'),
            'echec_connexion'               => $a('Échec de connexion', 'securite', 'fa-exclamation-triangle', 'danger'),
            'compte_bloque'                 => $a('Compte bloqué', 'securite', 'fa-lock', 'danger'),
            'demande_reinitialisation'      => $a('Demande de réinitialisation', 'securite', 'fa-envelope', 'warning'),
            'reinitialisation_mot_de_passe' => $a('Mot de passe réinitialisé', 'securite', 'fa-key', 'warning'),
            // Comptes
            'validation_compte'             => $a('Compte candidat validé', 'comptes', 'fa-user-check', 'success'),
            'rejet_compte'                  => $a('Compte candidat rejeté', 'comptes', 'fa-user-times', 'danger'),
            'creation_utilisateur'          => $a('Compte créé', 'comptes', 'fa-user-plus', 'primary'),
            'modification_utilisateur'      => $a('Compte modifié', 'comptes', 'fa-user-edit', 'info'),
            'activation_utilisateur'        => $a('Compte activé', 'comptes', 'fa-toggle-on', 'success'),
            'suspension_utilisateur'        => $a('Compte suspendu', 'comptes', 'fa-toggle-off', 'warning'),
            'reinitialisation_mdp_admin'    => $a('Mot de passe réinitialisé par un admin', 'comptes', 'fa-key', 'warning'),
            'suppression_utilisateur'       => $a('Compte supprimé', 'comptes', 'fa-user-slash', 'danger'),
            // Données
            'export'                        => $a('Export de données', 'donnees', 'fa-file-export', 'info'),
            'export_journal'                => $a('Export du journal', 'donnees', 'fa-file-csv', 'info'),
            // Profil
            'modification_profil'           => $a('Profil modifié', 'comptes', 'fa-id-card', 'info'),
            'changement_mot_de_passe'       => $a('Mot de passe changé', 'securite', 'fa-key', 'success'),
            'echec_changement_mdp'          => $a('Échec de changement de mot de passe', 'securite', 'fa-exclamation-triangle', 'danger'),
        ];
    }

    /* ================================================================== */
    /*  Mon profil (admin et jury)                                        */
    /* ================================================================== */

    /** Échecs successifs du mot de passe actuel tolérés avant un blocage temporaire */
    const PROFIL_ESSAIS_MAX   = 5;
    const PROFIL_BLOCAGE_MIN  = 10;

    public function profil()
    {
        $this->_exiger_connecte();

        $data['title']   = 'Mon profil';
        $data['moi']     = $this->moi;
        $data['roles']   = $this->roles;
        $data['actions'] = $this->_journal_actions_connues();
        $data['nb_activites'] = $this->admin->journal_compter(['utilisateur_id' => $this->moi['id']]);

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        $this->load->view('back/components/app/admin/profil', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }

    /** Mise à jour des informations personnelles. Changer d'e-mail exige le mot de passe actuel. */
    public function profil_infos()
    {
        $this->_exiger_connecte(true);

        $email          = strtolower(trim((string) $this->input->post('email', true)));
        $email_change   = $email !== strtolower($this->moi['email']);

        $this->form_validation->set_rules('nom', 'Nom', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('prenom', 'Prénom', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('email', 'E-mail', 'trim|required|valid_email|max_length[150]');
        $this->form_validation->set_rules('telephone', 'Téléphone', 'trim|max_length[30]');
        $this->form_validation->set_rules('organisation', 'Organisation', 'trim|max_length[150]');
        if ($email_change) {
            $this->form_validation->set_rules(
                'mot_de_passe_actuel',
                'Mot de passe actuel',
                'required',
                ['required' => 'Saisissez votre mot de passe actuel pour changer d\'adresse e-mail.']
            );
        }

        if (!$this->form_validation->run()) {
            return $this->_json([
                'ok'      => false,
                'message' => 'Veuillez corriger les champs indiqués.',
                'erreurs' => $this->form_validation->error_array(),
            ], 422);
        }

        if ($email_change) {
            if ($this->admin->email_existe($email, $this->moi['id'])) {
                return $this->_json([
                    'ok'      => false,
                    'message' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
                    'erreurs' => ['email' => 'Cette adresse e-mail est déjà utilisée par un autre compte.'],
                ], 422);
            }
            if (!$this->_verifier_mot_de_passe_actuel('mot_de_passe_actuel')) {
                return;
            }
        }

        $data = [
            'nom'          => $this->input->post('nom', true),
            'prenom'       => $this->input->post('prenom', true),
            'email'        => $email,
            'telephone'    => $this->input->post('telephone', true) ?: null,
            'organisation' => $this->input->post('organisation', true) ?: null,
        ];

        $modifies = [];
        foreach ($data as $champ => $valeur) {
            if ((string) $valeur !== (string) $this->moi[$champ]) {
                $modifies[] = $champ;
            }
        }
        if (!$modifies) {
            return $this->_json(['ok' => true, 'message' => 'Aucune modification à enregistrer.', 'utilisateur' => $this->moi]);
        }

        $this->admin->modifier($this->moi['id'], $data);
        $this->admin->journaliser(
            $this->moi['id'],
            'modification_profil',
            'utilisateur',
            $this->moi['id'],
            ['champs' => implode(', ', $modifies)] + ($email_change ? ['ancien_email' => $this->moi['email']] : [])
        );

        // Garde la session cohérente si le header lit le nom / l'e-mail depuis la session
        foreach (['nom', 'prenom', 'email'] as $cle) {
            if ($this->session->has_userdata($cle)) {
                $this->session->set_userdata($cle, $data[$cle]);
            }
        }

        $this->_json([
            'ok'          => true,
            'message'     => 'Vos informations ont été mises à jour.',
            'utilisateur' => $this->admin->trouver($this->moi['id']),
        ]);
    }

    /** Changement de mot de passe par l'utilisateur lui-même. */
    public function profil_mot_de_passe()
    {
        $this->_exiger_connecte(true);

        $nouveau = (string) $this->input->post('nouveau');

        $this->form_validation->set_rules('actuel', 'Mot de passe actuel', 'required');
        $this->form_validation->set_rules('nouveau', 'Nouveau mot de passe', 'required|min_length[8]|max_length[72]');
        $this->form_validation->set_rules(
            'confirmation',
            'Confirmation',
            'required|matches[nouveau]',
            ['matches' => 'La confirmation ne correspond pas au nouveau mot de passe.']
        );

        if (!$this->form_validation->run()) {
            return $this->_json([
                'ok'      => false,
                'message' => 'Veuillez corriger les champs indiqués.',
                'erreurs' => $this->form_validation->error_array(),
            ], 422);
        }

        if ($faiblesse = $this->_faiblesse_mot_de_passe($nouveau)) {
            return $this->_json(['ok' => false, 'message' => $faiblesse, 'erreurs' => ['nouveau' => $faiblesse]], 422);
        }
        if (!$this->_verifier_mot_de_passe_actuel('actuel')) {
            return;
        }
        if (password_verify($nouveau, $this->admin->hash_mot_de_passe($this->moi['id']))) {
            $msg = 'Le nouveau mot de passe doit être différent de l\'actuel.';
            return $this->_json(['ok' => false, 'message' => $msg, 'erreurs' => ['nouveau' => $msg]], 422);
        }

        $this->admin->modifier($this->moi['id'], [
            'mot_de_passe'        => password_hash($nouveau, PASSWORD_DEFAULT),
            'tentatives_echouees' => 0,
            'bloque_jusqua'       => null,
        ]);
        $this->admin->invalider_liens_reset($this->moi['id']);
        $this->admin->journaliser($this->moi['id'], 'changement_mot_de_passe', 'utilisateur', $this->moi['id']);

        // Nouvel identifiant de session (protection contre la fixation de session)
        $this->session->sess_regenerate(true);

        $this->_json(['ok' => true, 'message' => 'Votre mot de passe a été changé.']);
    }

    /** Mes dernières activités (onglet « Mon activité »). */
    public function profil_activite()
    {
        $this->_exiger_connecte(true);

        $f = ['utilisateur_id' => $this->moi['id']];
        $this->_json([
            'ok'     => true,
            'lignes' => $this->admin->journal_lister($f, 20, 0),
            'total'  => $this->admin->journal_compter($f),
        ]);
    }

    /**
     * Vérifie le mot de passe actuel saisi. Renvoie true si correct ; sinon prépare la réponse d'erreur et renvoie false.
     * Après PROFIL_ESSAIS_MAX échecs, les tentatives sont refusées pendant PROFIL_BLOCAGE_MIN minutes.
     */
    private function _verifier_mot_de_passe_actuel($champ)
    {
        $cle    = 'profil_echecs_mdp';
        $etat   = $this->session->userdata($cle) ?: ['n' => 0, 'jusqua' => 0];

        if ($etat['jusqua'] > time()) {
            $min = (int) ceil(($etat['jusqua'] - time()) / 60);
            $this->_json([
                'ok' => false,
                'message' => 'Trop de tentatives incorrectes. Réessayez dans ' . $min . ' minute' . ($min > 1 ? 's' : '') . '.'
            ], 429);
            return false;
        }

        if (password_verify((string) $this->input->post($champ), $this->admin->hash_mot_de_passe($this->moi['id']))) {
            $this->session->unset_userdata($cle);
            return true;
        }

        $etat['n']++;
        if ($etat['n'] >= self::PROFIL_ESSAIS_MAX) {
            $etat = ['n' => 0, 'jusqua' => time() + self::PROFIL_BLOCAGE_MIN * 60];
        }
        $this->session->set_userdata($cle, $etat);
        $this->admin->journaliser($this->moi['id'], 'echec_changement_mdp', 'utilisateur', $this->moi['id']);

        $msg = 'Mot de passe actuel incorrect.';
        $this->_json(['ok' => false, 'message' => $msg, 'erreurs' => [$champ => $msg]], 422);
        return false;
    }

    /** Règles minimales : 8 caractères, au moins une lettre et un chiffre, différent de l'e-mail. */
    private function _faiblesse_mot_de_passe($mdp)
    {
        if (!preg_match('/[A-Za-z]/', $mdp) || !preg_match('/\d/', $mdp)) {
            return 'Le mot de passe doit contenir au moins une lettre et un chiffre.';
        }
        $local = strtolower(strstr($this->moi['email'], '@', true));
        if ($local !== '' && stripos($mdp, $local) !== false) {
            return 'Le mot de passe ne doit pas contenir votre adresse e-mail.';
        }
        return null;
    }
}
