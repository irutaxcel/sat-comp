<?php

/** @var object|null $concours */
$estPost = $this->input->method() === 'post';

// Valeur d'un champ : POST (après échec de validation) sinon base, sinon défaut
$v = function ($champ, $defaut = '') use ($concours) {
    return set_value($champ, $concours->$champ ?? $defaut);
};
// Case à cocher / switch
$chk = function ($champ, $defaut = 1) use ($concours, $estPost) {
    $on = $estPost ? (bool) $this->input->post($champ) : (bool) ($concours->$champ ?? $defaut);
    return $on ? 'checked' : '';
};
// Classe d'erreur Bootstrap
$err = function ($champ) {
    return form_error($champ) ? ' is-invalid' : '';
};

$extActives = $estPost
    ? (array) $this->input->post('extensions')
    : explode(',', $concours->extensions_autorisees ?? 'pdf,zip,jpg,png');

$statut       = $v('statut', 'brouillon');
$badgeStatut  = ['brouillon' => 'secondary', 'publie' => 'success', 'cloture' => 'warning', 'archive' => 'dark'];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?= html_escape($title) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= site_url() ?>">Accueil</a></li>
                        <li class="breadcrumb-item">Concours</li>
                        <li class="breadcrumb-item active"><?= html_escape($title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if ($msg = $this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check"></i> <?= html_escape($msg) ?>
            </div>
            <?php endif; ?>

            <?php if ($msg = $this->session->flashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-exclamation-triangle"></i> <?= html_escape($msg) ?>
            </div>
            <?php endif; ?>

            <?php if ($msg = $this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-ban"></i> <?= html_escape($msg) ?>
            </div>
            <?php endif; ?>

            <?php if (validation_errors() || $erreur_upload): ?>
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Le formulaire contient des erreurs</h5>
                <?php if ($erreur_upload): ?><p class="mb-1"><?= html_escape($erreur_upload) ?></p><?php endif; ?>
                <?= validation_errors('<div>• ', '</div>') ?>
            </div>
            <?php endif; ?>

            <?php if (!$concours): ?>
            <div class="callout callout-warning">
                <h5><i class="fas fa-info-circle"></i> Aucun concours configuré</h5>
                <p class="mb-0">Renseignez les informations ci-dessous pour créer le concours. Il restera en
                    <strong>brouillon</strong> (invisible du public) tant que vous ne le publiez pas.
                </p>
            </div>
            <?php endif; ?>

            <?= form_open_multipart('concours-parametres/enregistrer', ['id' => 'form-parametres', 'novalidate' => 'novalidate']) ?>

            <div class="row">
                <!-- ============ COLONNE PRINCIPALE ============ -->
                <div class="col-lg-9">
                    <div class="card card-success card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#tab-general"
                                        role="tab"><i class="fas fa-info-circle mr-1"></i> Général</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-participation"
                                        role="tab"><i class="fas fa-users mr-1"></i> Participation</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-soumission"
                                        role="tab"><i class="fas fa-file-upload mr-1"></i> Soumissions</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-documents"
                                        role="tab"><i class="fas fa-images mr-1"></i> Documents &amp; visuels</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#tab-contact"
                                        role="tab"><i class="fas fa-address-card mr-1"></i> Contact</a></li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">

                                <!-- ---------- GÉNÉRAL ---------- -->
                                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                                    <div class="row">
                                        <div class="form-group col-md-8">
                                            <label for="titre">Titre du concours <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="titre" id="titre" maxlength="200"
                                                class="form-control<?= $err('titre') ?>" value="<?= $v('titre') ?>"
                                                placeholder="Ex. Concours d'architecture SATRACO">
                                            <?= form_error('titre') ?>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="edition">Édition</label>
                                            <input type="text" name="edition" id="edition" maxlength="20"
                                                class="form-control<?= $err('edition') ?>"
                                                value="<?= $v('edition', date('Y')) ?>" placeholder="Ex. 2026">
                                            <?= form_error('edition') ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="sous_titre">Sous-titre / accroche</label>
                                        <input type="text" name="sous_titre" id="sous_titre" maxlength="255"
                                            class="form-control<?= $err('sous_titre') ?>"
                                            value="<?= $v('sous_titre') ?>"
                                            placeholder="Une phrase courte affichée sous le titre sur la page d'accueil">
                                        <?= form_error('sous_titre') ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description du concours <small
                                                class="text-muted">(obligatoire pour publier)</small></label>
                                        <textarea name="description" id="description" rows="6"
                                            class="form-control<?= $err('description') ?>"
                                            placeholder="Contexte, thème, enjeux…"><?= $v('description') ?></textarea>
                                        <?= form_error('description') ?>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="objectifs">Objectifs</label>
                                            <textarea name="objectifs" id="objectifs" rows="5" class="form-control"
                                                placeholder="Un objectif par ligne"><?= $v('objectifs') ?></textarea>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="criteres_eligibilite">Critères d'éligibilité</label>
                                            <textarea name="criteres_eligibilite" id="criteres_eligibilite" rows="5"
                                                class="form-control"
                                                placeholder="Un critère par ligne"><?= $v('criteres_eligibilite') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- ---------- PARTICIPATION ---------- -->
                                <div class="tab-pane fade" id="tab-participation" role="tabpanel">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="public_cible">Public cible <span
                                                    class="text-danger">*</span></label>
                                            <select name="public_cible" id="public_cible"
                                                class="form-control<?= $err('public_cible') ?>">
                                                <?php foreach ($publics as $cle => $label): ?>
                                                <option value="<?= $cle ?>"
                                                    <?= $v('public_cible', 'mixte') === $cle ? 'selected' : '' ?>>
                                                    <?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?= form_error('public_cible') ?>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="mode_validation_compte">Validation des comptes candidats <span
                                                    class="text-danger">*</span></label>
                                            <select name="mode_validation_compte" id="mode_validation_compte"
                                                class="form-control<?= $err('mode_validation_compte') ?>">
                                                <?php foreach ($validations as $cle => $label): ?>
                                                <option value="<?= $cle ?>"
                                                    <?= $v('mode_validation_compte', 'manuelle') === $cle ? 'selected' : '' ?>>
                                                    <?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?= form_error('mode_validation_compte') ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="d-block">Mode de participation <span
                                                class="text-danger">*</span></label>
                                        <?php foreach ($modes as $cle => $label): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="radio" class="custom-control-input js-mode"
                                                id="mode_<?= $cle ?>" name="mode_participation" value="<?= $cle ?>"
                                                <?= $v('mode_participation', 'les_deux') === $cle ? 'checked' : '' ?>>
                                            <label class="custom-control-label"
                                                for="mode_<?= $cle ?>"><?= $label ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                        <?= form_error('mode_participation') ?>
                                    </div>

                                    <div class="row" id="bloc-equipe">
                                        <div class="form-group col-md-3">
                                            <label for="taille_equipe_min">Membres min.</label>
                                            <input type="number" min="1" max="20" name="taille_equipe_min"
                                                id="taille_equipe_min"
                                                class="form-control<?= $err('taille_equipe_min') ?>"
                                                value="<?= $v('taille_equipe_min', 2) ?>">
                                            <?= form_error('taille_equipe_min') ?>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="taille_equipe_max">Membres max.</label>
                                            <input type="number" min="1" max="20" name="taille_equipe_max"
                                                id="taille_equipe_max"
                                                class="form-control<?= $err('taille_equipe_max') ?>"
                                                value="<?= $v('taille_equipe_max', 5) ?>">
                                            <?= form_error('taille_equipe_max') ?>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-center">
                                            <small class="text-muted">Le chef d'équipe est compté parmi les
                                                membres.</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- ---------- SOUMISSIONS ---------- -->
                                <div class="tab-pane fade" id="tab-soumission" role="tabpanel">
                                    <div class="form-group">
                                        <label class="d-block">Extensions autorisées <span
                                                class="text-danger">*</span></label>
                                        <?php foreach ($extensions as $ext): ?>
                                        <div class="custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" class="custom-control-input" id="ext_<?= $ext ?>"
                                                name="extensions[]" value="<?= $ext ?>"
                                                <?= in_array($ext, $extActives, true) ? 'checked' : '' ?>>
                                            <label class="custom-control-label"
                                                for="ext_<?= $ext ?>">.<?= $ext ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                        <?= form_error('extensions[]') ?>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="taille_max_fichier_mo">Taille maximale par fichier</label>
                                            <div class="input-group">
                                                <input type="number" min="1" max="500" name="taille_max_fichier_mo"
                                                    id="taille_max_fichier_mo"
                                                    class="form-control<?= $err('taille_max_fichier_mo') ?>"
                                                    value="<?= $v('taille_max_fichier_mo', 50) ?>"
                                                    data-limite-php="<?= (int) $limite_php ?>">
                                                <div class="input-group-append"><span class="input-group-text">Mo</span>
                                                </div>
                                            </div>
                                            <?= form_error('taille_max_fichier_mo') ?>
                                            <small class="form-text text-muted">Limite actuelle du serveur PHP :
                                                <strong><?= (int) $limite_php ?> Mo</strong></small>
                                            <small id="alerte-limite" class="form-text text-warning d-none"><i
                                                    class="fas fa-exclamation-triangle"></i> Valeur supérieure à la
                                                limite du serveur : augmentez <code>upload_max_filesize</code> et
                                                <code>post_max_size</code>.</small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="nb_max_fichiers">Nombre maximal de fichiers par projet</label>
                                            <input type="number" min="1" max="50" name="nb_max_fichiers"
                                                id="nb_max_fichiers" class="form-control<?= $err('nb_max_fichiers') ?>"
                                                value="<?= $v('nb_max_fichiers', 10) ?>">
                                            <?= form_error('nb_max_fichiers') ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input"
                                                id="modification_avant_cloture" name="modification_avant_cloture"
                                                value="1" <?= $chk('modification_avant_cloture') ?>>
                                            <label class="custom-control-label"
                                                for="modification_avant_cloture">Autoriser les candidats à
                                                modifier/remplacer leurs fichiers jusqu'à la clôture</label>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="anonymisation_jury"
                                                name="anonymisation_jury" value="1" <?= $chk('anonymisation_jury') ?>>
                                            <label class="custom-control-label" for="anonymisation_jury">Anonymiser les
                                                projets présentés au jury</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- ---------- DOCUMENTS & VISUELS ---------- -->
                                <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                                    <?php
                                    $docs = [
                                        'logo'          => ['label' => 'Logo du concours', 'accept' => '.jpg,.jpeg,.png,.svg', 'aide' => 'JPG, PNG ou SVG — 2 Mo max.', 'image' => true],
                                        'banniere'      => ['label' => "Bannière de la page d'accueil", 'accept' => '.jpg,.jpeg,.png', 'aide' => 'JPG ou PNG, idéalement 1920×600 — 5 Mo max.', 'image' => true],
                                        'reglement_pdf' => ['label' => 'Règlement du concours (PDF)', 'accept' => '.pdf', 'aide' => 'PDF — 20 Mo max. Obligatoire pour publier.', 'image' => false],
                                        'guide_pdf'     => ['label' => 'Guide de participation (PDF)', 'accept' => '.pdf', 'aide' => 'PDF — 20 Mo max.', 'image' => false],
                                    ];
                                    foreach ($docs as $champ => $d):
                                        $actuel = $concours->$champ ?? null;
                                    ?>
                                    <div class="form-group border rounded p-3">
                                        <label><?= $d['label'] ?></label>
                                        <?php if ($actuel): ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <?php if ($d['image']): ?>
                                            <img src="<?= base_url($actuel) ?>" alt="" class="img-thumbnail mr-3"
                                                style="max-height:80px">
                                            <?php else: ?>
                                            <a href="<?= base_url($actuel) ?>" target="_blank"
                                                class="btn btn-sm btn-outline-danger mr-3"><i
                                                    class="fas fa-file-pdf"></i> Voir le document actuel</a>
                                            <?php endif; ?>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="supprimer_<?= $champ ?>" name="supprimer_<?= $champ ?>"
                                                    value="1">
                                                <label class="custom-control-label text-danger"
                                                    for="supprimer_<?= $champ ?>">Supprimer</label>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="<?= $champ ?>"
                                                name="<?= $champ ?>" accept="<?= $d['accept'] ?>">
                                            <label class="custom-file-label"
                                                for="<?= $champ ?>"><?= $actuel ? 'Remplacer le fichier…' : 'Choisir un fichier…' ?></label>
                                        </div>
                                        <small class="form-text text-muted"><?= $d['aide'] ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- ---------- CONTACT ---------- -->
                                <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="email_contact">E-mail de contact</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fas fa-envelope"></i></span></div>
                                                <input type="email" name="email_contact" id="email_contact"
                                                    maxlength="150" class="form-control<?= $err('email_contact') ?>"
                                                    value="<?= $v('email_contact') ?>"
                                                    placeholder="concours@satraco.bi">
                                            </div>
                                            <?= form_error('email_contact') ?>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="telephone_contact">Téléphone</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text"><i
                                                            class="fas fa-phone"></i></span></div>
                                                <input type="text" name="telephone_contact" id="telephone_contact"
                                                    maxlength="30" class="form-control<?= $err('telephone_contact') ?>"
                                                    value="<?= $v('telephone_contact') ?>" placeholder="+257 ...">
                                            </div>
                                            <?= form_error('telephone_contact') ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============ COLONNE LATÉRALE ============ -->
                <div class="col-lg-3">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-flag mr-1"></i> Publication</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="statut">Statut</label>
                                <select name="statut" id="statut" class="form-control<?= $err('statut') ?>">
                                    <?php foreach ($statuts as $cle => $label): ?>
                                    <option value="<?= $cle ?>" <?= $statut === $cle ? 'selected' : '' ?>><?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?= form_error('statut') ?>
                                <small class="form-text text-muted">Seul un concours « Publié » est visible sur le site
                                    public.</small>
                            </div>

                            <?php if ($concours): ?>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li>Statut actuel : <span
                                        class="badge badge-<?= $badgeStatut[$concours->statut] ?? 'secondary' ?>"><?= $statuts[$concours->statut] ?? '' ?></span>
                                </li>
                                <li>Créé le : <?= date('d/m/Y H:i', strtotime($concours->created_at)) ?></li>
                                <?php if ($concours->updated_at): ?>
                                <li>Modifié le : <?= date('d/m/Y H:i', strtotime($concours->updated_at)) ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-save mr-1"></i>
                                <?= $concours ? 'Enregistrer' : 'Créer le concours' ?></button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> Complétude</h3>
                        </div>
                        <div class="card-body">
                            <div class="progress progress-sm mb-3">
                                <div class="progress-bar bg-success" style="width: <?= $completude['pourcentage'] ?>%">
                                </div>
                            </div>
                            <p class="text-center mb-3"><strong><?= $completude['pourcentage'] ?> %</strong></p>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($completude['items'] as $item): ?>
                                <li class="mb-1">
                                    <i
                                        class="fas <?= $item['ok'] ? 'fa-check-circle text-success' : 'fa-circle text-muted' ?> mr-1"></i>
                                    <?= $item['label'] ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="card card-outline card-secondary">
                        <div class="card-body small">
                            <i class="fas fa-lightbulb text-warning"></i>
                            Les <a href="<?= site_url('concours-dates') ?>">dates clés</a> (ouverture, clôture des
                            soumissions…) se configurent dans leur page dédiée.
                        </div>
                    </div>
                </div>
            </div>

            <?= form_close() ?>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bloc taille d'équipe visible uniquement si l'équipe est possible
    var radios = document.querySelectorAll('.js-mode');
    var blocEquipe = document.getElementById('bloc-equipe');

    function majEquipe() {
        var mode = document.querySelector('.js-mode:checked');
        blocEquipe.style.display = (mode && mode.value === 'individuel') ? 'none' : '';
    }
    radios.forEach(function(r) {
        r.addEventListener('change', majEquipe);
    });
    majEquipe();

    // Nom du fichier choisi dans les champs custom-file
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var label = input.nextElementSibling;
            if (input.files.length) label.textContent = input.files[0].name;
        });
    });

    // Avertissement si la taille dépasse la limite PHP
    var taille = document.getElementById('taille_max_fichier_mo');
    var alerte = document.getElementById('alerte-limite');

    function majLimite() {
        var limite = parseInt(taille.dataset.limitePhp, 10) || 0;
        alerte.classList.toggle('d-none', !(limite && parseInt(taille.value, 10) > limite));
    }
    taille.addEventListener('input', majLimite);
    majLimite();

    // Après une erreur, ouvrir le premier onglet contenant un champ invalide
    var invalide = document.querySelector('#form-parametres .is-invalid');
    if (invalide) {
        var pane = invalide.closest('.tab-pane');
        var lien = pane && document.querySelector('a[href="#' + pane.id + '"]');
        if (lien && window.jQuery) {
            jQuery(lien).tab('show');
        }
    }
});
</script>