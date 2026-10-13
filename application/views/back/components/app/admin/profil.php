<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$fdate = function ($d, $avecHeure = true) {
    if (!$d) return null;
    $t = strtotime($d);
    return $t ? date($avecHeure ? 'd/m/Y \à H:i' : 'd/m/Y', $t) : $d;
};
$initiales = strtoupper(mb_substr($moi['prenom'], 0, 1) . mb_substr($moi['nom'], 0, 1));
$estAdmin  = $moi['role'] === 'admin';
?>

<style>
.pf-avatar {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    margin: 0 auto .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
    color: #fff;
    background: #1f7a45;
    box-shadow: 0 0 0 4px #e9f5ee
}

.pf-avatar.jury {
    background: #d39e00;
    box-shadow: 0 0 0 4px #fbf3dc
}

.pf-meta .list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .6rem .25rem;
    border-left: 0;
    border-right: 0
}

.pf-meta .list-group-item:first-child {
    border-top: 0
}

.pf-meta .list-group-item span:first-child {
    color: #6c757d;
    font-size: .875rem
}

.pf-meta .list-group-item span:last-child {
    font-weight: 500;
    text-align: right
}

.pf-tabs .nav-link {
    color: #495057
}

.pf-tabs .nav-link.active {
    background: #28a745
}

.pf-force {
    height: 6px;
    border-radius: 3px;
    background: #e9ecef;
    overflow: hidden;
    margin-top: .5rem
}

.pf-force>div {
    height: 100%;
    width: 0;
    transition: width .2s, background .2s
}

.pf-regles {
    list-style: none;
    padding: 0;
    margin: .5rem 0 0;
    font-size: .8rem
}

.pf-regles li {
    color: #6c757d;
    margin-bottom: .15rem
}

.pf-regles li i {
    width: 1rem
}

.pf-regles li.ok {
    color: #28a745
}

.pf-activite {
    list-style: none;
    margin: 0;
    padding: 0
}

.pf-activite li {
    display: flex;
    gap: .75rem;
    padding: .65rem 0;
    border-bottom: 1px solid #f1f1f1
}

.pf-activite li:last-child {
    border-bottom: 0
}

.pf-activite .pf-ico {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .8rem
}

.pf-activite .pf-quand {
    font-size: .75rem;
    color: #6c757d
}

.pf-vide {
    padding: 2rem 1rem;
    text-align: center;
    color: #6c757d
}
</style>

