<?php
namespace Alphansotech\GatewayStreaming;

use App\Models\LiveStreaming;

class GatewayStream{
	public $wsc_api_key = '';
	public $wsc_access_key = '';
	public $wsc_api_baseurl = '';
	public $auth_token = '';
    public $livestream = '';
	function __construct(){
		$this->wsc_api_baseurl = env('LIVE_STREAMING_URL');
		$this->auth_token = env('LIVE_STREAMING_AUTH');

        $this->livestream = new LiveStreaming;
	}

	/* ========== Start:: Wowza API Functions ========== */
    
    /* Search Live stream by stream title */
    public function SearchLiveStream($txt) {
        $data = LiveStreaming::where('stream_title', $txt)->get();
        if(count($data) > 0){
            return ['status' => 1, 'message' => 'Live stream found', 'data' => json_decode(json_encode($data), true)];
        }else{
            return ['status' => 0, 'message' => 'Live stream not found'];
        }
	}

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
            "name"                  => $data['sname'],
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
                'user_id' => $data['user_id'],
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
	public function getAllLiveStreams($user_id){
        $data = LiveStreaming::where('user_id', $user_id)->get();
        if(!empty($data)){
            return ['status' => 1, 'message' => 'Live streams data found.', 'data' => json_decode(json_encode($data), true)];
        }else{
            return ['status' => 0, 'message' => 'Live streams data not found.'];
        }

		// $url = $this->wsc_api_baseurl."/live_streams";
		// $header = [
		// 	"Content-Type:"  	. "application/json",
		// 	"charset:"			. "utf-8",
		// 	"Authorization: Bearer ". $this->auth_token
		// 	// "wsc-api-key:"		. $this->wsc_api_key,
		// 	// "wsc-access-key:"	. $this->wsc_access_key,
		// ];

		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "GET");
		// curl_setopt($ch, CURLOPT_URL,$url);
		// curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

