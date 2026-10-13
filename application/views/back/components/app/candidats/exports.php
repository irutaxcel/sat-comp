<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page : Candidatures › Exports
 * Variables : $title, $compteurs, $historique
 *
 * Réutilise les partials partagés du module (_styles, _outils_js → window.CAV).
 * ⚠ Aligne le conteneur racine (.content-wrapper) sur tes autres composants si besoin.
 */
$c = array_merge(['candidats' => 0, 'participants' => 0, 'projets' => 0, 'journal' => 0, 'archive' => 0], $compteurs ?? []);

$opt_statut = '<option value="">Tous les statuts</option><option value="valide">Validés</option>'
    . '<option value="en_attente">En attente</option><option value="rejete">Rejetés</option><option value="suspendu">Suspendus</option>';
$opt_categorie = '<option value="">Toutes catégories</option><option value="professionnel">Professionnels</option><option value="etudiant">Étudiants</option>';

/** Cartes d'export : clé => [icône, couleur, titre, description, filtres HTML, unité] */
$cartes = [
    'candidats' => [
        'fa-id-card',
        'blue',
        'Candidatures',
        'Une ligne par inscription : coordonnées, éligibilité, statut, décision et état du projet.',
        '<select name="statut">' . $opt_statut . '</select>'
            . '<select name="type"><option value="">Individuels et équipes</option><option value="individuel">Individuels</option><option value="equipe">Équipes</option></select>'
            . '<select name="categorie">' . $opt_categorie . '</select>',
        'ligne(s)'
    ],
    'participants' => [
        'fa-users',
        'green',
        'Participants',
        'Une ligne par personne (responsables et membres d\'équipe). Utile pour les badges, attestations et listes d\'émargement.',
        '<select name="statut">' . str_replace('<option value="valide">', '<option value="valide" selected>', $opt_statut) . '</select>'
            . '<select name="categorie">' . $opt_categorie . '</select>',
        'personne(s)'
    ],
    'projets' => [
        'fa-drafting-compass',
        'amber',
        'Projets soumis',
        'Fiche synthétique de chaque projet : code, titre, conformité, pièces, horodatage des soumissions.',
        '<select name="statut_projet"><option value="">Soumis et brouillons</option><option value="soumis">Soumis uniquement</option><option value="brouillon">Brouillons</option></select>'
            . '<select name="conformite"><option value="">Toute conformité</option><option value="a_verifier">À vérifier</option><option value="conforme">Conformes</option><option value="non_conforme">Non conformes</option></select>'
            . '<label class="exp-check"><input type="checkbox" name="anonymise" value="1"> Version anonymisée (jury)</label>',
        'projet(s)'
    ],
    'journal' => [
        'fa-history',
        'grey',
        'Journal des décisions',
        'Traçabilité complète : validations, rejets, suspensions, soumissions et contrôles de conformité.',
        '<select name="periode"><option value="">Depuis le début</option><option value="7">7 derniers jours</option><option value="30">30 derniers jours</option><option value="90">90 derniers jours</option></select>'
            . '<select name="source"><option value="">Candidatures et projets</option><option value="candidature">Candidatures</option><option value="projet">Projets</option></select>',
        'événement(s)'
    ],
    'archive' => [
        'fa-file-archive',
        'red',
        'Archive des dossiers',
        'Tous les fichiers déposés dans un ZIP, un dossier par code anonyme avec sa fiche projet. Prête à transmettre au jury.',
        '<select name="conformite"><option value="conforme" selected>Projets conformes</option><option value="">Tous les projets soumis</option><option value="a_verifier">À vérifier</option><option value="non_conforme">Non conformes</option></select>',
        'dossier(s)'
    ],
];
$libelles_types = ['candidats' => 'Candidatures', 'participants' => 'Participants', 'projets' => 'Projets', 'journal' => 'Journal', 'archive' => 'Archive ZIP'];
?>
<div class="content-wrapper">
    <section class="cav" id="cav" data-url-compter="<?= site_url('exports/compter') ?>"
        data-url-historique="<?= site_url('exports/historique') ?>"
        data-url-telecharger="<?= site_url('exports/telecharger') ?>"
        data-url-archive="<?= site_url('exports/archive') ?>" data-csrf-name="" data-csrf-hash="">

        <header class="cav-head">
            <div>
                <p class="cav-crumb">Candidatures <span>›</span> Exports</p>
                <h1 class="cav-title">Exports</h1>
                <p class="cav-sub">Extrayez les données du concours en Excel ou CSV. Chaque export est tracé dans
                    l'historique ci-dessous.</p>
            </div>
        </header>

        <div class="exp-grid">
            <?php foreach ($cartes as $type => $k): ?>
            <form class="exp-card" data-type="<?= $type ?>" onsubmit="return false" autocomplete="off">
                <div class="exp-top">
                    <span class="cav-kpi-ico cav-ico-<?= $k[1] ?>"><i class="fas <?= $k[0] ?>"></i></span>
                    <div>
                        <h3><?= $k[2] ?></h3>
                        <p><?= $k[3] ?></p>
                    </div>
                </div>
                <div class="exp-filtres"><?= $k[4] ?></div>
                <div class="exp-bas">
                    <span class="exp-nb"><b data-nb><?= (int) $c[$type] ?></b> <?= $k[5] ?></span>
                    <?php if ($type !== 'archive'): ?>
                    <div class="exp-formats" role="radiogroup" aria-label="Format">
                        <label><input type="radio" name="format" value="xlsx" checked><span><i
                                    class="fas fa-file-excel"></i> Excel</span></label>
                        <label><input type="radio" name="format" value="csv"><span><i class="fas fa-file-csv"></i>
                                CSV</span></label>
                    </div>
                    <?php endif; ?>
                    <button type="button" class="cav-btn cav-btn-success" data-action="exporter"
                        <?= $c[$type] ? '' : 'disabled' ?>>
                        <i class="fas fa-download"></i> <?= $type === 'archive' ? 'Télécharger le ZIP' : 'Exporter' ?>
                    </button>
                </div>
            </form>
            <?php endforeach; ?>
        </div>

        <div class="cav-card">
            <div class="exp-hist-head">
                <h3><i class="fas fa-clipboard-list"></i> Derniers exports</h3>
                <button type="button" class="cav-link" data-action="historique">Actualiser</button>
            </div>
            <div class="cav-table-wrap">
                <table class="cav-table exp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Export</th>
                            <th>Format</th>
                            <th>Filtres</th>
                            <th>Lignes</th>
                            <th>Par</th>
                        </tr>
                    </thead>
                    <tbody id="exp-hist">
                        <?php if (empty($historique)): ?>
                        <tr>
                            <td colspan="6" class="cav-state">Aucun export réalisé pour le moment.</td>
                        </tr>
                        <?php else: foreach ($historique as $h): ?>
                        <tr>
                            <td><?= html_escape(date('d/m/Y H:i', strtotime($h['created_at']))) ?></td>
                            <td><?= html_escape($libelles_types[$h['type']] ?? $h['type']) ?></td>
                            <td><span class="cav-tag cav-tag-ind"><?= strtoupper(html_escape($h['format'])) ?></span>
                            </td>
                            <td class="exp-filtres-txt" data-filtres="<?= html_escape($h['filtres'] ?? '') ?>">—</td>
                            <td><?= (int) $h['nb_lignes'] ?></td>
                            <td><?= html_escape($h['admin_libelle'] ?: '—') ?></td>
                        </tr>
                        <?php endforeach;
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="exp-note"><i class="fas fa-shield-alt"></i> Ces fichiers contiennent des données personnelles : ne les
            partagez qu'avec les personnes habilitées.
            Pour le jury, utilisez la <b>version anonymisée</b> des projets et l'<b>archive des dossiers</b>, nommés
            uniquement par code projet.</p>
    </section>

    <div class="cav-toasts" id="cav-toasts" aria-live="polite"></div>
