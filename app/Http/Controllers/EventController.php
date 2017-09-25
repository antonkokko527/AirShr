<?php namespace App\Http\Controllers;

use Request;
use App\Event;
use App\Tag;
use App\Station;
use App\ContentType;
use App\TerrestrialStreamDelay;
use App\User;
use App\Remote;
use App\Phone;
use App\User2Remote;
use App\AirShrArtisanQueue;
use App\LinkTrack;
use App\CoverArt;

if (!defined('MATCHER_DELAY_THRESHOLD')) {
	define('MATCHER_DELAY_THRESHOLD', 0);
}

class EventController extends JSONController {

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
			\Log::info("Event List Request : " . json_encode(Request::all()));
		}
		
		// get parameters
		$record_device_id = Request::input('record_device_id');
		$timestamp = Request::input('timestamp');
		
		if (empty($timestamp)) $timestamp = 0;
	
		$events = Event::where('record_timestamp', '>=', $timestamp)
						->where('record_device_id', '=', $record_device_id)
						->orderBy('id', 'asc')
						->with('tagForEvent.coverart')
						->with('tagForEvent.connectContent')
						->with('stationForEvent')
						->with('tagForEvent.connectContent.actionDetail')
						->with('tagForEvent.connectContent.attachments')
						->with('eventCoverArt')
						->get();
		
		// prepare for output data
		$this->setJSONOutputInfo("data", Event::getArrayListForEventList($events));
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	public function redirectLink() {
		
		$url = Request::input('url');
		$event_id = Request::input('event_id');
		
		if (empty($url) || empty($event_id)) {
			echo '';
			die();
		}
		
		LinkTrack::create([
				'event_id'			=> $event_id,
				'click_timestamp'	=> time(),
				'url'				=> $url
		]);
		
		/*\DB::table('airshr_link_tracks')->insert([
				'event_id'			=> $event_id,
				'click_timestamp'	=> time(),
				'url'				=> $url
		]);*/
		
		
		// redirect with 301 - moved permanently
		return redirect($url, 301);
	}
	
	/**
	 * Get diagnostics information for Event
	 * @param bigInt $id : EventID
	 */
	public function getEventDiagnostics($id) {
		
		if (env("APP_DEBUG")) {
			\Log::info("Event Diagnostics View Request : " . json_encode(Request::all()));
		}
		
		// validation
		if (empty($id)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$event = null;
		
		try {
			$event = Event::findOrFail($id);
		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		// prepare for output data
		$this->setJSONOutputInfo("data", $event->getJSONArrayForDiagnostics());
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	/**
	 * Returns cover art id
	 */
	public function getCoverartInfo() {
	
		$who	=				Request::input('who');
		$what	=				Request::input('what');
	
		$coverartInfo = CoverArt::getCoverArtInfo($who, $what);
	
		if ($coverartInfo) {
	
			$this->setJSONOutputInfo("data", array('coverart_id' => $coverartInfo['id']));
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
				
		} else {
				
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
	}
	
	
	/** 
	 * Get Diagnostics information for Event - 2
	 */
	public function getEventDiagnostics2($id) {
	
		if (env("APP_DEBUG")) {
			\Log::info("Event Diagnostics View Request : " . json_encode(Request::all()));
		}
	
		// validation
		if (empty($id)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
	
		$event = null;
	
		try {
			$event = Event::findOrFail($id);
		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
	
		// prepare for output data
		/*$this->setJSONOutputInfo("data", $event->getJSONArrayForDiagnostics2());
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();*/
		
		echo "<pre>";
		echo json_encode($event->getJSONArrayForDiagnostics2(), JSON_PRETTY_PRINT);
		echo "</pre>";
	}
	
	
	/** 
	 * Get single event information
	 * @param id $event_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function show($event_id) {
		
		if (env("APP_DEBUG")) {
			\Log::info("Event View Request : " . json_encode(Request::all()));
		}
		
		// validation
		if (empty($event_id)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$event = null;
		
		try {
			$event = Event::findOrFail($event_id);
			$event->updateEventFirstPollTime();	
		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		// prepare for output data
		$this->setJSONOutputInfo("data", $event->getJSONArrayForEventDetail());
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	/**
	 * Remove event
	 */
	
	public function destroy($event_id) {
		
		if (env("APP_DEBUG")) {
			\Log::info("Event Remove Request : " . json_encode(Request::all()));
		}
		
		// validation
		if (empty($event_id)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$event = null;
		
		try {
			$event = Event::findOrFail($event_id);
			$event->delete();
		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	} 
	
	/**
	 * Accept closest match
	 */
	public function acceptClosestMatch() {
		
		// get parameters
		// $userId	=				Request::input('userId');
		$eventId=				Request::input('eventId');
				
		// validation
		/*if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}*/
		
		if (empty($eventId)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$event = null;
		try {
			$event = Event::findOrFail($eventId);
		} catch (\Exception $ex){
			\Log::error($ex);
		}
		
		if (!$event) {
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		try {
			
			$event->event_data_status = 1;
			$event->event_data_status_updateon = time();
			$event->save();
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			
			$this->setErrorCode("EVENT_CANNOT_BE_SAVED");
			return $this->sendJSONOutput();
		}
				
		$this->setJSONOutputInfo("data", $event->getJSONArrayForEventDetail());
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
		
	}
	
	/**
	 * Save Vote Details for event
	 */
	public function saveMyVote() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Save My vote request : " . json_encode(Request::all()));
		}
		
		// get parameters
		$userId	=				Request::input('userId');
		$eventId=				Request::input('eventId');
		$voteSelection	=		Request::input('voteSelection');
		
		
		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($eventId)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($voteSelection) || $voteSelection == 0) {
			$this->setErrorCode("VOTE_SELECTION_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		try {
			
			$event = Event::findOrFail($eventId);
			
			if ($event->vote_selection == 0) {
				$event->vote_selection = $voteSelection;
				$event->vote_timestamp = time();
				$event->save();

				// update option count for tag
				$tagForEvent = $event->tagForEvent;
				if ($tagForEvent && !$tagForEvent->vote_expired) {
					Tag::IncreaseEventVoteOptionCount($tagForEvent->id, $voteSelection);
				}
			}
			
		} catch(\Exception $ex){
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
				
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	/**
	 * Save music rating for event
	 */
	public function saveEventRate() {
	
		if (env("APP_DEBUG")) {
			\Log::info("Save Event Rate request : " . json_encode(Request::all()));
		}
	
		// get parameters
		$userId	=				Request::input('userId');
		$eventId=				Request::input('eventId');
		$rateOption		=		Request::input('rateOption');
	
	
		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
	
		if (empty($eventId)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
	
		if (empty($rateOption)) {
			$this->setErrorCode("RATE_SELECTION_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
	
		try {
				
			$event = Event::findOrFail($eventId);
				
			if ($event->rate_option == 'no_rate' || empty($event->rate_option)) {
				$event->rate_option = $rateOption;
				$event->rate_timestamp = time();
				$event->save();
			}
				
		} catch(\Exception $ex){
			\Log::error($ex);
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
	
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	/**
	 * Create Event from previous tag of this event
	 */
	
	public function createEventFromPreviousTag() {
		
		// get parameters
		$userId	=				Request::input('userId');
		$eventId=				Request::input('eventId');
		$pushToken		=		Request::input('pushToken');
		$appVersion 	=		Request::input('app_version');
						
		// validation
		if (empty($userId)) {
			$this->setErrorCode("USER_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($eventId)) {
			$this->setErrorCode("EVENT_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($pushToken)) {
			$this->setErrorCode("PUSH_TOKEN_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$device_type =			Request::input('device_type');
		if (empty($device_type)) $device_type = 'iOS';
		else $device_type = 'Android';
		
		if (empty($appVersion)) $appVersion = '';
		
		$user = User::findUserByUniqueID($userId);
		
		if (!$user) {
			$this->setErrorCode("USER_INFO_NOT_FOUND");
			return $this->sendJSONOutput();
		}

		$event = null;
		
		try {
			$event = Event::findOrFail($eventId);
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		if (!$event || empty($event->tag_id)) {
			$this->setErrorCode("EVENT_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$eventTag = $event->tagForEvent;
		
		if (!$eventTag) {
			$this->setErrorCode("TAG_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$prevTag = $eventTag->getJustPrevTag();
		
		if (!$prevTag) {
			$this->setErrorCode("TAG_NOT_FOUND");
			return $this->sendJSONOutput();
		}
				
		$newEvent = User::createUserEventFromTag($userId, $prevTag->id, $pushToken, $device_type, true, 0, $appVersion);

		if (!$newEvent) {
			$this->setErrorCode("EVENT_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		/*$newEvent->record_file = $event->record_file;
		$newEvent->event_lat = $event->event_lat;
		$newEvent->event_lng = $event->event_lng;*/
		
		$this->setJSONOutputInfo("data", $newEvent->getJSONArrayForEventDetail());
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
		
	}
	
	/**
	 * Create Event from Meta tag - for time machine
	 */
	
	public function createEventFromTag() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Event Create From Meta Tag Request : " . json_encode(Request::all()));
		}
		
		// get parameters
		$tag_id	=				Request::input('tag_id');
		$record_device_id = 	Request::input('record_device_id');
		$push_token = 			Request::input('push_token');
		$device_type =			Request::input('device_type');
		$appVersion = 			Request::input('app_version');
		
		// validation
		if (empty($tag_id)) {
			$this->setErrorCode("EVENT_TAG_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		if (empty($record_device_id)) {
			$this->setErrorCode("RECORD_DEVICEID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		if (empty($push_token)) {
			$this->setErrorCode("PUSH_TOKEN_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($device_type)) $device_type = 'iOS';
		else $device_type = 'Android';
		
		if (empty($appVersion)) $appVersion = '';
		
		$newEvent = User::createUserEventFromTag($record_device_id, $tag_id, $push_token, $device_type, true, 0, $appVersion);
		
		if (!$newEvent) {
			$this->setErrorCode("EVENT_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		} 
		
		// prepare for output data
		$this->setJSONOutputInfo("data", $newEvent->getJSONArrayForEventDetail());
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	/**
	 *  Register new event
	 *  
	 */
	public function store()
	{
		if (env("APP_DEBUG")) {
			\Log::info("New Event Create Request : " . json_encode(Request::all()));
		}
		
		// get parameters		
		$record_file = 			Request::input('record_file');
		$record_timestamp = 	Request::input('record_timestamp');
		$record_device_id = 	Request::input('record_device_id');
		$push_token = 			Request::input('push_token');
		$device_type =			Request::input('device_type');
		$button_press_type =	Request::input('button_press_type');
		$record_timestamp_ms = 	Request::input('record_timestamp_ms');
		
		// additional parameters
		$remote_name		=	Request::input('remote_name');
		$remote_voltage		=	Request::input('remote_voltage');
		$phone_model		=	Request::input('phone_model');
		$phone_os			=	Request::input('phone_os');
		$event_lat			=	Request::input('event_lat');
		$event_lng			= 	Request::input('event_lng');
		
		$button_press_key	= 	Request::input('button_press_key');
		if (empty($button_press_key)) $button_press_key = 0;
		
		$appVersion			= 	Request::input('app_version');
		if (empty($appVersion)) $appVersion = '';
		
		// validation
		if (empty($record_file)) {
			$this->setErrorCode("RECORD_FILE_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		if (empty($record_timestamp)) {
			$this->setErrorCode("RECORD_TIMESTAMP_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		if (empty($record_device_id)) {
			$this->setErrorCode("RECORD_DEVICEID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		if (empty($push_token)) {
			$this->setErrorCode("PUSH_TOKEN_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		if (empty($device_type)) $device_type = 'iOS';
		else $device_type = 'Android';
		
		if (empty($button_press_type)) $button_press_type = 'None';
		
		
		// get user by unique id
		$currentUser = User::findUserByUniqueID($record_device_id);
		
		if ($currentUser) {				// found user by this id
			
			// remote manipulation
			if (!empty($remote_name) && strlen($remote_name) >= 4) {
				
				$last4DigitsOfRemote = substr($remote_name, strlen($remote_name) - 4, 4);
				$remote = Remote::findRemoteByLastDigit($last4DigitsOfRemote);
				if ($remote) {			// remote found
					if (!$remote->installed) {
						$remote->updateFirstInstallInformation($remote_name, $remote_voltage);
					}
					User2Remote::addUserRemoteInfo($currentUser->id, $remote->id);
				}
			}
			
			// phone manipulation
			if (!empty($phone_model) || !empty($phone_os)) {
				Phone::addUserPhoneInfo($currentUser->id, $phone_model, $phone_os);
			}
			
			// check if this is first event
			if (!$currentUser->has_event && (!empty($event_lat) && !empty($event_lng))) {
				$currentUser->saveFirstEventInfo($event_lat, $event_lng);
			}
			
		}
		
		
		$newEvent = null;
		// create new event record
		try {
			$newEvent = Event::create([
				'user_id' 			=> 0,     // currently just set to 0
				'record_file' 		=> $record_file,
				'record_timestamp'	=> $record_timestamp,
				'record_device_id'	=> $record_device_id,
				'push_token'		=> $push_token,
				'device_type'		=> $device_type,
				'button_press_type' => $button_press_type,
				'record_timestamp_ms' => $record_timestamp_ms,
				'event_lat'			=> $event_lat,
				'event_lng'			=> $event_lng,
				'remote_name'		=> $remote_name,
				'remote_voltage'	=> $remote_voltage,
				'phone_model'		=> $phone_model,
				'phone_os'			=> $phone_os,
				'button_press_key'	=> $button_press_key,
				'app_request_received_on'	=> time(),
				'app_version'		=> $appVersion
			]);
			
			$newEvent = Event::findOrFail($newEvent->id);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("EVENT_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		
		// call update url asynchronously
		/*$eventUpdateURL = url("event/updateEventWithMatcher") . "?event_id=" . $newEvent->id;
		if (env("APP_DEBUG")) {
			\Log::info("Calling event status update URL : " . $eventUpdateURL);
		}
		$this->urlCallAsync($eventUpdateURL); */

		// queue job for sending Amazon sqs to listener		
		$recommendedStationList = array("wollongongwave", "nova-1069-brisbane", "nova-969-sydney");  // for now just static list, will change later
		try {
			//\Artisan::queue('airshr:sendlistenerservicerequest', ['event_id' => $newEvent->id, 's3_url' => convertHttpURLtoS3($record_file), 'timestamp' => $record_timestamp_ms, 'stations' => implode(",", $recommendedStationList)]);
			//AirShrArtisanQueue::QueueArtisanCommandToAPIQueue('airshr:sendlistenerservicerequest', ['event_id' => $newEvent->id, 's3_url' => convertHttpURLtoS3($record_file), 'timestamp' => $record_timestamp_ms, 'stations' => implode(",", $recommendedStationList)]);
			
			// call artisan directly instead of queueing
			\Artisan::call('airshr:sendlistenerservicerequest', ['event_id' => $newEvent->id, 's3_url' => convertHttpURLtoS3($record_file), 'timestamp' => $record_timestamp_ms, 'stations' => implode(",", $recommendedStationList)]);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
				
		// prepare for output data
		$this->setJSONOutputInfo("data", $newEvent->getJSONArrayForEventDetail());
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	

	public function updateEventWithMatcher() {
		
		set_time_limit(600);
		ini_set("max_execution_time", 600);
		
		if (env("APP_DEBUG")) {
			\Log::info("Update Event With Matcher Request : " . json_encode(Request::all()));
		}
		
		// get parameters
		$event_id = 		Request::input('event_id');
		
		if (empty($event_id)) {
			if (env("APP_DEBUG")) {
				\Log::info("Update Request - Event ID is missing.");
			}	
			return;
		}
		
		$event = null;
		
		try {
			$event = Event::findOrFail($event_id);
		} catch (\Exception $ex) {
			\Log::error($ex);
			return;
		}
		
		// call listener service
		$listenerServiceResult = $this->callListenerService($event->id, $event->record_file, $event->record_timestamp_ms);
		
		if (env("APP_DEBUG")) {
			\Log::info("Listener Service Result for EventID : {$event->id} - " . json_encode($listenerServiceResult));
		}
		
		$matchFound = false;
		if ($listenerServiceResult['success']) {
			$matchFound = true;
		}
		
		$event->event_data_status = $matchFound ? 1 : -1;
		$event->event_data_status_updateon = time();
		if (isset($listenerServiceResult['match_percent'])) {
			$event->match_percent = $listenerServiceResult['match_percent'];
		}
		
		$match_station = false;
		$tag_timestamp = 0;
		
		if ($matchFound) {
			$match_station = Station::getStationObjectByName($listenerServiceResult['station_name']);
			if ($match_station != false) {
				$event->station_id = $match_station->id;
			}
			$event->match_time = $listenerServiceResult['match_time'];
			$match_timestamp = $event->match_time;
			
			$match_delay = $match_timestamp - $event->record_timestamp_ms;
			
			$terrestrialDelay = 0;
				
			if ($match_delay >= MATCHER_DELAY_THRESHOLD) { // Terrestrial Radio Event
					
				// Store Terrestrial Log
				try {
					TerrestrialStreamDelay::create([
						'event_id'					=> $event_id,
						'station_id'				=> $event->station_id,
						'event_timestamp'			=> $event->record_timestamp_ms,
						'match_timestamp'			=> $match_timestamp,
						'terrestrial_stream_delay' 	=> $match_delay
						]);
				} catch(\Exception $ex) {
					\Log::error($ex);
				}
					
			} else {	// Stream Radio Event
					
				// get recent terrestrial log
				$recentTerrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelay($event->station_id);
			
				if ($recentTerrestrialDelay) {
					$terrestrialDelay = $recentTerrestrialDelay->terrestrial_stream_delay;
				}
			
			}
				
			//$tag_timestamp = $event->getEventUserTimestamp() + $terrestrialDelay;
				
			// Time accuracy version 2
			$tag_timestamp = $match_timestamp - $terrestrialDelay;
				
			$event->terrestrial_delay = $terrestrialDelay;
			
			
		} else {
			$event->station_id = Station::$DEFAULT_MATCH_STATION_ID;
			$event->match_time = $event->record_timestamp_ms;
			
			$event->terrestrial_delay = 0;
			$tag_timestamp = $event->match_time;
		}
		
		if ($event->station_id > 0 && $tag_timestamp > 0) {
		
			// use match timestamp
			//$tags = Tag::getMostRecentTagByTimestamp($match_station->id, $tag_timestamp);
		
			// use user timestamp
			$tags = Tag::getMostRecentTagByTimestamp($event->station_id, $tag_timestamp);
		
			$currentTag = $tags['current'];
			$prevTag = $tags['prev'];
		
			if ($currentTag && $currentTag->content_type_id == ContentType::GetSweeperContentTypeID()) {  // if sweeper tag, move to previous tag
				$currentTag = $prevTag;
			}
		
			if ($currentTag) {
					
				$currentTag->createHashForTag(); // create hash for tag linked with event
					
				$event->content_type_id = $currentTag->content_type_id;
				$event->tag_id = $currentTag->id;
					
				$currentTag->increaseEventCount();
			}
		}
		
		
		/*if ($listenerServiceResult['success']) {
			$event->event_data_status = 1;
			$event->event_data_status_updateon = time();
			$event->match_percent = $listenerServiceResult['match_percent'];
			$event->match_time = $listenerServiceResult['match_time'];
			
			$match_station = Station::getStationObjectByName($listenerServiceResult['station_name']);
			
			if ($match_station != false) {
				$event->station_id = $match_station->id;
			}
			
			$match_timestamp = $event->match_time;
			$match_delay = $match_timestamp - $event->record_timestamp_ms;
				
			$terrestrialDelay = 0;
			
			if ($match_delay >= MATCHER_DELAY_THRESHOLD) { // Terrestrial Radio Event
			
				// Store Terrestrial Log
				try {
					TerrestrialStreamDelay::create([
							'event_id'					=> $event_id,
							'station_id'				=> $event->station_id,
							'event_timestamp'			=> $event->record_timestamp_ms,
							'match_timestamp'			=> $match_timestamp,
							'terrestrial_stream_delay' 	=> $match_delay
						]
					);
				} catch(\Exception $ex) {
					\Log::error($ex);
				}
			
			} else {	// Stream Radio Event
			
				// get recent terrestrial log 
				$recentTerrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelay($event->station_id);
				
				if ($recentTerrestrialDelay) {
					$terrestrialDelay = $recentTerrestrialDelay->terrestrial_stream_delay;
				}
				
			}
			
			//$tag_timestamp = $event->getEventUserTimestamp() + $terrestrialDelay;
			
			// Time accuracy version 2
			$tag_timestamp = $match_timestamp - $terrestrialDelay;
			
			$event->terrestrial_delay = $terrestrialDelay;
			
			if ($match_station != false && $tag_timestamp > 0) {
				
				// use match timestamp
				//$tags = Tag::getMostRecentTagByTimestamp($match_station->id, $tag_timestamp);
				
				// use user timestamp
				$tags = Tag::getMostRecentTagByTimestamp($match_station->id, $tag_timestamp);
				
				$currentTag = $tags['current'];
				$prevTag = $tags['prev'];
				
				if ($currentTag && $currentTag->content_type_id == ContentType::GetSweeperContentTypeID()) {  // if sweeper tag, move to previous tag
					$currentTag = $prevTag;
				}
				
				if ($currentTag) {
					
					$currentTag->createHashForTag(); // create hash for tag linked with event
					
					$event->content_type_id = $currentTag->content_type_id;
					$event->tag_id = $currentTag->id;
					
					$currentTag->increaseEventCount();
				}
				
			}
			
		} else {
			$event->event_data_status = -1;
			$event->event_data_status_updateon = time();
			
			if (isset($listenerServiceResult['match_percent'])) {
				$event->match_percent = $listenerServiceResult['match_percent'];
			}
		} */
		
		try {
			$event->save();
		} catch (\Exception $ex) {
			\Log::error($ex);
			return;
		}
		
		try {
			//\Artisan::queue('airshr:sendeventupdatenotify', ['eventid' => $event_id]);
			AirShrArtisanQueue::QueueArtisanCommandToAPIQueue('airshr:sendeventupdatenotify', ['eventid' => $event_id]);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		if (env("APP_DEBUG")) {
			\Log::info("Update Event With Matcher Request Finished.");
		}
	}
	
	/*
	 *  Call listener service
	 */
	private function callListenerService($eventID, $file, $timestamp) {
		
		$result = array();
		
		$result['success'] = false;
		$result['msg'] = "";
				
		if (env("APP_DEBUG")) {
			\Log::info("Calling listener service - EventID: {$eventID}, File: {$file}, Timestamp: {$timestamp}");
		}
		
		$listenerServiceEndpoint = \Config::get("app.ListenerServiceEndpoint") . "?file=" . urlencode($file) . "&timeStamp=" . urlencode($timestamp) . '&eventID=' . urlencode($eventID);
		
		try {
			
			$response = \Httpful\Request::get($listenerServiceEndpoint)->timeout(\Config::get("app.ListenerServiceTimeout"))->send();
			
			if ($response->code == 200) {
				
				$result_json = $response->body;
				
				if ($result_json == null){ 
					$result['msg'] = "Response json is empty.";
				} else {
					
					if (env("APP_DEBUG")) {
						\Log::info("Listener Service response for EventID : {$eventID} - " . json_encode($result_json));
					}
					
					if ($result_json->status == 'success'){
						
						if ($result_json->data->foundMatch){
							$result['success'] = true;
							$result['station_name'] = $result_json->data->bestMatch->station;
							$result['timestamp'] = $result_json->data->bestMatch->matchTime;
							$result['match_percent'] = $result_json->data->bestMatch->matchPercentage;
							$result['match_time'] = $result_json->data->bestMatch->matchTime;
						} else {
							$result['msg'] = 'Not found match';
							if (isset($result_json->data->bestMatch)) {
								if (isset($result_json->data->bestMatch->station)) $result['station_name'] = $result_json->data->bestMatch->station;
								if (isset($result_json->data->bestMatch->matchTime)) $result['timestamp'] = $result_json->data->bestMatch->matchTime;
								if (isset($result_json->data->bestMatch->matchPercentage)) $result['match_percent'] = $result_json->data->bestMatch->matchPercentage;
								if (isset($result_json->data->bestMatch->matchTime)) $result['match_time'] = $result_json->data->bestMatch->matchTime;
							}
						}
						
					} else {
						$result['msg'] = 'Status response is not success';
					}
				}
				
			} else {
				$result['msg'] = "Response code from listener service : " . $response->code;
			}
			
		} catch (\Exception $ex) {
			\Log::error("Listener service response error for Event: " . $eventID);
			\Log::error($ex);
			$result['msg'] = "Connection error to listener service.";
		}
		
		
		return $result;
	}
	
	
	public function test() {
		
	}
	
	
	
	/**
	 * Call Url Asynchronously
	 * 
	 * @param unknown $url
	 * @return mixed
	 */
	protected function urlCallAsync($url){
	
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($c, CURLOPT_HEADER, false);         // Don't retrieve headers
		curl_setopt($c, CURLOPT_NOBODY, true);          // Don't retrieve the body
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);  // Return from curl_exec rather than echoing
		curl_setopt($c, CURLOPT_FRESH_CONNECT, true);   // Always ensure the connection is fresh
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		
		// Timeout super fast once connected, so it goes into async.
		curl_setopt( $c, CURLOPT_TIMEOUT, 1 );
	
		$result =  curl_exec( $c );
		
		return $result;
	}
}
