<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Candidatures › Candidats & équipes
 * Variables : $title, $stats, $statut_initial, $csrf
 *
 * Registre de toutes les candidatures, quel que soit leur statut.
 * ⚠ Aligne le conteneur racine (.content-wrapper) sur tes autres composants si besoin.
 */
$s = array_merge([
    'total' => 0,
    'valide' => 0,
    'en_attente' => 0,
    'rejete' => 0,
    'suspendu' => 0,
    'equipes_validees' => 0,
    'individuels_valides' => 0,
    'participants' => 0,
    'etudiants_valides' => 0,
], $stats ?? []);

$onglets = [
    ''           => ['Tous',       $s['total']],
    'valide'     => ['Validés',    $s['valide']],
    'en_attente' => ['En attente', $s['en_attente']],
    'rejete'     => ['Rejetés',    $s['rejete']],
    'suspendu'   => ['Suspendus',  $s['suspendu']],
];
$statut_initial = $statut_initial ?? '';
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-liste="<?= site_url('candidats/liste') ?>"
        data-url-detail="<?= site_url('candidats/detail') ?>" data-url-statut="<?= site_url('candidats/statut') ?>"
        data-url-attente="<?= site_url('candidats-en-attente') ?>" data-statut="<?= html_escape($statut_initial) ?>"
        data-csrf-name="<?= html_escape($csrf['name']) ?>" data-csrf-hash="<?= html_escape($csrf['hash']) ?>">

        <!-- ============ EN-TÊTE ============ -->
        <header class="cav-head">
            <div>
                <p class="cav-crumb">Candidatures <span>›</span> Candidats &amp; équipes</p>
                <h1 class="cav-title">Candidats &amp; équipes</h1>
                <p class="cav-sub">Registre de toutes les inscriptions au concours, individuelles et en équipe.</p>
            </div>
            <a href="<?= site_url('candidats-en-attente') ?>" class="cav-btn cav-btn-ghost" id="cav-lien-attente"
                <?= $s['en_attente'] ? '' : 'hidden' ?>>
                <i class="fas fa-user-clock"></i> <span
                    data-stat="en_attente"><?= $s['en_attente'] ?></span>&nbsp;compte(s) à valider
            </a>
        </header>

        <!-- ============ INDICATEURS ============ -->
        <div class="cav-kpis">
            <button type="button" class="cav-kpi" data-kpi="">
                <span class="cav-kpi-ico cav-ico-blue"><i class="fas fa-id-card"></i></span>
                <span><b data-stat="total"><?= $s['total'] ?></b><small>Inscriptions au total</small>
                    <em><span data-stat="rejete"><?= $s['rejete'] ?></span> rejetée(s) · <span
                            data-stat="suspendu"><?= $s['suspendu'] ?></span> suspendue(s)</em></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="valide">
                <span class="cav-kpi-ico cav-ico-green"><i class="fas fa-user-check"></i></span>
                <span><b data-stat="valide"><?= $s['valide'] ?></b><small>Candidatures validées</small>
                    <em><span data-stat="equipes_validees"><?= $s['equipes_validees'] ?></span> équipe(s) · <span
                            data-stat="individuels_valides"><?= $s['individuels_valides'] ?></span>
                        individuel(s)</em></span>
            </button>
            <div class="cav-kpi">
                <span class="cav-kpi-ico cav-ico-grey"><i class="fas fa-users"></i></span>
                <span><b data-stat="participants"><?= $s['participants'] ?></b><small>Participants engagés</small>
                    <em>Responsables + membres d'équipe validés</em></span>
            </div>
            <button type="button" class="cav-kpi" data-kpi="en_attente">
                <span class="cav-kpi-ico cav-ico-amber"><i class="fas fa-hourglass-half"></i></span>
                <span><b data-stat="en_attente"><?= $s['en_attente'] ?></b><small>En attente de validation</small>
                    <em>À traiter dans « Comptes à valider »</em></span>
            </button>
        </div>

        <!-- ============ CARTE LISTE ============ -->
        <div class="cav-card">
            <!-- Onglets par statut -->
            <nav class="cav-tabs" id="cav-tabs" role="tablist">
                <?php foreach ($onglets as $cle => $o): ?>
                <button type="button" role="tab" class="cav-tab<?= $cle === $statut_initial ? ' is-active' : '' ?>"
                    data-statut="<?= $cle ?>">
                    <?= $o[0] ?> <span class="cav-cnt"
                        data-stat="<?= $cle === '' ? 'total' : $cle ?>"><?= $o[1] ?></span>
                </button>
                <?php endforeach; ?>
            </nav>

            <!-- Filtres -->
            <form class="cav-filters" id="cav-filtres" autocomplete="off" onsubmit="return false">
                <div class="cav-search">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" placeholder="Nom, e-mail, organisation, équipe, membre, référence…">
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
                <select name="tri">
                    <option value="recent">Inscription récente</option>
                    <option value="ancien">Inscription ancienne</option>
                    <option value="decision">Dernière décision</option>
                    <option value="nom">Nom (A→Z)</option>
                    <option value="organisation">Organisation (A→Z)</option>
                </select>
                <button type="button" class="cav-link" data-action="reinitialiser">Réinitialiser</button>
            </form>

            <!-- Barre d'actions groupées -->
            <div class="cav-bulk" id="cav-bulk" hidden>
                <span><b id="cav-nb-sel">0</b> sélectionné(s)</span>
                <div id="cav-bulk-actions"></div>
            </div>

            <!-- Tableau -->
            <div class="cav-table-wrap">
                <table class="cav-table">
                    <thead>
                        <tr>
                            <th class="cav-col-chk"><input type="checkbox" id="cav-tout" aria-label="Tout sélectionner">
                            </th>
                            <th>Candidat</th>
                            <th>Participation</th>
                            <th>Organisation</th>
                            <th>Statut</th>
                            <th>Inscrit</th>
                            <th class="cav-col-act">Actions</th>
                        </tr>
                    </thead>
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
    <aside class="cav-drawer" id="cav-drawer" aria-hidden="true">
        <div class="cav-drawer-head">
            <h3>Fiche candidat</h3>
            <button type="button" class="cav-x" data-action="fermer-detail" aria-label="Fermer">&times;</button>
        </div>
        <div class="cav-drawer-body" id="cav-detail"></div>
        <div class="cav-drawer-foot" id="cav-detail-actions"></div>
    </aside>

    <!-- ============ MODALE ACTION (motif obligatoire ou simple confirmation) ============ -->
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
                    <label class="cav-label" for="cav-motif">Motif communiqué au candidat <span
                            class="cav-req">*</span></label>
                    <textarea id="cav-motif" rows="4" maxlength="1000"
                        placeholder="Expliquez clairement la raison (envoyé par e-mail)."></textarea>
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

