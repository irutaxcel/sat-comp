<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title ?? 'Connexion') ?> | Concours d'architecture SATRACO</title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/img/favicon-satraco.png') ?>">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/') ?>plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet"
        href="<?= base_url('assets/v2/adminlte/') ?>plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/') ?>dist/css/adminlte.min.css">
    <!-- Personnalisation SATRACO (toujours en dernier) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/satraco-auth.css') ?>">

    <style>
    /* =========================================================
   SATRACO – Concours d'architecture
   Personnalisation de la page de connexion (AdminLTE 3)
   Emplacement : assets/css/satraco-auth.css
   ========================================================= */

    :root {
        --satraco-green: #76c477;
        /* vert du logo */
        --satraco-green-dark: #3f8a42;
        /* vert foncé (boutons, contraste texte blanc) */
        --satraco-green-darker: #2f6b32;
        /* survol */
        --satraco-green-light: #eaf6ea;
        /* fonds légers */
        --satraco-yellow: #e6de84;
        /* jaune de la maison du logo */
        --satraco-black: #1d1d1b;
        /* noir du texte "SATRACO CONSTRUCTION" */
        --satraco-gray: #6c757d;
    }

    body.login-page {
        font-family: 'Source Sans Pro', sans-serif;
        background:
            linear-gradient(135deg, rgba(47, 107, 50, .92) 0%, rgba(118, 196, 119, .85) 100%),
            url('../img/auth-bg.jpg') center / cover no-repeat;
        min-height: 100vh;
    }

    /* Carte */
    .login-box {
        width: 420px;
        max-width: calc(100% - 32px);
    }

    .login-box .card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(0, 0, 0, .25);
    }

    .login-box .card-outline.card-primary {
        border-top: 5px solid var(--satraco-yellow);
    }

    .login-box .card-header {
        background: #fff;
        border-bottom: 1px solid var(--satraco-green-light);
        padding: 1.75rem 1.5rem 1.25rem;
    }

    .auth-logo {
        max-width: 210px;
        height: auto;
    }

    .auth-badge {
        display: inline-block;
        margin-top: .9rem;
        padding: .25rem .8rem;
        border-radius: 50px;
        background: var(--satraco-green-light);
        color: var(--satraco-green-dark);
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .auth-title {
        margin: .6rem 0 0;
        color: var(--satraco-black);
        font-size: 1.15rem;
        font-weight: 700;
    }

    .login-box-msg {
        color: var(--satraco-gray);
        padding: 0 0 1.25rem;
    }

    /* Champs */
    .login-box .form-control {
        height: calc(2.5rem + 2px);
        border-color: #dfe5df;
    }

    .login-box .form-control:focus {
        border-color: var(--satraco-green);
        box-shadow: 0 0 0 .2rem rgba(118, 196, 119, .25);
    }

    .login-box .form-control:focus+.input-group-append .input-group-text {
        border-color: var(--satraco-green);
        color: var(--satraco-green-dark);
    }

    .login-box .input-group-text {
        background: #fff;
        border-color: #dfe5df;
        color: var(--satraco-gray);
        min-width: 44px;
        justify-content: center;
    }

    .toggle-password {
        cursor: pointer;
    }

    /* Bouton principal */
    .btn-satraco {
        background: var(--satraco-green-dark);
        border-color: var(--satraco-green-dark);
        color: #fff;
        font-weight: 600;
        height: calc(2.5rem + 2px);
    }

    .btn-satraco:hover,
    .btn-satraco:focus {
        background: var(--satraco-green-darker);
        border-color: var(--satraco-green-darker);
        color: #fff;
    }

    .btn-satraco:focus {
        box-shadow: 0 0 0 .2rem rgba(118, 196, 119, .45);
    }

    /* Case à cocher iCheck */
    .icheck-primary>input:first-child:checked+label::before,
    .icheck-primary>input:first-child:checked+input[type="hidden"]+label::before {
        background-color: var(--satraco-green-dark);
        border-color: var(--satraco-green-dark);
    }

    .icheck-primary>input:first-child:not(:checked):not(:disabled):hover+label::before {
        border-color: var(--satraco-green);
    }

    /* Liens */
    .login-box a.auth-link {
        color: var(--satraco-green-dark);
        font-weight: 600;
    }

    .login-box a.auth-link:hover {
        color: var(--satraco-green-darker);
        text-decoration: underline;
    }

    .auth-links {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--satraco-green-light);
        font-size: .92rem;
    }

    /* Règles du mot de passe */
    .password-rules li {
        color: var(--satraco-gray);
        margin-bottom: .2rem;
        transition: color .2s;
    }

    .password-rules li i {
        font-size: .45rem;
        vertical-align: middle;
        margin-right: .45rem;
    }

    .password-rules li.valid {
        color: var(--satraco-green-dark);
        font-weight: 600;
    }

    .password-rules li.valid i::before {
        content: "\f00c";
        /* coche Font Awesome */
        font-size: .75rem;
    }

    /* Alertes */
    .alert {
        border-radius: 8px;
        font-size: .92rem;
    }

    /* Pied de page */
    .auth-footer {
        margin-top: 1.25rem;
        text-align: center;
        color: rgba(255, 255, 255, .9);
        font-size: .85rem;
    }

    .auth-footer em {
        color: var(--satraco-yellow);
        font-style: italic;
    }
    </style>
</head>