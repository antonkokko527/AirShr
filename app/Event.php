<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

if (!defined('DEFAULT_AUDIO_RECORD_DURATION')) {
	define('DEFAULT_AUDIO_RECORD_DURATION', 5);
}

if (!defined('DEFAULT_ITUNES_PREVIEW_DURATION')) {
	define('DEFAULT_ITUNES_PREVIEW_DURATION', 30);
}

if (!defined('MATCHER_DELAY_THRESHOLD')) {
	define('MATCHER_DELAY_THRESHOLD', 0);
}

class Event extends Model {

	use SoftDeletes;
	
	public static $REMOTE_BUTTON_PRESS_DELAY = 1500;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_events';
	
	protected $guarded = array();
	
	// Returns the event user timestamp considering button press type 
	public function getEventUserTimestamp() {
		if ($this->button_press_type == 'Remote') {
			return $this->record_timestamp_ms - Event::$REMOTE_BUTTON_PRESS_DELAY;
		}
			
		return $this->record_timestamp_ms;
	}
	
	
	public function tagForEvent() {
		return $this->belongsTo('App\Tag', 'tag_id');
	}
	
	public function stationForEvent() {
		return $this->belongsTo('App\Station', 'station_id');
	}
	
	public function userForEvent() {
		return $this->hasOne('App\User', 'user_id', 'record_device_id');
	}
	
	public function eventCoverArt() {
		return $this->belongsTo('App\CoverArt', 'event_coverart_id');
	}
	
	public function coverArtForEvent() {
				
		$tag = $this->tagForEvent;
		
		if (empty($tag)) {
			return null;
		}
		
		return $tag->coverart;
	}
	
	public function connectContentForEvent() {
	
		$tag = $this->tagForEvent;
	
		if (empty($tag)) {
			return null;
		}
	
		return $tag->connectContent;
	}
	
	public static function getArrayListForEventList($items) {
		$result = array();
		foreach($items as $event) {
			//$result[] = $event->getJSONArrayForEventList();
			$result[] = $event->getJSONArrayForEventDetail();
		}
		return $result;
	}

	public function getJSONArrayForDiagnostics() {
		
		$result = array();
		
		$result['id'] 					= $this->id;
		$result['record_file']			= $this->record_file;
		$result['record_timestamp']		= $this->record_timestamp;
		$result['record_timestamp_ms']		= $this->record_timestamp_ms;
		$result['event_data_status'] 	= $this->event_data_status;
		$result['response_timestamp'] 	= $this->event_data_status_updateon;
		$result['match_percent']		= $this->match_percent;
		$result['match_time']			= $this->match_time;
		$result['device_type']			= $this->device_type;
		$result['button_press_type']	= $this->button_press_type;
		$result['terrestrial_delay']	= $this->terrestrial_delay;
		
		return $result;
		
	}
	
