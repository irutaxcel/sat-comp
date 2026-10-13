<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Candidatures › Projets soumis
 * Variables : $title, $stats, $onglet_initial, $csrf
 *
 * Réutilise les partials partagés du module (_styles, _outils_js → window.CAV).
 * ⚠ Aligne le conteneur racine (.content-wrapper) sur tes autres composants si besoin.
 */
$s = array_merge([
    'soumis' => 0,
    'brouillon' => 0,
    'a_verifier' => 0,
    'conforme' => 0,
    'non_conforme' => 0,
    'dernieres_24h' => 0,
    'valides' => 0,
    'non_soumis' => 0,
    'fichiers' => 0,
    'volume' => 0,
    'taux' => 0,
], $stats ?? []);

if (!function_exists('cav_octets')) {
    function cav_octets($o)
    {
        $u = ['o', 'Ko', 'Mo', 'Go', 'To'];
        for ($i = 0; $o >= 1024 && $i < 4; $i++) {
            $o /= 1024;
        }
        return number_format($o, $i > 1 ? 1 : 0, ',', ' ') . ' ' . $u[$i];
    }
}

$onglets = [
    'soumis'       => ['Tous les soumis', $s['soumis']],
    'a_verifier'   => ['À vérifier',      $s['a_verifier']],
    'conforme'     => ['Conformes',       $s['conforme']],
    'non_conforme' => ['Non conformes',   $s['non_conforme']],
    'brouillon'    => ['Brouillons',      $s['brouillon']],
    'non_soumis'   => ['Sans projet',     $s['non_soumis']],
];
$onglet_initial = $onglet_initial ?? 'soumis';
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-liste="<?= site_url('projets/liste') ?>"
        data-url-detail="<?= site_url('projets/detail') ?>" data-url-conformite="<?= site_url('projets/conformite') ?>"
        data-url-relancer="<?= site_url('projets/relancer') ?>" data-url-fichier="<?= site_url('projets/fichier') ?>"
        data-url-apercu="<?= site_url('projets/apercu') ?>" data-url-zip="<?= site_url('projets/zip') ?>"
        data-onglet="<?= html_escape($onglet_initial) ?>" data-csrf-name="<?= html_escape($csrf['name']) ?>"
        data-csrf-hash="<?= html_escape($csrf['hash']) ?>">

        <!-- ============ EN-TÊTE ============ -->
        <header class="cav-head">
            <div>
                <p class="cav-crumb">Candidatures <span>›</span> Projets soumis</p>
                <h1 class="cav-title">Projets soumis</h1>
                <p class="cav-sub">Contrôle de conformité des dossiers déposés et suivi des candidats qui n'ont pas
                    encore soumis.</p>
            </div>
            <button type="button" class="cav-btn cav-btn-ghost" data-action="rafraichir"><i class="fas fa-sync-alt"></i>
                Actualiser</button>
        </header>

        <!-- ============ INDICATEURS ============ -->
        <div class="cav-kpis">
            <button type="button" class="cav-kpi" data-kpi="soumis">
                <span class="cav-kpi-ico cav-ico-green"><i class="fas fa-file-upload"></i></span>
                <span class="prj-kpi-txt"><b data-stat="soumis"><?= $s['soumis'] ?></b><small>Projets soumis</small>
                    <span class="prj-jauge"><i style="width:<?= min(100, $s['taux']) ?>%" data-jauge></i></span>
                    <em><span data-stat="taux"><?= $s['taux'] ?></span> % des <span
                            data-stat="valides"><?= $s['valides'] ?></span> candidature(s) validée(s)</em></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="a_verifier">
                <span class="cav-kpi-ico cav-ico-amber"><i class="fas fa-clipboard-check"></i></span>
                <span><b data-stat="a_verifier"><?= $s['a_verifier'] ?></b><small>À vérifier</small>
                    <em><span data-stat="conforme"><?= $s['conforme'] ?></span> conforme(s) · <span
                            data-stat="non_conforme"><?= $s['non_conforme'] ?></span> non conforme(s)</em></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="non_soumis">
                <span class="cav-kpi-ico cav-ico-red"><i class="fas fa-user-clock"></i></span>
                <span><b data-stat="non_soumis"><?= $s['non_soumis'] ?></b><small>Validés sans projet</small>
                    <em><span data-stat="brouillon"><?= $s['brouillon'] ?></span> brouillon(s) en cours</em></span>
            </button>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-blue"><i class="fas fa-paperclip"></i></span>
                <span><b data-stat="fichiers"><?= $s['fichiers'] ?></b><small>Pièces reçues</small>
                    <em><span data-stat-octets="volume"><?= cav_octets($s['volume']) ?></span> · <span
                            data-stat="dernieres_24h"><?= $s['dernieres_24h'] ?></span> dépôt(s) en 24 h</em></span>
            </div>
        </div>

        <!-- ============ CARTE LISTE ============ -->
        <div class="cav-card">
            <nav class="cav-tabs" id="cav-tabs" role="tablist">
                <?php foreach ($onglets as $cle => $o): ?>
                <button type="button" role="tab" class="cav-tab<?= $cle === $onglet_initial ? ' is-active' : '' ?>"
                    data-onglet="<?= $cle ?>">
                    <?= $o[0] ?> <span class="cav-cnt" data-stat="<?= $cle ?>"><?= $o[1] ?></span>
                </button>
                <?php endforeach; ?>
            </nav>

            <form class="cav-filters" id="cav-filtres" autocomplete="off" onsubmit="return false">
                <div class="cav-search">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" placeholder="Titre, code projet, candidat, équipe, organisation…">
                </div>
                <select name="type">
                    <option value="">Tous les types</option>
                    <option value="individuel">Individuel</option>
                    <option value="equipe">Équipe</option>
                </select>
                <select name="categorie">
                    <option value="">Toutes catégories</option>
                    <option value="professionnel">Professionnel</option>
                    <option value="etudiant">Étudiant</option>
                </select>
                <select name="tri" id="cav-tri">
                    <option value="recent">Dernière soumission</option>
                    <option value="ancien">Première soumission</option>
                    <option value="titre">Titre (A→Z)</option>
                    <option value="nom">Candidat (A→Z)</option>
                </select>
                <button type="button" class="cav-link" data-action="reinitialiser">Réinitialiser</button>
            </form>

            <div class="cav-bulk" id="cav-bulk" hidden>
                <span><b id="cav-nb-sel">0</b> sélectionné(s)</span>
                <div id="cav-bulk-actions"></div>
            </div>

            <div class="cav-table-wrap">
                <table class="cav-table">
                    <thead id="cav-thead"></thead>
                    <tbody id="cav-tbody">
                        <tr>
                            <td colspan="7" class="cav-state">Chargement…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="cav-foot">
                <span id="cav-resume">—</span>
                <div class="cav-pager" id="cav-pager"></div>
            </footer>
        </div>
    </section>

    <!-- ============ PANNEAU DÉTAIL ============ -->
    <div class="cav-overlay" id="cav-drawer-ov" hidden></div>
    <aside class="cav-drawer prj-drawer" id="cav-drawer" aria-hidden="true">
        <div class="cav-drawer-head">
            <h3>Dossier projet</h3>
            <button type="button" class="cav-x" data-action="fermer-detail" aria-label="Fermer">&times;</button>
        </div>
        <div class="cav-drawer-body" id="cav-detail"></div>
        <div class="cav-drawer-foot" id="cav-detail-actions"></div>
    </aside>

    <!-- ============ MODALE (verdict / relance) ============ -->
    <div class="cav-overlay cav-overlay-modal" id="cav-action-ov" hidden>
        <div class="cav-modal" role="dialog" aria-modal="true" aria-labelledby="cav-action-titre">
            <div class="cav-modal-head">
                <h3 id="cav-action-titre">Confirmer</h3>
                <button type="button" class="cav-x" data-action="fermer-action" aria-label="Fermer">&times;</button>
            </div>
            <div class="cav-modal-body">
                <p id="cav-action-texte"></p>
                <p class="cav-muted" id="cav-action-cible"></p>
                <div id="cav-action-motif" hidden>
                    <label class="cav-label">Motif rapide</label>
                    <div class="cav-chips">
                        <button type="button" class="cav-chip">Pièces graphiques manquantes (plans, coupes ou
                            élévations).</button>
                        <button type="button" class="cav-chip">Fichier illisible ou corrompu.</button>
                        <button type="button" class="cav-chip">Format ou résolution non conforme au règlement.</button>
                        <button type="button" class="cav-chip">Le dossier contient des éléments permettant d'identifier
                            l'auteur (anonymat).</button>
                    </div>
                    <label class="cav-label" for="cav-motif">Motif communiqué au candidat <span
                            class="cav-req">*</span></label>
                    <textarea id="cav-motif" rows="4" maxlength="1000"
                        placeholder="Précisez ce qui doit être corrigé (envoyé par e-mail)."></textarea>
                    <small class="cav-muted"><span id="cav-motif-n">0</span>/1000 — 10 caractères minimum</small>
                </div>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer-action">Annuler</button>
                <button type="button" class="cav-btn" id="cav-action-ok"
                    data-action="confirmer-action">Confirmer</button>
            </div>
        </div>
    </div>

    <div class="cav-toasts" id="cav-toasts" aria-live="polite"></div>