<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <h1 class="m-0"><i class="fas fa-id-card mr-2 text-success"></i><?= html_escape($title) ?></h1>
            <small class="text-muted">Vos informations personnelles et la sécurité de votre compte.</small>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <!-- Carte d'identité -->
                <div class="col-lg-4">
                    <div class="card card-success card-outline">
                        <div class="card-body box-profile">
                            <div class="pf-avatar <?= html_escape($moi['role']) ?>" id="pfAvatar">
                                <?= html_escape($initiales) ?></div>
                            <h3 class="profile-username text-center mb-0" id="pfNom">
                                <?= html_escape($moi['prenom'] . ' ' . $moi['nom']) ?></h3>
                            <p class="text-muted text-center mb-1" id="pfEmail"><?= html_escape($moi['email']) ?></p>
                            <p class="text-center mb-3">
                                <span class="badge badge-<?= $estAdmin ? 'success' : 'warning' ?> px-2 py-1">
                                    <i
                                        class="fas <?= $estAdmin ? 'fa-user-cog' : 'fa-gavel' ?> mr-1"></i><?= html_escape($moi['role_libelle']) ?>
                                </span>
                            </p>

                            <ul class="list-group list-group-unbordered pf-meta mb-0">
                                <li class="list-group-item"><span>Organisation</span>
                                    <span id="pfOrg"><?= html_escape($moi['organisation'] ?: '—') ?></span>
                                </li>
                                <li class="list-group-item"><span>Membre depuis</span>
                                    <span><?= $fdate($moi['created_at'], false) ?></span>
                                </li>
                                <li class="list-group-item"><span>Dernière connexion</span>
                                    <span><?= $fdate($moi['derniere_connexion']) ?: '—' ?></span>
                                </li>
                                <li class="list-group-item"><span>E-mail vérifié</span>
                                    <span><?= $moi['email_verifie_le']
                                                ? '<i class="fas fa-check-circle text-success mr-1"></i>' . $fdate($moi['email_verifie_le'], false)
                                                : '<span class="text-warning">Non</span>' ?></span>
                                </li>
                                <li class="list-group-item"><span>Actions enregistrées</span>
                                    <span><?= number_format((int) $nb_activites, 0, ',', ' ') ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Onglets -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills pf-tabs">
                                <li class="nav-item"><a class="nav-link active" href="#tabInfos" data-toggle="tab">
                                        <i class="fas fa-user mr-1"></i>Informations</a></li>
                                <li class="nav-item"><a class="nav-link" href="#tabSecurite" data-toggle="tab">
                                        <i class="fas fa-shield-alt mr-1"></i>Sécurité</a></li>
                                <li class="nav-item"><a class="nav-link" href="#tabActivite" data-toggle="tab"
                                        id="lienActivite">
                                        <i class="fas fa-history mr-1"></i>Mon activité</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">

                                <!-- Informations -->
                                <div class="tab-pane active" id="tabInfos">
                                    <form id="formInfos" novalidate autocomplete="off">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="pNom">Nom <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="nom" id="pNom"
                                                    maxlength="100" required value="<?= html_escape($moi['nom']) ?>">
                                                <div class="invalid-feedback" data-err="nom"></div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="pPrenom">Prénom <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="prenom" id="pPrenom"
                                                    maxlength="100" required value="<?= html_escape($moi['prenom']) ?>">
                                                <div class="invalid-feedback" data-err="prenom"></div>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="pEmail">E-mail <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" name="email" id="pEmail"
                                                    maxlength="150" required value="<?= html_escape($moi['email']) ?>"
                                                    data-initial="<?= html_escape($moi['email']) ?>">
                                                <small class="form-text text-muted">C'est aussi votre identifiant de
                                                    connexion.</small>
                                                <div class="invalid-feedback" data-err="email"></div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="pTel">Téléphone</label>
                                                <input type="text" class="form-control" name="telephone" id="pTel"
                                                    maxlength="30" placeholder="+257 …"
                                                    value="<?= html_escape($moi['telephone']) ?>">
                                                <div class="invalid-feedback" data-err="telephone"></div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="pOrg">Organisation</label>
                                            <input type="text" class="form-control" name="organisation" id="pOrg"
                                                maxlength="150" value="<?= html_escape($moi['organisation']) ?>">
                                            <div class="invalid-feedback" data-err="organisation"></div>
                                        </div>
                                        <div class="form-group">
                                            <label>Rôle</label>
                                            <input type="text" class="form-control"
                                                value="<?= html_escape($moi['role_libelle']) ?>" disabled>
                                            <small class="form-text text-muted">Le rôle ne peut être modifié que par un
                                                autre administrateur.</small>
                                        </div>

                                        <div class="alert alert-warning py-2" id="blocMdpEmail" style="display:none">
                                            <label for="pMdpEmail" class="small mb-1">
                                                <i class="fas fa-lock mr-1"></i>Vous changez d'adresse e-mail :
                                                confirmez avec votre mot de passe actuel.
                                            </label>
                                            <input type="password" class="form-control form-control-sm"
                                                name="mot_de_passe_actuel" id="pMdpEmail"
                                                autocomplete="current-password">
                                            <div class="invalid-feedback" data-err="mot_de_passe_actuel"></div>
                                        </div>

                                        <div class="text-right">
                                            <button type="reset" class="btn btn-light"
                                                id="btnAnnulerInfos">Annuler</button>
                                            <button type="submit" class="btn btn-success" id="btnInfos">
                                                <i class="fas fa-save mr-1"></i>Enregistrer</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Sécurité -->
                                <div class="tab-pane" id="tabSecurite">
                                    <form id="formMdp" novalidate autocomplete="off">
                                        <div class="form-group">
                                            <label for="mActuel">Mot de passe actuel <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="actuel" id="mActuel"
                                                    required autocomplete="current-password">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary js-voir" type="button"
                                                        data-cible="#mActuel"><i class="fas fa-eye"></i></button>
                                                </div>
                                            </div>
                                            <div class="invalid-feedback d-block" data-err="actuel"></div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="mNouveau">Nouveau mot de passe <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" name="nouveau"
                                                        id="mNouveau" required minlength="8" maxlength="72"
                                                        autocomplete="new-password">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary js-voir" type="button"
                                                            data-cible="#mNouveau"><i class="fas fa-eye"></i></button>
                                                    </div>
                                                </div>
                                                <div class="pf-force">
                                                    <div id="forceBarre"></div>
                                                </div>
                                                <small class="text-muted" id="forceTexte">&nbsp;</small>
                                                <div class="invalid-feedback d-block" data-err="nouveau"></div>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="mConfirm">Confirmer <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" name="confirmation"
                                                        id="mConfirm" required maxlength="72"
                                                        autocomplete="new-password">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-outline-secondary js-voir" type="button"
                                                            data-cible="#mConfirm"><i class="fas fa-eye"></i></button>
                                                    </div>
                                                </div>
                                                <small id="confirmTexte">&nbsp;</small>
                                                <div class="invalid-feedback d-block" data-err="confirmation"></div>
                                            </div>
                                        </div>

                                        <ul class="pf-regles" id="regles">
                                            <li data-regle="longueur"><i class="far fa-circle"></i> Au moins 8
                                                caractères</li>
                                            <li data-regle="lettre"><i class="far fa-circle"></i> Au moins une lettre
                                            </li>
                                            <li data-regle="chiffre"><i class="far fa-circle"></i> Au moins un chiffre
                                            </li>
                                            <li data-regle="bonus"><i class="far fa-circle"></i> Recommandé : majuscule
                                                et caractère spécial, 12 caractères ou plus</li>
                                        </ul>

                                        <div class="d-flex flex-wrap align-items-center justify-content-between mt-3">
                                            <small class="text-muted my-1"><i class="fas fa-info-circle mr-1"></i>
                                                Après 5 essais incorrects, le changement est bloqué 10 minutes.</small>
                                            <button type="submit" class="btn btn-success my-1" id="btnMdp" disabled>
                                                <i class="fas fa-key mr-1"></i>Changer le mot de passe</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Mon activité -->
                                <div class="tab-pane" id="tabActivite">
                                    <ul class="pf-activite" id="listeActivite">
                                        <li class="pf-vide d-block"><i class="fas fa-spinner fa-spin"></i> Chargement…
                                        </li>
                                    </ul>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted" id="resumeActivite"></small>
                                        <?php if ($estAdmin): ?>
                                        <a href="<?= site_url('journal') ?>" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-history mr-1"></i>Journal complet</a>
                                        <?php endif; ?>
                                    </div>
                                    <p class="small text-muted mt-3 mb-0"><i class="fas fa-shield-alt mr-1"></i>
                                        Une connexion que vous ne reconnaissez pas ? Changez immédiatement votre mot de
                                        passe
                                        et prévenez un administrateur.</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    var URL = {
        infos: '<?= site_url('profil/infos') ?>',
        mdp: '<?= site_url('profil/mot-de-passe') ?>',
        activite: '<?= site_url('profil/activite') ?>'
    };
    var CSRF = {
        nom: '<?= $this->security->get_csrf_token_name() ?>',
        hash: '<?= $this->security->get_csrf_hash() ?>'
    };
    var ACTIONS = <?= json_encode($actions, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    var EMAIL_LOCAL = <?= json_encode(strtolower(strstr($moi['email'], '@', true)), JSON_HEX_TAG) ?>;

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

    function majCsrf(j) {
        if (j && j.csrf) CSRF.hash = j.csrf;
    }

    function post(url, data) {
        data.push({
            name: CSRF.nom,
            value: CSRF.hash
        });
        return $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json'
            })
            .done(majCsrf).fail(function(x) {
                majCsrf(x.responseJSON);
            });
    }

    function viderErreurs($f) {
        $f.find('.is-invalid').removeClass('is-invalid');
        $f.find('[data-err]').text('');
    }

    function erreurs($f, x) {
        var j = x.responseJSON;
        var msg = (j && j.message) || (x.status === 404 ?
            'Route introuvable (404) : ajoutez les routes « profil/… ».' :
            'Une erreur est survenue (HTTP ' + x.status + ').');
        if (j && j.erreurs) {
            $.each(j.erreurs, function(champ, m) {
                $f.find('[name="' + champ + '"]').addClass('is-invalid');
                $f.find('[data-err="' + champ + '"]').text(m);
            });
        }
        if (!j && window.console) console.error('[profil]', x.status, (x.responseText || '').substring(0,
        2000));
        notifier('error', msg);
    }

    function dateFr(s) {
        var m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(s || '');
        return m ? m[3] + '/' + m[2] + '/' + m[1] + ' à ' + m[4] + ':' + m[5] : esc(s);
    }

    function appareil(ua) {
        if (!ua) return '';
        var nav = /Edg\//.test(ua) ? 'Edge' : /OPR\//.test(ua) ? 'Opera' : /Firefox\//.test(ua) ? 'Firefox' :
            /Chrome\//.test(ua) ? 'Chrome' : /Safari\//.test(ua) ? 'Safari' : 'Autre navigateur';
        var os = /iPhone|iPad/.test(ua) ? 'iOS' : /Android/.test(ua) ? 'Android' : /Windows/.test(ua) ?
            'Windows' :
            /Mac OS X/.test(ua) ? 'macOS' : /Linux/.test(ua) ? 'Linux' : '';
        return nav + (os ? ' · ' + os : '');
    }

    /* ---------------- Informations ---------------- */

    var $fi = $('#formInfos');
    var $email = $('#pEmail');

    function emailModifie() {
        return $.trim($email.val()).toLowerCase() !== String($email.data('initial')).toLowerCase();
    }
    $email.on('input', function() {
        var m = emailModifie();
        $('#blocMdpEmail').toggle(m);
        if (!m) $('#pMdpEmail').val('');
    });
    $fi.on('reset', function() {
        viderErreurs($fi);
        setTimeout(function() {
            $('#blocMdpEmail').hide();
        }, 0);
    });

    $fi.on('submit', function(e) {
        e.preventDefault();
        viderErreurs($fi);
        var $b = $('#btnInfos').prop('disabled', true);
        post(URL.infos, $fi.serializeArray()).done(function(r) {
                notifier('success', r.message);
                var u = r.utilisateur;
                if (u) {
                    // Met à jour la carte et les valeurs de référence du formulaire
                    $('#pfNom').text(u.prenom + ' ' + u.nom);
                    $('#pfEmail').text(u.email);
                    $('#pfOrg').text(u.organisation || '—');
                    $('#pfAvatar').text(((u.prenom || '').charAt(0) + (u.nom || '').charAt(0))
                        .toUpperCase());
                    $fi.find('input[name]').each(function() {
                        if (this.name !== 'mot_de_passe_actuel') this.defaultValue = this
                            .value;
                    });
                    $email.data('initial', u.email);
                    $('#pMdpEmail').val('');
                    $('#blocMdpEmail').hide();
                    EMAIL_LOCAL = u.email.split('@')[0].toLowerCase();
                }
            }).fail(function(x) {
                erreurs($fi, x);
            })
            .always(function() {
                $b.prop('disabled', false);
            });
    });

    /* ---------------- Sécurité ---------------- */

    var $fm = $('#formMdp');

    $('.js-voir').on('click', function() {
        var $c = $($(this).data('cible'));
        $c.attr('type', $c.attr('type') === 'password' ? 'text' : 'password');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    function evaluer(m) {
        var r = {
            longueur: m.length >= 8,
            lettre: /[A-Za-z]/.test(m),
            chiffre: /\d/.test(m),
            bonus: m.length >= 12 && /[A-Z]/.test(m) && /[^A-Za-z0-9]/.test(m)
        };
        var score = 0;
        if (m.length >= 8) score++;
        if (m.length >= 12) score++;
        if (/[a-z]/.test(m) && /[A-Z]/.test(m)) score++;
        if (/\d/.test(m)) score++;
        if (/[^A-Za-z0-9]/.test(m)) score++;
        if (EMAIL_LOCAL && m.toLowerCase().indexOf(EMAIL_LOCAL) !== -1) {
            score = Math.min(score, 1);
            r.email = true;
        }
        r.score = score;
        r.valide = r.longueur && r.lettre && r.chiffre && !r.email;
        return r;
    }

    function majMdp() {
        var m = $('#mNouveau').val(),
            c = $('#mConfirm').val(),
            r = evaluer(m);

        $('#regles li').each(function() {
            var ok = !!r[$(this).data('regle')];
            $(this).toggleClass('ok', ok).find('i').attr('class', ok ? 'fas fa-check-circle' :
                'far fa-circle');
        });

        var niveaux = [{
                t: 'Très faible',
                c: '#dc3545'
            }, {
                t: 'Faible',
                c: '#dc3545'
            }, {
                t: 'Moyen',
                c: '#ffc107'
            },
            {
                t: 'Bon',
                c: '#17a2b8'
            }, {
                t: 'Fort',
                c: '#28a745'
            }, {
                t: 'Très fort',
                c: '#1f7a45'
            }
        ];
        var n = niveaux[r.score];
        $('#forceBarre').css({
            width: m ? (r.score / 5 * 100) + '%' : 0,
            background: n.c
        });
        $('#forceTexte').html(m ? (r.email ?
            '<span class="text-danger">Ne doit pas contenir votre e-mail</span>' : 'Robustesse : ' + n.t
            ) : '&nbsp;');

        var concorde = c && c === m;
        $('#confirmTexte').attr('class', concorde ? 'text-success' : 'text-danger')
            .html(c ? (concorde ? '<i class="fas fa-check mr-1"></i>Identiques' :
                'Les mots de passe diffèrent') : '&nbsp;');

        $('#btnMdp').prop('disabled', !($('#mActuel').val() && r.valide && concorde));
    }
    $fm.on('input', 'input', majMdp);

    $fm.on('submit', function(e) {
        e.preventDefault();
        viderErreurs($fm);
        var $b = $('#btnMdp').prop('disabled', true);
        post(URL.mdp, $fm.serializeArray()).done(function(r) {
            notifier('success', r.message);
            $fm[0].reset();
            $fm.find('input').attr('type', 'password');
            $fm.find('.js-voir i').removeClass('fa-eye-slash').addClass('fa-eye');
            majMdp();
            chargeActivite = true;
        }).fail(function(x) {
            erreurs($fm, x);
            if (x.status === 422) $('#mActuel').trigger('focus').select();
        }).always(function() {
            majMdp();
        });
    });

    /* ---------------- Mon activité ---------------- */

    var chargeActivite = true;
    $('#lienActivite').on('shown.bs.tab', function() {
        if (!chargeActivite) return;
        chargeActivite = false;
        $.getJSON(URL.activite).done(function(r) {
            majCsrf(r);
            if (!r.lignes.length) {
                $('#listeActivite').html(
                    '<li class="pf-vide d-block">Aucune activité enregistrée pour le moment.</li>'
                    );
                $('#resumeActivite').text('');
                return;
            }
            $('#listeActivite').html(r.lignes.map(function(l) {
                var a = ACTIONS[l.action] || {
                    libelle: l.action.replace(/_/g, ' '),
                    icone: 'fa-circle',
                    couleur: 'secondary'
                };
                return '<li><span class="pf-ico bg-' + a.couleur +
                    '"><i class="fas ' + a.icone + '"></i></span><div>' +
                    '<div>' + esc(a.libelle) + '</div>' +
                    '<div class="pf-quand">' + dateFr(l.created_at) +
                    (l.ip ? ' · ' + esc(l.ip) : '') + (l.user_agent ? ' · ' + esc(
                        appareil(l.user_agent)) : '') + '</div>' +
                    '</div></li>';
            }).join(''));
            $('#resumeActivite').text(r.lignes.length < r.total ?
                r.lignes.length + ' dernières actions sur ' + r.total : r.total +
                ' action' + (r.total > 1 ? 's' : ''));
        }).fail(function(x) {
            chargeActivite = true;
            $('#listeActivite').html(
                '<li class="pf-vide d-block text-danger">Impossible de charger votre activité (HTTP ' +
                x.status + ').</li>');
        });
    });

    // Ouvre directement un onglet via l'URL : profil#securite / profil#activite
    var ancre = {
        '#securite': '#tabSecurite',
        '#activite': '#tabActivite'
    } [location.hash];
    if (ancre) $('.pf-tabs a[href="' + ancre + '"]').tab('show');
});
</script>