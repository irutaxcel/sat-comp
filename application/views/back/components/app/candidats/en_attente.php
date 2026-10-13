<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Candidatures › Comptes à valider
 * Variables : $title, $stats, $csrf
 *
 * Vue autonome (CSS + JS scopés « cav- », sans dépendance jQuery/Bootstrap),
 * compatible AdminLTE 3 et 4.
 * ⚠ Si tes autres composants utilisent un autre conteneur racine que
 *   .content-wrapper (ex. <main class="app-main">), aligne la 1re balise ci-dessous.
 */
$s = array_merge(['total' => 0, 'aujourdhui' => 0, 'email_non_verifie' => 0, 'plus_48h' => 0], $stats ?? []);
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-liste="<?= site_url('candidats-en-attente/liste') ?>"
        data-url-detail="<?= site_url('candidats-en-attente/detail') ?>"
        data-url-valider="<?= site_url('candidats-en-attente/valider') ?>"
        data-url-rejeter="<?= site_url('candidats-en-attente/rejeter') ?>"
        data-csrf-name="<?= html_escape($csrf['name']) ?>" data-csrf-hash="<?= html_escape($csrf['hash']) ?>">

        <!-- ============ EN-TÊTE ============ -->
        <header class="cav-head">
            <div>
                <p class="cav-crumb">Candidatures <span>›</span> Comptes à valider</p>
                <h1 class="cav-title">Comptes à valider</h1>
                <p class="cav-sub">Vérifiez l'éligibilité des inscrits avant de leur ouvrir l'accès à l'espace candidat.
                </p>
            </div>
            <button type="button" class="cav-btn cav-btn-ghost" data-action="rafraichir" title="Actualiser">
                <i class="fas fa-sync-alt"></i> Actualiser
            </button>
        </header>

        <!-- ============ INDICATEURS (cliquables = filtres rapides) ============ -->
        <div class="cav-kpis">
            <button type="button" class="cav-kpi" data-kpi="">
                <span class="cav-kpi-ico cav-ico-green"><i class="fas fa-user-clock"></i></span>
                <span><b data-stat="total"><?= $s['total'] ?></b><small>En attente</small></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="aujourdhui">
                <span class="cav-kpi-ico cav-ico-blue"><i class="fas fa-calendar-day"></i></span>
                <span><b data-stat="aujourdhui"><?= $s['aujourdhui'] ?></b><small>Inscrits aujourd'hui</small></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="non_verifie">
                <span class="cav-kpi-ico cav-ico-amber"><i class="fas fa-envelope"></i></span>
                <span><b data-stat="email_non_verifie"><?= $s['email_non_verifie'] ?></b><small>E-mail non
                        vérifié</small></span>
            </button>
            <button type="button" class="cav-kpi" data-kpi="plus_48h">
                <span class="cav-kpi-ico cav-ico-red"><i class="fas fa-hourglass-half"></i></span>
                <span><b data-stat="plus_48h"><?= $s['plus_48h'] ?></b><small>En attente &gt; 48 h</small></span>
            </button>
        </div>

        <!-- ============ CARTE LISTE ============ -->
        <div class="cav-card">
            <!-- Filtres -->
            <form class="cav-filters" id="cav-filtres" autocomplete="off" onsubmit="return false">
                <div class="cav-search">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" placeholder="Nom, e-mail, organisation, équipe, référence…">
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
                <select name="email">
                    <option value="">E-mail : tous</option>
                    <option value="verifie">E-mail vérifié</option>
                    <option value="non_verifie">E-mail non vérifié</option>
                </select>
                <select name="anciennete">
                    <option value="">Toutes dates</option>
                    <option value="aujourdhui">Aujourd'hui</option>
                    <option value="plus_48h">Plus de 48 h</option>
                </select>
                <select name="tri">
                    <option value="ancien">Plus anciens d'abord</option>
                    <option value="recent">Plus récents d'abord</option>
                    <option value="nom">Nom (A→Z)</option>
                    <option value="organisation">Organisation (A→Z)</option>
                </select>
                <button type="button" class="cav-link" data-action="reinitialiser">Réinitialiser</button>
            </form>

            <!-- Barre d'actions groupées -->
            <div class="cav-bulk" id="cav-bulk" hidden>
                <span><b id="cav-nb-sel">0</b> sélectionné(s)</span>
                <div>
                    <button type="button" class="cav-btn cav-btn-success" data-action="valider-sel">
                        <i class="fas fa-check"></i> Valider
                    </button>
                    <button type="button" class="cav-btn cav-btn-danger-o" data-action="rejeter-sel">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                    <button type="button" class="cav-link" data-action="deselectionner">Annuler</button>
                </div>
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
                            <th>Inscrit</th>
                            <th>E-mail</th>
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

            <!-- Pagination -->
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

    <!-- ============ MODALE REJET ============ -->
    <div class="cav-overlay cav-overlay-modal" id="cav-rejet-ov" hidden>
        <div class="cav-modal" role="dialog" aria-modal="true" aria-labelledby="cav-rejet-titre">
            <div class="cav-modal-head">
                <h3 id="cav-rejet-titre">Rejeter l'inscription</h3>
                <button type="button" class="cav-x" data-action="fermer-rejet" aria-label="Fermer">&times;</button>
            </div>
            <div class="cav-modal-body">
                <p class="cav-muted" id="cav-rejet-cible"></p>
                <label class="cav-label">Motif rapide</label>
                <div class="cav-chips">
                    <button type="button" class="cav-chip">Dossier incomplet ou informations manquantes.</button>
                    <button type="button" class="cav-chip">Ne remplit pas les critères d'éligibilité du
                        concours.</button>
                    <button type="button" class="cav-chip">Inscription en doublon : un compte existe déjà pour ce
                        candidat.</button>
                    <button type="button" class="cav-chip">Numéro d'inscription à l'Ordre / matricule non
                        vérifiable.</button>
                </div>
                <label class="cav-label" for="cav-motif">Motif communiqué au candidat <span
                        class="cav-req">*</span></label>
                <textarea id="cav-motif" rows="4" maxlength="1000"
                    placeholder="Expliquez clairement la raison du rejet (envoyé par e-mail)."></textarea>
                <small class="cav-muted"><span id="cav-motif-n">0</span>/1000 — 10 caractères minimum</small>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer-rejet">Annuler</button>
                <button type="button" class="cav-btn cav-btn-danger" data-action="confirmer-rejet">
                    <i class="fas fa-times"></i> Confirmer le rejet
                </button>
            </div>
        </div>
    </div>

    <!-- ============ MODALE CONFIRMATION VALIDATION ============ -->
    <div class="cav-overlay cav-overlay-modal" id="cav-valid-ov" hidden>
        <div class="cav-modal cav-modal-sm" role="dialog" aria-modal="true">
            <div class="cav-modal-body cav-center">
                <span class="cav-big-ico cav-ico-green"><i class="fas fa-user-check"></i></span>
                <h3 id="cav-valid-titre">Valider ce compte ?</h3>
                <p class="cav-muted" id="cav-valid-texte"></p>
            </div>
            <div class="cav-modal-foot">
                <button type="button" class="cav-btn cav-btn-ghost" data-action="fermer-valid">Annuler</button>
                <button type="button" class="cav-btn cav-btn-success" data-action="confirmer-valid">
                    <i class="fas fa-check"></i> Valider
                </button>
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
    if (!root) return;

    var U = {
        liste: root.dataset.urlListe,
        detail: root.dataset.urlDetail,
        valider: root.dataset.urlValider,
        rejeter: root.dataset.urlRejeter
    };
    var etat = {
        page: 1,
        ids: new Set(),
        lignes: {},
        cibleDecision: [],
        detailId: null,
        chargement: null
    };

    CAV.init(root);
    var $ = CAV.$,
        $$ = CAV.$$,
        esc = CAV.esc,
        initiales = CAV.initiales,
        ilya = CAV.ilya,
        dateFr = CAV.dateFr,
        enRetard = CAV.enRetard,
        toast = CAV.toast,
        requete = CAV.requete,
        poster = CAV.poster,
        majBadgeMenu = CAV.majBadgeMenu;
    var form = $('#cav-filtres'),
        tbody = $('#cav-tbody');
    /* ---------- Liste ---------- */
    function charger(page) {
        etat.page = page || etat.page;
        var p = new URLSearchParams(new FormData(form));
        p.set('page', etat.page);
        tbody.style.opacity = '.55';

        var jeton = {};
        etat.chargement = jeton;
        requete(U.liste + '?' + p.toString()).then(function(j) {
            if (etat.chargement !== jeton) return; // réponse obsolète
            etat.page = j.page;
            rendreLignes(j.rows);
            rendrePager(j.total, j.page, j.pages, j.par_page);
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
            var filtre = Array.prototype.some.call(form.elements, function(el) {
                return el.name !== 'tri' && el.value;
            });
            tbody.innerHTML = '<tr><td colspan="7" class="cav-state"><div class="cav-empty">' +
                (filtre ?
                    '<i class="fas fa-filter"></i><b>Aucun résultat</b>Aucun compte ne correspond à ces filtres.' :
                    '<i class="fas fa-check-double"></i><b>Tout est à jour</b>Aucun compte en attente de validation.'
                    ) +
                '</div></td></tr>';
            majSelection();
            return;
        }
        tbody.innerHTML = rows.map(function(r) {
            etat.lignes[r.id] = r;
            var sel = etat.ids.has(String(r.id));
            var participation = r.type_participation === 'equipe' ?
                '<span class="cav-tag cav-tag-eq"><i class="fas fa-users"></i> Équipe</span>' +
                '<span class="cav-cell-sub">' + esc(r.nom_equipe || '—') + ' · ' + (1 + parseInt(r
                    .nb_membres, 10)) + ' pers.</span>' :
                '<span class="cav-tag cav-tag-ind"><i class="fas fa-user"></i> Individuel</span>';
            var cat = r.categorie === 'etudiant' ? 'Étudiant' : 'Professionnel';
            return '<tr data-id="' + r.id + '"' + (sel ? ' class="is-sel"' : '') + '>' +
                '<td><input type="checkbox" class="cav-chk" value="' + r.id + '"' + (sel ? ' checked' :
                '') + ' aria-label="Sélectionner"></td>' +
                '<td><div class="cav-who"><span class="cav-av">' + esc(initiales(r)) +
                '</span><div style="min-width:0">' +
                '<b data-action="voir" data-id="' + r.id + '">' + esc(r.prenom + ' ' + r.nom) + '</b>' +
                '<small>' + esc(r.email) + '</small></div></div></td>' +
                '<td>' + participation + '</td>' +
                '<td>' + esc(r.organisation || '—') + '<span class="cav-cell-sub">' + cat + (r
                    .numero_ordre ? ' · ' + esc(r.numero_ordre) : '') + '</span></td>' +
                '<td title="' + esc(dateFr(r.created_at)) + '">' +
                (enRetard(r.created_at) ? '<span class="cav-tag cav-tag-late">' + ilya(r.created_at) +
                    '</span>' : ilya(r.created_at)) + '</td>' +
                '<td>' + (r.email_verifie_le ?
                    '<span class="cav-tag cav-tag-ok"><i class="fas fa-check"></i> Vérifié</span>' :
                    '<span class="cav-tag cav-tag-ko"><i class="fas fa-clock"></i> Non vérifié</span>') +
                '</td>' +
                '<td><div class="cav-acts">' +
                '<button type="button" class="cav-ibtn" data-action="voir" data-id="' + r.id +
                '" title="Voir la fiche"><i class="fas fa-eye"></i></button>' +
                '<button type="button" class="cav-ibtn cav-ibtn-ok" data-action="valider" data-id="' + r
                .id + '" title="Valider"><i class="fas fa-check"></i></button>' +
                '<button type="button" class="cav-ibtn cav-ibtn-ko" data-action="rejeter" data-id="' + r
                .id + '" title="Rejeter"><i class="fas fa-times"></i></button>' +
                '</div></td></tr>';
        }).join('');
        majSelection();
    }

    function rendrePager(total, page, pages, parPage) {
        CAV.pager($('#cav-pager'), $('#cav-resume'), total, page, pages, parPage || 15, 'compte(s)');
    }

    function majStats(s) {
        if (!s) return;
        $$('[data-stat]').forEach(function(el) {
            el.textContent = s[el.dataset.stat] || 0;
        });
        majBadgeMenu(s.total);
    }

    /* ---------- Sélection ---------- */
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
    }

    /* ---------- Détail ---------- */
    function ouvrirDetail(id) {
        etat.detailId = id;
        $('#cav-detail').innerHTML = '<p class="cav-muted">Chargement…</p>';
        $('#cav-detail-actions').innerHTML = '';
        $('#cav-drawer-ov').hidden = false;
        $('#cav-drawer').classList.add('is-open');
        $('#cav-drawer').setAttribute('aria-hidden', 'false');

        requete(U.detail + '/' + id).then(function(j) {
            var c = j.candidat,
                h = '';
            if (c.statut !== 'en_attente') {
                h += '<div class="cav-alert cav-alert-warn"><i class="fas fa-info-circle"></i>Ce compte a déjà été traité (' +
                    esc(c.statut) + ').</div>';
            }
            if (!c.email_verifie_le) {
                h +=
                '<div class="cav-alert cav-alert-warn"><i class="fas fa-envelope"></i>L\'adresse e-mail n\'a pas encore été confirmée par le candidat.</div>';
            }
            if (c.doublons && c.doublons.length) {
                h += '<div class="cav-alert cav-alert-red"><i class="fas fa-clone"></i><div>Doublon possible (même téléphone ou n° d\'ordre) : ' +
                    c.doublons.map(function(d) {
                        return esc(d.reference + ' — ' + d.prenom + ' ' + d.nom + ' (' + d.statut +
                        ')');
                    }).join(', ') + '</div></div>';
            }
            h += '<div class="cav-d-id"><span class="cav-av">' + esc(initiales(c)) + '</span><div><h4>' +
                esc(c.prenom + ' ' + c.nom) + '</h4>' +
                '<span class="cav-tag ' + (c.type_participation === 'equipe' ? 'cav-tag-eq' :
                'cav-tag-ind') + '">' +
                (c.type_participation === 'equipe' ? 'Équipe' : 'Individuel') +
                '</span> <span class="cav-muted">' + esc(c.reference) + '</span></div></div>';

            h += '<div class="cav-d-sec">Coordonnées</div><dl class="cav-dl">' +
                '<dt>E-mail</dt><dd><a href="mailto:' + esc(c.email) + '">' + esc(c.email) + '</a></dd>' +
                '<dt>Téléphone</dt><dd>' + (c.telephone ? '<a href="tel:' + esc(c.telephone) + '">' + esc(c
                    .telephone) + '</a>' : '—') + '</dd>' +
                '<dt>Inscrit le</dt><dd>' + esc(dateFr(c.created_at)) + '</dd>' +
                '<dt>E-mail vérifié</dt><dd>' + (c.email_verifie_le ? esc(dateFr(c.email_verifie_le)) :
                    'Non') + '</dd></dl>';

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
                    c.email) + '</small></div><span class="cav-tag cav-tag-ok">Responsable</span></div>';
                h += c.membres.map(function(m) {
                    return '<div class="cav-member"><div><b>' + esc(m.prenom + ' ' + m.nom) +
                        '</b><small>' + esc(m.email || m.telephone || '—') + '</small></div>' +
                        '<small>' + esc(m.role || '') + '</small></div>';
                }).join('') || '';
            }
            h += CAV.timeline(c.historique);
            $('#cav-detail').innerHTML = h;

            if (c.statut === 'en_attente') {
                $('#cav-detail-actions').innerHTML =
                    '<button type="button" class="cav-btn cav-btn-danger-o" data-action="rejeter" data-id="' +
                    c.id + '"><i class="fas fa-times"></i> Rejeter</button>' +
                    '<button type="button" class="cav-btn cav-btn-success" data-action="valider" data-id="' +
                    c.id + '"><i class="fas fa-check"></i> Valider le compte</button>';
            }
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

    /* ---------- Décisions ---------- */
    function libelleCible(ids) {
        if (ids.length === 1) {
            var r = etat.lignes[ids[0]];
            return r ? r.prenom + ' ' + r.nom + (r.nom_equipe ? ' (équipe « ' + r.nom_equipe + ' »)' : '') :
                '1 compte';
        }
        return ids.length + ' comptes sélectionnés';
    }

    function demanderValidation(ids) {
        etat.cibleDecision = ids;
        $('#cav-valid-titre').textContent = ids.length > 1 ? 'Valider ' + ids.length + ' comptes ?' :
            'Valider ce compte ?';
        $('#cav-valid-texte').textContent = libelleCible(ids) +
            ' — un e-mail de confirmation sera envoyé et l\'accès à l\'espace candidat sera ouvert.';
        $('#cav-valid-ov').hidden = false;
    }

    function demanderRejet(ids) {
        etat.cibleDecision = ids;
        $('#cav-rejet-cible').textContent = 'Concerne : ' + libelleCible(ids);
        $('#cav-motif').value = '';
        $('#cav-motif-n').textContent = '0';
        $('#cav-rejet-ov').hidden = false;
        setTimeout(function() {
            $('#cav-motif').focus();
        }, 50);
    }

    function executer(url, champs, bouton, overlay) {
        bouton.disabled = true;
        poster(url, champs).then(function(j) {
            overlay.hidden = true;
            if (etat.detailId && j.traites.indexOf(+etat.detailId) !== -1) fermerDetail();
            j.traites.forEach(function(id) {
                etat.ids.delete(String(id));
                var tr = tbody.querySelector('tr[data-id="' + id + '"]');
                if (tr) tr.classList.add('is-out');
            });
            toast(j.message, true);
            majBadgeMenu(j.restants);
            setTimeout(function() {
                // Si la page courante est vidée, revenir à la précédente
                var restantes = $$('tr[data-id]:not(.is-out)', tbody).length;
                charger(restantes === 0 && etat.page > 1 ? etat.page - 1 : etat.page);
            }, 320);
        }).catch(function(e) {
            toast(e.message, false);
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
        if (e.target.name !== 'q') {
            syncKpi();
            charger(1);
        }
    });

    function syncKpi() {
        var a = form.anciennete.value,
            m = form.email.value,
            cle = '';
        if (a === 'aujourdhui' && !m) cle = 'aujourdhui';
        else if (a === 'plus_48h' && !m) cle = 'plus_48h';
        else if (m === 'non_verifie' && !a) cle = 'non_verifie';
        $$('.cav-kpi').forEach(function(k) {
            k.classList.toggle('is-active', k.dataset.kpi === cle && cle !== '');
        });
    }

    $$('.cav-kpi').forEach(function(k) {
        k.addEventListener('click', function() {
            var v = k.dataset.kpi,
                actif = k.classList.contains('is-active');
            form.anciennete.value = '';
            form.email.value = '';
            if (!actif) {
                if (v === 'aujourdhui' || v === 'plus_48h') form.anciennete.value = v;
                if (v === 'non_verifie') form.email.value = 'non_verifie';
            }
            syncKpi();
            charger(1);
        });
    });

    tbody.addEventListener('change', function(e) {
        if (!e.target.classList.contains('cav-chk')) return;
        var id = e.target.value;
        e.target.checked ? etat.ids.add(id) : etat.ids.delete(id);
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
        var id = el.dataset.id;
        switch (el.dataset.action) {
            case 'voir':
                ouvrirDetail(id);
                break;
            case 'valider':
                demanderValidation([id]);
                break;
            case 'rejeter':
                demanderRejet([id]);
                break;
            case 'valider-sel':
                demanderValidation(Array.from(etat.ids));
                break;
            case 'rejeter-sel':
                demanderRejet(Array.from(etat.ids));
                break;
            case 'deselectionner':
                etat.ids.clear();
                $$('.cav-chk', tbody).forEach(function(c) {
                    c.checked = false;
                    c.closest('tr').classList.remove('is-sel');
                });
                majSelection();
                break;
            case 'fermer-detail':
                fermerDetail();
                break;
            case 'fermer-rejet':
                $('#cav-rejet-ov').hidden = true;
                break;
            case 'fermer-valid':
                $('#cav-valid-ov').hidden = true;
                break;
            case 'rafraichir':
                charger();
                break;
            case 'reinitialiser':
                form.reset();
                syncKpi();
                charger(1);
                break;
            case 'confirmer-valid':
                executer(U.valider, {
                    ids: etat.cibleDecision
                }, el, $('#cav-valid-ov'));
                break;
            case 'confirmer-rejet':
                var motif = $('#cav-motif').value.trim();
                if (motif.length < 10) {
                    toast('Le motif doit contenir au moins 10 caractères.', false);
                    $('#cav-motif').focus();
                    return;
                }
                executer(U.rejeter, {
                    ids: etat.cibleDecision,
                    motif: motif
                }, el, $('#cav-rejet-ov'));
                break;
        }
    });
    $('#cav-drawer-ov').addEventListener('click', fermerDetail);
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') return;
        if (!$('#cav-rejet-ov').hidden) $('#cav-rejet-ov').hidden = true;
        else if (!$('#cav-valid-ov').hidden) $('#cav-valid-ov').hidden = true;
        else if (etat.detailId) fermerDetail();
    });

    charger(1);
})();
</script>