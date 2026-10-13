<?php

/** @var object|null $concours */
// 1 500 000 BIF / 2 500,00 USD
$montant = function ($valeur, $devise) {
    if ($valeur === null || $valeur === '') return null;
    $decimales = ($devise === 'BIF' || floor($valeur) == $valeur) ? 0 : 2;
    return number_format((float) $valeur, $decimales, ',', ' ') . ' ' . $devise;
};
// Style "podium" pour les 3 premiers rangs
$podium = [1 => ['#d4a017', 'fa-trophy', 'Or'], 2 => ['#9ea7ad', 'fa-medal', 'Argent'], 3 => ['#b0702b', 'fa-medal', 'Bronze']];
$badgeCategorie = ['principal' => 'success', 'special' => 'info', 'mention' => 'secondary'];
$prixVerrouilles = $phase && in_array($phase['code'], ['deliberation', 'termine'], true);
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
                <p>Les prix se rattachent à un concours. Commencez par le créer.</p>
                <a href="<?= site_url('concours-parametres') ?>" class="btn btn-success btn-sm"><i
                        class="fas fa-sliders-h mr-1"></i> Aller aux paramètres</a>
            </div>
            <?php else: ?>

            <?php if ($prixVerrouilles): ?>
            <div class="callout callout-warning">
                <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                Les soumissions sont closes : les candidats ont concouru pour ces prix. Évitez de les modifier, sauf
                correction.
            </div>
            <?php endif; ?>

            <!-- ============ CHIFFRES CLÉS ============ -->
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-trophy"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Prix</span>
                            <span class="info-box-number"><?= $totaux['nb_prix'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-user-graduate"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Lauréats récompensés</span>
                            <span class="info-box-number"><?= $totaux['nb_laureats'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Dotation totale</span>
                            <span class="info-box-number">
                                <?php if (!$totaux['dotation']): ?>
                                —
                                <?php else: ?>
                                <?= implode(' + ', array_map(function ($dev, $total) use ($montant) {
                                            return $montant($total, $dev);
                                        }, array_keys($totaux['dotation']), $totaux['dotation'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============ LISTE DES PRIX ============ -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-award mr-1"></i> Prix du concours</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-success js-ajouter" data-toggle="modal"
                            data-target="#modal-prix">
                            <i class="fas fa-plus mr-1"></i> Ajouter un prix
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!$prix): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-trophy fa-3x mb-3" style="opacity:.3"></i>
                        <p class="mb-1">Aucun prix défini pour le moment.</p>
                        <small>Commencez par le 1er prix, puis ajoutez les prix spéciaux et les mentions.</small>
                    </div>
                    <?php else: ?>
                    <div class="row">
                        <?php foreach ($prix as $p):
                                    $style = ($p->categorie === 'principal' && isset($podium[$p->rang])) ? $podium[$p->rang] : null; ?>
                        <div class="col-xl-4 col-md-6 d-flex">
                            <div class="card w-100 shadow-sm"
                                style="<?= $style ? 'border-top:4px solid ' . $style[0] : '' ?>">
                                <?php if ($p->image): ?>
                                <img src="<?= base_url($p->image) ?>" class="card-img-top" alt=""
                                    style="height:150px;object-fit:cover">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge badge-<?= $badgeCategorie[$p->categorie] ?>">
                                            <?= $categories[$p->categorie] ?><?= $p->categorie === 'principal' ? ' · rang ' . (int) $p->rang : '' ?>
                                        </span>
                                        <?php if (!$p->est_public): ?>
                                        <span class="badge badge-light" title="Non affiché sur le site public"><i
                                                class="fas fa-eye-slash"></i> Interne</span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="mb-1">
                                        <?php if ($style): ?><i class="fas <?= $style[1] ?> mr-1"
                                            style="color:<?= $style[0] ?>"></i><?php endif; ?>
                                        <?= html_escape($p->libelle) ?>
                                    </h5>

                                    <?php if ($p->montant !== null): ?>
                                    <div class="h4 font-weight-bold text-success mb-1">
                                        <?= $montant($p->montant, $p->devise) ?></div>
                                    <?php endif; ?>
                                    <?php if ($p->nombre_laureats > 1): ?>
                                    <small class="text-muted mb-2"><?= (int) $p->nombre_laureats ?>
                                        lauréats<?= $p->montant !== null ? ' — ' . $montant($p->montant, $p->devise) . ' chacun' : '' ?></small>
                                    <?php endif; ?>

                                    <?php if ($p->description): ?>
                                    <p class="small text-muted mb-3"><?= nl2br(html_escape($p->description)) ?></p>
                                    <?php endif; ?>

                                    <div class="mt-auto text-right">
                                        <button type="button" class="btn btn-sm btn-outline-primary js-modifier"
                                            data-toggle="modal" data-target="#modal-prix" data-prix="<?= html_escape(json_encode([
                                                                        'id'              => (int) $p->id,
                                                                        'categorie'       => $p->categorie,
                                                                        'rang'            => $p->rang,
                                                                        'libelle'         => $p->libelle,
                                                                        'montant'         => $p->montant !== null ? (float) $p->montant : '',
                                                                        'devise'          => $p->devise,
                                                                        'nombre_laureats' => (int) $p->nombre_laureats,
                                                                        'description'     => $p->description,
                                                                        'est_public'      => (int) $p->est_public,
                                                                        'image'           => $p->image ? base_url($p->image) : '',
                                                                    ])) ?>">
                                            <i class="fas fa-pen"></i> Modifier
                                        </button>
                                        <?= form_open('concours-prix/supprimer', ['class' => 'd-inline', 'onsubmit' => "return confirm('Supprimer ce prix ?');"]) ?>
                                        <input type="hidden" name="id" value="<?= (int) $p->id ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i
                                                class="fas fa-trash"></i></button>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ============ MODAL PRIX ============ -->
            <div class="modal fade" id="modal-prix" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <?= form_open_multipart('concours-prix/enregistrer', ['class' => 'modal-content']) ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-prix-titre">Ajouter un prix</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="prix_id" value="">

                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="prix_categorie">Catégorie <span class="text-danger">*</span></label>
                                <select name="categorie" id="prix_categorie" class="form-control">
                                    <?php foreach ($categories as $cle => $label): ?>
                                    <option value="<?= $cle ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-2" id="bloc-rang">
                                <label for="prix_rang">Rang <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="20" name="rang" id="prix_rang" class="form-control">
                            </div>
                            <div class="form-group col-md-5">
                                <label for="prix_libelle">Libellé <span class="text-danger">*</span></label>
                                <input type="text" name="libelle" id="prix_libelle" maxlength="150" class="form-control"
                                    required placeholder="Ex. 1er Prix">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="prix_montant">Montant par lauréat</label>
                                <input type="number" min="0" step="any" name="montant" id="prix_montant"
                                    class="form-control" placeholder="Vide si prix non monétaire">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="prix_devise">Devise</label>
                                <select name="devise" id="prix_devise" class="form-control">
                                    <?php foreach ($devises as $cle => $label): ?>
                                    <option value="<?= $cle ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="prix_laureats">Nombre de lauréats</label>
                                <input type="number" min="1" max="50" name="nombre_laureats" id="prix_laureats"
                                    class="form-control" value="1">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prix_description">Avantages / description</label>
                            <textarea name="description" id="prix_description" rows="3" maxlength="2000"
                                class="form-control"
                                placeholder="Ex. Trophée, stage de 3 mois chez SATRACO, publication du projet…"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Image <small class="text-muted">(facultatif — JPG, PNG ou WEBP, 2 Mo
                                    max.)</small></label>
                            <div id="bloc-image-actuelle" class="d-none align-items-center mb-2">
                                <img id="prix_image_apercu" src="" alt="" class="img-thumbnail mr-3"
                                    style="max-height:70px">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="prix_supprimer_image"
                                        name="supprimer_image" value="1">
                                    <label class="custom-control-label text-danger" for="prix_supprimer_image">Supprimer
                                        l'image</label>
                                </div>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="prix_image" name="image"
                                    accept=".jpg,.jpeg,.png,.webp">
                                <label class="custom-file-label" for="prix_image">Choisir une image…</label>
                            </div>
                        </div>

                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="prix_public" name="est_public"
                                value="1" checked>
                            <label class="custom-control-label" for="prix_public">Afficher sur le site public</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>
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
    if (!f('prix_id')) return;

    var categorie = f('prix_categorie'),
        rang = f('prix_rang'),
        blocRang = f('bloc-rang');
    var blocImage = f('bloc-image-actuelle'),
        labelFichier = document.querySelector('label[for="prix_image"]');

    // Le rang n'a de sens que pour un prix principal
    function majRang() {
        var principal = categorie.value === 'principal';
        blocRang.style.display = principal ? '' : 'none';
        rang.required = principal;
    }
    categorie.addEventListener('change', majRang);

    function remplir(p) {
        f('prix_id').value = p.id || '';
        categorie.value = p.categorie || 'principal';
        rang.value = p.rang || '';
        f('prix_libelle').value = p.libelle || '';
        f('prix_montant').value = p.montant === undefined ? '' : p.montant;
        f('prix_devise').value = p.devise || 'BIF';
        f('prix_laureats').value = p.nombre_laureats || 1;
        f('prix_description').value = p.description || '';
        f('prix_public').checked = p.est_public === undefined ? true : !!p.est_public;
        f('prix_supprimer_image').checked = false;
        f('prix_image').value = '';
        labelFichier.textContent = p.image ? 'Remplacer l\'image…' : 'Choisir une image…';

        if (p.image) {
            f('prix_image_apercu').src = p.image;
            blocImage.classList.remove('d-none');
            blocImage.classList.add('d-flex');
        } else {
            blocImage.classList.add('d-none');
            blocImage.classList.remove('d-flex');
        }

        f('modal-prix-titre').textContent = p.id ? 'Modifier le prix' : 'Ajouter un prix';
        majRang();
    }

    document.querySelectorAll('.js-ajouter').forEach(function(btn) {
        btn.addEventListener('click', function() {
            remplir({});
        });
    });
    document.querySelectorAll('.js-modifier').forEach(function(btn) {
        btn.addEventListener('click', function() {
            try {
                remplir(JSON.parse(btn.dataset.prix));
            } catch (e) {
                remplir({});
            }
        });
    });

    f('prix_image').addEventListener('change', function() {
        if (this.files.length) labelFichier.textContent = this.files[0].name;
    });

    majRang();
});
</script>