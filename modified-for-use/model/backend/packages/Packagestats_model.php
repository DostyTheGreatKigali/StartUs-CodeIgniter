<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packagestats_model extends CI_Model
{

    public function create($data = array())
    {
        return $this->db->insert('pending_package_buying', $data);
    }

    public function read($limit, $offset)
    {
        return $this->db->select("*")
            ->from('pending_package_buying')
            ->order_by('package_request_date', 'asc')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    public function single($pending_package_id = null)
    {
        return $this->db->select('*')
            ->from('pending_package_buying')
            ->where('pending_package_id', $pending_package_id)
            ->get()
            ->row();
    }

    public function all()
    {
        return $this->db->select('*')
            ->from('pending_package_buying')
            ->get()
            ->result();
    }

    public function update($data = array())
    {
        return $this->db->where('pending_package_id', $data["pending_package_id"])
            ->update("pending_package_buying", $data);
    }

    public function delete($pending_package_id = null)
    {
        return $this->db->where('pending_package_id', $pending_package_id)
            ->delete("pending_package_buying");
    }
}
