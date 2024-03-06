<?php
namespace Alphansotech\GatewayStreaming;

class GatewayStream{
	public $wsc_api_key = '';
	public $wsc_access_key = '';
	public $wsc_api_baseurl = '';
	function __construct($wsc_api_key_id, $wsc_access_key_id){
		$this->wsc_api_key = $wsc_api_key_id;
		$this->wsc_access_key = $wsc_access_key_id;
		$this->wsc_api_baseurl = 'https://api.video.wowza.com/api/v1.8';
	}

	function sayHi(){
		return "Hi ".rand(0,1000).' I am '.$this->wsc_api_key;
	}

	public function GetLiveStreaming($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"wsc-api-key:"		. $this->wsc_api_key,
			"wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "GET");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}
}