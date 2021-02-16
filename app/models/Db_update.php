<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Db_update extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->dbforge();
    }

    public function update()
    {
        if ($this->Settings->version == '4.0.12') {
            $this->update4013();
            redirect();
        }
    }

    public function update4013()
    {
        $settings = [
            'rtl'       => ['type' => 'TINYINT', 'constraint' => '1', 'default' => 0, 'null' => true],
            'print_img' => ['type' => 'TINYINT', 'constraint' => '1', 'default' => 0, 'null' => true],
        ];
        $this->dbforge->add_column('settings', $settings);
        return $this->db->update('settings', ['version' => '4.0.13']);
    }
}
