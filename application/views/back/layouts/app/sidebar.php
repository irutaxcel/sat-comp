<?php
/*
 * Menu latéral du back-office.
 * Variables fournies par Back_Controller : $currentUser, $isAdmin, $pendingCount
 * L'élément actif est détecté automatiquement par nav_active() (helpers/nav_helper.php).
 */
?>
<!-- Menu latéral -->
<aside class="main-sidebar sidebar-dark-success elevation-4 sidebar-satraco">

    <!-- Logo -->
    <a href="<?= site_url('dashboard') ?>" class="brand-link">
        <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="SATRACO Construction"
            class="brand-image">
        <span class="brand-text">
            <strong>SATRACO</strong>
            <small class="d-block">Back-office concours</small>
        </span>
    </a>

    <div class="sidebar">

        <!-- Utilisateur connecté -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <span class="user-avatar user-avatar-sm">
                    <?= html_escape(user_initials($currentUser->prenom, $currentUser->nom)) ?>
                </span>
            </div>
            <div class="info">
                <a href="<?= site_url('profil') ?>" class="d-block">
                    <?= html_escape($currentUser->prenom . ' ' . $currentUser->nom) ?>
                </a>
                <small class="role-label"><?= html_escape($currentUser->role_libelle) ?></small>
            </div>
        </div>

        <!-- Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <a href="<?= site_url('dashboard') ?>" class="nav-link <?= nav_active('dashboard') ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Tableau de bord</p>
                    </a>
                </li>

                <?php if ($isAdmin): ?>

                    <!-- ===================== CONCOURS ===================== -->
                    <li class="nav-header">CONCOURS</li>

                    <li class="nav-item">
                        <a href="<?= site_url('concours-parametres') ?>"
                            class="nav-link <?= nav_active('concours-parametres') ?>">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>Paramètres</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('concours-agenda') ?>" class="nav-link <?= nav_active('concours-agenda') ?>">
                            <i class="nav-icon far fa-calendar-alt"></i>
                            <p>Dates clés</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('concours-prix') ?>" class="nav-link <?= nav_active('concours-prix') ?>">
                            <i class="nav-icon fas fa-trophy"></i>
                            <p>Prix à gagner</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('concours-ressources') ?>"
                            class="nav-link <?= nav_active('concours-ressources') ?>">
                            <i class="nav-icon fas fa-folder-open"></i>
                            <p>Brief & ressources</p>
                        </a>
                    </li>

                    <!-- ===================== CANDIDATURES ===================== -->
                    <li class="nav-header">CANDIDATURES</li>

                    <li class="nav-item">
                        <a href="<?= site_url('candidats-en-attente') ?>"
                            class="nav-link <?= nav_active('candidats-en-attente') ?>">
                            <i class="nav-icon fas fa-user-clock"></i>
                            <p>
                                Comptes à valider
                                <?php if ($pendingCount > 0): ?>
                                    <span class="badge badge-warning right"><?= $pendingCount ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('candidats') ?>" class="nav-link <?= nav_active('candidats', true) ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Candidats & équipes</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('projets') ?>" class="nav-link <?= nav_active('projets') ?>">
                            <i class="nav-icon fas fa-file-archive"></i>
                            <p>Projets soumis</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('exports') ?>" class="nav-link <?= nav_active('exports') ?>">
                            <i class="nav-icon fas fa-file-export"></i>
                            <p>Exports</p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- ===================== ÉVALUATION ===================== -->
                <li class="nav-header">ÉVALUATION</li>

                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a href="<?= site_url('evaluation-criteres') ?>"
                            class="nav-link <?= nav_active('evaluation-criteres') ?>">
                            <i class="nav-icon fas fa-list-ol"></i>
                            <p>Critères de notation</p>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?= site_url('evaluation') ?>" class="nav-link <?= nav_active('evaluation', true) ?>">
                            <i class="nav-icon fas fa-star-half-alt"></i>
                            <p>Projets à évaluer</p>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="<?= site_url('evaluation-classement') ?>"
                        class="nav-link <?= nav_active('evaluation-classement') ?>">
                        <i class="nav-icon fas fa-medal"></i>
                        <p>Classement</p>
                    </a>
                </li>

                <?php if ($isAdmin): ?>
                    <!-- ===================== ADMINISTRATION ===================== -->
                    <li class="nav-header">ADMINISTRATION</li>

                    <li class="nav-item">
                        <a href="<?= site_url('utilisateurs') ?>" class="nav-link <?= nav_active('utilisateurs') ?>">
                            <i class="nav-icon fas fa-user-shield"></i>
                            <p>Administrateurs & jury</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('journal') ?>" class="nav-link <?= nav_active('journal') ?>">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Journal d'activité</p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- ===================== MON COMPTE ===================== -->
                <li class="nav-header">MON COMPTE</li>

                <li class="nav-item">
                    <a href="<?= site_url('profil') ?>" class="nav-link <?= nav_active('profil') ?>">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>Mon profil</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= site_url('logout') ?>" class="nav-link nav-logout">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Déconnexion</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>