</div>

<?php $this->load->view('back/components/app/candidats/_styles'); ?>
<?php $this->load->view('back/components/app/candidats/_outils_js'); ?>

<style>
.exp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 16px;
    margin-bottom: 20px
}

.exp-card {
    background: #fff;
    border: 1px solid var(--bd);
    border-radius: 12px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: border-color .15s, box-shadow .15s;
    margin: 0
}

.exp-card:hover {
    border-color: #cfe7d7;
    box-shadow: 0 2px 12px rgba(30, 123, 60, .06)
}

.exp-top {
    display: flex;
    gap: 14px;
    align-items: flex-start
}

.exp-top h3 {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 700
}

.exp-top p {
    margin: 0;
    color: var(--mu);
    font-size: 13px;
    line-height: 1.45
}

.exp-filtres {
    display: flex;
    flex-wrap: wrap;
    gap: 8px
}

.exp-filtres select {
    height: 34px;
    border: 1px solid var(--bd);
    border-radius: 8px;
    padding: 0 8px;
    font: inherit;
    font-size: 13px;
    background: #fff;
    color: inherit;
    flex: 1 1 140px;
    min-width: 0
}

.exp-filtres select:focus {
    border-color: var(--g);
    outline: none;
    box-shadow: 0 0 0 3px var(--g-l)
}

