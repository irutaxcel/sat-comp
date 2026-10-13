<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.ja-user {
    display: flex;
    align-items: center;
    gap: .6rem;
    min-width: 180px
}

.ja-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: .75rem;
    color: #fff;
    flex-shrink: 0;
    background: #6c757d
}

.ja-avatar.admin {
    background: #1f7a45
}

.ja-avatar.jury {
    background: #d39e00
}

.ja-avatar.candidat {
    background: #17a2b8
}

.ja-user .ja-nom {
    font-weight: 600;
    line-height: 1.2
}

.ja-user .ja-mail {
    font-size: .75rem;
    color: #6c757d
}

.ja-date {
    white-space: nowrap
}

.ja-date small {
    display: block;
    color: #6c757d
}

.ja-action {
    white-space: nowrap;
    font-size: .8rem;
    font-weight: 500
}

.ja-details {
    font-size: .8rem;
    max-width: 280px
}

.ja-details .ja-kv {
    display: inline-block;
    background: #f4f6f9;
    border-radius: 4px;
    padding: 0 .35rem;
    margin: 0 .25rem .2rem 0
}

.ja-details .ja-kv b {
    font-weight: 600;
    color: #495057
}

.ja-ip {
    font-family: monospace;
    font-size: .8rem
}

.ja-ua {
    display: block;
    font-size: .75rem;
    color: #6c757d
}

#tableJournal td {
    vertical-align: middle
}

.ja-vide {
    padding: 3rem 1rem;
    text-align: center;
    color: #6c757d
}

.ja-periode .btn {
    font-size: .8rem
}

.ja-periode .btn.active {
    background: #28a745;
    border-color: #28a745;
    color: #fff
}

#modalJournal dt {
    font-weight: 600;
    color: #6c757d;
    font-size: .8rem;
    text-transform: uppercase
}

#modalJournal dd {
    margin-bottom: .75rem;
    word-break: break-word
}