		// $server_output = curl_exec($ch);
		// $err = curl_error($ch);
		// curl_close ($ch);
		// $output = json_decode($server_output);
		// return $output;
	}

	/* Get Live Streaming Detail */
	public function GetLiveStreaming($user_id, $wowza_id) {
        $data = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->first();
        $wowzaData = $this->GetWowzaSingleStreaming($wowza_id);
        if(!empty($data)){
            return ['status' => 1, 'message' => 'Live stream data found.', 'data' =>  json_decode(json_encode($data), true), 'wowzaData' => $wowzaData];
        }else{
            return ['status' => 0, 'message' => 'Live stream data not found.'];
        }	
	}

	/* Update Live Stream */
	public function UpdateLiveStream($user_id, $wowza_id, $data){
        $getData = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->get()->first();
        if(!empty($getData)){
            /* $data should be json encoded */
            $url = $this->wsc_api_baseurl."/live_streams/".$wowza_id;
            $header = [
                "Content-Type:"  	. "application/json",
                "charset:"			. "utf-8",
                "Authorization: Bearer ". $this->auth_token
                // "wsc-api-key:"		. $this->wsc_api_key,
                // "wsc-access-key:"	. $this->wsc_access_key,
            ];

            $postdata['live_stream'] = [
                "name"                  => $data['sname'],
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
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
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
                    'stream_title' => $outputData->name,
                    'description' => $outputData->description,
                    'state' => $outputData->state,
                    'billing_mode' => $outputData->billing_mode,
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

                $update = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->update($input);
                if($update) {
                    return ['status' => 1, 'message' => 'Live Streaming update successully.'];
                }else{
                    return ['status' => 0, 'message' => 'Live Streaming not update please try again 1.'];
                }
            }else{
                return ['status' => 0, 'message' => 'Live Streaming not update please try again 2.'];
            }
        }else{
            return ['status' => 0, 'message' => 'Live Streaming details not found.'];
        }
	}

	/* Delete Live Streaming */
	public function DeleteLiveStreaming($user_id, $wowza_id) {
        $getData = $this->GetLiveStreaming($user_id, $wowza_id);
        if(!empty($getData) && $getData['data']['state'] == 'stopped'){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id";
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
            
            if($output == null){
                $delete = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->delete();
                return ['status' => 1, 'message' => 'Live stream remove successfully.'];
            }else{
                return ['status' => 1, 'message' => $output->meta->message];
            }
        }else if(!empty($getData) && $getData['data']['state'] == 'started'){
            return ['status' => 0, 'message' => 'Live stream is started, please stop first and then remove.'];
        }else{
            return ['status' => 0, 'message' => 'Live stream details not found.'];
        }
	}

	/* Start Live Streaming */
	public function LiveStreamingStart($user_id, $wowza_id) {
        $streamData = $this->GetLiveStreaming($user_id, $wowza_id);
        if($streamData['status'] == 1 && isset($streamData['data']['state']) && $streamData['data']['state'] == 'stopped'){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id/start";
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
            if(isset($output->live_stream) && $output->live_stream->state == 'starting'){
                $update = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->update(['state' => 'started']);
                do {
                    $streamStatus = $this->LiveStreamingStatus($user_id,$wowza_id);
                } while ($streamStatus['status'] == 1 && isset($streamStatus['data']['live_stream']) && $streamStatus['data']['live_stream']['state'] != 'started');

                return ['status' => 1, 'message' => 'Live stream started'];
            }else{
                return ['status' => 0, 'message' => $output->meta->message];
            }
        }else if($streamData['status'] == 1 && isset($streamData['data']['state']) && $streamData['data']['state'] == 'started'){
            return ['status' => 2, 'message' => 'Live starem already started.'];
        }else{
            return ['status' => 0, 'message' => 'Something went wrong please try again.'];
        }
	}

	/* Stop Live Streaming */
	public function LiveStreamingStop($user_id, $wowza_id) {
        $streamData = $this->GetLiveStreaming($user_id, $wowza_id);
        if($streamData['status'] == 1 && isset($streamData['data']['state']) && $streamData['data']['state'] == 'started'){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id/stop";
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
            if(isset($output->live_stream) && $output->live_stream->state == 'stopped'){
                $update = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->update(['state' => 'stopped', 'stream_status' => 0, 'advertisement_status' => 0]);
                return ['status' => 1, 'message' => 'Live stream stopped'];
            }else{
                return ['status' => 0, 'message' => $output->meta->message];
            }
        }else{
            return ['status' => 0, 'message' => 'Live stream already stopped'];
        }
	}

	/* Reset Live Stream */
	public function LiveStreamingReset($user_id, $wowza_id){
        $streamData = $this->GetLiveStreaming($user_id, $wowza_id);
        if($streamData['status'] == 1 && isset($streamData['data']['state']) && $streamData['data']['state'] == 'started'){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id/reset";
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
            
            if(isset($output->live_stream) && $output->live_stream->state == 'resetting'){
                do {
                    $streamStatus = $this->LiveStreamingStatus($user_id, $wowza_id);
                } while ($streamStatus['status'] == 1 && isset($streamStatus['data']['live_stream']) && $streamStatus['data']['live_stream']['state'] != 'started');

                return ['status' => 1, 'message' => 'Live stream reset'];
            }else{
                return ['status' => 0, 'message' => $output->meta->message]; 
            }
        }else{
            return ['status' => 0, 'message' => 'Live stream stopped, please start live stream to reset a stream'];
        }
	}

	/* Regenerate Connection Code */
	public function RegenerateConnectionCode($user_id, $wowza_id){
        $getData = $this->GetLiveStreaming($user_id, $wowza_id);
        if(!empty($getData) && $getData['status'] == 1){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id/regenerate_connection_code";
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
        }else{
            return ['status' => 0, 'message' => 'Live Streaming details not found.'];
        }
	}

	/* Show Live Streaming Status */
	public function LiveStreamingStatus($user_id, $wowza_id) {
        $getData = $this->GetLiveStreaming($user_id, $wowza_id);
        if(!empty($getData) && $getData['status'] == 1){
            $url = $this->wsc_api_baseurl."/live_streams/$wowza_id/state";
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
            
            return ['status' => 1, 'message' => 'Status found', 'data' => json_decode(json_encode($output), true)];
        }else{
            return ['status' => 0, 'message' => 'Live Streaming details not found.'];
        }
	}

    /* Publish Live Streaming */
    public function LiveStreamingPublish($user_id, $wowza_id){
        $getData = $this->GetLiveStreaming($user_id, $wowza_id);
        if(!empty($getData) && $getData['status'] == 1){
            $getStream = $this->GetWowzaSingleStreaming($wowza_id);
            if(isset($getStream->live_stream)) {
                $streamStatus = $this->LiveStreamingStatus($user_id, $wowza_id);
                if($streamStatus['status'] == 1 && isset($streamStatus['data']['live_stream']) && $streamStatus['data']['live_stream']['state'] == 'stopped'){
                    return ['status' => 0, 'message' => 'Live Streaming is not started please try again.'];
                }
                if(isset($streamStatus['data']['live_stream']['state'])){
                    if($streamStatus['data']['live_stream']['state'] == 'started' || $streamStatus['data']['live_stream']['state'] == 'starting' ){
                        do {
                            $streamStatusCheck = $this->LiveStreamingStatus($user_id,$wowza_id);
                        } while ($streamStatusCheck['status'] == 1 && isset($streamStatusCheck['data']['live_stream']) && $streamStatusCheck['data']['live_stream']['state'] != 'started');
                        $update = LiveStreaming::where(['user_id' => $user_id, 'wowza_id' => $wowza_id])->update(['stream_status' => 1, 'advertisement_status' => 0]);

                        return ['status' => 1, 'message' => 'Live stream published', 'data' => ['user_id' => $user_id, 'stream_data' => $getData['data']]];
                    }else{
                        return ['status' => 0, 'message' => 'Live Streaming is not started please try again.3'];
                    }
                }else{
                    return ['status' => 0, 'message' => 'Live Streaming is not started please try again.2'];
                }
            }else if(isset($getStream->meta)) {
                $streamingData = $getStream->meta;
                return ['status' => 0, 'message' => $streamingData->message];
            } else {
                return ['status' => 0, 'message' => 'Live Streaming is not started please try again.1'];
            }
        }else{
            return ['status' => 0, 'message' => 'Live Streaming details not found.'];
        }
    }

    /* Get Wowza Single Streaming */
    public function GetWowzaSingleStreaming($wowza_id){
        $url = $this->wsc_api_baseurl."/live_streams/$wowza_id";
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

	public function LiveStreamingPlayingStatus($user_id, $wowza_id) {
		$getData = $this->GetLiveStreaming($user_id, $wowza_id);
        if(!empty($getData) && $getData['status'] == 1){
            $url = $this->wsc_api_baseurl."/analytics/ingest/live_streams/$wowza_id";
            $header = [
                "Content-Type:"  	. "application/json",
                "charset:"			. "utf-8",
                "Authorization: Bearer ". $this->auth_token
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
            
            if(isset($output->live_stream)){
                $data = json_decode(json_encode($output->live_stream), true);
                $res = [
                    'inbound' => $data['connected']['value'],
                    'inbound_bit_rate' => $data['bytes_in_rate']['value'],
                    'frame_size' => $data['frame_size']['value'],
                    'frame_rate' => $data['frame_rate']['value'],
                    'keyframe_interval' => $data['keyframe_interval']['value'],
                ];

                /* Viewers details */
                $url1 = $this->wsc_api_baseurl."/analytics/viewers/live_streams/$wowza_id";

                $ch1 = curl_init();
                curl_setopt($ch1, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
                curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch1, CURLOPT_CUSTOMREQUEST , "GET");
                curl_setopt($ch1, CURLOPT_URL,$url1);
                curl_setopt($ch1, CURLOPT_HTTPHEADER, $header);

                $server_output1 = curl_exec($ch1);
                $err = curl_error($ch1);
                curl_close ($ch1);
                $output1 = json_decode($server_output1);
                
                if(isset($output1->live_stream)){
                    $data1 = json_decode(json_encode($output1->live_stream), true);
                    $res['unique_views'] = $data1['viewers'];
                }else{
                    $res['unique_views'] = 0;
                }

                return ['status' => 1, 'message' => 'Data found', 'data' => $res];
            }else if(isset($output->meta)){
                return ['status' => 0, 'message' => $output->meta->message];
            }else{
                return ['status' => 0, 'message' => 'Live Streaming details not found.'];    
            }
        }else{
            return ['status' => 0, 'message' => 'Live Streaming details not found.'];
        }
	}

	/* ========== End:: Wowza Streaming Publish Status ========== */
	/* ========== Start:: Wowza Player ========== */

	public function LiveStreamingPlayer($wowza_id) {
		$url = $this->wsc_api_baseurl."/players/$wowza_id";
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