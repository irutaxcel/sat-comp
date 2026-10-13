<?php
defined('BASEPATH') or exit('No direct script access allowed');
/* Utilitaires JS partagés du module Candidatures → window.CAV */
?>
<script>
window.CAV = window.CAV || (function() {
    'use strict';
    var csrf = {
        name: '',
        hash: ''
    };

    var STATUTS = {
        en_attente: 'En attente',
        valide: 'Validé',
        rejete: 'Rejeté',
        suspendu: 'Suspendu'
    };
    var DECISIONS = {
        valide: 'Compte validé',
        rejete: 'Inscription rejetée',
        suspendu: 'Compte suspendu',
        reouvert: 'Dossier remis en attente'
    };

    function init(root) {
        csrf.name = root.dataset.csrfName;
        csrf.hash = root.dataset.csrfHash;
    }

    function $(s, ctx) {
        return (ctx || document).querySelector(s);
    }

    function $$(s, ctx) {
        return Array.prototype.slice.call((ctx || document).querySelectorAll(s));
    }

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function(c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c];
        });
    }

    function initiales(r) {
        return ((r.prenom || '').charAt(0) + (r.nom || '').charAt(0)).toUpperCase();
    }

    function dateSql(s) {
        return s ? new Date(String(s).replace(' ', 'T')) : null;
    }

    function ilya(s) {
        var d = dateSql(s);
        if (!d) return '—';
        var m = Math.round((Date.now() - d.getTime()) / 60000);
        if (m < 1) return "à l'instant";
        if (m < 60) return 'il y a ' + m + ' min';
        var h = Math.round(m / 60);
        if (h < 24) return 'il y a ' + h + ' h';
        var j = Math.round(h / 24);
        if (j < 31) return 'il y a ' + j + ' j';
        return dateCourte(s);
    }

    function dateCourte(s) {
        var d = dateSql(s);
        if (!d) return '—';
        return d.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function dateFr(s) {
        var d = dateSql(s);
        if (!d) return '—';
        return dateCourte(s) + ' à ' + d.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function enRetard(s) {
        var d = dateSql(s);
        return !!d && (Date.now() - d.getTime()) > 48 * 3600 * 1000;
    }

    function badgeStatut(st) {
        return '<span class="cav-st cav-st-' + esc(st) + '">' + esc(STATUTS[st] || st) + '</span>';
    }

    /** Historique des décisions (frise) */
    function timeline(hist) {
        if (!hist || !hist.length) return '';
        return '<div class="cav-d-sec">Historique</div><ul class="cav-tl">' + hist.map(function(d) {
            return '<li class="d-' + esc(d.decision) + '"><b>' + esc(DECISIONS[d.decision] || d
                    .decision) + '</b>' +
                '<small>' + esc(dateFr(d.created_at)) + '</small>' +
                (d.motif ? '<p>' + esc(d.motif) + '</p>' : '') + '</li>';
        }).join('') + '</ul>';
    }

    function toast(msg, ok) {
        var box = $('#cav-toasts');
        if (!box) {
            box = document.createElement('div');
            box.id = 'cav-toasts';
            box.className = 'cav-toasts';
            document.body.appendChild(box);
        }
        var t = document.createElement('div');
        t.className = 'cav-toast ' + (ok === false ? 'err' : 'ok');
        t.innerHTML = '<i class="fas ' + (ok === false ? 'fa-exclamation-circle' : 'fa-check-circle') +
            '"></i><span>' + esc(msg) + '</span>';
        box.appendChild(t);
        setTimeout(function() {
            t.remove();
        }, 4500);
    }

    function requete(url, opts) {
        opts = opts || {};
        opts.headers = {
            'X-Requested-With': 'XMLHttpRequest'
        };
        opts.credentials = 'same-origin';
        return fetch(url, opts).then(function(r) {
            return r.json().catch(function() {
                throw new Error(r.status === 403 ? 'Session expirée. Rechargez la page.' :
                    'Réponse inattendue du serveur (' + r.status + ').');
            }).then(function(j) {
                if (j.csrf) csrf.hash = j.csrf;
                if (!r.ok || j.ok === false) throw new Error(j.message || 'Erreur ' + r.status);
                return j;
            });
        });
    }

    function poster(url, champs) {
        var fd = new FormData();
        fd.append(csrf.name, csrf.hash);
        Object.keys(champs).forEach(function(k) {
            var v = champs[k];
            if (Array.isArray(v)) v.forEach(function(x) {
                fd.append(k + '[]', x);
            });
            else if (v != null) fd.append(k, v);
        });
        return requete(url, {
            method: 'POST',
            body: fd
        });
    }

    /** Met à jour le badge du menu latéral « Comptes à valider » */
    function majBadgeMenu(n) {
        var lien = document.querySelector('a[href*="candidats-en-attente"]');
        var badge = lien && lien.querySelector('.badge, .right, .nav-badge');
        if (badge) {
            badge.textContent = n;
            badge.style.display = n > 0 ? '' : 'none';
        }
    }

    /** Pagination : rendu des boutons */
    function pager(el, resumeEl, total, page, pages, parPage, libelle) {
        var debut = total ? (page - 1) * parPage + 1 : 0,
            fin = Math.min(page * parPage, total);
        resumeEl.textContent = total ? debut + '–' + fin + ' sur ' + total + ' ' + libelle : 'Aucun ' + libelle
            .replace(/\(s\)/, '');
        var h = '<button type="button" data-page="' + (page - 1) + '"' + (page <= 1 ? ' disabled' : '') +
            '>‹</button>';
        for (var i = 1; i <= pages; i++) {
            if (pages > 7 && i > 2 && i < pages - 1 && Math.abs(i - page) > 1) {
                if (i === 3 || i === pages - 2) h += '<button type="button" disabled>…</button>';
                continue;
            }
            h += '<button type="button" data-page="' + i + '"' + (i === page ? ' class="is-active"' : '') +
                '>' + i + '</button>';
        }
        h += '<button type="button" data-page="' + (page + 1) + '"' + (page >= pages ? ' disabled' : '') +
            '>›</button>';
        el.innerHTML = pages > 1 ? h : '';
    }

    return {
        init: init,
        $: $,
        $$: $$,
        esc: esc,
        initiales: initiales,
        ilya: ilya,
        dateFr: dateFr,
        dateCourte: dateCourte,
        enRetard: enRetard,
        badgeStatut: badgeStatut,
        toast: toast,
        requete: requete,
        poster: poster,
        majBadgeMenu: majBadgeMenu,
        pager: pager,
        timeline: timeline,
        STATUTS: STATUTS,
        DECISIONS: DECISIONS
    };
})();
</script>