/* SATRACO — Concours d'architecture — Front-office */
(function () {
    'use strict';

    /* Menu mobile ------------------------------------------------------- */
    var bouton = document.querySelector('.nav-toggle');
    var menu = document.getElementById('menu-principal');

    if (bouton && menu) {
        var fermer = function () {
            menu.classList.remove('is-open');
            bouton.setAttribute('aria-expanded', 'false');
        };

        bouton.addEventListener('click', function () {
            var ouvert = menu.classList.toggle('is-open');
            bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
        });

        // Ferme le menu après un clic sur une ancre de la page
        menu.addEventListener('click', function (e) {
            if (e.target.closest('a')) fermer();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('is-open')) {
                fermer();
                bouton.focus();
            }
        });
    }

    /* Compte à rebours ---------------------------------------------------
       Le nombre de secondes restantes est calculé par le SERVEUR : l'horloge
       du visiteur (souvent fausse) n'influence pas l'échéance affichée.   */
    var compte = document.querySelector('.compte[data-secondes]');

    if (compte) {
        var fin = Date.now() + parseInt(compte.getAttribute('data-secondes'), 10) * 1000;
        var champs = {
            j: compte.querySelector('[data-unite="j"]'),
            h: compte.querySelector('[data-unite="h"]'),
            m: compte.querySelector('[data-unite="m"]'),
            s: compte.querySelector('[data-unite="s"]')
        };
        var message = compte.parentNode.querySelector('.compte__fin');
        var deux = function (n) { return n < 10 ? '0' + n : String(n); };
        var minuterie;

        var maj = function () {
            var reste = Math.max(0, Math.floor((fin - Date.now()) / 1000));

            champs.j.textContent = Math.floor(reste / 86400);
            champs.h.textContent = deux(Math.floor(reste % 86400 / 3600));
            champs.m.textContent = deux(Math.floor(reste % 3600 / 60));
            champs.s.textContent = deux(reste % 60);

            if (reste === 0) {
                clearInterval(minuterie);
                if (message) message.hidden = false;
            }
        };

        maj();
        minuterie = setInterval(maj, 1000);
    }
})();