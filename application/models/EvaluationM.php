<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Évaluation — grille de notation et paramètres.
 *
 * Tables : criteres_notation, evaluation_parametres, evaluation_notes
 *          (+ soumissions, candidats pour le classement)
 *
 * Règles de la grille :
 *  - la somme des pondérations des critères actifs doit faire 100 % pour être validée ;
 *  - une grille validée est verrouillée : pondérations, barèmes, ajouts, suppressions et
 *    activations sont figés (seuls l'intitulé, la description et l'ordre restent modifiables),
 *    pour que tous les membres du jury notent avec la même grille ;
 *  - la grille ne peut plus être déverrouillée dès qu'une note a été saisie ;
 *  - un critère déjà noté ne peut pas être supprimé (on le désactive).
 */
class EvaluationM extends CI_Model
{
    const T_CRITERES   = 'criteres_notation';
    const T_PARAMETRES = 'evaluation_parametres';
    const T_NOTES      = 'evaluation_notes';

    const NOTE_MAX_PERMIS = [5, 10, 20, 100];

    /** Modèle de grille adapté à un concours d'architecture */
    const MODELE = [
        ['Pertinence du concept', 'Réponse au brief, force et clarté de l\'idée architecturale, lecture du site et du programme.', 25],
        ['Qualité architecturale et spatiale', 'Composition, volumétrie, qualité des espaces, lumière, rapport intérieur / extérieur.', 25],
        ['Fonctionnalité', 'Organisation du programme, circulations, accessibilité, adéquation aux usages.', 20],
        ['Durabilité et innovation', 'Matériaux locaux, confort bioclimatique, sobriété énergétique, solutions innovantes.', 15],
        ['Faisabilité technique et économique', 'Réalisme constructif, cohérence structurelle, maîtrise du coût.', 10],
        ['Qualité de la présentation', 'Lisibilité des planches, clarté de la note descriptive, qualité graphique.', 5],
    ];

    /* ================================================================
     *  LECTURE
     * ================================================================ */

    public function criteres()
    {
        $tn = $this->db->dbprefix(self::T_NOTES);
        return $this->db
            ->select('c.id, c.nom, c.description, c.poids, c.note_max, c.ordre, c.actif, c.updated_at')
            ->select("(SELECT COUNT(*) FROM {$tn} n WHERE n.critere_id = c.id) AS nb_notes", FALSE)
            ->order_by('c.ordre', 'ASC')
            ->order_by('c.id', 'ASC')
            ->get(self::T_CRITERES . ' c')
            ->result_array();
    }

    public function critere($id)
    {
        return $this->db->where('id', (int) $id)->get(self::T_CRITERES)->row_array();
    }

