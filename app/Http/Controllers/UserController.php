<?php namespace App\Http\Controllers;

use Request;
use App\User;
use App\PhoneVerification;

class UserController extends JSONController {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	
	/**
	 *  Register new user
	 *
	 */
	public function store()
	{
		if (env("APP_DEBUG")) {
			\Log::info("New User Registration Request : " . json_encode(Request::all()));
		}
	
		// get parameters
		$countrycode = 			Request::input('countrycode');
		$phone_number = 		Request::input('phone_number');
		
		
		if (empty($countrycode)) {
			$countrycode = \Config::get('app.DefaultCountryCode');
		}
	
		// validation
		if (empty($phone_number)) {
			$this->setErrorCode("PHONE_NUMBER_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		
		
		$newUser = null;
		
		// create new user record
		try {
			$newUser = User::create([
					'countrycode'	=> $countrycode,
					'phone_number'	=> $phone_number
					]);
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("USER_REGISTRATION_FAILED");
			return $this->sendJSONOutput();
		}
	
		// prepare for output data
		$this->setJSONOutputInfo("data", $newUser->toArray());
	
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	/*
	 * Send App Link using Sinch! service
	*/
	public function sendAppLink() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Send App Link Request : " . json_encode(Request::all()));
		}
		
		header("Access-Control-Allow-Origin: *");
		
		// get parameters
		$countrycode = 			Request::input('countrycode');
		$phone_number = 		Request::input('phone_number');
		$callback =				Request::input('callback');
		
		if (empty($callback)) $callback = "callback";
		
		if (empty($countrycode)) {
			$countrycode = \Config::get('app.DefaultCountryCode');
		}
		
		// validation
		if (empty($phone_number)) {
			$this->setErrorCode("PHONE_NUMBER_PARAM_MISSING");
			return $this->sendJSONPOutput($callback);
		}

		$fullPhoneNumber = getFullPhoneNumber($countrycode, $phone_number);
		
		$msgId = false;
		
		try {
				
			$payload = array(
					'From'	=> \Config::get('app.Sinch_SMS_From'),
					'Message' => "AirShr is the easiest way to remember any moment on radio. Get the app from your app store here - " . \Config::get('app.AirShrGetAppURL')
			);
				
			$request = \Httpful\Request::post(\Config::get("app.Sinch_SMS_API_URL") . $fullPhoneNumber, json_encode($payload), "application/json");
				
			$request->authenticateWith("application\\" . \Config::get('app.Sinch_API_KEY'), \Config::get('app.Sinch_API_SECRET'));
				
			$request->addHeader("X-Timestamp", date("c"));
			//$request->addHeader("Authorization", "Application " . \Config::get('app.Sinch_API_KEY') . ":" . \Config::get('app.Sinch_API_SECRET'));
				
			$response = $request->send();
		
			if ($response->code == 200){
		
				$result_json = $response->body;
		
				if (!empty($result_json->messageId)) {
					$msgId = $result_json->messageId;
				}
			}
		
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		if ($msgId === false) {
			$this->setErrorCode("SMS_APPLINK_SEND_FAILED");
			return $this->sendJSONPOutput($callback);
		}
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONPOutput($callback);
	}
	
	/**
	 * Request Verification
	 */
	
	public function requestVerification() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Phone Verification Request : " . json_encode(Request::all()));
		}
		
		// get parameters
		$countrycode = 			Request::input('countrycode');
		$phone_number = 		Request::input('phone_number');
		
		if (empty($countrycode)) {
			$countrycode = \Config::get('app.DefaultCountryCode');
		}
		
		// validation
		if (empty($phone_number)) {
			$this->setErrorCode("PHONE_NUMBER_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$verificationCode = '';
		
		for ($i = 0; $i < 4; $i++) {
			$verificationCode .= mt_rand(0, 9);
		}
		
		// create new verification record
		try {
			$newVerification = PhoneVerification::create([
					'countrycode'	=> $countrycode,
					'phone_number'	=> $phone_number,
					'verification_code' => $verificationCode,
					'is_valid'		=> '0'
					]);
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("VERIFICATIONCODE_SEND_FAILED");
			return $this->sendJSONOutput();
		}
		
		$fullPhoneNumber = getFullPhoneNumber($countrycode, $phone_number);
		$msgId = $this->sendVerificationCode($fullPhoneNumber, $verificationCode);
		
		if ($msgId === false) {
			$this->setErrorCode("VERIFICATIONCODE_SEND_FAILED");
			return $this->sendJSONOutput();
		}
		
		$newVerification->msg_id = $msgId;
		$newVerification->is_valid = 1;
		
		try{
			$newVerification->save();
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	/*
	 * Verify phone number
	 */
	
	public function verifyPhoneNumber() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Phone verify : " . json_encode(Request::all()));
		}
		
		// get parameters
		$countrycode = 			Request::input('countrycode');
		$phone_number = 		Request::input('phone_number');
		$verification_code	=	Request::input('verification_code');
		$device_id			=	Request::input('device_id');
		$flashcall_verify	=	Request::input('flashcall_verify');
		
		// other info
		$device_type =			Request::input('device_type');
		$push_token = 			Request::input('push_token');
		
		if (empty($device_type)) $device_type = 'iOS';
		else $device_type = 'Android';
		
		if (empty($push_token)) $push_token = "";
		
		if (empty($countrycode)) {
			$countrycode = \Config::get('app.DefaultCountryCode');
		}
		
		// validation
		if (empty($phone_number)) {
			$this->setErrorCode("PHONE_NUMBER_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($flashcall_verify) || $flashcall_verify != 1) {
		
			if (empty($verification_code)) {
				$this->setErrorCode("VERIFICATIONCODE_PARAM_MISSING");
				return $this->sendJSONOutput();
			}
			
			
			try {
			
				$verification = PhoneVerification::where('countrycode', '=', $countrycode)
												->where('phone_number', '=', $phone_number)
												->orderBy('created_at', 'desc')
												->firstOrFail();
					
			} catch(\Exception $ex) {
				\Log::error($ex);
				$this->setErrorCode("VERIFICATIONCODE_NO_MATCH");
				return $this->sendJSONOutput();
			}
			
			
			if (!$verification->is_valid || $verification_code != $verification->verification_code) {
				
				$this->setErrorCode("VERIFICATIONCODE_NO_MATCH");
				return $this->sendJSONOutput();
			}
			
			// invalidate used verification code
			$verification->is_valid = 0;
			try{
				$verification->save();
			} catch (\Exception $ex) {
				\Log::error($ex);
			}
		
		}
		
		// clean up phone number
		$countrycode = cleanupPhoneNumber($countrycode);
		$phone_number = cleanupPhoneNumber($phone_number);
	
		$existingUser = User::findUserByPhoneNumber($countrycode, $phone_number);
		
		if (!$existingUser) {
			$existingUser = User::createOrFindUserByPhoneNumber($countrycode, $phone_number, $device_id);

			if ($existingUser) {
				// create sample events for this new user
				/*$tagIds = \Config::get('app.StartupEventTags');
				if (!empty($tagIds) && is_array($tagIds)) {
					foreach ($tagIds as $tagId) {
						User::createUserEventFromTag($existingUser->user_id, $tagId, $push_token, $device_type, false);
					}
				}*/
			}	
		}
		
		if (!$existingUser) {
			$this->setErrorCode("USER_REGISTRATION_FAILED");
			return $this->sendJSONOutput();
		}
		
		$this->setJSONOutputInfo("data", $existingUser->getJSONArrayForUserVerification());
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
		
	}
	
	
	
	public function test(){
		
		//echo $this->sendVerificationCode("+601137023526", "4323");
		
	}
	
	
	
	private function sendVerificationCode2($phoneNumber, $verificationCode) {
		$key = \Config::get('app.Sinch_API_KEY');
		$secret = \Config::get('app.Sinch_API_SECRET');
		$phone_number = $phoneNumber;
		
		$user = "application\\" . $key . ":" . $secret;
		$message = array("message"=>"Test");
		$data = json_encode($message);
		$ch = curl_init('https://messagingapi.sinch.com/v1/sms/' . $phone_number);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_USERPWD,$user);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		
		$result = curl_exec($ch);
		
		if(curl_errno($ch)) {
			echo 'Curl error: ' . curl_error($ch);
		} else {
			echo $result;
		}
		
		curl_close($ch);
	}
	
	
	/*
	 * Send Verification Code using Sinch! service
	*/
	private function sendVerificationCode($phoneNumber, $verificationCode) {
	
		$msgId = false;
	
		try {
			
			$payload = array(
				'From'	=> \Config::get('app.Sinch_SMS_From'),
				'Message' => $verificationCode . " is your verification code for AirShr"
			);
			
			$request = \Httpful\Request::post(\Config::get("app.Sinch_SMS_API_URL") . $phoneNumber, json_encode($payload), "application/json");
			
			$request->authenticateWith("application\\" . \Config::get('app.Sinch_API_KEY'), \Config::get('app.Sinch_API_SECRET'));
			
			$request->addHeader("X-Timestamp", date("c"));
			//$request->addHeader("Authorization", "Application " . \Config::get('app.Sinch_API_KEY') . ":" . \Config::get('app.Sinch_API_SECRET'));
			
			$response = $request->send();
	
			if ($response->code == 200){
	
				$result_json = $response->body;
				
				if (!empty($result_json->messageId)) {
					$msgId = $result_json->messageId;
				}
			}
	
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	
		return $msgId;
	}
	
	
}
