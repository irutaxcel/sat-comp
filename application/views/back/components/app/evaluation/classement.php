<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Évaluation › Classement
 * Variables : $title, $classement (rows, stats, criteres), $csrf
 *
 * Réutilise les partials partagés (_styles, _outils_js → window.CAV) du dossier candidats.
 * ⚠ Aligne le conteneur racine (.content-wrapper) sur tes autres composants si besoin.
 */
$initial = json_encode($classement ?? ['rows' => [], 'stats' => [], 'criteres' => []], JSON_UNESCAPED_UNICODE);
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-donnees="<?= site_url('evaluation-classement/donnees') ?>"
        data-url-detail="<?= site_url('evaluation-classement/detail') ?>"
        data-url-figer="<?= site_url('evaluation-classement/figer') ?>"
        data-url-export="<?= site_url('evaluation-classement/export') ?>"
        data-url-criteres="<?= site_url('evaluation-criteres') ?>" data-url-projets="<?= site_url('projets') ?>"
        data-csrf-name="<?= html_escape($csrf['name']) ?>" data-csrf-hash="<?= html_escape($csrf['hash']) ?>"
        data-initial="<?= html_escape($initial) ?>">

        <header class="cav-head">
            <div>
                <p class="cav-crumb">Évaluation <span>›</span> Classement</p>
                <h1 class="cav-title">Classement <span id="cl-badge"></span></h1>
                <p class="cav-sub">Scores calculés à partir des notes du jury et de la grille de notation. Seuls les
                    projets soumis et conformes sont classés.</p>
            </div>
            <div class="cl-head-actions">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="rafraichir"><i
                        class="fas fa-sync-alt"></i> Actualiser</button>
                <a class="cav-btn cav-btn-ghost" id="cl-export"
                    href="<?= site_url('evaluation-classement/export') ?>"><i class="fas fa-file-excel"></i>
                    Exporter</a>
                <span id="cl-btn-figer"></span>
            </div>
        </header>

        <div id="cl-alertes"></div>

        <!-- ============ INDICATEURS ============ -->
        <div class="cav-kpis">
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-green"><i class="fas fa-trophy"></i></span>
                <span><b><span data-k="classes">0</span> <small class="cl-sur">/ <span
                                data-k="eligibles">0</span></small></b><small>Projets classés</small>
                    <em data-k="hors"></em></span>
            </div>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-blue"><i class="fas fa-user-tie"></i></span>
                <span class="cl-kpi-txt"><b><span data-k="progression">0</span> %</b><small>Avancement du jury</small>
                    <span class="cl-jauge"><i data-k="jauge"></i></span><em data-k="jures"></em></span>
            </div>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-amber"><i class="fas fa-star"></i></span>
                <span><b data-k="score_max">—</b><small>Meilleur score / 100</small><em
                        data-k="score_moyen"></em></span>
            </div>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-red"><i class="fas fa-not-equal"></i></span>
                <span><b data-k="divergences">0</b><small>Notations divergentes</small><em>Écart &gt; 15 pts entre
                        jurés</em></span>
            </div>
        </div>

        <!-- ============ PODIUM ============ -->
        <div class="cl-podium" id="cl-podium" hidden></div>

        <!-- ============ TABLEAU ============ -->
        <div class="cav-card">
            <form class="cav-filters" id="cl-filtres" autocomplete="off" onsubmit="return false">
                <div class="cav-search">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" placeholder="Code projet, titre, candidat…" id="cl-q">
                </div>
                <select name="categorie">
                    <option value="">Toutes catégories</option>
                    <option value="professionnel">Professionnels</option>
                    <option value="etudiant">Étudiants</option>
                </select>
                <select name="statut">
                    <option value="">Tous les projets</option>
                    <option value="classes">Classés uniquement</option>
                    <option value="en_cours">Évaluation en cours</option>
                    <option value="non_evalue">Non évalués</option>
                    <option value="divergence">Notations divergentes</option>
                </select>
            </form>
            <div class="cav-table-wrap">
                <table class="cav-table cl-table">
                    <thead id="cl-thead"></thead>
                    <tbody id="cl-tbody">
                        <tr>
                            <td colspan="7" class="cav-state">Chargement…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="cl-aide"><i class="fas fa-info-circle"></i> Score d'un juré = Σ (note ÷ barème × pondération) sur
                les critères actifs ; il n'est compté que si le juré a noté tous les critères.
                Score du projet = moyenne des jurés ayant terminé. Les projets à égalité (au centième) partagent le même
                rang.</p>
        </div>
    </section>

    <!-- ============ PANNEAU DÉTAIL ============ -->
    <div class="cav-overlay" id="cav-drawer-ov" hidden></div>
    <aside class="cav-drawer cl-drawer" id="cav-drawer" aria-hidden="true">
        <div class="cav-drawer-head">
            <h3>Détail de l'évaluation</h3>
            <button type="button" class="cav-x" data-action="fermer-detail" aria-label="Fermer">&times;</button>
        </div>
        <div class="cav-drawer-body" id="cl-detail"></div>
    </aside>

    <!-- ============ MODALE CONFIRMATION ============ -->
    <div class="cav-overlay cav-overlay-modal" id="cl-conf-ov" hidden>
        <div class="cav-modal cav-modal-sm" role="dialog" aria-modal="true">
            <div class="cav-modal-body cav-center">
                <span class="cav-big-ico" id="cl-conf-ico"></span>
                <h3 id="cl-conf-titre"></h3>
                <p class="cav-muted" id="cl-conf-texte"></p>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer">Annuler</button>
                <button type="button" class="cav-btn" id="cl-conf-ok" data-action="confirmer">Confirmer</button>
            </div>
        </div>
    </div>

    <div class="cav-toasts" id="cav-toasts" aria-live="polite"></div>
