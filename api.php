<?php
/**
 * Proxy script to communicate cross-domain with a CouchDB Server via PHP
 */

// Config file containing all constants used below must be included
require_once('config.php');

// Just in case
disable_magic_quotes();

session_start();

// Attempt login with hardcoded user credentials
if (isset($_SESSION['logged']) === FALSE) {
	if ($_POST['user'] === USER_USERNAME && $_POST['pass'] === USER_PASSWORD) {
		$_SESSION['logged'] = TRUE;
	} else {
		echo json_encode(array("error" => "ACCESS_DENIED"));
		die;
	}
}

// Build request and send it to the CouchDB Server
if (isset($_SESSION['logged']) && $_SESSION['logged'] === TRUE) {
	$url = COUCH_PROTOCOL.'://'.COUCH_USER.':'.COUCH_PASS.'@'.COUCH_DOMAIN.'/'.COUCH_DATABASE.'/';
	$method = $_SERVER['REQUEST_METHOD'];
	$content = ${"_$method"};

	$response = exec_request($url, $method, $content);
	
	echo $response;
}


//////////////////////////////////////////////////////////////
// And all the magic goes here								//
//////////////////////////////////////////////////////////////

function exec_request($url, $method, $content) {
	$content = json_encode($content);

	$opts = array('http' =>
	    array(
	        'method'  => $method,
	        'content' => $content,
	        'header'  => 'Content-Type: application/json'
	    )
	);	

	try {
		$context  = stream_context_create($opts);
		$result = file_get_contents($url, false, $context);	
	} catch (Exception $e) {
		$result = json_encode(array("error" => "INVALID_REQUEST_PARAMS"));
	}

	return $result;
} 

function disable_magic_quotes() {
	if (get_magic_quotes_gpc()) {
	    	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
		while (list($key, $val) = each($process)) {
			foreach ($val as $k => $v) {
			    unset($process[$key][$k]);
			    if (is_array($v)) {
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			    } else {
			        $process[$key][stripslashes($k)] = stripslashes($v);
			    }
			}
		}
		unset($process);
	}
}
