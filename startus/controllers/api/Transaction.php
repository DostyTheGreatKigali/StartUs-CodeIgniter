<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Transaction extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // if (!$this->session->userdata('isLogIn'))
        //     redirect('login');

        // if (!$this->session->userdata('user_id'))
        //     redirect('login');

        $this->load->model(array(
            'customer/transections_model',
            'customer/Profile_model',
        ));
    }


    public function index_get()
    {
        $data = $this->response(['user_id' => $_REQUEST['user_id'], 'api_token' => $_REQUEST['api_token']], REST_Controller::HTTP_OK);
        // var_dump($post_user_data); die;

        $data = $this->transections_model->get_cata_wais_transections();
        $data['transection'] = $this->transections_model->all_transection();
        $data['title']   = display('transection');
        // $data['content'] = $this->load->view('customer/pages/transection', $data, true);
        // $this->load->view('customer/layout/main_wrapper', $data);
        return $this->response(['success' => TRUE, 'transactionsInfo' => $data], REST_Controller::HTTP_OK);
        // $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
    }

    public function transection_details($id = NULL, $table = NULL)
    {

        $data['title']   = display('transection_details');
        $data['my_info'] = $this->Profile_model->my_info();
        $data['transection'] = $this->transections_model->transection_by_id($id, $table);
        $data['content'] = $this->load->view('customer/pages/transection_details', $data, true);
        $this->load->view('customer/layout/main_wrapper', $data);
    }
}
