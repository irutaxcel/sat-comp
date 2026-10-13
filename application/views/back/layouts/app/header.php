<?php
/*
 * Variables fournies par Back_Controller : $currentUser, $isAdmin, $concours, $pendingCount
 * Variable fournie par le contrôleur : $title
 */

// Échéance des soumissions affichée dans la barre du haut
$deadline = null;
if (!empty($concours) && $concours->date_limite_soumission) {
    $ts       = strtotime($concours->date_limite_soumission);
    $jours    = (int) floor(($ts - time()) / 86400);
    $deadline = [
        'date'  => date('d/m/Y à H:i', $ts),
        'passe' => $ts <= time(),
        'jours' => $jours,
        'class' => $ts <= time() ? 'badge-danger' : ($jours <= 7 ? 'badge-warning' : 'badge-success'),
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($title ?? 'Back-office') ?> | Concours d'architecture SATRACO</title>

    <link rel="icon" type="image/png" href="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/plugins/') ?>fontawesome-free/css/all.min.css">
    <!-- iCheck -->
    <link rel="stylesheet"
        href="<?= base_url('assets/v2/adminlte/plugins/') ?>icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet"
        href="<?= base_url('assets/v2/adminlte/plugins/') ?>overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/dist/') ?>css/adminlte.min.css">
    <!-- Personnalisation SATRACO (toujours en dernier) -->
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/dist/css/satraco-admin.css') ?>">
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">

        <!-- Préchargement -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>"
                alt="SATRACO Construction" height="70">
        </div>

        <!-- Barre du haut -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">

            <!-- Gauche -->
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button" title="Réduire le menu">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-inline-block">
                    <span class="nav-link navbar-concours">
                        <i class="fas fa-drafting-compass mr-1"></i>
                        <?= !empty($concours) ? html_escape($concours->titre) : 'Aucun concours configuré' ?>
                    </span>
                </li>
            </ul>

            <!-- Droite -->
            <ul class="navbar-nav ml-auto align-items-center">

                <!-- Échéance des soumissions -->
                <?php if ($deadline): ?>
                    <li class="nav-item d-none d-lg-inline-block mr-2">
                        <span class="badge badge-deadline <?= $deadline['class'] ?>"
                            title="Date limite de soumission : <?= $deadline['date'] ?>">
                            <i class="far fa-clock mr-1"></i>
                            <?php if ($deadline['passe']): ?>
                                Soumissions clôturées
                            <?php elseif ($deadline['jours'] < 1): ?>
                                Clôture aujourd'hui
                            <?php else: ?>
                                Clôture dans <?= $deadline['jours'] ?> jour<?= $deadline['jours'] > 1 ? 's' : '' ?>
                            <?php endif; ?>
                        </span>
                    </li>
                <?php endif; ?>

                <!-- Lien vers le site public -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url() ?>" target="_blank" rel="noopener"
                        title="Voir le site public">
                        <i class="fas fa-globe-africa"></i>
                    </a>
                </li>

                <!-- Notifications (admin) : comptes à valider -->
                <?php if ($isAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-toggle="dropdown" href="#" title="Notifications">
                            <i class="far fa-bell"></i>
                            <?php if ($pendingCount > 0): ?>
                                <span class="badge badge-warning navbar-badge"><?= $pendingCount ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <span class="dropdown-item dropdown-header">
                                <?= $pendingCount ?> notification<?= $pendingCount > 1 ? 's' : '' ?>
                            </span>
                            <div class="dropdown-divider"></div>
                            <?php if ($pendingCount > 0): ?>
                                <a href="<?= site_url('candidats/en-attente') ?>" class="dropdown-item">
                                    <i class="fas fa-user-clock mr-2 text-warning"></i>
                                    <?= $pendingCount ?> compte<?= $pendingCount > 1 ? 's' : '' ?> à valider
                                </a>
                            <?php else: ?>
                                <span class="dropdown-item text-muted">
                                    <i class="fas fa-check-circle mr-2 text-success"></i> Rien à traiter
                                </span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Plein écran -->
                <li class="nav-item d-none d-sm-inline-block">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Plein écran">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <!-- Menu utilisateur -->
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                        <span class="user-avatar user-avatar-sm mr-2">
                            <?= html_escape(user_initials($currentUser->prenom, $currentUser->nom)) ?>
                        </span>
                        <span class="d-none d-md-inline"><?= html_escape($currentUser->prenom) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header">
                            <span class="user-avatar user-avatar-lg">
                                <?= html_escape(user_initials($currentUser->prenom, $currentUser->nom)) ?>
                            </span>
                            <p>
                                <?= html_escape($currentUser->prenom . ' ' . $currentUser->nom) ?>
                                <small><?= html_escape($currentUser->role_libelle) ?></small>
                                <small><?= html_escape($currentUser->email) ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="<?= site_url('profil') ?>" class="btn btn-default btn-flat">
                                <i class="fas fa-user-cog mr-1"></i> Mon profil
                            </a>
                            <a href="<?= site_url('logout') ?>" class="btn btn-default btn-flat float-right">
                                <i class="fas fa-sign-out-alt mr-1"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->