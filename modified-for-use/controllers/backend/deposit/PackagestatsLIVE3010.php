<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Packagestats extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(

            'customer/package_model',
            'backend/package/packagestats_model',
            'customer/packageconfirmed_model',
            'customer/transections_model',
            'common_model',
            'customer/diposit_model',
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
        $data['packages'] = $this->db->select('*')->from('pending_package_buying')
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
        $data['packages'] = $this->db->select('*')->from('pending_package_buying')
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


        // Prevent admin from approving the same package twice
        // Get the package info
        $package_data = $this->db->select('*')->from('pending_package_buying')->where('pending_package_id', $id)->get()->row();
        $userdata = $this->db->select('*')->from('user_registration')->where('user_id', $user_id)->get()->row();
        $user_data = $userdata;
        $sponsor_id = $userdata->sponsor_id;

        if ($package_data != NULL) {
            // data exists now update it, check its status before approving
            if ($package_data->status != 2) {
                $this->db->where('pending_package_id', $id)->where('user_id', $user_id)->update('pending_package_buying', $data);
            }

            // Admin approved
            if ($package_data->status != 2 && $set_status == 2) {
                $date           = new DateTime();
                $deposit_date   = $date->format('Y-m-d H:i:s');

                $sdata['deposit']   = (object)$userdata = array(
                    'deposit_id'        => @$deposit['deposit_id'],
                    'user_id'           => $user_id,
                    'deposit_amount'    => $package_data->buy_amount,
                    'deposit_method'    => 'coin', //$this->input->post('method', TRUE),
                    'fees'              => 0, //$this->input->post('fees', TRUE),
                    'comments'          => 'Package Deposit creation with coins',
                    'deposit_date'      => $deposit_date,
                    'deposit_ip'        => $this->input->ip_address(),
                    'status'            => $set_status
                );
                $deposit = $this->diposit_model->save_deposit($sdata['deposit']);
                // var_dump($deposit); 
                // Grab your deposit id after creation
                $deposit_id = $deposit->deposit_id;


                if ($deposit != NULL) {

                    $transections_data = array(
                        'user_id'                   => $user_id,
                        'transection_category'      => 'deposit',
                        'releted_id'                => $deposit->deposit_id,
                        'amount'                    => $package_data->buy_amount,
                        'comments'                  => "Deposite by OM Mobile",
                        'transection_date_timestamp' => date('Y-m-d h:i:s')
                    );
                    $this->diposit_model->save_transections($transections_data);


                    $package_id = $package_data->package_id; //input->post('package_id');

                    // $blance = $this->transections_model->get_cata_wais_transections($userdata->user_id);
                    // $blance = $this->check_balance($package_id, $user_id);

                    // Deposit has just been made so there is balance
                    // if($blance!=NULL){
                    //   return $this->response($this->check_investment($user_id), REST_Controller::HTTP_OK);

                    if ($this->check_investment($user_id) == '') {
                        $saveLevel = array(
                            'user_id'           => $user_id,
                            'sponser_commission' => 0.0,
                            'team_commission'   => 0.0,
                            'level'             => 1,
                            'last_update'       => date('Y-m-d h:i:s')
                        );

                        /*********************************
                         *   Data Store Details Table
                         **********************************/
                        $this->db->insert('team_bonus_details', $saveLevel);
                        $this->db->insert('team_bonus', $saveLevel);
                    }

                    $buy_data = array(
                        'user_id'       => $user_id,
                        'sponsor_id'    => $sponsor_id,
                        'package_id'    => $package_id,
                        'amount'        => $package_data->buy_amount,
                        'invest_date'   => date('Y-m-d'),
                        'day'           => date('N'),
                        'status'            => $set_status,
                    );

                    $result = $this->package_model->buy_package($buy_data);

                    // check investment by customent
                    $customent_investment = $this->db->select('*')->from('investment')->where('user_id', $user_id)->get()->num_rows();


                    // $sponsor_id = $this->session->userdata('sponsor_id');
                    // get sponsert information by id
                    $sponsers_info = $this->db->select('*')->from('user_registration')->where('user_id', $sponsor_id)->get()->row();
                    // check invesment by sponser
                    $investment = $this->db->select('*')->from('investment')->where('user_id', $sponsor_id)->get()->num_rows();


                    if ($sponsor_id != NULL) {

                        if ($investment > 0) {
                            // get package informaion by package id
                            // Package already obtained from $package_data
                            // $pack_info = $this->package_model->package_info_by_id($package_id);
                            #--------------------------------
                            #    commission data save
                            $commission_amount = ($package_data->buy_amount / 100) * 6;

                            $commission = array(

                                'user_id'       => $sponsor_id,
                                'Purchaser_id'  => $user_id,
                                'earning_type'  => 'type1',
                                'package_id'    => $package_id,
                                'amount'        => $commission_amount,
                                'date'          => date('Y-m-d'),

                            );

                            $this->db->insert('earnings', $commission);
                            #   end commission
                            #---------------------------------

                            //get total balance
                            $balance = $this->common_model->get_all_transection_by_user($sponsers_info->user_id);
                            $new_balance = ($balance['balance'] + $commission_amount);
                            #----------------------------
                            # sms send to commission received
                            #----------------------------

                            // $this->load->library('sms_lib');
                            // $template = array( 
                            //     'name'      => $sponsers_info->f_name.' '.$sponsers_info->l_name,
                            //     'amount'    => $commission_amount,
                            //     'new_balance'=> $new_balance,
                            //     'date'      => date('d F Y')
                            // );

                            // $send_sms = $this->sms_lib->send(array(

                            //     'to'              => $sponsers_info->phone, 
                            //     'template'        => 'You received a referral commission of $%amount% . Your new balance is $%new_balance%', 
                            //     'template_config' => $template,

                            // ));

                            // #----------------------------------
                            // #   sms insert to received commission
                            // #---------------------------------
                            // if($send_sms){
                            //     $message_data = array(
                            //         'sender_id' =>1,
                            //         'receiver_id' => $sponsers_info->user_id,
                            //         'subject' => 'Commission',
                            //         'message' => 'You received a referral commission of $'.$commission_amount.'. Your new balance is $'.$new_balance,
                            //         'datetime' => date('Y-m-d h:i:s'),
                            //     );

                            //     $this->db->insert('message',$message_data);
                            // }
                            // #-------------------------------------     


                            #---------------------------------
                            #   Won Sponser set personal and team commission set heare

                            $sponsers = $this->db->select('*')
                                ->from('team_bonus')
                                ->where('user_id', $sponsor_id)
                                ->get()
                                ->row();

                            if ($sponsers != NULL) {

                                $scom = @$sponsers->sponser_commission + $package_data->buy_amount;
                                $tcom = @$sponsers->team_commission + $package_data->buy_amount;
                                $sdata = array(
                                    'sponser_commission' => $scom,
                                    'team_commission' => $tcom,
                                    'last_update' => date('Y-m-d h:i:s')
                                );
                                $detailsdata = array(
                                    'user_id' => $sponsor_id,
                                    'sponser_commission' => $scom,
                                    'team_commission' => $tcom,
                                    'last_update' => date('Y-m-d h:i:s')
                                );




                                /******************************
                                 *   Data Store Details Table
                                 ******************************/
                                $this->db->insert('team_bonus_details', $detailsdata);


                                $this->db->where('user_id', $sponsor_id)->update('team_bonus', $sdata);
                            } else {

                                $scom = @$sponsers->sponser_commission + $package_data->buy_amount;
                                $tcom = @$sponsers->team_commission + $package_data->buy_amount;

                                $sdata = array(
                                    'user_id' => $sponsor_id,
                                    'sponser_commission' => $scom,
                                    'team_commission' => $tcom,
                                    'last_update' => date('Y-m-d h:i:s')
                                );

                                /******************************
                                 *   Data Store Details Table
                                 ******************************/
                                $this->db->insert('team_bonus_details', $sdata);
                                $this->db->insert('team_bonus', $sdata);
                            }

                            # END 
                            #---------------------------------

                            #-----------------------------
                            # Level bonus heare with level
                            $getBool = $this->setlevel_withbonus($sponsor_id);
                            #-----------------------------

                            #-----------------------------------
                            # Leveling heare without level bonus
                            // if($getBool!=TRUE){
                            //     $this->setUserLevel($sponsor_id);
                            // }
                            #
                            #-----------------------------------

                            #---------------------------------
                            #    sponser leveling check and set commission
                            $lc = $this->db->select('user_id,level')
                                ->from('team_bonus')
                                ->where('user_id', $sponsor_id)
                                ->where('level >=', 2)
                                ->get()
                                ->row();

                            if (@$lc != NULL) {

                                $referral_bonous = $this->db->select('*')
                                    ->from('setup_commission')
                                    ->where('level_name', 1)
                                    ->get()
                                    ->row();

                                $commission_amount = ($package_data->buy_amount / 100) * $referral_bonous->referral_bonous;

                                $commission = array(
                                    'user_id'       => $sponsor_id,
                                    'Purchaser_id'  => $user_id,
                                    'earning_type'  => 'type1',
                                    'package_id'    => $package_id,
                                    'amount'        => $commission_amount,
                                    'date'          => date('Y-m-d'),
                                );

                                $this->db->insert('earnings', $commission);
                            }
                        }
                        #
                        #-----------------------------------

                        #--------------------------------
                        #
                        $tuSdata = array(

                            'genaretion'    => 2,
                            'package_id'    => $package_id,
                            'amount'        => $package_data->buy_amount,
                            'sponsor_id'    => $sponsor_id
                        );

                        $this->recursive_data($tuSdata, $package_data, $user_id);
                        #
                        #--------------------------------

                    }

                    if ($result != NULL) {
                        $transections_data = array(
                            'user_id'                   => $user_id,
                            'transection_category'      => 'investment',
                            'releted_id'                => $result['investment_id'],
                            'amount'                    => $package_data->buy_amount,
                            'transection_date_timestamp' => date('Y-m-d h:i:s')
                        );
                        // var_dump($transections_datatransections_data);

                        $this->transections_model->save_transections($transections_data);
                        #----------------------------
                        # sms send to commission received
                        #----------------------------

                        // $this->load->library('sms_lib');

                        // $template = array( 
                        //     'name'      => $user_data->f_name.' '.$user_data->l_name,
                        //     'amount'    =>$package_data->buy_amount,
                        //     'date'      => date('d F Y')
                        // );

                        // $send_sms = $this->sms_lib->send(array(
                        //     'to'              => $user_data->phone, 
                        //     'template'        => 'You bought a $%amount% package successfully', 
                        //     'template_config' => $template, 
                        // ));

                        // #----------------------------------
                        // #   sms insert to received commission
                        // #---------------------------------
                        // if($send_sms){

                        //     $message_data = array(
                        //         'sender_id' =>1,
                        //         'receiver_id' => $user_id,
                        //         'subject' => 'Package Buy',
                        //         'message' => 'You bought a '.$package_data->buy_amount.' package successfully',
                        //         'datetime' => date('Y-m-d h:i:s'),
                        //     );

                        //     $this->db->insert('message',$message_data);
                        // }
                        // #------------------------------------- 

                        // $set = $this->common_model->email_sms('email');
                        // $appSetting = $this->common_model->get_setting();
                        //     #----------------------------
                        //     #      email verify smtp
                        //     #----------------------------
                        //      $post = array(
                        //         'title'           => $appSetting->title,
                        //         'subject'           => 'Package Buy',
                        //         'to'                => $user_data->email,
                        //         'message'           => 'You bought a '.$package_data->buy_amount.' package successfully',
                        //     );
                        //     $send_email = $this->common_model->send_email($post);

                        //     if($send_email){
                        //             $n = array(
                        //             'user_id'                => $user_id,
                        //             'subject'                => 'Package Buy',
                        //             'notification_type'      => 'package_by',
                        //             'details'                => 'You bought a '.$package_data->buy_amount.' package successfully',
                        //             'date'                   => date('Y-m-d h:i:s'),
                        //             'status'                 => '0'
                        //         );
                        //         $this->db->insert('notifications',$n);    
                        //     }


                    }
                    // return $this->response(['success'=> TRUE, 'message'=> display('package_buy_successfully')], REST_Controller::HTTP_OK);

                    // $this->session->set_flashdata('message', display('package_buy_successfully'));
                    // redirect('customer/package/buy_success/'.$package_id.'/'.$result['investment_id']);

                    // } else{
                    //     return $this->response(['success'=> FALSE, 'message'=> display('balance_is_unavailable')], REST_Controller::HTTP_OK);

                    //         // $this->session->set_flashdata('exception', display('balance_is_unavailable'));
                    //         // redirect('customer/package/confirm_package/'.$package_id);

                    // }
                }
            } // end if status == 2

            // // Data is updated. Now check if status is approved 3
            // $transections_data = array(
            //     'user_id'                   => $data->user_id,
            //     'transection_category'      => 'investment',
            //     'releted_id'                => $data->pending_package_id,
            //     'amount'                    => $data->buy_amount,
            //     // 'comments'                  => "Deposite by OM Mobile",
            //     'transection_date_timestamp' => date('Y-m-d h:i:s')
            // );
            // $this->packageconfirmed_model->save_transections($transections_data);
        }

        // $set = $this->common_model->email_sms('email');
        // $appSetting = $this->common_model->get_setting();
        // #-----------------------------------------------------
        // $balance = $this->transections_model->get_cata_wais_transections($userdata->user_id);


        // #-----------------------------------------------------
        // if ($set->deposit != NULL) {
        //     #----------------------------
        //     #      email verify smtp
        //     #----------------------------
        //     $post = array(
        //         'title'           => $appSetting->title,
        //         'subject'           => 'Deposit',
        //         'to'                => $userdata->email,
        //         'message'           => 'You successfully deposit the amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
        //     );
        //     $send_email = $this->common_model->send_email($post);

        //     if ($send_email) {
        //         $n = array(
        //             'user_id'                => $userdata->user_id,
        //             'subject'                => display('diposit'),
        //             'notification_type'      => 'deposit',
        //             'details'                => 'You successfully deposit The amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
        //             'date'                   => date('Y-m-d h:i:s'),
        //             'status'                 => '1'
        //         );
        //         $this->db->insert('notifications', $n);
        //     }

        //     $this->load->library('sms_lib');
        //     $template = array(
        //         'name'       => $userdata->f_name . " " . $userdata->l_name,
        //         'amount'     => $data->deposit_amount,
        //         'new_balance' => $balance['balance'],
        //         'date'       => date('d F Y')
        //     );

        //     #------------------------------
        //     #   SMS Sending
        //     #------------------------------
        //     $send_sms = $this->sms_lib->send(array(
        //         'to'              => $userdata->phone,
        //         'header'         => 'Package Buying',
        //         'template'        => 'You successfully deposit the amount $%amount% . Your new balance is $%new_balance%.',
        //         'template_config' => $template,
        //     ));

        //     if ($send_sms) {

        //         $message_data = array(
        //             'sender_id' => 1,
        //             'receiver_id' => $userdata->user_id,
        //             'subject' => 'Deposit',
        //             'message' => 'You successfully deposit the amount $' . $data->deposit_amount . '. Your new balance is $' . $balance['balance'],
        //             'datetime' => date('Y-m-d h:i:s'),
        //         );

        //         $this->db->insert('message', $message_data);
        //     }
        // }



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


    /***************************
     *   check customer balance 
     *   @param pacakate id
     *   return array()
     ***************************/
    public function check_balance($package_id = NULL, $user_id)
    {

        $pak_info = $this->package_model->package_info_by_id($package_id);
        $data = $this->transections_model->get_cata_wais_transections($user_id);

        if ($pak_info->package_amount <= $data['balance']) {
            return $pak_info;
        } else {

            return $pak_info = array();
        }
    }

    /***************************
     *   check investment  
     *   @param user Id
     *   return number of rows
     ***************************/
    public function check_investment($user_id = NULL)
    {

        return $this->db->select('*')
            ->from('investment')
            ->where('user_id', $user_id)
            ->get()
            ->num_rows();
    }

    /***************************
     *   SET LEVEL BY SPONSER  
     *   @param sponser id
     *   return ture or false
     ***************************/
    public function setUserLevel($sponsor_id)
    {

        $investment = $this->db->select('*')->from('investment')->where('user_id', $sponsor_id)->get()->num_rows();

        if ($investment != NUll) {

            $sponsers_info = $this->db->select('*')->from('user_registration')->where('user_id', $sponsor_id)->get()->row();
            $sponsers = $this->db->select('*')
                ->from('team_bonus')
                ->where('user_id', $sponsor_id)
                ->get()
                ->row();

            if (@$sponsers->sponser_commission != 0 && @$sponsers->team_commission != 0) {

                $level = @$sponsers->level;
                $setLevel = $this->db->select('*')
                    ->from('setup_commission')
                    ->where('total_invest<=', @$sponsers->team_commission)
                    ->where('level_name', $level)
                    ->get()
                    ->row();

                if ($setLevel != NULL) {

                    $new_level = $level + 1;
                    $data = $this->db->set('level', $new_level)
                        ->where('user_id', $sponsor_id)
                        ->update('team_bonus');

                    $levelChack = $this->db->select('*')->from('user_level')->where('user_id', $sponsor_id)->where('level_id', $level)->get()->row();
                    if (empty($levelChack)) {
                        $level_data = array(
                            'user_id'       => @$sponsor_id,
                            'level_id'      => @$level,
                            'achive_date'   => date('Y-m-d h:i:s'),
                            'bonus'         => 0.0,
                            'status'        => 1,
                        );
                        $this->db->insert('user_level', $level_data);
                    }

                    #----------------------------
                    # sms send for  team bonus
                    #----------------------------
                    $balance2 = $this->common_model->get_all_transection_by_user($sponsor_id);

                    $new_balance2 = $balance2['balance'];

                    $this->load->library('sms_lib');

                    $template = array(
                        'name'          => $sponsers_info->f_name . ' ' . $sponsers_info->l_name,
                        'amount'        => 0.0,
                        'new_balance'   => $new_balance2,
                        'stage'         => $new_level,
                        'date'          => date('d F Y')
                    );

                    $send_sms = $this->sms_lib->send(array(
                        'to'              => $sponsers_info->phone,
                        'template'        => 'Congrats! You received the amount $%amount% for team bonus. Your new balance is $%new_balance% . You are now in Stage %stage%',
                        'template_config' => $template,
                    ));

                    #----------------------------------
                    #   sms insert for team bonus
                    #------------------------------------- 
                    if ($send_sms) {
                        $message_data = array(
                            'sender_id'     => 1,
                            'receiver_id'   => $sponsers_info->user_id,
                            'subject'       => 'Team Bonus',
                            'message'       => 'Congrats! You received the amount $0.0 for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                            'datetime'      => date('Y-m-d h:i:s'),
                        );
                        $this->db->insert('message', $message_data);
                    }

                    #-------------------------------------
                    #      email verify
                    #------------------------------------- 
                    $appSetting = $this->common_model->get_setting();

                    #----------------------------
                    #      email verify smtp
                    #----------------------------
                    $post = array(
                        'title'           => $appSetting->title,
                        'subject'           => 'Team Bonus',
                        'to'                => $sponsers_info->email,
                        'message'           => 'Congrats! You received the amount $0.0 for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                    );
                    $send_email = $this->common_model->send_email($post);

                    if ($send_email) {
                        $n = array(
                            'user_id'                => $sponsers_info->user_id,
                            'subject'                => 'Team Bonus',
                            'notification_type'      => 'Team_Bonus',
                            'details'                => 'Congrats! You received the amount $0.0 for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                            'date'                   => date('Y-m-d h:i:s'),
                            'status'                 => '0'
                        );
                        $this->db->insert('notifications', $n);
                    }

                    return TRUE;
                } else {

                    return FALSE;
                }
            } else {

                return FALSE;
            }
        }
    }

    /*
|   SET LEVEL with Level bonus 
|   @param sponser id
|   return ture or false
*/
    public function setlevel_withbonus($sponsor_id)
    {
        $appSetting = $this->common_model->get_setting();

        $investment = $this->db->select('*')->from('investment')->where('user_id', $sponsor_id)->get()->num_rows();

        if ($investment != NUll) {

            $sponsers_info = $this->db->select('*')->from('user_registration')->where('user_id', $sponsor_id)->get()->row();

            $sponsers2 = $this->db->select('*')
                ->from('team_bonus')
                ->where('user_id', $sponsor_id)
                ->get()
                ->row();

            if (@$sponsers2->sponser_commission != 0 && @$sponsers2->team_commission != 0) {

                $level = @$sponsers2->level;

                $get_commi = $this->db->select('*')
                    ->from('setup_commission')
                    ->where('personal_invest<=', @$sponsers2->sponser_commission)
                    ->where('total_invest<=', @$sponsers2->team_commission)
                    ->where('level_name', $level)
                    ->get()
                    ->row();

                if ($get_commi != NULL) {

                    $new_level = $level + 1;
                    $data = $this->db->set('level', $new_level)
                        ->where('user_id', $sponsor_id)
                        ->update('team_bonus');

                    $level_data = array(
                        'user_id'       => @$sponsor_id,
                        'level_id'      => @$level,
                        'achive_date'   => date('Y-m-d h:i:s'),
                        'bonus'         => @$get_commi->team_bonous,
                        'status'        => 1,
                    );
                    $this->db->insert('user_level', $level_data);

                    #----------------------------
                    # sms send for  team bonus
                    #----------------------------
                    $balance2 = $this->common_model->get_all_transection_by_user($sponsor_id);
                    $new_balance2 = $balance2['balance'] + $get_commi->team_bonous;

                    $this->load->library('sms_lib');

                    $template = array(
                        'name'          => $sponsers_info->f_name . ' ' . $sponsers_info->l_name,
                        'amount'        => $get_commi->team_bonous,
                        'new_balance'   => $new_balance2,
                        'stage'         => $new_level,
                        'date'          => date('d F Y')
                    );

                    $send_sms = $this->sms_lib->send(array(
                        'to'              => $sponsers_info->phone,
                        'template'        => 'Congrats! You received the amount $%amount% for team bonus. Your new balance is $%new_balance% . You are now in Stage %stage%',
                        'template_config' => $template,
                    ));

                    #----------------------------------
                    #   sms insert for team bonus
                    #----------------------------------
                    if ($send_sms) {
                        $message_data = array(
                            'sender_id'     => 1,
                            'receiver_id'   => $sponsers_info->user_id,
                            'subject'       => 'Team Bonus',
                            'message'       => 'Congrats! You received the amount $' . $get_commi->team_bonous . ' for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                            'datetime'      => date('Y-m-d h:i:s'),
                        );
                        $this->db->insert('message', $message_data);
                    }

                    #----------------------------
                    #      email verify smtp
                    #----------------------------
                    $post = array(
                        'title'           => $appSetting->title,
                        'subject'           => 'Team Bonus',
                        'to'                => $sponsers_info->email,
                        'message'           => 'Congrats! You received the amount $' . $get_commi->team_bonous . ' for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                    );
                    $send_email = $this->common_model->send_email($post);

                    if ($send_email) {

                        $n = array(
                            'user_id'                => $sponsers_info->user_id,
                            'subject'                => 'Team Bonus',
                            'notification_type'      => 'Team_Bonus',
                            'details'                => 'Congrats! You received the amount $' . $get_commi->team_bonous . ' for team bonus. Your new balance is $' . $new_balance2 . '. You are now in Stage ' . $new_level,
                            'date'                   => date('Y-m-d h:i:s'),
                            'status'                 => '0'
                        );
                        $this->db->insert('notifications', $n);
                    }

                    return TRUE;
                } else {

                    return FALSE;
                }
            } else {
                return FALSE;
            }
        }
    }


    /**
     * recursive function
     *
     * @param   array  $sp
     * @return   1 and finish recursive function
     * specific package needed because amount is now not tied to the
     * package itself but rather what the input based on range provided 
     * by the package
     */

    public function recursive_data($sp = NULL, $package_data, $user_id)
    {

        $data = $this->db->select('user_id,sponsor_id')
            ->from('user_registration')
            ->where('user_id', @$sp['sponsor_id'])
            ->get()->row();


        if (@$data->sponsor_id != NULL && @$sp['genaretion'] <= 5) {

            $investment = $this->db->select('*')->from('investment')->where('user_id', $data->sponsor_id)->get()->num_rows();


            if ($investment != NUll) {

                $sponsers = $this->db->select('*')
                    ->from('team_bonus')
                    ->where('user_id', $data->sponsor_id)
                    ->get()
                    ->row();

                if ($sponsers != NULL) {
                    $scom = @$sponsers->sponser_commission + @$sp['amount'];
                    $tcom = @$sponsers->team_commission + @$sp['amount'];


                    $detailsdata = array('user_id' => @$data->sponsor_id, 'team_commission' => $tcom, 'sponser_commission' => $scom, 'last_update' => date('Y-m-d h:i:s'));
                    /*
                    |
                    |   Data Store Details Table
                    |
                    */
                    $this->db->insert('team_bonus_details', $detailsdata);

                    $sdata = array('team_commission' => $tcom, 'last_update' => date('Y-m-d h:i:s'));
                    $this->db->where('user_id', @$data->sponsor_id)->update('team_bonus', $sdata);
                } else {

                    //$scom = @$sponsers->sponser_commission + $sp['amount'];
                    $tcom = @$sponsers->team_commission + @$sp['amount'];
                    $sdata = array('user_id' => @$data->sponsor_id, 'team_commission' => $tcom, 'last_update' => date('Y-m-d h:i:s'));

                    /*
                    |
                    |   Data Store Details Table
                    |
                    */
                    $this->db->insert('team_bonus_details', $team_bonus_details);


                    $this->db->insert('team_bonus', $sdata);
                }

                //$this->level_complit($data->sponsor_id);

                #-----------------------------
                # Level bonus heare with level
                $getBool = $this->setlevel_withbonus($data->sponsor_id);
                #-----------------------------

                #-----------------------------------
                # Leveling heare without level bonus
                // if($getBool!=TRUE){
                //     $this->setUserLevel($data->sponsor_id);
                // }
                #
                #-----------------------------------

                #---------------------------------
                #    sponser leveling check and set commission
                $lc = $this->db->select('user_id,level')
                    ->from('team_bonus')
                    ->where('user_id', $data->sponsor_id)
                    ->where('level >=', 2)
                    ->get()
                    ->row();

                if (@$lc != NULL) {

                    $referral_bonous = $this->db->select('*')
                        ->from('setup_commission')
                        ->where('level_name', $lc->level)
                        ->get()
                        ->row();

                    // $pack_info = $this->package_model->package_info_by_id(@$sp['package_id']);

                    // $commission_amount = ($pack_info->package_amount/100)*$referral_bonous->referral_bonous;
                    $commission_amount = ($package_data->buy_amount / 100) * $referral_bonous->referral_bonous;

                    $commission = array(
                        'user_id'       => $data->sponsor_id,
                        'Purchaser_id'  => $user_id,
                        'earning_type'  => 'type1',
                        'package_id'    => @$sp['package_id'],
                        'amount'        => $commission_amount,
                        'date'          => date('Y-m-d'),
                    );

                    $this->db->insert('earnings', $commission);
                }
            }

            $tuSdata = array(
                'genaretion' => @$sp['genaretion'] + 1,
                'amount' => @$sp['amount'],
                'package_id' => @$sp['package_id'],
                'sponsor_id' => @$data->sponsor_id,
            );

            $this->recursive_data($tuSdata, $package_data, $user_id);
        } else {

            return 1;
        }
    }
}
