<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('usermodel');
        $this->load->library('ratelimit');
        
    }
	public function index()
	{
		echo date('Y-m-d H:i:s');
		echo date('Y-m-d H:i:s', time() - 60);
	}
	public function login(){
		$user_name = $this->input->post('username');
		$password = $this->input->post('password');
		$user_data = $this->usermodel->getuser($user_name);
		if(@$user_data){
			
			$user_rateLimit = $this->ratelimit->getRequest($user_data->user_id);
			if($user_rateLimit == 0){
				 return $this->output->set_content_type('application/json')->set_status_header(401)->set_output(json_encode(['error' => 'Rate limit exceeded']));
			}
		}
		$checkUser = $this->usermodel->checkuser($user_name, $password);
		if ($checkUser == 0) {
			return $this->output->set_content_type('application/json')->set_status_header(401)->set_output(json_encode(['error' => 'Invalid credentials']));
        } elseif ($checkUser['status'] === 'expired') {
			return $this->output->set_content_type('application/json')->set_status_header(401)->set_output(json_encode(['error' => 'Password expired on -'.$checkUser['expiry_date']]));
		}else{
			$userData = $checkUser['user'];
			if (!$this->config->item('allow_multiple_sessions')) {
				if(get_cookie('session_id') == $userData->session_id && $userData->session_id != ''){
					return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(403)
                        ->set_output(json_encode(['error' => 'User already logged in']));
				}
                
            }
			$user_session_id = session_id();
        	$this->db->update('users', ['session_id' => $user_session_id], ['user_id' => $userData->user_id]);
			$sessionData = [
                'user_id' => $userData->user_id,
                'user_name' => $user_name,
            ];
			
			$this->session->set_userdata($sessionData);
            set_cookie('session_id', $user_session_id, 7200); 
			return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode(['success' => 'Login Successfull ','password_expiry'=>'Password will expire on -'.$userData->password_expiry]));
		}
	}
	public function logout() {
        $this->session->sess_destroy();
        delete_cookie('session_id');
        return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode(['success' => 'Logout Successfull ']));
    }
	
}
