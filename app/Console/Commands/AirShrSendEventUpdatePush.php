<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Event;

class AirShrSendEventUpdatePush extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:sendeventupdatenotify';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send event update push notification.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		try {
		
			$eventId = $this->argument('eventid');
			$dev = $this->argument('dev');
			
			$devEnv = empty($dev) ? false: true;
			
			$pushAlert = $this->argument('push_alert');
			$pushAction = $this->argument('push_action');
			
			$pushAlert = empty($pushAlert) ? '' : $pushAlert;
			$pushAction = empty($pushAction) ? '' : $pushAction;
			
			$pushToken = $this->argument('push_token');
			$pushDeviceType = $this->argument('push_device_type');
						
			$event = Event::find($eventId);
			
			if (empty($pushToken)) {
				$pushToken = $event->push_token;
			}
			
			if (empty($pushDeviceType)) {
				$pushDeviceType = $event->device_type;
			}
			
			if (!empty($pushToken)) {
					
				if ($pushDeviceType == 'iOS')
					$this->sendEventUpdateiOSPushNotification($pushToken, $eventId, $event->event_data_status, $devEnv, $pushAction, $pushAlert);
				else {
					$this->sendEventUpdateAndroidPushNotification($pushToken, $event->getJSONArrayForEventDetail(), $devEnv, $pushAction, $pushAlert);
				}
				
				// decrease detail view count
				$event->detail_views = $event->detail_views - 1;
				$event->save();
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->error($ex);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['eventid', InputArgument::REQUIRED, 'Event ID'],
			['push_alert', InputArgument::OPTIONAL, 'Push alert message.'],
			['push_action', InputArgument::OPTIONAL, 'Push alert action.'],
			['dev', InputArgument::OPTIONAL, 'Development Environment'],
			['push_token', InputArgument::OPTIONAL, 'Explicit push token.'],
			['push_device_type', InputArgument::OPTIONAL, 'Explicit push device type']
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			
		];
	}
	
	
	protected function sendEventUpdateiOSPushNotification($push_token, $event_id, $valid, $dev, $pushAction, $pushAlert) {
	
		if (env("APP_DEBUG")) {
			\Log::info("Sending event update push notification - deviceToken: {$push_token}, event_id: {$event_id}");
		}
	
		if (empty($pushAlert)) {
			
			$data = array(
					'aps' => array(
							'content-available' => 1
					),
					'event_id' => $event_id,
					'valid' => ($valid == 1) ? '1' : '0'
			);
			
		} else {
			
			$data = array(
					'aps' => array(
							'alert' => $pushAlert,
							'sound' => 'default'
					),
					'event_id' => $event_id,
					'valid' => ($valid == 1) ? '1' : '0'
			);
			
			if (!empty($pushAction)) $data['push_action'] = $pushAction;
		}
	
		$this->_sendiOSPushNotification($push_token, json_encode($data), $dev);
	}
	
	
	protected function sendEventUpdateAndroidPushNotification($push_token, $eventArray, $dev, $pushAction, $pushAlert) {
			
		if (env("APP_DEBUG")) {
			\Log::info("Sending event update android push notification - deviceToken: {$push_token}, eventInfo: " . json_encode($eventArray));
		}
	
		if (!empty($pushAlert)) {
			$eventArray['push_alert'] = $pushAlert;
		}	
		
		if (!empty($pushAction)) {
			$eventArray['push_action'] = $pushAction;
		}
		
		$this->_sendAndroidPushNotification($push_token, $eventArray, $dev);
	
	}
	
	
	/**
	 * Send push notification to Android device
	 */
	protected function _sendAndroidPushNotification($deviceToken, $payload, $devMode = false) {
	
		if (env("APP_DEBUG")) {
			\Log::info("Sending android push notification - deviceToken: {$deviceToken}, payload: " . json_encode($payload));
		}
	
		$url = 'https://android.googleapis.com/gcm/send';
	
		$fields = array(
				'registration_ids' => array($deviceToken),
				'data' => $payload
		);
	
		$headers = array(
				'Authorization: key=' . \Config::get('app.Google_API_Key'),
				'Content-Type: application/json'
		);
	
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $url);
	
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		// Disabling SSL Certificate support temporarly
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	
		$result = curl_exec($ch);
		if ($result === FALSE) {
			\Log::error("Android push notification failed. " . curl_error($ch));
			return false;
		}
	
		curl_close($ch);
	
		return true;
	}
	
	
	/**
	 * Send Push Notification - iOS
	 */
	
	protected function _sendiOSPushNotification($deviceToken, $payload, $devProfile = false) {
	
		if (env("APP_DEBUG")) {
			\Log::info("Sending push notification - deviceToken: {$deviceToken}, payload: {$payload}");
		}
	
		$passphrase = \Config::get('app.PushCertPassPhrase');
	
		$ctx = stream_context_create();
	
		if ($devProfile) {
			stream_context_set_option($ctx, 'ssl', 'local_cert', base_path('cert/airshr_push_cert_dev.pem'));
		} else {
			stream_context_set_option($ctx, 'ssl', 'local_cert', base_path('cert/airshr_push_cert.pem'));
		}
	
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
		// Open a connection to the APNS server
	
		if ($devProfile) {
			$fp = stream_socket_client(
					'ssl://gateway.sandbox.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		} else {
			$fp = stream_socket_client(
					'ssl://gateway.push.apple.com:2195', $err,
					$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		}
	
		if (!$fp) {
			\Log::error("Failed to connect APNS Server: $err $errstr");
			return false;
		}
	
		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
	
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
	
		if (!$result) {
			if (env("APP_DEBUG")) {
				\Log::info("Message not delivered.");
			}
		}
		else {
			if (env("APP_DEBUG")) {
				\Log::info("Message delivered.");
			}
		}
	
		// Close the connection to the server
		fclose($fp);
	
		return $result;
	}

}
