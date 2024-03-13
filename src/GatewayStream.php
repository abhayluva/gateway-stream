<?php
namespace Alphansotech\GatewayStreaming;
use App\Models\LiveStreaming;
class GatewayStream{
	public $wsc_api_key = '';
	public $wsc_access_key = '';
	public $wsc_api_baseurl = '';
	public $auth_token = '';
	function __construct($authorization){
		// $this->wsc_api_key = $wsc_api_key_id;
		// $this->wsc_access_key = $wsc_access_key_id;
		$this->wsc_api_baseurl = 'https://api.video.wowza.com/api/v2.0';
		$this->auth_token = $authorization;
	}

	/* ========== Start:: Wowza API Functions ========== */
	/* Create Live Stream */
	public function CreateLiveStream($data){
		/* $data should be json encoded */
		$url = $this->wsc_api_baseurl."/live_streams";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

        $postdata['live_stream'] = [
            "name"                  => $data['name'],
            "broadcast_location"    => $data['broadcast_location'],
            "description"           => $data['description'],
            "transcoder_type"       => "transcoded",
            "billing_mode"          => "pay_as_you_go",
            "encoder"               => $data['encoder'],    
            "disable_authentication" => true,
            "aspect_ratio_height"   => "720",
            "aspect_ratio_width"    => "1280",
            "delivery_method"       => "push",
            "player_responsive"     => true,
            "low_latency"           => true,
            "recording"             => true
        ];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		
        if(isset($output->live_stream)) {
            $outputData = $output->live_stream;
            $input = [
                'wowza_id' => $outputData->id,
                'stream_title' => $outputData->name,
                'description' => $outputData->description,
                'state' => $outputData->state,
                'billing_mode' => $outputData->billing_mode,
                'broadcast_location' => $outputData->broadcast_location,
                'recording' => $outputData->recording,
                'encoder' => $outputData->encoder,
                'delivery_method' => $outputData->delivery_method,
                'sdp_url' => $outputData->source_connection_information->sdp_url,
                'application_name' => $outputData->source_connection_information->application_name,
                'stream_name' => $outputData->source_connection_information->stream_name,
                'hls_playback_url' => $outputData->hls_playback_url,
                'stream_price' => $data['stream_price'],
                'price_currency' => $data['price_currency'],
                'image' => $data['image'],
                'player_id' => $outputData->player_id,
                'stream_date' => $data['stream_date'],
                'stream_time' => $data['stream_time']
            ];

            $insert = LiveStreaming::create($input);
            if(isset($insert->wowza_id)) {
				$msg = "Live Streaming create successully.";
                return ['status' => 1, 'message' => $msg];
			}else{
				$msg = 'Live Streaming not crete please try again.';
				return ['status' => 0, 'message' => $msg];
			}
        }else if(isset($output->meta)) {
            $msg = $output->meta;
            return ['status' => 0, 'message' => $msg->message];
        }else{
            $msg = 'Live Streaming not crete please try again.';
            return ['status' => 0, 'message' => $msg];
        }
	}

	/* Get All Live Streams */
	public function getAllLiveStreams(){
		$url = $this->wsc_api_baseurl."/live_streams";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
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

	/* Get Live Streaming Detail */
	public function GetLiveStreaming($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
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

	/* Update Live Stream */
	public function UpdateLiveStream($streamingId, $data){
		/* $data should be json encoded */
		$url = $this->wsc_api_baseurl."/live_streams/".$streamingId;
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Delete Live Streaming */
	public function DeleteLiveStreaming($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "DELETE");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Start Live Streaming */
	public function LiveStreamingStart($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/start";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "PUT");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Stop Live Streaming */
	public function LiveStreamingStop($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/stop";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "PUT");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Reset Live Stream */
	public function LiveStreamingReset($streamingId){
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/reset";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "PUT");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Regenerate Connection Code */
	public function RegenerateConnectionCode($streamingId){
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/regenerate_connection_code";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "PUT");
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		$server_output = curl_exec($ch);
		$err = curl_error($ch);
		curl_close ($ch);
		$output = json_decode($server_output);
		return $output;
	}

	/* Show Live Streaming Status */
	public function LiveStreamingStatus($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/state";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
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

	/* ========== End:: Wowza API Functions ========== */
	/* ========== Start:: Wowza Streaming Publish Status ========== */

	public function LiveStreamingPlayingStatus($streamingId) {
		$url = $this->wsc_api_baseurl."/live_streams/$streamingId/stats";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
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

	/* ========== End:: Wowza Streaming Publish Status ========== */
	/* ========== Start:: Wowza Player ========== */

	public function LiveStreamingPlayer($streamingId) {
		$url = $this->wsc_api_baseurl."/players/$streamingId";
		$header = [
			"Content-Type:"  	. "application/json",
			"charset:"			. "utf-8",
			"Authorization: Bearer ". $this->auth_token
			// "wsc-api-key:"		. $this->wsc_api_key,
			// "wsc-access-key:"	. $this->wsc_access_key,
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

	/* ========== End:: Wowza Player ========== */
	/* ========== Start:: Wowza HLS MulitBitrate URL ========== */

	public function GetHlsBitrateUrls($hlsURL) {
		$playurl	= array();
		$playurls	= array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $hlsURL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    return $playurl;
		}
		curl_close($ch);

		$bitraturl = $result;
		$pieces = explode("\n", $bitraturl);
		unset($pieces[0],$pieces[1]); // remove #EXTM3U
		$pieces = array_map('trim', $pieces); // remove unnecessary space
		$pieces = array_filter($pieces);
		$pieces = array_chunk($pieces, 2); // group them by two's

		$playRootUrl =  strstr($hlsURL, '/live', true);		
		foreach($pieces as $key => $value){
			$value[0] = explode(',', $value[0]);
	        foreach($value[0] as $index => $element) {
	            if(stripos($element, 'RESOLUTION') !== false) {
	                $resolutionElement = str_replace('RESOLUTION=', '', $element);
	                $resolutionElement = explode('x', $resolutionElement);
	                $resolution = (String)$resolutionElement[1].'p';
	                $quality = '';
	                if($resolutionElement[1] >= '720'){
	                	$quality = 'HD';
	                }

	            }
	        }
	        $playurl[$key]['resolution']	= $resolution;
	        $playurl[$key]['quality']		= $quality;
	        $playurl[$key]['url']			= str_replace('../', $playRootUrl.'/', $value[1]);
		}
		$defaultPlayurl[0]['resolution']	= 'auto';
        $defaultPlayurl[0]['url']			= $hlsURL;
        $defaultPlayurl[0]['quality']		= '';

        $playurls = array_merge($defaultPlayurl,$playurl);

		return $playurls;
	}

	/* ========== End:: Wowza HLS MulitBitrate URL ========== */
}