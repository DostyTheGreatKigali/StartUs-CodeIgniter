  <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    require APPPATH . 'libraries/REST_Controller.php';

    class Packages extends REST_Controller
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

        public function index_get()
        {

            $cat_id = $this->app_model->catidBySlug($this->uri->segment(1));

            //Language setting
            // $data['lang'] = $this->langSet();

            $data['title'] = $this->uri->segment(1);
            $data['article'] = $this->app_model->article($cat_id->cat_id);
            $data['cat_info'] = $this->app_model->cat_info($this->uri->segment(1));
            $data['package'] = $this->app_model->activePackage();

            $this->response(['packages' => $data, 'success' => TRUE, 'message' => 'Packages successfully loaded'], REST_Controller::HTTP_OK);

            // $this->load->view('website/header', $data);
            // $this->load->view('website/lending', $data);
            // $this->load->view('website/footer', $data);
        }

        public function confirm_package($package_id = NULL)
        {
            $data['title']   = display('package');
            $data['my_info'] = $this->Profile_model->my_info();
            $data['package'] = $this->package_model->package_info_by_id($package_id);
            $data['content'] = $this->load->view('customer/pages/package_confirmation', $data, true);
            // $this->load->view('customer/layout/main_wrapper', $data);
            $this->response($data, REST_Controller::HTTP_OK);
        }
    }