.exp-check {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    cursor: pointer;
    flex-basis: 100%;
    margin: 0;
    font-weight: 500
}

.exp-check input {
    accent-color: var(--g);
    width: 15px;
    height: 15px
}

.exp-bas {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: auto;
    padding-top: 12px;
    border-top: 1px solid var(--bd);
    flex-wrap: wrap
}

.exp-nb {
    flex: 1;
    color: var(--mu);
    font-size: 13px;
    white-space: nowrap
}

.exp-nb b {
    color: var(--tx);
    font-size: 18px;
    margin-right: 2px
}

.exp-nb.is-load b {
    opacity: .4
}

.exp-formats {
    display: inline-flex;
    border: 1px solid var(--bd);
    border-radius: 8px;
    overflow: hidden
}

.exp-formats label {
    margin: 0;
    cursor: pointer
}

.exp-formats input {
    position: absolute;
    opacity: 0;
    pointer-events: none
}

.exp-formats span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 10px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--mu)
}

.exp-formats input:checked+span {
    background: var(--g-l);
    color: var(--g)
}

.exp-formats input:focus-visible+span {
    outline: 2px solid var(--g);
    outline-offset: -2px
}

.exp-hist-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid var(--bd)
}

.exp-hist-head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700
}

.exp-hist-head h3 i {
    color: var(--g);
    margin-right: 6px
}

.exp-table {
    min-width: 720px
}

.exp-filtres-txt {
    color: var(--mu);
    font-size: 12.5px;
    max-width: 320px
}

.exp-note {
    margin: 16px 2px 0;
    color: var(--mu);
    font-size: 12.5px;
    line-height: 1.5
}

.exp-note i {
    color: var(--g);
    margin-right: 4px
}

