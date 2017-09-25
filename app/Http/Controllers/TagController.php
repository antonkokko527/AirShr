<?php namespace App\Http\Controllers;

use Request;
use App\Tag;
use App\Station;
use App\ContentType;
use App\ConnectContent;
use App\WebSocketPub;
use App\CoverArt;
use App\PreviewTag;
use Cache;
use App\MetaParsers\NovaParser;
use App\AirShrArtisanQueue;


define('DEFAULT_TAG_LIST_COUNT_PER_PAGE', 5);

class TagController extends JSONController {

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
	 *  Register new tag
	 *  
	 */
	public function store()
	{
		if (env("APP_DEBUG")) {
			\Log::info("New Meta Tag Request : " . json_encode(Request::all()));
		}
		
		$station = Request::input('station');
		$meta = Request::input('meta');
		$type = Request::input('type');
		
		
		if (empty($station)) {
			$this->setErrorCode("STATION_NAME_INVALID");
			return $this->sendJSONOutput();
		}
		
		if (empty($meta)) {
			$this->setErrorCode("TAG_META_BODY_EMPTY");
			return $this->sendJSONOutput();
		}
		
		if (empty($type)) {
			$this->setErrorCode("TAG_METADATA_TYPE_INVALID");
			return $this->sendJSONOutput();
		}
		
		if ($type == 'talk') {
			return $this->metaTalkSignalFromStation($station, $meta);
		} else if ($type == 'meta') {
			return $this->metaTagFromStation($station, $meta);
		} else {
			$this->setErrorCode("TAG_METADATA_TYPE_INVALID");
			return $this->sendJSONOutput();
		}
	}
	
	
	/**
	 * Meta talk signal from any radio station
	 */
	protected function metaTalkSignalFromStation($station, $meta) {
		
		// get station name
		// $station = Request::input('station');
		
		if ($station == 'nova-1069-brisbane') {
			return $this->metaTalkSignalFromNova($station, $meta);
		} else {
			$this->setErrorCode("STATION_NAME_INVALID");
			return $this->sendJSONOutput();
		}
	}
	
