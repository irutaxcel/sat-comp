<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Prix à gagner d'un concours (table prix).
 */
class PrixM extends CI_Model
{
    protected $table = 'prix';

    const CATEGORIES = [
        'principal' => 'Prix principal',
        'special'   => 'Prix spécial',
        'mention'   => 'Mention',
    ];

    const DEVISES = ['BIF' => 'BIF', 'USD' => 'USD', 'EUR' => 'EUR'];

    /** Prix principaux par rang, puis prix spéciaux, puis mentions. */
    public function get_par_concours($concours_id, $public_seulement = false)
    {
        $this->db->where('concours_id', (int) $concours_id);
        if ($public_seulement) {
            $this->db->where('est_public', 1);
        }
        return $this->db
            ->order_by("FIELD(categorie, 'principal', 'special', 'mention')", '', false)
            ->order_by('rang', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table)
            ->result();
    }

    /** Récupère un prix en vérifiant qu'il appartient bien au concours. */
    public function get($id, $concours_id)
    {
        return $this->db->get_where($this->table, [
            'id'          => (int) $id,
            'concours_id' => (int) $concours_id,
        ])->row();
    }

    /** Un rang de prix principal ne peut être attribué qu'une fois par concours. */
    public function rang_existe($concours_id, $rang, $exclure_id = null)
    {
        $this->db->where([
            'concours_id' => (int) $concours_id,
            'categorie'   => 'principal',
            'rang'        => (int) $rang,
        ]);
        if ($exclure_id) {
            $this->db->where('id !=', (int) $exclure_id);
        }
        return $this->db->count_all_results($this->table) > 0;
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

    /**
     * Chiffres clés : nombre de prix, de lauréats et dotation totale par devise.
     */
    public function totaux(array $prix)
    {
        $totaux = ['nb_prix' => count($prix), 'nb_laureats' => 0, 'dotation' => []];

        foreach ($prix as $p) {
            $totaux['nb_laureats'] += (int) $p->nombre_laureats;
            if ($p->montant !== null) {
                $totaux['dotation'][$p->devise] = ($totaux['dotation'][$p->devise] ?? 0)
                    + (float) $p->montant * (int) $p->nombre_laureats;
            }
        }

        return $totaux;
    }
}