@media (max-width:576px) {
    .exp-grid {
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

    var TYPES = {
        candidats: 'Candidatures',
        participants: 'Participants',
        projets: 'Projets',
        journal: 'Journal',
        archive: 'Archive ZIP'
    };
    var LIBELLES = {
        statut: {
            valide: 'Validés',
            en_attente: 'En attente',
            rejete: 'Rejetés',
            suspendu: 'Suspendus'
        },
        type: {
            individuel: 'Individuels',
            equipe: 'Équipes'
        },
        categorie: {
            professionnel: 'Professionnels',
            etudiant: 'Étudiants'
        },
        statut_projet: {
            soumis: 'Soumis',
            brouillon: 'Brouillons'
        },
        conformite: {
            a_verifier: 'À vérifier',
            conforme: 'Conformes',
            non_conforme: 'Non conformes'
        },
        periode: {
            '7': '7 jours',
            '30': '30 jours',
            '90': '90 jours'
        },
        source: {
            candidature: 'Candidatures',
            projet: 'Projets'
        },
        anonymise: {
            '1': 'Anonymisé'
        }
    };

    function libelleFiltres(json) {
        var f;
        try {
            f = JSON.parse(json || '{}') || {};
        } catch (e) {
            f = {};
        }
        var t = Object.keys(f).map(function(k) {
            return (LIBELLES[k] && LIBELLES[k][f[k]]) || f[k];
        });
        return t.length ? t.join(' · ') : 'Aucun filtre';
    }

    function parametres(form) {
        var p = new URLSearchParams();
        p.set('type', form.dataset.type);
        new FormData(form).forEach(function(v, k) {
            if (v !== '') p.set(k, v);
        });
        return p;
    }

    /* ---------- Aperçu du nombre de lignes ---------- */
    function compter(form) {
        var nb = $('[data-nb]', form),
            zone = nb.parentNode,
            bouton = $('[data-action="exporter"]', form);
        var jeton = {};
        form._jeton = jeton;
        zone.classList.add('is-load');
        var p = parametres(form);
        p.delete('format');
        CAV.requete(D.urlCompter + '?' + p.toString()).then(function(j) {
            if (form._jeton !== jeton) return;
            nb.textContent = j.nb;
            bouton.disabled = j.nb === 0;
        }).catch(function(e) {
            nb.textContent = '—';
            bouton.disabled = true;
            CAV.toast(e.message, false);
        }).then(function() {
            zone.classList.remove('is-load');
        });
    }

    /* ---------- Historique ---------- */
    function rendreHistorique(rows) {
        $('#exp-hist').innerHTML = rows.length ? rows.map(function(h) {
            return '<tr><td>' + esc(CAV.dateFr(h.created_at)) + '</td><td>' + esc(TYPES[h.type] || h.type) +
                '</td>' +
                '<td><span class="cav-tag cav-tag-ind">' + esc(String(h.format).toUpperCase()) +
                '</span></td>' +
                '<td class="exp-filtres-txt">' + esc(libelleFiltres(h.filtres)) + '</td>' +
                '<td>' + esc(h.nb_lignes) + '</td><td>' + esc(h.admin_libelle || '—') + '</td></tr>';
        }).join('') : '<tr><td colspan="6" class="cav-state">Aucun export réalisé pour le moment.</td></tr>';
    }

    function chargerHistorique() {
        CAV.requete(D.urlHistorique).then(function(j) {
            rendreHistorique(j.rows);
        }).catch(function() {});
    }

    /* ---------- Export ---------- */
    function exporter(form, bouton) {
        var type = form.dataset.type;
        var url = (type === 'archive' ? D.urlArchive : D.urlTelecharger) + '?' + parametres(form).toString();
        var nb = $('[data-nb]', form).textContent;

        bouton.disabled = true;
        var html = bouton.innerHTML;
        bouton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Préparation…';

        // Iframe cachée : le téléchargement démarre sans quitter la page
        var cadre = document.createElement('iframe');
        cadre.style.display = 'none';
        cadre.src = url;
        document.body.appendChild(cadre);

        CAV.toast(TYPES[type] + ' : export de ' + nb + ' élément(s) lancé.', true);
        setTimeout(function() {
            bouton.disabled = false;
            bouton.innerHTML = html;
            chargerHistorique();
        }, type === 'archive' ? 4000 : 1500);
        setTimeout(function() {
            cadre.remove();
        }, 120000);
    }

    /* ---------- Événements ---------- */
    $$('.exp-card').forEach(function(form) {
        form.addEventListener('change', function(e) {
            if (e.target.name !== 'format') compter(form);
        });
        // Les cartes dont un filtre est présélectionné (participants validés, archive conformes) recomptent au chargement
        if ($('option[selected]', form)) compter(form);
    });
    document.addEventListener('click', function(e) {
        var el = e.target.closest('[data-action]');
        if (!el) return;
        if (el.dataset.action === 'exporter' && !el.disabled) exporter(el.closest('.exp-card'), el);
        if (el.dataset.action === 'historique') chargerHistorique();
    });

    $$('.exp-filtres-txt[data-filtres]').forEach(function(td) {
        td.textContent = libelleFiltres(td.dataset.filtres);
    });
})();
</script>