<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Évaluation › Critères de notation
 * Variables : $title, $etat, $criteres, $csrf
 *
 * Réutilise les partials partagés (_styles, _outils_js → window.CAV) du dossier candidats.
 * ⚠ Aligne le conteneur racine (.content-wrapper) sur tes autres composants si besoin.
 */
$initial = json_encode(['criteres' => $criteres ?? [], 'etat' => $etat ?? []], JSON_UNESCAPED_UNICODE);
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-liste="<?= site_url('evaluation-criteres/liste') ?>"
        data-url-enregistrer="<?= site_url('evaluation-criteres/enregistrer') ?>"
        data-url-supprimer="<?= site_url('evaluation-criteres/supprimer') ?>"
        data-url-actif="<?= site_url('evaluation-criteres/actif') ?>"
        data-url-ordre="<?= site_url('evaluation-criteres/ordre') ?>"
        data-url-repartir="<?= site_url('evaluation-criteres/repartir') ?>"
        data-url-modele="<?= site_url('evaluation-criteres/modele') ?>"
        data-url-verrou="<?= site_url('evaluation-criteres/verrou') ?>"
        data-csrf-name="<?= html_escape($csrf['name']) ?>" data-csrf-hash="<?= html_escape($csrf['hash']) ?>"
        data-initial="<?= html_escape($initial) ?>">

        <header class="cav-head">
            <div>
                <p class="cav-crumb">Évaluation <span>›</span> Critères de notation</p>
                <h1 class="cav-title">Critères de notation <span id="crit-statut"></span></h1>
                <p class="cav-sub">Définissez la grille que le jury utilisera pour noter chaque projet. La somme des
                    pondérations doit faire 100 %.</p>
            </div>
            <div class="crit-head-actions">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="apercu"><i class="fas fa-eye"></i>
                    Aperçu fiche jury</button>
                <span id="crit-btn-verrou"></span>
            </div>
        </header>

        <div class="cav-alert crit-verrou-info" id="crit-verrou-info" hidden></div>

        <!-- ============ INDICATEURS ============ -->
        <div class="cav-kpis">
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-blue"><i class="fas fa-list-ol"></i></span>
                <span><b data-k="actifs">0</b><small>Critères actifs</small><em data-k="inactifs"></em></span>
            </div>
            <div class="cav-kpi crit-kpi-somme" id="crit-kpi-somme">
                <span class="cav-kpi-ico cav-ico-green"><i class="fas fa-balance-scale"></i></span>
                <span class="crit-kpi-txt"><b><span data-k="somme">0</span> %</b><small>Pondération totale</small>
                    <span class="prj-jauge"><i data-k="jauge"></i></span><em data-k="somme-msg"></em></span>
            </div>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-amber"><i class="fas fa-lock"></i></span>
                <span><b data-k="statut">—</b><small>Statut de la grille</small><em data-k="statut-msg"></em></span>
            </div>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-grey"><i class="fas fa-star-half-alt"></i></span>
                <span><b data-k="projets">0</b><small>Projets déjà notés</small><em><span data-k="notes">0</span>
                        note(s) saisie(s)</em></span>
            </div>
        </div>

        <!-- ============ RÉPARTITION ============ -->
        <div class="cav-card crit-repartition" id="crit-repartition" hidden>
            <div class="crit-barre" id="crit-barre" aria-label="Répartition des pondérations"></div>
            <div class="crit-legende" id="crit-legende"></div>
        </div>

        <!-- ============ GRILLE ============ -->
        <div class="cav-card">
            <div class="crit-liste-head">
                <h3>Grille de notation</h3>
                <div class="crit-liste-actions">
                    <button type="button" class="cav-btn cav-btn-ghost" data-action="repartir" data-modif><i
                            class="fas fa-equals"></i> Répartir équitablement</button>
                    <button type="button" class="cav-btn cav-btn-success" data-action="ajouter" data-modif><i
                            class="fas fa-plus"></i> Ajouter un critère</button>
                </div>
            </div>
            <ol class="crit-liste" id="crit-liste"></ol>
            <p class="crit-aide"><i class="fas fa-info-circle"></i> Score d'un projet (sur 100) = Σ (note du critère ÷
                barème × pondération).
                Glissez <i class="fas fa-grip-vertical"></i> pour réordonner les critères ; l'ordre est celui de la
                fiche du jury.</p>
        </div>
    </section>

    <!-- ============ MODALE CRITÈRE ============ -->
    <div class="cav-overlay cav-overlay-modal" id="crit-form-ov" hidden>
        <form class="cav-modal" id="crit-form" role="dialog" aria-modal="true" aria-labelledby="crit-form-titre"
            novalidate>
            <div class="cav-modal-head">
                <h3 id="crit-form-titre">Nouveau critère</h3>
                <button type="button" class="cav-x" data-action="fermer" aria-label="Fermer">&times;</button>
            </div>
            <div class="cav-modal-body">
                <input type="hidden" name="critere_id" value="">
                <div class="cav-alert cav-alert-warn" id="crit-form-verrou" hidden><i class="fas fa-lock"></i>Grille
                    verrouillée : seuls l'intitulé et la description sont modifiables.</div>
                <label class="cav-label" for="crit-nom">Intitulé <span class="cav-req">*</span></label>
                <input class="crit-input" id="crit-nom" name="nom" maxlength="120" required
                    placeholder="Ex. : Qualité architecturale et spatiale">
                <label class="cav-label" for="crit-desc">Ce que le jury doit apprécier</label>
                <textarea class="crit-input" id="crit-desc" name="description" rows="3" maxlength="2000"
                    placeholder="Points d'attention, éléments attendus…"></textarea>
                <div class="crit-form-ligne">
                    <div>
                        <label class="cav-label" for="crit-poids">Pondération <span class="cav-req">*</span></label>
                        <div class="crit-poids">
                            <input type="range" min="0" max="100" step="1" name="poids_range" aria-label="Pondération"
                                data-verrouillable>
                            <span><input class="crit-input" id="crit-poids" type="number" name="poids" min="0" max="100"
                                    step="1" required data-verrouillable> %</span>
                        </div>
                        <small class="cav-muted" id="crit-somme-apres"></small>
                    </div>
                    <div>
                        <label class="cav-label" for="crit-bareme">Barème</label>
                        <select class="crit-input" id="crit-bareme" name="note_max" data-verrouillable>
                            <option value="5">Note sur 5</option>
                            <option value="10">Note sur 10</option>
                            <option value="20" selected>Note sur 20</option>
                            <option value="100">Note sur 100</option>
                        </select>
                    </div>
                </div>
                <label class="crit-switch"><input type="checkbox" name="actif" value="1" checked
                        data-verrouillable><span></span> Critère actif (utilisé par le jury)</label>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer">Annuler</button>
                <button type="submit" class="cav-btn cav-btn-success"><i class="fas fa-check"></i> Enregistrer</button>
            </div>
        </form>
    </div>

    <!-- ============ MODALE CONFIRMATION ============ -->
    <div class="cav-overlay cav-overlay-modal" id="crit-conf-ov" hidden>
        <div class="cav-modal cav-modal-sm" role="dialog" aria-modal="true">
            <div class="cav-modal-body cav-center">
                <span class="cav-big-ico" id="crit-conf-ico"></span>
                <h3 id="crit-conf-titre"></h3>
                <p class="cav-muted" id="crit-conf-texte"></p>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer">Annuler</button>
                <button type="button" class="cav-btn" id="crit-conf-ok" data-action="confirmer">Confirmer</button>
            </div>
        </div>
    </div>

    <!-- ============ MODALE APERÇU FICHE JURY ============ -->
    <div class="cav-overlay cav-overlay-modal" id="crit-apercu-ov" hidden>
        <div class="cav-modal crit-apercu" role="dialog" aria-modal="true" aria-labelledby="crit-apercu-titre">
            <div class="cav-modal-head">
                <h3 id="crit-apercu-titre">Aperçu de la fiche de notation du jury</h3>
                <button type="button" class="cav-x" data-action="fermer" aria-label="Fermer">&times;</button>
            </div>
            <div class="cav-modal-body">
                <p class="cav-muted">Saisissez des notes fictives pour vérifier le calcul. Rien n'est enregistré.</p>
                <div id="crit-apercu-liste"></div>
            </div>
            <div class="cav-modal-foot crit-score">
                <span>Score du projet</span>
                <b><span id="crit-score">0</span> / 100</b>
            </div>
        </div>
    </div>

    <div class="cav-toasts" id="cav-toasts" aria-live="polite"></div>
