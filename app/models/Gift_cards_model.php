<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Gift_cards_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addGiftCard($data = [])
    {
        if ($this->db->insert('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getGiftCardByID($id)
    {
        $q = $this->db->get_where('gift_cards', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateGiftCard($id, $data = [])
    {
        if ($this->db->update('gift_cards', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
}
