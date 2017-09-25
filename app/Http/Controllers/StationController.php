<?php namespace App\Http\Controllers;

use Request;
use App\Station;
use App\User;

class StationController extends JSONController {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index() {
	
		if (env("APP_DEBUG")) {
			\Log::info("Station List Request : " . json_encode(Request::all()));
		}
	
		$stations = Station::where('is_private', '=', '0')->where('is_staging', '=', '0')->get();
		
		// prepare for output data
		$this->setJSONOutputInfo("data", Station::getArrayListForStationList($stations));
	
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	public function getRegionStations() {
		
		// get parameters
		$userLat=				Request::input('userLat');
		$userLng=				Request::input('userLng');
		$userId	=				Request::input('userId');

		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
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
		
		$user = User::findUserByUniqueID($userId);
		
		if (!$user) {
			$this->setErrorCode("USER_INFO_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$this->setJSONOutputInfo("data", Station::GetRegionStationArrayByUserLocation($user->id, $userLat, $userLng));
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	public function saveStationVote() {
		
		// get parameters
		$userId	=				Request::input('userId');
		$stationVoteInfo=		Request::input('stationVoteInfo');
		
		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($stationVoteInfo)) {
			$this->setErrorCode("STATION_VOTE_INFO_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$user = User::findUserByUniqueID($userId);
		
		if (!$user) {
			$this->setErrorCode("USER_INFO_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$voteStationIDList = array();

		$votePairs = explode(";", $stationVoteInfo);
		
		foreach ($votePairs as $votePair) {
			$votePair = trim($votePair);
			if ($votePair == '') continue;
			$keyValuePairs= explode(":", $votePair);
			if (count($keyValuePairs) < 2) continue;
			$key = trim($keyValuePairs[0]);
			$value = trim($keyValuePairs[1]);
			if ($value == '1' && !in_array($key, $voteStationIDList)) {
				$voteStationIDList[] = $key;
			}
		}
		
		Station::UpdateUserStationVote($user->id, $voteStationIDList);
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	public function setProfanityDelay() {
		
		// get parameters
		$stationId=				Request::input('stationId');
		$delay=					Request::input('delay');
		
		if (empty($stationId)) {
			$this->setErrorCode("STATION_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}

		if (empty($delay)) $delay = 0;
			
		try {
			
			$station = Station::findOrFail($stationId);
			$station->profanity_delay= $delay;
			$station->save();
			
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
			
		} catch (\Exception $ex) {
			$this->setErrorCode("UNKNOWN_ERROR");
			return $this->sendJSONOutput();
		}
	}
}
