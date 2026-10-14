<?php

/**
 * application/views/front/compte/acces.php
 * Page unique : onglet "Se connecter" + onglet "Créer un compte"
 * Le header/footer sont ajoutés par le layout public (comme accueil.php).
 */
defined('BASEPATH') or exit('No direct script access allowed');
$estInscription = ($onglet === 'inscription');
?>
<section class="acces">
    <div class="wrap acces__inner">

        <section class="acces__intro">
            <h1>Espace participant</h1>
            <p>Connectez-vous pour suivre votre dossier, ou créez votre compte pour participer au concours.</p>
        </section>

        <div class="acces__carte">
            <div class="acces__onglets" role="tablist" aria-label="Accès au compte">
                <a href="<?= site_url('connexion') ?>" role="tab" id="tab-connexion"
                    class="acces__onglet<?= $estInscription ? '' : ' est-actif' ?>"
                    aria-selected="<?= $estInscription ? 'false' : 'true' ?>" aria-controls="panneau-connexion"
                    data-onglet="connexion">J'ai déjà un compte</a>
                <a href="<?= site_url('inscription') ?>" role="tab" id="tab-inscription"
                    class="acces__onglet<?= $estInscription ? ' est-actif' : '' ?>"
                    aria-selected="<?= $estInscription ? 'true' : 'false' ?>" aria-controls="panneau-inscription"
                    data-onglet="inscription">Créer un compte</a>
            </div>

            <!-- ========== CONNEXION ========== -->
            <section class="acces__panneau" id="panneau-connexion" role="tabpanel" aria-labelledby="tab-connexion"
                <?= $estInscription ? 'hidden' : '' ?>>

                <?php if ($msg = $this->session->flashdata('erreur_connexion')): ?>
                <div class="alerte alerte--erreur" role="alert"><?= html_escape($msg) ?></div>
                <?php endif; ?>

                <?= form_open('connexion', ['class' => 'formulaire', 'novalidate' => 'novalidate']) ?>
                <div class="champ">
                    <label for="cx-email">E-mail</label>
                    <input type="email" id="cx-email" name="email" autocomplete="email" required
                        value="<?= $estInscription ? '' : html_escape(set_value('email')) ?>">
                    <?= $estInscription ? '' : form_error('email') ?>
                </div>

                <div class="champ">
                    <label for="cx-mdp">Mot de passe</label>
                    <div class="champ__mdp">
                        <input type="password" id="cx-mdp" name="mot_de_passe" autocomplete="current-password" required>
                        <button type="button" class="champ__voir" data-cible="cx-mdp"
                            aria-label="Afficher le mot de passe">Voir</button>
                    </div>
                    <?= $estInscription ? '' : form_error('mot_de_passe') ?>
                </div>

                <button type="submit" class="btn btn--vert acces__envoyer">Se connecter</button>
                <?= form_close() ?>

                <p class="acces__bascule">Pas encore de compte ?
                    <a href="<?= site_url('inscription') ?>" data-onglet="inscription">Créer un compte</a>
                </p>
            </section>

            <!-- ========== INSCRIPTION ========== -->
            <section class="acces__panneau" id="panneau-inscription" role="tabpanel" aria-labelledby="tab-inscription"
                <?= $estInscription ? '' : 'hidden' ?>>

                <?php if (empty($inscriptionsOuvertes)): ?>
                <div class="alerte alerte--info">Les inscriptions sont actuellement fermées.</div>
                <?php else: ?>
                <?= form_open('inscription', ['class' => 'formulaire', 'novalidate' => 'novalidate']) ?>
                <div class="formulaire__ligne">
                    <div class="champ">
                        <label for="in-prenom">Prénom</label>
                        <input type="text" id="in-prenom" name="prenom" autocomplete="given-name" required
                            value="<?= html_escape(set_value('prenom')) ?>">
                        <?= form_error('prenom') ?>
                    </div>
                    <div class="champ">
                        <label for="in-nom">Nom</label>
                        <input type="text" id="in-nom" name="nom" autocomplete="family-name" required
                            value="<?= html_escape(set_value('nom')) ?>">
                        <?= form_error('nom') ?>
                    </div>
                </div>

                <div class="champ">
                    <label for="in-email">E-mail</label>
                    <input type="email" id="in-email" name="email" autocomplete="email" required
                        value="<?= $estInscription ? html_escape(set_value('email')) : '' ?>">
                    <?= $estInscription ? form_error('email') : '' ?>
                </div>

                <div class="formulaire__ligne">
                    <div class="champ">
                        <label for="in-tel">Téléphone <small>(facultatif)</small></label>
                        <input type="tel" id="in-tel" name="telephone" autocomplete="tel"
                            value="<?= html_escape(set_value('telephone')) ?>">
                        <?= form_error('telephone') ?>
                    </div>
                    <div class="champ">
                        <label for="in-univ">Université</label>
                        <input type="text" id="in-univ" name="universite" required
                            value="<?= html_escape(set_value('universite')) ?>">
                        <?= form_error('universite') ?>
                    </div>
                </div>

                <div class="formulaire__ligne">
                    <div class="champ">
                        <label for="in-mdp">Mot de passe</label>
                        <div class="champ__mdp">
                            <input type="password" id="in-mdp" name="mot_de_passe" autocomplete="new-password"
                                minlength="8" required aria-describedby="in-mdp-aide">
                            <button type="button" class="champ__voir" data-cible="in-mdp"
                                aria-label="Afficher le mot de passe">Voir</button>
                        </div>
                        <small id="in-mdp-aide" class="champ__aide">8 caractères minimum.</small>
                        <?= $estInscription ? form_error('mot_de_passe') : '' ?>
                    </div>
                    <div class="champ">
                        <label for="in-conf">Confirmer</label>
                        <input type="password" id="in-conf" name="confirmation" autocomplete="new-password" required>
                        <?= form_error('confirmation') ?>
                    </div>
                </div>

                <label class="case">
                    <input type="checkbox" name="cgu" value="1" <?= set_checkbox('cgu', '1') ?>>
                    J'accepte le <a href="<?= site_url() ?>#documents" target="_blank">règlement du concours</a>
                </label>
                <?= form_error('cgu') ?>

                <button type="submit" class="btn btn--vert acces__envoyer">Créer mon compte</button>
                <?= form_close() ?>
                <?php endif; ?>

                <p class="acces__bascule">Déjà inscrit ?
                    <a href="<?= site_url('connexion') ?>" data-onglet="connexion">Se connecter</a>
                </p>
            </section>
        </div>
    </div>
