<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ratelimit {
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }
    public function getRequest($user_id) {
        $date = date('Y-m-d H:i:s');
        $this->CI->db->where('user_id', $user_id);
        $this->CI->db->set('request_count', 'request_count + 1',FALSE);
        $this->CI->db->set('last_request_time', $date);
        $this->CI->db->update('user_rate_limit');

        $rateLimit = $this->get_limit($user_id);
        if(@$rateLimit == ''){
            $this->createRequest($user_id);
        }
        
        if(@$rateLimit && strtotime($rateLimit->request_time) >=  strtotime(date('Y-m-d H:i:s', time() - 60))){
            if ($rateLimit && $rateLimit->request_count > $this->CI->config->item('rate_limit')) {
              return false; 
            }
        }
        
        if ($rateLimit && $rateLimit->request_count > $this->CI->config->item('rate_limit')) {
            return false; 
        }
        return true;
    }
    public function get_limit($user_id) {
        
        $limit =  $this->CI->db->get_where('user_rate_limit', ['user_id' => $user_id])->row();
        return $limit;  
    }
    public function createRequest($user_id) {
        $data = array(
            'user_id' => $user_id,
            'request_count' => 1,
            'request_time' => date('Y-m-d H:i:s'),
            'last_request_time' => date('Y-m-d H:i:s'),
        );
        $this->CI->db->insert('user_rate_limit',$data);
    }
}