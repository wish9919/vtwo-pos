<?php
 if (!defined('BASEPATH')) {
     exit('No direct script access allowed');
 }

class Settings_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPrinter($data = [])
    {
        if ($this->db->insert('printers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function addStore($data = [])
    {
        if ($this->db->insert('stores', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function deletePrinter($id)
    {
        if ($this->db->delete('printers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function deleteStore($id)
    {
        if ($this->db->delete('stores', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getStoreByID($id)
    {
        $q = $this->db->get_where('stores', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updatePrinter($id, $data = [])
    {
        if ($this->db->update('printers', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function updateSetting($data = [])
    {
        if ($this->db->update('settings', $data, ['setting_id' => 1])) {
            return true;
        }
        return false;
    }

    public function updateStore($id, $data = [])
    {
        if ($this->db->update('stores', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
}
