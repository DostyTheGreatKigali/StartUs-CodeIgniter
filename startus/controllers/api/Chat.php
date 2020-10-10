<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Chat extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(

            // 'customer/buy_model',
            // 'customer/diposit_model',
            // 'customer/profile_model',
            // 'common_model',  
        ));

        $this->load->library('payment');
    }

    public function getChats()
    {
        $query_string = 'SELECT  chats.id as _id, chats.user_id, text, image, sent, video, audio, reg.username, chats.created_at as createdAt   FROM `chat_messages` AS chats INNER JOIN user_registration AS reg ON chats.user_id = reg.uid ORDER BY createdAt DESC';

        $query = $this->db->query($query_string);

        $results = $query->result_array();

        foreach ($results as &$result) {
            $user = new stdClass;
            $user->_id = $result['user_id'];
            $user->name = $result['username'];
            $result['user'] = $user;
            unset($result['user_id']);
            unset($result['username']);
        }
        return $results;
        // $this->response(['success' => TRUE, 'chats' => $results ], REST_Controller::HTTP_OK);

        // var_dump($results);
        // var_dump($query->result_array());
    }

    public function fetch_post()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data[0]->uid); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $query_string = 'SELECT  chats.id as _id, chats.user_id, text, image, sent, video, audio, reg.username, chats.created_at as createdAt   FROM `chat_messages` AS chats INNER JOIN user_registration AS reg ON chats.user_id = reg.uid';

        $query = $this->db->query($query_string);

        // $results = $query->result_array();
        $results = $this->getChats();

        // foreach($results as &$result){
        // 	$user = new stdClass;
        // 	$user->_id = $result['user_id'];
        // 	$user->name = $result['username'];
        // 	$result['user'] = $user;
        // 	unset($result['user_id']);
        // 	unset($result['username']);
        // }
        return $this->response(['success' => TRUE, 'chats' => $results], REST_Controller::HTTP_OK);

        var_dump($results);
        var_dump($query->result_array());
    }

    public function send_post()
    {
        $post_user_data = $this->db->select('*')
            ->from('user_registration')
            ->where('user_id', $this->input->post('user_id'))
            ->where('api_token', $this->input->post('api_token'))
            ->get()
            ->result();
        // var_dump($post_user_data[0]->uid); die;
        if (empty($post_user_data)) {
            return $this->response(['success' => FALSE, 'message' => 'Invalid token'], REST_Controller::HTTP_OK);
        }

        $user_id = $this->input->post('user_id');
        $n = array(
            'user_id'           => $post_user_data[0]->uid,
            'text'           => $this->input->post('text'),
            'created_at' => date('Y-m-d h:i:s'),
            'system'            => '0',
            'sent'            => '1',
            'received'            => '1',
            'pending'            => '1'
        );
        $inserted = $this->db->insert('chat_messages', $n);
        $results = $this->getChats();

        return $this->response(['success' => TRUE, 'message' => $inserted, 'chats' => $results], REST_Controller::HTTP_OK);

        // `image` text COLLATE utf8_unicode_ci,
        // `video` text COLLATE utf8_unicode_ci,
        // `audio` text COLLATE utf8_unicode_ci,
        // `deleted` tinyint(1) NOT NULL DEFAULT '0',
        // `deleted_at` timestamp NULL DEFAULT NULL
    }
}
