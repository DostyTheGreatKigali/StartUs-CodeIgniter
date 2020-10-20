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
        if ($this->input->post('token') != hash('sha256', $this->input->post('device_id'))) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        // File configs
        $config['upload_path']          = './upload/api/users';
        $config['allowed_types']        = 'gif|jpg|png|jpeg';
        $config['max_size']             = 10240;
        // if ($this->session->userdata('isLogIn'))
        //     redirect(base_url());
        //  return $this->response($_FILES, REST_Controller::HTTP_OK);

        //     print_r($_POST);
        //     die();
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
        $this->form_validation->set_rules('f_name', 'Firstname', 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('l_name', 'Lastname', 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('username', 'Username', 'required|max_length[50]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('email', 'Email', "required|valid_email|max_length[100]");
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('pass', 'Password', 'required|min_length[6]|max_length[32]|matches[r_pass]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('r_pass', 'Password', 'trim');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('phone',  'Mobile', 'max_length[100]');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('res_address', 'Residential Address', 'trim|required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('city_town', 'Town', 'trim|required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('country', 'Country', 'required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('id_type', 'ID Type', 'required');
        // print_r($_POST);
        // die();
        $this->form_validation->set_rules('id_num', 'ID Number', 'required');

        $this->form_validation->set_rules('sponsor_id', 'Referral Code', 'required');
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
            $allImagesEmptyErrors[] = 'User Signature image required';

            // $this->form_validation->set_rules('user_sign_image', 'User and Signature', 'required');
        }

        if (!empty($allImagesEmptyErrors)) {
            return $this->response(['success' => FALSE, 'message' => implode(', ', $allImagesEmptyErrors)], REST_Controller::HTTP_OK);
        }


        // $this->form_validation->set_rules('id_image', display('nationalId'), 'trim|required');
        // $this->form_validation->set_rules('user_id_image', display('userImage'), 'required');
        // $this->form_validation->set_rules('user_sign_image', display('userSignature'), 'required');

        // print_r($_POST);
        // die();

        // We need extension of the first file

        $extensionOneArray = explode('/', $_FILES['id_image']['type']);

        // $extensionOneArray = explode('/', $_FILES['user_id_image']['type']);
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

            $sponsor_user_id = $this->db->select('user_id')->where('user_id', $this->input->post('sponsor_id'))->get('user_registration')->row();

            if (!$sponsor_user_id) {
                return $this->response(['success' => FALSE, 'message' => 'Referral Code not valid'], REST_Controller::HTTP_OK);
            }

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
                return $this->response(['success' => FALSE, 'message' => 'Email and username used'], REST_Controller::HTTP_OK);
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
                // if (!$this->upload->do_upload('user_id_image')) {
                //     $uploadErrors[] = $this->upload->display_errors();
                // }

                if (!$this->upload->do_upload('id_image')) {
                    $uploadErrors[] = $this->upload->display_errors();
                }

                $uploadedFiles[] = $this->upload->data();
                // We need a different file name
                // $extensionTwoArray = explode('/', $_FILES['user_sign_image']['type']);
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
                // We need a different file name
                // $extensionThreeArray = explode('/', $_FILES['user_sign_image']['type']);
                // //  print_r($extensionThreeArray); die;
                // $config['file_name'] = uniqid() . '.' . round(microtime(true) * 1000) . '.' . $extensionThreeArray[1];

                // $this->load->library('upload', $config);

                // if (!$this->upload->do_upload('user_sign_image')) {
                //     $uploadErrors[] = $this->upload->display_errors();
                // }
                // $uploadedFiles[] = $this->upload->data();
                // print_r($uploadedFiles); die;
                // print_r([$uploadedFiles, $this->upload->data(), $uploadErrors]); die;

                // die($uploadErrors);
                // Counting errors
                // if(count($uploadErrors) > 0){
                //     or
                // if(empty($uploadErrors) ){
                if (count($uploadErrors) > 0) {
                    // An error occurred in uploads, return the $uploadError
                    // print_r($_POST);
                    // die();
                    return $this->response(['success' => FALSE, 'message' => implode(', ', $uploadErrors)], REST_Controller::HTTP_OK);
                    // return $this->response(['success'=> FALSE, 'message' => 'Error uploading one or more files'], REST_Controller::HTTP_OK);
                }

                $data = [
                    'f_name'        => $this->input->post('f_name'),
                    'l_name'        => $this->input->post('l_name'),
                    'sponsor_id'    => $this->input->post('sponsor_id'),
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
                    'user_id_image'      => $uploadedFiles[0]['file_name'], //->input->post('user_id_image'),
                    'user_sign_image'      => $uploadedFiles[1]['file_name'], //$this->input->post('user_sign_image'),
                ];
                $duplicatemail = $this->app_model->checkDuplictemail($data);
                if ($duplicatemail->num_rows() > 0) {
                    return $this->response(['success' => FALSE, 'message' => 'Email used'], REST_Controller::HTTP_OK);
                    // $this->session->set_flashdata('exception', display('email_used'));
                    // redirect("register");
                }
                $checkDuplictuser = $this->app_model->checkDuplictuser($data);
                if ($checkDuplictuser->num_rows() > 0) {
                    // $this->session->set_flashdata('exception', display('username_used'));
                    // redirect("register");
                    return $this->response(['success' => FALSE, 'message' => 'Username used'], REST_Controller::HTTP_OK);
                }


                // print_r($_POST);
                // die();
                if ($this->app_model->registerUser($data)) {
                    // print_r($_POST);
                    // die();
                    $appSetting = $this->common_model->get_setting();

                    $data['title']      = $appSetting->title;
                    $data['to']         = $this->input->post('email');
                    $data['subject']    = 'Account Activation';
                    $data['message']    = "<br><b>Your account was created successfully, Please click on the link below to activate your account. </b><br> <a target='_blank' href='" . base_url('home/activeAcc/') . strtolower($userid) . md5($userid) . "'>" . base_url('home/activeAcc/') . strtolower($userid) . md5($userid) . "</a>";
                    $this->common_model->send_email($data);

                    // $this->session->set_flashdata('message', display('account_create_active_link'));
                    // redirect("register#tab2");
                    return $this->response(['message' => 'Successfully registered', 'success' => TRUE], REST_Controller::HTTP_OK);
                } else {
                    //         print("couldn not register");
                    //               print_r($_POST);
                    // die();
                    // $this->session->set_flashdata('exception',  display('please_try_again'));
                    // redirect("register");
                    return $this->response(['message' => 'Regitration Failed. Please try again', 'success' => FALSE], REST_Controller::HTTP_OK);
                }
            }
        }

        $validation_errors = validation_errors();
        $validation_errors = str_replace('<p>', '', $validation_errors);
        $validation_errors = str_replace('</p>', ', ', $validation_errors);

        $this->response(['success' => FALSE, 'message' => $validation_errors], REST_Controller::HTTP_OK);

        // $this->response(['success'=> FALSE, 'message' => implode(', ' , validation_errors())], REST_Controller::HTTP_OK);
        // $this->load->view('website/header', $data);
        // $this->load->view('website/register', $data);
        // $this->load->view('website/footer', $data);
    }



    public function countries_get()
    {

        $countryArray = array(
            'GH' => array('name' => 'GHANA', 'code' => '233'),
            'AD' => array('name' => 'ANDORRA', 'code' => '376'),
            'AE' => array('name' => 'UNITED ARAB EMIRATES', 'code' => '971'),
            'AF' => array('name' => 'AFGHANISTAN', 'code' => '93'),
            'AG' => array('name' => 'ANTIGUA AND BARBUDA', 'code' => '1268'),
            'AI' => array('name' => 'ANGUILLA', 'code' => '1264'),
            'AL' => array('name' => 'ALBANIA', 'code' => '355'),
            'AM' => array('name' => 'ARMENIA', 'code' => '374'),
            'AN' => array('name' => 'NETHERLANDS ANTILLES', 'code' => '599'),
            'AO' => array('name' => 'ANGOLA', 'code' => '244'),
            'AQ' => array('name' => 'ANTARCTICA', 'code' => '672'),
            'AR' => array('name' => 'ARGENTINA', 'code' => '54'),
            'AS' => array('name' => 'AMERICAN SAMOA', 'code' => '1684'),
            'AT' => array('name' => 'AUSTRIA', 'code' => '43'),
            'AU' => array('name' => 'AUSTRALIA', 'code' => '61'),
            'AW' => array('name' => 'ARUBA', 'code' => '297'),
            'AZ' => array('name' => 'AZERBAIJAN', 'code' => '994'),
            'BA' => array('name' => 'BOSNIA AND HERZEGOVINA', 'code' => '387'),
            'BB' => array('name' => 'BARBADOS', 'code' => '1246'),
            'BD' => array('name' => 'BANGLADESH', 'code' => '880'),
            'BE' => array('name' => 'BELGIUM', 'code' => '32'),
            'BF' => array('name' => 'BURKINA FASO', 'code' => '226'),
            'BG' => array('name' => 'BULGARIA', 'code' => '359'),
            'BH' => array('name' => 'BAHRAIN', 'code' => '973'),
            'BI' => array('name' => 'BURUNDI', 'code' => '257'),
            'BJ' => array('name' => 'BENIN', 'code' => '229'),
            'BL' => array('name' => 'SAINT BARTHELEMY', 'code' => '590'),
            'BM' => array('name' => 'BERMUDA', 'code' => '1441'),
            'BN' => array('name' => 'BRUNEI DARUSSALAM', 'code' => '673'),
            'BO' => array('name' => 'BOLIVIA', 'code' => '591'),
            'BR' => array('name' => 'BRAZIL', 'code' => '55'),
            'BS' => array('name' => 'BAHAMAS', 'code' => '1242'),
            'BT' => array('name' => 'BHUTAN', 'code' => '975'),
            'BW' => array('name' => 'BOTSWANA', 'code' => '267'),
            'BY' => array('name' => 'BELARUS', 'code' => '375'),
            'BZ' => array('name' => 'BELIZE', 'code' => '501'),
            'CA' => array('name' => 'CANADA', 'code' => '1'),
            'CC' => array('name' => 'COCOS (KEELING) ISLANDS', 'code' => '61'),
            'CD' => array('name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'code' => '243'),
            'CF' => array('name' => 'CENTRAL AFRICAN REPUBLIC', 'code' => '236'),
            'CG' => array('name' => 'CONGO', 'code' => '242'),
            'CH' => array('name' => 'SWITZERLAND', 'code' => '41'),
            'CI' => array('name' => 'COTE D IVOIRE', 'code' => '225'),
            'CK' => array('name' => 'COOK ISLANDS', 'code' => '682'),
            'CL' => array('name' => 'CHILE', 'code' => '56'),
            'CM' => array('name' => 'CAMEROON', 'code' => '237'),
            'CN' => array('name' => 'CHINA', 'code' => '86'),
            'CO' => array('name' => 'COLOMBIA', 'code' => '57'),
            'CR' => array('name' => 'COSTA RICA', 'code' => '506'),
            'CU' => array('name' => 'CUBA', 'code' => '53'),
            'CV' => array('name' => 'CAPE VERDE', 'code' => '238'),
            'CX' => array('name' => 'CHRISTMAS ISLAND', 'code' => '61'),
            'CY' => array('name' => 'CYPRUS', 'code' => '357'),
            'CZ' => array('name' => 'CZECH REPUBLIC', 'code' => '420'),
            'DE' => array('name' => 'GERMANY', 'code' => '49'),
            'DJ' => array('name' => 'DJIBOUTI', 'code' => '253'),
            'DK' => array('name' => 'DENMARK', 'code' => '45'),
            'DM' => array('name' => 'DOMINICA', 'code' => '1767'),
            'DO' => array('name' => 'DOMINICAN REPUBLIC', 'code' => '1809'),
            'DZ' => array('name' => 'ALGERIA', 'code' => '213'),
            'EC' => array('name' => 'ECUADOR', 'code' => '593'),
            'EE' => array('name' => 'ESTONIA', 'code' => '372'),
            'EG' => array('name' => 'EGYPT', 'code' => '20'),
            'ER' => array('name' => 'ERITREA', 'code' => '291'),
            'ES' => array('name' => 'SPAIN', 'code' => '34'),
            'ET' => array('name' => 'ETHIOPIA', 'code' => '251'),
            'FI' => array('name' => 'FINLAND', 'code' => '358'),
            'FJ' => array('name' => 'FIJI', 'code' => '679'),
            'FK' => array('name' => 'FALKLAND ISLANDS (MALVINAS)', 'code' => '500'),
            'FM' => array('name' => 'MICRONESIA, FEDERATED STATES OF', 'code' => '691'),
            'FO' => array('name' => 'FAROE ISLANDS', 'code' => '298'),
            'FR' => array('name' => 'FRANCE', 'code' => '33'),
            'GA' => array('name' => 'GABON', 'code' => '241'),
            'GB' => array('name' => 'UNITED KINGDOM', 'code' => '44'),
            'GD' => array('name' => 'GRENADA', 'code' => '1473'),
            'GE' => array('name' => 'GEORGIA', 'code' => '995'),
            'GI' => array('name' => 'GIBRALTAR', 'code' => '350'),
            'GL' => array('name' => 'GREENLAND', 'code' => '299'),
            'GM' => array('name' => 'GAMBIA', 'code' => '220'),
            'GN' => array('name' => 'GUINEA', 'code' => '224'),
            'GQ' => array('name' => 'EQUATORIAL GUINEA', 'code' => '240'),
            'GR' => array('name' => 'GREECE', 'code' => '30'),
            'GT' => array('name' => 'GUATEMALA', 'code' => '502'),
            'GU' => array('name' => 'GUAM', 'code' => '1671'),
            'GW' => array('name' => 'GUINEA-BISSAU', 'code' => '245'),
            'GY' => array('name' => 'GUYANA', 'code' => '592'),
            'HK' => array('name' => 'HONG KONG', 'code' => '852'),
            'HN' => array('name' => 'HONDURAS', 'code' => '504'),
            'HR' => array('name' => 'CROATIA', 'code' => '385'),
            'HT' => array('name' => 'HAITI', 'code' => '509'),
            'HU' => array('name' => 'HUNGARY', 'code' => '36'),
            'ID' => array('name' => 'INDONESIA', 'code' => '62'),
            'IE' => array('name' => 'IRELAND', 'code' => '353'),
            'IL' => array('name' => 'ISRAEL', 'code' => '972'),
            'IM' => array('name' => 'ISLE OF MAN', 'code' => '44'),
            'IN' => array('name' => 'INDIA', 'code' => '91'),
            'IQ' => array('name' => 'IRAQ', 'code' => '964'),
            'IR' => array('name' => 'IRAN, ISLAMIC REPUBLIC OF', 'code' => '98'),
            'IS' => array('name' => 'ICELAND', 'code' => '354'),
            'IT' => array('name' => 'ITALY', 'code' => '39'),
            'JM' => array('name' => 'JAMAICA', 'code' => '1876'),
            'JO' => array('name' => 'JORDAN', 'code' => '962'),
            'JP' => array('name' => 'JAPAN', 'code' => '81'),
            'KE' => array('name' => 'KENYA', 'code' => '254'),
            'KG' => array('name' => 'KYRGYZSTAN', 'code' => '996'),
            'KH' => array('name' => 'CAMBODIA', 'code' => '855'),
            'KI' => array('name' => 'KIRIBATI', 'code' => '686'),
            'KM' => array('name' => 'COMOROS', 'code' => '269'),
            'KN' => array('name' => 'SAINT KITTS AND NEVIS', 'code' => '1869'),
            'KP' => array('name' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF', 'code' => '850'),
            'KR' => array('name' => 'KOREA REPUBLIC OF', 'code' => '82'),
            'KW' => array('name' => 'KUWAIT', 'code' => '965'),
            'KY' => array('name' => 'CAYMAN ISLANDS', 'code' => '1345'),
            'KZ' => array('name' => 'KAZAKSTAN', 'code' => '7'),
            'LA' => array('name' => 'LAO PEOPLES DEMOCRATIC REPUBLIC', 'code' => '856'),
            'LB' => array('name' => 'LEBANON', 'code' => '961'),
            'LC' => array('name' => 'SAINT LUCIA', 'code' => '1758'),
            'LI' => array('name' => 'LIECHTENSTEIN', 'code' => '423'),
            'LK' => array('name' => 'SRI LANKA', 'code' => '94'),
            'LR' => array('name' => 'LIBERIA', 'code' => '231'),
            'LS' => array('name' => 'LESOTHO', 'code' => '266'),
            'LT' => array('name' => 'LITHUANIA', 'code' => '370'),
            'LU' => array('name' => 'LUXEMBOURG', 'code' => '352'),
            'LV' => array('name' => 'LATVIA', 'code' => '371'),
            'LY' => array('name' => 'LIBYAN ARAB JAMAHIRIYA', 'code' => '218'),
            'MA' => array('name' => 'MOROCCO', 'code' => '212'),
            'MC' => array('name' => 'MONACO', 'code' => '377'),
            'MD' => array('name' => 'MOLDOVA, REPUBLIC OF', 'code' => '373'),
            'ME' => array('name' => 'MONTENEGRO', 'code' => '382'),
            'MF' => array('name' => 'SAINT MARTIN', 'code' => '1599'),
            'MG' => array('name' => 'MADAGASCAR', 'code' => '261'),
            'MH' => array('name' => 'MARSHALL ISLANDS', 'code' => '692'),
            'MK' => array('name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'code' => '389'),
            'ML' => array('name' => 'MALI', 'code' => '223'),
            'MM' => array('name' => 'MYANMAR', 'code' => '95'),
            'MN' => array('name' => 'MONGOLIA', 'code' => '976'),
            'MO' => array('name' => 'MACAU', 'code' => '853'),
            'MP' => array('name' => 'NORTHERN MARIANA ISLANDS', 'code' => '1670'),
            'MR' => array('name' => 'MAURITANIA', 'code' => '222'),
            'MS' => array('name' => 'MONTSERRAT', 'code' => '1664'),
            'MT' => array('name' => 'MALTA', 'code' => '356'),
            'MU' => array('name' => 'MAURITIUS', 'code' => '230'),
            'MV' => array('name' => 'MALDIVES', 'code' => '960'),
            'MW' => array('name' => 'MALAWI', 'code' => '265'),
            'MX' => array('name' => 'MEXICO', 'code' => '52'),
            'MY' => array('name' => 'MALAYSIA', 'code' => '60'),
            'MZ' => array('name' => 'MOZAMBIQUE', 'code' => '258'),
            'NA' => array('name' => 'NAMIBIA', 'code' => '264'),
            'NC' => array('name' => 'NEW CALEDONIA', 'code' => '687'),
            'NE' => array('name' => 'NIGER', 'code' => '227'),
            'NG' => array('name' => 'NIGERIA', 'code' => '234'),
            'NI' => array('name' => 'NICARAGUA', 'code' => '505'),
            'NL' => array('name' => 'NETHERLANDS', 'code' => '31'),
            'NO' => array('name' => 'NORWAY', 'code' => '47'),
            'NP' => array('name' => 'NEPAL', 'code' => '977'),
            'NR' => array('name' => 'NAURU', 'code' => '674'),
            'NU' => array('name' => 'NIUE', 'code' => '683'),
            'NZ' => array('name' => 'NEW ZEALAND', 'code' => '64'),
            'OM' => array('name' => 'OMAN', 'code' => '968'),
            'PA' => array('name' => 'PANAMA', 'code' => '507'),
            'PE' => array('name' => 'PERU', 'code' => '51'),
            'PF' => array('name' => 'FRENCH POLYNESIA', 'code' => '689'),
            'PG' => array('name' => 'PAPUA NEW GUINEA', 'code' => '675'),
            'PH' => array('name' => 'PHILIPPINES', 'code' => '63'),
            'PK' => array('name' => 'PAKISTAN', 'code' => '92'),
            'PL' => array('name' => 'POLAND', 'code' => '48'),
            'PM' => array('name' => 'SAINT PIERRE AND MIQUELON', 'code' => '508'),
            'PN' => array('name' => 'PITCAIRN', 'code' => '870'),
            'PR' => array('name' => 'PUERTO RICO', 'code' => '1'),
            'PT' => array('name' => 'PORTUGAL', 'code' => '351'),
            'PW' => array('name' => 'PALAU', 'code' => '680'),
            'PY' => array('name' => 'PARAGUAY', 'code' => '595'),
            'QA' => array('name' => 'QATAR', 'code' => '974'),
            'RO' => array('name' => 'ROMANIA', 'code' => '40'),
            'RS' => array('name' => 'SERBIA', 'code' => '381'),
            'RU' => array('name' => 'RUSSIAN FEDERATION', 'code' => '7'),
            'RW' => array('name' => 'RWANDA', 'code' => '250'),
            'SA' => array('name' => 'SAUDI ARABIA', 'code' => '966'),
            'SB' => array('name' => 'SOLOMON ISLANDS', 'code' => '677'),
            'SC' => array('name' => 'SEYCHELLES', 'code' => '248'),
            'SD' => array('name' => 'SUDAN', 'code' => '249'),
            'SE' => array('name' => 'SWEDEN', 'code' => '46'),
            'SG' => array('name' => 'SINGAPORE', 'code' => '65'),
            'SH' => array('name' => 'SAINT HELENA', 'code' => '290'),
            'SI' => array('name' => 'SLOVENIA', 'code' => '386'),
            'SK' => array('name' => 'SLOVAKIA', 'code' => '421'),
            'SL' => array('name' => 'SIERRA LEONE', 'code' => '232'),
            'SM' => array('name' => 'SAN MARINO', 'code' => '378'),
            'SN' => array('name' => 'SENEGAL', 'code' => '221'),
            'SO' => array('name' => 'SOMALIA', 'code' => '252'),
            'SR' => array('name' => 'SURINAME', 'code' => '597'),
            'ST' => array('name' => 'SAO TOME AND PRINCIPE', 'code' => '239'),
            'SV' => array('name' => 'EL SALVADOR', 'code' => '503'),
            'SY' => array('name' => 'SYRIAN ARAB REPUBLIC', 'code' => '963'),
            'SZ' => array('name' => 'SWAZILAND', 'code' => '268'),
            'TC' => array('name' => 'TURKS AND CAICOS ISLANDS', 'code' => '1649'),
            'TD' => array('name' => 'CHAD', 'code' => '235'),
            'TG' => array('name' => 'TOGO', 'code' => '228'),
            'TH' => array('name' => 'THAILAND', 'code' => '66'),
            'TJ' => array('name' => 'TAJIKISTAN', 'code' => '992'),
            'TK' => array('name' => 'TOKELAU', 'code' => '690'),
            'TL' => array('name' => 'TIMOR-LESTE', 'code' => '670'),
            'TM' => array('name' => 'TURKMENISTAN', 'code' => '993'),
            'TN' => array('name' => 'TUNISIA', 'code' => '216'),
            'TO' => array('name' => 'TONGA', 'code' => '676'),
            'TR' => array('name' => 'TURKEY', 'code' => '90'),
            'TT' => array('name' => 'TRINIDAD AND TOBAGO', 'code' => '1868'),
            'TV' => array('name' => 'TUVALU', 'code' => '688'),
            'TW' => array('name' => 'TAIWAN, PROVINCE OF CHINA', 'code' => '886'),
            'TZ' => array('name' => 'TANZANIA, UNITED REPUBLIC OF', 'code' => '255'),
            'UA' => array('name' => 'UKRAINE', 'code' => '380'),
            'UG' => array('name' => 'UGANDA', 'code' => '256'),
            'US' => array('name' => 'UNITED STATES', 'code' => '1'),
            'UY' => array('name' => 'URUGUAY', 'code' => '598'),
            'UZ' => array('name' => 'UZBEKISTAN', 'code' => '998'),
            'VA' => array('name' => 'HOLY SEE (VATICAN CITY STATE)', 'code' => '39'),
            'VC' => array('name' => 'SAINT VINCENT AND THE GRENADINES', 'code' => '1784'),
            'VE' => array('name' => 'VENEZUELA', 'code' => '58'),
            'VG' => array('name' => 'VIRGIN ISLANDS, BRITISH', 'code' => '1284'),
            'VI' => array('name' => 'VIRGIN ISLANDS, U.S.', 'code' => '1340'),
            'VN' => array('name' => 'VIET NAM', 'code' => '84'),
            'VU' => array('name' => 'VANUATU', 'code' => '678'),
            'WF' => array('name' => 'WALLIS AND FUTUNA', 'code' => '681'),
            'WS' => array('name' => 'SAMOA', 'code' => '685'),
            'XK' => array('name' => 'KOSOVO', 'code' => '381'),
            'YE' => array('name' => 'YEMEN', 'code' => '967'),
            'YT' => array('name' => 'MAYOTTE', 'code' => '262'),
            'ZA' => array('name' => 'SOUTH AFRICA', 'code' => '27'),
            'ZM' => array('name' => 'ZAMBIA', 'code' => '260'),
            'ZW' => array('name' => 'ZIMBABWE', 'code' => '263')
        );


        $countries_with_codes = [];
        foreach ($countryArray as $code => $country) {
            $countryName = ucwords(strtolower($country["name"]));
            $country["description"] = $countryName . " (+" . $country["code"] . ")";
            $country['iso2'] = $code;

            $countries_with_codes[] = $country;
        }
        return $this->response(['success' => TRUE, 'countries' => $countries_with_codes], REST_Controller::HTTP_OK);
    }
}