#modalJournal pre {
    background: #f4f6f9;
    border-radius: 4px;
    padding: .5rem .75rem;
    font-size: .8rem;
    white-space: pre-wrap;
    margin: 0
}
</style>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-7">
                    <h1 class="m-0"><i class="fas fa-history mr-2 text-success"></i><?= html_escape($title) ?></h1>
                    <small class="text-muted">Traçabilité des connexions et des actions effectuées sur la
                        plateforme.</small>
                </div>
                <div class="col-sm-5 text-sm-right mt-2 mt-sm-0">
                    <a href="#" class="btn btn-outline-success" id="btnExport">
                        <i class="fas fa-file-csv mr-1"></i> Exporter (CSV)
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- Statistiques -->
            <div class="row">
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-stream"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Événements aujourd'hui</span>
                            <span class="info-box-number" id="statJour">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-sign-in-alt"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Connexions aujourd'hui</span>
                            <span class="info-box-number" id="statConnexions">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box" id="boxEchecs">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-lock"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Échecs de connexion (24 h)</span>
                            <span class="info-box-number" id="statEchecs">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Utilisateurs actifs aujourd'hui</span>
                            <span class="info-box-number" id="statUtilisateurs">–</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="card card-outline card-success">
                <div class="card-header py-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div class="btn-group btn-group-sm ja-periode my-1" id="periode">
                            <button type="button" class="btn btn-outline-secondary" data-jours="0">Aujourd'hui</button>
                            <button type="button" class="btn btn-outline-secondary active" data-jours="7">7
                                jours</button>
                            <button type="button" class="btn btn-outline-secondary" data-jours="30">30 jours</button>
                            <button type="button" class="btn btn-outline-secondary" data-jours="">Tout</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-muted my-1" id="btnReinit">
                            <i class="fas fa-undo mr-1"></i>Réinitialiser les filtres
                        </button>
                    </div>
                </div>
                <div class="card-body pb-1">
                    <div class="form-row">
                        <div class="form-group col-6 col-md-3 col-xl-2">
                            <label class="small text-muted mb-1" for="fDebut">Du</label>
                            <input type="date" class="form-control form-control-sm" id="fDebut">
                        </div>
                        <div class="form-group col-6 col-md-3 col-xl-2">
                            <label class="small text-muted mb-1" for="fFin">Au</label>
                            <input type="date" class="form-control form-control-sm" id="fFin">
                        </div>
                        <div class="form-group col-md-6 col-xl-2">
                            <label class="small text-muted mb-1" for="fCategorie">Catégorie</label>
                            <select class="form-control form-control-sm" id="fCategorie">
                                <option value="">Toutes</option>
                                <option value="auth">Authentification</option>
                                <option value="securite">Sécurité</option>
                                <option value="comptes">Gestion des comptes</option>
                                <option value="donnees">Données &amp; exports</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-xl-2">
                            <label class="small text-muted mb-1" for="fAction">Action</label>
                            <select class="form-control form-control-sm" id="fAction">
                                <option value="">Toutes</option>
                                <?php foreach ($actions as $code => $a): ?>
                                <option value="<?= html_escape($code) ?>"
                                    data-categorie="<?= html_escape($a['categorie']) ?>">
                                    <?= html_escape($a['libelle']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-xl-2">
                            <label class="small text-muted mb-1" for="fUtilisateur">Utilisateur</label>
                            <select class="form-control form-control-sm" id="fUtilisateur">
                                <option value="">Tous</option>
                                <?php foreach ($utilisateurs as $u): ?>
                                <option value="<?= (int) $u['id'] ?>">
                                    <?= html_escape($u['prenom'] . ' ' . $u['nom'] . ' — ' . $u['email']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6 col-xl-2">
                            <label class="small text-muted mb-1" for="fQ">Recherche</label>
                            <input type="search" class="form-control form-control-sm" id="fQ"
                                placeholder="IP, e-mail, détail…">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tableJournal">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                    <th>Cible</th>
                                    <th>Détails</th>
                                    <th>IP / appareil</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="ja-vide"><i class="fas fa-spinner fa-spin"></i> Chargement…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex flex-wrap align-items-center justify-content-between">
                    <div class="small text-muted my-1" id="resume"></div>
                    <div class="d-flex align-items-center my-1">
                        <select class="form-control form-control-sm mr-2" id="parPage" style="width:auto">
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                            <option value="100">100 / page</option>
                        </select>
                        <ul class="pagination pagination-sm m-0" id="pagination"></ul>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal : détail d'un événement -->
<div class="modal fade" id="modalJournal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mjTitre">Détail de l'événement</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0" id="mjCorps"></dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm mr-auto" id="mjFiltrerIp">
                    <i class="fas fa-filter mr-1"></i>Voir toute l'activité de cette IP
                </button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var URL = {
        liste: '<?= site_url('journal/liste') ?>',
        export: '<?= site_url('journal/export') ?>'
    };
    var ACTIONS = <?= json_encode($actions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    var CATEGORIES = {
        auth: 'Authentification',
        securite: 'Sécurité',
        comptes: 'Gestion des comptes',
        donnees: 'Données & exports'
    };

    var etat = {
        page: 1,
        par_page: 25
    };
    var lignesParId = {};
    var $tbody = $('#tableJournal tbody');

    /* ---------------- Utilitaires ---------------- */

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c];
        });
    }

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function isoJour(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    /** '2026-10-13 07:12:12' → { date: '13/10/2026', heure: '07:12:12' } (heure du serveur, sans conversion) */
    function dateFr(s) {
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}:\d{2}:\d{2})/.exec(s || '');
        return m ? {
            date: m[3] + '/' + m[2] + '/' + m[1],
            heure: m[4]
        } : {
            date: esc(s),
            heure: ''
        };
    }

    function infoAction(code) {
        return ACTIONS[code] || {
            libelle: (code || '').replace(/_/g, ' ').replace(/^./, function(c) {
                return c.toUpperCase();
            }),
            categorie: '',
            icone: 'fa-circle',
            couleur: 'secondary'
        };
    }

    /** Résumé lisible d'un user-agent : « Chrome · macOS » */
    function appareil(ua) {
        if (!ua) return '';
        var nav = /Edg\//.test(ua) ? 'Edge' : /OPR\/|Opera/.test(ua) ? 'Opera' : /Firefox\//.test(ua) ?
            'Firefox' :
            /Chrome\//.test(ua) ? 'Chrome' : /Safari\//.test(ua) ? 'Safari' : /curl|wget|python|bot/i.test(ua) ?
            'Script / robot' : 'Autre';
        var os = /iPhone|iPad/.test(ua) ? 'iOS' : /Android/.test(ua) ? 'Android' : /Windows/.test(ua) ?
            'Windows' :
            /Mac OS X|Macintosh/.test(ua) ? 'macOS' : /Linux/.test(ua) ? 'Linux' : '';
        return nav + (os ? ' · ' + os : '');
    }

    function detailsObjet(d) {
        if (!d) return null;
        try {
            var o = JSON.parse(d);
            return (o && typeof o === 'object') ? o : null;
        } catch (e) {
            return null;
        }
    }

    function valeur(v) {
        if (v === true) return 'oui';
        if (v === false) return 'non';
        if (v !== null && typeof v === 'object') return JSON.stringify(v);
        return String(v);
    }

    function detailsHtml(d) {
        if (!d) return '<span class="text-muted">—</span>';
        var o = detailsObjet(d);
        if (!o) return esc(d.length > 90 ? d.substring(0, 90) + '…' : d);
        return Object.keys(o).map(function(k) {
            return '<span class="ja-kv"><b>' + esc(k.replace(/_/g, ' ')) + '</b> ' + esc(valeur(o[k])) +
                '</span>';
        }).join('');
    }

    function initiales(l) {
        return ((l.prenom || '').charAt(0) + (l.nom || '').charAt(0)).toUpperCase() || '?';
    }

    function filtres() {
        var f = {
            date_debut: $('#fDebut').val(),
            date_fin: $('#fFin').val(),
            categorie: $('#fCategorie').val(),
            action: $('#fAction').val(),
            utilisateur_id: $('#fUtilisateur').val(),
            q: $.trim($('#fQ').val())
        };
        $.each(f, function(k, v) {
            if (!v) delete f[k];
        });
        return f;
    }

    function signaler(msg) {
        var $a = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        $a.prepend(document.createTextNode(msg));
        $('.content-wrapper .content .container-fluid').first().prepend($a);
    }

    /* ---------------- Chargement ---------------- */

    var requete = null;

    function charger() {
        if (requete) requete.abort();
        $('#btnExport').attr('href', URL.export+'?' + $.param(filtres()));
        requete = $.getJSON(URL.liste, $.extend(filtres(), etat)).done(function(r) {
            etat.page = r.page;
            afficherStats(r.stats);
            afficherLignes(r.lignes);
            afficherPagination(r);
        }).fail(function(x, s) {
            if (s === 'abort') return;
            var j = x.responseJSON;
            var msg = (j && j.message) || (x.status === 404 ?
                'Route introuvable (404) : ajoutez les routes « journal/… ».' :
                'Impossible de charger le journal (HTTP ' + x.status + ').');
            if (window.console) console.error('[journal]', x.status, (x.responseText || '').substring(0,
                2000));
            signaler(msg);
            $tbody.html('<tr><td colspan="7" class="ja-vide text-danger">' + esc(msg) + '</td></tr>');
        });
    }

    function afficherStats(s) {
        $('#statJour').text(s.evenements_jour);
        $('#statConnexions').text(s.connexions_jour);
        $('#statEchecs').text(s.echecs_24h);
        $('#statUtilisateurs').text(s.utilisateurs_jour);
        $('#boxEchecs').toggleClass('bg-danger text-white', s.echecs_24h >= 5);
    }

    function afficherLignes(lignes) {
        lignesParId = {};
        if (!lignes.length) {
            $tbody.html('<tr><td colspan="7" class="ja-vide"><i class="fas fa-search fa-2x mb-2 d-block"></i>' +
                'Aucune activité ne correspond à ces critères.</td></tr>');
            return;
        }
        $tbody.html(lignes.map(function(l) {
            lignesParId[l.id] = l;
            return ligneHtml(l);
        }).join(''));
    }

    function ligneHtml(l) {
        var d = dateFr(l.created_at),
            a = infoAction(l.action);
        var user = l.utilisateur_id && l.email ?
            '<div class="ja-user"><span class="ja-avatar ' + esc(l.role) + '">' + esc(initiales(l)) +
            '</span><div>' +
            '<div class="ja-nom">' + esc(l.prenom + ' ' + l.nom) + '</div><div class="ja-mail">' + esc(l
            .email) + '</div></div></div>' :
            '<span class="text-muted"><i class="fas fa-robot mr-1"></i>' + (l.utilisateur_id ?
                'Compte supprimé' : 'Système / inconnu') + '</span>';
        var cible = l.cible_type ?
            esc(l.cible_type) + (l.cible_id ? ' #' + l.cible_id : '') + (l.cible_nom ?
                '<small class="d-block text-muted">' + esc(l.cible_nom) + '</small>' : '') :
            '<span class="text-muted">—</span>';

        return '<tr data-id="' + l.id + '">' +
            '<td class="ja-date">' + d.date + '<small>' + d.heure + '</small></td>' +
            '<td>' + user + '</td>' +
            '<td><span class="badge badge-' + a.couleur + ' ja-action"><i class="fas ' + a.icone +
            ' mr-1"></i>' + esc(a.libelle) + '</span></td>' +
            '<td>' + cible + '</td>' +
            '<td class="ja-details">' + detailsHtml(l.details) + '</td>' +
            '<td><span class="ja-ip">' + esc(l.ip || '—') + '</span><span class="ja-ua">' + esc(appareil(l
                .user_agent)) + '</span></td>' +
            '<td class="text-right"><button type="button" class="btn btn-sm btn-outline-secondary js-voir" title="Détail">' +
            '<i class="fas fa-eye"></i></button></td>' +
            '</tr>';
    }

    function afficherPagination(r) {
        var debut = r.total ? (r.page - 1) * r.par_page + 1 : 0;
        var fin = Math.min(r.page * r.par_page, r.total);
        $('#resume').text(r.total ? debut + '–' + fin + ' sur ' + r.total.toLocaleString('fr-FR') +
            ' événement' + (r.total > 1 ? 's' : '') : '');

        var html = '',
            p = r.page,
            n = r.pages;

        function item(num, label, actif, inactif) {
            return '<li class="page-item' + (actif ? ' active' : '') + (inactif ? ' disabled' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + num + '">' + label + '</a></li>';
        }
        if (n > 1) {
            html += item(p - 1, '&laquo;', false, p === 1);
            var pages = [1, p - 1, p, p + 1, n].filter(function(x, i, t) {
                    return x >= 1 && x <= n && t.indexOf(x) === i;
                })
                .sort(function(a, b) {
                    return a - b;
                });
            pages.forEach(function(x, i) {
                if (i && x - pages[i - 1] > 1) html += item(0, '…', false, true);
                html += item(x, x, x === p, false);
            });
            html += item(p + 1, '&raquo;', false, p === n);
        }
        $('#pagination').html(html);
    }

    /* ---------------- Filtres ---------------- */

    function appliquerPeriode(jours) {
        $('#periode .btn').removeClass('active').filter('[data-jours="' + jours + '"]').addClass('active');
        if (jours === '') {
            $('#fDebut, #fFin').val('');
        } else {
            var debut = new Date();
            debut.setDate(debut.getDate() - Number(jours));
            $('#fDebut').val(isoJour(debut));
            $('#fFin').val(isoJour(new Date()));
        }
    }

    function relancer() {
        etat.page = 1;
        charger();
    }

    $('#periode').on('click', '.btn', function() {
        appliquerPeriode(String($(this).data('jours')));
        relancer();
    });
    $('#fDebut, #fFin').on('change', function() {
        $('#periode .btn').removeClass('active');
        relancer();
    });
    $('#fUtilisateur, #fAction').on('change', relancer);
    $('#fCategorie').on('change', function() {
        var c = this.value;
        // N'affiche que les actions de la catégorie choisie
        $('#fAction option[data-categorie]').each(function() {
            $(this).prop('hidden', c && $(this).data('categorie') !== c);
        });
        if (c && $('#fAction option:selected').data('categorie') !== c) $('#fAction').val('');
        relancer();
    });
    var tempo;
    $('#fQ').on('input', function() {
        clearTimeout(tempo);
        tempo = setTimeout(relancer, 350);
    });

    $('#btnReinit').on('click', function() {
        $('#fCategorie, #fAction, #fUtilisateur').val('');
        $('#fQ').val('');
        $('#fAction option').prop('hidden', false);
        appliquerPeriode('7');
        relancer();
    });

    $('#parPage').on('change', function() {
        etat.par_page = Number(this.value);
        relancer();
    });
    $('#pagination').on('click', 'a', function(e) {
        e.preventDefault();
        var p = Number($(this).data('page'));
        if (!p || $(this).parent().is('.disabled, .active')) return;
        etat.page = p;
        charger();
        $('html, body').animate({
            scrollTop: $('#tableJournal').offset().top - 80
        }, 150);
    });

    /* ---------------- Détail ---------------- */

    var ipCourante = null;
    $tbody.on('click', '.js-voir', function() {
        var l = lignesParId[$(this).closest('tr').data('id')];
        if (!l) return;
        var a = infoAction(l.action),
            d = dateFr(l.created_at),
            o = detailsObjet(l.details);
        ipCourante = l.ip;

        function ligne(titre, contenu) {
            return '<dt class="col-sm-3">' + titre + '</dt><dd class="col-sm-9">' + contenu + '</dd>';
        }

        $('#mjTitre').html('<i class="fas ' + a.icone + ' text-' + a.couleur + ' mr-2"></i>' + esc(a
            .libelle));
        $('#mjCorps').html(
            ligne('Date', d.date + ' à ' + d.heure) +
            ligne('Utilisateur', l.email ? esc(l.prenom + ' ' + l.nom) + ' &lt;' + esc(l.email) +
                '&gt;' +
                (l.role_libelle ? ' <span class="badge badge-light">' + esc(l.role_libelle) +
                    '</span>' : '') :
                '<span class="text-muted">Système / inconnu</span>') +
            ligne('Action', esc(a.libelle) + ' <code class="ml-1">' + esc(l.action) + '</code>' +
                (a.categorie ? ' <span class="badge badge-light">' + esc(CATEGORIES[a.categorie] ||
                    a.categorie) + '</span>' : '')) +
            ligne('Cible', l.cible_type ? esc(l.cible_type) + (l.cible_id ? ' #' + l.cible_id :
                '') + (l.cible_nom ? ' — ' + esc(l.cible_nom) : '') : '—') +
            ligne('Détails', l.details ? '<pre>' + esc(o ? JSON.stringify(o, null, 2) : l.details) +
                '</pre>' : '—') +
            ligne('Adresse IP', '<span class="ja-ip">' + esc(l.ip || '—') + '</span>') +
            ligne('Appareil', esc(appareil(l.user_agent)) + (l.user_agent ?
                    '<small class="d-block text-muted mt-1">' + esc(l.user_agent) + '</small>' : ''
                    )) +
            ligne('Référence', '#' + l.id)
        );
        $('#mjFiltrerIp').toggle(!!l.ip);
        $('#modalJournal').modal('show');
    });

    $('#mjFiltrerIp').on('click', function() {
        $('#modalJournal').modal('hide');
        $('#fCategorie, #fAction, #fUtilisateur').val('');
        $('#fAction option').prop('hidden', false);
        $('#fQ').val(ipCourante);
        appliquerPeriode('');
        relancer();
    });

    /* ---------------- Démarrage ---------------- */

    appliquerPeriode('7');
    charger();
});
</script>