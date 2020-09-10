<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

// use Restserver\Libraries\REST_Controller;

class Auth extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'customer/auth_model'
        ));
    }

    private function getToken_get()
    {
        return "areYou4Real";
    }

    private function validate_token($token = '')
    {
        if (!empty($token)) {
            $tk = $token;
        } else return false;
    }



    public function login_post()
    {
        // $data['title']    = display('customer');
        #-------------------------------------#
        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('password', 'password', 'required|max_length[32]|md5|trim');
        $this->form_validation->set_rules('token', 'token', 'required');

        // Setting Initial state for Token
        $update_token_session = false;

        #-------------------------------------#
        $data['user'] = (object)$userData = array(
            'email'      => $this->input->post('email'),
            'password'   => $this->input->post('password'),
        );

        #-------------------------------------#
        if ($this->form_validation->run()) {
            // Checking If User has Token
            if (!empty($_POST['token'])) {
                if ($this->getToken_get($_POST['token'])) {
                    // $update_token_session = true;
                } else {
                    // $update_token_session = false;
                    $this->response([
                        'status' => false,
                        'message' => 'invalid token'
                    ]);

                    return false;
                }
            }

            $user = $this->auth_model->checkUser($userData);

            if ($user->num_rows() > 0) {

                // $sData = array(
                // 	'isLogIn' 	  => true,
                // 	'id' 		  => $user->row()->uid,
                // 	'user_id' 	  => $user->row()->user_id,
                // 	'sponsor_id'  => $user->row()->sponsor_id,
                // 	'fullname'	  => $user->row()->f_name.' '.$user->row()->l_name,
                // 	'email' 	  => $user->row()->email,
                // 	'phone' 	  => $user->row()->phone,
                // );	

                return $this->response(['Successfully logged in'], REST_Controller::HTTP_OK);
            } else {
                return    $this->response(['Invalid credentials'], REST_Controller::HTTP_OK);
            }
        }
    }

    public function logout()
    {
        //update database status
        //$this->auth_model->last_logout();
        //destroy session
        $this->session->sess_destroy();
        redirect(base_url());
    }
}
