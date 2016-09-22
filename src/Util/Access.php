<?php

namespace UKMNorge\APIBundle\Util;

use UKMNorge\APIBundle\Util\AccessInterface; 

require_once('UKM/curl.class.php');
use UKMCURL;

class Access implements AccessInterface {
	
	protected $api_key;
	protected $sys_key;
	private $errors = array();

	public function __construct($api_key, $sys_key, $sys_secret) {
		$this->api_key = $api_key;
		$this->sys_key = $sys_key;

		$this->signer = new Signer($sys_key, $sys_secret);
	}

	// If this is called, got will sign the request-data and compare it to the signed request.
	// TODO: Implement.
	// Params:
	// $data - Key/value-array with all parameters in request.
	public function validate($data) {
		$this->SIGN1 = $data['SIGNED_REQUEST'];
		unset($data['SIGNED_REQUEST']);
		$this->parameters = $data;
		#$signer1 = new Signer('test', 'pah');
		#dump($signer1->sign($data)); # ef140725a1fccef1f7e25ed12966feec839d06c651c0804ba0676f9ca26e2a97
	}

	// CURLer UKMno for å sjekke om spørrende system har rettigheten den spør om til dette systemet.
	public function got($permission) {
		$curl = new UKMCURL();
		if(true)
			$url = 'http://api.ukm.dev/ekstern:v1/signedTilgang2';
		else
			$url = 'http://api.ukm.no/ekstern:v1/signedTilgang2';

		$this->time = time();

		$data = $this->SIGN1.$permission;
		#var_dump($data);
		$this->SIGN2 = $this->signer->sign($this->time, $data);
		if(false == $this->SIGN2) {
			$this->errors[] = "UKMAPIBundle: Klarte ikke å signere requesten.";
			return false;
		}
		#var_dump($this->SIGN2);
		#dump($this->SIGN2);

		$postdata = array();
		$postdata['api_key'] = $this->api_key;
		$postdata['time'] = $this->time;
		$postdata['sys_key'] = $this->sys_key;
		$postdata['permission'] = $permission;
		$postdata['sign1'] = $this->SIGN1;
		$postdata['externalTime'] = $this->parameters['time'];
		$postdata['sign2'] = $this->SIGN2;

		$curl->post($postdata);
		try {
			$result = $curl->process($url);
			
			error_log('UKMAPIBundle: Curl-resultat: '.var_export($result, true));
			if(!is_object($result)) {
				$this->errors[] = 'UKMAPIBundle: UKMapi svarte ikke med en godkjent status!';
				return false;
			}
			if(isset($result->errors))
				$this->errors = $result->errors;

			if($result->success == false)
				return $result->success;
			
			// Validate that result is from UKM.no
			$signature = $result->sign;
			unset($result->sign);
			
			$this->SIGN3 = $this->signer->responseSign($this->SIGN1, $this->time, $result);

			if($this->SIGN3 != $signature) {
				$this->errors[] = 'UKMAPIBundle: UKMapi klarte ikke å signere riktig! Kontakt support.';
				return false;
			}

			if(isset($result->errors))
				$this->errors = $result->errors;
			return $result->success;
		}
		catch (Exception $e) {
			#echo 'UKMAPIBundle: Curl feilet.<br>';
			error_log('UKMAPIBundle: Tilgangssjekken feilet, med følgende feilmelding: '.$e->getMessage());
			return false;
		}
	}

	public function errors() {
		return $this->errors;
	}
}