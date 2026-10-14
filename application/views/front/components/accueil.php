<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Page d'accueil publique.
 * Variables : $concours, $phase, $inscriptionsOuvertes, $soumissionsOuvertes,
 *             $calendrier, $prix, $totauxPrix, $documents, $banniere, $logo
 */
$c          = $concours;
$prochaine  = $phase['prochaine'] ?? null;
$objectifs  = lignes($c->objectifs);
$criteres   = lignes($c->criteres_eligibilite);
$reglement  = $documents['reglement'] ?? null;
$guide      = $documents['guide'] ?? null;

// Appel à l'action selon la phase du concours (heure serveur)
switch ($phase['code']) {
    case 'a_venir':
        $cta = [
            'titre'  => 'Les inscriptions ouvrent le ' . date_fr($c->ouverture_inscriptions),
            'texte'  => 'Lisez dès maintenant le règlement pour préparer votre candidature.',
            'bouton' => $reglement ? ['Lire le règlement', $reglement, true] : ['Voir le calendrier', '#calendrier', false],
        ];
        break;
    case 'inscriptions':
        $cta = [
            'titre'  => 'Les inscriptions sont ouvertes',
            'texte'  => 'Créez votre compte avant le ' . date_fr($c->cloture_inscriptions) . ', puis déposez votre projet depuis votre espace candidat.',
            'bouton' => ['Créer mon compte', site_url('inscription'), false],
        ];
        break;
    case 'soumissions':
        $cta = [
            'titre'  => 'Les inscriptions sont closes',
            'texte'  => 'Les candidats inscrits peuvent déposer ou modifier leur projet jusqu\'au ' . date_fr($c->date_limite_soumission) . '.',
            'bouton' => ['Déposer mon projet', site_url('connexion'), false],
        ];
        break;
    case 'deliberation':
        $cta = [
            'titre'  => 'Les dépôts sont clos, le jury délibère',
            'texte'  => !empty($c->date_resultats)
                ? 'Les résultats seront proclamés le ' . date_fr($c->date_resultats) . '.'
                : 'Les résultats seront annoncés sur cette page.',
            'bouton' => ['Accéder à mon espace', site_url('connexion'), false],
        ];
        break;
    case 'termine':
        $cta = [
            'titre'  => 'Les résultats ont été proclamés',
            'texte'  => 'Merci à toutes les équipes qui ont participé à cette édition.',
            'bouton' => ['Accéder à mon espace', site_url('connexion'), false],
        ];
        break;
    default:
        $cta = [
            'titre'  => 'Le calendrier sera bientôt publié',
            'texte'  => 'Revenez sur cette page pour connaître les dates d\'inscription.',
            'bouton' => null,
        ];
}

$participation = ConcoursM::MODES[$c->mode_participation] ?? '';
if ($c->mode_participation !== 'individuel') {
    $participation .= ' (équipes de ' . (int) $c->taille_equipe_min . ' à ' . (int) $c->taille_equipe_max . ' personnes)';
}
?>

<!-- ═══ En-tête du concours ═══════════════════════════════════════ -->
<section class="hero" aria-labelledby="hero-titre">
    <div class="wrap hero__grid">
        <div class="hero__texte">
            <p class="hero__surtitre">
                Concours d'architecture<?= !empty($c->edition) ? ', édition ' . html_escape($c->edition) : '' ?>
            </p>
            <h1 class="hero__titre" id="hero-titre"><?= html_escape($c->titre) ?></h1>
            <?php if (!empty($c->sous_titre)) : ?>
            <p class="hero__chapo"><?= html_escape($c->sous_titre) ?></p>
            <?php endif; ?>

            <div class="hero__actions">
                <?php if ($inscriptionsOuvertes) : ?>
                <a class="btn btn--vert" href="<?= site_url('inscription') ?>">Créer mon compte</a>
                <?php elseif ($soumissionsOuvertes) : ?>
                <a class="btn btn--vert" href="<?= site_url('connexion') ?>">Déposer mon projet</a>
                <?php endif; ?>
                <?php if ($reglement) : ?>
                <a class="btn btn--trait" href="<?= $reglement ?>" target="_blank" rel="noopener">
                    <i class="far fa-file-pdf" aria-hidden="true"></i> Lire le règlement
                </a>
                <?php else : ?>
                <a class="btn btn--trait" href="#concours">Découvrir le concours</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cartouche : comme sur une planche d'architecte -->
        <aside class="cartouche" aria-label="Fiche du concours">
            <div class="cartouche__ligne cartouche__ligne--tete">
                <?php if ($logo) : ?>
                <img class="cartouche__logo" src="<?= $logo ?>" alt="" width="44" height="44">
                <?php endif; ?>
                <div>
                    <span class="cartouche__label">Maître d'ouvrage</span>
                    <span class="cartouche__valeur">SATRACO Construction</span>
                </div>
            </div>
            <div class="cartouche__ligne cartouche__ligne--double">
                <div>
                    <span class="cartouche__label">Édition</span>
                    <span class="cartouche__valeur"><?= html_escape($c->edition ?: '—') ?></span>
                </div>
                <div>
                    <span class="cartouche__label">Ouvert aux</span>
                    <span
                        class="cartouche__valeur"><?= html_escape(ConcoursM::PUBLICS[$c->public_cible] ?? '') ?></span>
                </div>
            </div>
            <div class="cartouche__ligne">
                <span class="cartouche__label">Phase en cours</span>
                <span class="cartouche__valeur">
                    <span class="pastille pastille--<?= html_escape($phase['code']) ?>" aria-hidden="true"></span>
                    <?= html_escape($phase['label']) ?>
                </span>
            </div>

            <?php if ($prochaine && $prochaine['secondes'] > 0) : ?>
            <div class="cartouche__ligne cartouche__ligne--compte">
                <span class="cartouche__label"><?= html_escape($prochaine['label']) ?></span>
                <div class="compte" data-secondes="<?= (int) $prochaine['secondes'] ?>" role="timer" aria-live="off">
                    <span class="compte__bloc"><b data-unite="j">–</b><small>jours</small></span>
                    <span class="compte__bloc"><b data-unite="h">–</b><small>heures</small></span>
                    <span class="compte__bloc"><b data-unite="m">–</b><small>min</small></span>
                    <span class="compte__bloc"><b data-unite="s">–</b><small>s</small></span>
                </div>
                <span class="cartouche__date"><?= html_escape(date_fr($prochaine['date'])) ?></span>
                <p class="compte__fin" hidden>Échéance atteinte. <a href="<?= current_url() ?>">Actualisez la page</a>.
                </p>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php if ($banniere) : ?>
