<?php namespace App\MetaParsers;

use Cache;

class NovaParser extends BaseParser {
	
	protected static $TALK_SIGNALS = array(
		'talk_on'			=> 'vox_onair_on',
		'talk_off'			=> 'vox_onair_off',
		'net_switch_on'		=> 'rx_onair_on',
		'net_switch_off'	=> 'rx_onair_off'	
	);
	
	protected static $PREV_TAG_TRACK_COUNT = 2;
	
	public static $MUSIC_FADE_MILISECONDS_FOR_TALK = 20000;

	public static function isTalkOnSignal($talkSignal) {
		if ($talkSignal == static::$TALK_SIGNALS['talk_on']) {
			return true;
		}
		return false;
	}
	
	public static function isTalkOffSignal($talkSignal) {
		if ($talkSignal == static::$TALK_SIGNALS['talk_off']) {
			return true;
		}
		return false;
	}
	
	public static function isNetworkSwitchOnSingal($talkSignal) {
		if ($talkSignal == static::$TALK_SIGNALS['net_switch_on']) {
			return true;
		}
		return false;
	}
	
	public static function isNetworkSwitchOffSingal($talkSignal) {
		if ($talkSignal == static::$TALK_SIGNALS['net_switch_off']) {
			return true;
		}
		return false;
	}
	
	public static function filterTalkSignal($talkSignal, $stationName = '') {
		
		$talkSignal = strtolower(trim($talkSignal));
		
		$correctOne = false;
		
		foreach(static::$TALK_SIGNALS as $key => $val) {
			
			if (stripos($talkSignal, $val) === 0) {
				$correctOne = true;
				$talkSignal = $val;
				break;
			}
		}
		
		if (!$correctOne) return '';
		
		$prevTalkSignal = Cache::get($stationName . "_PREV_TALK_SIGNAL");
		
		// ignore duplicates
		if ($prevTalkSignal && $prevTalkSignal == $talkSignal) {
			return '';
		}
		
		Cache::put($stationName . "_PREV_TALK_SIGNAL", $talkSignal, 60);  // cache it for 1 hour
		
		return $talkSignal;
		
	}
	
	public static function getReservedTag($stationName = '') {
		
		$tagInfo = Cache::get($stationName . "_RESERVED_TAG_FOR_CREATION");
		
		return $tagInfo;
	}
	
	public static function setReservedTag($tagInfo, $stationName = '') {
		
		Cache::put($stationName . '_RESERVED_TAG_FOR_CREATION', $tagInfo, 60); // cache the previous tag for 1 hour
		
	}
	
	public static function removeReservedTag($stationName = '') {
		
		Cache::forget($stationName . "_RESERVED_TAG_FOR_CREATION");
		
	}
	
	public static function getPrevTalkSignal($stationName = '') {
		
		$prevTalkSignal = Cache::get($stationName . "_PREV_TALK_SIGNAL");
		
		return $prevTalkSignal;
	}
	
	
	public static function getPrevTag($stationName = '') {
		
		$previewTag = Cache::get($stationName . "_PREV_TAG_INFO");
		
		return $previewTag;
		
	}
	
	public static function storePrevTag($tag, $stationName = '') {
		
		Cache::put($stationName . '_PREV_TAG_INFO', $tag, 60); // cache the previous tag for 1 hour
		
	}
	
	public static function parseRealTimeMetaData($content, $stationName = '') {
		
		$result = null;
		
		try {

			// load xml string and parse
			$xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOWARNING );
			
			// loop zetta events
			$events = array();
					
			foreach ($xml->LogEvents->LogEvent as $event) {
				
				$assetTypeID = '';
				$assetTypeName = '';
				$title = '';
				$airStartTime = '';
				$airEndTime = '';
				$sponsorName = '';
				$artist = '';
				$statusCode = '';
				$description = '';
				
				
				try {
					$statusCode = (int)$event['StatusCode'][0];
				} catch (\Exception $ex2) {}
				
				if ($statusCode != 2) continue;    // Extract elements with statusCode = 2
				
				
				try {
					$assetTypeID = (int)$event->xpath('AssetEvent/Asset')[0]['AssetTypeID'];
				} catch (\Exception $ex2) {}
				
				try {
					$assetTypeName = (string)$event->xpath('AssetEvent/Asset')[0]['AssetTypeName'];
				} catch (\Exception $ex2) {}
				
				try {
					$title = (string)$event->xpath('AssetEvent/Asset')[0]['Title'];
				} catch (\Exception $ex2) {}
				
				try {
					$airStartTime = (string)$event['AirStarttime'][0];
				} catch (\Exception $ex2) {}
				
				try {
					$airEndTime = (string)$event['AirStoptime'][0];
				} catch (\Exception $ex2) {}
				
				try {
					$sponsorName = (string)$event->xpath('AssetEvent/Asset/Sponsor')[0]['Name'];
				} catch (\Exception $ex2) {}
				
				try {
					$artist = (string)$event->xpath('AssetEvent/Asset/Artist')[0]['Name'];
				} catch (\Exception $ex2) {}
				
				try {
					$description = (string)$event['Description'][0];
				} catch (\Exception $ex2) {}
				
				$events[] = array(
						'AssetTypeID'		=> $assetTypeID,
						'AssetTypeName'		=> $assetTypeName,
						'Title'				=> $title,
						'AirStarttime'		=> $airStartTime,
						'AirStoptime'		=> $airEndTime,
						'SponsorName'		=> $sponsorName,
						'Artist' 			=> $artist,
						'Description'		=> $description
				);
				
				
			}
			
			if (count($events) == 0) return $result;
			
			// select the last event as candidate
			$candidateEvent = $events[count($events) - 1];
			
			// compare with previous event. if same, ignore it
			$previousEvents = Cache::get($stationName . '_PREV_EVENT_INFO_ARRAY');

			if (!$previousEvents) $previousEvents = array();

			foreach ($previousEvents as $previousEvent) {
				// same with previous event? ignore it
				if ($previousEvent['AssetTypeID'] == $candidateEvent['AssetTypeID'] && $previousEvent['Title'] == $candidateEvent['Title'] && $previousEvent['Artist'] == $candidateEvent['Artist'] && $previousEvent['Description'] == $candidateEvent['Description']) {
					return $result;
				}
			}

			$result = $candidateEvent;

			array_push($previousEvents, $result);

			// keep only the pre defined number of previous tags
			if (count($previousEvents) > static::$PREV_TAG_TRACK_COUNT) {
				array_shift($previousEvents);
			}
			
			Cache::put($stationName . '_PREV_EVENT_INFO_ARRAY', $previousEvents, 60); // cache the previous event for 1 hour
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
	}
	
}