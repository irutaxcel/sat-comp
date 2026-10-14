<?php

/**
 * application/views/front/compte/espace.php — page d'arrivée après connexion (à enrichir)
 * Le header/footer sont ajoutés par le layout public (comme accueil.php).
 */
defined('BASEPATH') or exit('No direct script access allowed');
?>
<section class="acces">
    <div class="wrap">
        <?php if ($msg = $this->session->flashdata('succes')): ?>
            <div class="alerte alerte--succes" role="status"><?= html_escape($msg) ?></div>
        <?php endif; ?>

        <h1>Bonjour <?= html_escape($participant->prenom) ?></h1>
        <p>Université : <?= html_escape($participant->universite) ?></p>

        <a class="btn btn--vert" href="<?= site_url('deconnexion') ?>">Se déconnecter</a>
    </div>
</section>