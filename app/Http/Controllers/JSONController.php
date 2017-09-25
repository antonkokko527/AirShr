<?php namespace App\Http\Controllers;

class JSONController extends Controller {

	
	protected $_jsonResult = array();
	
	public static $error_codes = array(
	
			//Global Errors
			'SUCCESS' => 				array('code' => 0, 'msg' => 'Success'),
			'UNKNOWN_ERROR' => 			array('code' => 505, 'msg' => 'Unknown error.'),
			
			
			//Event-related
			'RECORD_FILE_PARAM_MISSING' => 			array('code' => 10, 'msg' => 'Recorded file url parameter is missing.'),
			'RECORD_TIMESTAMP_PARAM_MISSING' => 	array('code' => 11, 'msg' => 'Record timestamp parameter is missing.'),
			'RECORD_DEVICEID_PARAM_MISSING' => 		array('code' => 12, 'msg' => 'Record device id parameter is missing.'),
			'PUSH_TOKEN_PARAM_MISSING' => 			array('code' => 13, 'msg' => 'Push token parameter is missing.'),
			'EVENT_RECORD_INSERT_ERROR' => 			array('code' => 14, 'msg' => 'Error occurred while creating new event record.'),
			'EVENT_ID_PARAM_MISSING'	=>			array('code' => 15, 'msg' => 'Event ID Parameter is missing.'),
			'EVENT_NOT_FOUND'	=>					array('code' => 16, 'msg' => 'Event information is not found.'),
			'EVENT_TAG_ID_PARAM_MISSING' =>			array('code' => 17, 'msg' => 'Tag ID parameter is missing.'),
			'EVENT_CANNOT_BE_SAVED' =>				array('code' => 18, 'msg' => 'Event informatin could not be updated.'),
			// Listener Service Related
			
			
			
			// Station Related
			'STATION_VOTE_INFO_PARAM_MISSING'=> 	array('code' => 30, 'msg' => 'Station vote information is missing.'),
			'STATION_ID_PARAM_MISSING'=> 			array('code' => 31, 'msg' => 'Station Id parameter is missing.'),
			
			
			// Stream Related
			'STREAM_STATUS_PARAM_MISSING'=> 		array('code' => 41, 'msg' => 'Streaming status parameter is missing.'),
			'STREAM_STATUS_INSERT_ERROR'	=> 		array('code' => 42, 'msg' => 'Streaming status can not be added right now.'),
			
			// Tag Related
			'TAG_META_BODY_EMPTY'				=> array('code' => 50, 'msg' => 'Meta data parameter is emtpy.'),
			'WAVEFM_METADATA_INVALIDFORMAT'		=> array('code' => 51, 'msg' => 'WaveFM Meta data is not in correct format.'),
			'WAVEFM_STATION_NOT_FOUND'			=> array('code' => 52, 'msg' => 'WaveFM station information is not found.'),
			'WAVEFM_DATETIME_INVALID'			=> array('code' => 53, 'msg' => 'WaveFM Data and Time can not be parsed.'),
			'TAG_RECORD_INSERT_ERROR'			=> array('code' => 54, 'msg' => 'Error occurred while creating new tag record.'),
			'TAG_RECORD_NOT_FOUND_ERROR'		=> array('code' => 55, 'msg' => 'Tag record can not be found in db.'),
			'TAG_ID_PARAM_MISSING'				=> array('code' => 56, 'msg' => 'Tag ID Parameter is missing.'),
			'TAG_NOT_FOUND'						=> array('code' => 57, 'msg' => 'Tag information is not found.'),
			'STATION_NAME_INVALID'				=> array('code' => 58, 'msg' => 'Station name is Invalid.'),
			'NOVA_METADATA_INVALIDFORMAT'		=> array('code' => 59, 'msg' => 'Nova Meta data is not in correct format.'),
			
			'TAG_CONTENTTYPE_INVALID'			=> array('code' => 40, 'msg' => 'Unable to determine content type from meta stream.'),
			'TAG_METADATA_TYPE_INVALID'			=> array('code' => 61, 'msg' => 'Tag type is missing.'),
			
			// Talk signal Related
			'TALK_SIGNAL_DUPLICATE'				=> array('code' => 62, 'msg' => 'Duplicate talk signal.'),
			'PREV_TAG_NOT_FOUND'				=> array('code' => 63, 'msg' => 'Previous Tag is not found.'),
			'TALK_SIGNAL_NO_ACTION'				=> array('code' => 64, 'msg' => 'No action on this signal.'),
			
			// Firmware Related
			'FIRMWARE_INFO_NOT_FOUND'			=> array('code' => 60, 'msg' => 'Firmware information not found.'),
			
			
			// User Related
			'PHONE_NUMBER_PARAM_MISSING'		=> array('code' => 70, 'msg' => 'Phone number parameter is missing.'),
			'USER_REGISTRATION_FAILED' 			=> array('code' => 71, 'msg' => 'New user registration failed.'),
			'VERIFICATIONCODE_SEND_FAILED'		=> array('code' => 72, 'msg' => 'Unable to send verification code.'),
			'SMS_APPLINK_SEND_FAILED'			=> array('code' => 75, 'msg' => 'Unable to send SMS.'),
			'VERIFICATIONCODE_PARAM_MISSING'	=> array('code' => 73, 'msg' => 'Verification code parameter is missing.'),
			'VERIFICATIONCODE_NO_MATCH'			=> array('code' => 74, 'msg' => 'Verification failed.'),
			
			
			'USER_ID_PARAM_MISSING'				=> array('code' => 76, 'msg' => 'User ID parameter is missing.'),
			'USER_LAT_PARAM_MISSING'			=> array('code' => 77, 'msg' => 'User latitude parameter is missing.'),
			'USER_LNG_PARAM_MISSING'			=> array('code' => 78, 'msg' => 'User longitude parameter is missing.'),
			'USER_INFO_NOT_FOUND'				=> array('code' => 79, 'msg' => 'Such user id does not exist in our database'),
			
			
			// Vote Related
			'VOTE_SELECTION_PARAM_MISSING'		=> array('code' => 80, 'msg' => 'Vote selection parameter is missing.'),
			'RATE_SELECTION_PARAM_MISSING'		=> array('code' => 81, 'msg' => 'Rate selection parameter is missing.'),
			
			
			
			
			'LISTENEROK_TAGGER_NOTOK' => array('code' => 1, 'msg' => 'Listener information has been fetched. Tagger information is not available.'),
			'LISTENERRESULT_AUDIO_INVALID' => array('code' => 2, 'msg' => 'Listener information - audio is invalid.'),
			'FUNCTION_REQUIRED' => array('code' => 300, 'msg' => 'Web service function required.'),
				
			//User Event History
			'USERID_REQUIRED' => array('code' => 10, 'msg' => 'User id has not been specified.'),
			'TIMESTAMP_REQUIRED' => array('code' => 11, 'msg' => 'Timestamp has not been specified.'),
			
			'INVALID_FILE_URL' => array('code' => 13, 'msg' => 'File url is not in correct form.'),
			'INVALID_TIMESTAMP' => array('code' => 14, 'msg' => 'Timestamp is not in correct form.'),
			'RECORDID_NOT_AVAILABLE' => array('code' => 15, 'msg' => 'Unable to get record id from given file path.'),
			'RECORDINFO_SAVE_ERROR' => array('code' => 16, 'msg' => 'Unable to save record information to db.'),
	
			//User Login
			'EMAIL_REQUIRED' => array('code' => 50, 'msg' => 'Email required.'),
			'USERNAME_REQUIRED' => array('code' => 51, 'msg' => 'User name required.'),
			'USER_REGISTRATION_FAILED' => array('code' => 52, 'msg' => 'New user registration failed.'),
			'USER_NOT_FOUND' => array('code' => 53, 'msg' => 'User with such email has not been found.'),
			'USERPROFILE_UPDATE_ERROR' => array('code' => 54, 'msg' => 'Unable to update user profile.'),
				
			//Listener Service
			'NORESULT_FROM_LISTENER' => array('code' => 30, 'msg' => 'Unable to get station name from listener service.'),
			'UNABLE_TO_REGISTER_NEWTOEKN' => array('code' => 31, 'msg' => 'Unable to register new service call track to db.'),
			'TOKEN_REQUIRED' => array('code' => 32, 'msg' => 'Token has not been specified'),
			'LOAD_SERVICECALL_TRACK_BYTOKEN_FAIL' => array('code' => 33, 'msg' => 'Unable to load service call track by token'),
			'NEWEVENT_DATA_PENDING' => array('code' => 34, 'msg' => 'Event data is still pending.'),
			'NEWEVENT_DATA_INVALID' => array('code' => 35, 'msg' => 'New event details can not be fetched right now.'),
			'URL_CALL_FAIL' => array('code' => 38, 'msg' => 'Unable to call update url.'),
				
			//Tagger Service
			'NORESULT_FROM_TAGGER' => array('code' => 40, 'msg' => 'Unable to get tag information from tagger service. Timeout.'),
			'RECORDS_NOT_FOUND' => array('code' => 20,  'msg' => 'No record found on stackmob'),
	);
	
	
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->_jsonResult = array();
	}
	
	protected function setErrorCode($error_type){
			
		$errorInfo = $this->getErrorInfo($error_type);
		
		foreach ($errorInfo as $key => $val) {
			$this->_jsonResult[$key] = $val;
		}
	}	
	
	protected function setJSONOutputInfo($key, $val) {
		$this->_jsonResult[$key] =  $val;
	}
	
	protected function sendJSONOutput() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Sending JSON Output : " . json_encode($this->_jsonResult));
		}
		
		return response()->json($this->_jsonResult);
		
	}
	
	protected function sendJSONPOutput($callback) {
		
		if (env("APP_DEBUG")) {
			\Log::info("Sending JSONP Output : " . json_encode($this->_jsonResult));
		}
		
		return response()->json($this->_jsonResult)->setCallback($callback);
	}
	
	protected function getErrorInfo($error)
	{
		$error_codes = self::$error_codes;
	
		$array = array();
	
		if (isset($error_codes[$error])) {
				
			$array['code'] = $error_codes[$error]['code'];
			$array['msg'] = $error_codes[$error]['msg'];
				
		} else {
				
			$array['code'] = $error_codes['UNKNOWN_ERROR']['code'];
			$array['msg'] = $error_codes['UNKNOWN_ERROR']['msg'];
		}
	
		return $array;
	}
}
