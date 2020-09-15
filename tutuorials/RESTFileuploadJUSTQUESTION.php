<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . './libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Update_image extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $models[] = 'Quiz_model';
        $models[] = 'Users_model';
        $this->load->model($models);
        $libs[] =  'form_validation';
        $libs[] =  'image_lib';
        $libs[] =  'email';
        //$libs[] =  'rest';
        $this->load->library($libs);
        $helpers[] = 'file';
        $helpers[] = 'security';
        $helpers[] = 'url';
        $helpers[] = 'string';
        $this->load->helper($helpers);
    }
    function index_post()
    {

        $users_id = $this->input->post('users_id');
        $users_id = 6;
        $config['upload_path']   = './public/user_images/';
        $config['allowed_types'] = 'jpg|png';
        $config['max_size']      = 20; // for 5mb file
        $config['max_width']     = 5000;
        $config['max_height']    = 5000;
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('users_image')) {
            $error = array('error' => $this->upload->display_errors('<span>', '<span>'));
            $data['msg'] = error('Please Fix the errors below');
            $data['file_upload_error'] = formErrorParse($this->upload->display_errors());
            $this->form_validation->set_message('quiz_image', implode('<br/>', $error));
            $response['status'] = 0;
            $response['msg'] = strip_tags($this->upload->display_errors());
        } else {
            $uploadStat = $this->upload->data();
            $img_config['image_library'] = 'gd2';
            $img_config['source_image'] = './public/uploads/user_images/ '  . $uploadStat['file_name'];
            $img_config['new_image'] = './public/uploads/user_images/thumbnail/ '  . $uploadStat['file_name'];
            //$img_config['create_thumb'] = TRUE;
            $img_config['maintain_ratio'] = TRUE;
            $img_config['width']     = 150;
            $img_config['height']   = 150;
            $this->image_lib->clear();
            $this->image_lib->initialize($img_config);
            $this->image_lib->resize();
            $updates['users_images'] = $uploadStat['file_name'];
            $cond['users_id'] = $users_id;
            $this->db->update('users', $updates, $cond);
            $response['status'] = 1;
            $response['msg'] = 'Image Uploaded';
        }
        $this->response($response, 200);
    }
}
