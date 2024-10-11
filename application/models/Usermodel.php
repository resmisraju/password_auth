<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usermodel extends CI_Model {

	public function getuser($user_name){
		$user = $this->db->get_where('users',['user_name' => $user_name])->row();
		return $user;
	}
	public function checkuser($user_name, $password) {
        $user = $this->db->get_where('users', ['user_name' => $user_name])->row();
		if ($user && password_verify($password, $user->password)) {
			$current_date = strtotime(date('Y-m-d H:i:s'));
			$expiry_date = strtotime($user->password_expiry);
			if ($current_date > $expiry_date) {
                return ['status' => 'expired', 'expiry_date' => $user->password_expiry];
            }
            return ['status' => 'success', 'user' => $user];
        }
        return false;
    }
}