</div>

<?php $this->load->view('back/components/app/candidats/_styles'); ?>
<?php $this->load->view('back/components/app/candidats/_outils_js'); ?>

<style>
.cl-head-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap
}

.cl-head-actions a.cav-btn {
    text-decoration: none
}

.cav-title .cav-st {
    vertical-align: middle;
    font-size: 12px;
    margin-left: 8px
}

.cav-st-provisoire {
    background: #fff4e0;
    color: #b26a00
}

.cav-st-fige {
    background: #e8f5ec;
    color: #1e7b3c
}

#cl-alertes .cav-alert {
    margin-bottom: 14px
}

#cl-alertes a {
    color: inherit;
    font-weight: 700
}

.cl-sur {
    font-size: 13px;
    color: var(--mu);
    font-weight: 600
}

.cl-kpi-txt {
    flex: 1;
    min-width: 0
}

.cl-jauge {
    display: block;
    height: 5px;
    background: #eef1f4;
    border-radius: 99px;
    margin: 6px 0 2px;
    overflow: hidden
}

.cl-jauge i {
    display: block;
    height: 100%;
    width: 0;
    background: var(--b);
    border-radius: 99px;
    transition: width .4s
}

/* Podium */
.cl-podium {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 18px;
    align-items: end
}

.cl-place {
    background: #fff;
    border: 1px solid var(--bd);
    border-radius: 14px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    transition: box-shadow .15s, border-color .15s;
    position: relative;
    overflow: hidden
}

.cl-place:hover {
    border-color: #cfe7d7;
    box-shadow: 0 4px 16px rgba(30, 123, 60, .08)
}

.cl-place::before {
    content: "";
    position: absolute;
    inset: 0 0 auto 0;
    height: 4px;
    background: var(--m)
}

.cl-place.r1 {
    padding-top: 22px;
    padding-bottom: 22px
}

.cl-medaille {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    margin: 0 auto 10px;
    font-weight: 800;
    font-size: 17px;
    color: #fff;
    background: var(--m);
    box-shadow: 0 2px 8px rgba(0, 0, 0, .12)
}

.cl-place .prj-code {
    display: inline-block;
    margin-bottom: 6px
}

.cl-place b.cl-ptitre {
    display: block;
    font-size: 14px;
    line-height: 1.35;
    margin-bottom: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
}

.cl-place .cl-pscore {
    font-size: 26px;
    font-weight: 800;
    color: var(--tx)
}

.cl-place .cl-pscore small {
    font-size: 13px;
    color: var(--mu);
    font-weight: 600
}

.cl-place .cl-pid {
    display: block;
    font-size: 12.5px;
    color: var(--mu);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
}

/* Tableau */
.cl-table {
    min-width: 900px
}

.cl-rang {
    width: 62px;
    text-align: center
}

.cl-rang b {
    display: inline-grid;
    place-items: center;
    min-width: 34px;
    height: 34px;
    padding: 0 6px;
    border-radius: 9px;
    background: #eef1f4;
    font-size: 14px
}

