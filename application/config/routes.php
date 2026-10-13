<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller']    = 'BackOfficeC';
$route['sign-in']               = 'BackOfficeC/signIn';
$route['authenticate']          = 'BackOfficeC/authenticate';
$route['logout']                = 'BackOfficeC/logout';
$route['forgot-password']       = 'BackOfficeC/forgotPassword';
$route['forgot-password/send']  = 'BackOfficeC/sendResetLink';
$route['reset-password/update'] = 'BackOfficeC/updatePassword';
$route['reset-password/(:any)'] = 'BackOfficeC/resetPassword/$1';
$route['dashboard']             = 'DashboardC';


$route['concours-parametres'] = 'ConcoursC/parametres';
$route['concours-parametres/enregistrer']['post'] = 'ConcoursC/enregistrer_parametres';

$route['concours-agenda'] = 'ConcoursC/agenda';
$route['concours-agenda/dates']['post'] = 'ConcoursC/enregistrer_dates';
$route['concours-agenda/evenement']['post'] = 'ConcoursC/enregistrer_evenement';
$route['concours-agenda/evenement/supprimer']['post'] = 'ConcoursC/supprimer_evenement';


$route['concours-prix'] = 'ConcoursC/prix';
$route['concours-prix/enregistrer']['post'] = 'ConcoursC/enregistrer_prix';
$route['concours-prix/supprimer']['post'] = 'ConcoursC/supprimer_prix';

$route['concours-ressources'] = 'ConcoursC/ressources';
$route['concours-ressources/enregistrer']['post'] = 'ConcoursC/enregistrer_ressource';
$route['concours-ressources/supprimer']['post'] = 'ConcoursC/supprimer_ressource';
$route['concours-ressources/deplacer']['post'] = 'ConcoursC/deplacer_ressource';
$route['concours-ressources/telecharger/(:num)'] = 'ConcoursC/telecharger_ressource/$1';

// Candidatures — Comptes à valider
$route['candidats-en-attente']                  = 'CandidatsC/en_attente';
$route['candidats-en-attente/liste']            = 'CandidatsC/liste_en_attente';
$route['candidats-en-attente/detail/(:num)']    = 'CandidatsC/detail/$1';
$route['candidats-en-attente/valider']['post']  = 'CandidatsC/valider';
$route['candidats-en-attente/rejeter']['post']  = 'CandidatsC/rejeter';


// Candidatures — Candidats & équipes
$route['candidats']                = 'CandidatsC/candidats';
$route['candidats/liste']          = 'CandidatsC/liste_candidats';
$route['candidats/detail/(:num)']  = 'CandidatsC/detail/$1';
$route['candidats/statut']['post'] = 'CandidatsC/changer_statut';


// Candidatures — Projets soumis
$route['projets']                     = 'ProjetsC/index';
$route['projets/liste']               = 'ProjetsC/liste';
$route['projets/detail/(:num)']       = 'ProjetsC/detail/$1';
$route['projets/conformite']['post']  = 'ProjetsC/conformite';
$route['projets/relancer']['post']    = 'ProjetsC/relancer';
$route['projets/fichier/(:num)']      = 'ProjetsC/telecharger/$1';
$route['projets/apercu/(:num)']       = 'ProjetsC/apercu/$1';
$route['projets/zip/(:num)']          = 'ProjetsC/zip/$1';


// Candidatures — Exports
$route['exports']             = 'ExportsC/index';
$route['exports/compter']     = 'ExportsC/compter';
$route['exports/historique']  = 'ExportsC/historique';
$route['exports/telecharger'] = 'ExportsC/telecharger';
$route['exports/archive']     = 'ExportsC/archive';


// Évaluation — Critères de notation
$route['evaluation-criteres']                       = 'EvaluationC/criteres';
$route['evaluation-criteres/liste']                 = 'EvaluationC/criteres_liste';
$route['evaluation-criteres/enregistrer']['post']   = 'EvaluationC/criteres_enregistrer';
$route['evaluation-criteres/supprimer']['post']     = 'EvaluationC/criteres_supprimer';
$route['evaluation-criteres/actif']['post']         = 'EvaluationC/criteres_actif';
$route['evaluation-criteres/ordre']['post']         = 'EvaluationC/criteres_ordre';
$route['evaluation-criteres/repartir']['post']      = 'EvaluationC/criteres_repartir';
$route['evaluation-criteres/modele']['post']        = 'EvaluationC/criteres_modele';
$route['evaluation-criteres/verrou']['post']        = 'EvaluationC/criteres_verrou';

// Évaluation — Classement
$route['evaluation-classement']                     = 'EvaluationC/classement';
$route['evaluation-classement/donnees']             = 'EvaluationC/classement_donnees';
$route['evaluation-classement/detail/(:num)']       = 'EvaluationC/classement_detail/$1';
$route['evaluation-classement/figer']['post']       = 'EvaluationC/classement_figer';
$route['evaluation-classement/export']              = 'EvaluationC/classement_export';

// Administration — Administrateurs & jury
$route['utilisateurs']                        = 'AdminC/utilisateurs';
$route['utilisateurs/liste']                  = 'AdminC/liste';
$route['utilisateurs/detail/(:num)']          = 'AdminC/detail/$1';
$route['utilisateurs/enregistrer']['post']    = 'AdminC/enregistrer';
$route['utilisateurs/statut']['post']         = 'AdminC/statut';
$route['utilisateurs/reinitialiser']['post']  = 'AdminC/reinitialiser';
$route['utilisateurs/supprimer']['post']      = 'AdminC/supprimer';

// Administration — Journal d'activités
$route['journal']          = 'AdminC/journal';
$route['journal/liste']    = 'AdminC/journal_liste';
$route['journal/export']   = 'AdminC/journal_export';

// Mon compte — Mon profil (admin et jury)
$route['profil']                        = 'AdminC/profil';
$route['profil/infos']['post']          = 'AdminC/profil_infos';
$route['profil/mot-de-passe']['post']   = 'AdminC/profil_mot_de_passe';
$route['profil/activite']               = 'AdminC/profil_activite';
