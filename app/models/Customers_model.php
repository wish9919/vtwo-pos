<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Customers_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addCustomer($data = [])
    {
        if ($this->db->insert('customers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function deleteCustomer($id)
    {
        if ($this->db->delete('customers', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getCustomerByID($id)
    {
        $q = $this->db->get_where('customers', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function updateCustomer($id, $data = [])
    {
        if ($this->db->update('customers', $data, ['id' => $id])) {
            return true;
        }
        return false;
    }
}
