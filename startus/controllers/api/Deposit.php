<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Deposit extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(

            'customer/diposit_model',
            'customer/transections_model',
            'common_model',
        ));
        $this->load->library('payment');
    }


    /*
|-----------------------------------
|   Add new Deposit form
|-----------------------------------
*/
    public function index_post()
    {

        // return $this->response(['success' => FALSE, 'message' => $this->input->ip_address()], REST_Controller::HTTP_OK);

        $data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($data); die;
        if (empty($data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->post('user_id');

        $this->form_validation->set_rules('amount', display('amount'), 'required|numeric|trim');
        $this->form_validation->set_rules('method', display('payment_method'), 'required|alpha_numeric|trim');
        $this->form_validation->set_rules('fees', display('fees'), 'required|numeric|trim');

        $date           = new DateTime();
        $deposit_date   = $date->format('Y-m-d H:i:s');

        if ($this->form_validation->run()) {
            if ($this->input->post('method') == 'phone') {
                $mobiledata =  array(
                    'om_name'         => $this->input->post('om_name', TRUE),
                    'om_mobile'       => $this->input->post('om_mobile', TRUE),
                    'transaction_no'  => $this->input->post('transaction_no', TRUE),
                    'idcard_no'       => $this->input->post('idcard_no', TRUE),
                );

                $comment = json_encode($mobiledata);
            } else if ($this->input->post('method') == 'payeer') {

                $comment = $this->input->post('comments', TRUE);
                (object)$depositdata = array(
                    'user_id'           => $user_id,
                    'deposit_amount'    => $this->input->post('amount', TRUE),
                    'deposit_method'    => $this->input->post('method', TRUE),
                    'fees'              => $this->input->post('fees', TRUE),
                    'comments'          => $comment,
                    'deposit_date'      => $deposit_date,
                    'deposit_ip'        => $this->input->ip_address(),
                );

                $deposit = $this->diposit_model->save_deposit($depositdata);
            } else if ($this->input->post('method') == 'bank') { } else {
                $comment = $this->input->post('comments', TRUE);
            }

            $sdata['deposit']   = (object)$userdata = array(
                'deposit_id'        => @$deposit['deposit_id'],
                'user_id'           => $user_id,
                'deposit_amount'    => $this->input->post('amount', TRUE),
                'deposit_method'    => $this->input->post('method', TRUE),
                'fees'              => $this->input->post('fees', TRUE),
                'comments'          => $comment,
                'deposit_date'      => $deposit_date,
                'deposit_ip'        => $this->input->ip_address(),
            );
            $deposit = $this->diposit_model->save_deposit($sdata['deposit']);
            // return $this->response(['success'=> TRUE, 'message'=> 'Deposit Successfully Added.'], REST_Controller::HTTP_OK);


            //Store Deposit Session Data
            // $this->session->set_userdata($sdata);

            return $this->response(['success' => TRUE, 'message' => 'Deposit Successfully Added.'], REST_Controller::HTTP_OK);
        }

        return $this->response(['success' => FALSE, 'message' => 'Please enter the right information to let deposit work'], REST_Controller::HTTP_OK);


        $data['payment_gateway'] = $this->common_model->payment_gateway();


        $data['content'] = $this->load->view('customer/pages/diposit', $data, true);
        $this->load->view('customer/layout/main_wrapper', $data);
    }

    /*
    |-----------------------------------
    |   Fees for deposit Check
    |-----------------------------------
    */

    public function fees_post()
    {
        $fees = $this->fees_load($this->input->post('amount'), $this->input->post('method'), 'deposit');
        return $this->response(['success' => TRUE, 'fees' => $fees], REST_Controller::HTTP_OK);
    }



    /*
    |---------------------------------
    |   Fees Load and deposit amount 
    |---------------------------------
    */
    public function fees_load($amount = null, $method = null, $level)
    {

        $result = $this->db->select('*')
            ->from('fees_tbl')
            ->where('level', $level)
            ->get()
            ->row();
        return $fees = ($amount / 100) * $result->fees;
    }
}
