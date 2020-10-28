<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packagestats extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(

            'backend/package/packagestats_model',
            'customer/packageconfirmed_model',
            'customer/transections_model',
            'common_model',
        ));

        if (!$this->session->userdata('isAdmin'))
            redirect('logout');

        if (
            !$this->session->userdata('isLogin')
            && !$this->session->userdata('isAdmin')
        )
            redirect('admin');
    }

    public function package_list()
    {
        $data['title'] = "Packages List";
        #-------------------------------#
        #
        #pagination starts
        #
        $config["base_url"] = base_url('backend/package/packagestats/package_list');
        $config["total_rows"] = $this->db->get_where('pending_package_buying', array('status' => 2))->num_rows();
        $config["per_page"] = 25;
        $config["uri_segment"] = 5;
        $config["last_link"] = "Last";
        $config["first_link"] = "First";
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Prev';
        $config['full_tag_open'] = "<ul class='pagination col-xs pull-right'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        /* ends of bootstrap */
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;
        $data['confirmedPackages'] = $this->db->select('*')->from('pending_package_buying')
            ->where('status', 2)
            // ->where('deposit_method', 'phone')
            ->limit($config["per_page"], $page)
            ->get()
            ->result();
        $data["links"] = $this->pagination->create_links();
        #
        #pagination ends
        #    
        $data['content'] = $this->load->view("backend/package/pendinglist", $data, true);
        $this->load->view("backend/layout/main_wrapper", $data);
    }


    public function pending_package()
    {
        $data['title'] = "Pending Packages List";
        #-------------------------------#
        #
        #pagination starts
        #
        $config["base_url"] = base_url('backend/package/packagestats/pending_package');
        $config["total_rows"] = $this->db->get_where('pending_package_buying', array('status' => 1))->num_rows();
        $config["per_page"] = 25;
        $config["uri_segment"] = 5;
        $config["last_link"] = "Last";
        $config["first_link"] = "First";
        $config['next_link'] = 'Next';
        $config['prev_link'] = 'Prev';
        $config['full_tag_open'] = "<ul class='pagination col-xs pull-right'>";
        $config['full_tag_close'] = "</ul>";
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = "<li class='disabled'><li class='active'><a href='#'>";
        $config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
        $config['next_tag_open'] = "<li>";
        $config['next_tag_close'] = "</li>";
        $config['prev_tag_open'] = "<li>";
        $config['prev_tagl_close'] = "</li>";
        $config['first_tag_open'] = "<li>";
        $config['first_tagl_close'] = "</li>";
        $config['last_tag_open'] = "<li>";
        $config['last_tagl_close'] = "</li>";
        /* ends of bootstrap */
        $this->pagination->initialize($config);
        $page = ($this->uri->segment(5)) ? $this->uri->segment(5) : 0;
        $data['pendingPackages'] = $this->db->select('*')->from('pending_package_buying')
            ->where('status', 1)
            // ->where('deposit_method', 'phone')
            ->limit($config["per_page"], $page)
            ->get()
            ->result();
        $data["links"] = $this->pagination->create_links();
        #
        #pagination ends
        #

        $data['content'] = $this->load->view("backend/package/pendinglist", $data, true);
        $this->load->view("backend/layout/main_wrapper", $data);
    }


    public function confirm_package()
    {
        $set_status = $_GET['set_status'];
        $user_id = $_GET['user_id'];
        $id = $_GET['id'];
        $data = array(
            'status' => $set_status,
        );

        $this->db->where('pending_package_id', $id)->where('user_id', $user_id)->update('pending_package_buying', $data);

        $data = $this->db->select('*')->from('pending_package_buying')->where('pending_package_id', $id)->get()->row();
        $userdata = $this->db->select('*')->from('user_registration')->where('user_id', $user_id)->get()->row();

        if ($data != NULL) {

            $transections_data = array(
                'user_id'                   => $data->user_id,
                'transection_category'      => 'investment',
                'releted_id'                => $data->pending_package_id,
                'amount'                    => $data->buy_amount,
                // 'comments'                  => "Deposite by OM Mobile",
                'transection_date_timestamp' => date('Y-m-d h:i:s')
            );
            $this->packageconfirmed_model->save_transections($transections_data);
        }

        $set = $this->common_model->email_sms('email');
        $appSetting = $this->common_model->get_setting();
        #-----------------------------------------------------
        $balance = $this->transections_model->get_cata_wais_transections($userdata->user_id);


        #-----------------------------------------------------
        if ($set->deposit != NULL) {
            #----------------------------
            #      email verify smtp
            #----------------------------
            $post = array(
                'title'           => $appSetting->title,
                'subject'           => 'Deposit',
                'to'                => $userdata->email,
                'message'           => 'You successfully deposit the amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
            );
            $send_email = $this->common_model->send_email($post);

            if ($send_email) {
                $n = array(
                    'user_id'                => $userdata->user_id,
                    'subject'                => display('diposit'),
                    'notification_type'      => 'deposit',
                    'details'                => 'You successfully deposit The amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
                    'date'                   => date('Y-m-d h:i:s'),
                    'status'                 => '1'
                );
                $this->db->insert('notifications', $n);
            }

            $this->load->library('sms_lib');
            $template = array(
                'name'       => $userdata->f_name . " " . $userdata->l_name,
                'amount'     => $data->deposit_amount,
                'new_balance' => $balance['balance'],
                'date'       => date('d F Y')
            );

            #------------------------------
            #   SMS Sending
            #------------------------------
            $send_sms = $this->sms_lib->send(array(
                'to'              => $userdata->phone,
                'header'         => 'Package Buying',
                'template'        => 'You successfully deposit the amount $%amount% . Your new balance is $%new_balance%.',
                'template_config' => $template,
            ));

            if ($send_sms) {

                $message_data = array(
                    'sender_id' => 1,
                    'receiver_id' => $userdata->user_id,
                    'subject' => 'Deposit',
                    'message' => 'You successfully deposit the amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
                    'datetime' => date('Y-m-d h:i:s'),
                );

                $this->db->insert('message', $message_data);
            }
        }



        redirect('backend/package/packagestats/pending_package');
    }


    public function cancel_package()
    {
        $set_status = $_GET['set_status'];
        $user_id = $_GET['user_id'];
        $id = $_GET['id'];

        $data = array(
            'status' => $set_status,
        );


        $this->db->where('pending_package_id', $id)
            ->where('user_id', $user_id)
            ->update('pending_package_buying', $data);

        redirect('backend/package/packagestats/pending_package');
    }
}
