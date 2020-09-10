 <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';

    class Purchase extends REST_Controller
    {
        public function __construct()
        {
            parent::__construct();

            $this->load->model(array(

                'common_model',
                'api/app_model',

            ));

            $this->load->library('payment');

            $gData['category']    = $this->app_model->categoryList();
            $gData['social_link'] = $this->app_model->social_link();
            $gData['web_language'] = $this->app_model->webLanguage();
            @$gData['service']     = $this->app_model->article($this->app_model->catidBySlug('service')->cat_id, 8);

            $this->load->vars($gData);
        }

        public function index_post()
        {

            if ($this->session->userdata('buy')) {
                $this->session->unset_userdata('buy');
            }
            $this->load->model('customer/buy_model');

            $cat_id = $this->app_model->catidBySlug($this->uri->segment(1));

            //Language setting
            // $data['lang']                   = $this->langSet();

            $data['title']                  = $this->uri->segment(1);
            $data['article']                = $this->app_model->article($cat_id->cat_id);
            $data['cat_info']               = $this->app_model->cat_info($this->uri->segment(1));
            $data['payment_gateway']        = $this->common_model->payment_gateway();
            $data['currency']               = $this->buy_model->findExcCurrency();
            $data['selectedlocalcurrency']  = $this->buy_model->findlocalCurrency();

            //Set Rules From validation
            $this->form_validation->set_rules('cid', display('coin_name'), 'required');
            $this->form_validation->set_rules('buy_amount', display('buy_amount'), 'required');
            $this->form_validation->set_rules('wallet_id', display('wallet_data'), 'required');
            $this->form_validation->set_rules('payment_method', display('payment_method'), 'required');
            $this->form_validation->set_rules('usd_amount', display('usd_amount'), 'required');
            $this->form_validation->set_rules('rate_coin', display('rate_coin'), 'required');
            $this->form_validation->set_rules('local_amount', display('local_amount'), 'required');

            if ($this->input->post('payment_method') == 'bitcoin' || $this->input->post('payment_method') == 'payeer') {
                $this->form_validation->set_rules('comments', display('comments'), 'required');
            }
            if ($this->input->post('payment_method') == 'phone') {
                $this->form_validation->set_rules('om_name', display('om_name'), 'required');
                $this->form_validation->set_rules('om_mobile', display('om_mobile'), 'required');
                $this->form_validation->set_rules('transaction_no', display('transaction_no'), 'required');
                $this->form_validation->set_rules('idcard_no', display('idcard_no'), 'required');
            }


            //Validation Check confirm then Redirect to Payment
            if ($this->form_validation->run()) {
                if (!$this->input->valid_ip($this->input->ip_address())) {
                    $this->response(validation_errors(), REST_Controller::HTTP_OK);
                    // $this->session->set_flashdata('exception', display("ip_address") . " Invalid");
                    // redirect("buy");
                }


                $sdata['buy']   = (object)$userdata = array(
                    'coin_id'               => $this->input->post('cid'),
                    'user_id'               => $this->session->userdata('user_id'),
                    'coin_wallet_id'        => $this->input->post('wallet_id'),
                    'transection_type'      => "buy",
                    'coin_amount'           => $this->input->post('buy_amount'),
                    'usd_amount'            => $this->input->post('usd_amount'),
                    'local_amount'          => $this->input->post('local_amount'),
                    'payment_method'        => $this->input->post('payment_method'),
                    'request_ip'            => $this->input->ip_address(),
                    'verification_code'     => "",
                    'payment_details'       => $this->input->post('comments'),
                    'rate_coin'             => $this->input->post('rate_coin'),
                    'document_status'       => 0,
                    'om_name'               => $this->input->post('om_name'),
                    'om_mobile'             => $this->input->post('om_mobile'),
                    'transaction_no'        => $this->input->post('transaction_no'),
                    'idcard_no'             => $this->input->post('idcard_no'),
                    'status'                => 1
                );

                $sdata['deposit']   = (object)$userdata = array(
                    'deposit_id'        => '',
                    'user_id'           => $this->session->userdata('user_id'),
                    'deposit_amount'    => $this->input->post('usd_amount', TRUE),
                    'deposit_method'    => $this->input->post('payment_method', TRUE),
                    'fees'              => 0
                );

                return $this->response(['Username used'], REST_Controller::HTTP_OK);
                // $this->session->set_userdata($sdata);
                // redirect("paymentform");
            }

            $this->response(validation_errors(), REST_Controller::HTTP_OK);

            // $this->load->view('website/header', $data);
            // $this->load->view('website/buy', $data);
            // $this->load->view('website/footer', $data);
        }
    }