	public function getJSONArrayForDiagnostics2() {
	
		$sqsProcessTime = $this->sqs_processed_on == 0 ? $this->match_timeout : $this->sqs_processed_on;
		$firstPollTime = $this->device_type == 'Android' ? $sqsProcessTime : $this->first_poll_timestamp;
		$totalRoundTrip = $firstPollTime == 0 ? ($sqsProcessTime - getSecondsFromMili($this->record_timestamp_ms)) : ($firstPollTime - getSecondsFromMili($this->record_timestamp_ms));
		$matchDuration = getSecondsFromMili($this->sqs_response_sent_on) - $this->sqs_sent_on;
		
		$result = [
		
			'Event ID'						=> $this->id,
			'Record File'					=> $this->record_file,
			'Status'						=> $this->event_data_status == 0 ? "Finding..." : ($this->event_data_status == 1 ? "Match Found" : "OOPS"),
			'Device'						=> $this->device_type,
			'Button Type'					=> $this->button_press_type,
			'Terrestiral Delay'				=> $this->terrestrial_delay,
			'Timestamps'					=> [
												  'Record Start Time'   =>  getDateTimeStringInTimezone(getSecondsFromMili($this->record_timestamp_ms), "Y-m-d H:i:s", "Australia/Sydney"),
												  'Event Creation Time'	=> getDateTimeStringInTimezone($this->app_request_received_on, "Y-m-d H:i:s", "Australia/Sydney"),
												  'SQS Request Sent Time (to matcher)' => getDateTimeStringInTimezone($this->sqs_sent_on, "Y-m-d H:i:s", "Australia/Sydney"),
												  'SQS Response Sent Time (from matcher)' => getDateTimeStringInTimezone(getSecondsFromMili($this->sqs_response_sent_on), "Y-m-d H:i:s", "Australia/Sydney"),
												  'SQS Response Process Time' => getDateTimeStringInTimezone($sqsProcessTime, "Y-m-d H:i:s", "Australia/Sydney"),
												  'Event Display Time (App Listview)' => $firstPollTime == 0 ? "" : getDateTimeStringInTimezone($firstPollTime, "Y-m-d H:i:s", "Australia/Sydney")
											   ],
											   
			'Durations'						=> [
												  'Matching Duration'	=>	$matchDuration,
												  'Other' => $totalRoundTrip - $matchDuration,
												  'Total Round Trip'	=>   $totalRoundTrip
											   ]
		];
		
		
		/*$result = array();
	
		
		
		$result['id'] 					= $this->id;
		$result['record_file']			= $this->record_file;
		$result['App Record AWS Time']	= getDateTimeStringInTimezone(getSecondsFromMili($this->record_timestamp_ms), "Y-m-d H:i:s", "Australia/Sydney");
		
		$result['App Request Receive Time']	= getDateTimeStringInTimezone($this->app_request_received_on, "Y-m-d H:i:s", "Australia/Sydney");
		$result['SQS Request Sent Time']	= getDateTimeStringInTimezone($this->sqs_sent_on, "Y-m-d H:i:s", "Australia/Sydney");
		$result['SQS Response Sent Time']	= getDateTimeStringInTimezone(getSecondsFromMili($this->sqs_response_sent_on), "Y-m-d H:i:s", "Australia/Sydney");
		$result['SQS Response Process Time']	= getDateTimeStringInTimezone($this->sqs_processed_on, "Y-m-d H:i:s", "Australia/Sydney");
		$result['Total Round Trip'] = ($this->sqs_processed_on == 0) ? ($this->match_timeout - $this->app_request_received_on) :  ($this->sqs_processed_on - $this->app_request_received_on);
		
		$result['event_data_status'] 	= $this->event_data_status;
		$result['match_percent']		= $this->match_percent;
		$result['match_time']			= $this->match_time;
		$result['device_type']			= $this->device_type;
		$result['button_press_type']	= $this->button_press_type;
		$result['terrestrial_delay']	= $this->terrestrial_delay;*/
	
		return $result;
	
	}
	
	public function getJSONArrayForEventList() {
		
		$stationForEvent = $this->stationForEvent;
				
		$resultArray =  array(
				'id'					=> $this->id,
				'record_timestamp'		=> $this->record_timestamp,
				'record_timestamp_ms'	=> $this->record_timestamp_ms,
				'record_device_id'		=> $this->record_device_id,
				'station_id'			=> $this->station_id,
				'station_name'			=> empty($stationForEvent) ? '' : $stationForEvent->station_name,
				'station_abbrev'		=> empty($stationForEvent) ? '' : $stationForEvent->station_abbrev,
				'station_twitterhandle'	=> empty($stationForEvent) ? '' : $stationForEvent->station_twitterhandle,
				'content_type_id'		=> $this->content_type_id,
				'content_type'			=> ContentType::getContentTypeText($this->content_type_id),
				'content_color'			=> ContentType::getContentTypeColor($this->content_type_id),
				'event_data_status'		=> $this->event_data_status,
				'who'					=> '',
				'what'					=> '',
				'local_event_id'		=> $this->local_event_id
		);
		
		$audioInfo = $this->getAudioInfoForEvent();
		
		$resultArray['stream_url'] = $audioInfo['audioURL'];
		$resultArray['stream_duration'] = $audioInfo['audioDuration'];
		$resultArray['stream_duration_ms'] = $audioInfo['audioDurationMs'];
		
		$tagForEvent = $this->tagForEvent;
		
		if (!empty($tagForEvent)) {
			$resultArray['who'] = $tagForEvent->who;
			$resultArray['what'] = $tagForEvent->what;
		}
		
		$coverartForEvent = $this->coverArtForEvent();
		if (!empty($coverartForEvent) && !empty($coverartForEvent->google_music_url)) {
			$resultArray['google_music_url'] = $coverartForEvent->google_music_url;
		}
		
		$eventCoverArt = $this->eventCoverArt;
		if (!empty($eventCoverArt)) {
			
			$resultArray['who'] = $eventCoverArt->artist;
			$resultArray['what'] = $eventCoverArt->track;
			
			if (!empty($eventCoverArt->google_music_url)) {
				$resultArray['google_music_url'] = $eventCoverArt->google_music_url;
			}
		}
		
		return $resultArray;
	}
	
