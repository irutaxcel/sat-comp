<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title) ?> | Concours d'architecture SATRACO</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/') ?>plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/') ?>dist/css/adminlte.min.css">
    <style>
        .navbar-satraco {
            background: #3f8a42;
        }

        .navbar-satraco .nav-link,
        .navbar-satraco .navbar-text {
            color: #fff !important;
        }
    </style>
</head>

<!-- Page provisoire pour tester l'authentification : sera remplacée par le vrai layout AdminLTE -->

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-satraco">
            <span class="navbar-brand text-white font-weight-bold">Concours SATRACO</span>
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item">
                    <span class="navbar-text mr-3">
                        <i class="fas fa-user-circle mr-1"></i>
                        <?= html_escape($currentUser->prenom . ' ' . $currentUser->nom) ?>
                        <small>(<?= html_escape($currentUser->role_libelle) ?>)</small>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('logout') ?>">
                        <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <h1 class="m-0">Tableau de bord</h1>
                </div>
            </div>
            <div class="content">
                <div class="container">
                    <div class="callout callout-success">
                        <h5>Bienvenue, <?= html_escape($currentUser->prenom) ?> !</h5>
                        <p class="mb-0">L'authentification fonctionne. Dernière connexion enregistrée :
                            <?= html_escape(date('d/m/Y à H:i', strtotime($currentUser->derniere_connexion))) ?>.</p>
                    </div>

                    <div class="card card-outline" style="border-top: 3px solid #e6de84;">
                        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                            <div class="mb-2 mb-md-0">
                                <strong><?= html_escape($currentUser->email) ?></strong><br>
                                <span class="text-muted">Rôle : <?= html_escape($currentUser->role_libelle) ?></span>
                            </div>
                            <a href="<?= site_url('logout') ?>" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt mr-1"></i> Se déconnecter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>