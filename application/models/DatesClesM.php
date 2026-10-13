<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Étapes complémentaires de l'agenda d'un concours (table dates_cles).
 */
class DatesClesM extends CI_Model
{
    protected $table = 'dates_cles';

    const ICONES = [
        'far fa-calendar'           => 'Étape générale',
        'fas fa-bullhorn'           => 'Annonce',
        'fas fa-chalkboard-teacher' => "Séance d'information",
        'fas fa-map-marker-alt'     => 'Visite de site / rencontre',
        'fas fa-gavel'              => 'Délibération du jury',
        'fas fa-award'              => 'Cérémonie de remise des prix',
    ];

    public function get_par_concours($concours_id, $public_seulement = false)
    {
        $this->db->where('concours_id', (int) $concours_id);
        if ($public_seulement) {
            $this->db->where('est_public', 1);
        }
        return $this->db->order_by('date_debut', 'ASC')->get($this->table)->result();
    }

    /** Récupère une étape en vérifiant qu'elle appartient bien au concours. */
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
            return $this->db->affected_rows() >= 0 ? (int) $id : false;
        }

        $this->db->insert($this->table, $data);
        return $this->db->insert_id() ?: false;
    }

    public function supprimer($id, $concours_id)
    {
        $this->db->delete($this->table, ['id' => (int) $id, 'concours_id' => (int) $concours_id]);
        return $this->db->affected_rows() > 0;
    }
}