</div>

<?php $this->load->view('back/components/app/candidats/_styles'); ?>
<?php $this->load->view('back/components/app/candidats/_outils_js'); ?>

<style>
.prj-kpi-txt {
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
    background: var(--g);
    border-radius: 99px;
    transition: width .4s
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

.prj-titre {
    display: block;
    font-weight: 600;
    margin-top: 4px;
    cursor: pointer;
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
}

.prj-titre:hover {
    color: var(--g)
}

.prj-v {
    font-size: 11px;
    font-weight: 700;
    color: var(--b);
    background: var(--b-l);
    padding: 1px 6px;
    border-radius: 5px;
    margin-left: 4px
}

.cav-st-a_verifier {
    background: #fff4e0;
    color: #b26a00
}

.cav-st-conforme {
    background: #e8f5ec;
    color: #1e7b3c
}

.cav-st-non_conforme {
    background: #fdecea;
    color: #c62828
}

.cav-st-brouillon {
    background: #eef1f4;
    color: #4a5560
}

.prj-drawer {
    width: min(560px, 100%)
}

.prj-note {
    white-space: pre-line;
    background: #fafbfa;
    border: 1px solid #e4e8ec;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13.5px;
    line-height: 1.55
}

.prj-cat {
    margin: 14px 0 6px;
    font-size: 12.5px;
    font-weight: 700;
    color: #33404d
}

.prj-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 10px;
    border: 1px solid #e4e8ec;
    border-radius: 10px;
    margin-bottom: 6px
}

