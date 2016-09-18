<?php

namespace UKMNorge\APIBundle\Util;

use UKMNorge\APIBundle\Util\AccessInterface; 

require_once('UKM/curl.class.php');
use UKMCURL;

class Access implements AccessInterface {
	
	protected $api_key;
	protected $system;
	private $request = null;
	private $signed_request = null;
	private $errors = array();

	public function __construct($api_key, $system) {
		$this->api_key = $api_key;
		$this->system = $system;
	}

	// If this is called, got will sign the request-data and compare it to the signed request.
	// TODO: Implement.
	// Params:
	// $request - Key/value-array with all POST-parameters in request
	public function valid($request, $signed_request) {
		$this->request = $request;
		$this->signed_request = $signed_request;
	}

	// CURLer UKMno for å sjekke om spørrende system har rettigheten den spør om til dette systemet.
	public function got($permission) {
		$curl = new UKMCURL();
		if(true)
			$url = 'http://api.ukm.dev/ekstern:v1/tilgang';
		else
			$url = 'http://api.ukm.no/ekstern:v1/tilgang';
		
		$data = array();
		$data['API_KEY'] = $this->api_key;
		$data['SYSTEM'] = $this->system;
		$data['PERMISSION'] = $permission;
		
		$curl->post($data);
		try {
			$result = $curl->process($url);
			#var_dump($result);
			#var_dump($curl);
			error_log('UKMAPIBundle: Curl-resultat: '.var_export($result, true));
			if(!is_object($result)) {
				$this->errors[] = 'UKMAPIBundle: UKMapi svarte ikke med en godkjent status!';
				return false;
			}
			else if(isset($result->errors))
				$this->errors = $result->errors;
			return $result->success;
		}
		catch (Exception $e) {
			#echo 'UKMAPIBundle: Curl feilet.<br>';
			error_log('UKMAPIBundle: Curl feilet, med følgende Exception: '.$e->getMessage());
			return false;
		}
	}

	public function errors() {
		return $this->errors;
	}
}