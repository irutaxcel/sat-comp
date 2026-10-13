<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">

            <div class="card-header text-center">
                <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="SATRACO Construction"
                    class="auth-logo">
                <div>
                    <span class="auth-badge"><i class="fas fa-drafting-compass mr-1"></i> Concours d'architecture</span>
                </div>
                <h1 class="auth-title">Nouveau mot de passe</h1>
            </div>

            <div class="card-body">
                <p class="login-box-msg">
                    Choisissez un nouveau mot de passe pour votre compte administrateur.
                </p>

                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <?= html_escape($this->session->flashdata('error')) ?>
                </div>
                <?php endif; ?>

                <?= form_open('reset-password/update', ['novalidate' => 'novalidate']) ?>

                <input type="hidden" name="token" value="<?= html_escape($token) ?>">

                <div class="input-group mb-3">
                    <input type="password" name="password" id="password"
                        class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
                        placeholder="Nouveau mot de passe" autocomplete="new-password" autofocus required>
                    <div class="input-group-append">
                        <div class="input-group-text toggle-password" data-target="#password"
                            title="Afficher / masquer le mot de passe">
                            <span class="fas fa-eye"></span>
                        </div>
                    </div>
                    <?= form_error('password', '<div class="invalid-feedback d-block">', '</div>') ?>
                </div>

                <div class="input-group mb-2">
                    <input type="password" name="password_confirm" id="password_confirm"
                        class="form-control <?= form_error('password_confirm') ? 'is-invalid' : '' ?>"
                        placeholder="Confirmer le mot de passe" autocomplete="new-password" required>
                    <div class="input-group-append">
                        <div class="input-group-text toggle-password" data-target="#password_confirm"
                            title="Afficher / masquer le mot de passe">
                            <span class="fas fa-eye"></span>
                        </div>
                    </div>
                    <?= form_error('password_confirm', '<div class="invalid-feedback d-block">', '</div>') ?>
                </div>

                <ul class="password-rules list-unstyled small mb-3">
                    <li data-rule="length"><i class="fas fa-circle"></i> Au moins 8 caractères</li>
                    <li data-rule="upper"><i class="fas fa-circle"></i> Une lettre majuscule</li>
                    <li data-rule="digit"><i class="fas fa-circle"></i> Un chiffre</li>
                    <li data-rule="match"><i class="fas fa-circle"></i> Les deux mots de passe correspondent</li>
                </ul>

                <button type="submit" class="btn btn-satraco btn-block">
                    <i class="fas fa-lock mr-1"></i> Enregistrer le nouveau mot de passe
                </button>

                <?= form_close() ?>

                <div class="auth-links">
                    <a href="<?= site_url('backoffice/signin') ?>" class="auth-link">
                        <i class="fas fa-sign-in-alt mr-1"></i> Retour à la connexion
                    </a>
                </div>
            </div>
        </div>

        <div class="auth-footer">
            &copy; <?= date('Y') ?> SATRACO Construction — <em>Excellence is our passion</em>
        </div>
    </div>