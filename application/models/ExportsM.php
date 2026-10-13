<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Exports — construction des jeux de données exportables.
 *
 * Chaque type d'export a ses filtres (liste blanche) et ses colonnes.
 * Le même code sert au comptage (aperçu en direct sur la page) et à l'extraction,
 * ce qui garantit que le fichier contient exactement le nombre de lignes annoncé.
 */
class ExportsM extends CI_Model
{
    const T_JOURNAL = 'exports_journal';

    /** Catalogue des exports : libellé, nom de fichier, filtres autorisés */
    const TYPES = [
        'candidats'    => ['Candidatures',              'candidatures',  ['statut', 'type', 'categorie']],
        'participants' => ['Participants (personnes)',  'participants',  ['statut', 'categorie']],
        'projets'      => ['Projets soumis',            'projets',       ['statut_projet', 'conformite', 'anonymise']],
        'journal'      => ['Journal des décisions',     'journal',       ['periode', 'source']],
        'archive'      => ['Archive des dossiers (ZIP)', 'dossiers',     ['conformite']],
    ];

    const STATUTS_CANDIDAT = ['en_attente' => 'En attente', 'valide' => 'Validé', 'rejete' => 'Rejeté', 'suspendu' => 'Suspendu'];
    const CONFORMITES      = ['a_verifier' => 'À vérifier', 'conforme' => 'Conforme', 'non_conforme' => 'Non conforme'];
    const ACTIONS_JOURNAL  = [
        'valide' => 'Compte validé',
        'rejete' => 'Inscription rejetée',
        'suspendu' => 'Compte suspendu',
        'reouvert' => 'Dossier remis en attente',
        'creation' => 'Dossier projet créé',
        'depot_fichier' => 'Fichier déposé',
        'remplacement_fichier' => 'Fichier remplacé',
        'soumission' => 'Projet soumis',
        'conforme' => 'Projet conforme',
        'non_conforme' => 'Projet non conforme',
        'a_verifier' => 'Projet remis à vérifier',
    ];

    /* ================================================================
     *  API PUBLIQUE
     * ================================================================ */

    /** Ne garde que les filtres autorisés pour ce type, avec des valeurs valides */
    public function filtres_valides($type, array $brut)
    {
        $permis = self::TYPES[$type][2] ?? [];
        $listes = [
            'statut'        => array_keys(self::STATUTS_CANDIDAT),
            'type'          => ['individuel', 'equipe'],
            'categorie'     => ['professionnel', 'etudiant'],
            'statut_projet' => ['soumis', 'brouillon'],
            'conformite'    => array_keys(self::CONFORMITES),
            'periode'       => ['7', '30', '90'],
            'source'        => ['candidature', 'projet'],
            'anonymise'     => ['1'],
        ];

        $f = [];
        foreach ($permis as $cle) {
            $v = (string) ($brut[$cle] ?? '');
            if (in_array($v, $listes[$cle], TRUE)) {
                $f[$cle] = $v;
            }
        }
        return $f;
    }

    public function compter($type, array $f)
    {
        switch ($type) {
            case 'candidats':
                $this->_requete_candidats($f);
                return (int) $this->db->count_all_results();
            case 'participants':
                return count($this->_participants($f));
            case 'projets':
            case 'archive':
                $this->_requete_projets($type === 'archive' ? $f + ['statut_projet' => 'soumis'] : $f);
                return (int) $this->db->count_all_results();
            case 'journal':
                list($sql, $params) = $this->_sql_journal($f);
                return (int) $this->db->query("SELECT COUNT(*) AS n FROM ({$sql}) j", $params)->row()->n;
        }
        return 0;
    }

    /**
     * @return array [entêtes, lignes]
     */
    public function donnees($type, array $f)
    {
        switch ($type) {
            case 'candidats':
                return $this->_donnees_candidats($f);
            case 'participants':
                return $this->_donnees_participants($f);
            case 'projets':
                return $this->_donnees_projets($f);
            case 'journal':
                return $this->_donnees_journal($f);
        }
        return [[], []];
    }