    /** État global de la grille (indicateurs + règles applicables) */
    public function etat()
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS total, SUM(actif = 1) AS actifs, COALESCE(SUM(CASE WHEN actif = 1 THEN poids END), 0) AS somme
               FROM ' . $this->db->dbprefix(self::T_CRITERES)
        )->row_array();

        $nb_notes = (int) $this->db->count_all(self::T_NOTES);
        $verrou   = $this->parametre('grille_verrouillee') === '1';

        return [
            'total'           => (int) $row['total'],
            'actifs'          => (int) $row['actifs'],
            'somme_poids'     => (int) $row['somme'],
            'verrouillee'     => $verrou,
            'verrouillee_le'  => $verrou ? $this->parametre('grille_verrouillee_le') : NULL,
            'nb_notes'        => $nb_notes,
            'projets_notes'   => (int) $this->db->query('SELECT COUNT(DISTINCT soumission_id) AS n FROM ' . $this->db->dbprefix(self::T_NOTES))->row()->n,
            'deverrouillable' => $verrou && $nb_notes === 0,
            'validable'       => !$verrou && (int) $row['actifs'] > 0 && (int) $row['somme'] === 100,
        ];
    }

    public function parametre($cle)
    {
        $r = $this->db->select('valeur')->where('cle', $cle)->get(self::T_PARAMETRES)->row();
        return $r ? $r->valeur : NULL;
    }

    /* ================================================================
     *  ÉCRITURE — chaque méthode renvoie TRUE ou un message d'erreur
     * ================================================================ */

    /**
     * Création (id = 0) ou modification d'un critère.
     * Grille verrouillée : seuls nom et description sont modifiables.
     */
    public function enregistrer($id, array $d)
    {
        $verrou = $this->parametre('grille_verrouillee') === '1';
        $champs = ['nom' => $d['nom'], 'description' => $d['description'] !== '' ? $d['description'] : NULL];

        if ($id) {
            $actuel = $this->critere($id);
            if (!$actuel) {
                return 'Critère introuvable.';
            }
            if ($verrou) {
                if ((int) $actuel['poids'] !== $d['poids'] || (int) $actuel['note_max'] !== $d['note_max'] || (int) $actuel['actif'] !== $d['actif']) {
                    return 'La grille est verrouillée : seuls l\'intitulé et la description peuvent être modifiés.';
                }
            } else {
                if ((int) $actuel['note_max'] !== $d['note_max'] && $this->_nb_notes($id) > 0) {
                    return 'Ce critère a déjà été noté : son barème ne peut plus changer.';
                }
                $champs += ['poids' => $d['poids'], 'note_max' => $d['note_max'], 'actif' => $d['actif']];
            }
            $this->db->where('id', (int) $id)->update(self::T_CRITERES, $champs);
            return TRUE;
        }

        if ($verrou) {
            return 'La grille est verrouillée : impossible d\'ajouter un critère.';
        }
        $champs += [
            'poids'    => $d['poids'],
            'note_max' => $d['note_max'],
            'actif'    => $d['actif'],
            'ordre'    => (int) $this->db->select_max('ordre')->get(self::T_CRITERES)->row()->ordre + 1,
        ];
        $this->db->insert(self::T_CRITERES, $champs);
        return TRUE;
    }

    public function supprimer($id)
    {
        if ($this->parametre('grille_verrouillee') === '1') {
            return 'La grille est verrouillée : impossible de supprimer un critère.';
        }
        if (!$this->critere($id)) {
            return 'Critère introuvable.';
        }
        if ($this->_nb_notes($id) > 0) {
            return 'Ce critère a déjà été utilisé par le jury : désactivez-le plutôt que de le supprimer.';
        }
        $this->db->where('id', (int) $id)->delete(self::T_CRITERES);
        return TRUE;
    }

    public function basculer_actif($id, $actif)
    {
        if ($this->parametre('grille_verrouillee') === '1') {
            return 'La grille est verrouillée.';
        }
        if (!$this->critere($id)) {
            return 'Critère introuvable.';
        }
        $this->db->where('id', (int) $id)->update(self::T_CRITERES, ['actif' => $actif ? 1 : 0]);
        return TRUE;
    }

    /** Ordre d'affichage (autorisé même grille verrouillée : purement visuel) */
    public function reordonner(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $lot = [];
        foreach ($ids as $i => $id) {
            $lot[] = ['id' => $id, 'ordre' => $i + 1];
        }
        if ($lot) {
            $this->db->update_batch(self::T_CRITERES, $lot, 'id');
        }
        return TRUE;
    }

    /** Répartit 100 % à parts égales entre les critères actifs (le reste va aux premiers) */
    public function repartir_equitablement()
    {
        if ($this->parametre('grille_verrouillee') === '1') {
            return 'La grille est verrouillée.';
        }
        $actifs = $this->db->select('id')->where('actif', 1)->order_by('ordre', 'ASC')->order_by('id', 'ASC')
            ->get(self::T_CRITERES)->result_array();
        $n = count($actifs);
        if ($n === 0) {
            return 'Aucun critère actif à pondérer.';
        }
        if ($n > 100) {
            return 'Trop de critères actifs pour une répartition en pourcentages entiers.';
        }

        $base = intdiv(100, $n);
        $reste = 100 % $n;
        $lot = [];
        foreach ($actifs as $i => $c) {
            $lot[] = ['id' => $c['id'], 'poids' => $base + ($i < $reste ? 1 : 0)];
        }
        $this->db->update_batch(self::T_CRITERES, $lot, 'id');
        return TRUE;
    }

    /** Charge le modèle de grille (uniquement si aucun critère n'existe) */
    public function charger_modele()
    {
        if ($this->parametre('grille_verrouillee') === '1') {
            return 'La grille est verrouillée.';
        }
        if ($this->db->count_all(self::T_CRITERES) > 0) {
            return 'La grille contient déjà des critères : le modèle ne peut être chargé que sur une grille vide.';
        }
        $lignes = [];
        foreach (self::MODELE as $i => $m) {
            $lignes[] = ['nom' => $m[0], 'description' => $m[1], 'poids' => $m[2], 'note_max' => 20, 'ordre' => $i + 1, 'actif' => 1];
        }
        $this->db->insert_batch(self::T_CRITERES, $lignes);
        return TRUE;
    }

    public function verrouiller($admin_id)
    {
        $e = $this->etat();
        if ($e['verrouillee']) {
            return 'La grille est déjà validée.';
        }
        if ($e['actifs'] === 0) {
            return 'Ajoutez au moins un critère actif avant de valider la grille.';
        }
        if ($e['somme_poids'] !== 100) {
            return 'La somme des pondérations doit être égale à 100 % (actuellement ' . $e['somme_poids'] . ' %).';
        }
        $this->_poser('grille_verrouillee', '1');
        $this->_poser('grille_verrouillee_le', date('Y-m-d H:i:s'));
        $this->_poser('grille_verrouillee_par', $admin_id ? (string) $admin_id : NULL);
        return TRUE;
    }

    public function deverrouiller()
    {
        if ($this->parametre('grille_verrouillee') !== '1') {
            return 'La grille n\'est pas verrouillée.';
        }
        if ($this->db->count_all(self::T_NOTES) > 0) {
            return 'Des notes ont déjà été saisies par le jury : la grille ne peut plus être modifiée.';
        }
        $this->_poser('grille_verrouillee', '0');
        $this->_poser('grille_verrouillee_le', NULL);
        $this->_poser('grille_verrouillee_par', NULL);
        return TRUE;
    }


    /* ================================================================
     *  CLASSEMENT
     *
     *  Score d'un juré pour un projet = Σ (note / barème × pondération), sur 100,
     *  calculé seulement si le juré a noté TOUS les critères actifs.
     *  Score du projet = moyenne des scores des jurés ayant terminé leur évaluation.
     *  Sont classés : les projets soumis et déclarés conformes, avec au moins
     *  une évaluation complète. Égalité de score (au centième) = rang ex aequo.
     * ================================================================ */

    const ECART_ALERTE = 15;   // écart (points sur 100) entre jurés au-delà duquel on signale une divergence

    /**
     * @return array ['rows' => [...], 'stats' => [...], 'criteres' => [...]]
     */
    public function classement($avec_identite = FALSE)
    {
        $criteres = $this->_criteres_actifs();
        $projets  = $this->_projets_eligibles();
        $notes    = $this->_notes_par_projet(array_keys($projets), array_keys($criteres));
        $jures    = $this->_jures();

        $rows = [];
        foreach ($projets as $sid => $p) {
            $calc = $this->_calculer($notes[$sid] ?? [], $criteres);
            $row = [
                'id'             => (int) $sid,
                'code_anonyme'   => $p['code_anonyme'],
                'titre'          => $p['titre'],
                'categorie'      => $p['categorie'],
                'score'          => $calc['score'],
                'ecart'          => $calc['ecart'],
                'divergence'     => $calc['ecart'] !== NULL && $calc['ecart'] > self::ECART_ALERTE,
                'jures_complets' => count($calc['par_jure']),
                'jures_partiels' => $calc['partiels'],
                'jures_total'    => count($jures),
                'par_critere'    => $calc['par_critere'],
                'statut'         => $calc['score'] === NULL
                    ? ($calc['partiels'] ? 'en_cours' : 'non_evalue')
                    : (count($calc['par_jure']) >= count($jures) ? 'complet' : 'en_cours'),
                'rang'           => NULL,
                'ex_aequo'       => FALSE,
            ];
            if ($avec_identite) {
                $row['candidat'] = $p['type_participation'] === 'equipe' && $p['nom_equipe']
                    ? $p['nom_equipe'] : $p['prenom'] . ' ' . $p['nom'];
                $row['responsable']  = $p['prenom'] . ' ' . $p['nom'];
                $row['organisation'] = $p['organisation'];
                $row['reference']    = $p['reference'];
            }
            $rows[] = $row;
        }

        // Tri : classés par score décroissant, puis non classés ; ordre stable par code
        usort($rows, function ($a, $b) {
            if ($a['score'] === NULL || $b['score'] === NULL) {
                return ($a['score'] === NULL) <=> ($b['score'] === NULL) ?: strcmp($a['code_anonyme'], $b['code_anonyme']);
            }
            return $b['score'] <=> $a['score'] ?: strcmp($a['code_anonyme'], $b['code_anonyme']);
        });

        // Rang « sportif » : 1, 2, 2, 4 …
        $rang = 0;
        $precedent = NULL;
        foreach ($rows as $i => &$r) {
            if ($r['score'] === NULL) {
                continue;
            }
            if ($precedent === NULL || $r['score'] !== $precedent) {
                $rang = $i + 1;
            }
            $r['rang'] = $rang;
            $precedent = $r['score'];
        }
        unset($r);
        $compte = array_count_values(array_filter(array_column($rows, 'rang')));
        foreach ($rows as &$r) {
            $r['ex_aequo'] = $r['rang'] !== NULL && $compte[$r['rang']] > 1;
        }
        unset($r);

        return ['rows' => $rows, 'stats' => $this->_stats($rows, $jures, $criteres), 'criteres' => array_values($criteres)];
    }

    /** Détail d'un projet : notes par juré et par critère, commentaires */
    public function detail_classement($soumission_id, $avec_identite = FALSE)
    {
        $projets = $this->_projets_eligibles((int) $soumission_id, TRUE);
        if (!$projets) {
            return NULL;
        }
        $p        = reset($projets);
        $criteres = $this->_criteres_actifs();
        $notes    = $this->_notes_par_projet([(int) $soumission_id], array_keys($criteres), TRUE);
        $calc     = $this->_calculer($notes[$soumission_id] ?? [], $criteres);
        $jures    = $this->_jures();

        // Tableau critère × juré
        $grille = [];
        foreach ($criteres as $cid => $c) {
            $ligne = [
                'critere_id' => $cid,
                'nom' => $c['nom'],
                'poids' => (int) $c['poids'],
                'note_max' => (int) $c['note_max'],
                'moyenne' => $calc['par_critere'][$cid]['moyenne'] ?? NULL,
                'notes' => []
            ];
            foreach ($jures as $jid => $libelle) {
                $n = $notes[$soumission_id][$jid][$cid] ?? NULL;
                $ligne['notes'][] = $n === NULL ? NULL : (float) $n['note'];
            }
            $grille[] = $ligne;
        }

        $scores_jures = [];
        $commentaires = [];
        foreach ($jures as $jid => $libelle) {
            $scores_jures[] = [
                'jure' => $libelle,
                'score' => $calc['par_jure'][$jid] ?? NULL,
                'complet' => isset($calc['par_jure'][$jid])
            ];
            foreach ($notes[$soumission_id][$jid] ?? [] as $cid => $n) {
                if (!empty($n['commentaire']) && isset($criteres[$cid])) {
                    $commentaires[] = ['jure' => $libelle, 'critere' => $criteres[$cid]['nom'], 'texte' => $n['commentaire']];
                }
            }
        }

        $d = [
            'id' => (int) $p['id'],
            'code_anonyme' => $p['code_anonyme'],
            'titre' => $p['titre'],
            'note_descriptive' => $p['note_descriptive'],
            'conformite' => $p['conformite'],
            'score' => $calc['score'],
            'ecart' => $calc['ecart'],
            'jures' => array_values($jures),
            'grille' => $grille,
            'scores_jures' => $scores_jures,
            'commentaires' => $commentaires,
        ];
        if ($avec_identite) {
            $d['candidat'] = $p['type_participation'] === 'equipe' && $p['nom_equipe'] ? $p['nom_equipe'] : $p['prenom'] . ' ' . $p['nom'];
            $d['responsable'] = $p['prenom'] . ' ' . $p['nom'];
            $d['email'] = $p['email'];
            $d['organisation'] = $p['organisation'];
            $d['reference'] = $p['reference'];
        }
        return $d;
    }

    public function classement_fige()
    {
        return $this->parametre('classement_fige') === '1';
    }

    /** Fige (ou rouvre) le classement. Figer lève l'anonymat dans le back-office. */
    public function figer_classement($figer, $admin_id)
    {
        if ($figer) {
            if ($this->classement_fige()) {
                return 'Le classement est déjà figé.';
            }
            if ($this->parametre('grille_verrouillee') !== '1') {
                return 'Validez d\'abord la grille de notation.';
            }
            $c = $this->classement();
            if ($c['stats']['classes'] === 0) {
                return 'Aucun projet n\'a encore d\'évaluation complète : rien à figer.';
            }
            $this->_poser('classement_fige', '1');
            $this->_poser('classement_fige_le', date('Y-m-d H:i:s'));
            $this->_poser('classement_fige_par', $admin_id ? (string) $admin_id : NULL);
            return TRUE;
        }
        if (!$this->classement_fige()) {
            return 'Le classement n\'est pas figé.';
        }
        $this->_poser('classement_fige', '0');
        $this->_poser('classement_fige_le', NULL);
        $this->_poser('classement_fige_par', NULL);
        return TRUE;
    }

    /* ---------- privé : classement ---------- */

    private function _criteres_actifs()
    {
        $rows = $this->db->select('id, nom, poids, note_max')->where('actif', 1)
            ->order_by('ordre', 'ASC')->order_by('id', 'ASC')
            ->get(self::T_CRITERES)->result_array();
        return array_column($rows, NULL, 'id');
    }

    /** Projets soumis et conformes (ou un projet précis, quel que soit son verdict, pour le détail) */
    private function _projets_eligibles($id = NULL, $tout_verdict = FALSE)
    {
        $this->db->select('s.id, s.code_anonyme, s.titre, s.note_descriptive, s.conformite,
                           c.reference, c.nom, c.prenom, c.email, c.type_participation, c.nom_equipe, c.organisation, c.categorie')
            ->from('soumissions s')->join('candidats c', 'c.id = s.candidat_id')
            ->where('s.statut', 'soumis');
        if (!$tout_verdict) {
            $this->db->where('s.conformite', 'conforme');
        }
        if ($id) {
            $this->db->where('s.id', (int) $id);
        }
        return array_column($this->db->get()->result_array(), NULL, 'id');
    }

    /** [soumission][jury][critere] => note (ou ['note','commentaire'] si $complet) */
    private function _notes_par_projet(array $sids, array $cids, $complet = FALSE)
    {
        if (!$sids || !$cids) {
            return [];
        }
        $rows = $this->db->select('soumission_id, jury_id, critere_id, note, commentaire')
            ->where_in('soumission_id', $sids)->where_in('critere_id', $cids)
            ->get(self::T_NOTES)->result_array();
        $n = [];
        foreach ($rows as $r) {
            $n[$r['soumission_id']][$r['jury_id']][$r['critere_id']] = $complet ? $r : (float) $r['note'];
        }
        return $n;
    }

    /**
     * Membres du jury ayant saisi au moins une note : [jury_id => libellé].
     * ⚠ Quand la page « Administrateurs & jury » existera, remplacer le libellé
     *   « Juré n » par le nom réel (jointure sur la table des membres du jury).
     */
    private function _jures()
    {
        $ids = array_column($this->db->distinct()->select('jury_id')->order_by('jury_id', 'ASC')
            ->get(self::T_NOTES)->result_array(), 'jury_id');
        $j = [];
        foreach ($ids as $i => $id) {
            $j[$id] = 'Juré ' . ($i + 1);
        }
        return $j;
    }

    private function _calculer(array $notes_projet, array $criteres)
    {
        $par_jure = [];
        $partiels = 0;
        $somme_crit = [];
        foreach ($notes_projet as $jid => $par_crit) {
            $par_crit = array_map(function ($v) {
                return is_array($v) ? (float) $v['note'] : (float) $v;
            }, $par_crit);
            foreach ($par_crit as $cid => $note) {
                if (isset($criteres[$cid])) {
                    $somme_crit[$cid][] = $note / $criteres[$cid]['note_max'];   // note normalisée 0..1
                }
            }
            if (count(array_intersect_key($criteres, $par_crit)) < count($criteres)) {
                $partiels++;
                continue;
            }
            $s = 0;
            foreach ($criteres as $cid => $c) {
                $s += $par_crit[$cid] / $c['note_max'] * $c['poids'];
            }
            $par_jure[$jid] = round($s, 2);
        }

        $par_critere = [];
        foreach ($criteres as $cid => $c) {
            $par_critere[$cid] = isset($somme_crit[$cid])
                ? [
                    'moyenne' => round(array_sum($somme_crit[$cid]) / count($somme_crit[$cid]) * $c['note_max'], 2),
                    'pourcentage' => round(array_sum($somme_crit[$cid]) / count($somme_crit[$cid]) * 100, 1)
                ]
                : ['moyenne' => NULL, 'pourcentage' => NULL];
        }

        return [
            'score'       => $par_jure ? round(array_sum($par_jure) / count($par_jure), 2) : NULL,
            'ecart'       => count($par_jure) > 1 ? round(max($par_jure) - min($par_jure), 2) : NULL,
            'par_jure'    => $par_jure,
            'partiels'    => $partiels,
            'par_critere' => $par_critere,
        ];
    }

    private function _stats(array $rows, array $jures, array $criteres)
    {
        $classes = array_values(array_filter($rows, function ($r) {
            return $r['score'] !== NULL;
        }));
        $attendues = count($rows) * count($jures);
        $faites = array_sum(array_column($rows, 'jures_complets'));
        $scores = array_column($classes, 'score');

        $hors = (int) $this->db->where('statut', 'soumis')->where('conformite !=', 'conforme')->count_all_results('soumissions');

        return [
            'eligibles'       => count($rows),
            'classes'         => count($classes),
            'complets'        => count(array_filter($rows, function ($r) {
                return $r['statut'] === 'complet';
            })),
            'non_evalues'     => count(array_filter($rows, function ($r) {
                return $r['statut'] === 'non_evalue';
            })),
            'divergences'     => count(array_filter($rows, function ($r) {
                return $r['divergence'];
            })),
            'hors_classement' => $hors,
            'jures'           => count($jures),
            'progression'     => $attendues ? (int) round(100 * $faites / $attendues) : 0,
            'score_max'       => $scores ? max($scores) : NULL,
            'score_moyen'     => $scores ? round(array_sum($scores) / count($scores), 2) : NULL,
            'grille_validee'  => $this->parametre('grille_verrouillee') === '1',
            'criteres_actifs' => count($criteres),
            'fige'            => $this->classement_fige(),
            'fige_le'         => $this->parametre('classement_fige_le'),
        ];
    }

    /* ================================================================ */

    private function _nb_notes($critere_id)
    {
        return (int) $this->db->where('critere_id', (int) $critere_id)->count_all_results(self::T_NOTES);
    }

    private function _poser($cle, $valeur)
    {
        $t = $this->db->dbprefix(self::T_PARAMETRES);
        $this->db->query("INSERT INTO {$t} (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)", [$cle, $valeur]);
    }
}
