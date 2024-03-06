This is live streaming library which is based on wowza platform www.wowza.com

1 - install php library
	- composer require alphansotech/gateway-streaming
 
2 - Get the api access token from wowza platform
	- https://auth.wowza.com/client/token-management
 
3 - We have used live streaming api of wowza platform
	- https://developer.wowza.com/docs/wowza-video/api/video/current/overview/
 
4 - Add below code at the start of your page where you want to user library
	- use Alphansotech\GatewayStreaming\GatewayStream
 
5 - Create object of class
	- $data = new GatewayStream(api_access_token)
 
6 - Now you can call eoeza platform any live streaming api with the help of stream_key
	- ex:- $data->GetLiveStreaming($stream_key)

7 - live streaming method which we have created in library
	- GetLiveStreaming($stream_key); /* Get the details of specific strem */
	- LiveStreamingStatus($stream_key); /* Get the status of stream ex:- started/stopped */
	- LiveStreamingStart($stream_key); /* Start the live streaming */
	- LiveStreamingStop($stream_key); /* Stop live streaming */
	- DeleteLiveStreaming($stream_key); /* Delete any stream which you have created */
	- LiveStreamingPlayingStatus($stream_key); /* This is only work in wowza api version 1.8 otherwise it will not work */
	- LiveStreamingPlayer($stream_key);
	- GetHlsBitrateUrls($hlsURL); /* Show the details of recorded stream */
 
8 - to test the live streaming working properly, you can use "Larix Broadcaster" App or you have to implement wowza player in your CMS and after that you have to use HLS link "hls_playback_url" which you will get in "create stream api" and after recording stream it will show in wowza dashboard assets