	public function getJSONArrayForEventDetail() {
		
		$resultArray = $this->getJSONArrayForEventList();
		
		$resultArray['connectContent'] = array();
		
		$connectContentForEvent = $this->connectContentForEvent();
		$coverartForEvent = $this->coverArtForEvent();
		$eventCoverart = $this->eventCoverArt;
		
		if (!empty($connectContentForEvent) && $connectContentForEvent->is_ready) {
			$resultArray['connectContent'] = $connectContentForEvent->getArrayDataForApp();
			$resultArray['more'] = $connectContentForEvent->more . "";
			
			// add logo if any
			//$logoAttachment = $connectContentForEvent->getLogoAttachment();
			/*if (!empty($logoAttachment)) {			// has airshr connect logo data?
				$logoAttachmentInfo = $logoAttachment->getJSONArrayForAttachment();
				$resultArray['logo_url'] = $logoAttachmentInfo['url'];
			}*/
		}
		
		if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {   // if content is music, place get action with itunes url, lyrics and cover art picture
			
			if (empty($coverartForEvent) && !empty($eventCoverart)) {
				$coverartForEvent = $eventCoverart;
			}
			
			// get action
			$resultArray['connectContent']['action'] = array('action_type' => 'get', 'action_label' => 'Get');
			// get action parameter
			if (!empty($coverartForEvent) && !empty($coverartForEvent->itunes_url)) {
				$resultArray['connectContent']['action_params'] = array('website' => $coverartForEvent->itunes_url);
			}
			// get action parameter for google music
			if (!empty($coverartForEvent) && !empty($coverartForEvent->google_music_url)) {
				if (!isset($resultArray['connectContent']['action_params'])) 
					$resultArray['connectContent']['action_params'] = array();
				$resultArray['connectContent']['action_params']['website_google'] = $coverartForEvent->google_music_url;
			}
			
			// lyrics
			if (!empty($coverartForEvent) && !empty($coverartForEvent->lyrics)) {
				$resultArray['more'] = $coverartForEvent->lyrics . "";
			}
			// cover art picture
			if (!empty($coverartForEvent)) {
				
				$coverartAttachments = $coverartForEvent->getCoverArtAttachmentsArray();
				if (count($coverartAttachments) > 0) $resultArray['connectContent']['attachments'] = $coverartAttachments;
				
			}
		}	
		
		// add share url
		$tagForEvent = $this->tagForEvent;
		if (!empty($tagForEvent)) {
			$resultArray['share_url'] = \Config::get('app.AirShrShareURLBase') . $tagForEvent->createHashForTag();
		}
		
		
		// Associate default content if none is attached
		if ($this->content_type_id != ContentType::$MUSIC_CONTENT_TYPE_ID) {
			
			if (empty($connectContentForEvent) || !$connectContentForEvent->is_ready) { // if non is associated, or not airshr ready
				
				if (!empty($tagForEvent)) {
					$resultArray['who'] = $tagForEvent->original_who;
					$resultArray['what'] = $tagForEvent->original_what;
					
					$defaultContent = ConnectContent::GetStationDefaultContent($tagForEvent->station_id);
					
					if (!empty($defaultContent)) {
						$resultArray['connectContent'] = $defaultContent->getArrayDataForApp();
						$resultArray['more'] = $defaultContent->more . "";
						
						$resultArray['who'] = $defaultContent->who;
						$resultArray['what'] = $defaultContent->what;
					}
					
				}
			}			
		}
		
		// returns redirct url for website url
		if (!empty($resultArray['connectContent']) && !empty($resultArray['connectContent']['action_params']) && !empty($resultArray['connectContent']['action_params']['website'])) {
			$resultArray['connectContent']['action_params']['website'] = sprintf(\Config::get('app.EventLinkTrackURL'), urlencode($resultArray['connectContent']['action_params']['website']), $this->id);
		}
		if (!empty($resultArray['connectContent']) && !empty($resultArray['connectContent']['action_params']) && !empty($resultArray['connectContent']['action_params']['website_google'])) {
			$resultArray['connectContent']['action_params']['website_google'] = sprintf(\Config::get('app.EventLinkTrackURL'), urlencode($resultArray['connectContent']['action_params']['website_google']), $this->id);
		}
		
		// include vote information
		if ($connectContentForEvent && $connectContentForEvent->is_vote && $connectContentForEvent->is_ready) {
			$resultArray['v2_is_vote'] = '1';
			$resultArray['vote_question'] = empty($connectContentForEvent->vote_question) ? "" : $connectContentForEvent->vote_question;
			$resultArray['v2_vote_option_1'] = empty($connectContentForEvent->vote_option_1) ? "" : $connectContentForEvent->vote_option_1;
			$resultArray['v2_vote_option_2'] = empty($connectContentForEvent->vote_option_2) ? "" : $connectContentForEvent->vote_option_2;
			$resultArray['content_color'] = ContentType::getContentSpecialColor('VOTE');
			
			if ($tagForEvent) {
				$resultArray['vote_expired'] = $tagForEvent->vote_expired ? '1' : '0';
				$resultArray['vote_option_1_count'] = $tagForEvent->vote_option1_count;
				$resultArray['vote_option_2_count'] = $tagForEvent->vote_option2_count;
			}
			
			$resultArray['vote_selection'] = $this->vote_selection;
			
			$resultArray['content_type'] = 'Vote';
			$resultArray['what'] = $resultArray['vote_question'];
		}
		
		// include rate information
		if ($tagForEvent) {
			$resultArray['is_rating'] = $tagForEvent->is_rating;
			$resultArray['rate_option'] = $this->rate_option;
			$resultArray['rate_timestamp'] = $this->rate_timestamp;
		}
		
		return $resultArray;
	}
	
	
	public function getAudioInfoForEvent() {
		
		$result = array('audioURL' => '', 'audioDuration' => 0, 'audioDurationMs' => 0);
				 
		//if ($this->event_data_status == 1) {			// Found match ?
		if (!empty($this->station_id) && !empty($this->tag_id)) {			// Found match ?
			
			$tagForEvent = $this->tagForEvent;
			$coverartForEvent = $this->coverArtForEvent();
			$connectContentForEvent = $this->connectContentForEvent();
			
			$eventCoverart = $this->eventCoverArt;
			if (empty($coverartForEvent) && !empty($eventCoverart)) {
				$coverartForEvent = $eventCoverart;
			}
			
			$station_name = Station::getStationNameById($this->station_id);
			
			if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {   // if content is music, then itunes preview audio
				if (!empty($coverartForEvent) && !empty($coverartForEvent->preview)) {
					$result['audioURL'] = $coverartForEvent->preview;
					$result['audioDuration'] = DEFAULT_ITUNES_PREVIEW_DURATION;
					$result['audioDurationMs'] = DEFAULT_ITUNES_PREVIEW_DURATION * 1000;
				}
			} else {			// Not Music then get segment from tag
				
				$hasConnectAudio = false;
				
				if (!empty($connectContentForEvent)) {			// has airshr connect data?
					$audioAttachment = $connectContentForEvent->getAudioAttachment();
					
					if (!empty($audioAttachment)) {			// has airshr connect audio data?
						$audioAttachmentInfo = $audioAttachment->getJSONArrayForAttachment();
						
						$result['audioURL'] = $audioAttachmentInfo['url'];
						$result['audioDuration'] = $connectContentForEvent->ad_length + 0;
						
						if ($result['audioDuration'] == 0) {
							$result['audioDuration'] = $audioAttachment->duration + 0;
						}
						
						if ($result['audioDuration'] == 0) { 
							if ($tagForEvent && $tagForEvent != null) {
								$result['audioDuration'] = getSecondsFromMili($tagForEvent->getTagDuration()) + 0;
							} 
						}
						
						$result['audioDurationMs'] = $result['audioDuration'] * 1000;
						$hasConnectAudio = true;
					}
				}
				
				if (!$hasConnectAudio) {								// no connect audio?
					if ($tagForEvent && $tagForEvent != null) {		// only if matching tag is found

						$duration = $tagForEvent->getTagDuration();
						//$startTimeStamp = $tagForEvent->tag_timestamp;
						//$endTimestamp = $tagForEvent->tag_timestamp + $tagForEvent->getTagDuration();
						
						// get terrestrial delay
						/*$delay = TerrestrialStreamDelay::getMostRecentTerrestrialDelayOfEvent($this);
					
						$terrestrialDelay = 0;
					
						if ($delay) {
							$terrestrialDelay = $delay->terrestrial_stream_delay;
						}*/
						
						/*$terrestrialDelay = $this->recent_terrestrial_log;
					
						$startTimeStamp += $terrestrialDelay;
						$endTimestamp += $terrestrialDelay;
						
						// station profanity delay
						$station = $this->stationForEvent;
						if ($station) {
							$startTimeStamp += $station->profanity_delay;
							$endTimestamp += $station->profanity_delay;
						}
							
						$duration = $endTimestamp - $startTimeStamp;*/
							
						
						//$result['audioURL'] = \Config::get('app.AudioStreamURL') . "/" . $station_name . "/" . $startTimeStamp . "-" . $endTimestamp . ".m3u8";
						if (!empty($tagForEvent->trimmed_audio)) {
							$result['audioURL'] = $tagForEvent->trimmed_audio;
							$result['audioDuration'] = getSecondsFromMili($duration);
							$result['audioDurationMs'] = $duration;
						}
					}
				}
			}
			
		} else {										// Did not found match, send recorded audio url - s3
			
			/*$result['audioURL'] = $this->record_file;
			$result['audioDuration'] = DEFAULT_AUDIO_RECORD_DURATION;	
			$result['audioDurationMs'] = DEFAULT_AUDIO_RECORD_DURATION * 1000;*/
			
			$eventCoverart = $this->eventCoverArt;
			
			if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID && !empty($eventCoverart)) {   
				if (!empty($eventCoverart->preview)) {
					$result['audioURL'] = $eventCoverart->preview;
					$result['audioDuration'] = DEFAULT_ITUNES_PREVIEW_DURATION;
					$result['audioDurationMs'] = DEFAULT_ITUNES_PREVIEW_DURATION * 1000;
				}
			}
		}
		