<figure class="planche wrap">
    <img src="<?= $banniere ?>" alt="Illustration du concours <?= html_escape($c->titre) ?>" loading="lazy">
</figure>
<?php endif; ?>

<!-- ═══ Le concours ═════════════════════════════════════════════════ -->
<section class="section" id="concours" aria-labelledby="concours-titre">
    <div class="wrap section__grid">
        <div class="section__principal">
            <h2 class="section__titre" id="concours-titre">Le concours</h2>
            <div class="texte">
                <?= $c->description ? paragraphes($c->description) : '<p>La présentation détaillée du concours sera bientôt publiée.</p>' ?>
            </div>
        </div>
        <?php if ($objectifs) : ?>
        <div class="section__annexe">
            <h3 class="sous-titre">Objectifs</h3>
            <ul class="liste-trait">
                <?php foreach ($objectifs as $o) : ?>
                <li><?= html_escape($o) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══ Participer ══════════════════════════════════════════════════ -->
<section class="section section--teinte" id="participer" aria-labelledby="participer-titre">
    <div class="wrap section__grid">
        <div class="section__principal">
            <h2 class="section__titre" id="participer-titre">Qui peut participer&nbsp;?</h2>
            <?php if ($criteres) : ?>
            <ul class="liste-coche">
                <?php foreach ($criteres as $cr) : ?>
                <li><?= html_escape($cr) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php else : ?>
            <p class="texte">Les critères d'éligibilité sont détaillés dans le règlement du concours.</p>
            <?php endif; ?>
        </div>
        <dl class="section__annexe fiche">
            <div>
                <dt>Public</dt>
                <dd><?= html_escape(ConcoursM::PUBLICS[$c->public_cible] ?? '') ?></dd>
            </div>
            <div>
                <dt>Participation</dt>
                <dd><?= html_escape($participation) ?></dd>
            </div>
            <div>
                <dt>Livrables acceptés</dt>
                <dd>
                    <?= html_escape(strtoupper(str_replace(',', ', ', $c->extensions_autorisees))) ?>,
                    <?= (int) $c->taille_max_fichier_mo ?>&nbsp;Mo maximum par fichier
                </dd>
            </div>
            <div>
                <dt>Validation du compte</dt>
                <dd><?= html_escape(ConcoursM::VALIDATIONS[$c->mode_validation_compte] ?? '') ?></dd>
            </div>
        </dl>
    </div>
</section>

<!-- ═══ Calendrier ══════════════════════════════════════════════════ -->
<?php if ($calendrier) : ?>
<section class="section" id="calendrier" aria-labelledby="calendrier-titre">
    <div class="wrap">
        <h2 class="section__titre" id="calendrier-titre">Calendrier</h2>
        <p class="section__intro">Toutes les heures sont celles de Bujumbura. Les dépôts sont fermés automatiquement à
            la date limite.</p>

        <ol class="calendrier">
            <?php foreach ($calendrier as $e) : ?>
            <li
                class="etape etape--<?= $e['etat'] ?><?= $e['prochaine'] ? ' etape--prochaine' : '' ?><?= $e['reglementaire'] ? ' etape--officielle' : '' ?>">
                <time class="etape__date" datetime="<?= html_escape(date('c', strtotime($e['debut']))) ?>">
                    <?= html_escape(date_fr($e['debut'], $e['reglementaire'] ? true : 'auto', true)) ?>
                    <?php if ($e['fin']) : ?>
                    <span class="etape__fin">au <?= html_escape(date_fr($e['fin'], 'auto', true)) ?></span>
                    <?php endif; ?>
                </time>
                <div class="etape__corps">
                    <h3 class="etape__titre">
                        <i class="<?= html_escape($e['icone']) ?>" aria-hidden="true"></i>
                        <?= html_escape($e['libelle']) ?>
                    </h3>
                    <?php if ($e['description']) : ?>
                    <p><?= html_escape($e['description']) ?></p>
                    <?php endif; ?>
                    <?php if ($e['etat'] === 'en_cours') : ?>
                    <span class="etiquette etiquette--encours">En cours</span>
                    <?php elseif ($e['prochaine']) : ?>
                    <span class="etiquette">Prochaine étape</span>
                    <?php elseif ($e['etat'] === 'passe') : ?>
                    <span class="sr-only">(étape passée)</span>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>
