<?php

namespace UKMNorge\APIBundle\Util;

class Signer {

	private $sys_key;
	private $sys_secret;

	public function __construct($sys_key, $sys_secret) {
		$this->sys_key = $sys_key;
		$this->sys_secret = $sys_secret;
	}
	
	public function sign($data) {
		$data = array_change_key_case($data, CASE_LOWER);
		ksort($data);
		#var_dump($params);
		if(isset($data['time'])) {
			$time = $data['time'];
			unset($data['time']);
		}
		else {
			$time = time();
		}
		$params = http_build_query($data);
		$params = $this->sys_key . $params . $time . $this->sys_secret;
		#var_dump($params);
		return hash('sha256', $params);
	}
	
	public function responseSign($sign, $time, $response) {
		$response = http_build_query($response);
		return hash('sha256', $sign . $time . $this->sys_secret . $response);
	}
}