</div>


<?php $this->load->view('back/components/app/candidats/_styles'); ?>
<?php $this->load->view('back/components/app/candidats/_outils_js'); ?>

<style>
    .crit-head-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap
    }

    .cav-title .cav-st {
        vertical-align: middle;
        font-size: 12px;
        margin-left: 8px
    }

    .cav-st-brouillon {
        background: #eef1f4;
        color: #4a5560
    }

    .cav-st-verrouillee {
        background: #e8f5ec;
        color: #1e7b3c
    }

    .crit-verrou-info {
        margin-bottom: 16px
    }

    .crit-kpi-txt {
        flex: 1;
        min-width: 0
    }

    .prj-jauge {
        display: block;
        height: 5px;
        background: #eef1f4;
        border-radius: 99px;
        margin: 6px 0 2px;
        overflow: hidden
    }

    .prj-jauge i {
        display: block;
        height: 100%;
        width: 0;
        background: var(--g);
        border-radius: 99px;
        transition: width .35s, background .2s
    }

    .crit-kpi-somme.is-moins .prj-jauge i {
        background: var(--a)
    }

    .crit-kpi-somme.is-moins b {
        color: var(--a)
    }

    .crit-kpi-somme.is-plus .prj-jauge i {
        background: var(--r)
    }

    .crit-kpi-somme.is-plus b {
        color: var(--r)
    }

    .crit-repartition {
        padding: 16px;
        margin-bottom: 18px
    }

    .crit-barre {
        display: flex;
        height: 14px;
        border-radius: 99px;
        overflow: hidden;
        background: #eef1f4
    }

    .crit-barre i {
        display: block;
        height: 100%;
        transition: width .35s
    }

    .crit-legende {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 16px;
        margin-top: 10px;
        font-size: 12.5px;
        color: var(--mu)
    }

    .crit-legende span {
        display: inline-flex;
        align-items: center;
        gap: 6px
    }

    .crit-legende i {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        display: inline-block
    }

    .crit-liste-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--bd);
        flex-wrap: wrap
    }

    .crit-liste-head h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700
    }

    .crit-liste-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap
    }

    .crit-liste {
        list-style: none;
        margin: 0;
        padding: 0;
        counter-reset: crit
    }

    .crit-item {
        display: grid;
        grid-template-columns: 28px 34px minmax(0, 1fr) 200px 70px 52px 76px;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--bd);
        background: #fff;
        transition: background .12s
    }

    .crit-item:hover {
        background: #fafcfb
    }

    .crit-item.is-inactif {
        opacity: .55
    }

    .crit-item.is-drag {
        opacity: .4
    }

    .crit-item.is-cible {
        box-shadow: inset 0 2px 0 var(--g)
    }

    .crit-poignee {
        cursor: grab;
        color: #b4bcc5;
        text-align: center;
        font-size: 15px;
        border: 0;
        background: none;
        padding: 6px 0
    }

    .crit-poignee:active {
        cursor: grabbing
    }

    .crit-num {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 13px;
        color: #fff
    }

    .crit-txt b {
        display: block;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .crit-txt small {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--mu);
        font-size: 12.5px;
        line-height: 1.4
    }

    .crit-poids-cell {
        display: flex;
        align-items: center;
        gap: 10px
    }

    .crit-poids-cell .prj-jauge {
        flex: 1;
        margin: 0;
        height: 7px
    }

    .crit-poids-cell b {
        min-width: 42px;
        text-align: right
    }

    .crit-bareme {
        color: var(--mu);
        font-size: 13px;
        white-space: nowrap
    }

    .crit-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: 500;
        margin: 14px 0 0
    }

    .crit-switch input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px
    }

    .crit-switch span {
        width: 38px;
        height: 22px;
        border-radius: 99px;
        background: #cfd6dd;
        position: relative;
        transition: background .15s;
        flex: none
    }

    .crit-switch span::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #fff;
        transition: transform .15s;
        box-shadow: 0 1px 2px rgba(0, 0, 0, .2)
    }

    .crit-switch input:checked+span {
        background: var(--g)
    }

    .crit-switch input:checked+span::after {
        transform: translateX(16px)
    }

    .crit-switch input:focus-visible+span {
        outline: 2px solid var(--g);
        outline-offset: 2px
    }

    .crit-switch input:disabled+span {
        opacity: .5;
        cursor: not-allowed
    }

    .crit-item .crit-switch {
        margin: 0
    }

    .crit-item .cav-acts {
        justify-content: flex-end
    }

    .crit-vide {
        padding: 40px 16px;
        text-align: center;
        color: var(--mu)
    }

    .crit-vide i.fa-clipboard-list {
        font-size: 34px;
        color: var(--g);
        display: block;
        margin-bottom: 10px
    }

    .crit-vide b {
        display: block;
        color: var(--tx);
        font-size: 15px;
        margin-bottom: 4px
    }

    .crit-vide .crit-liste-actions {
        justify-content: center;
        margin-top: 14px
    }

    .crit-aide {
        margin: 0;
        padding: 12px 16px;
        color: var(--mu);
        font-size: 12.5px;
        border-top: 1px solid var(--bd)
    }

    .crit-input {
        width: 100%;
        height: 38px;
        border: 1px solid #e4e8ec;
        border-radius: 8px;
        padding: 0 10px;
        font: inherit;
        color: inherit;
        background: #fff;
        outline: none
    }

    textarea.crit-input {
        height: auto;
        padding: 9px 10px;
        resize: vertical
    }

    .crit-input:focus {
        border-color: #1e7b3c;
        box-shadow: 0 0 0 3px #e8f5ec
    }

    .crit-input:disabled,
    .crit-poids input[type=range]:disabled {
        background: #f4f6f8;
        color: #8a95a1;
        cursor: not-allowed
    }

    .crit-input.is-err {
        border-color: #c62828
    }

    .crit-form-ligne {
        display: grid;
        grid-template-columns: 1fr 160px;
        gap: 16px
    }

    .crit-poids {
        display: flex;
        align-items: center;
        gap: 10px
    }

    .crit-poids input[type=range] {
        flex: 1;
        accent-color: #1e7b3c
    }

    .crit-poids span {
        display: flex;
        align-items: center;
        gap: 4px;
        width: 92px
    }

    .crit-poids span input {
        text-align: right
    }

    .crit-apercu {
        width: min(600px, 100%)
    }

    .crit-apercu .cav-modal-body {
        max-height: 60vh;
        overflow-y: auto
    }

    .crit-ap-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 120px;
        gap: 12px;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e4e8ec
    }

    .crit-ap-item b {
        display: block
    }

    .crit-ap-item small {
        color: #6b7785
    }

    .crit-ap-note {
        display: flex;
        align-items: center;
        gap: 6px;
        justify-content: flex-end;
        color: #6b7785
    }

    .crit-ap-note input {
        width: 70px;
        text-align: right
    }

    .crit-score {
        justify-content: space-between !important;
        align-items: center;
        background: #fafbfa
    }

    .crit-score b {
        font-size: 22px;
        color: #1e7b3c
    }

    @media (max-width:992px) {
        .crit-item {
            grid-template-columns: 24px 30px minmax(0, 1fr) 76px;
            row-gap: 8px
        }

        .crit-item .crit-poids-cell {
            grid-column: 3 / 5
        }

        .crit-item .crit-bareme,
        .crit-item>.crit-switch {
            display: none
        }
    }

    @media (max-width:576px) {
        .crit-form-ligne {
            grid-template-columns: 1fr
        }
    }
