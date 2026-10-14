<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * application/models/Participant_model.php
 */
class Participant_model extends CI_Model
{
    protected $table = 'participants';

    public function trouver_par_email($email)
    {
        return $this->db->where('email', strtolower(trim($email)))
            ->get($this->table)
            ->row();
    }

    public function trouver($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function creer(array $data)
    {
        $this->db->insert($this->table, [
            'nom'          => trim($data['nom']),
            'prenom'       => trim($data['prenom']),
            'email'        => strtolower(trim($data['email'])),
            'telephone'    => trim($data['telephone'] ?? '') ?: null,
            'universite'   => trim($data['universite']),
            'mot_de_passe' => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
        ]);
        return $this->db->insert_id();
    }

    /** Retourne le participant si email + mot de passe sont valides, sinon null */
    public function verifier_identifiants($email, $mot_de_passe)
    {
        $p = $this->trouver_par_email($email);

        if (!$p || !password_verify($mot_de_passe, $p->mot_de_passe)) {
            return null;
        }

        // Re-hachage si l'algorithme par défaut de PHP a évolué
        if (password_needs_rehash($p->mot_de_passe, PASSWORD_DEFAULT)) {
            $this->db->where('id', $p->id)->update($this->table, [
                'mot_de_passe' => password_hash($mot_de_passe, PASSWORD_DEFAULT),
            ]);
        }
        return $p;
    }

    public function marquer_connexion($id)
    {
        $this->db->where('id', (int) $id)
            ->update($this->table, ['derniere_connexion' => date('Y-m-d H:i:s')]);
    }
}