    /** Compteurs affichés sur les cartes au chargement de la page */
    public function compteurs()
    {
        $c = [];
        foreach (array_keys(self::TYPES) as $type) {
            $defaut = $type === 'participants' ? ['statut' => 'valide']
                : ($type === 'archive' ? ['conformite' => 'conforme'] : []);
            try {
                $c[$type] = $this->compter($type, $defaut);
            } catch (Throwable $e) {
                $c[$type] = 0;   // table absente (script SQL non exécuté)
            }
        }
        return $c;
    }

    /** Projets à mettre dans l'archive ZIP (pour ExportsC::archive) */
    public function projets_archive(array $f)
    {
        $this->_requete_projets($f + ['statut_projet' => 'soumis']);
        return $this->db
            ->select('s.id, s.code_anonyme, s.titre, s.note_descriptive, s.version, s.conformite, s.derniere_soumission_le')
            ->order_by('s.code_anonyme', 'ASC')
            ->get()->result_array();
    }

    /* ---------- Journal des exports ---------- */

    public function journaliser($type, $format, array $filtres, $nb, $admin_id, $admin_libelle, $ip)
    {
        $this->db->insert(self::T_JOURNAL, [
            'type'          => $type,
            'format'        => $format,
            'filtres'       => $filtres ? json_encode($filtres, JSON_UNESCAPED_UNICODE) : NULL,
            'nb_lignes'     => (int) $nb,
            'admin_id'      => $admin_id ?: NULL,
            'admin_libelle' => $admin_libelle ? mb_substr($admin_libelle, 0, 150) : NULL,
            'ip'            => $ip,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function derniers_exports($limite = 10)
    {
        if (!$this->db->table_exists(self::T_JOURNAL)) {
            return [];
        }
        return $this->db->select('type, format, filtres, nb_lignes, admin_libelle, created_at')
            ->order_by('id', 'DESC')
            ->limit((int) $limite)
            ->get(self::T_JOURNAL)->result_array();
    }

    /* ================================================================
     *  CANDIDATURES
     * ================================================================ */

    private function _requete_candidats(array $f)
    {
        $this->db->from('candidats c');
        foreach (['statut' => 'c.statut', 'type' => 'c.type_participation', 'categorie' => 'c.categorie'] as $cle => $col) {
            if (isset($f[$cle])) {
                $this->db->where($col, $f[$cle]);
            }
        }
    }

    private function _donnees_candidats(array $f)
    {
        $this->_requete_candidats($f);
        $rows = $this->db
            ->select('c.*, s.statut AS projet_statut, s.conformite AS projet_conformite, s.code_anonyme')
            ->select('(SELECT COUNT(*) FROM ' . $this->db->dbprefix('candidat_membres') . ' m WHERE m.candidat_id = c.id) AS nb_membres', FALSE)
            ->join('soumissions s', 's.candidat_id = c.id', 'left')
            ->order_by('c.created_at', 'ASC')
            ->get()->result_array();

        $entetes = [
            'Référence',
            'Statut',
            'Type',
            'Équipe',
            'Membres (hors resp.)',
            'Catégorie',
            'Nom',
            'Prénom',
            'E-mail',
            'Téléphone',
            'Organisation',
            'N° Ordre / matricule',
            'E-mail vérifié',
            'Inscrit le',
            'Décision le',
            'Motif de rejet',
            'Projet',
            'Code projet',
            'Conformité'
        ];

        $lignes = array_map(function ($r) {
            return [
                $r['reference'],
                self::STATUTS_CANDIDAT[$r['statut']] ?? $r['statut'],
                $r['type_participation'] === 'equipe' ? 'Équipe' : 'Individuel',
                $r['nom_equipe'],
                $r['type_participation'] === 'equipe' ? (int) $r['nb_membres'] : NULL,
                $r['categorie'] === 'etudiant' ? 'Étudiant' : 'Professionnel',
                $r['nom'],
                $r['prenom'],
                $r['email'],
                $r['telephone'],
                $r['organisation'],
                $r['numero_ordre'],
                $r['email_verifie_le'] ? 'Oui' : 'Non',
                $this->_date($r['created_at']),
                $this->_date($r['decide_le']),
                $r['motif_rejet'],
                $r['projet_statut'] === 'soumis' ? 'Soumis' : ($r['projet_statut'] === 'brouillon' ? 'Brouillon' : 'Aucun'),
                $r['code_anonyme'],
                $r['projet_statut'] === 'soumis' ? (self::CONFORMITES[$r['projet_conformite']] ?? '') : NULL,
            ];
        }, $rows);

        return [$entetes, $lignes];
    }

    /* ================================================================
     *  PARTICIPANTS (une ligne par personne physique)
     * ================================================================ */

    private function _participants(array $f)
    {
        $this->_requete_candidats($f);
        $resp = $this->db
            ->select("c.id, c.reference, c.statut, c.type_participation, c.nom_equipe, c.categorie, c.organisation,
                      c.nom, c.prenom, c.email, c.telephone, 'Responsable' AS role, 0 AS ordre", FALSE)
            ->get()->result_array();

        if (empty($resp)) {
            return [];
        }
        $par_id = array_column($resp, NULL, 'id');

        $membres = $this->db
            ->select('candidat_id, nom, prenom, email, telephone, role')
            ->where_in('candidat_id', array_keys($par_id))
            ->order_by('id', 'ASC')
            ->get('candidat_membres')->result_array();

        $toutes = $resp;
        foreach ($membres as $m) {
            $c = $par_id[$m['candidat_id']];
            $toutes[] = array_merge($c, [
                'nom' => $m['nom'],
                'prenom' => $m['prenom'],
                'email' => $m['email'],
                'telephone' => $m['telephone'],
                'role' => $m['role'] ?: 'Membre',
                'ordre' => 1,
            ]);
        }
        // Regroupe par candidature, responsable en premier
        usort($toutes, function ($a, $b) {
            return [$a['reference'], $a['ordre']] <=> [$b['reference'], $b['ordre']];
        });
        return $toutes;
    }

    private function _donnees_participants(array $f)
    {
        $entetes = [
            'Référence candidature',
            'Statut',
            'Équipe',
            'Rôle',
            'Nom',
            'Prénom',
            'E-mail',
            'Téléphone',
            'Organisation',
            'Catégorie'
        ];
        $lignes = array_map(function ($p) {
            return [
                $p['reference'],
                self::STATUTS_CANDIDAT[$p['statut']] ?? $p['statut'],
                $p['type_participation'] === 'equipe' ? $p['nom_equipe'] : '(individuel)',
                $p['role'],
                $p['nom'],
                $p['prenom'],
                $p['email'],
                $p['telephone'],
                $p['organisation'],
                $p['categorie'] === 'etudiant' ? 'Étudiant' : 'Professionnel',
            ];
        }, $this->_participants($f));

        return [$entetes, $lignes];
    }

    /* ================================================================
     *  PROJETS
     * ================================================================ */

    private function _requete_projets(array $f)
    {
        $this->db->from('soumissions s')->join('candidats c', 'c.id = s.candidat_id');
        if (isset($f['statut_projet'])) {
            $this->db->where('s.statut', $f['statut_projet']);
        }
        if (isset($f['conformite'])) {
            $this->db->where('s.statut', 'soumis')->where('s.conformite', $f['conformite']);
        }
    }

    private function _donnees_projets(array $f)
    {
        $anonyme = !empty($f['anonymise']);
        $tf = $this->db->dbprefix('soumission_fichiers');

        $this->_requete_projets($f);
        $rows = $this->db
            ->select('s.*, c.reference, c.nom, c.prenom, c.email, c.type_participation, c.nom_equipe, c.organisation, c.categorie')
            ->select("(SELECT COUNT(*) FROM {$tf} f WHERE f.soumission_id = s.id AND f.actif = 1) AS nb_fichiers", FALSE)
            ->select("(SELECT COALESCE(SUM(f.taille_octets),0) FROM {$tf} f WHERE f.soumission_id = s.id AND f.actif = 1) AS taille", FALSE)
            ->order_by('s.code_anonyme', 'ASC')
            ->get()->result_array();

        $entetes = [
            'Code projet',
            'Titre',
            'Statut',
            'Conformité',
            'Motif de non-conformité',
            'Version',
            'Fichiers',
            'Taille (Mo)',
            'Première soumission',
            'Dernière soumission',
            'Catégorie',
            'Note descriptive'
        ];
        if (!$anonyme) {
            $entetes = array_merge($entetes, ['Référence candidature', 'Candidat / équipe', 'Responsable', 'E-mail', 'Organisation']);
        }

        $lignes = array_map(function ($r) use ($anonyme) {
            $l = [
                $r['code_anonyme'],
                $r['titre'],
                $r['statut'] === 'soumis' ? 'Soumis' : 'Brouillon',
                $r['statut'] === 'soumis' ? (self::CONFORMITES[$r['conformite']] ?? '') : NULL,
                $r['motif_conformite'],
                (int) $r['version'],
                (int) $r['nb_fichiers'],
                round($r['taille'] / 1048576, 2),
                $this->_date($r['premiere_soumission_le']),
                $this->_date($r['derniere_soumission_le']),
                $r['categorie'] === 'etudiant' ? 'Étudiant' : 'Professionnel',
                $r['note_descriptive'],
            ];
            if (!$anonyme) {
                $l = array_merge($l, [
                    $r['reference'],
                    $r['type_participation'] === 'equipe' ? $r['nom_equipe'] : $r['prenom'] . ' ' . $r['nom'],
                    $r['prenom'] . ' ' . $r['nom'],
                    $r['email'],
                    $r['organisation'],
                ]);
            }
            return $l;
        }, $rows);

        return [$entetes, $lignes];
    }

    /* ================================================================
     *  JOURNAL DES DÉCISIONS (candidatures + projets)
     * ================================================================ */

    private function _sql_journal(array $f)
    {
        $p = function ($t) {
            return $this->db->dbprefix($t);
        };
        $params = [];
        $depuis = '';
        if (isset($f['periode'])) {
            $depuis = ' AND x.created_at >= (NOW() - INTERVAL ? DAY)';
        }

        $parties = [];
        if (($f['source'] ?? '') !== 'projet') {
            $parties[] = "SELECT x.created_at, 'Candidature' AS source, c.reference AS ref, x.decision AS action,
                                 x.motif AS detail, 'admin' AS auteur_type, x.ip
                            FROM {$p('candidat_decisions')} x JOIN {$p('candidats')} c ON c.id = x.candidat_id
                           WHERE 1 = 1{$depuis}";
            if ($depuis) {
                $params[] = (int) $f['periode'];
            }
        }
        if (($f['source'] ?? '') !== 'candidature') {
            $parties[] = "SELECT x.created_at, 'Projet' AS source, s.code_anonyme AS ref, x.action,
                                 x.detail, x.auteur_type, x.ip
                            FROM {$p('soumission_historique')} x JOIN {$p('soumissions')} s ON s.id = x.soumission_id
                           WHERE 1 = 1{$depuis}";
            if ($depuis) {
                $params[] = (int) $f['periode'];
            }
        }
        return [implode(' UNION ALL ', $parties), $params];
    }

    private function _donnees_journal(array $f)
    {
        list($sql, $params) = $this->_sql_journal($f);
        $rows = $this->db->query("SELECT * FROM ({$sql}) j ORDER BY j.created_at DESC", $params)->result_array();

        $entetes = ['Date', 'Source', 'Référence / code', 'Action', 'Motif / détail', 'Auteur', 'Adresse IP'];
        $lignes  = array_map(function ($r) {
            return [
                $this->_date($r['created_at']),
                $r['source'],
                $r['ref'],
                self::ACTIONS_JOURNAL[$r['action']] ?? $r['action'],
                $r['detail'],
                $r['auteur_type'] === 'candidat' ? 'Candidat' : 'SATRACO',
                $r['ip'],
            ];
        }, $rows);

        return [$entetes, $lignes];
    }

    /* ================================================================ */

    private function _date($d)
    {
        return $d ? date('d/m/Y H:i', strtotime($d)) : NULL;
    }
}
