<?php defined('BASEPATH') or exit('No direct script access allowed');

class Packageconfirmed_model extends CI_Model
{

    public function save_package($data)
    {
        $this->db->insert('pending_package_buying', $data);
        return array('pending_package_id' => $this->db->insert_id());
    }
    public function read($limit, $offset)
    {
        return $this->db->select("*")
            ->from('pending_package_buying')
            ->where('user_id', $this->session->userdata('user_id'))
            ->limit($limit, $offset)
            ->get()
            ->result();
    }
    public function all_deposit()
    {
        return $this->db->select('*')
            ->from('pending_package_buying')
            ->where('user_id', $this->session->userdata('user_id'))
            ->get()
            ->result();
    }

    public function save_transections($data)
    {
        $this->db->insert('transections', $data);
    }
}
