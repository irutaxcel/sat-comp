<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Brief & ressources fournis aux candidats (table ressources).
 */
class RessourcesM extends CI_Model
{
    protected $table = 'ressources';

    const CATEGORIES = [
        'brief'    => ['label' => "Brief d'architecture",     'icone' => 'fas fa-file-signature',   'aide' => 'Programme, cahier des charges du projet à concevoir'],
        'plans'    => ['label' => 'Plans & fichiers de base', 'icone' => 'fas fa-drafting-compass', 'aide' => 'Plan de situation, levé topographique, fichiers DWG…'],
        'photos'   => ['label' => 'Photos du site',           'icone' => 'fas fa-camera',           'aide' => 'Reportage photo, vues aériennes'],
        'gabarits' => ['label' => 'Gabarits de rendu',        'icone' => 'fas fa-th-large',         'aide' => 'Cartouche, format imposé des planches'],
        'autre'    => ['label' => 'Autres documents',         'icone' => 'fas fa-folder',           'aide' => 'Tout autre document utile'],
    ];

    /** Niveaux d'accès, du plus ouvert au plus restreint. */
    const VISIBILITES = [
        'public'   => ['label' => 'Public',            'badge' => 'info',    'icone' => 'fas fa-globe',      'aide' => 'Tous les visiteurs du site'],
        'inscrits' => ['label' => 'Candidats inscrits', 'badge' => 'primary', 'icone' => 'fas fa-user',       'aide' => 'Tout candidat connecté'],
        'valides'  => ['label' => 'Comptes validés',   'badge' => 'success', 'icone' => 'fas fa-user-check', 'aide' => 'Seulement les candidats dont le compte est validé'],
    ];

    /** Extensions acceptées (contrôle par liste blanche). */
    const EXTENSIONS = ['pdf', 'zip', 'rar', '7z', 'dwg', 'dxf', 'skp', 'rvt', 'ifc', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];

    /** Taille maximale applicative par fichier (bornée ensuite par la limite PHP). */
    const TAILLE_MAX_MO = 100;

    public function get_par_concours($concours_id)
    {
        return $this->db->where('concours_id', (int) $concours_id)
            ->order_by("FIELD(categorie, 'brief', 'plans', 'photos', 'gabarits', 'autre')", '', false)
            ->order_by('ordre', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    /**
     * Ressources accessibles à un niveau donné — pour l'espace candidat et le site public.
     * @param string $niveau 'public' | 'inscrits' | 'valides'
     */
    public function visibles_pour($concours_id, $niveau)
    {
        $niveaux = array_keys(self::VISIBILITES);
        $index   = array_search($niveau, $niveaux, true);
        $permis  = $index === false ? ['public'] : array_slice($niveaux, 0, $index + 1);

        return $this->db->where('concours_id', (int) $concours_id)
            ->where('est_publie', 1)
            ->where_in('visibilite', $permis)
            ->order_by("FIELD(categorie, 'brief', 'plans', 'photos', 'gabarits', 'autre')", '', false)
            ->order_by('ordre', 'ASC')
            ->get($this->table)
            ->result();
    }

    /** Récupère une ressource en vérifiant qu'elle appartient bien au concours. */
    public function get($id, $concours_id)
    {
        return $this->db->get_where($this->table, [
            'id'          => (int) $id,
            'concours_id' => (int) $concours_id,
        ])->row();
    }

    public function enregistrer($id, array $data)
    {
        if ($id) {
            $this->db->where('id', (int) $id)
                ->where('concours_id', (int) $data['concours_id'])
                ->update($this->table, $data);
            return $this->db->error()['code'] ? false : (int) $id;
        }

        $this->db->insert($this->table, $data);
        return $this->db->insert_id() ?: false;
    }

    public function supprimer($id, $concours_id)
    {
        $this->db->delete($this->table, ['id' => (int) $id, 'concours_id' => (int) $concours_id]);
        return $this->db->affected_rows() > 0;
    }

    /** Position suivante dans une catégorie (nouvelle ressource en fin de liste). */
    public function prochain_ordre($concours_id, $categorie)
    {
        $ligne = $this->db->select_max('ordre', 'max_ordre')
            ->where(['concours_id' => (int) $concours_id, 'categorie' => $categorie])
            ->get($this->table)
            ->row();
        return (int) ($ligne->max_ordre ?? 0) + 1;
    }

    /** Échange la position d'une ressource avec sa voisine dans la même catégorie. */
    public function deplacer($id, $concours_id, $sens)
    {
        $r = $this->get($id, $concours_id);
        if (!$r) {
            return false;
        }

        // Voisine dans l'ordre d'affichage (ordre, puis id)
        $o  = (int) $r->ordre;
        $i  = (int) $r->id;
        $op = $sens === 'haut' ? '<' : '>';

        $voisin = $this->db->where(['concours_id' => (int) $concours_id, 'categorie' => $r->categorie])
            ->where("(ordre $op $o OR (ordre = $o AND id $op $i))", null, false)
            ->order_by('ordre', $sens === 'haut' ? 'DESC' : 'ASC')
            ->order_by('id', $sens === 'haut' ? 'DESC' : 'ASC')
            ->limit(1)
            ->get($this->table)
            ->row();

        if (!$voisin) {
            return false;
        }

        // Si les deux ont la même position, on les départage d'abord
        $ordre_r      = (int) $voisin->ordre;
        $ordre_voisin = (int) $r->ordre;
        if ($ordre_r === $ordre_voisin) {
            $ordre_r = $sens === 'haut' ? max(0, $ordre_voisin - 1) : $ordre_voisin + 1;
        }

        $this->db->trans_start();
        $this->db->where('id', (int) $r->id)->update($this->table, ['ordre' => $ordre_r]);
        $this->db->where('id', (int) $voisin->id)->update($this->table, ['ordre' => $ordre_voisin]);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    /** À appeler depuis l'espace candidat à chaque téléchargement. */
    public function incrementer_telechargements($id)
    {
        $this->db->set('nb_telechargements', 'nb_telechargements + 1', false)
            ->where('id', (int) $id)
            ->update($this->table);
    }

    public function stats(array $ressources)
    {
        $stats = ['nb' => count($ressources), 'publiees' => 0, 'taille' => 0, 'telechargements' => 0, 'brief_publie' => false];

        foreach ($ressources as $r) {
            $stats['taille']          += (int) $r->fichier_taille;
            $stats['telechargements'] += (int) $r->nb_telechargements;
            if ($r->est_publie) {
                $stats['publiees']++;
                if ($r->categorie === 'brief') {
                    $stats['brief_publie'] = true;
                }
            }
        }

        return $stats;
    }
}
