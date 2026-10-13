<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
.au-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: .85rem;
    color: #fff;
    flex-shrink: 0
}

.au-avatar.admin {
    background: #1f7a45
}

.au-avatar.jury {
    background: #d39e00
}

.au-user {
    display: flex;
    align-items: center;
    gap: .75rem;
    min-width: 220px
}

.au-user .au-nom {
    font-weight: 600;
    line-height: 1.2
}

.au-user .au-mail {
    font-size: .8rem;
    color: #6c757d
}

.au-tabs .nav-link {
    border-radius: 20px;
    padding: .3rem .9rem;
    margin-right: .25rem;
    color: #495057
}

.au-tabs .nav-link.active {
    background: #28a745;
    color: #fff
}

#tableUtilisateurs td {
    vertical-align: middle
}

#tableUtilisateurs .btn-group .btn {
    padding: .2rem .5rem
}

.au-vide {
    padding: 3rem 1rem;
    text-align: center;
    color: #6c757d
}

.au-mdp {
    font-family: monospace;
    font-size: 1.1rem;
    letter-spacing: .05em
}

.au-moi {
    font-size: .7rem
}

.au-etat {
    display: block;
    font-size: .7rem;
    margin-top: .15rem
}
</style>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-shield mr-2 text-success"></i><?= html_escape($title) ?></h1>
                    <small class="text-muted">Gérez les comptes de l'équipe SATRACO et des membres du jury.</small>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <button type="button" class="btn btn-success" id="btnAjouter">
                        <i class="fas fa-user-plus mr-1"></i> Ajouter un compte
                    </button>
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
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Total comptes</span>
                            <span class="info-box-number" id="statTotal">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-user-cog"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Administrateurs</span>
                            <span class="info-box-number" id="statAdmins">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-gavel"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Membres du jury</span>
                            <span class="info-box-number" id="statJury">–</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-check"></i></span>
                        <div class="info-box-content"><span class="info-box-text">Comptes actifs</span>
                            <span class="info-box-number"><span id="statActifs">–</span>
                                <small class="text-muted font-weight-normal" id="statInactifs"></small></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste -->
            <div class="card card-outline card-success">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-2 mb-lg-0">
                            <ul class="nav au-tabs" id="filtreRole">
                                <li class="nav-item"><a class="nav-link active" href="#" data-role="">Tous</a></li>
                                <li class="nav-item"><a class="nav-link" href="#" data-role="admin">Administrateurs</a>
                                </li>
                                <li class="nav-item"><a class="nav-link" href="#" data-role="jury">Jury</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex">
                                <select class="form-control form-control-sm mr-2" id="filtreStatut"
                                    style="max-width:160px">
                                    <option value="">Tous statuts</option>
                                    <option value="actif">Actifs</option>
                                    <option value="suspendu">Suspendus</option>
                                    <option value="en_attente">En attente</option>
                                </select>
                                <div class="input-group input-group-sm">
                                    <input type="search" class="form-control" id="filtreQ"
                                        placeholder="Rechercher nom, e-mail, organisation…">
                                    <div class="input-group-append"><span class="input-group-text"><i
                                                class="fas fa-search"></i></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tableUtilisateurs">
                            <thead class="thead-light">
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Rôle</th>
                                    <th>Organisation</th>
                                    <th>Téléphone</th>
                                    <th>Dernière connexion</th>
                                    <th class="text-center">Actif</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7" class="au-vide"><i class="fas fa-spinner fa-spin"></i> Chargement…
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Modal : ajout / modification -->
<div class="modal fade" id="modalUtilisateur" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form class="modal-content" id="formUtilisateur" novalidate autocomplete="off">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="modalTitre">Ajouter un compte</h5>
                <button type="button" class="close text-white" data-dismiss="modal"
                    aria-label="Fermer"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="champId">

                <div class="form-group">
                    <label>Rôle <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons" id="choixRole">
                        <?php foreach ($roles as $code => $r): ?>
                        <label class="btn btn-outline-success flex-fill<?= $code === 'admin' ? ' active' : '' ?>">
                            <input type="radio" name="role" value="<?= html_escape($code) ?>"
                                <?= $code === 'admin' ? ' checked' : '' ?>>
                            <i class="fas <?= $code === 'jury' ? 'fa-gavel' : 'fa-user-cog' ?> mr-1"></i>
                            <?= html_escape($r['libelle']) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="form-text text-muted" id="aideRole"></small>
                    <div class="invalid-feedback d-block" data-err="role"></div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="champNom">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nom" id="champNom" maxlength="100" required>
                        <div class="invalid-feedback" data-err="nom"></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="champPrenom">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="prenom" id="champPrenom" maxlength="100" required>
                        <div class="invalid-feedback" data-err="prenom"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="champEmail">E-mail <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="champEmail" maxlength="150" required>
                        <div class="invalid-feedback" data-err="email"></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="champTel">Téléphone</label>
                        <input type="text" class="form-control" name="telephone" id="champTel" maxlength="30"
                            placeholder="+257 …">
                        <div class="invalid-feedback" data-err="telephone"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="champOrg">Organisation</label>
                    <input type="text" class="form-control" name="organisation" id="champOrg" maxlength="150"
                        placeholder="Ex. SATRACO Construction, Ordre des architectes, Université du Burundi…">
                    <div class="invalid-feedback" data-err="organisation"></div>
                </div>

                <hr>

                <div class="form-group">
                    <label for="champMdp">Mot de passe</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="mot_de_passe" id="champMdp" minlength="8"
                            maxlength="72" autocomplete="new-password">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnVoirMdp" title="Afficher"><i
                                    class="fas fa-eye"></i></button>
                            <button class="btn btn-outline-secondary" type="button" id="btnGenererMdp"
                                title="Générer"><i class="fas fa-magic"></i></button>
                        </div>
                    </div>
                    <small class="form-text text-muted" id="aideMdp"></small>
                    <div class="invalid-feedback d-block" data-err="mot_de_passe"></div>
                </div>

                <div class="d-flex flex-wrap">
                    <div class="custom-control custom-switch mr-4 bloc-creation">
                        <input type="checkbox" class="custom-control-input" name="actif" id="champActif" value="1"
                            checked>
                        <label class="custom-control-label" for="champActif">Compte actif</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="envoyer_email" id="champEnvoyer"
                            value="1" checked>
                        <label class="custom-control-label" for="champEnvoyer">Envoyer les identifiants par
                            e-mail</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success" id="btnEnregistrer"><i class="fas fa-save mr-1"></i>
                    Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal : identifiants générés (affichés une seule fois) -->
