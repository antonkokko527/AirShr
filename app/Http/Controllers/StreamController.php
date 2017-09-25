<?php namespace App\Http\Controllers;

use Request;
use App\StreamingStatus;
use App\User;

class StreamController extends JSONController {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	
	public function updateStreamingStatus() {
		
		// get parameters
		$userId	=				Request::input('userId');
		$stationId=				Request::input('stationId');
		$streamingStatus=		Request::input('streamingStatus');
		$userLat=				Request::input('userLat');
		$userLng=				Request::input('userLng');
		
		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($stationId)) {
			$this->setErrorCode("STATION_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($userLat)) {
			$this->setErrorCode("USER_LAT_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($userLng)) {
			$this->setErrorCode("USER_LNG_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($streamingStatus)) {
			$this->setErrorCode("STREAM_STATUS_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$user = User::findUserByUniqueID($userId);
		
		if (!$user) {
			$this->setErrorCode("USER_INFO_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$newStreamingStatus = StreamingStatus::addStreamingStatus($user->id, $stationId, $streamingStatus, $userLat, $userLng);
		if (!$newStreamingStatus) {
			$this->setErrorCode("STREAM_STATUS_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
}
