<?php

/** @var object|null $concours */
// 15 728 640 → "15 Mo"
$taille = function ($octets) {
    $octets = (int) $octets;
    if ($octets >= 1073741824) return number_format($octets / 1073741824, 1, ',', ' ') . ' Go';
    if ($octets >= 1048576)    return number_format($octets / 1048576, 1, ',', ' ') . ' Mo';
    if ($octets >= 1024)       return number_format($octets / 1024, 0, ',', ' ') . ' Ko';
    return $octets . ' o';
};
// Icône selon l'extension
$iconeFichier = function ($ext) {
    $map = [
        'pdf'  => 'fa-file-pdf text-danger',
        'docx' => 'fa-file-word text-primary',
        'xlsx' => 'fa-file-excel text-success',
        'zip'  => 'fa-file-archive text-warning',
        'rar' => 'fa-file-archive text-warning',
        '7z' => 'fa-file-archive text-warning',
        'jpg'  => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png'  => 'fa-file-image text-info',
    ];
    return 'fas ' . ($map[$ext] ?? 'fa-cube text-secondary'); // dwg, dxf, skp, rvt, ifc
};
$mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
$date = function ($dt) use ($mois) {
    $ts = strtotime($dt);
    return date('d', $ts) . ' ' . $mois[date('n', $ts) - 1] . ' ' . date('Y', $ts);
};
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
                        <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>">Accueil</a></li>
                        <li class="breadcrumb-item">Concours</li>
                        <li class="breadcrumb-item active"><?= html_escape($title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php foreach (['success' => ['success', 'fa-check'], 'warning' => ['warning', 'fa-exclamation-triangle'], 'error' => ['danger', 'fa-ban']] as $cle => $style): ?>
                <?php if ($msg = $this->session->flashdata($cle)): ?>
                    <div class="alert alert-<?= $style[0] ?> alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas <?= $style[1] ?>"></i> <?= html_escape($msg) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!$concours): ?>
                <div class="callout callout-warning">
                    <h5><i class="fas fa-info-circle"></i> Aucun concours configuré</h5>
                    <p>Les ressources se rattachent à un concours. Commencez par le créer.</p>
                    <a href="<?= site_url('concours-parametres') ?>" class="btn btn-success btn-sm"><i
                            class="fas fa-sliders-h mr-1"></i> Aller aux paramètres</a>
                </div>
            <?php else: ?>

                <?php if (!$stats['brief_publie']): ?>
                    <div class="callout callout-danger">
                        <h5><i class="fas fa-exclamation-circle text-danger"></i> Aucun brief publié</h5>
                        <p class="mb-0">Les candidats ont besoin du brief d'architecture pour concevoir leur projet. Ajoutez-le
                            dans la catégorie « Brief d'architecture » et publiez-le.</p>
                    </div>
                <?php endif; ?>

                <!-- ============ CHIFFRES CLÉS ============ -->
                <div class="row">
                    <div class="col-md-4 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-folder-open"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Ressources publiées</span>
                                <span class="info-box-number"><?= $stats['publiees'] ?> <small class="text-muted">/
                                        <?= $stats['nb'] ?></small></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-hdd"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Volume total</span>
                                <span class="info-box-number"><?= $taille($stats['taille']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-download"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Téléchargements candidats</span>
                                <span
                                    class="info-box-number"><?= number_format($stats['telechargements'], 0, ',', ' ') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted"><i class="fas fa-lock mr-1"></i> Les fichiers sont stockés hors du dossier
                        public et ne sont servis qu'après contrôle d'accès.</small>
                    <button type="button" class="btn btn-success js-ajouter" data-toggle="modal"
                        data-target="#modal-ressource">
                        <i class="fas fa-upload mr-1"></i> Ajouter une ressource
                    </button>
                </div>

                <!-- ============ RESSOURCES PAR CATÉGORIE ============ -->
                <?php foreach ($categories as $cat => $def):
                    $liste = $groupes[$cat];
                    if (!$liste && $cat !== 'brief') continue; // le brief reste toujours visible 
                ?>
                    <div class="card <?= $cat === 'brief' ? 'card-success card-outline' : '' ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="<?= $def['icone'] ?> mr-1"></i> <?= $def['label'] ?> <small
                                    class="text-muted ml-1">(<?= count($liste) ?>)</small></h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool js-ajouter" data-toggle="modal"
                                    data-target="#modal-ressource" data-categorie="<?= $cat ?>"
                                    title="Ajouter dans cette catégorie">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$liste): ?>
                                <p class="text-muted text-center my-3 small"><?= $def['aide'] ?> — aucun fichier pour le moment.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-valign-middle mb-0">
                                        <tbody>
                                            <?php foreach ($liste as $i => $r):
                                                $vis = $visibilites[$r->visibilite]; ?>
                                                <tr class="<?= $r->est_publie ? '' : 'text-muted' ?>">
                                                    <td style="width:44px" class="text-center"><i
                                                            class="<?= $iconeFichier($r->fichier_extension) ?> fa-2x"></i></td>
                                                    <td>
                                                        <strong><?= html_escape($r->titre) ?></strong>
                                                        <?php if ($r->version > 1): ?><span class="badge badge-warning ml-1"
                                                                title="Fichier remplacé">v<?= (int) $r->version ?></span><?php endif; ?>
                                                        <?php if (!$r->est_publie): ?><span
                                                                class="badge badge-secondary ml-1">Brouillon</span><?php endif; ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?= html_escape($r->fichier_nom_original) ?> ·
                                                            <?= $taille($r->fichier_taille) ?> ·
                                                            mis à jour le <?= $date($r->updated_at ?: $r->created_at) ?>
                                                        </small>
                                                        <?php if ($r->description): ?><br><small><?= html_escape($r->description) ?></small><?php endif; ?>
                                                    </td>
                                                    <td class="text-nowrap">
                                                        <span class="badge badge-<?= $vis['badge'] ?>" title="<?= $vis['aide'] ?>"><i
                                                                class="<?= $vis['icone'] ?>"></i> <?= $vis['label'] ?></span>
                                                    </td>
                                                    <td class="text-nowrap text-center" title="Téléchargements par les candidats">
                                                        <i class="fas fa-download text-muted"></i> <?= (int) $r->nb_telechargements ?>
                                                    </td>
                                                    <td class="text-right text-nowrap">
                                                        <?php foreach (['haut' => 'fa-arrow-up', 'bas' => 'fa-arrow-down'] as $sens => $fleche):
                                                            $bord = $sens === 'haut' ? $i === 0 : $i === count($liste) - 1; ?>
                                                            <?= form_open('concours-ressources/deplacer', ['class' => 'd-inline']) ?>
                                                            <input type="hidden" name="id" value="<?= (int) $r->id ?>">
                                                            <input type="hidden" name="sens" value="<?= $sens ?>">
                                                            <button type="submit" class="btn btn-xs btn-default"
                                                                <?= $bord ? 'disabled' : '' ?> title="Monter / descendre"><i
                                                                    class="fas <?= $fleche ?>"></i></button>
                                                            <?= form_close() ?>
                                                        <?php endforeach; ?>
                                                        <a href="<?= site_url('concours-ressources/telecharger/' . (int) $r->id) ?>"
                                                            class="btn btn-xs btn-outline-success" title="Télécharger"><i
                                                                class="fas fa-download"></i></a>
                                                        <button type="button" class="btn btn-xs btn-outline-primary js-modifier"
                                                            data-toggle="modal" data-target="#modal-ressource"
                                                            data-ressource="<?= html_escape(json_encode([
                                                                                'id'          => (int) $r->id,
                                                                                'titre'       => $r->titre,
                                                                                'description' => $r->description,
                                                                                'categorie'   => $r->categorie,
                                                                                'visibilite'  => $r->visibilite,
                                                                                'est_publie'  => (int) $r->est_publie,
                                                                                'fichier'     => $r->fichier_nom_original . ' (' . $taille($r->fichier_taille) . ', v' . (int) $r->version . ')',
                                                                            ])) ?>" title="Modifier"><i
                                                                class="fas fa-pen"></i></button>
                                                        <?= form_open('concours-ressources/supprimer', ['class' => 'd-inline', 'onsubmit' => "return confirm('Supprimer définitivement cette ressource et son fichier ?');"]) ?>
                                                        <input type="hidden" name="id" value="<?= (int) $r->id ?>">
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Supprimer"><i
                                                                class="fas fa-trash"></i></button>
                                                        <?= form_close() ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- ============ MODAL RESSOURCE ============ -->
                <div class="modal fade" id="modal-ressource" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <?= form_open_multipart('concours-ressources/enregistrer', ['class' => 'modal-content', 'id' => 'form-ressource']) ?>
                        <div class="modal-header">
                            <h5 class="modal-title" id="modal-ressource-titre">Ajouter une ressource</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="res_id" value="">

                            <div class="row">
                                <div class="form-group col-md-7">
                                    <label for="res_titre">Titre <span class="text-danger">*</span></label>
                                    <input type="text" name="titre" id="res_titre" maxlength="150" class="form-control"
                                        required placeholder="Ex. Brief d'architecture — Centre culturel">
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="res_categorie">Catégorie <span class="text-danger">*</span></label>
                                    <select name="categorie" id="res_categorie" class="form-control">
                                        <?php foreach ($categories as $cle => $def): ?>
                                            <option value="<?= $cle ?>"><?= $def['label'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="res_description">Description</label>
                                <textarea name="description" id="res_description" rows="2" maxlength="1000"
                                    class="form-control"
                                    placeholder="Contenu du fichier, consignes d'utilisation…"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="d-block">Qui peut télécharger ce fichier ? <span
                                        class="text-danger">*</span></label>
                                <?php foreach ($visibilites as $cle => $vis): ?>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="res_vis_<?= $cle ?>"
                                            name="visibilite" value="<?= $cle ?>" <?= $cle === 'valides' ? 'checked' : '' ?>>
                                        <label class="custom-control-label font-weight-normal" for="res_vis_<?= $cle ?>">
                                            <i
                                                class="<?= $vis['icone'] ?> text-<?= $vis['badge'] ?> mr-1"></i><strong><?= $vis['label'] ?></strong>
                                            — <?= $vis['aide'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="form-group">
                                <label for="res_fichier">Fichier <span class="text-danger"
                                        id="res_fichier_requis">*</span></label>
                                <div id="res_fichier_actuel" class="alert alert-light border py-2 small d-none"></div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="res_fichier" name="fichier"
                                        accept="<?= '.' . implode(',.', $extensions) ?>"
                                        data-max-mo="<?= (int) $taille_max ?>">
                                    <label class="custom-file-label" for="res_fichier">Choisir un fichier…</label>
                                </div>
                                <small class="form-text text-muted">
                                    <?= implode(', ', array_map('strtoupper', $extensions)) ?> — <?= (int) $taille_max ?> Mo
                                    maximum.
                                    Pour plusieurs fichiers (plans, photos), regroupez-les dans un ZIP.
                                </small>
                                <small id="res_fichier_erreur" class="form-text text-danger d-none"></small>
                            </div>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="res_publie" name="est_publie"
                                    value="1" checked>
                                <label class="custom-control-label" for="res_publie">Publier (sinon, reste en brouillon et
                                    invisible des candidats)</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success" id="res_envoyer"><i class="fas fa-save mr-1"></i>
                                Enregistrer</button>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var f = function(id) {
            return document.getElementById(id);
        };
        if (!f('res_id')) return;

        var fichier = f('res_fichier'),
            label = document.querySelector('label[for="res_fichier"]');
        var erreur = f('res_fichier_erreur'),
            actuel = f('res_fichier_actuel'),
            requis = f('res_fichier_requis');

        function remplir(r) {
            f('res_id').value = r.id || '';
            f('res_titre').value = r.titre || '';
            f('res_description').value = r.description || '';
            f('res_categorie').value = r.categorie || 'brief';
            f('res_publie').checked = r.est_publie === undefined ? true : !!r.est_publie;
            var vis = f('res_vis_' + (r.visibilite || 'valides'));
            if (vis) vis.checked = true;

            fichier.value = '';
            erreur.classList.add('d-none');
            fichier.required = !r.id;
            requis.style.display = r.id ? 'none' : '';

            if (r.id) {
                actuel.innerHTML = '<i class="fas fa-paperclip mr-1"></i> Fichier actuel : <strong></strong><br>' +
                    '<span class="text-muted">Choisissez un nouveau fichier uniquement pour le remplacer (une nouvelle version sera créée).</span>';
                actuel.querySelector('strong').textContent = r.fichier;
                actuel.classList.remove('d-none');
                label.textContent = 'Remplacer le fichier…';
            } else {
                actuel.classList.add('d-none');
                label.textContent = 'Choisir un fichier…';
            }
            f('modal-ressource-titre').textContent = r.id ? 'Modifier la ressource' : 'Ajouter une ressource';
        }

        document.querySelectorAll('.js-ajouter').forEach(function(btn) {
            btn.addEventListener('click', function() {
                remplir({
                    categorie: btn.dataset.categorie
                });
            });
        });
        document.querySelectorAll('.js-modifier').forEach(function(btn) {
            btn.addEventListener('click', function() {
                try {
                    remplir(JSON.parse(btn.dataset.ressource));
                } catch (e) {
                    remplir({});
                }
            });
        });

        // Contrôle de taille avant l'envoi : évite d'attendre un long upload refusé
        fichier.addEventListener('change', function() {
            erreur.classList.add('d-none');
            if (!this.files.length) return;
            var fic = this.files[0],
                max = parseInt(this.dataset.maxMo, 10) * 1048576;
            label.textContent = fic.name;
            if (max && fic.size > max) {
                erreur.textContent = 'Ce fichier dépasse la taille maximale (' + this.dataset.maxMo +
                    ' Mo).';
                erreur.classList.remove('d-none');
                this.value = '';
                label.textContent = 'Choisir un fichier…';
            }
        });

        // Retour visuel pendant l'envoi des gros fichiers
        f('form-ressource').addEventListener('submit', function() {
            var btn = f('res_envoyer');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Envoi en cours…';
        });
    });
</script>