<div class="modal fade" id="modalIdentifiants" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key text-success mr-2"></i>Identifiants du compte</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Ce mot de passe ne sera plus affiché. Copiez-le maintenant si vous devez le transmettre.
                </div>
                <p class="mb-1 text-muted small">Identifiant</p>
                <p class="font-weight-bold" id="idEmail"></p>
                <p class="mb-1 text-muted small">Mot de passe</p>
                <div class="input-group">
                    <input type="text" class="form-control au-mdp" id="idMdp" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-success" type="button" id="btnCopierMdp"><i
                                class="fas fa-copy mr-1"></i>Copier</button>
                    </div>
                </div>
                <p class="small mt-3 mb-0" id="idEmailStatut"></p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var URL = {
        liste: '<?= site_url('utilisateurs/liste') ?>',
        detail: '<?= site_url('utilisateurs/detail') ?>/',
        enregistrer: '<?= site_url('utilisateurs/enregistrer') ?>',
        statut: '<?= site_url('utilisateurs/statut') ?>',
        reinitialiser: '<?= site_url('utilisateurs/reinitialiser') ?>',
        supprimer: '<?= site_url('utilisateurs/supprimer') ?>'
    };
    var CSRF = {
        nom: '<?= $this->security->get_csrf_token_name() ?>',
        hash: '<?= $this->security->get_csrf_hash() ?>'
    };
    var MOI = {
        id: <?= (int) ($moi['id'] ?? 0) ?>
    };
    var ROLES = <?= json_encode($roles, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    var ROLE_BADGE = {
        admin: 'badge-success',
        jury: 'badge-warning'
    };
    var ROLE_ICONE = {
        admin: 'fa-user-cog',
        jury: 'fa-gavel'
    };
    var AIDE_ROLE = {
        admin: 'Accès complet au back-office : concours, candidatures, évaluation et administration.',
        jury: 'Accède uniquement aux projets anonymisés pour les noter.'
    };

    var filtres = {
        role: '',
        statut: '',
        q: ''
    };
    var maintenant = null;
    var $tbody = $('#tableUtilisateurs tbody');
    var $form = $('#formUtilisateur');

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

    function initiales(u) {
        return ((u.prenom || '').charAt(0) + (u.nom || '').charAt(0)).toUpperCase() || '?';
    }

    function dateFr(d) {
        if (!d) return '<span class="text-muted">Jamais</span>';
        var x = new Date(d.replace(' ', 'T'));
        return isNaN(x) ? esc(d) : x.toLocaleDateString('fr-FR') + ' <small class="text-muted">' +
            x.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            }) + '</small>';
    }

    function notifier(type, msg) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: msg,
                showConfirmButton: false,
                timer: 3500
            });
        } else if (window.toastr) {
            toastr[type] ? toastr[type](msg) : toastr.info(msg);
        } else {
            // Bandeau dans la page plutôt qu'un alert() bloquant
            var cls = {
                success: 'alert-success',
                warning: 'alert-warning',
                info: 'alert-info'
            } [type] || 'alert-danger';
            var $a = $('<div class="alert ' + cls + ' alert-dismissible fade show" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
            $a.prepend(document.createTextNode(msg));
            $('.content-wrapper .content .container-fluid').first().prepend($a);
            if (type === 'success') setTimeout(function() {
                $a.alert('close');
            }, 3500);
        }
    }

    function confirmer(titre, texte, bouton, danger) {
        if (window.Swal) {
            return Swal.fire({
                title: titre,
                html: texte,
                icon: danger ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: bouton,
                cancelButtonText: 'Annuler',
                confirmButtonColor: danger ? '#dc3545' : '#28a745'
            }).then(function(r) {
                return r.isConfirmed;
            });
        }
        return Promise.resolve(window.confirm(titre + '\n\n' + $('<div>').html(texte).text()));
    }

    function majCsrf(j) {
        if (j && j.csrf) CSRF.hash = j.csrf;
    }

    function post(url, data) {
        data = data || {};
        if (Array.isArray(data)) data.push({
            name: CSRF.nom,
            value: CSRF.hash
        });
        else data[CSRF.nom] = CSRF.hash;
        return $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json'
            })
            .done(majCsrf)
            .fail(function(x) {
                majCsrf(x.responseJSON);
            });
    }

    /** Affiche la vraie cause de l'échec (404 de route, erreur PHP/SQL, session expirée…) */
    function erreurAjax(x, statut) {
        var j = x && x.responseJSON;
        majCsrf(j);
        var msg = j && j.message;

        if (!msg) {
            var code = x ? x.status : 0;
            if (code === 0) msg = 'Serveur injoignable. Vérifiez votre connexion.';
            else if (code === 404) msg =
                'Route introuvable (404). Vérifiez les routes « utilisateurs/… » dans config/routes.php.';
            else if (code === 403) msg =
                'Accès refusé (403) : session expirée ou jeton CSRF invalide. Rechargez la page.';
            else if (code >= 500) msg = 'Erreur serveur (' + code +
                '). Consultez application/logs ou l\'onglet Réseau.';
            else if (statut === 'parsererror') msg =
                'Réponse invalide du serveur (une erreur PHP s\'affiche probablement avant le JSON).';
            else msg = 'Une erreur est survenue (' + code + '). Réessayez.';

            if (window.console && x) {
                console.error('[utilisateurs] ' + (this && this.url ? this.url : '') + ' → HTTP ' + x.status,
                    (x.responseText || '').substring(0, 2000));
            }
        }
        notifier('error', msg);
        return j;
    }

    /* ---------------- Liste ---------------- */

    function charger() {
        $.getJSON(URL.liste, filtres).done(function(r) {
            majCsrf(r);
            maintenant = r.now;
            afficherStats(r.stats);
            afficherLignes(r.lignes);
        }).fail(function(x, s) {
            erreurAjax.call(this, x, s);
            $tbody.html(
                '<tr><td colspan="7" class="au-vide text-danger">Impossible de charger la liste.</td></tr>'
            );
        });
    }

    function afficherStats(s) {
        $('#statTotal').text(s.total);
        $('#statAdmins').text(s.admins);
        $('#statJury').text(s.jury);
        $('#statActifs').text(s.actifs);
        $('#statInactifs').text(s.inactifs ? '/ ' + s.inactifs + ' inactif' + (s.inactifs > 1 ? 's' : '') : '');
    }

    function afficherLignes(lignes) {
        if (!lignes.length) {
            $tbody.html(
                '<tr><td colspan="7" class="au-vide"><i class="fas fa-user-slash fa-2x mb-2 d-block"></i>' +
                'Aucun compte ne correspond à ces critères.</td></tr>');
            return;
        }
        $tbody.html(lignes.map(ligneHtml).join(''));
    }

    function etatHtml(u) {
        // Comparaison de chaînes 'Y-m-d H:i:s' avec l'heure du serveur
        if (u.bloque_jusqua && maintenant && u.bloque_jusqua > maintenant) {
            return '<span class="au-etat text-danger"><i class="fas fa-lock mr-1"></i>Bloqué (trop d\'échecs)</span>';
        }
        if (u.statut === 'en_attente') return '<span class="au-etat text-warning">En attente</span>';
        if (u.statut === 'suspendu') return '<span class="au-etat text-muted">Suspendu</span>';
        return '';
    }

    function ligneHtml(u) {
        var estMoi = +u.id === MOI.id;
        var actif = u.statut === 'actif';

        return '<tr data-id="' + u.id + '" data-nom="' + esc(u.prenom + ' ' + u.nom) + '"' + (actif ? '' :
                ' class="text-muted"') + '>' +
            '<td><div class="au-user"><span class="au-avatar ' + esc(u.role) + '">' + esc(initiales(u)) +
            '</span>' +
            '<div><div class="au-nom">' + esc(u.prenom) + ' ' + esc(u.nom) +
            (estMoi ? ' <span class="badge badge-light au-moi">Vous</span>' : '') + '</div>' +
            '<div class="au-mail">' + esc(u.email) + '</div></div></div></td>' +
            '<td><span class="badge ' + (ROLE_BADGE[u.role] || 'badge-secondary') + '"><i class="fas ' +
            (ROLE_ICONE[u.role] || '') + ' mr-1"></i>' + esc(u.role_libelle) + '</span></td>' +
            '<td>' + (u.organisation ? esc(u.organisation) : '<span class="text-muted">—</span>') + '</td>' +
            '<td>' + (u.telephone ? esc(u.telephone) : '<span class="text-muted">—</span>') + '</td>' +
            '<td>' + dateFr(u.derniere_connexion) + '</td>' +
            '<td class="text-center"><div class="custom-control custom-switch d-inline-block">' +
            '<input type="checkbox" class="custom-control-input js-statut" id="sw' + u.id + '"' +
            (actif ? ' checked' : '') + (estMoi ? ' disabled' : '') + '>' +
            '<label class="custom-control-label" for="sw' + u.id + '"></label></div>' + etatHtml(u) + '</td>' +
            '<td class="text-right"><div class="btn-group btn-group-sm">' +
            '<button type="button" class="btn btn-outline-primary js-modifier" title="Modifier"><i class="fas fa-pen"></i></button>' +
            '<button type="button" class="btn btn-outline-warning js-reinit" title="Réinitialiser le mot de passe"><i class="fas fa-key"></i></button>' +
            '<button type="button" class="btn btn-outline-danger js-supprimer" title="Supprimer"' + (estMoi ?
                ' disabled' : '') +
            '><i class="fas fa-trash"></i></button>' +
            '</div></td></tr>';
    }

    /* ---------------- Filtres ---------------- */

    $('#filtreRole').on('click', '.nav-link', function(e) {
        e.preventDefault();
        $('#filtreRole .nav-link').removeClass('active');
        $(this).addClass('active');
        filtres.role = $(this).data('role');
        charger();
    });
    $('#filtreStatut').on('change', function() {
        filtres.statut = this.value;
        charger();
    });
    var tempo;
    $('#filtreQ').on('input', function() {
        clearTimeout(tempo);
        var v = this.value.trim();
        tempo = setTimeout(function() {
            filtres.q = v;
            charger();
        }, 300);
    });

    /* ---------------- Formulaire ---------------- */

    function roleCourant() {
        return $form.find('input[name=role]:checked').val();
    }

    function majRole() {
        $('#aideRole').text(AIDE_ROLE[roleCourant()] || '');
    }

    function choisirRole(r) {
        var $in = $form.find('input[name=role][value="' + r + '"]');
        if (!$in.length) $in = $form.find('input[name=role]').first();
        $form.find('input[name=role]').prop('checked', false).parent().removeClass('active');
        $in.prop('checked', true).parent().addClass('active');
        majRole();
    }

    function viderErreurs() {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('[data-err]').text('');
    }

    function ouvrirForm(u) {
        $form[0].reset();
        viderErreurs();
        var creation = !u;
        var soiMeme = !creation && +u.id === MOI.id;

        $('#champId').val(creation ? '' : u.id);
        $('#modalTitre').text(creation ? 'Ajouter un compte' : 'Modifier — ' + u.prenom + ' ' + u.nom);
        $('.bloc-creation').toggle(creation);
        $('#aideMdp').text(creation ?
            'Laissez vide pour générer automatiquement un mot de passe sécurisé (8 caractères minimum).' :
            'Laissez vide pour conserver le mot de passe actuel.');
        $('#champEnvoyer').prop('checked', creation);
        $('#champMdp').attr('type', 'password');
        $('#btnVoirMdp i').removeClass('fa-eye-slash').addClass('fa-eye');

        if (!creation) {
            $('#champNom').val(u.nom);
            $('#champPrenom').val(u.prenom);
            $('#champEmail').val(u.email);
            $('#champTel').val(u.telephone);
            $('#champOrg').val(u.organisation);
        }
        choisirRole(creation ? (filtres.role || 'admin') : u.role);

        // On ne peut pas changer son propre rôle : l'autre choix est grisé
        $form.find('input[name=role]').each(function() {
            var bloque = soiMeme && this.value !== u.role;
            $(this).prop('disabled', bloque).parent().toggleClass('disabled', bloque);
        });

        $('#modalUtilisateur').modal('show');
    }

    $form.on('change', 'input[name=role]', majRole);
    $('#btnAjouter').on('click', function() {
        ouvrirForm(null);
    });

    $('#btnVoirMdp').on('click', function() {
        var $m = $('#champMdp');
        $m.attr('type', $m.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    $('#btnGenererMdp').on('click', function() {
        var g = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '@#$%&*?!'];
        var tous = g.join(''),
            out = [],
            buf = new Uint32Array(24);
        crypto.getRandomValues(buf);
        g.forEach(function(s, i) {
            out.push(s[buf[i] % s.length]);
        });
        for (var i = 4; i < 12; i++) out.push(tous[buf[i] % tous.length]);
        for (var k = out.length - 1; k > 0; k--) {
            var j = buf[12 + k] % (k + 1);
            var t = out[k];
            out[k] = out[j];
            out[j] = t;
        }
        $('#champMdp').val(out.join('')).attr('type', 'text');
        $('#btnVoirMdp i').removeClass('fa-eye').addClass('fa-eye-slash');
    });

    $form.on('submit', function(e) {
        e.preventDefault();
        viderErreurs();
        var $btn = $('#btnEnregistrer').prop('disabled', true);

        post(URL.enregistrer, $form.serializeArray()).done(function(r) {
            $('#modalUtilisateur').modal('hide');
            notifier('success', r.message);
            if (r.identifiants) afficherIdentifiants(r.identifiants, r.email_envoye);
            else if (r.email_envoye === false) notifier('warning',
                "Compte enregistré, mais l'e-mail n'a pas pu être envoyé.");
            charger();
        }).fail(function(x) {
            var j = erreurAjax(x);
            if (j && j.erreurs) {
                $.each(j.erreurs, function(champ, msg) {
                    $form.find('[name="' + champ + '"]').addClass('is-invalid');
                    $form.find('[data-err="' + champ + '"]').text(msg);
                });
            }
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });

    /* ---------------- Actions de ligne ---------------- */

    function ligne(el) {
        var $tr = $(el).closest('tr');
        return {
            id: $tr.data('id'),
            nom: $tr.data('nom')
        };
    }

    $tbody.on('click', '.js-modifier', function() {
        $.getJSON(URL.detail + ligne(this).id).done(function(r) {
            majCsrf(r);
            ouvrirForm(r.utilisateur);
        }).fail(erreurAjax);
    });

    $tbody.on('change', '.js-statut', function() {
        var $sw = $(this),
            actif = $sw.is(':checked');
        $sw.prop('disabled', true);
        post(URL.statut, {
                id: ligne(this).id,
                statut: actif ? 'actif' : 'suspendu'
            })
            .done(function(r) {
                notifier('success', r.message);
                charger();
            })
            .fail(function(x) {
                erreurAjax(x);
                $sw.prop('checked', !actif).prop('disabled', false);
            });
    });

    $tbody.on('click', '.js-reinit', function() {
        var l = ligne(this);
        confirmer('Réinitialiser le mot de passe ?',
            'Un nouveau mot de passe sera généré pour <b>' + esc(l.nom) +
            '</b> et lui sera envoyé par e-mail. ' +
            'Un éventuel blocage du compte sera levé.',
            'Réinitialiser', false
        ).then(function(ok) {
            if (!ok) return;
            post(URL.reinitialiser, {
                    id: l.id,
                    envoyer_email: 1
                })
                .done(function(r) {
                    notifier('success', r.message);
                    afficherIdentifiants(r.identifiants, r.email_envoye);
                    charger();
                })
                .fail(erreurAjax);
        });
    });

    $tbody.on('click', '.js-supprimer', function() {
        var l = ligne(this);
        confirmer('Supprimer ce compte ?',
            'Le compte de <b>' + esc(l.nom) + '</b> sera supprimé définitivement. ' +
            'Si ce compte a déjà un historique (notes, candidatures…), la suppression sera refusée : suspendez-le plutôt.',
            'Supprimer', true
        ).then(function(ok) {
            if (!ok) return;
            post(URL.supprimer, {
                    id: l.id
                })
                .done(function(r) {
                    notifier('success', r.message);
                    charger();
                })
                .fail(erreurAjax);
        });
    });

    /* ---------------- Identifiants ---------------- */

    function afficherIdentifiants(id, emailEnvoye) {
        $('#idEmail').text(id.email);
        $('#idMdp').val(id.mot_de_passe);
        $('#idEmailStatut').html(
            emailEnvoye === true ?
            '<i class="fas fa-check-circle text-success mr-1"></i>Les identifiants ont été envoyés par e-mail.' :
            emailEnvoye === false ?
            '<i class="fas fa-times-circle text-danger mr-1"></i>L\'envoi de l\'e-mail a échoué — transmettez-les manuellement.' :
            '<i class="fas fa-info-circle text-muted mr-1"></i>Aucun e-mail envoyé.'
        );
        setTimeout(function() {
            $('#modalIdentifiants').modal('show');
        }, 400);
    }

    $('#btnCopierMdp').on('click', function() {
        var v = $('#idMdp').val(),
            $b = $(this);
        var fini = function() {
            $b.html('<i class="fas fa-check mr-1"></i>Copié');
            setTimeout(function() {
                $b.html('<i class="fas fa-copy mr-1"></i>Copier');
            }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(v).then(fini);
        else {
            $('#idMdp').trigger('select');
            document.execCommand('copy');
            fini();
        }
    });
    $('#modalIdentifiants').on('hidden.bs.modal', function() {
        $('#idMdp').val('');
    });

    charger();
});
</script>