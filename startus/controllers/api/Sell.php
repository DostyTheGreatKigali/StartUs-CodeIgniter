<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Sell extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(

            'api/sell_model',
            'common_model',
        ));
    }
    public function sellInfo_post()
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

        $data['payment_gateway'] = $this->common_model->payment_gateway();
        $data['currency'] = $this->sell_model->findExcCurrency();
        $data['selectedlocalcurrency'] = $this->sell_model->findlocalCurrency();
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
        $data['title']  = display('sell');
        #-------------------------------#
        #
        #pagination starts
        #
        $config["base_url"] = base_url('customer/sell/index');
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
        // $data['sell'] = $this->sell_model->read($config["per_page"], $page);
        $data['sell'] = $this->sell_model->read($user_id);
        // $data["links"] = $this->pagination->create_links();
        #
        #pagination ends
        #    
        // $data['content'] = $this->load->view("customer/sell/list", $data, true);
        // $this->load->view("customer/layout/main_wrapper", $data);
        return $this->response(['success' => TRUE, 'message' => 'All Sold Currencies and Coins loaded.', 'soldOrders' => $data], REST_Controller::HTTP_OK);
    }

    public function index_post($sell_id = null)
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

        // $this->load->helper('string');

        $user_id = $this->input->post('user_id');

        // $data['title']  = display('add_sell');
        if (!isset($_POST['payable_usd']) || empty($this->input->post('payable_usd'))) {
            return $this->response(['success' => FALSE, 'message' => 'payable usd required'], REST_Controller::HTTP_OK);
        }

        // $admin_ref_id = strtoupper(random_string('alnum', 9));

        $payable_usd = $this->input->post('payable_usd');

        // $data['payment_gateway'] = $this->common_model->payment_gateway();
        // $data['currency'] = $this->sell_model->findExcCurrency();
        // $data['selectedlocalcurrency'] = $this->sell_model->findlocalCurrency();
        #------------------------#
        // $selected_data['admin_ref_id']   = $admin_ref_id;

        // Calculate like buypayable will at public function sellPayable_post()
        $selected_data['selectedcryptocurrency'] = $this->sell_model->findCurrency($this->input->post('cid'));
        $selected_data['selectedexccurrency'] = $this->sell_model->findExchangeCurrency($this->input->post('cid'));
        $selected_data['selectedlocalcurrency'] = $this->sell_model->findlocalCurrency();

        $selected_data['price_usd']         = $this->getPercentOfNumber($selected_data['selectedcryptocurrency']->price_usd, $selected_data['selectedexccurrency']->sell_adjustment) + $selected_data['selectedcryptocurrency']->price_usd;
        $payableusd             = $this->input->post('payable_usd'); //$selected_data['price_usd']*$this->input->post('sell_amount');
        $selected_data['payableusd']     = $payableusd;
        $selected_data['payablelocal']     = $payableusd * $selected_data['selectedlocalcurrency']->usd_exchange_rate;
        // End of  calculation like public function sellPayable_post()
        $coins_amount   = $payableusd / $selected_data['price_usd'];


        $this->form_validation->set_rules('cid', display('coin_name'), 'required');
        // $this->form_validation->set_rules('sell_amount', display('sell_amount'),'required');
        $this->form_validation->set_rules('payable_usd', 'payable_usd', 'required');
        $this->form_validation->set_rules('wallet_id', display('wallet_data'), 'required');
        $this->form_validation->set_rules('payment_method', display('payment_method'), 'required');
        // $this->form_validation->set_rules('usd_amount', display('usd_amount'),'required');
        // $this->form_validation->set_rules('rate_coin', display('rate_coin'),'required');
        // $this->form_validation->set_rules('local_amount', display('local_amount'),'required');
        // $this->form_validation->set_rules('admin_ref_id', display('admin_reference_id'), 'required');
        $this->form_validation->set_rules('ref_id', display('reference_id'), 'required');
        $this->form_validation->set_rules('coin_name', display('coin_name'), 'required');

        if ($this->input->post('payment_method') == 'bitcoin' || $this->input->post('payment_method') == 'payeer') {
            // $this->form_validation->set_rules('comments', display('comments'), 'required');
            $this->form_validation->set_rules('bank_account_name', display('bank_account_name'), 'required');
            $this->form_validation->set_rules('bank_account_num', display('bank_account_num'), 'required');
            $this->form_validation->set_rules('bank_name', display('bank_name'), 'required');
        }

        if ($this->input->post('payment_method') == 'phone') {
            $this->form_validation->set_rules('om_name', display('om_name'), 'required');
            $this->form_validation->set_rules('om_mobile', display('om_mobile'), 'required');
            // $this->form_validation->set_rules('transaction_no', display('transaction_no'), 'required');
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
            'file_ext_tolower'     => true
        ];
        $this->load->library('upload', $config);
        if ($this->upload->do_upload('document')) {
            $data = $this->upload->data();
            $image = $config['upload_path'] . $data['file_name'];

            // $this->session->set_flashdata('message', display("image_upload_successfully"));
        }

        /*-----------------------------------*/
        $data['sell']   = (object)$userdata = array(
            'coin_id'                  => $this->input->post('cid'),
            'user_id'                  => $user_id,
            'coin_wallet_id'          => $this->input->post('wallet_id'),
            'transection_type'      => "sell",
            'coin_amount'              => $coins_amount, //$this->input->post('sell_amount'),
            'usd_amount'              => $selected_data['payableusd'], //$this->input->post('usd_amount'),
            'local_amount'          => $selected_data['payablelocal'], //$this->input->post('local_amount'),
            'payment_method'          => $this->input->post('payment_method'),
            'request_ip'              => $this->input->ip_address(),
            'verification_code'     => "",
            // 'payment_details'          => $this->input->post('comments'),
            'rate_coin'              => $selected_data['price_usd'], //$this->input->post('rate_coin'),
            // 'document_status'          => (!empty($image) ? 1 : 0),
            'om_name'                => $this->input->post('om_name'),
            'om_mobile'                => $this->input->post('om_mobile'),
            // 'transaction_no'        => $this->input->post('transaction_no'),
            // 'idcard_no'                => $this->input->post('idcard_no'),
            'ref_id'        => $this->input->post('ref_id'),
            'admin_ref_id'        => $this->input->post('ref_id'),
            'bank_account_name'        => $this->input->post('bank_account_name'),
            'bank_account_num'        => $this->input->post('bank_account_num'),
            'coin_name'        => $this->input->post('coin_name'),
            'bank_name'        => $this->input->post('bank_name'),
            'status'                  => 1
        );

        /*-----------------------------------*/
        if ($this->form_validation->run()) {
            if (empty($sell_id)) {
                if ($this->sell_model->create($userdata)) {
                    if (!empty($image)) {
                        $data['document']   = (object)$documentdata = array(
                            'ext_exchange_id'      => $this->db->insert_id(),
                            'doc_url'              => (!empty($image) ? $image : '')
                        );
                        $this->sell_model->documentcreate($documentdata);
                    }

                    // $this->session->set_flashdata('message', display('save_successfully'));
                    return $this->response(['success' => TRUE, 'message' => 'Sell request successful'], REST_Controller::HTTP_OK);
                } else {
                    // if (data['ref_id'] != data['admin_ref_id']) {
                    //     return $this->response(['success' => FALSE, 'message' => "Invalid Reference ID"], REST_Controller::HTTP_OK);
                    // }
                    return $this->response(['success' => TRUE, 'message' => display('please_try_again')], REST_Controller::HTTP_OK);
                    // $this->session->set_flashdata('exception', display('please_try_again'));
                }
                // redirect("customer/sell/form/");
            }
            // else 
            // {
            // 	if ($this->sell_model->update($userdata)) {
            // 		$this->session->set_flashdata('message', display('update_successfully'));
            // 	} else {
            // 		$this->session->set_flashdata('exception', display('please_try_again'));
            // 	}
            // 	redirect("customer/sell/form/$sell_id");
            // }
        } else {
            return $this->response(['success' => FALSE, 'message' => display('please_try_again')], REST_Controller::HTTP_OK);
            // if(!empty($sell_id)) {
            // 	$data['title'] = display('edit_sell');
            // 	$data['sell']   = $this->sell_model->single($sell_id);
            // }
            // $data['content'] = $this->load->view("customer/sell/form", $data, true);
            // $this->load->view("customer/layout/main_wrapper", $data);
        }
    }


    public function sellPayable_post()
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
        $cid = $this->input->post('cid');
        // $amount = $this->input->post('amount');
        $payable_usd = $this->input->post('payable_usd');

        $data['selectedcryptocurrency'] = $this->sell_model->findCurrency($cid);
        $data['selectedexccurrency'] = $this->sell_model->findExchangeCurrency($cid);
        $data['selectedlocalcurrency'] = $this->sell_model->findlocalCurrency();
        // if (!empty($amount)) {
        if (!empty($payable_usd)) {
            $data['price_usd']         = $this->getPercentOfNumber($data['selectedcryptocurrency']->price_usd, $data['selectedexccurrency']->sell_adjustment) + $data['selectedcryptocurrency']->price_usd;
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
                $data['price_usd']      = $this->getPercentOfNumber($data['selectedcryptocurrency']->price_usd, $data['selectedexccurrency']->sell_adjustment) + $data['selectedcryptocurrency']->price_usd;
            }
        }
        return $this->response(['success' => TRUE, 'purchaseInfo' => $data], REST_Controller::HTTP_OK);

        // $this->load->view("customer/sell/ajaxpayable", $data);
    }

    public function getPercentOfNumber($number, $percent)
    {
        return ($percent / 100) * $number;
    }
}