.prj-file.is-absent {
    background: #fffaf2;
    border-style: dashed
}

.prj-fico {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    font-size: 16px;
    flex: none;
    overflow: hidden;
    background: #eef1f4;
    color: #4a5560
}

.prj-fico img {
    width: 100%;
    height: 100%;
    object-fit: cover
}

.prj-fico.pdf {
    background: #fdecea;
    color: #c62828
}

.prj-fico.zip {
    background: #fff4e0;
    color: #b26a00
}

.prj-fico.img {
    background: #e3f0fc;
    color: #1565c0
}

.prj-fmeta {
    flex: 1;
    min-width: 0
}

.prj-fmeta b {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
}

.prj-fmeta small {
    color: #6b7785;
    font-size: 12px
}

.prj-file .cav-ibtn {
    text-decoration: none
}

.cav-tl li.d-soumission::before,
.cav-tl li.d-conforme::before {
    border-color: #1e7b3c
}

.cav-tl li.d-non_conforme::before {
    border-color: #c62828
}

.cav-tl li.d-remplacement_fichier::before,
.cav-tl li.d-depot_fichier::before {
    border-color: #1565c0
}

.cav-tl li.d-a_verifier::before {
    border-color: #b26a00
}

.cav-drawer-foot {
    flex-wrap: wrap
}

