<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">

            <div class="card-header text-center">
                <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="SATRACO Construction"
                    class="auth-logo">
                <div>
                    <span class="auth-badge"><i class="fas fa-drafting-compass mr-1"></i> Concours d'architecture</span>
                </div>
                <h1 class="auth-title">Espace d'administration</h1>
            </div>

            <div class="card-body">
                <p class="login-box-msg">Connectez-vous pour gérer les candidatures et les projets soumis.</p>

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

                <?= form_open('authenticate', ['autocomplete' => 'on', 'novalidate' => 'novalidate']) ?>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control <?= form_error('email') ? 'is-invalid' : '' ?>"
                        placeholder="Adresse e-mail" value="<?= set_value('email') ?>" autofocus required>
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                    <?= form_error('email', '<div class="invalid-feedback d-block">', '</div>') ?>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" id="password"
                        class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
                        placeholder="Mot de passe" required>
                    <div class="input-group-append">
                        <div class="input-group-text toggle-password" title="Afficher / masquer le mot de passe">
                            <span class="fas fa-eye" id="togglePasswordIcon"></span>
                        </div>
                    </div>
                    <?= form_error('password', '<div class="invalid-feedback d-block">', '</div>') ?>
                </div>

                <div class="row align-items-center">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember" value="1">
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-satraco btn-block">
                            Se connecter
                        </button>
                    </div>
                </div>

                <?= form_close() ?>

                <div class="auth-links">
                    <a href="<?= site_url('forgot-password') ?>" class="auth-link">
                        <i class="fas fa-key mr-1"></i> Mot de passe oublié ?
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