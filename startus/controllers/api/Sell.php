 <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';

    class Signup extends REST_Controller
    {
        public function sells()
        {

            $cat_id = $this->web_model->catidBySlug($this->uri->segment(1));

            $this->load->model('customer/sell_model');

            //Language setting
            $data['lang'] = $this->langSet();

            $data['title'] = $this->uri->segment(1);
            $data['article'] = $this->web_model->article($cat_id->cat_id);
            $data['cat_info'] = $this->web_model->cat_info($this->uri->segment(1));
            $data['payment_gateway'] = $this->common_model->payment_gateway();
            $data['currency'] = $this->sell_model->findExcCurrency();
            $data['selectedlocalcurrency'] = $this->sell_model->findlocalCurrency();

            //Set Rules From validation
            $this->form_validation->set_rules('cid', display('coin_name'), 'required');
            $this->form_validation->set_rules('sell_amount', display('sell_amount'), 'required');
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

            //Set Upload File Config
            $config = [
                'upload_path' => 'upload/document/',
                'allowed_types' => 'gif|jpg|png|jpeg|pdf',
                'overwrite' => false,
                'maintain_ratio' => true,
                'encrypt_name' => true,
                'remove_spaces' => true,
                'file_ext_tolower' => true
            ];

            $this->load->library('upload', $config);
            if ($this->upload->do_upload('document')) {
                $data = $this->upload->data();
                $image = $config['upload_path'] . $data['file_name'];
            }

            $data['sell'] = (object)$userdata = array(
                'coin_id' => $this->input->post('cid'),
                'user_id' => $this->session->userdata('user_id'),
                'coin_wallet_id' => $this->input->post('wallet_id'),
                'transection_type' => "sell",
                'coin_amount' => $this->input->post('sell_amount'),
                'usd_amount' => $this->input->post('usd_amount'),
                'local_amount' => $this->input->post('local_amount'),
                'payment_method' => $this->input->post('payment_method'),
                'request_ip' => $this->input->ip_address(),
                'verification_code' => "",
                'payment_details' => $this->input->post('comments'),
                'rate_coin' => $this->input->post('rate_coin'),
                'document_status' => (!empty($image) ? 1 : 0),
                'om_name' => $this->input->post('om_name'),
                'om_mobile' => $this->input->post('om_mobile'),
                'transaction_no' => $this->input->post('transaction_no'),
                'idcard_no' => $this->input->post('idcard_no'),
                'status' => 1
            );

            //From Validation Check
            if ($this->form_validation->run()) {
                if (!$this->input->valid_ip($this->input->ip_address())) {
                    $this->session->set_flashdata('exception', display("ip_address") . " Invalid");
                    redirect("sells");
                }
                if (empty($this->session->userdata('user_id'))) {
                    redirect("register#tab2");
                }

                if ($this->sell_model->create($userdata)) {
                    if (!empty($image)) {
                        $data['document'] = (object)$documentdata = array(
                            'ext_exchange_id' => $this->db->insert_id(),
                            'doc_url' => (!empty($image) ? $image : '')

                        );
                        $this->sell_model->documentcreate($documentdata);
                    }
                    $this->session->set_flashdata('message', display('sell_successfully'));
                } else {
                    $this->session->set_flashdata('exception', display('please_try_again'));
                }

                redirect("sells");
            }


            $this->load->view('website/header', $data);
            $this->load->view('website/sell', $data);
            $this->load->view('website/footer', $data);
        }
    }
