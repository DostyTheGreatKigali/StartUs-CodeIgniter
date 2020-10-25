<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Buy extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(

            'api/buy_model',
            // 'customer/buy_model',
            'customer/diposit_model',
            'customer/profile_model',
            'common_model',
        ));

        $this->load->library('payment');
    }

    public function buyInfo_post()
    {

        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->post('user_id');

        $data['payment_gateway']         = $this->common_model->payment_gateway();
        $data['currency']                 = $this->buy_model->findExcCurrency();
        $data['selectedlocalcurrency']     = $this->buy_model->findlocalCurrency();
        #------------------------#
        return $this->response(['success' => TRUE, 'purchaseInfo' => $data], REST_Controller::HTTP_OK);
    }

    public function buyInfo_get()
    {

        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->get('user_id'))
            ->where('api_token', $this->input->get('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->post('user_id');

        $data['payment_gateway']         = $this->common_model->payment_gateway();
        $data['currency']                 = $this->buy_model->findExcCurrency();
        $data['selectedlocalcurrency']     = $this->buy_model->findlocalCurrency();
        #------------------------#
        return $this->response(['success' => TRUE, 'purchaseInfo' => $data], REST_Controller::HTTP_OK);
    }

    public function index_get()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->get('user_id'))
            ->where('api_token', $this->input->get('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->get('user_id');

        $data['currency'] = $this->buy_model->findExcCurrency();

        $data['title']  = display('buy_list');
        #-------------------------------#
        #
        #pagination starts
        #
        $config["base_url"] = base_url('customer/buy/index');
        $config["total_rows"] = $this->db->count_all('ext_exchange');
        $config["per_page"] = 25;
        $config["uri_segment"] = 4;
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
        // $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        $data['buy'] = $this->buy_model->read($user_id);
        // $data['buy'] = $this->buy_model->read($config["per_page"], $page);
        // $data["links"] = $this->pagination->create_links();
        #
        #pagination ends
        #    
        // $data['content'] = $this->load->view("customer/buy/list", $data, true);
        // $this->load->view("customer/layout/main_wrapper", $data);
        return $this->response(['success' => TRUE, 'message' => 'All Bought Currencies and Coins loaded.', 'boughtOrders' => $data], REST_Controller::HTTP_OK);
    }

    public function index_post($buy_id = null)
    {

        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->post('user_id');

        $data['title']  = display('buy');

        // if ($this->session->userdata('buy')) {
        // 	$this->session->unset_userdata('buy');
        // }
        if (!isset($_POST['payable_usd']) || empty($this->input->post('payable_usd'))) {
            return $this->response(['success' => FALSE, 'message' => 'payable usd required'], REST_Controller::HTTP_OK);
        }

        $payable_usd = $this->input->post('payable_usd');

        // $data['payment_gateway'] 		= $this->common_model->payment_gateway();
        // $data['currency'] 				= $this->buy_model->findExcCurrency();
        // $data['selectedlocalcurrency'] 	= $this->buy_model->findlocalCurrency();
        #------------------------#
        // Calculate like buypayable will at public function buyPayable_post()
        $selected_data['selectedcryptocurrency'] = $this->buy_model->findCurrency($this->input->post('cid'));
        $selected_data['selectedexccurrency']     = $this->buy_model->findExchangeCurrency($this->input->post('cid'));
        $selected_data['selectedlocalcurrency']     = $this->buy_model->findlocalCurrency();
        $selected_data['price_usd']         = $this->getPercentOfNumber($selected_data['selectedcryptocurrency']->price_usd, $selected_data['selectedexccurrency']->buy_adjustment) + $selected_data['selectedcryptocurrency']->price_usd;
        $payableusd             = $this->input->post('payable_usd'); //$selected_data['price_usd']*$this->input->post('buy_amount');
        $selected_data['payableusd']     = $payableusd;
        $selected_data['payablelocal']     = $payableusd * $selected_data['selectedlocalcurrency']->usd_exchange_rate;
        // End of  calculation like public function buyPayable_post()
        $coins_amount   = $payableusd / $selected_data['price_usd'];;

        // return $this->response(['success' => FALSE, 'message' => $selected_data], REST_Controller::HTTP_OK);

        // return $this->response(['success' => FALSE, 'message' => $data], REST_Controller::HTTP_OK);

        $this->form_validation->set_rules('cid', display('coin_name'), 'required');
        // $this->form_validation->set_rules('buy_amount', display('buy_amount'), 'required');
        $this->form_validation->set_rules('payable_usd', 'payable_usd', 'required');
        $this->form_validation->set_rules('wallet_id', display('wallet_data'), 'required');
        $this->form_validation->set_rules('payment_method', display('payment_method'), 'required');
        // $this->form_validation->set_rules('usd_amount', display('usd_amount'), 'required');
        // $this->form_validation->set_rules('rate_coin', display('rate_coin'), 'required');
        // $this->form_validation->set_rules('local_amount', display('local_amount'), 'required');
        $this->form_validation->set_rules('ref_id', display('reference_id'), 'required');

        if ($this->input->post('payment_method') == 'bitcoin' || $this->input->post('payment_method') == 'payeer') {
            // $this->form_validation->set_rules('comments', display('comments'), 'required');
            $this->form_validation->set_rules('bank_account_name', display('bank_account_name'), 'required');
            $this->form_validation->set_rules('bank_account_num', display('bank_account_num'), 'required');
        }
        if ($this->input->post('payment_method') == 'phone') {
            $this->form_validation->set_rules('om_name', display('om_name'), 'required');
            $this->form_validation->set_rules('om_mobile', display('om_mobile'), 'required');
            $this->form_validation->set_rules('transaction_no', display('transaction_no'), 'required');
            // $this->form_validation->set_rules('idcard_no', display('idcard_no'), 'required');
        }

        if (!$this->input->valid_ip($this->input->ip_address())) {
            return false;
        }


        //set config 
        $config = [
            'upload_path'       => 'upload/document/',
            'allowed_types'     => 'gif|jpg|png|jpeg|pdf',
            'overwrite'         => false,
            'maintain_ratio'     => true,
            'encrypt_name'      => true,
            'remove_spaces'     => true,
            'file_ext_tolower'     => true,
            'max_size'     => 10240
        ];
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('document')) {
            $data = $this->upload->data();
            $image = $config['upload_path'] . $data['file_name'];

            // $this->session->set_flashdata('message', display("image_upload_successfully"));
        }

        // $allImagesEmptyErrors = [];
        // if (empty($_FILES['document']['name'])) {
        //     $allImagesEmptyErrors[] = 'Payment proof document is required';
        //     // print_r($_FILES);
        //     // die();
        //     // $this->form_validation->set_rules('id_image', 'ID Image', 'required');
        // }


        $sdata['buy']   = (object)$userdata = array(
            'coin_id'                  => $this->input->post('cid', TRUE),
            'user_id'                  => $user_id,
            'coin_wallet_id'          => $this->input->post('wallet_id', TRUE),
            'transection_type'      => "buy",
            'coin_amount'              => $coins_amount, //$this->input->post('buy_amount', TRUE),
            'usd_amount'              => $selected_data['payableusd'], //$this->input->post('usd_amount', TRUE),
            'local_amount'          => $selected_data['payablelocal'], //$this->input->post('local_amount', TRUE),
            'payment_method'          => $this->input->post('payment_method', TRUE),
            'request_ip'              => $this->input->ip_address(),
            'verification_code'     => "",
            // 'payment_details'          => $this->input->post('comments', TRUE),
            'rate_coin'              => $selected_data['price_usd'], //$this->input->post('rate_coin', TRUE),
            'document_status'          => 0,
            'om_name'                => $this->input->post('om_name', TRUE),
            'om_mobile'                => $this->input->post('om_mobile', TRUE),
            'transaction_no'        => $this->input->post('transaction_no', TRUE),
            'ref_id'        => $this->input->post('ref_id'),
            'admin_ref_id'        => $this->input->post('ref_id'),
            'bank_account_name'        => $this->input->post('bank_account_name'),
            'bank_account_num'        => $this->input->post('bank_account_num'),
            // 'idcard_no'                => $this->input->post('idcard_no', TRUE),
            'status'                  => 1
        );


        if ($this->form_validation->run()) {
            if (empty($buy_id)) {
                if ($this->buy_model->create($userdata)) {
                    if (!empty($image)) {
                        $data['document']   = (object)$documentdata = array(
                            'ext_exchange_id'      => $this->db->insert_id(),
                            'doc_url'              => (!empty($image) ? $image : '')
                        );
                        $this->buy_model->documentcreate($documentdata);
                    }

                    // $this->session->set_flashdata('message', display('save_successfully'));
                    // return $this->response(['success' => TRUE, 'message' => display('save_successfully')], REST_Controller::HTTP_OK);
                } else {
                    // if (data['ref_id'] != data['admin_ref_id']) {
                    //     return $this->response(['success' => FALSE, 'message' => "Invalid Reference ID"], REST_Controller::HTTP_OK);
                    // }
                    //     $uploadedFiles = [];
                    //     $uploadErrors = [];


                    // if(!$this->upload->do_upload('document')) {
                    //         $uploadErrors[] = $this->upload->display_errors();
                    //     }

                    return $this->response(['success' => TRUE, 'message' => display('please_try_again')], REST_Controller::HTTP_OK);
                    // return $this->response(['success' => TRUE, 'message' => display('please_try_again')], REST_Controller::HTTP_OK);
                    // $this->session->set_flashdata('exception', display('please_try_again'));
                }
                // redirect("customer/sell/form/");
            }

            // $sdata['buy']   = (object)$userdata = array(
            //     'coin_id'                  => $this->input->post('cid', TRUE),
            //     'user_id'                  => $user_id,
            //     'coin_wallet_id'          => $this->input->post('wallet_id', TRUE),
            //     'transection_type'      => "buy",
            //     'coin_amount'              => $coins_amount, //$this->input->post('buy_amount', TRUE),
            //     'usd_amount'              => $selected_data['payableusd'], //$this->input->post('usd_amount', TRUE),
            //     'local_amount'          => $selected_data['payablelocal'], //$this->input->post('local_amount', TRUE),
            //     'payment_method'          => $this->input->post('payment_method', TRUE),
            //     'request_ip'              => $this->input->ip_address(),
            //     'verification_code'     => "",
            //     'payment_details'          => $this->input->post('comments', TRUE),
            //     'rate_coin'              => $selected_data['price_usd'], //$this->input->post('rate_coin', TRUE),
            //     'document_status'          => 0,
            //     'om_name'                => $this->input->post('om_name', TRUE),
            //     'om_mobile'                => $this->input->post('om_mobile', TRUE),
            //     'transaction_no'        => $this->input->post('transaction_no', TRUE),
            //     'idcard_no'                => $this->input->post('idcard_no', TRUE),
            //     'status'                  => 1
            // );

            $ext_exchange_id = $this->buy_model->create($userdata);
            // $ext_exchange_id = $this->buy_model->create($sdata['buy']);
            return $this->response(['success' => TRUE, 'message' => 'Buy Request Successful. Please wait for confirmation.'], REST_Controller::HTTP_OK);

            // $sdata['deposit']   = (object)$userdata = array(
            //     'deposit_id'   		=> '',
            //     'user_id'           => $this->session->userdata('user_id'),
            //     'deposit_amount'    => $this->input->post('usd_amount', TRUE),
            //     'deposit_method'    => $this->input->post('payment_method', TRUE),
            //     'fees'              => 0
            // );


            // $this->session->set_userdata($sdata);
            // redirect("customer/buy/paymentform");

        }
        return $this->response(['success' => FALSE, 'message' => 'Please enter the right information to let deposit work'], REST_Controller::HTTP_OK);

        // $data['content'] = $this->load->view("customer/buy/form", $data, true);
        // $this->load->view("customer/layout/main_wrapper", $data);

    }


    public function buyPayable_post()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $this->load->helper('string');

        $admin_ref_id = strtoupper(random_string('alnum', 9));
        $data['admin_ref_id']   = $admin_ref_id;

        $cid     = $this->input->post('cid');
        // $amount = $this->input->post('amount');
        $payable_usd = $this->input->post('payable_usd');

        $data['selectedcryptocurrency'] = $this->buy_model->findCurrency($cid);
        $data['selectedexccurrency']     = $this->buy_model->findExchangeCurrency($cid);
        $data['selectedlocalcurrency']     = $this->buy_model->findlocalCurrency();
        // if (!empty($amount)) {
        if (!empty($payable_usd)) {
            $data['price_usd']         = $this->getPercentOfNumber($data['selectedcryptocurrency']->price_usd, $data['selectedexccurrency']->buy_adjustment) + $data['selectedcryptocurrency']->price_usd;
            $payableusd             =  $payable_usd; //;$data['price_usd']*$amount;
            $coins_quantity = $payableusd / $data['price_usd'];
            $data['payableusd']     = $payableusd;
            $data['payablelocal']     = $payableusd * $data['selectedlocalcurrency']->usd_exchange_rate;
            $data['coins_quantity']   = $coins_quantity;
        } else {
            $data['payableusd']     = 0;
            $data['payablelocal']   = 0;
            $data['coins_quantity']   = 0;
            if (empty($cid)) {
                $data['price_usd']  = 0;
            } else {
                $data['price_usd']      = $this->getPercentOfNumber($data['selectedcryptocurrency']->price_usd, $data['selectedexccurrency']->buy_adjustment) + $data['selectedcryptocurrency']->price_usd;
            }
        }

        return $this->response(['success' => TRUE, 'purchaseInfo' => $data], REST_Controller::HTTP_OK);
    }

    public function getPercentOfNumber($number, $percent)
    {
        return ($percent / 100) * $number;
    }
}