.cl-rang b.r1 {
    background: #f4c430;
    color: #5c4400
}

.cl-rang b.r2 {
    background: #cfd6dd;
    color: #33404d
}

.cl-rang b.r3 {
    background: #e0a96d;
    color: #4d2e0c
}

.cl-rang small {
    display: block;
    font-size: 10.5px;
    color: var(--mu);
    margin-top: 2px;
    white-space: nowrap
}

.prj-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .03em;
    background: #eef1f4;
    color: #33404d;
    padding: 2px 7px;
    border-radius: 6px;
    white-space: nowrap
}

.cl-titre {
    display: block;
    font-weight: 600;
    margin-top: 4px;
    cursor: pointer;
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
}

.cl-titre:hover {
    color: var(--g)
}

.cl-ident {
    display: block;
    font-size: 12.5px;
    color: var(--mu);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 280px
}

.cl-score {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 150px
}

.cl-score b {
    font-size: 16px;
    min-width: 52px
}

.cl-score .cl-jauge {
    flex: 1;
    margin: 0;
    height: 7px
}

.cl-score .cl-jauge i {
    background: var(--g)
}

.cl-crits {
    display: flex;
    gap: 3px;
    align-items: flex-end;
    height: 28px
}

.cl-crits i {
    display: block;
    width: 9px;
    border-radius: 2px 2px 0 0;
    min-height: 2px
}

.cl-crits i.vide {
    background: #eef1f4 !important;
    height: 28px !important
}

.cl-jures {
    white-space: nowrap
}

.cl-jures small {
    display: block;
    color: var(--mu);
    font-size: 12px
}

.cav-st-complet {
    background: #e8f5ec;
    color: #1e7b3c
}

.cav-st-en_cours {
    background: #e3f0fc;
    color: #1565c0
}

.cav-st-non_evalue {
    background: #eef1f4;
    color: #4a5560
}

.cl-div {
    color: #c62828;
    font-weight: 700
}

.cl-aide {
    margin: 0;
    padding: 12px 16px;
    color: var(--mu);
    font-size: 12.5px;
    border-top: 1px solid var(--bd);
    line-height: 1.5
}

/* Détail */
.cl-drawer {
    width: min(640px, 100%)
}

.cl-dscore {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 16px;
    background: #fafbfa;
    border: 1px solid #e4e8ec;
    border-radius: 12px;
    margin-bottom: 6px
}

.cl-dscore b {
    font-size: 30px;
    color: #1e7b3c;
    line-height: 1
}

.cl-dscore small {
    color: #6b7785
}

.cl-dscore .cl-drang {
    margin-left: auto;
    text-align: right
}

.cl-grille {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px
}

.cl-grille th,
.cl-grille td {
    padding: 8px 6px;
    border-bottom: 1px solid #e4e8ec;
    text-align: center
}

.cl-grille th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #6b7785;
    font-weight: 600;
    background: #fafbfa
}

.cl-grille td:first-child,
.cl-grille th:first-child {
    text-align: left
}

.cl-grille td.moy {
    font-weight: 700;
    background: #f4faf6
}

.cl-grille td.vide {
    color: #b4bcc5
}

.cl-grille tfoot td {
    font-weight: 700;
    border-bottom: 0;
    background: #fafbfa
}

.cl-grille small {
    color: #6b7785;
    font-weight: 400
}

.cl-com {
    padding: 10px 12px;
    border: 1px solid #e4e8ec;
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 13px
}

.cl-com small {
    display: block;
    color: #6b7785;
    margin-bottom: 3px
}

.cl-note {
    white-space: pre-line;
    background: #fafbfa;
    border: 1px solid #e4e8ec;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
    line-height: 1.55
}