	/**
	 * Meta talk signal from nova station
	 */
	protected function metaTalkSignalFromNova($stationName, $talkSignal) {
		
		/*if (env("APP_DEBUG")) {
			\Log::info("Meta talk signal request from {$stationName} : " . json_encode(Request::all()));
		}*/
		
		//$talkSignal = Request::input('signal');
		
		$talkSignal = trim($talkSignal);
		$talkSignal = NovaParser::filterTalkSignal($talkSignal, $stationName);

		if (empty($talkSignal)) {			// if it is duplicate one, ignore it.
			$this->setErrorCode("TALK_SIGNAL_DUPLICATE");
			return $this->sendJSONOutput();
		}
			
		$novaStation = Station::getStationObjectByName($stationName);
		
		$prevTag = NovaParser::getPrevTag($stationName);
		
		if (empty($prevTag)) {
			$this->setErrorCode("PREV_TAG_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		$prevTagContentType = $prevTag->content_type_id;
		$prevTagStartTimestamp = $prevTag->tag_timestamp;
		$prevTagDuration = $prevTag->tag_duration;
		$prevTagEndTimestamp = $prevTagStartTimestamp + $prevTagDuration * 1000;
		$currentTagTimestamp = getCurrentMilisecondsTimestamp();
		
		
		if ($prevTagContentType == ContentType::findContentTypeIDByName('Music')) {  // In the middle of Song?
			
			if (NovaParser::isTalkOnSignal($talkSignal)) {		// mic on signal
				
				$untilLast20SecondsOfSong = floor(($prevTagEndTimestamp - NovaParser::$MUSIC_FADE_MILISECONDS_FOR_TALK - $currentTagTimestamp) / 1000);
				
				if ($untilLast20SecondsOfSong <= 0) { // if it is within the last 20 seconds of music, then start the talk
					
					Tag::CreateManualTag($novaStation->id, 0, "", "", "", "", "", "Talk", $stationName, 0, 0);
					
					$this->setErrorCode("SUCCESS");
					return $this->sendJSONOutput();
					
				} else {		// delay creation of talk segment
					
					AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:createtagwithdelay', ['station' => $stationName, 'who' => '', 'what' => '', 'original_who' => '', 'original_what' => '', 'adkey' => '', 'content_type' => 'Talk', 'tag_duration' => 0, 'prevtag_id' => $prevTag->id], \Config::get('app.QueueForTagInsert'), $untilLast20SecondsOfSong);
					
					$this->setErrorCode("SUCCESS");
					return $this->sendJSONOutput();
					
				}
			}
		}  
		
		// if off signal is received, then create tag from reserved
		if (NovaParser::isTalkOffSignal($talkSignal)) {

			$reserved = NovaParser::getReservedTag($stationName);
			
			if (!empty($reserved)) {
				
				$realStation = Station::getStationObjectByName($reserved['station']);
				
				Tag::CreateManualTag($realStation->id, 0, $reserved['who'], $reserved['what'], $reserved['original_who'], $reserved['original_what'], $reserved['adkey'], $reserved['content_type'], $reserved['station'], 0, 0);
				
				NovaParser::removeReservedTag($stationName);
				
				$this->setErrorCode("SUCCESS");
				return $this->sendJSONOutput();
				
			}
		}
		
		
		// display rx signals on the on air
		if (NovaParser::isNetworkSwitchOnSingal($talkSignal) || NovaParser::isNetworkSwitchOffSingal($talkSignal)) {
			$newTag = Tag::CreateManualTag($novaStation->id, 0, $talkSignal, "", $talkSignal, "", "", "Sweeper", $stationName, 0, 0);
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
		}
		
		
		$this->setErrorCode("TALK_SIGNAL_NO_ACTION");
		return $this->sendJSONOutput();
		
			
		/*$newTag = Tag::CreateManualTag($novaStation->id, 0, $talkSignal, "", $talkSignal, "", "", "Sweeper");
			
		if ($newTag) {
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
		} else {
			$this->setErrorCode("TAG_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}*/
	}
	
	/*
	 * Meta data post from any radio station
	 */
	protected function metaTagFromStation($station, $meta){
		
		// get station name
		// $station = Request::input('station');
		
		/*if ($station == 'wollongongwave') {
			return $this->metaTagFromWollongong();	
		} else*/ if ($station == 'nova-1069-brisbane' || $station == 'nova-969-sydney') {
			return $this->metaTagFromNova($station, $meta);
		} else {
			$this->setErrorCode("STATION_NAME_INVALID");
			return $this->sendJSONOutput();
		}
	}
	
	
	/*
	 * Meta data post from Nova
	 */
	protected function metaTagFromNova($stationName, $metaString) {
		
		/*if (env("APP_DEBUG")) {
			\Log::info("Meta tag request from {$stationName} : " . json_encode(Request::all()));
		}*/
		
		$novaStation = Station::getStationObjectByName($stationName);
				
		if ($novaStation === false) {
			$this->setErrorCode("STATION_NAME_INVALID");
			return $this->sendJSONOutput();
		}
		
		$currentHourInBrisbaneTimezone = getCurrentTimeInTimezone('H', 'Australia/Brisbane');
		$currentDayOfWeekInBrisbane = getCurrentTimeInTimezone('N', 'Australia/Brisbane');
		
		$currentMonthInBrisbane = getCurrentTimeInTimezone('m', 'Australia/Brisbane');
		$currentDateInBrisbane = getCurrentTimeInTimezone('d', 'Australia/Brisbane');
		//$switchingPeriod = ((($currentHourInBrisbaneTimezone >= 16 || $currentHourInBrisbaneTimezone <= 4) && ($currentDayOfWeekInBrisbane >= 1 && $currentDayOfWeekInBrisbane <= 5)) || ($currentDayOfWeekInBrisbane == 6 && $currentHourInBrisbaneTimezone <= 5) || ($currentDayOfWeekInBrisbane == 7 && $currentHourInBrisbaneTimezone >= 13)) ? true : false;
		
		$switchingPeriod = ((($currentHourInBrisbaneTimezone >= 16 || $currentHourInBrisbaneTimezone <= 4) && ($currentDayOfWeekInBrisbane >= 1 && $currentDayOfWeekInBrisbane <= 5)) || ($currentDayOfWeekInBrisbane == 6 && ($currentHourInBrisbaneTimezone <= 5 || $currentHourInBrisbaneTimezone >= 15)) || ($currentDayOfWeekInBrisbane == 7 && ( $currentHourInBrisbaneTimezone <= 5 || ($currentHourInBrisbaneTimezone >= 13 && $currentHourInBrisbaneTimezone <= 17  )) )) ? true : false;
		
		
		// specfic rule for April 22 and April 25
		if ($currentMonthInBrisbane == 4 && $currentDateInBrisbane == 22) {
			if ($currentHourInBrisbaneTimezone == 18) {
			 	$switchingPeriod = false;
			}
		}
		if ($currentMonthInBrisbane == 4 && $currentDateInBrisbane == 25) {
			if ($currentHourInBrisbaneTimezone <= 17) {
				$switchingPeriod = true;
			}
		}	

		// for only April 7 - Important: please remove later
		/*if ($currentHourInBrisbaneTimezone == 18) {
			$switchingPeriod = false;
		}*/

		$timeZoneDifference = getOffsetBetweenTimezones('Australia/Brisbane', 'Australia/Sydney');
		$daylightSaving = $timeZoneDifference < 0 ? true : false;
		
		$currentHourInSydneyTimezone = getCurrentTimeInTimezone('H', 'Australia/Sydney');
		$currentDayOfWeekInSydney = getCurrentTimeInTimezone('N', 'Australia/Sydney');
		
		$cacheTags = ($daylightSaving && ((($currentHourInSydneyTimezone >= 16 || $currentHourInSydneyTimezone <= 4) && ($currentDayOfWeekInSydney >= 1 && $currentDayOfWeekInSydney <= 5)) || ($currentDayOfWeekInSydney == 6 && $currentHourInSydneyTimezone <= 5) || ($currentDayOfWeekInSydney == 7 && $currentHourInSydneyTimezone >= 13) )) ? true : false;
		
		// Saturday afternoon - Sunday morning
		//$liveNetworking = (($currentDayOfWeekInBrisbane == 6 && $currentHourInBrisbaneTimezone >= 14) || ($currentDayOfWeekInBrisbane == 7 && $currentDayOfWeekInBrisbane <= 4)) ? true : false;
		$liveNetworking = false;   // daylight savings off
		
		// get parameters
		// $metaString = Request::input('meta');
		
		// log meta data extractor values
		LogInfoToFile("MetaDataExtractor", storage_path('logs/meta/' . $stationName), 'meta-' . date("Y-m-d") . '.log', $metaString);
		 		
		// validation
		if (empty($metaString)) {
			$this->setErrorCode("TAG_META_BODY_EMPTY");
			return $this->sendJSONOutput();
		}
		
		$xmlElements = array();
		
		try {
			
			$xmlElements = NovaParser::parseRealTimeMetaData($metaString, $stationName);
			
			if (!$xmlElements) {
				$this->setErrorCode("TAG_META_BODY_EMPTY");
				return $this->sendJSONOutput();
			}
			
			/*$xml = \XmlParser::extract($metaString);
			$xmlElements = $xml->parse([
				'AssetTypeID'	=> ['uses' => 'LogEvents.LogEvent.AssetEvent.Asset::AssetTypeID'],
				'AssetTypeName'	=> ['uses' => 'LogEvents.LogEvent.AssetEvent.Asset::AssetTypeName'],
				'Title'			=> ['uses' => 'LogEvents.LogEvent.AssetEvent.Asset::Title'],
				'AirStarttime'	=> ['uses' => 'LogEvents.LogEvent::AirStarttime'],
				'AirStoptime'	=> ['uses' => 'LogEvents.LogEvent::AirStoptime'],
				'SponsorName'	=> ['uses' => 'LogEvents.LogEvent.AssetEvent.Asset.Sponsor::Name'],
				'Artist'		=> ['uses' => 'LogEvents.LogEvent.AssetEvent.Asset.Artist::Name'],
				'StatusCode'	=> ['uses' => 'LogEvents.LogEvent::StatusCode'],
				'Description'	=> ['uses' => 'LogEvents.LogEvent::Description']
			]);*/
						
		} catch (\Exception $ex) {
			$this->setErrorCode("NOVA_METADATA_INVALIDFORMAT");
			return $this->sendJSONOutput();
		}
		
		
		$tagger = 0;
		$station_id = $novaStation->id;
		
		$contentType = '';
		$who = '';
		$what = '';
		$original_who = '';
		$original_what = '';
		
		$adkey = '';
		$connectContentId = 0;
		$coverartId = 0;
		$tagDuration = 0;
		
		// use arrived miliseconds timestamp for tag
		$tagTimestamp_ms = getCurrentMilisecondsTimestamp();
		$tagTimestamp = getSecondsFromMili($tagTimestamp_ms);
		
		/*if ($xmlElements['StatusCode'] == '1' && empty($xmlElements['AirStarttime'])) {    // Paused and empty start time? then live talk
			$contentType = 'Talk';
			$what = $xmlElements['Title'];
			$original_what = $what;
		} else*/ if (/*$xmlElements['StatusCode'] == '2' && */ !empty($xmlElements['AirStarttime'])) {
			$startTime = strtotime($xmlElements['AirStarttime']);
			$endTime = strtotime($xmlElements['AirStoptime']);
			
			if ($startTime !== FALSE && $endTime !== FALSE) {
				$tagDuration = $endTime - $startTime;
			} else {
				$tagDuration = 0;
			}
		
			if ($tagDuration < 0) $tagDuration = 0;
			
			$assetTypeID = $xmlElements['AssetTypeID'];
			$assetTypeName = strtoupper(trim($xmlElements['AssetTypeName']));
			
			if ($assetTypeID == '1' || $assetTypeName == 'SONG')  {  // Song
				
				$contentType = 'Music';
				
				$who = $xmlElements['Artist'];
				$what = $xmlElements['Title'];
				
				$original_what = $what;
				$original_who = $who;
				
				// for music, call cover art web service
				/*$coverartInfo = CoverArt::getCoverArtInfo($who, $what);
				
				if ($coverartInfo) {
						
					if (!empty($coverartInfo['artist']))
						$who = $coverartInfo['artist'];
						
					if (!empty($coverartInfo['track']))
						$what = $coverartInfo['track'];
						
					$coverartId = $coverartInfo['id'];
				}*/
				
			} else if ($assetTypeID == '2' || $assetTypeName == 'SPOT') {   // Spot - Ad
				
				$contentType = 'Ad';
				
				$adkey = cleanupAdKey($xmlElements['Title']);
				$who = $xmlElements['SponsorName'];
				$original_who = $who;
				
				$what = $xmlElements['Description'];
				$original_what = $what;
				
				
				
			} else if ($assetTypeID == '3' || $assetTypeName == 'LINK') {		// Link - News, Promotion, Sweeper
				
				$descriptionUpperCase = strtoupper($xmlElements['Description']);
				
				if (strpos($descriptionUpperCase, "PROMO") === 0 || strpos($descriptionUpperCase, "PRM") === 0) {  // promo starts with PROMO or PRM
					
					$contentType = "Promotion";
					$adkey = cleanupAdKey($xmlElements['Title']);
					$what = $xmlElements['Description'];
					$original_what = $what;
				} else if (/*strpos($descriptionUpperCase, "CRE") === 0 || strpos($descriptionUpperCase, "CREDIT") === 0 || */strpos($descriptionUpperCase, "SF-COLOUR") === 0 || strpos($descriptionUpperCase, "VH-COLOUR") === 0) {  // Starts with cre or credit - Ad
					
					$contentType = "Ad";
					
					$adkey = cleanupAdKey($xmlElements['Title']);
					$what = $xmlElements['Description'];
					$original_what = $what;
					
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
				} else if (strpos($descriptionUpperCase, "BED") === 0 || strpos($descriptionUpperCase, "SEG") === 0 || strpos($descriptionUpperCase, "INT") === 0 || strpos($descriptionUpperCase, "VT") === 0 || strpos($descriptionUpperCase, "OOB") === 0 || strpos($descriptionUpperCase, "TOH") === 0 || strpos($descriptionUpperCase, "ELM") === 0 || strpos($descriptionUpperCase, "KTM") === 0 || strpos($descriptionUpperCase, "AKL") === 0 || strpos($descriptionUpperCase, "TWS") === 0 || strpos($descriptionUpperCase, "SF-") === 0 || strpos($descriptionUpperCase, "OPENER") === 0) {  // Starts with BED - talk
					
					$contentType = 'Talk';
					
					$what = $xmlElements['Title'];
					$original_what = $what;
					
				} else if (strpos($descriptionUpperCase, "NEWS") === 0 ||  strpos($descriptionUpperCase, "BRIS NOVA NEWS") === 0) {  // Starts with news, traffic, sports - news
					
					$contentType = 'News';
					
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
					
					$what = $xmlElements['Description'];
					$original_what = $what;
					
				} else if (strpos($descriptionUpperCase, "TRAF") === 0 || strpos($descriptionUpperCase, "TRAFFIC") === 0) {
					
					$contentType = 'Traffic';
						
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
						
					$what = $xmlElements['Description'];
					$original_what = $what;
					
				} else if (strpos($descriptionUpperCase, "SPORTS") === 0) {
					
					$contentType = 'Sport';
					
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
					
					$what = $xmlElements['Description'];
					$original_what = $what;
					
				} else {
				
					$contentType = 'Sweeper';
				
					$what = $xmlElements['Title'];
					$original_what = $what;
				}
				
				if (strpos($descriptionUpperCase, "TRAF") !== FALSE ) {
					$contentType = 'Traffic';
						
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
						
					$what = $xmlElements['Description'];
					$original_what = $what;
				} else if (strpos($descriptionUpperCase, "NEWS") !== FALSE) {
					$contentType = 'News';
						
					$who = $xmlElements['SponsorName'];
					$original_who = $who;
						
					$what = $xmlElements['Description'];
					$original_what = $what;
				}
				
			} else if ($assetTypeID == '4' || $assetTypeName == 'VOICE TRACK') {			// Recorded Talk
				
				$contentType = 'Talk';
				
				$what = $xmlElements['Title'];
				$original_what = $what;
				
			} else {
				$this->setErrorCode("TAG_CONTENTTYPE_INVALID");
				return $this->sendJSONOutput();
			}
			
			// search for connect content
			/*if (($contentType == 'Ad' || $contentType == 'Promotion') && !empty($adkey)) {
				// look for airshr connect content
				$connectContentObj = ConnectContent::getConnectContentForTag($station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $adkey);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$what = $connectContentObj->what;
					}
			
					$connectContentId = $connectContentObj->id;
				}
			}
			
			if ($contentType == 'Talk') {
				// look for airshr connect content - talk
				$connectContentObj = ConnectContent::getConnectContentForTalkTag($station_id, $tagTimestamp);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$what = $connectContentObj->what;
					}
						
					$connectContentId = $connectContentObj->id;
				}
					
				$connectContentObj = ConnectContent::getConnectContentForIndividualTalk($station_id, $tagTimestamp_ms);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$what = $connectContentObj->what;
					}
						
					$connectContentId = $connectContentObj->id;
				}
					
			}
			
			if ($contentType == 'News') {
				
				$connectContentObj = ConnectContent::getConnectContentForNewsTag($station_id, $tagTimestamp);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$what = $connectContentObj->what;
					}
				
					$connectContentId = $connectContentObj->id;
				}
				
			}*/
			
			
		} else {
			$this->setErrorCode("TAG_CONTENTTYPE_INVALID");
			return $this->sendJSONOutput();
		} 
		
		$insert_timestamp = getCurrentMilisecondsTimestamp();
		$insert_lag = $insert_timestamp - $tagTimestamp_ms;
		
		$metaTagTimestamp = $startTime * 1000;
		$metaTagTimestampDiff = $tagTimestamp_ms - $metaTagTimestamp;
		
		$prevTag = NovaParser::getPrevTag($stationName);
		
		// insert new tag
		try {
			
			$newlyAddedTagId = 0;
			
			if ($novaStation->station_name == 'nova-1069-brisbane' && ($switchingPeriod || $liveNetworking) && $contentType != 'Ad' && $contentType != 'Promotion') {
				// skip - do not add
			} else {
				
				if ($contentType == 'Music' && NovaParser::isTalkOnSignal(NovaParser::getPrevTalkSignal($stationName))) { //Music tag when mic is still on, create music after 20 seconds
					
					// create talk segment if previous tag is not talk
					if ($prevTag && $prevTag->content_type_id != ContentType::findContentTypeIDByName('Talk')) {
						Tag::CreateManualTag($station_id, 0, "", "", "", "", "", "Talk", $stationName, 0, 0);
					}
					
					NovaParser::setReservedTag(['station' => $stationName, 'who' => $who, 'what' => $what, 'original_who' => $original_who, 'original_what' => $original_what, 'adkey' => $adkey, 'content_type' => $contentType, 'tag_duration' => $tagDuration], $stationName);					
					// after 20 seconds, add music tag
					AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:createtagwithdelay', ['station' => $stationName, 'who' => $who, 'what' => $what, 'original_who' => $original_who, 'original_what' => $original_what, 'adkey' => $adkey, 'content_type' => $contentType, 'tag_duration' => $tagDuration, 'prevtag_id' => 0, 'prevtalk_signal' => NovaParser::getPrevTalkSignal($stationName), 'check_reserve' => $stationName], \Config::get('app.QueueForTagInsert'), NovaParser::$MUSIC_FADE_MILISECONDS_FOR_TALK / 1000);
					
				} else {
				
					$merge = false;
					
					$descriptionUpperCase = strtoupper($xmlElements['Description']);
					
					// we should merge?
					if ($prevTag){ 
						if ($prevTag->content_type_id == ContentType::findContentTypeIDByName('News') && ($contentType == 'News' || $contentType == 'Talk')) {
							$merge = true;
						}
						if ($prevTag->content_type_id == ContentType::findContentTypeIDByName('Talk') && $contentType == 'Talk') {
							$merge = true;
						}
						if ($prevTag->content_type_id == ContentType::GetTrafficContentTypeID() && ($contentType == 'Traffic' || $contentType == 'Talk')) {
							$merge = true;
						}
						if (($prevTag->content_type_id == ContentType::GetTalkContentTypeID() || $prevTag->content_type_id == ContentType::GetTrafficContentTypeID() || $prevTag->content_type_id == ContentType::GetNewsContentTypeID()) && strpos($descriptionUpperCase, "CREDIT") === 0) {
							$merge = true;
						}  
					}
					
					
					if ($merge) {
						
						Tag::MergeTagWithPrev($prevTag->id, $tagTimestamp_ms, $tagDuration);
						
					} else {
				
						$newTag = Tag::create([
								'tagger_id' 			=> $tagger,
								'station_id'			=> $station_id,
								'content_type_id'		=> ContentType::findContentTypeIDByName($contentType),
								'tag_timestamp'			=> $tagTimestamp_ms,
								'who'					=> $who,
								'what'					=> $what,
								'adkey'					=> $adkey,
								'is_valid'				=> 1,
								'insert_timestamp'		=> $insert_timestamp,
								'insert_lag'			=> $insert_lag,
								'connect_content_id'	=> $connectContentId,
								'coverart_id'			=> $coverartId,
								'tag_duration'			=> $tagDuration,
								'cart'					=> '',
								'original_who'			=> $original_who,
								'original_what'			=> $original_what,
								'meta_tag_timestamp'	=> $metaTagTimestamp,
								'meta_tag_timestamp_diff'	=> $metaTagTimestampDiff
								]);
		
						$newlyAddedTagId = $newTag->id;
						
						$newTag->findConnectContentForTag();
						
						NovaParser::storePrevTag($newTag, $stationName);
						
						/*WebSocketPub::publishPushMessage(array(
							'event' 	=> 'NEWTAG',
							'tag'		=> $newTag->getArrayDataForOnAir()
						));*/
		
						WebSocketPub::publishPushMessageOnQueue(array(
							'event' 	=> 'NEWTAG',
							'tag'		=> $newTag->getArrayDataForOnAir()
						), \Config::get('app.QueueForNewTag'));
						
						// create content if not found
						if (($contentType == 'Ad' || $contentType == 'Promotion') && empty($connectContentId) && !empty($adkey)) {
							$newTag->createAdContentForTag();
						}
						
						$newTag->applyForPreviousTagCompetitionGeneration();
					
						$newTag->generateTrimmedAudioForPreviousTag();
						
						$newTag->storeVoteRelatedTags();
					}
				
				}
			}
			
			
			if ($novaStation->station_name == 'nova-969-sydney' && ((!$daylightSaving && $switchingPeriod) || $liveNetworking) && $contentType != 'Ad' && $contentType != 'Promotion') {
				
				// copy sydney meta to brisbane
				
				$novaBrisbaneStationName = 'nova-1069-brisbane';
				$novaBrisbaneStation = Station::getStationObjectByName($novaBrisbaneStationName);
				
				if ($contentType == 'Music' && NovaParser::isTalkOnSignal(NovaParser::getPrevTalkSignal($stationName))) { //Music tag when mic is still on, create music after 20 seconds
						
					// create talk segment if previous tag is not talk
					$prevTag = NovaParser::getPrevTag($stationName);
					
					if ($prevTag && $prevTag->content_type_id != ContentType::findContentTypeIDByName('Talk')) {
						Tag::CreateManualTag($novaBrisbaneStation->id, 0, "", "", "", "", "", "Talk", $novaBrisbaneStationName, 0, 0);
					}

					NovaParser::setReservedTag(['station' => $novaBrisbaneStationName, 'who' => $who, 'what' => $what, 'original_who' => $original_who, 'original_what' => $original_what, 'adkey' => $adkey, 'content_type' => $contentType, 'tag_duration' => $tagDuration], $stationName);
					// after 20 seconds, add music tag
					AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:createtagwithdelay', ['station' => $novaBrisbaneStationName, 'who' => $who, 'what' => $what, 'original_who' => $original_who, 'original_what' => $original_what, 'adkey' => $adkey, 'content_type' => $contentType, 'tag_duration' => $tagDuration, 'prevtag_id' => 0, 'prevtalk_signal' => NovaParser::getPrevTalkSignal($novaBrisbaneStationName), 'check_reserve' => $stationName], \Config::get('app.QueueForTagInsert'), NovaParser::$MUSIC_FADE_MILISECONDS_FOR_TALK / 1000);
					
				} else {
					
					$prevTag = NovaParser::getPrevTag($novaBrisbaneStationName);
					
					$descriptionUpperCase = strtoupper($xmlElements['Description']);
					
					$merge = false;
						
					// we should merge?
					if ($prevTag){
						if ($prevTag->content_type_id == ContentType::findContentTypeIDByName('News') && ($contentType == 'News' || $contentType == 'Talk')) {
							$merge = true;
						}
						if ($prevTag->content_type_id == ContentType::findContentTypeIDByName('Talk') && $contentType == 'Talk') {
							$merge = true;
						}
						if ($prevTag->content_type_id == ContentType::GetTrafficContentTypeID() && ($contentType == 'Traffic' || $contentType == 'Talk')) {
							$merge = true;
						}
						if (($prevTag->content_type_id == ContentType::GetTalkContentTypeID() || $prevTag->content_type_id == ContentType::GetTrafficContentTypeID() || $prevTag->content_type_id == ContentType::GetNewsContentTypeID()) && strpos($descriptionUpperCase, "CREDIT") === 0) {
							$merge = true;
						}
					}
				
					if ($merge) {
						
						Tag::MergeTagWithPrev($prevTag->id, $tagTimestamp_ms, $tagDuration);
						
					} else {
					
						$newTag = Tag::create([
								'tagger_id' 			=> $tagger,
								'station_id'			=> $novaBrisbaneStation->id,
								'content_type_id'		=> ContentType::findContentTypeIDByName($contentType),
								'tag_timestamp'			=> $tagTimestamp_ms,
								'who'					=> $who,
								'what'					=> $what,
								'adkey'					=> $adkey,
								'is_valid'				=> 1,
								'insert_timestamp'		=> $insert_timestamp,
								'insert_lag'			=> $insert_lag,
								'connect_content_id'	=> $connectContentId,
								'coverart_id'			=> $coverartId,
								'tag_duration'			=> $tagDuration,
								'cart'					=> '',
								'original_who'			=> $original_who,
								'original_what'			=> $original_what,
								'meta_tag_timestamp'	=> $metaTagTimestamp,
								'meta_tag_timestamp_diff'	=> $metaTagTimestampDiff
								]);
							
						$newTag->findConnectContentForTag();
						
						NovaParser::storePrevTag($newTag, $stationName);
						
						/*WebSocketPub::publishPushMessage(array(
							'event' 	=> 'NEWTAG',
							'tag'		=> $newTag->getArrayDataForOnAir()
						));*/
						
						WebSocketPub::publishPushMessageOnQueue(array(
							'event' 	=> 'NEWTAG',
							'tag'		=> $newTag->getArrayDataForOnAir()
						), \Config::get('app.QueueForNewTag'));
						
						// create content if not found
						if (($contentType == 'Ad' || $contentType == 'Promotion') && empty($connectContentId) && !empty($adkey)) {
							$newTag->createAdContentForTag();
						}
						
						$newTag->applyForPreviousTagCompetitionGeneration();
						
						$newTag->generateTrimmedAudioForPreviousTag();
						
						$newTag->storeVoteRelatedTags();
					}
				}
			}
			
			
			// cache tags for nova merge
			if ($cacheTags && $novaStation->station_name == 'nova-969-sydney' && $contentType != 'Ad' && $contentType != 'Promotion') {
				
				\DB::table('airshr_cached_tags')->insert([
					'station_id' => $novaStation->id,
					'tag_id' => $newlyAddedTagId,
					'tag_timestamp'	=> 	$tagTimestamp_ms,
					'created_at'	=> date("Y-m-d H:i:s"),
					'updated_at'	=> date("Y-m-d H:i:s"),
				]);
				
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("TAG_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
		
	}
	
	/*
	 * Meta data post from Wollongong
	 */
	
	/*public function metaTagFromWollongong()
	{
		if (env("APP_DEBUG")) {
			\Log::info("Meta tag request from WollongGong : " . json_encode(Request::all()));
		}
		
		$wavefmStation = Station::getStationObjectByName('wollongongwave');
		
		if ($wavefmStation === false) {
			$this->setErrorCode("WAVEFM_STATION_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		// get parameters
		$metaString = Request::input('meta');
		
		// log meta data extractor values
		LogInfoToFile("MetaDataExtractor", storage_path('logs/meta/wollongong'), 'meta-' . date("Y-m-d") . '.log', $metaString);
				
		// validation
		if (empty($metaString)) {
			$this->setErrorCode("TAG_META_BODY_EMPTY");
			return $this->sendJSONOutput();
		}
		
		preg_match("/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\s+([\w\s\/'&\(\)\.\+\[\]:\d]+)\s-\s([\w\s\/'&\(\)\.\+\[\]-]*)\s-\s([\w\s\/'&\(\)\.\+\[\]-]*)/", $metaString, $match);
		if (!is_array($match) || count($match) < 6) {
			
			preg_match("/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\s+([\w\s\/'&\(\)\.\+\[\]:\d]+)-{0,1}([\w\s\/'&\(\)\.\+\[\]]*)-{0,1}([\w\s\/'&\(\)\.\+\[\]-]*)/", $metaString, $match);
			if (!is_array($match) || count($match) < 6) {	
				$this->setErrorCode("WAVEFM_METADATA_INVALIDFORMAT");
				return $this->sendJSONOutput();
			}
		}
		
		$dateVal = trim($match[1]);
		$timeVal = trim($match[2]);
		$firstPart = trim($match[3]);
		$secondPart = trim($match[4]);
		$thirdPart = trim($match[5]);
		
		$tagger = 0;
		$station_id = $wavefmStation->id;
		
		$contentType = 'Music';
		$who = '';
		$what = '';
		$original_who = '';
		$original_what = '';
		
		$adkey = '';
		$connectContentId = 0;
		$coverartId = 0;
		$tagDuration = 0;
		
		// check timestamp
		$tagTimestamp = strtotime($dateVal . ' ' . $timeVal);
		if ($tagTimestamp === false || $tagTimestamp === -1) {
			$this->setErrorCode("WAVEFM_DATETIME_INVALID");
			return $this->sendJSONOutput();
		}
		
		// use arrived miliseconds timestamp for tag
		$tagTimestamp_ms = getCurrentMilisecondsTimestamp();
		$tagTimestamp = getSecondsFromMili($tagTimestamp_ms);
		
		// load preview tag info
		$prevContentType = apc_fetch("STATION_{$station_id}_TAG_CONTENT_TYPE");
		$prevCartNo = apc_fetch("STATION_{$station_id}_TAG_CART_NUMBER");
		$prevTagId = apc_fetch("STATION_{$station_id}_TAG_ID");
		$prevTagTimestamp = apc_fetch("STATION_{$station_id}_TAG_TIMESTAMP");
		$prevTagFirstPart = apc_fetch("STATION_{$station_id}_TAG_FIRSTPART");
		
		//whether to merge or insert
		$mergeTagWithPrev = false;
		$skipInsertion = false;
		
		if (strtoupper($firstPart) == 'STOPPED' && $secondPart == '') { // Talk
			$contentType = 'Talk';
		} else {
			preg_match("/(MUS|COM|NWS|SEG|SWP|PRO|STP|IDC|VTK|PCN|TMP)\s+(\d{1,2}:\d{1,2}:\d{1,2})\s+(.*)/", $firstPart, $firstPartMatch);
			
			if (!is_array($firstPartMatch) || count($firstPartMatch) < 4) {
				
				preg_match("/(MUS|COM|NWS|SEG|SWP|PRO|STP|IDC|VTK|PCN|TMP)\s+(\d{1,2}:\d{1,2}:\d{1,2})(.*)/", $firstPart, $firstPartMatch);
				
				if (!is_array($firstPartMatch) || count($firstPartMatch) < 4) {
					$this->setErrorCode("WAVEFM_METADATA_INVALIDFORMAT");
					return $this->sendJSONOutput();
				}
			}
			
			$firstPartType = trim($firstPartMatch[1]);
			$firstPartDuration = trim($firstPartMatch[2]);
			$firstPartContent = trim($firstPartMatch[3]);
			
			$tagDuration = parseTagDurationString($firstPartDuration);
			
			if ($firstPartType == 'MUS') {  // Music
				$contentType = 'Music';
				$what = $firstPartContent;
				$who = $secondPart;
				$original_who = $who;
				$original_what = $what;
				// for music, call cover art web service
				$coverartInfo = CoverArt::getCoverArtInfo($who, $what);
				
				if ($coverartInfo) {
					
					if (!empty($coverartInfo['artist']))
						$who = $coverartInfo['artist'];
					
					if (!empty($coverartInfo['track']))
						$what = $coverartInfo['track'];
					
					$coverartId = $coverartInfo['id'];
				}
				
			} else if ($firstPartType == 'COM') { // COMMERCIAL - AD
				$contentType = 'Ad';
				$who = $firstPartContent;
				$original_who = $who;
				$adkey = cleanupAdKey($secondPart);
				
				if (!empty($adkey)) {
					// look for airshr connect content
					$connectContentObj = ConnectContent::getConnectContentForTag($station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $adkey);
					if ($connectContentObj) {
						// replace who and what with connect data
						if (!empty($connectContentObj->who)) {
							$who = $connectContentObj->who;
						}
						if (!empty($connectContentObj->what)) {
							$what = $connectContentObj->what;
						}
						
						$connectContentId = $connectContentObj->id;
					}
				}
				
			} else if ($firstPartType == 'NWS') { // News
				$contentType = 'News';
				$what = $firstPartContent;
				$original_what = $what;
				$adKey = "6";
				//$who = "TEST";
			} else if ($firstPartType == 'SEG') { // SEG
				$contentType = 'Talk';
				$what = $firstPartContent;
				$original_what = $what;
			} else if ($firstPartType == 'SWP') { // Sweeper
				$contentType = 'Sweeper';
				$what = $firstPartContent;
				$who = $secondPart;
				$original_what = $what;
				$original_who = $who;
			} else if ($firstPartType == 'PRO') { // Promotion
				
				if (strpos(strtoupper($thirdPart), 'INT-NEWS') === 0) {  // include next talk segment and regard it as news)
					$contentType = 'News';
					$what = $firstPartContent;
					$original_what = $what;
					
					$adkey = cleanupAdKey("6");
					$connectContentObj = ConnectContent::getConnectContentForNewsTag($station_id, $tagTimestamp);
					
					if ($connectContentObj) {
						$connectContentId = $connectContentObj->id;
					}

					$who = $connectContentObj->who;
					$what = $connectContentObj->what;
					//$original_who = "News";
				} else if (strpos(strtoupper($thirdPart), 'INT-TRAFFIC') === 0) {
					$contentType = 'News';
					$what = $firstPartContent;
					$original_what = $what;

				} else if (strpos(strtoupper($thirdPart), 'INT-WTHR') === 0) {
					$contentType = 'Talk';
					$what = $firstPartContent;
					$original_what = $what;
				} else {			// otherwise just promotion
				
					$contentType = 'Promotion';
					$what = $firstPartContent;
					$original_what = $what;
					$adkey = cleanupAdKey($secondPart);
					$cart = cleanupAdKey($thirdPart);
					
					if (!empty($cart)) {
						// look for airshr connect content
						$connectContentObj = ConnectContent::getConnectContentForTagWitCart($station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $cart);
						if ($connectContentObj) {
							// replace who and what with connect data
							if (!empty($connectContentObj->who)) {
								$who = $connectContentObj->who;
							}
							if (!empty($connectContentObj->what)) {
								$what = $connectContentObj->what;
							}
								
							$connectContentId = $connectContentObj->id;
						}
					}
				
				}
			} else if ($firstPartType == 'STP') {		// Talk
				$contentType = 'Talk';
			} else if ($firstPartType == 'IDC') {		// Sweeper
				$contentType = 'Sweeper';
				$what = $firstPartContent;
				$who = $secondPart;
				$original_what = $what;
				$original_who = $who;
			} else if ($firstPartType == 'VTK' || $firstPartType == 'PCN') {		// Recorded Talk
				$contentType = 'Talk';
				$what = $firstPartContent;
				$who = $secondPart;
				$original_what = $what;
				$original_who = $who;
			} else if ($firstPartType == 'TMP' && strpos(strtoupper($firstPartContent), 'LIVE READ') === 0) {
				$contentType = 'Talk';
				$who = "LIVE READ";
				$original_who = $who;
			} else {
				$this->setErrorCode("WAVEFM_DATETIME_INVALID");
				return $this->sendJSONOutput();
			}
		} 
		
		if ($contentType == 'Talk') {
			// look for airshr connect content - talk
			$connectContentObj = ConnectContent::getConnectContentForTalkTag($station_id, $tagTimestamp);
			if ($connectContentObj) {
				// replace who and what with connect data
				if (!empty($connectContentObj->who)) {
					$who = $connectContentObj->who;
				}
				if (!empty($connectContentObj->what)) {
					$what = $connectContentObj->what;
				}
			
				$connectContentId = $connectContentObj->id;
			}
			
			$connectContentObj = ConnectContent::getConnectContentForIndividualTalk($station_id, $tagTimestamp_ms);
			if ($connectContentObj) {
				// replace who and what with connect data
				if (!empty($connectContentObj->who)) {
					$who = $connectContentObj->who;
				}
				if (!empty($connectContentObj->what)) {
					$what = $connectContentObj->what;
				}
					
				$connectContentId = $connectContentObj->id;
			}
			
		}
		
		$prevTag = null;
		
		// check for previous tag to merge - check if the previous tag is news intro
		if ($prevContentType == 'News' && strpos(strtoupper($prevCartNo), 'INT-NEWS') === 0 && ($contentType == 'Talk' || $contentType == 'News')) {
			
			try {
				$prevTag = Tag::findOrFail($prevTagId);
			}catch(\Exception $ex) {}
			
			if ($prevTag) {
				$mergeTagWithPrev = true;
			}
		}
		
		if ($prevContentType == 'Talk' && $contentType == 'Talk' && strtoupper($prevTagFirstPart) == 'BREKKIE TOH') {
			try {
				$prevTag = Tag::findOrFail($prevTagId);
			}catch(\Exception $ex) {}
				
			if ($prevTag) {
				$mergeTagWithPrev = true;
			}
		}
		
		// check same time arriving tags
		if ($prevTagTimestamp && $prevTagTimestamp > 0 && $tagTimestamp - $prevTagTimestamp < 2) {
			if ($contentType == 'Talk' && $prevContentType == 'Music') {
				$skipInsertion = true;
			}
			if ($contentType == 'Music' && $prevContentType == 'Talk') {
				try {
					$prevTalkTag = Tag::findOrFail($prevTagId);
					$prevTalkTag->delete();
				}catch(\Exception $ex) {}
			}
		}
		
		$insert_timestamp = getCurrentMilisecondsTimestamp();
		$insert_lag = $insert_timestamp - $tagTimestamp_ms;
		
		// insert new tag
		try {
			
			$newTagID = 0;
			
			if ($mergeTagWithPrev) {
				
				// merging with prev tag
				if ($tagDuration > 0) {
					$prevTag->tag_duration += $tagDuration;
				} else {
					$prevTag->tag_duration = 0;
				}	
				$prevTag->save();
				
			} else {
				
				if (!$skipInsertion) {
					$newTag = Tag::create([
							'tagger_id' 			=> $tagger,
							'station_id'			=> $station_id,
							'content_type_id'		=> ContentType::findContentTypeIDByName($contentType),
							'tag_timestamp'			=> $tagTimestamp_ms,
							'who'					=> $who,
							'what'					=> $what,
							'adkey'					=> $adkey,
							'is_valid'				=> 1,
							'insert_timestamp'		=> $insert_timestamp,
							'insert_lag'			=> $insert_lag,
							'connect_content_id'	=> $connectContentId,
							'coverart_id'			=> $coverartId,
							'tag_duration'			=> $tagDuration,
							'cart'					=> $thirdPart,
							'original_who'			=> $original_who,
							'original_what'			=> $original_what
							]);
					
					//WebSocketPub::publishPushMessage(array(
					//'event' 	=> 'NEWTAG',
					//'tag'		=> $newTag->getArrayDataForOnAir()
					//));
					
					WebSocketPub::publishPushMessageOnQueue(array(
						'event' 	=> 'NEWTAG',
						'tag'		=> $newTag->getArrayDataForOnAir()
					), \Config::get('app.QueueForNewTag'));
					
					$newTagID = $newTag->id;
					
					// create content if not found
					if (($contentType == 'Ad' || $contentType == 'Promotion') && empty($connectContentId) && !empty($adkey)) {
						$newTag->createAdContentForTag();
					} 
					
					$newTag->applyForPreviousTagCompetitionGeneration();
				}
			}
			
			// update preview tag info
			apc_store("STATION_{$station_id}_TAG_CONTENT_TYPE", $contentType);
			apc_store("STATION_{$station_id}_TAG_CART_NUMBER", $thirdPart);
			apc_store("STATION_{$station_id}_TAG_ID", $newTagID);
			apc_store("STATION_{$station_id}_TAG_TIMESTAMP", $tagTimestamp);
			apc_store("STATION_{$station_id}_TAG_FIRSTPART", $firstPartContent);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("TAG_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	} */
	
	
	/*
	 *  Returns meta data info list
	 */
	public function index() {
	
		if (env("APP_DEBUG")) {
			\Log::info("Tag List Request : " . json_encode(Request::all()));
		}
	
		// get parameters
		$station_id = Request::input('station_id');
		$timestamp = Request::input('timestamp');
		$pageNum = Request::input('page');
		
		if (empty($timestamp)) $timestamp = 0;
		if (empty($pageNum)) $pageNum = 0;
		if (empty($station_id)) $station_id = 0;
		
		$offset = DEFAULT_TAG_LIST_COUNT_PER_PAGE * $pageNum;
	
		$limitTimestamp = $timestamp - 20 * 60;
		
		$tags = Tag::where('tag_timestamp', '<', $timestamp * 1000)
					->where('tag_timestamp', '>=', $limitTimestamp * 1000)
					->where('station_id', '=', $station_id)
					->where('content_type_id', '<>', ContentType::GetSweeperContentTypeID())
					->orderBy('tag_timestamp', 'desc')
					->skip($offset)
					->take(DEFAULT_TAG_LIST_COUNT_PER_PAGE)->get();
	
		// prepare for output data
		$this->setJSONOutputInfo("data", Tag::getArrayListForMetaList($tags));
	
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	
	/**
	 * Get single tag information
	 * @param id $tag_id
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function show($tag_id) {

		if (env("APP_DEBUG")) {
			\Log::info("Tag View Request : " . json_encode(Request::all()));
		}

		$tagType = Request::input('type');

		if (empty($tagType)) $tagType = 'live';

		// validation
		if (empty($tag_id)) {
			$this->setErrorCode("TAG_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}

		$tag = null;

		try {

			if ($tagType == 'live') {
				$tag = Tag::findOrFail($tag_id);
			} else if ($tagType == 'preview') {
				$tag = PreviewTag::findOrFail($tag_id);
			} else {
				throw new \Exception('Unknown tag type.');
			}

		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("TAG_NOT_FOUND");
			return $this->sendJSONOutput();
		}

		// prepare for output data
		$this->setJSONOutputInfo("data", $tag->getJSONArrayForTagDetail());

		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
	
	/**
	 * Create manual tag - from on air screen
	 */
	
	public function createManualTag() {
		
		$tagType = Request::input('type');
		$original_who = Request::input('original_who');
		
		// validation
		if (empty($tagType)) {
			$this->setErrorCode("TAG_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		
		$newTag = Tag::CreateManualTag(\Auth::User()->station->id, \Auth::User()->id, "", "", $original_who, "", "", $tagType, \Auth::User()->station->station_name);
		
		if ($newTag) {
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
		} else {
			$this->setErrorCode("TAG_RECORD_INSERT_ERROR");
			return $this->sendJSONOutput();
		}
		
		
	}
	
	
	
	public function getTagEventsDiagnostics($tagId){
		
		if (env("APP_DEBUG")) {
			\Log::info("TAG Diagnostics View Request : " . json_encode(Request::all()));
		}
		
		// validation
		if (empty($tagId)) {
			$this->setErrorCode("TAG_ID_PARAM_MISSING");
			return $this->sendJSONOutput();
		}
		
		$tag = null;
		
		try {
			$tag = Tag::findOrFail($tagId);
		} catch(\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("TAG_NOT_FOUND");
			return $this->sendJSONOutput();
		}
		
		// prepare for output data
		/*$this->setJSONOutputInfo("data", $tag->getTagEventsDiagnostics());
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();*/
		echo "<pre>";
		echo json_encode($tag->getTagEventsDiagnostics(), JSON_PRETTY_PRINT);
		echo "</pre>";
	}
}
