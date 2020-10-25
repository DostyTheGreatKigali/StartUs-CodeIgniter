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
            'api/transections_model',
            'customer/Profile_model',
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
        // var_dump($post_user_data); die;

        $data = $this->transections_model->get_cata_wais_transections($user_id);
        $data['transection'] = $this->transections_model->all_transection($user_id);
        $data['title']   = display('transection');
        // $data['content'] = $this->load->view('customer/pages/transection', $data, true);
        // $this->load->view('customer/layout/main_wrapper', $data);
        return $this->response(['success' => TRUE, 'transactionsInfo' => $data], REST_Controller::HTTP_OK);
        // $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
    }

    public function investment_get()
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
        // var_dump($post_user_data); die;

        $data = $this->transections_model->get_cata_wais_transections($user_id);
        $data['transection'] = $this->transections_model->investment_transection($user_id);
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
