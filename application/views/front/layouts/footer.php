<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Layout public — pied de page.
 */
$email = !empty($concours->email_contact) ? $concours->email_contact : null;
$tel   = !empty($concours->telephone_contact) ? $concours->telephone_contact : null;
?>
</main>

<footer class="site-footer" id="contact">
    <div class="wrap site-footer__grid">
        <div class="site-footer__org">
            <img src="<?= base_url('assets/v2/adminlte/dist/img/logoUpdate.png') ?>" alt="SATRACO Construction"
                width="120" height="87" loading="lazy">
            <p>Concours organisé par SATRACO Construction, Bujumbura.</p>
        </div>

        <div>
            <h2 class="site-footer__titre">Une question sur le concours&nbsp;?</h2>
            <?php if ($email || $tel) : ?>
                <ul class="contact-liste">
                    <?php if ($email) : ?>
                        <li>
                            <i class="far fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:<?= html_escape($email) ?>"><?= html_escape($email) ?></a>
                        </li>
                    <?php endif; ?>
                    <?php if ($tel) : ?>
                        <li>
                            <i class="fas fa-phone-alt" aria-hidden="true"></i>
                            <a href="tel:<?= html_escape(preg_replace('/[^\d+]/', '', $tel)) ?>"><?= html_escape($tel) ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php else : ?>
                <p>Les coordonnées de l'équipe organisatrice seront bientôt publiées.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="site-footer__titre">Espace candidat</h2>
            <ul class="footer-liens">
                <li><a href="<?= site_url('connexion') ?>">Se connecter</a></li>
                <?php if (!empty($inscriptionsOuvertes)) : ?>
                    <li><a href="<?= site_url('inscription') ?>">Créer un compte</a></li>
                <?php endif; ?>
                <?php if (!empty($concours) && fichier_public($concours->reglement_pdf)) : ?>
                    <li><a href="<?= fichier_public($concours->reglement_pdf) ?>" target="_blank" rel="noopener">Règlement
                            (PDF)</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="wrap site-footer__bas">
        <span>&copy; <?= date('Y') ?> SATRACO Construction</span>
        <a href="#haut">Haut de page</a>
    </div>
</footer>

<script src="<?= front_asset('js/front.js') ?>" defer></script>
</body>

</html>