.cav-drawer-foot .prj-espace {
    flex: 1
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
        esc = CAV.esc;
    var D = root.dataset;
    var U = {
        liste: D.urlListe,
        detail: D.urlDetail,
        conformite: D.urlConformite,
        relancer: D.urlRelancer,
        fichier: D.urlFichier,
        apercu: D.urlApercu,
        zip: D.urlZip
    };
    var CONF = {
        a_verifier: 'À vérifier',
        conforme: 'Conforme',
        non_conforme: 'Non conforme'
    };
    var HIST = {
        creation: 'Dossier créé',
        depot_fichier: 'Fichier déposé',
        remplacement_fichier: 'Fichier remplacé',
        soumission: 'Projet soumis',
        conforme: 'Marqué conforme',
        non_conforme: 'Marqué non conforme',
        a_verifier: 'Remis à vérifier'
    };

    /** Actions disponibles. cible : 'projet' (ids de soumissions) ou 'candidat' (relance) */
    var ACTIONS = {
        conforme: {
            libelle: 'Conforme',
            icone: 'fa-check',
            btn: 'cav-btn-success',
            ibtn: 'cav-ibtn-ok',
            motif: false,
            titre: 'Marquer conforme',
            texte: 'Le dossier est complet et respecte le règlement. Il pourra être transmis au jury.'
        },
        non_conforme: {
            libelle: 'Non conforme',
            icone: 'fa-times',
            btn: 'cav-btn-danger',
            ibtn: 'cav-ibtn-ko',
            motif: true,
            titre: 'Marquer non conforme',
            texte: 'Le candidat sera prévenu par e-mail et pourra remplacer ses fichiers jusqu\'à la date limite.'
        },
        a_verifier: {
            libelle: 'Remettre à vérifier',
            icone: 'fa-undo',
            btn: 'cav-btn-ghost',
            ibtn: '',
            motif: false,
            titre: 'Remettre à vérifier',
            texte: 'Le verdict de conformité est annulé. Aucun e-mail n\'est envoyé.'
        },
        relancer: {
            libelle: 'Relancer',
            icone: 'fa-paper-plane',
            btn: 'cav-btn-success',
            ibtn: 'cav-ibtn-ok',
            motif: false,
            titre: 'Envoyer un rappel',
            texte: 'Un e-mail rappelle au candidat de déposer son projet avant la date limite.'
        }
    };

    var etat = {
        onglet: D.onglet || 'soumis',
        page: 1,
        ids: new Set(),
        lignes: {},
        detailId: null,
        action: null,
        cible: [],
        jeton: null
    };
    var form = $('#cav-filtres'),
        tbody = $('#cav-tbody'),
        thead = $('#cav-thead');

    /* ---------- Utilitaires locaux ---------- */
    function octets(o) {
        o = +o || 0;
        var u = ['o', 'Ko', 'Mo', 'Go', 'To'],
            i = 0;
        while (o >= 1024 && i < 4) {
            o /= 1024;
            i++;
        }
        return o.toLocaleString('fr-FR', {
            maximumFractionDigits: i > 1 ? 1 : 0
        }) + ' ' + u[i];
    }

    function nomCandidat(r) {
        return r.type_participation === 'equipe' && r.nom_equipe ?
            '<b>' + esc(r.nom_equipe) + '</b><small>Équipe · resp. ' + esc(r.prenom + ' ' + r.nom) + '</small>' :
            '<b>' + esc(r.prenom + ' ' + r.nom) + '</b><small>' + esc(r.email) + '</small>';
    }

    function badgeConf(c) {
        return '<span class="cav-st cav-st-' + esc(c) + '">' + esc(CONF[c] || c) + '</span>';
    }

    function actionsPour(r) {
        if (etat.onglet === 'non_soumis') return ['relancer'];
        if (r.statut !== 'soumis') return [];
        return {
            a_verifier: ['conforme', 'non_conforme'],
            conforme: ['non_conforme', 'a_verifier'],
            non_conforme: ['conforme', 'a_verifier']
        } [r.conformite] || [];
    }

    function idLigne(r) {
        return etat.onglet === 'non_soumis' ? r.candidat_id : r.id;
    }

    /* ---------- En-tête du tableau selon l'onglet ---------- */
    function rendreThead() {
        var cols = etat.onglet === 'non_soumis' ? ['Candidat', 'Participation', 'Organisation', 'Validé',
            'Contact'
        ] : ['Projet', 'Candidat', 'Pièces', etat.onglet === 'brouillon' ? 'Créé' : 'Soumis', etat
            .onglet === 'brouillon' ? 'Statut' : 'Conformité'
        ];
        var chk = etat.onglet === 'brouillon' ? '<th class="cav-col-chk"></th>' :
            '<th class="cav-col-chk"><input type="checkbox" id="cav-tout" aria-label="Tout sélectionner"></th>';
        thead.innerHTML = '<tr>' + chk + cols.map(function(c) {
                return '<th>' + c + '</th>';
            }).join('') +
            '<th class="cav-col-act">Actions</th></tr>';
        $('#cav-tri').disabled = etat.onglet === 'non_soumis';
    }

    /* ---------- Liste ---------- */
    function charger(page) {
        etat.page = page || etat.page;
        var p = new URLSearchParams(new FormData(form));
        p.set('onglet', etat.onglet);
        p.set('page', etat.page);
        tbody.style.opacity = '.55';

        var jeton = {};
        etat.jeton = jeton;
        CAV.requete(U.liste + '?' + p.toString()).then(function(j) {
            if (etat.jeton !== jeton) return;
            etat.page = j.page;
            rendreLignes(j.rows);
            CAV.pager($('#cav-pager'), $('#cav-resume'), j.total, j.page, j.pages, j.par_page,
                etat.onglet === 'non_soumis' ? 'candidat(s)' : 'projet(s)');
            majStats(j.stats);
        }).catch(function(e) {
            tbody.innerHTML = '<tr><td colspan="7" class="cav-state">' + esc(e.message) + '</td></tr>';
        }).then(function() {
            tbody.style.opacity = '';
        });
    }

    function rendreLignes(rows) {
        etat.lignes = {};
        if (!rows.length) {
            var vides = {
                soumis: ['fa-inbox', 'Aucun projet soumis',
                    'Les projets apparaîtront ici dès leur soumission.'
                ],
                a_verifier: ['fa-check-double', 'Tout est vérifié', 'Aucun projet en attente de contrôle.'],
                conforme: ['fa-clipboard-check', 'Aucun projet conforme',
                    'Les projets validés au contrôle apparaîtront ici.'
                ],
                non_conforme: ['fa-thumbs-up', 'Aucun projet non conforme', ''],
                brouillon: ['fa-pen', 'Aucun brouillon',
                    'Aucun candidat n\'a de dossier en cours de préparation.'
                ],
                non_soumis: ['fa-user-check', 'Tous les candidats ont commencé',
                    'Chaque candidature validée a au moins un brouillon ou un projet.'
                ]
            } [etat.onglet];
            var filtre = form.q.value || form.type.value || form.categorie.value;
            if (filtre) vides = ['fa-filter', 'Aucun résultat', 'Aucun élément ne correspond à ces filtres.'];
            tbody.innerHTML = '<tr><td colspan="7" class="cav-state"><div class="cav-empty"><i class="fas ' + vides[
                    0] + '"></i><b>' +
                vides[1] + '</b>' + vides[2] + '</div></td></tr>';
            majSelection();
            return;
        }

        tbody.innerHTML = rows.map(function(r) {
            var id = idLigne(r);
            etat.lignes[id] = r;
            var sel = etat.ids.has(String(id));
            var acts = actionsPour(r);
            var chk = etat.onglet === 'brouillon' ? '<td></td>' :
                '<td><input type="checkbox" class="cav-chk" value="' + id + '"' + (sel ? ' checked' : '') +
                ' aria-label="Sélectionner"></td>';
            var boutons = acts.map(function(a) {
                var A = ACTIONS[a];
                return '<button type="button" class="cav-ibtn ' + A.ibtn +
                    '" data-action="act" data-act="' + a + '" data-id="' + id + '" title="' + A
                    .libelle + '"><i class="fas ' + A.icone + '"></i></button>';
            }).join('');

            if (etat.onglet === 'non_soumis') {
                return '<tr data-id="' + id + '"' + (sel ? ' class="is-sel"' : '') + '>' + chk +
                    '<td><div class="cav-who"><span class="cav-av">' + esc(CAV.initiales(r)) +
                    '</span><div style="min-width:0">' + nomCandidat(r) + '</div></div></td>' +
                    '<td>' + (r.type_participation === 'equipe' ?
                        '<span class="cav-tag cav-tag-eq"><i class="fas fa-users"></i> Équipe</span>' :
                        '<span class="cav-tag cav-tag-ind"><i class="fas fa-user"></i> Individuel</span>') +
                    '</td>' +
                    '<td>' + esc(r.organisation || '—') + '<span class="cav-cell-sub">' + (r.categorie ===
                        'etudiant' ? 'Étudiant' : 'Professionnel') + '</span></td>' +
                    '<td title="' + esc(CAV.dateFr(r.decide_le)) + '">' + CAV.ilya(r.decide_le) + '</td>' +
                    '<td><a href="mailto:' + esc(r.email) + '">' + esc(r.email) + '</a>' + (r.telephone ?
                        '<span class="cav-cell-sub">' + esc(r.telephone) + '</span>' : '') + '</td>' +
                    '<td><div class="cav-acts">' + boutons + '</div></td></tr>';
            }

            var date = etat.onglet === 'brouillon' ? r.created_at : r.derniere_soumission_le;
            return '<tr data-id="' + id + '"' + (sel ? ' class="is-sel"' : '') + '>' + chk +
                '<td><span class="prj-code">' + esc(r.code_anonyme) + '</span>' + (+r.version > 1 ?
                    '<span class="prj-v">v' + esc(r.version) + '</span>' : '') +
                '<span class="prj-titre" data-action="voir" data-id="' + id + '" title="' + esc(r.titre) +
                '">' + esc(r.titre) + '</span></td>' +
                '<td><div class="cav-who" style="gap:0"><div style="min-width:0">' + nomCandidat(r) +
                '</div></div></td>' +
                '<td>' + esc(r.nb_fichiers) + ' fichier(s)<span class="cav-cell-sub">' + octets(r.taille) +
                '</span></td>' +
                '<td title="' + esc(CAV.dateFr(date)) + '">' + CAV.ilya(date) + '</td>' +
                '<td>' + (r.statut === 'soumis' ? badgeConf(r.conformite) :
                    '<span class="cav-st cav-st-brouillon">Brouillon</span>') + '</td>' +
                '<td><div class="cav-acts">' +
                '<button type="button" class="cav-ibtn" data-action="voir" data-id="' + id +
                '" title="Ouvrir le dossier"><i class="fas fa-eye"></i></button>' +
                (+r.nb_fichiers ? '<a class="cav-ibtn" href="' + U.zip + '/' + id +
                    '" title="Télécharger tout (ZIP)"><i class="fas fa-file-archive"></i></a>' : '') +
                boutons + '</div></td></tr>';
        }).join('');
        majSelection();
    }

    function majStats(st) {
        if (!st) return;
        $$('[data-stat]').forEach(function(el) {
            el.textContent = st[el.dataset.stat] != null ? st[el.dataset.stat] : 0;
        });
        $$('[data-stat-octets]').forEach(function(el) {
            el.textContent = octets(st[el.dataset.statOctets]);
        });
        var j = $('[data-jauge]');
        if (j) j.style.width = Math.min(100, st.taux || 0) + '%';
    }

    /* ---------- Sélection et actions groupées ---------- */
    function majSelection() {
        var n = etat.ids.size;
        $('#cav-nb-sel').textContent = n;
        $('#cav-bulk').hidden = n === 0;

        // Actions groupées : celles communes à toutes les lignes sélectionnées
        var communes = null;
        etat.ids.forEach(function(id) {
            var r = etat.lignes[id];
            if (!r) return;
            var a = actionsPour(r);
            communes = communes === null ? a : communes.filter(function(x) {
                return a.indexOf(x) !== -1;
            });
        });
        $('#cav-bulk-actions').innerHTML = (communes || []).map(function(a) {
            var A = ACTIONS[a];
            return '<button type="button" class="cav-btn ' + (A.btn === 'cav-btn-danger' ?
                    'cav-btn-danger-o' : A.btn) + '" data-action="act-sel" data-act="' + a +
                '"><i class="fas ' + A.icone + '"></i> ' + A.libelle + '</button>';
        }).join('') + '<button type="button" class="cav-link" data-action="deselectionner">Annuler</button>';

        var tout = $('#cav-tout');
        if (!tout) return;
        var chks = $$('.cav-chk', tbody),
            coches = chks.filter(function(c) {
                return c.checked;
            }).length;
        tout.checked = chks.length > 0 && coches === chks.length;
        tout.indeterminate = coches > 0 && coches < chks.length;
    }

    function viderSelection() {
        etat.ids.clear();
        $$('.cav-chk', tbody).forEach(function(c) {
            c.checked = false;
            c.closest('tr').classList.remove('is-sel');
        });
        majSelection();
    }

    /* ---------- Détail ---------- */
    function iconeFichier(f, apercuUrl) {
        var ext = (f.extension || '').toLowerCase();
        if ((ext === 'jpg' || ext === 'jpeg' || ext === 'png') && f.apercu) {
            return '<span class="prj-fico img"><img src="' + apercuUrl + '" alt="" loading="lazy"></span>';
        }
        var cls = ext === 'pdf' ? 'pdf' : ext === 'zip' ? 'zip' : (ext === 'jpg' || ext === 'jpeg' || ext ===
            'png') ? 'img' : '';
        var ico = {
            pdf: 'fa-file-pdf',
            zip: 'fa-file-archive',
            img: 'fa-file-image'
        } [cls] || 'fa-file';
        return '<span class="prj-fico ' + cls + '"><i class="fas ' + ico + '"></i></span>';
    }

    function ouvrirDetail(id) {
        etat.detailId = String(id);
        $('#cav-detail').innerHTML = '<p class="cav-muted">Chargement…</p>';
        $('#cav-detail-actions').innerHTML = '';
        $('#cav-drawer-ov').hidden = false;
        $('#cav-drawer').classList.add('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'false');

        CAV.requete(U.detail + '/' + id).then(function(j) {
            var p = j.projet,
                cats = j.categories,
                h = '';

            if (p.statut === 'brouillon') {
                h +=
                    '<div class="cav-alert cav-alert-warn"><i class="fas fa-pen"></i>Brouillon : le candidat n\'a pas encore soumis ce projet.</div>';
            } else if (p.conformite === 'non_conforme') {
                h += '<div class="cav-alert cav-alert-red"><i class="fas fa-exclamation-triangle"></i><div><b>Non conforme</b>' +
                    (p.motif_conformite ? ' — ' + esc(p.motif_conformite) : '') + '</div></div>';
            }
            var absents = p.fichiers.filter(function(f) {
                return !f.present;
            }).length;
            if (absents) {
                h += '<div class="cav-alert cav-alert-warn"><i class="fas fa-unlink"></i>' + absents +
                    ' fichier(s) référencé(s) introuvable(s) sur le serveur.</div>';
            }

            h += '<div class="cav-d-id" style="align-items:flex-start"><div>' +
                '<span class="prj-code">' + esc(p.code_anonyme) + '</span> ' +
                (p.statut === 'soumis' ? badgeConf(p.conformite) :
                    '<span class="cav-st cav-st-brouillon">Brouillon</span>') +
                '<h4 style="margin-top:8px">' + esc(p.titre) + '</h4>' +
                '<span class="cav-muted">' + (p.derniere_soumission_le ?
                    'Version ' + esc(p.version) + ' · soumise le ' + esc(CAV.dateFr(p
                        .derniere_soumission_le)) :
                    'Créé le ' + esc(CAV.dateFr(p.created_at))) + '</span></div></div>';

            h += '<div class="cav-d-sec">Candidat</div><dl class="cav-dl">' +
                '<dt>' + (p.type_participation === 'equipe' ? 'Équipe' : 'Candidat') + '</dt><dd>' +
                (p.type_participation === 'equipe' ? esc(p.nom_equipe || '—') +
                    '<br><small class="cav-muted">Resp. ' + esc(p.prenom + ' ' + p.nom) + '</small>' :
                    esc(p.prenom + ' ' + p.nom)) + '</dd>' +
                (p.membres.length ? '<dt>Membres</dt><dd>' + p.membres.map(function(m) {
                    return esc(m.prenom + ' ' + m.nom) + (m.role ? ' <small class="cav-muted">(' +
                        esc(m.role) + ')</small>' : '');
                }).join('<br>') + '</dd>' : '') +
                '<dt>Organisation</dt><dd>' + esc(p.organisation || '—') + ' · ' + (p.categorie ===
                    'etudiant' ? 'Étudiant' : 'Professionnel') + '</dd>' +
                '<dt>Contact</dt><dd><a href="mailto:' + esc(p.email) + '">' + esc(p.email) + '</a></dd>' +
                '<dt>Référence</dt><dd>' + esc(p.reference) + '</dd></dl>';

            h += '<div class="cav-d-sec">Note descriptive</div>' +
                (p.note_descriptive ? '<div class="prj-note">' + esc(p.note_descriptive) + '</div>' :
                    '<p class="cav-muted">Aucune note descriptive fournie.</p>');

            h += '<div class="cav-d-sec">Pièces déposées · ' + p.fichiers.length + ' fichier(s)</div>';
            if (!p.fichiers.length) {
                h += '<p class="cav-muted">Aucun fichier.</p>';
            } else {
                var courante = null;
                p.fichiers.forEach(function(f) {
                    if (f.categorie !== courante) {
                        courante = f.categorie;
                        h += '<div class="prj-cat">' + esc(cats[f.categorie] || f.categorie) +
                            '</div>';
                    }
                    var urlA = U.apercu + '/' + f.id,
                        urlF = U.fichier + '/' + f.id;
                    h += '<div class="prj-file' + (f.present ? '' : ' is-absent') + '">' +
                        iconeFichier(f, urlA) +
                        '<div class="prj-fmeta"><b title="' + esc(f.nom_original) + '">' + esc(f
                            .nom_original) + '</b>' +
                        '<small>' + esc((f.extension || '').toUpperCase()) + ' · ' + octets(f
                            .taille_octets) +
                        (+f.version > 1 ? ' · <span class="prj-v" style="margin:0">v' + esc(f
                            .version) + '</span>' : '') +
                        ' · ' + esc(CAV.dateFr(f.created_at)) + (f.present ? '' :
                            ' · <b style="display:inline;color:#b26a00">introuvable</b>') +
                        '</small></div>' +
                        (f.apercu ? '<a class="cav-ibtn" href="' + urlA +
                            '" target="_blank" rel="noopener" title="Aperçu"><i class="fas fa-external-link-alt"></i></a>' :
                            '') +
                        (f.present ? '<a class="cav-ibtn" href="' + urlF +
                            '" title="Télécharger"><i class="fas fa-download"></i></a>' : '') +
                        '</div>';
                });
                if (+p.nb_versions_remplacees) {
                    h += '<small class="cav-muted"><i class="fas fa-history"></i> ' + p
                        .nb_versions_remplacees +
                        ' version(s) antérieure(s) conservée(s) pour traçabilité.</small>';
                }
            }

            if (p.historique.length) {
                h += '<div class="cav-d-sec">Historique</div><ul class="cav-tl">' + p.historique.map(
                    function(e) {
                        return '<li class="d-' + esc(e.action) + '"><b>' + esc(HIST[e.action] || e
                                .action) +
                            ' <small>· ' + (e.auteur_type === 'admin' ? 'SATRACO' : 'candidat') +
                            '</small></b>' +
                            '<small>' + esc(CAV.dateFr(e.created_at)) + '</small>' + (e.detail ? '<p>' +
                                esc(e.detail) + '</p>' : '') + '</li>';
                    }).join('') + '</ul>';
            }
            $('#cav-detail').innerHTML = h;

            var pied = p.fichiers.some(function(f) {
                    return f.present;
                }) ?
                '<a class="cav-btn cav-btn-ghost" href="' + U.zip + '/' + p.id +
                '"><i class="fas fa-file-archive"></i> Tout télécharger</a>' : '';
            pied += '<span class="prj-espace"></span>';
            pied += actionsPour(p).map(function(a) {
                var A = ACTIONS[a];
                return '<button type="button" class="cav-btn ' + (A.btn === 'cav-btn-danger' ?
                        'cav-btn-danger-o' : A.btn) + '" data-action="act" data-act="' + a +
                    '" data-id="' + p.id + '"><i class="fas ' + A.icone + '"></i> ' + A.libelle +
                    '</button>';
            }).join('');
            $('#cav-detail-actions').innerHTML = pied;
            etat.lignes[p.id] = etat.lignes[p.id] || {
                id: p.id,
                titre: p.titre,
                code_anonyme: p.code_anonyme,
                statut: p.statut,
                conformite: p.conformite
            };
        }).catch(function(e) {
            $('#cav-detail').innerHTML = '<div class="cav-alert cav-alert-red">' + esc(e.message) +
                '</div>';
        });
    }

    function fermerDetail() {
        $('#cav-drawer').classList.remove('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'true');
        $('#cav-drawer-ov').hidden = true;
        etat.detailId = null;
    }

    /* ---------- Modale d'action ---------- */
    function libelleCible(ids) {
        if (ids.length > 1) return ids.length + ' élément(s) sélectionné(s)';
        var r = etat.lignes[ids[0]];
        if (!r) return '';
        return r.code_anonyme ? r.code_anonyme + ' — ' + r.titre : (r.nom_equipe || (r.prenom + ' ' + r.nom)) +
            ' · ' + r.email;
    }

    function ouvrirAction(action, ids) {
        var A = ACTIONS[action];
        etat.action = action;
        etat.cible = ids;
        $('#cav-action-titre').textContent = A.titre + (ids.length > 1 ? ' (' + ids.length + ')' : '');
        $('#cav-action-texte').textContent = A.texte;
        $('#cav-action-cible').textContent = libelleCible(ids);
        $('#cav-action-motif').hidden = !A.motif;
        $('#cav-motif').value = '';
        $('#cav-motif-n').textContent = '0';
        var ok = $('#cav-action-ok');
        ok.className = 'cav-btn ' + A.btn;
        ok.innerHTML = '<i class="fas ' + A.icone + '"></i> ' + A.libelle;
        $('#cav-action-ov').hidden = false;
        if (A.motif) setTimeout(function() {
            $('#cav-motif').focus();
        }, 50);
    }

    function confirmerAction(bouton) {
        var A = ACTIONS[etat.action],
            motif = $('#cav-motif').value.trim();
        if (A.motif && motif.length < 10) {
            CAV.toast('Le motif doit contenir au moins 10 caractères.', false);
            $('#cav-motif').focus();
            return;
        }
        var url = etat.action === 'relancer' ? U.relancer : U.conformite;
        var champs = etat.action === 'relancer' ? {
            ids: etat.cible
        } : {
            ids: etat.cible,
            verdict: etat.action,
            motif: A.motif ? motif : null
        };

        bouton.disabled = true;
        CAV.poster(url, champs).then(function(j) {
            $('#cav-action-ov').hidden = true;
            CAV.toast(j.message, true);
            if (etat.detailId && etat.cible.indexOf(etat.detailId) !== -1 && etat.action !== 'relancer')
                ouvrirDetail(etat.detailId);
            viderSelection();
            charger();
        }).catch(function(e) {
            CAV.toast(e.message, false);
        }).then(function() {
            bouton.disabled = false;
        });
    }

    /* ---------- Onglets ---------- */
    function changerOnglet(o) {
        if (o === etat.onglet) return;
        etat.onglet = o;
        $$('.cav-tab').forEach(function(t) {
            t.classList.toggle('is-active', t.dataset.onglet === o);
        });
        $$('.cav-kpi[data-kpi]').forEach(function(k) {
            k.classList.toggle('is-active', k.dataset.kpi === o);
        });
        etat.ids.clear();
        rendreThead();
        charger(1);
        try {
            history.replaceState(null, '', location.pathname + (o === 'soumis' ? '' : '?onglet=' + o));
        } catch (e) {}
    }

    /* ---------- Événements ---------- */
    var minuterie;
    form.addEventListener('input', function(e) {
        if (e.target.name === 'q') {
            clearTimeout(minuterie);
            minuterie = setTimeout(function() {
                charger(1);
            }, 350);
        }
    });
    form.addEventListener('change', function(e) {
        if (e.target.name !== 'q') charger(1);
    });

    $('#cav-tabs').addEventListener('click', function(e) {
        var t = e.target.closest('.cav-tab');
        if (t) changerOnglet(t.dataset.onglet);
    });
    $$('.cav-kpi[data-kpi]').forEach(function(k) {
        k.addEventListener('click', function() {
            changerOnglet(k.dataset.kpi);
        });
    });

    tbody.addEventListener('change', function(e) {
        if (!e.target.classList.contains('cav-chk')) return;
        e.target.checked ? etat.ids.add(e.target.value) : etat.ids.delete(e.target.value);
        e.target.closest('tr').classList.toggle('is-sel', e.target.checked);
        majSelection();
    });
    thead.addEventListener('change', function(e) {
        if (e.target.id !== 'cav-tout') return;
        var c = e.target.checked;
        $$('.cav-chk', tbody).forEach(function(chk) {
            chk.checked = c;
            c ? etat.ids.add(chk.value) : etat.ids.delete(chk.value);
            chk.closest('tr').classList.toggle('is-sel', c);
        });
        majSelection();
    });

    $('#cav-pager').addEventListener('click', function(e) {
        var b = e.target.closest('button[data-page]');
        if (b && !b.disabled) charger(+b.dataset.page);
    });

    $('#cav-motif').addEventListener('input', function() {
        $('#cav-motif-n').textContent = this.value.length;
    });
    $$('.cav-chip').forEach(function(ch) {
        ch.addEventListener('click', function() {
            var t = $('#cav-motif');
            t.value = (t.value.trim() ? t.value.trim() + '\n' : '') + ch.textContent;
            t.dispatchEvent(new Event('input'));
            t.focus();
        });
    });

    document.addEventListener('click', function(e) {
        var el = e.target.closest('[data-action]');
        if (!el) {
            if (e.target.classList.contains('cav-overlay-modal')) e.target.hidden = true;
            return;
        }
        switch (el.dataset.action) {
            case 'voir':
                ouvrirDetail(el.dataset.id);
                break;
            case 'act':
                ouvrirAction(el.dataset.act, [String(el.dataset.id)]);
                break;
            case 'act-sel':
                ouvrirAction(el.dataset.act, Array.from(etat.ids));
                break;
            case 'deselectionner':
                viderSelection();
                break;
            case 'fermer-detail':
                fermerDetail();
                break;
            case 'fermer-action':
                $('#cav-action-ov').hidden = true;
                break;
            case 'confirmer-action':
                confirmerAction(el);
                break;
            case 'rafraichir':
                charger();
                break;
            case 'reinitialiser':
                form.reset();
                charger(1);
                break;
        }
    });
    $('#cav-drawer-ov').addEventListener('click', fermerDetail);
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        if (!$('#cav-action-ov').hidden) $('#cav-action-ov').hidden = true;
        else if (etat.detailId) fermerDetail();
    });

    $$('.cav-kpi[data-kpi]').forEach(function(k) {
        k.classList.toggle('is-active', k.dataset.kpi === etat.onglet);
    });
    rendreThead();
    charger(1);
})();
</script>