</section>

<script>
(function() {
    var urls = {
        connexion: '<?= site_url('connexion') ?>',
        inscription: '<?= site_url('inscription') ?>'
    };

    function activer(nom) {
        document.querySelectorAll('.acces__onglet').forEach(function(t) {
            var actif = t.dataset.onglet === nom;
            t.classList.toggle('est-actif', actif);
            t.setAttribute('aria-selected', actif);
        });
        document.getElementById('panneau-connexion').hidden = nom !== 'connexion';
        document.getElementById('panneau-inscription').hidden = nom !== 'inscription';
        history.replaceState(null, '', urls[nom]);
        var champ = document.querySelector('#panneau-' + nom + ' input');
        if (champ) champ.focus();
    }

    // Bascule sans rechargement (les liens restent fonctionnels sans JS)
    document.querySelectorAll('[data-onglet]').forEach(function(lien) {
        lien.addEventListener('click', function(e) {
            e.preventDefault();
            activer(lien.dataset.onglet);
        });
    });

    // Afficher / masquer le mot de passe
    document.querySelectorAll('.champ__voir').forEach(function(b) {
        b.addEventListener('click', function() {
            var i = document.getElementById(b.dataset.cible),
                visible = i.type === 'text';
            i.type = visible ? 'password' : 'text';
            b.textContent = visible ? 'Voir' : 'Masquer';
        });
    });
})();
</script>