</style>

<script>
    (function() {
        'use strict';
        var root = document.getElementById('cav');
        if (!root || !window.CAV) return;
        CAV.init(root);

        var $ = CAV.$,
            $$ = CAV.$$,
            esc = CAV.esc,
            D = root.dataset;
        var COULEURS = ['#1e7b3c', '#1565c0', '#b26a00', '#6a1b9a', '#00838f', '#c62828', '#5d4037', '#3949ab',
            '#7cb342', '#d81b60'
        ];
        var etat = {
            criteres: [],
            grille: {},
            confirmer: null
        };

        var init = {};
        try {
            init = JSON.parse(D.initial || '{}');
        } catch (e) {}
        appliquer(init);

        /* ================= Rendu ================= */
        function appliquer(d) {
            if (d.criteres) etat.criteres = d.criteres;
            if (d.etat) etat.grille = d.etat;
            rendre();
        }

        function couleur(i) {
            return COULEURS[i % COULEURS.length];
        }

        function actifs() {
            return etat.criteres.filter(function(c) {
                return +c.actif;
            });
        }

        function verrou() {
            return !!etat.grille.verrouillee;
        }

        function rendre() {
            var g = etat.grille,
                somme = +g.somme_poids || 0;

            // En-tête
            $('#crit-statut').innerHTML = g.verrouillee ? '<span class="cav-st cav-st-verrouillee">Validée</span>' :
                '<span class="cav-st cav-st-brouillon">Brouillon</span>';
            $('#crit-btn-verrou').innerHTML = g.verrouillee ?
                (g.deverrouillable ?
                    '<button type="button" class="cav-btn cav-btn-ghost" data-action="deverrouiller"><i class="fas fa-lock-open"></i> Déverrouiller</button>' :
                    '') :
                '<button type="button" class="cav-btn cav-btn-success" data-action="verrouiller"' + (g.validable ? '' :
                    ' disabled title="La somme des pondérations doit faire 100 %"') +
                '><i class="fas fa-lock"></i> Valider la grille</button>';

            var info = $('#crit-verrou-info');
            info.hidden = !g.verrouillee;
            info.className = 'cav-alert crit-verrou-info ' + (g.deverrouillable ? 'cav-alert-warn' : 'cav-alert-red');
            info.innerHTML = '<i class="fas fa-lock"></i><div>Grille validée' + (g.verrouillee_le ? ' le ' + esc(CAV
                    .dateFr(g.verrouillee_le)) : '') + '. ' +
                (g.deverrouillable ?
                    'Pondérations et barèmes sont figés ; vous pouvez encore la déverrouiller tant qu\'aucune note n\'a été saisie.' :
                    'Des notes ont été saisies par le jury : la grille ne peut plus être modifiée (seuls les intitulés et descriptions restent éditables).'
                ) + '</div>';

            // Indicateurs
            var k = function(n) {
                return $('[data-k="' + n + '"]');
            };
            k('actifs').textContent = g.actifs || 0;
            var inactifs = (g.total || 0) - (g.actifs || 0);
            k('inactifs').textContent = inactifs ? inactifs + ' désactivé(s)' : 'sur ' + (g.total || 0) + ' critère(s)';
            k('somme').textContent = somme;
            k('jauge').style.width = Math.min(100, somme) + '%';
            k('somme-msg').textContent = somme === 100 ? 'Grille équilibrée' : somme < 100 ? 'Il manque ' + (100 -
                somme) + ' %' : (somme - 100) + ' % en trop';
            var kpi = $('#crit-kpi-somme');
            kpi.classList.toggle('is-moins', somme < 100);
            kpi.classList.toggle('is-plus', somme > 100);
            k('statut').textContent = g.verrouillee ? 'Validée' : 'Brouillon';
            k('statut-msg').textContent = g.verrouillee ? 'Pondérations figées' : (g.validable ?
                'Prête à être validée' : 'À compléter');
            k('projets').textContent = g.projets_notes || 0;
            k('notes').textContent = g.nb_notes || 0;

            // Boutons de modification de structure
            $$('[data-modif]').forEach(function(b) {
                b.disabled = verrou();
            });
            var rep = $('[data-action="repartir"]');
            if (rep && !actifs().length) rep.disabled = true;

            rendreRepartition();
            rendreListe();
        }

        function rendreRepartition() {
            var a = actifs(),
                zone = $('#crit-repartition');
            zone.hidden = !a.length;
            if (!a.length) return;
            var indexes = {};
            etat.criteres.forEach(function(c, i) {
                indexes[c.id] = i;
            });
            $('#crit-barre').innerHTML = a.map(function(c) {
                return '<i style="width:' + Math.min(100, +c.poids) + '%;background:' + couleur(indexes[c.id]) +
                    '" title="' + esc(c.nom) + ' : ' + esc(c.poids) + ' %"></i>';
            }).join('');
            var somme = +etat.grille.somme_poids || 0;
            $('#crit-legende').innerHTML = a.map(function(c) {
                return '<span><i style="background:' + couleur(indexes[c.id]) + '"></i>' + esc(c.nom) +
                    ' · <b>' + esc(c.poids) + ' %</b></span>';
            }).join('') + (somme < 100 ? '<span><i style="background:#eef1f4"></i>Non attribué · <b>' + (100 -
                somme) + ' %</b></span>' : '');
        }

        function rendreListe() {
            var liste = $('#crit-liste');
            if (!etat.criteres.length) {
                liste.innerHTML =
                    '<li class="crit-vide"><i class="fas fa-clipboard-list"></i><b>Aucun critère pour le moment</b>' +
                    'Partez d\'un modèle conçu pour les concours d\'architecture, ou créez vos critères un par un.' +
                    '<div class="crit-liste-actions">' +
                    '<button type="button" class="cav-btn cav-btn-success" data-action="modele"' + (verrou() ?
                        ' disabled' : '') + '><i class="fas fa-magic"></i> Charger le modèle (6 critères)</button>' +
                    '<button type="button" class="cav-btn cav-btn-ghost" data-action="ajouter"' + (verrou() ?
                        ' disabled' : '') + '><i class="fas fa-plus"></i> Créer un critère</button></div></li>';
                return;
            }
            var n = 0;
            liste.innerHTML = etat.criteres.map(function(c, i) {
                var actif = +c.actif,
                    num = actif ? ++n : '–';
                return '<li class="crit-item' + (actif ? '' : ' is-inactif') + '" data-id="' + c.id + '">' +
                    '<button type="button" class="crit-poignee" draggable="true" title="Glisser pour réordonner" aria-label="Réordonner"><i class="fas fa-grip-vertical"></i></button>' +
                    '<span class="crit-num" style="background:' + (actif ? couleur(i) : '#b4bcc5') + '">' +
                    num + '</span>' +
                    '<div class="crit-txt"><b>' + esc(c.nom) + '</b>' + (c.description ? '<small>' + esc(c
                        .description) + '</small>' : '') + '</div>' +
                    '<div class="crit-poids-cell"><span class="prj-jauge"><i style="width:' + Math.min(100, +c
                        .poids) + '%;background:' + couleur(i) + '"></i></span><b>' + esc(c.poids) +
                    ' %</b></div>' +
                    '<span class="crit-bareme">/ ' + esc(c.note_max) + '</span>' +
                    '<label class="crit-switch" title="' + (actif ? 'Désactiver' : 'Activer') +
                    '"><input type="checkbox" data-action="basculer" data-id="' + c.id + '"' + (actif ?
                        ' checked' : '') + (verrou() ? ' disabled' : '') + '><span></span></label>' +
                    '<div class="cav-acts">' +
                    '<button type="button" class="cav-ibtn" data-action="modifier" data-id="' + c.id +
                    '" title="Modifier"><i class="fas fa-pen"></i></button>' +
                    '<button type="button" class="cav-ibtn cav-ibtn-ko" data-action="supprimer" data-id="' + c
                    .id + '" title="' + (+c.nb_notes ? 'Déjà noté : désactivez-le plutôt' : 'Supprimer') + '"' +
                    (verrou() || +c.nb_notes ? ' disabled' : '') + '><i class="fas fa-trash"></i></button>' +
                    '</div></li>';
            }).join('');
        }

        /* ================= Requêtes ================= */
        function action(url, champs, bouton) {
            if (bouton) bouton.disabled = true;
            return CAV.poster(url, champs || {}).then(function(j) {
                appliquer(j);
                CAV.toast(j.message, true);
                return j;
            }).catch(function(e) {
                CAV.toast(e.message, false);
                throw e;
            }).finally(function() {
                if (bouton) bouton.disabled = false;
                rendre();
            });
        }

        /* ================= Formulaire ================= */
        var form = $('#crit-form');
        var F = function(nom) {
            return form.elements[nom];
        };

        function critere(id) {
            return etat.criteres.filter(function(c) {
                return String(c.id) === String(id);
            })[0];
        }

        function ouvrirForm(c) {
            form.reset();
            $$('.is-err', form).forEach(function(el) {
                el.classList.remove('is-err');
            });
            F('critere_id').value = c ? c.id : '';
            F('nom').value = c ? c.nom : '';
            F('description').value = c ? (c.description || '') : '';
            var reste = Math.max(0, 100 - (+etat.grille.somme_poids || 0));
            var poids = c ? +c.poids : reste;
            F('poids').value = poids;
            F('poids_range').value = poids;
            F('note_max').value = c ? c.note_max : 20;
            F('actif').checked = c ? !!+c.actif : true;
            $('#crit-form-titre').textContent = c ? 'Modifier le critère' : 'Nouveau critère';

            $('#crit-form-verrou').hidden = !verrou();
            $$('[data-verrouillable]', form).forEach(function(el) {
                el.disabled = verrou();
            });
            if (c && +c.nb_notes && !verrou()) F('note_max').disabled = true; // barème figé une fois noté

            majSommeApres();
            $('#crit-form-ov').hidden = false;
            setTimeout(function() {
                F('nom').focus();
            }, 50);
        }

        function majSommeApres() {
            var id = F('critere_id').value,
                c = id ? critere(id) : null;
            var avant = (+etat.grille.somme_poids || 0) - (c && +c.actif ? +c.poids : 0);
            var apres = avant + (F('actif').checked ? (+F('poids').value || 0) : 0);
            var el = $('#crit-somme-apres');
            el.textContent = 'Total de la grille après enregistrement : ' + apres + ' %';
            el.style.color = apres === 100 ? '#1e7b3c' : apres > 100 ? '#c62828' : '';
        }
        form.addEventListener('input', function(e) {
            if (e.target.name === 'poids_range') F('poids').value = e.target.value;
            if (e.target.name === 'poids') F('poids_range').value = Math.max(0, Math.min(100, +e.target.value ||
                0));
            if (e.target.classList.contains('is-err')) e.target.classList.remove('is-err');
            majSommeApres();
        });
        form.addEventListener('change', majSommeApres);
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var nom = F('nom').value.trim(),
                poids = F('poids').value;
            if (!nom) {
                F('nom').classList.add('is-err');
                F('nom').focus();
                CAV.toast('L\'intitulé est obligatoire.', false);
                return;
            }
            if (poids === '' || +poids < 0 || +poids > 100 || Math.floor(+poids) !== +poids) {
                F('poids').classList.add('is-err');
                F('poids').focus();
                CAV.toast('La pondération doit être un entier entre 0 et 100.', false);
                return;
            }
            // Les champs désactivés ne partent pas dans un formulaire : on envoie les valeurs actuelles
            var c = F('critere_id').value ? critere(F('critere_id').value) : null;
            action(D.urlEnregistrer, {
                id: F('critere_id').value,
                nom: nom,
                description: F('description').value.trim(),
                poids: F('poids').disabled && c ? c.poids : poids,
                note_max: F('note_max').disabled && c ? c.note_max : F('note_max').value,
                actif: (F('actif').disabled && c ? !!+c.actif : F('actif').checked) ? 1 : 0
            }, $('button[type=submit]', form)).then(function() {
                $('#crit-form-ov').hidden = true;
            }, function() {});
        });

        /* ================= Confirmation ================= */
        function confirmer(titre, texte, libelle, classe, icone, fn) {
            $('#crit-conf-titre').textContent = titre;
            $('#crit-conf-texte').textContent = texte;
            var ok = $('#crit-conf-ok');
            ok.className = 'cav-btn ' + classe;
            ok.innerHTML = '<i class="fas ' + icone + '"></i> ' + libelle;
            $('#crit-conf-ico').className = 'cav-big-ico ' + (classe === 'cav-btn-danger' ? 'cav-ico-red' :
                'cav-ico-green');
            $('#crit-conf-ico').innerHTML = '<i class="fas ' + icone + '"></i>';
            etat.confirmer = fn;
            $('#crit-conf-ov').hidden = false;
        }

        /* ================= Aperçu fiche jury ================= */
        function ouvrirApercu() {
            var a = actifs();
            $('#crit-apercu-liste').innerHTML = a.length ? a.map(function(c, i) {
                return '<div class="crit-ap-item"><div><b>' + (i + 1) + '. ' + esc(c.nom) + ' <small>(' + esc(c
                        .poids) + ' %)</small></b>' +
                    (c.description ? '<small>' + esc(c.description) + '</small>' : '') + '</div>' +
                    '<label class="crit-ap-note"><input class="crit-input" type="number" min="0" max="' + c
                    .note_max + '" step="0.5" data-poids="' + c.poids + '" data-max="' + c.note_max + '"> / ' +
                    esc(c.note_max) + '</label></div>';
            }).join('') : '<p class="cav-muted">Aucun critère actif.</p>';
            calculerScore();
            $('#crit-apercu-ov').hidden = false;
        }

        function calculerScore() {
            var total = 0;
            $$('#crit-apercu-liste input').forEach(function(inp) {
                var max = +inp.dataset.max,
                    v = Math.max(0, Math.min(max, +inp.value || 0));
                total += v / max * (+inp.dataset.poids);
            });
            $('#crit-score').textContent = (Math.round(total * 100) / 100).toLocaleString('fr-FR');
        }
        $('#crit-apercu-liste').addEventListener('input', calculerScore);

        /* ================= Glisser-déposer ================= */
        var glisse = null;
        var liste = $('#crit-liste');
        liste.addEventListener('dragstart', function(e) {
            var li = e.target.closest('.crit-item');
            if (!li) return;
            glisse = li;
            li.classList.add('is-drag');
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                try {
                    e.dataTransfer.setData('text/plain', li.dataset.id);
                } catch (x) {}
            }
        });
        liste.addEventListener('dragover', function(e) {
            var cible = e.target.closest('.crit-item');
            if (!glisse || !cible || cible === glisse) return;
            e.preventDefault();
            $$('.is-cible', liste).forEach(function(el) {
                el.classList.remove('is-cible');
            });
            cible.classList.add('is-cible');
        });
        liste.addEventListener('drop', function(e) {
            var cible = e.target.closest('.crit-item');
            if (!glisse || !cible || cible === glisse) return;
            e.preventDefault();
            liste.insertBefore(glisse, cible);
            var ids = $$('.crit-item', liste).map(function(li) {
                return li.dataset.id;
            });
            // Réordonne localement tout de suite, puis enregistre
            etat.criteres.sort(function(a, b) {
                return ids.indexOf(String(a.id)) - ids.indexOf(String(b.id));
            });
            rendre();
            CAV.poster(D.urlOrdre, {
                ids: ids
            }).then(appliquer).catch(function(er) {
                CAV.toast(er.message, false);
            });
        });
        liste.addEventListener('dragend', function() {
            if (glisse) glisse.classList.remove('is-drag');
            $$('.is-cible', liste).forEach(function(el) {
                el.classList.remove('is-cible');
            });
            glisse = null;
        });

        /* ================= Événements ================= */
        document.addEventListener('click', function(e) {
            var el = e.target.closest('[data-action]');
            if (!el) {
                if (e.target.classList.contains('cav-overlay-modal')) e.target.hidden = true;
                return;
            }
            if (el.disabled) return;
            var c = el.dataset.id ? critere(el.dataset.id) : null;
            switch (el.dataset.action) {
                case 'ajouter':
                    ouvrirForm(null);
                    break;
                case 'modifier':
                    ouvrirForm(c);
                    break;
                case 'apercu':
                    ouvrirApercu();
                    break;
                case 'fermer':
                    el.closest('.cav-overlay-modal').hidden = true;
                    break;
                case 'supprimer':
                    confirmer('Supprimer ce critère ?', '« ' + c.nom +
                        ' » sera retiré de la grille. Pensez à réajuster les pondérations.',
                        'Supprimer', 'cav-btn-danger', 'fa-trash',
                        function(b) {
                            return action(D.urlSupprimer, {
                                id: c.id
                            }, b);
                        });
                    break;
                case 'repartir':
                    confirmer('Répartir à parts égales ?', 'Les ' + actifs().length +
                        ' critères actifs recevront la même pondération (total 100 %). Les valeurs actuelles seront remplacées.',
                        'Répartir', 'cav-btn-success', 'fa-equals',
                        function(b) {
                            return action(D.urlRepartir, {}, b);
                        });
                    break;
                case 'modele':
                    action(D.urlModele, {}, el).catch(function() {});
                    break;
                case 'verrouiller':
                    confirmer('Valider la grille ?',
                        'Pondérations et barèmes seront figés pour que tous les membres du jury notent avec la même grille. Vous pourrez la déverrouiller tant qu\'aucune note n\'a été saisie.',
                        'Valider et verrouiller', 'cav-btn-success', 'fa-lock',
                        function(b) {
                            return action(D.urlVerrou, {
                                action: 'verrouiller'
                            }, b);
                        });
                    break;
                case 'deverrouiller':
                    confirmer('Déverrouiller la grille ?',
                        'La grille redevient modifiable. Elle devra être validée à nouveau avant le début de la notation.',
                        'Déverrouiller', 'cav-btn-success', 'fa-lock-open',
                        function(b) {
                            return action(D.urlVerrou, {
                                action: 'deverrouiller'
                            }, b);
                        });
                    break;
                case 'confirmer':
                    if (etat.confirmer) etat.confirmer(el).then(function() {
                        $('#crit-conf-ov').hidden = true;
                    }, function() {});
                    break;
            }
        });
        liste.addEventListener('change', function(e) {
            if (e.target.dataset.action !== 'basculer') return;
            var cb = e.target;
            action(D.urlActif, {
                id: cb.dataset.id,
                actif: cb.checked ? 1 : 0
            }).catch(function() {});
        });
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            $$('.cav-overlay-modal').forEach(function(m) {
                m.hidden = true;
            });
        });
    })();
</script>