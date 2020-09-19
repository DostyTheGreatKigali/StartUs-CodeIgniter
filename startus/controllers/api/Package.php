  <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';

    class Package extends REST_Controller
    {

        public function __construct()
        {
            parent::__construct();

            $this->load->model(array(

                'common_model',
                'api/app_model',
                'customer/auth_model',
                'customer/package_model',
                'customer/transections_model',
                'customer/transfer_model',
                'customer/Profile_model',

            ));

            $this->load->library('payment');

            $gData['category']    = $this->app_model->categoryList();
            // $gData['social_link'] = $this->app_model->social_link();
            // $gData['web_language'] = $this->app_model->webLanguage();
            @$gData['service']     = $this->app_model->article($this->app_model->catidBySlug('service')->cat_id, 8);

            $this->load->vars($gData);
        }

        // public function index_get()
        // {

        //     $cat_id = $this->app_model->catidBySlug($this->uri->segment(1));

        //     //Language setting
        //     // $data['lang'] = $this->langSet();

        //     $data['title'] = $this->uri->segment(1);
        //     $data['article'] = $this->app_model->article($cat_id->cat_id);
        //     $data['cat_info'] = $this->app_model->cat_info($this->uri->segment(1));
        //     $data['package'] = $this->app_model->package();

        //     $this->response($data, REST_Controller::HTTP_OK);

        //     // $this->load->view('website/header', $data);
        //     // $this->load->view('website/lending', $data);
        //     // $this->load->view('website/footer', $data);
        // }

        public function index_get($package_id = NULL)
        {
            $data['title']   = display('package');
            // $data['my_info'] = $this->Profile_model->my_info();
            $data['package'] = $this->package_model->package_info_by_id($package_id);
            // $data['content'] = $this->load->view('customer/pages/package_confirmation', $data, true);
            // $this->load->view('customer/layout/main_wrapper', $data);
            return $this->response($data, REST_Controller::HTTP_OK);
        }

        /*
|--------------------------------------------------------------
|   BUY PACKAGE 
|--------------------------------------------------------------
*/
        public function buy($package_id = NULL)
        {

            // balance chcck
            $blance = $this->check_balance($package_id);

            if ($blance != NULL) {

                $user_id = $this->session->userdata('user_id');
                if ($this->check_investment($user_id) == '') {
                    $saveLevel = array(
                        'user_id'           => $this->session->userdata('user_id'),
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
                    'user_id'       => $this->session->userdata('user_id'),
                    'sponsor_id'    => $this->session->userdata('sponsor_id'),
                    'package_id'    => $package_id,
                    'amount'        => $blance->package_amount,
                    'invest_date'   => date('Y-m-d'),
                    'day'           => date('N'),
                );

                $result = $this->package_model->buy_package($buy_data);
                // check investment by customent
                $customent_investment = $this->db->select('*')->from('investment')->where('user_id', $this->session->userdata('user_id'))->get()->num_rows();


                $sponsor_id = $this->session->userdata('sponsor_id');
                // get sponsert information by id
                $sponsers_info = $this->db->select('*')->from('user_registration')->where('user_id', $sponsor_id)->get()->row();
                // check invesment by sponser
                $investment = $this->db->select('*')->from('investment')->where('user_id', $sponsor_id)->get()->num_rows();


                if ($this->session->userdata('sponsor_id') != NULL) {

                    if ($investment > 0) {
                        // get package informaion by package id
                        $pack_info = $this->package_model->package_info_by_id($package_id);
                        #--------------------------------
                        #    commission data save
                        $commission_amount = ($blance->package_amount / 100) * 6;
                        $commission = array(

                            'user_id'       => $this->session->userdata('sponsor_id'),
                            'Purchaser_id'  => $this->session->userdata('user_id'),
                            'earning_type'  => 'type1',
                            'package_id'    => $pack_info->package_id,
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

                        $this->load->library('sms_lib');
                        $template = array(
                            'name'      => $sponsers_info->f_name . ' ' . $sponsers_info->l_name,
                            'amount'    => $commission_amount,
                            'new_balance' => $new_balance,
                            'date'      => date('d F Y')
                        );

                        $send_sms = $this->sms_lib->send(array(

                            'to'              => $sponsers_info->phone,
                            'template'        => 'You received a referral commission of $%amount% . Your new balance is $%new_balance%',
                            'template_config' => $template,

                        ));

                        #----------------------------------
                        #   sms insert to received commission
                        #---------------------------------
                        if ($send_sms) {
                            $message_data = array(
                                'sender_id' => 1,
                                'receiver_id' => $sponsers_info->user_id,
                                'subject' => 'Commission',
                                'message' => 'You received a referral commission of $' . $commission_amount . '. Your new balance is $' . $new_balance,
                                'datetime' => date('Y-m-d h:i:s'),
                            );

                            $this->db->insert('message', $message_data);
                        }
                        #-------------------------------------     


                        #---------------------------------
                        #   Won Sponser set personal and team commission set heare

                        $sponsers = $this->db->select('*')
                            ->from('team_bonus')
                            ->where('user_id', $sponsor_id)
                            ->get()
                            ->row();

                        if ($sponsers != NULL) {

                            $scom = @$sponsers->sponser_commission + $blance->package_amount;
                            $tcom = @$sponsers->team_commission + $blance->package_amount;
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

                            $scom = @$sponsers->sponser_commission + @$blance->package_amount;
                            $tcom = @$sponsers->team_commission + @$blance->package_amount;

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

                            $commission_amount = ($pack_info->package_amount / 100) * $referral_bonous->referral_bonous;

                            $commission = array(
                                'user_id'       => $sponsor_id,
                                'Purchaser_id'  => $this->session->userdata('user_id'),
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
                        'amount'        => $blance->package_amount,
                        'sponsor_id'    => $sponsor_id
                    );

                    $this->recursive_data($tuSdata);
                    #
                    #--------------------------------

                }

                if ($result != NULL) {
                    $transections_data = array(
                        'user_id'                   => $this->session->userdata('user_id'),
                        'transection_category'      => 'investment',
                        'releted_id'                => $result['investment_id'],
                        'amount'                    => $blance->package_amount,
                        'transection_date_timestamp' => date('Y-m-d h:i:s')
                    );

                    $this->transections_model->save_transections($transections_data);

                    #----------------------------
                    # sms send to commission received
                    #----------------------------

                    $this->load->library('sms_lib');

                    $template = array(
                        'name'      => $this->session->userdata('fullname'),
                        'amount'    => $blance->package_amount,
                        'date'      => date('d F Y')
                    );

                    $send_sms = $this->sms_lib->send(array(
                        'to'              => $this->session->userdata('phone'),
                        'template'        => 'You bought a $%amount% package successfully',
                        'template_config' => $template,
                    ));

                    #----------------------------------
                    #   sms insert to received commission
                    #---------------------------------
                    if ($send_sms) {

                        $message_data = array(
                            'sender_id' => 1,
                            'receiver_id' => $this->session->userdata('user_id'),
                            'subject' => 'Package Buy',
                            'message' => 'You bought a ' . $blance->package_amount . ' package successfully',
                            'datetime' => date('Y-m-d h:i:s'),
                        );

                        $this->db->insert('message', $message_data);
                    }
                    #------------------------------------- 

                    $set = $this->common_model->email_sms('email');
                    $appSetting = $this->common_model->get_setting();
                    #----------------------------
                    #      email verify smtp
                    #----------------------------
                    $post = array(
                        'title'           => $appSetting->title,
                        'subject'           => 'Package Buy',
                        'to'                => $this->session->userdata('email'),
                        'message'           => 'You bought a ' . $blance->package_amount . ' package successfully',
                    );
                    $send_email = $this->common_model->send_email($post);

                    if ($send_email) {
                        $n = array(
                            'user_id'                => $this->session->userdata('user_id'),
                            'subject'                => 'Package Buy',
                            'notification_type'      => 'package_by',
                            'details'                => 'You bought a ' . $blance->package_amount . ' package successfully',
                            'date'                   => date('Y-m-d h:i:s'),
                            'status'                 => '0'
                        );
                        $this->db->insert('notifications', $n);
                    }
                }

                $this->session->set_flashdata('message', display('package_buy_successfully'));
                redirect('customer/package/buy_success/' . $package_id . '/' . $result['investment_id']);
            } else {

                $this->session->set_flashdata('exception', display('balance_is_unavailable'));
                redirect('customer/package/confirm_package/' . $package_id);
            } // END FCHECK BALANCE

        } // END FUNCTION


    }
