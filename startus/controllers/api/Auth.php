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

    public function getToken_get()
    {
        // return "areYou4Real";
        // We need a device id for the user to give them token they will use to access public endpoints
        return $this->response(['token' => hash('sha256', $_REQUEST['device_id'])], REST_Controller::HTTP_OK);
    }

    public function login_post()
    {
        if ($this->input->post('token') != hash('sha256', $this->input->post('device_id'))) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }
        // $data['title']    = display('customer');
        #-------------------------------------#
        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('password', 'password', 'required|max_length[32]|md5|trim');

        #-------------------------------------#
        $data['user'] = (object)$userData = array(
            'email'      => $this->input->post('email'),
            'password'   => $this->input->post('password'),
        );

        #-------------------------------------#
        if ($this->form_validation->run()) {
            // Checking If User has Token

            $user = $this->auth_model->checkUser($userData);


            if ($user->num_rows() > 0) {

                // Create a new api_token for this user
                $api_token = hash('sha256', $this->input->post('device_id') . '-' . time() . '-' . $user->row()->uid);
                $this->db->set('api_token', $api_token);
                $this->db->where('uid', $user->row()->uid);
                $this->db->update('user_registration');

                $sData = array(
                    'isLogIn'       => true,
                    'id'           => $user->row()->uid,
                    'user_id'       => $user->row()->user_id,
                    'sponsor_id'  => $user->row()->sponsor_id,
                    'fullname'      => $user->row()->f_name . ' ' . $user->row()->l_name,
                    'email'       => $user->row()->email,
                    'phone'       => $user->row()->phone,
                    'username'       => $user->row()->username,
                    'address'       => $user->row()->res_address,
                    'city_town'       => $user->row()->city_town,
                    'country'       => $user->row()->country,
                    'lastname'       => $user->row()->l_name,
                    'firstname'       => $user->row()->f_name,
                    'api_token'       => $api_token
                    //$user->row()->api_token,
                );

                return $this->response(['success' => TRUE, 'message' => 'Successfully logged in', 'user' => $sData], REST_Controller::HTTP_OK);
            } else {
                return    $this->response(['success' => FALSE, 'message' => 'Invalid credentials'], REST_Controller::HTTP_OK);
            }
        }
    }


    public function register_post()
    {
        return $this->response(['Registration'], REST_Controller::HTTP_OK);
        // echo("Registration");
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