<script>
(function() {
    'use strict';
    var root = document.getElementById('cav');
    if (!root || !window.CAV) return;
    CAV.init(root);

    var $ = CAV.$,
        $$ = CAV.$$,
        esc = CAV.esc;
    var U = {
        liste: root.dataset.urlListe,
        detail: root.dataset.urlDetail,
        statut: root.dataset.urlStatut,
        attente: root.dataset.urlAttente
    };

    /** Actions possibles selon le statut actuel du compte */
    var ACTIONS = {
        suspendre: {
            de: 'valide',
            libelle: 'Suspendre',
            icone: 'fa-user-slash',
            motif: true,
            btn: 'cav-btn-danger',
            ibtn: 'cav-ibtn-warn',
            titre: 'Suspendre l\'accès',
            texte: 'Le candidat ne pourra plus se connecter ni soumettre de projet tant que le compte est suspendu. Il sera prévenu par e-mail.'
        },
        reactiver: {
            de: 'suspendu',
            libelle: 'Réactiver',
            icone: 'fa-user-check',
            motif: false,
            btn: 'cav-btn-success',
            ibtn: 'cav-ibtn-ok',
            titre: 'Réactiver le compte',
            texte: 'Le candidat retrouve l\'accès à son espace et en est informé par e-mail.'
        },
        rouvrir: {
            de: 'rejete',
            libelle: 'Remettre en attente',
            icone: 'fa-undo',
            motif: false,
            btn: 'cav-btn-success',
            ibtn: 'cav-ibtn-ok',
            titre: 'Remettre en attente',
            texte: 'Le dossier retourne dans « Comptes à valider » pour un nouvel examen. Aucun e-mail n\'est envoyé.'
        }
    };

    function actionPour(statut) {
        for (var k in ACTIONS) {
            if (ACTIONS[k].de === statut) return k;
        }
        return null;
    }

    var etat = {
        page: 1,
        statut: root.dataset.statut || '',
        ids: new Set(),
        lignes: {},
        action: null,
        cible: [],
        detailId: null,
        jeton: null
    };
    var form = $('#cav-filtres'),
        tbody = $('#cav-tbody');

    /* ---------- Chargement de la liste ---------- */
    function charger(page) {
        etat.page = page || etat.page;
        var p = new URLSearchParams(new FormData(form));
        p.set('statut', etat.statut);
        p.set('page', etat.page);
        tbody.style.opacity = '.55';

        var jeton = {};
        etat.jeton = jeton;
        CAV.requete(U.liste + '?' + p.toString()).then(function(j) {
            if (etat.jeton !== jeton) return; // réponse obsolète
            etat.page = j.page;
            etat.ids.clear();
            rendreLignes(j.rows);
            CAV.pager($('#cav-pager'), $('#cav-resume'), j.total, j.page, j.pages, j.par_page,
                'candidature(s)');
            majStats(j.stats);
        }).catch(function(e) {
            tbody.innerHTML = '<tr><td colspan="7" class="cav-state">' + esc(e.message) + '</td></tr>';
        }).then(function() {
            tbody.style.opacity = '';
        });
    }

    function boutonAction(r) {
        if (r.statut === 'en_attente') {
            return '<a class="cav-ibtn" href="' + esc(U.attente) +
                '" title="Traiter dans Comptes à valider"><i class="fas fa-arrow-right"></i></a>';
        }
        var a = actionPour(r.statut);
        if (!a) return '';
        return '<button type="button" class="cav-ibtn ' + ACTIONS[a].ibtn + '" data-action="act" data-act="' + a +
            '" data-id="' + r.id + '" title="' +
            esc(ACTIONS[a].libelle) + '"><i class="fas ' + ACTIONS[a].icone + '"></i></button>';
    }

    function rendreLignes(rows) {
        etat.lignes = {};
        if (!rows.length) {
            var filtre = form.q.value || form.type.value || form.categorie.value;
            tbody.innerHTML = '<tr><td colspan="7" class="cav-state"><div class="cav-empty">' +
                (filtre ?
                    '<i class="fas fa-filter"></i><b>Aucun résultat</b>Aucune candidature ne correspond à ces filtres.' :
                    '<i class="fas fa-inbox"></i><b>Aucune candidature</b>Rien à afficher pour ce statut.') +
                '</div></td></tr>';
            majSelection();
            return;
        }
        tbody.innerHTML = rows.map(function(r) {
            etat.lignes[r.id] = r;
            var nbPers = 1 + parseInt(r.nb_membres, 10);
            var participation = r.type_participation === 'equipe' ?
                '<span class="cav-tag cav-tag-eq"><i class="fas fa-users"></i> ' + esc(r.nom_equipe ||
                    'Équipe') + '</span>' +
                '<span class="cav-cell-sub">' + nbPers + ' personne' + (nbPers > 1 ? 's' : '') + '</span>' :
                '<span class="cav-tag cav-tag-ind"><i class="fas fa-user"></i> Individuel</span>';
            var cat = r.categorie === 'etudiant' ? 'Étudiant' : 'Professionnel';
            var decision = r.decide_le && r.statut !== 'en_attente' ?
                '<span class="cav-cell-sub" title="' + esc(CAV.dateFr(r.decide_le)) + '">le ' + esc(CAV
                    .dateCourte(r.decide_le)) + '</span>' : '';

            return '<tr data-id="' + r.id + '">' +
                '<td><input type="checkbox" class="cav-chk" value="' + r.id +
                '" aria-label="Sélectionner"></td>' +
                '<td><div class="cav-who"><span class="cav-av cav-av-' + esc(r.statut) + '">' + esc(CAV
                    .initiales(r)) + '</span><div style="min-width:0">' +
                '<b data-action="voir" data-id="' + r.id + '">' + esc(r.prenom + ' ' + r.nom) + '</b>' +
                '<small>' + esc(r.reference) + ' · ' + esc(r.email) + '</small></div></div></td>' +
                '<td>' + participation + '</td>' +
                '<td>' + esc(r.organisation || '—') + '<span class="cav-cell-sub">' + cat + '</span></td>' +
                '<td>' + CAV.badgeStatut(r.statut) + decision + '</td>' +
                '<td title="' + esc(CAV.dateFr(r.created_at)) + '">' + esc(CAV.dateCourte(r.created_at)) +
                '</td>' +
                '<td><div class="cav-acts">' +
                '<button type="button" class="cav-ibtn" data-action="voir" data-id="' + r.id +
                '" title="Voir la fiche"><i class="fas fa-eye"></i></button>' +
                boutonAction(r) +
                '</div></td></tr>';
        }).join('');
        majSelection();
    }

    function majStats(s) {
        if (!s) return;
        $$('[data-stat]').forEach(function(el) {
            el.textContent = s[el.dataset.stat] || 0;
        });
        $('#cav-lien-attente').hidden = !s.en_attente;
        CAV.majBadgeMenu(s.en_attente);
    }

    /* ---------- Onglets / KPI ---------- */
    function choisirStatut(st) {
        etat.statut = st;
        $$('.cav-tab').forEach(function(t) {
            t.classList.toggle('is-active', t.dataset.statut === st);
        });
        try {
            var url = new URL(window.location.href);
            st ? url.searchParams.set('statut', st) : url.searchParams.delete('statut');
            window.history.replaceState(null, '', url.toString());
        } catch (e) {
            /* navigateur ancien : on ignore */
        }
        charger(1);
    }
    $('#cav-tabs').addEventListener('click', function(e) {
        var t = e.target.closest('.cav-tab');
        if (t) choisirStatut(t.dataset.statut);
    });
    $$('button.cav-kpi[data-kpi]').forEach(function(k) {
        k.addEventListener('click', function() {
            choisirStatut(k.dataset.kpi);
        });
    });

    /* ---------- Sélection & actions groupées ---------- */
    function majSelection() {
        var visibles = $$('.cav-chk', tbody);
        var n = etat.ids.size;
        $('#cav-nb-sel').textContent = n;
        $('#cav-bulk').hidden = n === 0;

        var tout = $('#cav-tout');
        var cochees = visibles.filter(function(c) {
            return c.checked;
        }).length;
        tout.checked = visibles.length > 0 && cochees === visibles.length;
        tout.indeterminate = cochees > 0 && cochees < visibles.length;

        if (!n) return;
        var statuts = {};
        etat.ids.forEach(function(id) {
            if (etat.lignes[id]) statuts[etat.lignes[id].statut] = true;
        });
        var liste = Object.keys(statuts),
            zone = $('#cav-bulk-actions');
        var a = liste.length === 1 ? actionPour(liste[0]) : null;

        if (a) {
            zone.innerHTML = '<button type="button" class="cav-btn ' + (a === 'suspendre' ? 'cav-btn-danger-o' :
                    'cav-btn-success') +
                '" data-action="act-sel" data-act="' + a + '"><i class="fas ' + ACTIONS[a].icone + '"></i> ' + esc(
                    ACTIONS[a].libelle) + '</button>' +
                ' <button type="button" class="cav-link" data-action="deselectionner">Annuler</button>';
        } else {
            zone.innerHTML = '<span class="cav-bulk-info">' + (liste.length > 1 ?
                    'Sélection de statuts différents : filtrez par onglet pour agir en masse.' :
                    'Aucune action groupée pour ce statut.') +
                '</span> <button type="button" class="cav-link" data-action="deselectionner">Annuler</button>';
        }
    }
    tbody.addEventListener('change', function(e) {
        if (!e.target.classList.contains('cav-chk')) return;
        e.target.checked ? etat.ids.add(e.target.value) : etat.ids.delete(e.target.value);
        e.target.closest('tr').classList.toggle('is-sel', e.target.checked);
        majSelection();
    });
    $('#cav-tout').addEventListener('change', function() {
        var c = this.checked;
        $$('.cav-chk', tbody).forEach(function(chk) {
            chk.checked = c;
            c ? etat.ids.add(chk.value) : etat.ids.delete(chk.value);
            chk.closest('tr').classList.toggle('is-sel', c);
        });
        majSelection();
    });

    /* ---------- Fiche détail ---------- */
    function ouvrirDetail(id) {
        etat.detailId = id;
        $('#cav-detail').innerHTML = '<p class="cav-muted">Chargement…</p>';
        $('#cav-detail-actions').innerHTML = '';
        $('#cav-drawer-ov').hidden = false;
        $('#cav-drawer').classList.add('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'false');

        CAV.requete(U.detail + '/' + id).then(function(j) {
            var c = j.candidat,
                h = '';
            etat.lignes[c.id] = etat.lignes[c.id] || c; // pour le libellé de la modale

            if (c.doublons && c.doublons.length) {
                h += '<div class="cav-alert cav-alert-red"><i class="fas fa-clone"></i><div>Doublon possible (même téléphone ou n° d\'ordre) : ' +
                    c.doublons.map(function(d) {
                        return esc(d.reference + ' — ' + d.prenom + ' ' + d.nom + ' (' + (CAV.STATUTS[d
                            .statut] || d.statut) + ')');
                    }).join(', ') +
                    '</div></div>';
            }
            if (c.statut === 'rejete' && c.motif_rejet) {
                h += '<div class="cav-alert cav-alert-red"><i class="fas fa-ban"></i><div><b>Motif du rejet :</b> ' +
                    esc(c.motif_rejet) + '</div></div>';
            }

            h += '<div class="cav-d-id"><span class="cav-av cav-av-' + esc(c.statut) + '">' + esc(CAV
                    .initiales(c)) + '</span><div>' +
                '<h4>' + esc(c.prenom + ' ' + c.nom) + '</h4>' + CAV.badgeStatut(c.statut) +
                ' <span class="cav-muted">' + esc(c.reference) + '</span></div></div>';

            h += '<div class="cav-d-sec">Coordonnées</div><dl class="cav-dl">' +
                '<dt>E-mail</dt><dd><a href="mailto:' + esc(c.email) + '">' + esc(c.email) + '</a>' +
                (c.email_verifie_le ? ' <span class="cav-tag cav-tag-ok">vérifié</span>' :
                    ' <span class="cav-tag cav-tag-ko">non vérifié</span>') + '</dd>' +
                '<dt>Téléphone</dt><dd>' + (c.telephone ? '<a href="tel:' + esc(c.telephone) + '">' + esc(c
                    .telephone) + '</a>' : '—') + '</dd>' +
                '<dt>Inscrit le</dt><dd>' + esc(CAV.dateFr(c.created_at)) + '</dd>' +
                (c.decide_le ? '<dt>Dernière décision</dt><dd>' + esc(CAV.dateFr(c.decide_le)) + '</dd>' :
                    '') + '</dl>';

            h += '<div class="cav-d-sec">Éligibilité</div><dl class="cav-dl">' +
                '<dt>Catégorie</dt><dd>' + (c.categorie === 'etudiant' ? 'Étudiant' : 'Professionnel') +
                '</dd>' +
                '<dt>Organisation</dt><dd>' + esc(c.organisation || '—') + '</dd>' +
                '<dt>' + (c.categorie === 'etudiant' ? 'Matricule' : 'N° Ordre') + '</dt><dd>' + esc(c
                    .numero_ordre || '—') + '</dd></dl>';

            if (c.type_participation === 'equipe') {
                h += '<div class="cav-d-sec">Équipe « ' + esc(c.nom_equipe || '—') + ' » · ' + (c.membres
                    .length + 1) + ' membre(s)</div>';
                h += '<div class="cav-member"><div><b>' + esc(c.prenom + ' ' + c.nom) + '</b><small>' + esc(
                        c.email) + '</small></div>' +
                    '<span class="cav-tag cav-tag-ok">Responsable</span></div>';
                h += c.membres.map(function(m) {
                    return '<div class="cav-member"><div><b>' + esc(m.prenom + ' ' + m.nom) +
                        '</b><small>' + esc(m.email || m.telephone || '—') + '</small></div>' +
                        '<small>' + esc(m.role || '') + '</small></div>';
                }).join('');
            }

            h += CAV.timeline(c.historique);
            $('#cav-detail').innerHTML = h;

            var a = actionPour(c.statut),
                pied = '';
            if (c.statut === 'en_attente') {
                pied = '<a class="cav-btn cav-btn-success" href="' + esc(U.attente) +
                    '"><i class="fas fa-arrow-right"></i> Traiter dans Comptes à valider</a>';
            } else if (a) {
                pied = '<button type="button" class="cav-btn ' + (a === 'suspendre' ? 'cav-btn-danger-o' :
                        'cav-btn-success') +
                    '" data-action="act" data-act="' + a + '" data-id="' + c.id + '"><i class="fas ' +
                    ACTIONS[a].icone + '"></i> ' + esc(ACTIONS[a].libelle) + '</button>';
            }
            $('#cav-detail-actions').innerHTML = pied;
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

    /* ---------- Changement de statut ---------- */
    function libelleCible(ids) {
        if (ids.length === 1) {
            var r = etat.lignes[ids[0]];
            return r ? r.prenom + ' ' + r.nom + (r.nom_equipe ? ' (équipe « ' + r.nom_equipe + ' »)' : '') :
                '1 compte';
        }
        return ids.length + ' comptes sélectionnés';
    }

    function demanderAction(action, ids) {
        var a = ACTIONS[action];
        etat.action = action;
        etat.cible = ids;
        $('#cav-action-titre').textContent = a.titre + (ids.length > 1 ? ' (' + ids.length + ')' : '');
        $('#cav-action-texte').textContent = a.texte;
        $('#cav-action-cible').textContent = 'Concerne : ' + libelleCible(ids);
        $('#cav-action-motif').hidden = !a.motif;
        $('#cav-motif').value = '';
        $('#cav-motif-n').textContent = '0';
        var ok = $('#cav-action-ok');
        ok.className = 'cav-btn ' + a.btn;
        ok.innerHTML = '<i class="fas ' + a.icone + '"></i> ' + esc(a.libelle);
        $('#cav-action-ov').hidden = false;
        if (a.motif) setTimeout(function() {
            $('#cav-motif').focus();
        }, 50);
    }

    function confirmerAction(bouton) {
        var a = ACTIONS[etat.action],
            motif = $('#cav-motif').value.trim();
        if (a.motif && motif.length < 10) {
            CAV.toast('Le motif doit contenir au moins 10 caractères.', false);
            $('#cav-motif').focus();
            return;
        }
        bouton.disabled = true;
        CAV.poster(U.statut, {
            action: etat.action,
            ids: etat.cible,
            motif: a.motif ? motif : null
        }).then(function(j) {
            $('#cav-action-ov').hidden = true;
            CAV.toast(j.message, true);
            CAV.majBadgeMenu(j.restants);
            if (etat.detailId && j.traites.indexOf(+etat.detailId) !== -1) {
                ouvrirDetail(etat.detailId); // recharge la fiche avec le nouveau statut
            }
            // Si l'onglet courant filtre par statut, les lignes traitées disparaissent
            var restantes = $$('tr[data-id]', tbody).length - (etat.statut ? j.traites.length : 0);
            charger(restantes <= 0 && etat.page > 1 ? etat.page - 1 : etat.page);
        }).catch(function(e) {
            CAV.toast(e.message, false);
        }).then(function() {
            bouton.disabled = false;
        });
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

    $('#cav-pager').addEventListener('click', function(e) {
        var b = e.target.closest('button[data-page]');
        if (b && !b.disabled) charger(+b.dataset.page);
    });
    $('#cav-motif').addEventListener('input', function() {
        $('#cav-motif-n').textContent = this.value.length;
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
                demanderAction(el.dataset.act, [el.dataset.id]);
                break;
            case 'act-sel':
                demanderAction(el.dataset.act, Array.from(etat.ids));
                break;
            case 'confirmer-action':
                confirmerAction(el);
                break;
            case 'fermer-action':
                $('#cav-action-ov').hidden = true;
                break;
            case 'fermer-detail':
                fermerDetail();
                break;
            case 'reinitialiser':
                form.reset();
                charger(1);
                break;
            case 'deselectionner':
                etat.ids.clear();
                $$('.cav-chk', tbody).forEach(function(c) {
                    c.checked = false;
                    c.closest('tr').classList.remove('is-sel');
                });
                majSelection();
                break;
        }
    });
    $('#cav-drawer-ov').addEventListener('click', fermerDetail);
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        if (!$('#cav-action-ov').hidden) $('#cav-action-ov').hidden = true;
        else if (etat.detailId) fermerDetail();
    });

    charger(1);
})();
</script>