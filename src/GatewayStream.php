<?php
namespace Alphansotech\GatewayStreaming;

class GatewayStream{
	function __construct(){
		$wsc_api_key = 'wsc_api_key_detail';
		$wsc_access_key = 'wsc_access_key_detail';
	}

	function sayHi(){
		return "Hi ".rand(0,1000).' I am '.$this->wsc_api_key;
	}

	function sayHi1(){
		return "Hi12 ".rand(0,1000);
	}
}