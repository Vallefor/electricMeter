<?php
/**
 * Created by PhpStorm.
 * User: vallefor
 * Date: 02.08.14
 * Time: 20:21
 */

require_once $_SERVER["DOCUMENT_ROOT"]."/config.php";

function execCurl($urlAdd,$method='GET',$data=false)
{
	//http://<HC2 ip address>/api/sceneControl?id=14&action=start
	$URL='http://192.168.1.138/api/'.$urlAdd;
	$username=HC2_LOGIN;
	$password=HC2_PASS;


	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$URL);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($ch, CURLOPT_HEADER, false);
	if($method=='PUT')
		curl_setopt($ch, CURLOPT_PUT, true);

	if($data)
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;
	//echo $status_code.":".$result;
}
?>