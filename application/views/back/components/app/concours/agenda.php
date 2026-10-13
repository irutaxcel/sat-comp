<?php

/** @var object|null $concours */
$mois = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];

// "2026-11-05 08:00:00" → "05 nov. 2026 à 08:00"
$fmt = function ($dt, $avecHeure = true) use ($mois) {
    if (empty($dt)) return '—';
    $ts = strtotime($dt);
    $s  = date('d', $ts) . ' ' . $mois[date('n', $ts) - 1] . ' ' . date('Y', $ts);
    return $avecHeure ? $s . ' à ' . date('H:i', $ts) : $s;
};
// Valeur pour <input type="datetime-local">
$local = function ($dt) {
    return empty($dt) ? '' : date('Y-m-d\TH:i', strtotime($dt));
};
$err = function ($champ) {
    return form_error($champ) ? ' is-invalid' : '';
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
                <p>Le calendrier se rattache à un concours. Commencez par le créer.</p>
                <a href="<?= site_url('concours-parametres') ?>" class="btn btn-success btn-sm"><i
                        class="fas fa-sliders-h mr-1"></i> Aller aux paramètres</a>
            </div>
            <?php else: ?>

            <?php if (validation_errors()): ?>
            <div class="alert alert-danger">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Les dates ne sont pas cohérentes</h5>
                <?= validation_errors('<div>• ', '</div>') ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- ================= COLONNE PRINCIPALE ================= -->
                <div class="col-lg-8">

                    <!-- Dates réglementaires -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-lock mr-1"></i> Dates réglementaires</h3>
                            <div class="card-tools">
                                <span class="badge badge-light"
                                    title="Toutes les échéances sont contrôlées avec l'heure du serveur">
                                    <i class="far fa-clock"></i> Heure serveur : <?= date('d/m/Y H:i', $maintenant) ?>
                                    (<?= date_default_timezone_get() ?>)
                                </span>
                            </div>
                        </div>
                        <?= form_open('concours-agenda/dates', ['novalidate' => 'novalidate']) ?>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Ces dates pilotent le <strong>verrouillage automatique</strong> de la plateforme :
                                plus d'inscription après la clôture, plus de dépôt ni de modification de projet après la
                                date limite.
                            </p>
                            <div class="row">
                                <?php foreach ($dates_cles as $champ => $def): ?>
                                <div class="form-group col-md-6">
                                    <label for="<?= $champ ?>">
                                        <i class="<?= $def['icone'] ?> text-<?= $def['couleur'] ?> mr-1"></i>
                                        <?= $def['label'] ?>
                                        <?php if ($def['requis']): ?><span
                                            class="text-danger">*</span><?php else: ?><small
                                            class="text-muted">(facultatif)</small><?php endif; ?>
                                    </label>
                                    <input type="datetime-local" name="<?= $champ ?>" id="<?= $champ ?>"
                                        class="form-control<?= $err($champ) ?>"
                                        value="<?= set_value($champ, $local($concours->$champ ?? null)) ?>">
                                    <?= form_error($champ) ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="callout callout-info small mb-0 py-2">
                                Ordre attendu : ouverture &lt; clôture des inscriptions ≤ date limite de soumission &lt;
                                résultats.
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Enregistrer
                                les dates</button>
                        </div>
                        <?= form_close() ?>
                    </div>

                    <!-- Étapes complémentaires -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="far fa-calendar-alt mr-1"></i> Autres étapes de l'agenda
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-success js-ajouter" data-toggle="modal"
                                    data-target="#modal-etape">
                                    <i class="fas fa-plus mr-1"></i> Ajouter une étape
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!$evenements): ?>
                            <p class="text-muted text-center my-4">
                                Aucune étape complémentaire.<br>
                                <small>Ex. séance d'information, visite du site, cérémonie de remise des prix…</small>
                            </p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-valign-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Étape</th>
                                            <th>Date</th>
                                            <th class="text-center">Visibilité</th>
                                            <th class="text-right" style="width:110px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($evenements as $e): ?>
                                        <tr
                                            class="<?= strtotime($e->date_fin ?: $e->date_debut) < $maintenant ? 'text-muted' : '' ?>">
                                            <td>
                                                <i class="<?= html_escape($e->icone) ?> mr-2"></i>
                                                <strong><?= html_escape($e->libelle) ?></strong>
                                                <?php if ($e->description): ?><br><small
                                                    class="text-muted"><?= html_escape($e->description) ?></small><?php endif; ?>
                                            </td>
                                            <td class="text-nowrap">
                                                <?= $fmt($e->date_debut) ?>
                                                <?php if ($e->date_fin): ?><br><small>au
                                                    <?= $fmt($e->date_fin) ?></small><?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= $e->est_public
                                                                ? '<span class="badge badge-success"><i class="fas fa-globe"></i> Public</span>'
                                                                : '<span class="badge badge-secondary"><i class="fas fa-eye-slash"></i> Interne</span>' ?>
                                            </td>
                                            <td class="text-right text-nowrap">
                                                <button type="button" class="btn btn-xs btn-outline-primary js-modifier"
                                                    data-toggle="modal" data-target="#modal-etape" data-etape="<?= html_escape(json_encode([
                                                                                'id'          => (int) $e->id,
                                                                                'libelle'     => $e->libelle,
                                                                                'description' => $e->description,
                                                                                'date_debut'  => $local($e->date_debut),
                                                                                'date_fin'    => $local($e->date_fin),
                                                                                'icone'       => $e->icone,
                                                                                'est_public'  => (int) $e->est_public,
                                                                            ])) ?>" title="Modifier">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <?= form_open('concours-agenda/evenement/supprimer', ['class' => 'd-inline', 'onsubmit' => "return confirm('Supprimer cette étape ?');"]) ?>
                                                <input type="hidden" name="id" value="<?= (int) $e->id ?>">
                                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                                    title="Supprimer"><i class="fas fa-trash"></i></button>
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
                </div>

                <!-- ================= COLONNE LATÉRALE ================= -->
                <div class="col-lg-4">

                    <!-- Phase en cours + compte à rebours -->
                    <div class="card card-outline card-<?= $phase['couleur'] ?>">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-signal mr-1"></i> Phase en cours</h3>
                        </div>
                        <div class="card-body text-center">
                            <span class="badge badge-<?= $phase['couleur'] ?> p-2 mb-3"
                                style="font-size:.95rem"><?= $phase['label'] ?></span>

                            <?php if ($concours->statut !== 'publie'): ?>
                            <p class="small text-warning mb-2"><i class="fas fa-exclamation-triangle"></i> Le concours
                                n'est pas publié : inscriptions et soumissions restent fermées quelles que soient les
                                dates.</p>
                            <?php endif; ?>

                            <?php if (!empty($phase['prochaine'])): ?>
                            <p class="text-muted mb-1">Prochaine échéance</p>
                            <h5 class="mb-1"><?= $phase['prochaine']['label'] ?></h5>
                            <p class="small text-muted mb-2"><?= $fmt($phase['prochaine']['date']) ?></p>
                            <div class="h4 font-weight-bold text-<?= $phase['couleur'] ?> js-compte-rebours"
                                data-secondes="<?= (int) $phase['prochaine']['secondes'] ?>">…</div>
                            <?php elseif ($phase['code'] === 'non_planifie'): ?>
                            <p class="small text-muted mb-0">Renseignez les trois dates obligatoires pour activer le
                                calendrier.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Chronologie -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-stream mr-1"></i> Chronologie</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!$chronologie): ?>
                            <p class="text-muted text-center mb-0">Aucune date planifiée.</p>
                            <?php else: ?>
                            <div class="timeline timeline-inverse mb-0">
                                <?php foreach ($chronologie as $item):
                                            $passe = strtotime($item['date_fin'] ?: $item['date']) < $maintenant; ?>
                                <div style="<?= $passe ? 'opacity:.55' : '' ?>">
                                    <i class="<?= html_escape($item['icone']) ?> bg-<?= $item['couleur'] ?>"></i>
                                    <div class="timeline-item">
                                        <span class="time"><i class="far fa-clock"></i>
                                            <?= date('H:i', strtotime($item['date'])) ?></span>
                                        <h3 class="timeline-header">
                                            <strong><?= html_escape($item['libelle']) ?></strong>
                                            <?php if ($item['reglementaire']): ?><i
                                                class="fas fa-lock text-muted small ml-1"
                                                title="Date réglementaire"></i><?php endif; ?>
                                            <?php if (!$item['est_public']): ?><i
                                                class="fas fa-eye-slash text-muted small ml-1"
                                                title="Interne"></i><?php endif; ?>
                                        </h3>
                                        <div class="timeline-body small py-2">
                                            <?= $fmt($item['date'], false) ?>
                                            <?php if ($item['date_fin']): ?> →
                                            <?= $fmt($item['date_fin'], false) ?><?php endif; ?>
                                            <?php if ($item['description']): ?><br><span
                                                class="text-muted"><?= html_escape($item['description']) ?></span><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div><i class="far fa-flag bg-gray"></i></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= MODAL ÉTAPE ================= -->
            <div class="modal fade" id="modal-etape" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <?= form_open('concours-agenda/evenement', ['class' => 'modal-content', 'id' => 'form-etape']) ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-etape-titre">Ajouter une étape</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="etape_id" value="">
                        <div class="form-group">
                            <label for="etape_libelle">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" id="etape_libelle" maxlength="150" class="form-control"
                                required placeholder="Ex. Séance d'information des candidats">
                        </div>
                        <div class="form-group">
                            <label for="etape_icone">Type d'étape</label>
                            <select name="icone" id="etape_icone" class="form-control">
                                <?php foreach ($icones as $classe => $label): ?>
                                <option value="<?= $classe ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="etape_debut">Début <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="date_debut" id="etape_debut" class="form-control"
                                    required>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="etape_fin">Fin <small class="text-muted">(facultatif)</small></label>
                                <input type="datetime-local" name="date_fin" id="etape_fin" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="etape_description">Description</label>
                            <input type="text" name="description" id="etape_description" maxlength="255"
                                class="form-control" placeholder="Lieu, précisions…">
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="etape_public" name="est_public"
                                value="1" checked>
                            <label class="custom-control-label" for="etape_public">Afficher sur l'agenda public</label>
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
    // ----- Modal : ajout / modification d'une étape -----
    var champs = {
        id: document.getElementById('etape_id'),
        libelle: document.getElementById('etape_libelle'),
        description: document.getElementById('etape_description'),
        date_debut: document.getElementById('etape_debut'),
        date_fin: document.getElementById('etape_fin'),
        icone: document.getElementById('etape_icone'),
        est_public: document.getElementById('etape_public')
    };
    var titre = document.getElementById('modal-etape-titre');

    function remplir(e) {
        if (!champs.id) return;
        champs.id.value = e.id || '';
        champs.libelle.value = e.libelle || '';
        champs.description.value = e.description || '';
        champs.date_debut.value = e.date_debut || '';
        champs.date_fin.value = e.date_fin || '';
        champs.icone.value = e.icone || 'far fa-calendar';
        champs.est_public.checked = e.est_public === undefined ? true : !!e.est_public;
        titre.textContent = e.id ? "Modifier l'étape" : 'Ajouter une étape';
    }

    document.querySelectorAll('.js-ajouter').forEach(function(btn) {
        btn.addEventListener('click', function() {
            remplir({});
        });
    });
    document.querySelectorAll('.js-modifier').forEach(function(btn) {
        btn.addEventListener('click', function() {
            try {
                remplir(JSON.parse(btn.dataset.etape));
            } catch (err) {
                remplir({});
            }
        });
    });

    // ----- Compte à rebours (basé sur l'écart calculé par le serveur) -----
    var rebours = document.querySelector('.js-compte-rebours');
    if (rebours) {
        var restant = parseInt(rebours.dataset.secondes, 10) || 0;
        var finVisee = Date.now() + restant * 1000; // insensible à l'heure du poste client

        var tic = function() {
            var s = Math.max(0, Math.round((finVisee - Date.now()) / 1000));
            if (s === 0) {
                rebours.textContent = 'Échéance atteinte';
                setTimeout(function() {
                    location.reload();
                }, 3000);
                return;
            }
            var j = Math.floor(s / 86400),
                h = Math.floor(s % 86400 / 3600),
                m = Math.floor(s % 3600 / 60),
                sec = s % 60;
            rebours.textContent = (j ? j + 'j ' : '') + h + 'h ' + ('0' + m).slice(-2) + 'm ' + ('0' + sec)
                .slice(-2) + 's';
            setTimeout(tic, 1000);
        };
        tic();
    }
});
</script>