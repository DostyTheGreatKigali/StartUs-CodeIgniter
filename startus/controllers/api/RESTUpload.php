<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Upload extends REST_Controller
{

    public function __construct()
    {
        // print_r($_POST);
        // die();
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    // public function index()
    // {
    //     $this->load->view('upload_form', array('error' => ' '));
    // }

    public function index_post()
    {
        // print_r($_POST);
        // die();

        $config['upload_path']          = './upload/api/users';
        //  print_r($_POST);
        // die();
        $config['allowed_types']        = 'gif|jpg|png';
        //  print_r($_POST);
        // die();
        $config['max_size']             = 1024;
        //  print_r($_POST);
        // die();
        // $config['max_width']            = 1024;
        //  print_r($_POST);
        // die();
        // $config['max_height']           = 768;
        //  print_r($_POST);
        // die();

        $this->load->library('upload', $config);
        //   $this->upload->initialize($config);
        //  print_r($_POST);
        // die();

        if (!$this->upload->do_upload('userfile')) {
            $error = array('error' => $this->upload->display_errors());
            //  print_r($_POST);
            // print_r($error);
            // die();
            return $this->response(['Error uploading file'], REST_Controller::HTTP_OK);
            //      print_r($_POST);
            // die();

            // $this->load->view('upload_form', $error);
        } else {
            //      print_r($_POST);
            // die();
            try {
                $this->data = array('upload_data' => $this->upload->data());
                //  print_r($_POST);
                //         die();
                // $this->load->view('upload_success', $data);
                return $this->response(['Successfully uploaded'], REST_Controller::HTTP_OK);
                //      print_r($_POST);
                // die(); 
            } catch (Exception $e) {
                var_dump($e->getMessage());
            }
        }

        // try {
        //     $this->data['important'] = $this->Test_Model->do_something($data);
        //     if (empty($this->data['important'])) {
        //         throw new Exception('no data returned');
        //     }
        // } catch (Exception $e) {
        //     //alert the user.
        //     var_dump($e->getMessage());
        // }
    }



    // public function product_post()
    // {
    //     $uploaddir = './uploads/products/';
    //     $file_name = underscore($_FILES['file']['name']);
    //     $uploadfile = $uploaddir . $file_name;

    //     if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    //         $dataDB['status'] = 'success';
    //         $dataDB['filename'] = $file_name;
    //     } else {
    //         $dataDB['status'] =  'failure';
    //     }
    //     $this->response($dataDB, 200);
    // }

}