@media (max-width:768px) {
    .cl-podium {
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
    var MEDAILLES = {
        1: '#d4a514',
        2: '#8a96a3',
        3: '#c07a3a'
    };
    var STATUTS = {
        complet: 'Complet',
        en_cours: 'En cours',
        non_evalue: 'Non évalué'
    };
    var etat = {
        rows: [],
        stats: {},
        criteres: [],
        confirmer: null
    };
    var form = $('#cl-filtres');
    var F = function(n) {
        return form.elements[n];
    };

    try {
        appliquer(JSON.parse(D.initial || '{}'));
    } catch (e) {
        appliquer({});
    }

    function appliquer(d) {
        if (d.rows) etat.rows = d.rows;
        if (d.stats) etat.stats = d.stats;
        if (d.criteres) etat.criteres = d.criteres;
        rendre();
    }

    function fige() {
        return !!etat.stats.fige;
    }

    function nombre(v) {
        return v === null || v === undefined ? '—' : (+v).toLocaleString('fr-FR', {
            maximumFractionDigits: 2
        });
    }

    function identite(r) {
        return fige() && r.candidat ? r.candidat : '';
    }

    /* ================= Rendu ================= */
    function rendre() {
        var s = etat.stats;
        $('#cl-badge').innerHTML = s.fige ? '<span class="cav-st cav-st-fige">Figé</span>' :
            '<span class="cav-st cav-st-provisoire">Provisoire · anonyme</span>';
        $('#cl-btn-figer').innerHTML = s.fige ?
            '<button type="button" class="cav-btn cav-btn-ghost" data-action="rouvrir"><i class="fas fa-lock-open"></i> Rouvrir</button>' :
            '<button type="button" class="cav-btn cav-btn-success" data-action="figer"' + (s.classes && s
                .grille_validee ? '' : ' disabled') +
            '><i class="fas fa-flag-checkered"></i> Figer le classement</button>';

        // Alertes
        var a = '';
        if (!s.grille_validee) {
            a += '<div class="cav-alert cav-alert-warn"><i class="fas fa-exclamation-triangle"></i><div>La grille de notation n\'est pas encore validée : les scores peuvent encore évoluer. <a href="' +
                D.urlCriteres + '">Ouvrir les critères</a></div></div>';
        }
        if (s.hors_classement) {
            a += '<div class="cav-alert cav-alert-warn"><i class="fas fa-filter"></i><div>' + s.hors_classement +
                ' projet(s) soumis ne sont pas classés car non déclarés conformes. <a href="' + D.urlProjets +
                '?onglet=a_verifier">Contrôler la conformité</a></div></div>';
        }
        if (s.fige) {
            a += '<div class="cav-alert" style="background:#e8f5ec;color:#155c2c"><i class="fas fa-flag-checkered"></i><div>Classement figé' +
                (s.fige_le ? ' le ' + esc(CAV.dateFr(s.fige_le)) : '') +
                '. L\'anonymat est levé dans le back-office et l\'export contient les identités.</div></div>';
        }
        $('#cl-alertes').innerHTML = a;

        // Indicateurs
        var k = function(n) {
            return $('[data-k="' + n + '"]');
        };
        k('classes').textContent = s.classes || 0;
        k('eligibles').textContent = s.eligibles || 0;
        k('hors').textContent = (s.non_evalues || 0) + ' non évalué(s)' + (s.hors_classement ? ' · ' + s
            .hors_classement + ' hors classement' : '');
        k('progression').textContent = s.progression || 0;
        k('jauge').style.width = Math.min(100, s.progression || 0) + '%';
        k('jures').textContent = (s.jures || 0) + ' juré(s) · ' + (s.complets || 0) +
            ' projet(s) entièrement évalué(s)';
        k('score_max').textContent = nombre(s.score_max);
        k('score_moyen').textContent = s.score_moyen !== null && s.score_moyen !== undefined ? 'Moyenne : ' +
            nombre(s.score_moyen) : 'Aucun score';
        k('divergences').textContent = s.divergences || 0;

        rendrePodium();
        rendreTableau();
    }

    function rendrePodium() {
        var top = etat.rows.filter(function(r) {
            return r.rang && r.rang <= 3;
        }).slice(0, 3);
        var zone = $('#cl-podium');
        zone.hidden = !top.length;
        if (!top.length) return;
        // Ordre visuel : 2e, 1er, 3e
        var ordre = top.length === 3 ? [top[1], top[0], top[2]] : top;
        zone.innerHTML = ordre.map(function(r) {
            var m = MEDAILLES[r.rang] || '#8a96a3';
            return '<div class="cl-place r' + r.rang + '" style="--m:' + m +
                '" data-action="voir" data-id="' + r.id + '">' +
                '<span class="cl-medaille">' + r.rang + '</span>' +
                '<span class="prj-code">' + esc(r.code_anonyme) + '</span>' +
                '<b class="cl-ptitre" title="' + esc(r.titre) + '">' + esc(r.titre) + '</b>' +
                '<span class="cl-pscore">' + nombre(r.score) + ' <small>/ 100</small></span>' +
                (r.ex_aequo ? '<span class="cl-pid">ex aequo</span>' : '') +
                (identite(r) ? '<span class="cl-pid">' + esc(identite(r)) + '</span>' : '') + '</div>';
        }).join('');
    }

    function filtrees() {
        var q = (F('q').value || '').trim().toLowerCase(),
            cat = F('categorie').value,
            st = F('statut').value;
        return etat.rows.filter(function(r) {
            if (cat && r.categorie !== cat) return false;
            if (st === 'classes' && !r.rang) return false;
            if (st === 'divergence' && !r.divergence) return false;
            if ((st === 'en_cours' || st === 'non_evalue') && r.statut !== st) return false;
            if (q) {
                var txt = (r.code_anonyme + ' ' + r.titre + ' ' + (r.candidat || '') + ' ' + (r.reference ||
                    '')).toLowerCase();
                if (txt.indexOf(q) === -1) return false;
            }
            return true;
        });
    }

    function rendreTableau() {
        $('#cl-thead').innerHTML = '<tr><th class="cl-rang">Rang</th><th>Projet</th><th>Score / 100</th>' +
            '<th title="Moyenne par critère (hauteur = % du barème)">Profil par critère</th><th>Jury</th><th>Écart</th><th>Statut</th><th class="cav-col-act"></th></tr>';
        var rows = filtrees(),
            tb = $('#cl-tbody');
        if (!etat.rows.length) {
            tb.innerHTML =
                '<tr><td colspan="8" class="cav-state"><div class="cav-empty"><i class="fas fa-trophy"></i><b>Aucun projet à classer</b>' +
                'Les projets apparaîtront ici une fois soumis et déclarés conformes.</div></td></tr>';
            return;
        }
        if (!rows.length) {
            tb.innerHTML =
                '<tr><td colspan="8" class="cav-state"><div class="cav-empty"><i class="fas fa-filter"></i><b>Aucun résultat</b>Aucun projet ne correspond à ces filtres.</div></td></tr>';
            return;
        }
        var titreCrit = {};
        etat.criteres.forEach(function(c) {
            titreCrit[c.id] = c;
        });

        tb.innerHTML = rows.map(function(r) {
            var rang = r.rang ?
                '<b class="' + (r.rang <= 3 ? 'r' + r.rang : '') + '">' + r.rang + '</b>' + (r.ex_aequo ?
                    '<small>ex aequo</small>' : '') :
                '<b>—</b>';
            var crits = etat.criteres.map(function(c, i) {
                var p = r.par_critere && r.par_critere[c.id] ? r.par_critere[c.id].pourcentage :
                    null;
                var titre = c.nom + ' : ' + (p === null ? 'non noté' : nombre(r.par_critere[c.id]
                    .moyenne) + ' / ' + c.note_max);
                return p === null ? '<i class="vide" title="' + esc(titre) + '"></i>' :
                    '<i style="height:' + Math.max(2, Math.round(p * 0.28)) + 'px;background:' +
                    COULEURS[i % COULEURS.length] + '" title="' + esc(titre) + '"></i>';
            }).join('');
            return '<tr data-id="' + r.id + '">' +
                '<td class="cl-rang">' + rang + '</td>' +
                '<td><span class="prj-code">' + esc(r.code_anonyme) + '</span>' +
                '<span class="cl-titre" data-action="voir" data-id="' + r.id + '" title="' + esc(r.titre) +
                '">' + esc(r.titre) + '</span>' +
                (identite(r) ? '<span class="cl-ident">' + esc(identite(r)) + (r.organisation ? ' · ' + esc(
                    r.organisation) : '') + '</span>' : '') + '</td>' +
                '<td>' + (r.score === null ? '<span class="cav-muted">—</span>' :
                    '<div class="cl-score"><b>' + nombre(r.score) +
                    '</b><span class="cl-jauge"><i style="width:' + Math.min(100, r.score) +
                    '%"></i></span></div>') + '</td>' +
                '<td><div class="cl-crits">' + crits + '</div></td>' +
                '<td class="cl-jures">' + r.jures_complets + ' / ' + r.jures_total + (r.jures_partiels ?
                    '<small>+ ' + r.jures_partiels + ' en cours</small>' : '') + '</td>' +
                '<td>' + (r.ecart === null ? '<span class="cav-muted">—</span>' :
                    '<span class="' + (r.divergence ? 'cl-div' : '') +
                    '" title="Écart entre le score le plus haut et le plus bas des jurés">' + (r
                        .divergence ? '<i class="fas fa-exclamation-triangle"></i> ' : '') + nombre(r
                    .ecart) + '</span>') + '</td>' +
                '<td><span class="cav-st cav-st-' + esc(r.statut) + '">' + esc(STATUTS[r.statut] || r
                    .statut) + '</span></td>' +
                '<td><div class="cav-acts"><button type="button" class="cav-ibtn" data-action="voir" data-id="' +
                r.id + '" title="Détail des notes"><i class="fas fa-eye"></i></button></div></td></tr>';
        }).join('');
    }

    /* ================= Détail ================= */
    function ouvrirDetail(id) {
        $('#cl-detail').innerHTML = '<p class="cav-muted">Chargement…</p>';
        $('#cav-drawer-ov').hidden = false;
        $('#cav-drawer').classList.add('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'false');
        var ligne = etat.rows.filter(function(r) {
            return String(r.id) === String(id);
        })[0];

        CAV.requete(D.urlDetail + '/' + id).then(function(j) {
            var p = j.projet,
                h = '';
            h += '<div class="cav-d-id" style="align-items:flex-start;margin-bottom:12px"><div><span class="prj-code">' +
                esc(p.code_anonyme) + '</span>' +
                '<h4 style="margin-top:8px">' + esc(p.titre) + '</h4>' +
                (p.candidat ? '<span class="cav-muted">' + esc(p.candidat) + (p.organisation ? ' · ' + esc(p
                        .organisation) : '') + ' · ' + esc(p.reference) + '</span>' :
                    '<span class="cav-muted"><i class="fas fa-user-secret"></i> Identité masquée jusqu\'au classement figé</span>'
                    ) + '</div></div>';

            h += '<div class="cl-dscore"><div><b>' + nombre(p.score) +
                '</b> <small>/ 100</small><br><small>Score moyen du jury</small></div>' +
                (p.ecart !== null ? '<div><b style="font-size:20px;color:' + (p.ecart > 15 ? '#c62828' :
                        '#33404d') + '">' + nombre(p.ecart) +
                    '</b><br><small>Écart entre jurés</small></div>' : '') +
                (ligne && ligne.rang ? '<div class="cl-drang"><b style="font-size:24px;color:#33404d">' +
                    ligne.rang + '<sup style="font-size:12px">' + (ligne.rang === 1 ? 'er' : 'e') +
                    '</sup></b><br><small>' + (ligne.ex_aequo ? 'ex aequo' : 'Rang') + '</small></div>' : ''
                    ) + '</div>';

            if (p.conformite !== 'conforme') {
                h +=
                '<div class="cav-alert cav-alert-warn"><i class="fas fa-filter"></i>Projet non déclaré conforme : il n\'apparaît pas dans le classement.</div>';
            }

            // Grille critère × juré
            h += '<div class="cav-d-sec">Notes par critère</div>';
            if (!p.jures.length) {
                h += '<p class="cav-muted">Aucun juré n\'a encore saisi de note.</p>';
            } else {
                h += '<div style="overflow-x:auto"><table class="cl-grille"><thead><tr><th>Critère</th>' +
                    p.jures.map(function(jn) {
                        return '<th>' + esc(jn) + '</th>';
                    }).join('') + '<th>Moyenne</th></tr></thead><tbody>' +
                    p.grille.map(function(g) {
                        return '<tr><td>' + esc(g.nom) + ' <small>(' + g.poids + ' % · /' + g.note_max +
                            ')</small></td>' +
                            g.notes.map(function(n) {
                                return n === null ? '<td class="vide">—</td>' : '<td>' + nombre(n) +
                                    '</td>';
                            }).join('') +
                            '<td class="moy">' + nombre(g.moyenne) + '</td></tr>';
                    }).join('') + '</tbody><tfoot><tr><td>Score / 100</td>' +
                    p.scores_jures.map(function(s) {
                        return '<td>' + (s.complet ? nombre(s.score) : '<small>incomplet</small>') +
                            '</td>';
                    }).join('') +
                    '<td class="moy">' + nombre(p.score) + '</td></tr></tfoot></table></div>';
            }

            if (p.commentaires.length) {
                h += '<div class="cav-d-sec">Commentaires du jury</div>' + p.commentaires.map(function(c) {
                    return '<div class="cl-com"><small>' + esc(c.jure) + ' · ' + esc(c.critere) +
                        '</small>' + esc(c.texte) + '</div>';
                }).join('');
            }
            if (p.note_descriptive) {
                h += '<div class="cav-d-sec">Note descriptive du projet</div><div class="cl-note">' + esc(p
                    .note_descriptive) + '</div>';
            }
            $('#cl-detail').innerHTML = h;
        }).catch(function(e) {
            $('#cl-detail').innerHTML = '<div class="cav-alert cav-alert-red">' + esc(e.message) + '</div>';
        });
    }

    function fermerDetail() {
        $('#cav-drawer').classList.remove('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'true');
        $('#cav-drawer-ov').hidden = true;
    }

    /* ================= Actions ================= */
    function confirmer(titre, texte, libelle, classe, icone, fn) {
        $('#cl-conf-titre').textContent = titre;
        $('#cl-conf-texte').textContent = texte;
        var ok = $('#cl-conf-ok');
        ok.className = 'cav-btn ' + classe;
        ok.innerHTML = '<i class="fas ' + icone + '"></i> ' + libelle;
        $('#cl-conf-ico').className = 'cav-big-ico cav-ico-green';
        $('#cl-conf-ico').innerHTML = '<i class="fas ' + icone + '"></i>';
        etat.confirmer = fn;
        $('#cl-conf-ov').hidden = false;
    }

    function rafraichir(bouton) {
        if (bouton) bouton.disabled = true;
        return CAV.requete(D.urlDonnees).then(appliquer).catch(function(e) {
                CAV.toast(e.message, false);
            })
            .then(function() {
                if (bouton) bouton.disabled = false;
            });
    }

    document.addEventListener('click', function(e) {
        var el = e.target.closest('[data-action]');
        if (!el) {
            if (e.target.classList.contains('cav-overlay-modal')) e.target.hidden = true;
            return;
        }
        if (el.disabled) return;
        switch (el.dataset.action) {
            case 'voir':
                ouvrirDetail(el.dataset.id);
                break;
            case 'fermer-detail':
                fermerDetail();
                break;
            case 'fermer':
                el.closest('.cav-overlay-modal').hidden = true;
                break;
            case 'rafraichir':
                rafraichir(el);
                break;
            case 'figer':
                confirmer('Figer le classement ?',
                    'Le classement actuel devient le résultat officiel et l\'anonymat est levé dans le back-office. ' +
                    (etat.stats.progression < 100 ?
                        'Attention : toutes les évaluations ne sont pas terminées (' + etat.stats
                        .progression + ' %).' : 'Toutes les évaluations sont terminées.'),
                    'Figer', 'cav-btn-success', 'fa-flag-checkered',
                    function(b) {
                        b.disabled = true;
                        return CAV.poster(D.urlFiger, {
                                action: 'figer'
                            }).then(function(j) {
                                appliquer(j);
                                CAV.toast(j.message, true);
                            })
                            .finally(function() {
                                b.disabled = false;
                            });
                    });
                break;
            case 'rouvrir':
                confirmer('Rouvrir le classement ?',
                    'Le classement redevient provisoire et les projets sont de nouveau anonymes dans le back-office.',
                    'Rouvrir', 'cav-btn-success', 'fa-lock-open',
                    function(b) {
                        b.disabled = true;
                        return CAV.poster(D.urlFiger, {
                                action: 'rouvrir'
                            }).then(function(j) {
                                appliquer(j);
                                CAV.toast(j.message, true);
                            })
                            .finally(function() {
                                b.disabled = false;
                            });
                    });
                break;
            case 'confirmer':
                if (etat.confirmer) etat.confirmer(el).then(function() {
                        $('#cl-conf-ov').hidden = true;
                    },
                    function(er) {
                        CAV.toast(er.message, false);
                    });
                break;
        }
    });
    form.addEventListener('input', rendreTableau);
    form.addEventListener('change', rendreTableau);
    $('#cav-drawer-ov').addEventListener('click', fermerDetail);
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        $$('.cav-overlay-modal').forEach(function(m) {
            m.hidden = true;
        });
        fermerDetail();
    });
})();
</script>