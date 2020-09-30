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
            'customer/transections_model',
            'customer/investment_model',
        ));
    }

    public function index_get()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $data['title']   = display('investment');
        $data['invest'] = $this->investment_model->all_investment();
        // $data['content'] = $this->load->view('customer/pages/investment', $data, true);
        return $this->response(['packageOrders' => $data, 'success' => TRUE, 'message' => 'All Packages orders loaded'], REST_Controller::HTTP_OK);

        // $this->load->view('customer/layout/main_wrapper', $data);
    }
}
