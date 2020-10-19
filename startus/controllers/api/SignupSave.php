<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Signup extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'api/app_model',
            'common_model'
        ));
    }

    public function index_post()
    {
        // File configs
        $config['upload_path'] = './upload/api/users';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = 500;
        // if ($this->session->userdata('isLogIn'))
        //     redirect(base_url());
        // print_r($_POST);
        // die();
        // return $this->response(['Testing'], REST_Controller::HTTP_OK);
        // $cat_id = $this->user->catidBySlug($this->uri->segment(1));

        //Language setting
        // $data['lang']       = $this->langSet();
        // print_r($_POST);
        // die();
        // $data['title']      = $this->uri->segment(1);
        // $data['article']    = $this->user->article($cat_id->cat_id);
        // $data['cat_info']   = $this->user->cat_info($this->uri->segment(1));

        //Load Helper For [user_id] Generate
        $this->load->helper('string');

        // print_r($_POST);
        // die();
        // return $this->response(['Testing'], REST_Controller::HTTP_OK);

        //Set Rules From validation
        $this->form_validation->set_rules('f_name', display('firstname'), 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('l_name', display('lastname'), 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('username', display('username'), 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('email', display('email'), "required|valid_email|max_length[100]");
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('pass', display('password'), 'required|min_length[8]|max_length[32]|matches[r_pass]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('r_pass', display('password'), 'trim');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('phone', display('mobile'), 'max_length[100]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('res_address', display('res_address'), 'trim|required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('city_town', display('city_town'), 'trim|required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('country', display('country'), 'required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('id_type', display('id_type'), 'required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('id_num', display('id_num'), 'required');
        // print_r($_FILES);
        // die();
        // $files = $this->request->getFiles();
        // print_r($files); die;

        $allImagesEmptyErrors = [];
        if (empty($_FILES['id_image']['name'])) {
            $allImagesEmptyErrors[] = 'ID image required';
            // print_r($_FILES);
            // die();
            // $this->form_validation->set_rules('id_image', 'ID Image', 'required');
        }
        if (empty($_FILES['user_id_image']['name'])) {
            $allImagesEmptyErrors[] = 'User ID image required';
            // $this->form_validation->set_rules('user_id_image', 'User and ID Image', 'required');
        }
        if (empty($_FILES['user_sign_image']['name'])) {
            $allImagesEmptyErrors[] = 'User Sign image required';

            // $this->form_validation->set_rules('user_sign_image', 'User and Signature', 'required');
        }

        if (!empty($allImagesEmptyErrors)) {
            return $this->response($allImagesEmptyErrors, REST_Controller::HTTP_OK);
        }


        // $this->form_validation->set_rules('id_image', display('nationalId'), 'trim|required');
        // $this->form_validation->set_rules('user_id_image', display('userImage'), 'required');
        // $this->form_validation->set_rules('user_sign_image', display('userSignature'), 'required');

        // print_r($_POST);
        // die();

        // We need extension of the first file

        $extensionOneArray = explode('/', $_FILES['id_image']['type']);
        //  print_r($extensionOneArray); die;
        $config['file_name'] = uniqid() . '.' . round(microtime(true) * 1000) . '.' . $extensionOneArray[1];


        // Load Helper 
        $this->load->library('upload', $config);
        // return $this->response(['Testing'], REST_Controller::HTTP_OK);
        //From Validation Check
        if ($this->form_validation->run() === TRUE) {

            // print_r($_POST);
            // die();
            // return $this->response(['Testing'], REST_Controller::HTTP_OK);

            // $sponsor_user_id = $this->db->select('user_id')->where('user_id', $this->input->cookie('sponsor_id'))->get('user_registration')->row();

            // if (!$sponsor_user_id) {
            //     // $this->session->set_flashdata('exception', "Valid Sponsor ID Required");
            //     // redirect("register");
            //     return $this->response(['Sponsor Id invalid'], REST_Controller::HTTP_OK);
            //     return false;
            // }

            $dlanguage = $this->db->select('language')->get('setting')->row();
            $data = array();
            $data = [
                'username'  => $this->input->post('username'),
                'email'     => $this->input->post('email')
            ];

            $usercheck = $this->app_model->checkUser($data);
            if ($usercheck->num_rows() > 0) {
                // if ($usercheck->row()->oauth_provider == 'facebook' && $usercheck->row()->status == 0 || $usercheck->row()->oauth_provider == 'google' && $usercheck->row()->status == 0) {

                //     $checkDuplictuser = $this->user->checkDuplictuser($data);
                //     if ($checkDuplictuser->num_rows() > 0) {
                //         // $this->session->set_flashdata('exception', display('username_used'));
                //         // redirect("register");
                //         return $this->response(['Username taken'], REST_Controller::HTTP_OK);
                //     }

                //     $data = [
                //         'f_name'        => $this->input->post('f_name'),
                //         'l_name'        => $this->input->post('l_name'),
                //         // 'sponsor_id'    => $this->input->cookie('sponsor_id') != "" ? $this->input->cookie('sponsor_id') : '',
                //         'language'      => $dlanguage->language,
                //         'username'      => $this->input->post('username'),
                //         'email'         => $this->input->post('email'),
                //         'phone'         => $this->input->post('phone'),
                //         'password'      => MD5($this->input->post('pass')),
                //         'status'        => 1,
                //         'reg_ip'        => $this->input->ip_address(),
                //         'res_address'       => $this->input->post('res_address'),
                //         'city_town'      => $this->input->post('city_town'),
                //         'country'       => $this->input->post('country'),
                //         'id_type'      => $this->input->post('id_type'),
                //         'id_num'      => $this->input->post('id_num'),
                //         'id_image'      => $this->input->post('id_image'),
                //         'user_id_image'      => $this->input->post('user_id_image'),
                //         'user_sign_image'      => $this->input->post('user_sign_image'),
                //     ];
                //     $this->user->updateUser($data);
                //     $this->session->set_flashdata('message', display('account_create_success_social'));
                //     redirect('register#tab2');
                // } else {
                return $this->response(['Email and username used'], REST_Controller::HTTP_OK);
                // $this->session->set_flashdata('exception', display('email_used') . " " . display('username_used'));
                // redirect("register");
                // }
            } else {
                // print_r($_POST);
                // die();

                $userid = strtoupper(random_string('alnum', 6));

                // if (!$this->input->valid_ip($this->input->ip_address())) {
                //     // $this->session->set_flashdata('exception',  "Invalid IP address");
                //     // redirect("register");
                //     return $this->response(['Invalid IP Address'], REST_Controller::HTTP_OK);
                // }

                $uploadedFiles = [];
                $uploadErrors = [];
                if (!$this->upload->do_upload('id_image')) {
                    $uploadErrors[] = $this->upload->display_errors();
                }
                $uploadedFiles[] = $this->upload->data();
                // We need a different file name
                $extensionTwoArray = explode('/', $_FILES['user_id_image']['type']);
                //  print_r($extensionTwoArray); die;
                $config['file_name'] = uniqid() . '.' . round(microtime(true) * 1000) . '.' . $extensionTwoArray[1];
                $config['file_name'] = uniqid() . '.' . round(microtime(true) * 1000);
                $this->load->library('upload', $config);


                if (!$this->upload->do_upload('user_id_image')) {
                    $uploadErrors[] = $this->upload->display_errors();
                }
                $uploadedFiles[] = $this->upload->data();
                // We need a different file name
                $extensionThreeArray = explode('/', $_FILES['user_sign_image']['type']);
                //  print_r($extensionThreeArray); die;
                $config['file_name'] = uniqid() . '.' . round(microtime(true) * 1000) . '.' . $extensionThreeArray[1];

                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('user_sign_image')) {
                    $uploadErrors[] = $this->upload->display_errors();
                }
                $uploadedFiles[] = $this->upload->data();
                // print_r($uploadedFiles); die;
                // print_r([$uploadedFiles, $this->upload->data(), $uploadErrors]); die;

                // die($uploadErrors);
                // Counting errors
                // if(count($uploadErrors) > 0){
                //     or
                // if(empty($uploadErrors) ){
                if (count($uploadErrors) > 0) {
                    // An error occurred in uploads, return the $uploadError
                    print_r($_POST);
                    die();
                    return $this->response(['Error uploading one or more files'], REST_Controller::HTTP_OK);
                }

                $data = [
                    'f_name'        => $this->input->post('f_name'),
                    'l_name'        => $this->input->post('l_name'),
                    // 'sponsor_id'    => $this->input->cookie('sponsor_id') != "" ? $this->input->cookie('sponsor_id') : '',
                    'language'      => $dlanguage->language,
                    'user_id'       => $userid,
                    'username'      => $this->input->post('username'),
                    'email'         => $this->input->post('email'),
                    'phone'         => $this->input->post('phone'),
                    'oauth_provider' => 'website',
                    'password'      => MD5($this->input->post('pass')),
                    'status'        => 0,
                    'reg_ip'        => $this->input->ip_address(),
                    'res_address'       => $this->input->post('res_address'),
                    'city_town'      => $this->input->post('city_town'),
                    'country'       => $this->input->post('country'),
                    'id_type'      => $this->input->post('id_type'),
                    'id_num'      => $this->input->post('id_num'),
                    'id_image'      => $uploadedFiles[0]['file_name'], //$this->input->post('id_image'),
                    'user_id_image'      => $uploadedFiles[1]['file_name'], //->input->post('user_id_image'),
                    'user_sign_image'      => $uploadedFiles[2]['file_name'], //$this->input->post('user_sign_image'),
                ];
                $duplicatemail = $this->app_model->checkDuplictemail($data);
                if ($duplicatemail->num_rows() > 0) {
                    return $this->response(['Email used'], REST_Controller::HTTP_OK);
                    // $this->session->set_flashdata('exception', display('email_used'));
                    // redirect("register");
                }
                $checkDuplictuser = $this->app_model->checkDuplictuser($data);
                if ($checkDuplictuser->num_rows() > 0) {
                    // $this->session->set_flashdata('exception', display('username_used'));
                    // redirect("register");
                    return $this->response(['Username used'], REST_Controller::HTTP_OK);
                }


                // print_r($_POST);
                // die();
                if ($this->app_model->registerUser($data)) {
                    // print_r($_POST);
                    // die();
                    // $appSetting = $this->common_model->get_setting();

                    // $data['title']      = $appSetting->title;
                    // $data['to']         = $this->input->post('email');
                    // $data['subject']    = 'Account Activation';
                    // $data['message']    = "<br><b>Your account was created successfully, Please click on the link below to activate your account. </b><br> <a target='_blank' href='" . base_url('home/activeAcc/') . strtolower($userid) . md5($userid) . "'>" . base_url('home/activeAcc/') . strtolower($userid) . md5($userid) . "</a>";
                    // $this->common_model->send_email($data);

                    // $this->session->set_flashdata('message', display('account_create_active_link'));
                    // redirect("register#tab2");
                    return $this->response(['Successfully registered'], REST_Controller::HTTP_OK);
                } else {
                    //         print("couldn not register");
                    //               print_r($_POST);
                    // die();
                    // $this->session->set_flashdata('exception',  display('please_try_again'));
                    // redirect("register");
                    return $this->response(['Regitration Failed. Please try again'], REST_Controller::HTTP_OK);
                }
            }
        }

        $this->response(validation_errors(), REST_Controller::HTTP_OK);
        // $this->load->view('website/header', $data);
        // $this->load->view('website/register', $data);
        // $this->load->view('website/footer', $data);
    }
}
