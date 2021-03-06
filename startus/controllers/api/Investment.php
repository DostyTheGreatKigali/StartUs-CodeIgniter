<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Investment extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // if (!$this->session->userdata('isLogIn'))
        //     redirect('login');

        // if (!$this->session->userdata('user_id'))
        //     redirect('login');

        $this->load->model(array(
            'customer/auth_model',
            'customer/package_model',
            'api/transections_model',
            'api/investment_model',
        ));
    }

    public function index_get()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->get('user_id'))
            ->where('api_token', $this->input->get('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->get('user_id');

        $data['title']   = display('investment');
        $data['investedPackages'] = $this->investment_model->all_investment($user_id);
        // $data['content'] = $this->load->view('customer/pages/investment', $data, true);
        return $this->response(['packageOrders' => $data, 'success' => TRUE, 'message' => 'All Packages orders loaded'], REST_Controller::HTTP_OK);

        // $this->load->view('customer/layout/main_wrapper', $data);
    }
}