<?php endif; ?>

<!-- ═══ Prix ════════════════════════════════════════════════════════ -->
<?php if ($prix) : ?>
<section class="section section--teinte" id="prix" aria-labelledby="prix-titre">
    <div class="wrap">
        <div class="section__entete">
            <h2 class="section__titre" id="prix-titre">Prix à gagner</h2>
            <?php if (!empty($totauxPrix['dotation'])) : ?>
            <p class="dotation">
                Dotation totale
                <?php foreach ($totauxPrix['dotation'] as $devise => $total) : ?>
                <strong><?= html_escape(montant_fr($total, $devise)) ?></strong>
                <?php endforeach; ?>
            </p>
            <?php endif; ?>
        </div>

        <ul class="prix-liste">
            <?php foreach ($prix as $p) : ?>
            <li class="prix prix--<?= html_escape($p->categorie) ?>">
                <?php $img = fichier_public($p->image); ?>
                <?php if ($img) : ?>
                <img class="prix__image" src="<?= $img ?>" alt="" loading="lazy">
                <?php endif; ?>
                <div class="prix__corps">
                    <?php if ($p->categorie !== 'principal') : ?>
                    <span class="prix__categorie"><?= html_escape(PrixM::CATEGORIES[$p->categorie] ?? '') ?></span>
                    <?php endif; ?>
                    <h3 class="prix__titre"><?= html_escape($p->libelle) ?></h3>
                    <?php if ($p->montant !== null) : ?>
                    <p class="prix__montant">
                        <?= html_escape(montant_fr($p->montant, $p->devise)) ?>
                        <?php if ((int) $p->nombre_laureats > 1) : ?>
                        <small>par lauréat, <?= (int) $p->nombre_laureats ?> lauréats</small>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($p->description) : ?>
                    <div class="prix__description"><?= paragraphes($p->description) ?></div>
                    <?php endif; ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- ═══ Documents ═══════════════════════════════════════════════════ -->
<?php if ($documents) : ?>
<section class="section" id="documents" aria-labelledby="documents-titre">
    <div class="wrap">
        <h2 class="section__titre" id="documents-titre">Documents officiels</h2>
        <ul class="documents">
            <?php if ($reglement) : ?>
            <li>
                <a class="document" href="<?= $reglement ?>" target="_blank" rel="noopener">
                    <i class="far fa-file-pdf" aria-hidden="true"></i>
                    <span>
                        <strong>Règlement du concours</strong>
                        <small>Conditions de participation, livrables et critères d'évaluation
                            (PDF<?= is_file(FCPATH . $c->reglement_pdf) ? ', ' . taille_fichier(filesize(FCPATH . $c->reglement_pdf)) : '' ?>)</small>
                    </span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($guide) : ?>
            <li>
                <a class="document" href="<?= $guide ?>" target="_blank" rel="noopener">
                    <i class="far fa-file-pdf" aria-hidden="true"></i>
                    <span>
                        <strong>Guide de participation</strong>
                        <small>Les étapes pour s'inscrire et déposer son projet
                            (PDF<?= is_file(FCPATH . $c->guide_pdf) ? ', ' . taille_fichier(filesize(FCPATH . $c->guide_pdf)) : '' ?>)</small>
                    </span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <p class="section__note">Le brief d'architecture et les fichiers de base sont disponibles dans l'espace candidat
            après inscription.</p>
    </div>
</section>
<?php endif; ?>

<!-- ═══ Appel à l'action ════════════════════════════════════════════ -->
<section class="appel" aria-labelledby="appel-titre">
    <div class="wrap appel__inner">
        <div>
            <h2 class="appel__titre" id="appel-titre"><?= html_escape($cta['titre']) ?></h2>
            <p><?= html_escape($cta['texte']) ?></p>
        </div>
        <?php if ($cta['bouton']) : ?>
        <a class="btn btn--sable" href="<?= html_escape($cta['bouton'][1]) ?>"
            <?= $cta['bouton'][2] ? 'target="_blank" rel="noopener"' : '' ?>>
            <?= html_escape($cta['bouton'][0]) ?>
        </a>
        <?php endif; ?>
    </div>
</section>