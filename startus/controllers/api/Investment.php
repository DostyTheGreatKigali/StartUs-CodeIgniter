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
        $data['title']   = display('investment');
        $data['invest'] = $this->investment_model->all_investment();
        $data['content'] = $this->load->view('customer/pages/investment', $data, true);
        return $this->response(['packageOrders' => $data, 'success' => TRUE, 'message' => 'Successfully retrieved bought packages orders'], REST_Controller::HTTP_OK);

        // $this->load->view('customer/layout/main_wrapper', $data);
    }
}
