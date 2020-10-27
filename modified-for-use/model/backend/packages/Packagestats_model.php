<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packagestats_model extends CI_Model
{

    public function create($data = array())
    {
        return $this->db->insert('investment', $data);
    }

    public function read($limit, $offset)
    {
        return $this->db->select("*")
            ->from('investment')
            ->order_by('invest_date', 'asc')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    public function single($package_id = null)
    {
        return $this->db->select('*')
            ->from('investment')
            ->where('package_id', $package_id)
            ->get()
            ->row();
    }

    public function all()
    {
        return $this->db->select('*')
            ->from('investment')
            ->get()
            ->result();
    }

    public function update($data = array())
    {
        return $this->db->where('package_id', $data["package_id"])
            ->update("investment", $data);
    }

    public function delete($package_id = null)
    {
        return $this->db->where('package_id', $package_id)
            ->delete("investment");
    }
}
