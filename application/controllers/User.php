<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('usermodel');
        $this->load->library('ratelimit');
        
    }
	public function register()
	{
		$user_name = $this->input->post('username');
		$password = $this->input->post('password');
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $expiry_day = $this->config->item('password_expiry_days');
        $expiry_date = date('Y-m-d H:i:s', strtotime("+ $expiry_day days"));
        $data = [
            'user_name' => $user_name,
            'password' => $pass_hash,
            'password_expiry' => $expiry_date,
            'created_date' => date('Y-m-d H:i:s')
        ];

        $insert_id = $this->db->insert('users', $data);
        if($insert_id){
            return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode(['success' => 'User created']));
        }
	}
	
}
