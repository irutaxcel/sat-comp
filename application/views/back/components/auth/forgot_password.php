<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">

            <div class="card-header text-center">
                <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="SATRACO Construction"
                    class="auth-logo">
                <div>
                    <span class="auth-badge"><i class="fas fa-drafting-compass mr-1"></i> Concours d'architecture</span>
                </div>
                <h1 class="auth-title">Mot de passe oublié</h1>
            </div>

            <div class="card-body">
                <p class="login-box-msg">
                    Saisissez l'adresse e-mail de votre compte administrateur.
                    Nous vous enverrons un lien pour définir un nouveau mot de passe.
                </p>

                <?php if ($this->session->flashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <?= html_escape($this->session->flashdata('error')) ?>
                </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-1"></i>
                    <?= html_escape($this->session->flashdata('success')) ?>
                </div>
                <?php endif; ?>

                <?php if (ENVIRONMENT === 'development' && $this->session->flashdata('dev_link')): ?>
                <div class="alert alert-warning small">
                    <strong>Mode développement</strong> — lien de test (n'apparaît pas en production) :<br>
                    <a href="<?= html_escape($this->session->flashdata('dev_link')) ?>">Ouvrir le lien de
                        réinitialisation</a>
                </div>
                <?php endif; ?>

                <?= form_open('forgot-password/send', ['novalidate' => 'novalidate']) ?>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control <?= form_error('email') ? 'is-invalid' : '' ?>"
                        placeholder="Adresse e-mail" value="<?= set_value('email') ?>" autofocus required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                    <?= form_error('email', '<div class="invalid-feedback d-block">', '</div>') ?>
                </div>

                <button type="submit" class="btn btn-satraco btn-block">
                    <i class="fas fa-paper-plane mr-1"></i> Envoyer le lien de réinitialisation
                </button>

                <?= form_close() ?>

                <div class="auth-links">
                    <a href="<?= site_url('sign-in') ?>" class="auth-link">
                        <i class="fas fa-sign-in-alt mr-1"></i> Retour à la connexion
                    </a>
                    <a href="<?= base_url() ?>" class="auth-link">
                        <i class="fas fa-arrow-left mr-1"></i> Retour au concours
                    </a>
                </div>
            </div>
        </div>

        <div class="auth-footer">
            &copy; <?= date('Y') ?> SATRACO Construction — <em>Excellence is our passion</em>
        </div>
    </div>