		return $result;
		
	}
	
	
	public function sendEventUpdatePushNotification() {
	
		try {
			//\Artisan::queue('airshr:sendeventupdatenotify', ['eventid' => $this->id]);
			//AirShrArtisanQueue::QueueArtisanCommandToConnectQueue('airshr:sendeventupdatenotify', ['eventid' => $this->id]);
			
			AirShrArtisanQueue::QueueArtisanCommand('airshr:sendeventupdatenotify', ['eventid' => $this->id], \Config::get('app.QueueForEventUpdatePush'));
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
	public static function SendUpdatePushNotificationForEvent($eventId) {
		
		try {
			
			AirShrArtisanQueue::QueueArtisanCommand('airshr:sendeventupdatenotify', ['eventid' => $eventId], \Config::get('app.QueueForEventUpdatePush'));
				
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
	}
	
	public function markEventAsTimeout() {
		
		$this->updateEventWithListenerResult(['success' => false]);	
		$this->event_data_status_updateon = 0;
		$this->match_timeout = time();
		//$this->sqs_processed_on = 0;
		$this->save();
	}
	
	public function updateEventWithListenerResult($listenerServiceResult, $sqs_processed_on = 0, $sqs_sent_time = 0) {
		
		$matchFound = false;
		if ($listenerServiceResult['success']) {
			$matchFound = true;
		}
		
		$this->event_data_status = $matchFound ? 1 : -1;
		$this->event_data_status_updateon = time();
		
		if (isset($listenerServiceResult['match_percent'])) {
			$this->match_percent = $listenerServiceResult['match_percent'];
		}
		
		$match_station = false;
		$tag_timestamp = 0;
		
		if ($matchFound) {
			$match_station = Station::getStationByName($listenerServiceResult['station_name']);
			if ($match_station != false) {
				$this->station_id = $match_station->id;
			}
			$this->match_time = $listenerServiceResult['match_time'];
			$match_timestamp = $this->match_time;
				
			$match_delay = $match_timestamp - $this->record_timestamp_ms;
				
			$terrestrialDelay = 0;
						
			if ($match_delay >= MATCHER_DELAY_THRESHOLD) { // Terrestrial Radio Event
					
				// Store Terrestrial Log
				try {
					TerrestrialStreamDelay::create([
											'event_id'					=> $this->id,
											'station_id'				=> $this->station_id,
											'event_timestamp'			=> $this->record_timestamp_ms,
											'match_timestamp'			=> $match_timestamp,
											'terrestrial_stream_delay' 	=> $match_delay
											]);
					
				} catch(\Exception $ex) {
					\Log::error($ex);
				}
					
			} else {	// Stream Radio Event
					
				// get recent terrestrial log
				$recentTerrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelay($this->station_id);
					
				if ($recentTerrestrialDelay) {
					$terrestrialDelay = $recentTerrestrialDelay->terrestrial_stream_delay;
				}
					
			}
		
			//$tag_timestamp = $this->getEventUserTimestamp() + $terrestrialDelay;
		
			// Time accuracy version 2
			$tag_timestamp = $match_timestamp - $terrestrialDelay;
		
			$this->terrestrial_delay = $terrestrialDelay;
				
		} else {
			$this->station_id = Station::$DEFAULT_MATCH_STATION_ID;
			$this->match_time = $this->record_timestamp_ms;
				
			$this->terrestrial_delay = 0;
			$tag_timestamp = $this->match_time;
			
		}
		
		// get recent terrestrial log
		$recentTerrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelayOfEvent($this);
		if ($recentTerrestrialDelay) {
			$this->recent_terrestrial_log = $recentTerrestrialDelay->terrestrial_stream_delay;
		}
		
		
		if ($this->station_id > 0 && $tag_timestamp > 0) {
		
			// profanity station
			if (!$match_station) {
				$match_station = Station::getStationById($this->station_id);
			}
			if ($match_station){
				$tag_timestamp = $tag_timestamp - $match_station->profanity_delay;
			}
			
			// use match timestamp
			//$tags = Tag::getMostRecentTagByTimestamp($match_station->id, $tag_timestamp);
		
			// use user timestamp
			/*$tags = Tag::getMostRecentTagByTimestamp($this->station_id, $tag_timestamp);
		
			$currentTag = $tags['current'];
			$prevTag = $tags['prev'];
		
			if ($currentTag && $currentTag->content_type_id == ContentType::GetSweeperContentTypeID()) {  // if sweeper tag, move to previous tag
				$currentTag = $prevTag;
			}*/
			
			$currentTag = Tag::GetRecentTagOfStationByTimestamp($this->station_id, $tag_timestamp);
		
			if ($currentTag) {
					
				$currentTag->createHashForTag(); // create hash for tag linked with event

				if (empty($this->tag_id) || $this->tag_id == 0) {		// if this event is not calculated in tag event count?
					$currentTag->increaseEventCount();
				}
				
				$this->content_type_id = $currentTag->content_type_id;
				$this->tag_id = $currentTag->id;
			}
		}
		
		$this->sqs_processed_on = $sqs_processed_on;
		$this->sqs_response_sent_on = $sqs_sent_time;
		$this->save();
		
		//AirShrArtisanQueue::QueueArtisanCommandToAPIQueue('airshr:sendeventupdatenotify', ['eventid' => $this->id]);		
		\Artisan::call('airshr:sendeventupdatenotify', ['eventid' => $this->id]);
	}
	
	public function updateEventFirstPollTime() {
		try {
			if ($this->event_data_status == 0) return;
			if ($this->first_poll_timestamp > 0) return;		// already set?
			
			$this->first_poll_timestamp = time();
			$this->save();
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
}
