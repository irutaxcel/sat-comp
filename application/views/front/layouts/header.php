<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Layout public — en-tête.
 * Variables globales (Public_Controller) : $concours, $phase, $inscriptionsOuvertes
 * Variables de page : $title, $metaDescription (optionnelle)
 */
$nomSite  = 'Concours d\'architecture SATRACO';
$titre    = !empty($title) ? $title . ' | ' . $nomSite : $nomSite;
$metaDesc = $metaDescription
    ?? (!empty($concours->sous_titre) ? $concours->sous_titre : 'Plateforme officielle d\'inscription et de dépôt des projets du concours d\'architecture organisé par SATRACO Construction.');
$accueil  = site_url();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= html_escape($titre) ?></title>
    <meta name="description" content="<?= html_escape(character_limiter(strip_tags($metaDesc), 155)) ?>">

    <meta property="og:title" content="<?= html_escape($titre) ?>">
    <meta property="og:description" content="<?= html_escape(character_limiter(strip_tags($metaDesc), 155)) ?>">
    <?php if (!empty($banniere)) : ?>
        <meta property="og:image" content="<?= html_escape($banniere) ?>">
    <?php endif; ?>

    <link rel="icon" type="image/png" href="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&display=swap">
    <link rel="stylesheet" href="<?= base_url('assets/v2/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= front_asset('css/front.css') ?>">
</head>

<body>
    <a class="skip-link" href="#contenu">Aller au contenu</a>

    <header class="site-header" id="haut">
        <div class="wrap site-header__inner">
            <a class="brand" href="<?= $accueil ?>" aria-label="Accueil — <?= html_escape($nomSite) ?>">
                <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="" width="58" height="42">
                <span class="brand__text">
                    <strong>SATRACO</strong>
                    <span>Concours d'architecture</span>
                </span>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-principal">
                <span class="nav-toggle__bar" aria-hidden="true"></span>
                <span class="sr-only">Menu</span>
            </button>

            <nav class="site-nav" id="menu-principal" aria-label="Navigation principale">
                <?php if (!empty($concours)) : ?>
                    <ul class="site-nav__links">
                        <li><a href="<?= $accueil ?>#concours">Le concours</a></li>
                        <li><a href="<?= $accueil ?>#participer">Participer</a></li>
                        <li><a href="<?= $accueil ?>#calendrier">Calendrier</a></li>
                        <li><a href="<?= $accueil ?>#prix">Prix</a></li>
                        <li><a href="<?= $accueil ?>#documents">Documents</a></li>
                    </ul>
                <?php endif; ?>
                <div class="site-nav__compte">
                    <a class="lien-compte" href="<?= site_url('connexion') ?>">Se connecter</a>
                    <?php if (!empty($inscriptionsOuvertes)) : ?>
                        <a class="btn btn--vert btn--petit" href="<?= site_url('inscription') ?>">S'inscrire</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main id="contenu">