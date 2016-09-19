<?php

namespace UKMNorge\APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use UKMNorge\APIBundle\Util\Access;


class DefaultController extends Controller
{
    // TODO: Decouple even more, enough to not necessarily make this a Symfony-Bundle?
    public function apiAction(Request $request, $version, $category, $action)
    {	
    	// Try to get api_key
    	if(isset($_POST['API_KEY'])) {
    		$api_key = $_POST['API_KEY'];
    	} elseif(isset($_GET['API_KEY'])) {
    		$api_key = $_GET['API_KEY'];
    	} else {
    		// TODO: Return an error here, not this.
    		echo 'API_KEY is missing';
    		$api_key = 'test';
    	}

    	// Now start the Authentication-process
    	$apiAccess = new Access($api_key, $this->getParameter('ukmapi_system'), $this->getParameter('ukmapi_secret'));
    	
    	// Check if this request is signed
    	if(isset($_POST['SIGNED_REQUEST'])) {
    		$signed_request = $_POST['SIGNED_REQUEST'];
    	}
    	elseif(isset($_GET['SIGNED_REQUEST'])) {
    		$signed_request = $_GET['SIGNED_REQUEST'];
    	}
    	else {
    		$signed_request = null;
    	}

    	if(null != $signed_request) {
    		if($request->method == 'POST')
    			$apiAccess->valid($request->request->all(), $signed_request);
    		elseif($request->method == 'GET') 
    			$apiAccess->valid($request->query->all(), $signed_request);
    	}

    	### Send API-forespørselen til lokal kontroller
    	$dispatcher = $this->getParameter('ukmapi_dispatcher');
    	$dispatcher = new $dispatcher($this->container);
    	$dispatcher->setAccessInterface($apiAccess);
    	$data = $dispatcher->call($version, $category, $action);
    	
    	return new JsonResponse($data);
    }
}
