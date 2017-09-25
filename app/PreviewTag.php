<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

if (!defined('DEFAULT_ITUNES_PREVIEW_DURATION')) {
	define('DEFAULT_ITUNES_PREVIEW_DURATION', 30);
}

class PreviewTag extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_preview_tags';
	
	protected $fillable = array('station_id', 'content_type_id', 'tag_timestamp', 'who', 'what', 'adkey', 'connect_content_id', 'coverart_id', 'tag_duration', 'cart', 'preview_date', 'original_who', 'original_what', 'zettaid', 'is_client_found');
	
	public function contentType()
	{
		return $this->belongsTo('App\ContentType', 'content_type_id');
	}
	
	public function station()
	{
		return $this->belongsTo('App\Station', 'station_id');
	}
	
	public function connectContent() {
		return $this->belongsTo('App\ConnectContent', 'connect_content_id');
	}
	
	public function coverart() {
		return $this->belongsTo('App\CoverArt', 'coverart_id');
	}
	
	public function candidateAudio() {
		return $this->hasOne('App\ConnectContentAttachment', 'candidate_adkey', 'adkey');
	}

	public function hasEnoughConnectData() {
		
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
//			$connectContent = $this->connectContent;
//			if (!$connectContent || !$connectContent->is_ready) return false;
//			return true;

			$connectContent = $this->connectContent;
			if (!$connectContent || !$this->isReady() || !$this->isTextEnabled() || !$this->isImageEnabled() || !$this->isActionEnabled())  {
				return false;
			}
			return true;

		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || !$this->isAudioEnabled() || !$this->isTextEnabled() || !$this->isImageEnabled() || !$this->isActionEnabled() || $this->getMusicIsReady() != '1') {
				return false;
			}			
			return true;
		}
		
		return true;
		
	}
	
	public function isAudioEnabled() {
		
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->audio_enabled) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || empty($coverart->preview)) return false;
			return true;
		}
		return false;
	}
	
	public function isTextEnabled() {
	
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->text_enabled) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || empty($coverart->lyrics)) return false;
			return true;
		}
		return false;
	}
	
	public function isImageEnabled() {
	
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->image_enabled) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || empty($coverart->coverart_url)) return false;
			return true;
		}
		return false;
	}
	
	public function isActionEnabled() {
	
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->action_enabled) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || empty($coverart->itunes_url)) return false;
			return true;
		}
		return false;
	}
	
	public function isReady() {
	
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->is_ready) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart) return false;
			return true;
		}
		return false;
	}
	
	public function hasConnectData(){
		
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			return true;
		}
		return false;
	}
	
	public function getFinalConnectContentID() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return 0;
		return $connectContent->id;
	}
	
	
	public static function getArrayListForPreviewLogList($items) {
		$result = array();
		foreach($items as $previewTag) {
			$result[] = $previewTag->getJSONArrayForPreviewLogList();
		}
		return $result;
	}

	public function getMusicIsReady() {
		$isReady = $this->isReady() ? '1' : '0';

		if($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverartForTag = $this->coverart;

			if (!empty($coverartForTag)) {
				if (!$coverartForTag->google_ready || !$coverartForTag->itunes_ready || empty($coverartForTag->google_music_url) || empty($coverartForTag->itunes_url)) {
					$isReady = '2';
				}
			} else {
				$isReady = '0';
			}
		}

		return $isReady;
	}
	
	public function getJSONArrayForPreviewLogList() {
		
		$connectContent = $this->connectContent;

		$isReady = $this->getMusicIsReady();

		return array(
					'id'				=> $this->id,
					'tag_timestamp'		=> $this->tag_timestamp,
					'who'				=> $this->who,
					'what'				=> $this->what,
					'adkey'				=> $this->adkey,
					'tag_duration'		=> $this->tag_duration,
					'hasConnectData'	=> $this->hasConnectData() ? '1' : '0',
					'audio_enabled'		=> $this->isAudioEnabled() ? '1' : '0',
					'text_enabled'		=> $this->isTextEnabled() ? '1' : '0',
					'image_enabled'		=> $this->isImageEnabled() ? '1' : '0',
					'action_enabled'	=> $this->isActionEnabled() ? '1' : '0',
					'is_ready'			=> $isReady,
					'content_color'		=> ContentType::getContentTypeColor($this->content_type_id),
					'content_type_id'	=> $this->content_type_id,
					'cart'				=> $this->cart,
					'connect_content_id' => ($connectContent && $connectContent->id) ? $connectContent->id : 0,
					'is_client_found' => $this->is_client_found,
					'zettaid' => $this->zettaid,
				);
		
	}
	
	
	public function getTagDuration() {
	
		//$tagDuration = $this->tag_duration * 1000 + 0;
		
		$tagDuration = 0;
		// tag duration is 0? not included in meta tag?
		if ($tagDuration <= 0) {
			$nextTag = PreviewTag::getJustNextTagForTag($this->station_id, $this->tag_timestamp);
			if ($nextTag) {
				$tagDuration = $nextTag->tag_timestamp - $this->tag_timestamp;
			}
		}
	
		return $tagDuration;
	}
	
	public static function getJustNextTagForTag($station_id, $tag_timestamp) {
	
		$result = null;
	
		try {
	
			$tags = PreviewTag::where('station_id', '=', $station_id)
							->where('tag_timestamp', '>', $tag_timestamp)
							->orderBy('tag_timestamp', 'asc')
							->take(1)
							->get();
	
			if (isset($tags[0])) {
				$result = $tags[0];
			}
	
		} catch (\Exception $ex) {
			$result = null;
			\Log::error($ex);
		}
	
		return $result;
	}
	
	public static function getTalkCountBetweenTimestamp($station_id, $start_timestamp, $end_timestamp) {
		
		try {
			$tagCount = PreviewTag::where('station_id', '=', $station_id)
								->where('tag_timestamp', '>', $start_timestamp)
								->where('tag_timestamp', '<=', $end_timestamp)
								->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
								->count();
		
			return $tagCount;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return 0;
		}
	}
	public static function getTodayTags($station_id, $timestamp = 0) {
	
		try {
				
			$stationObj = Station::findOrFail($station_id);
				
			$stationTimeZone = $stationObj->getStationTimezone();
			
			$todayStartTimestamp = getTodayStartTimestampInTimezone($stationTimeZone) * 1000;
			$todayEndTimestamp = getTodayEndTimestampInTimezone($stationTimeZone) * 1000;
			
			/*$todayStartTimestamp = getTodayStartTimestamp() * 1000;
			$todayEndTimestamp = getTodayEndTimestamp() * 1000;*/
				
			$tags = PreviewTag::where('station_id', '=', $station_id)
					->where('tag_timestamp', '>=', $todayStartTimestamp)
					->where('tag_timestamp', '<=', $todayEndTimestamp)
					->where('tag_timestamp', '>', $timestamp)
					//->orderBy('tag_timestamp', 'asc')
					->orderBy('id', 'asc')
					->with('connectContent')
					->with('coverart')
					->get();
				
			return $tags;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return array();
		}
	
	}
	
	
	public function getArrayDataForOnAir() {
	
		$result = array();
	
		$result['id'] = $this->id;
		$result['tag_timestamp'] = $this->tag_timestamp;
		$result['who'] = $this->who;
		$result['what'] = $this->what;
		$result['content_type_id'] = $this->content_type_id;
		$result['content_type_color'] = ContentType::getContentTypeColor($this->content_type_id);
		$result['tag_duration'] = $this->tag_duration;
		$result['connect_content_id'] = $this->connect_content_id;
		$result['coverart_id'] = $this->coverart_id;
		$result['hasConnectData'] = $this->hasEnoughConnectData() ? '1' : '0';

		return $result;
	
	}
	
	
	
	
	public function getJSONArrayForTagList() {
	
		$resultArray =  array(
				'id'					=> $this->id,
				'station_id'			=> $this->station_id,
				'station_name'			=> Station::getStationNameById($this->station_id),
				'station_abbrev'		=> Station::getStationAbbrevById($this->station_id),
				'content_type_id'		=> $this->content_type_id,
				'content_type'			=> ContentType::getContentTypeText($this->content_type_id),
				'content_color'			=> ContentType::getContentTypeColor($this->content_type_id),
				'who'					=> $this->who,
				'what'					=> $this->what,
				'original_who'			=> $this->original_who,
				'original_what'			=> $this->original_what
		);
	
		$audioInfo = $this->getAudioInfoForTag();
	
		$resultArray['stream_url'] = $audioInfo['audioURL'];
		$resultArray['stream_duration'] = $audioInfo['audioDuration'];
	
		return $resultArray;
	}
	
	
	public function getAudioInfoForTag() {
	
		$result = array('audioURL' => '', 'audioDuration' => 0);
	
		$coverartForTag = $this->coverart;
		$connectContentForTag = $this->connectContent;
			
		$station_name = Station::getStationNameById($this->station_id);
			
		if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {   // if content is music, then itunes preview audio
			if (!empty($coverartForTag) && !empty($coverartForTag->preview)) {
				$result['audioURL'] = $coverartForTag->preview;
				$result['audioDuration'] = DEFAULT_ITUNES_PREVIEW_DURATION;
			}
		} else {			// Not Music then get segment from tag
	
			$hasConnectAudio = false;
	
			if (!empty($connectContentForTag)) {			// has airshr connect data?
				$audioAttachment = $connectContentForTag->getAudioAttachment();
					
				if (!empty($audioAttachment)) {			// has airshr connect audio data?
					$audioAttachmentInfo = $audioAttachment->getJSONArrayForAttachment();
	
					$result['audioURL'] = $audioAttachmentInfo['url'];
					$result['audioDuration'] = $connectContentForTag->ad_length + 0;
					
					if ($result['audioDuration'] == 0) {
						$result['audioDuration'] = $audioAttachment->duration + 0;
					}
					
					if ($result['audioDuration'] == 0) {
						$result['audioDuration'] = getSecondsFromMili($this->getTagDuration()) + 0;
					}
					
					$hasConnectAudio = true;
				}
			}
	
			if (!$hasConnectAudio) {								// no connect audio?
	
				$startTimeStamp = $this->tag_timestamp;
				$endTimestamp = $this->tag_timestamp + $this->getTagDuration();
	
				// get terrestrial delay
				$delay = TerrestrialStreamDelay::getMostRecentTerrestrialDelayOfTag($this->station_id, $this->tag_timestamp);
					
				$terrestrialDelay = 0;
					
				if ($delay) {
					$terrestrialDelay = $delay->terrestrial_stream_delay;
				}
					
				$startTimeStamp += $terrestrialDelay;
				$endTimestamp += $terrestrialDelay;
				
				// profanity
				$tagStation = $this->station;
				if ($tagStation) {
					$startTimeStamp += $tagStation->profanity_delay;
					$endTimestamp += $tagStation->profanity_delay;
				}
					
				$duration = $endTimestamp - $startTimeStamp;
					
				$result['audioURL'] = \Config::get('app.AudioStreamURL') . "/" . $station_name . "/" . $startTimeStamp . "-" . $endTimestamp . ".m3u8";
				$result['audioDuration'] = getSecondsFromMili($duration);
			}
		}
	
		return $result;
	
	}
	
	public function getJSONArrayForTagDetail() {
	
		$resultArray = $this->getJSONArrayForTagList();
	
		$resultArray['connectContent'] = array();
	
		$connectContentForTag = $this->connectContent;
		$coverartForTag = $this->coverart;
	
		if (!empty($connectContentForTag)) {
			$resultArray['connectContent'] = $connectContentForTag->getArrayDataForApp();
			$resultArray['more'] = $connectContentForTag->more;
			$resultArray['is_ready'] = $connectContentForTag->is_ready;
			$resultArray['text_enabled'] = $connectContentForTag->text_enabled;
			$resultArray['image_enabled'] = $connectContentForTag->image_enabled;
			$resultArray['action_enabled'] = $connectContentForTag->action_enabled;
            $resultArray['audio_enabled'] = $connectContentForTag->audio_enabled;
		}
		
		$resultArray['connectContentId'] = $this->connect_content_id;
	
		if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {   // if content is music, place get action with itunes url, lyrics and cover art picture
			// get action
			$resultArray['connectContent']['action'] = array('action_type' => 'get', 'action_label' => 'Get');
			// get action parameter
			if (!empty($coverartForTag) && !empty($coverartForTag->itunes_url)) {
				$resultArray['connectContent']['action_params'] = array('website' => $coverartForTag->itunes_url);
			}
			
			// get action parameter for google music
			if (!empty($coverartForTag) && !empty($coverartForTag->google_music_url)) {
				if (!isset($resultArray['connectContent']['action_params']))
					$resultArray['connectContent']['action_params'] = array();
				$resultArray['connectContent']['action_params']['website_google'] = $coverartForTag->google_music_url;
			}
			
			// lyrics
			if (!empty($coverartForTag) && !empty($coverartForTag->lyrics)) {
				$resultArray['more'] = $coverartForTag->lyrics;
			}
			// cover art picture
			if (!empty($coverartForTag)) {
				
				$resultArray['coverart_id'] = $coverartForTag->id;
				
				$coverartAttachments = $coverartForTag->getCoverArtAttachmentsArray();
				if (count($coverartAttachments) > 0) $resultArray['connectContent']['attachments'] = $coverartAttachments;
					
			} else {
				$resultArray['coverart_id'] = 0;
			}


			$resultArray['hasConnectData']	= $this->hasConnectData() ? '1' : '0';
			$resultArray['audio_enabled']	= $this->isAudioEnabled() ? '1' : '0';
			$resultArray['text_enabled']	= $this->isTextEnabled() ? '1' : '0';
			$resultArray['image_enabled']	= $this->isImageEnabled() ? '1' : '0';
			$resultArray['action_enabled']	= $this->isActionEnabled() ? '1' : '0';
			$resultArray['is_ready']		= $this->isReady() ? '1' : '0';
			
			if(!empty($coverartForTag)) {
				$resultArray['google_ready'] = $coverartForTag->google_ready;
				$resultArray['itunes_ready'] = $coverartForTag->itunes_ready;
				$resultArray['google_available'] = $coverartForTag->google_available;
				$resultArray['itunes_available'] = $coverartForTag->itunes_available;
                $resultArray['itunes_artist'] = $coverartForTag->itunes_artist;
                $resultArray['itunes_title'] = $coverartForTag->itunes_title;
			}

			if (!empty($coverartForTag)) {
				if(!$coverartForTag->google_ready || !$coverartForTag->itunes_ready || empty($coverartForTag->google_music_url) || empty($coverartForTag->itunes_url)) {
					$resultArray['is_ready'] = '2';
				}
			}
		}
		
		$resultArray['hasConnectData'] = $this->hasConnectData() ? '1' : '0';
		$resultArray['adkey'] = $this->adkey;
		$resultArray['finalContentID'] = $this->getFinalConnectContentID();
		$resultArray['is_client_found'] = $this->is_client_found;

		return $resultArray;
	}
	
	public function createAdContentForTag($userId = 0) {
		
		try {
			if (empty($this->adkey)) {
				return null;
			}

			if (!empty($this->connect_content_id)) {
				return null;
			}

			$data = array();
			$data['station_id']      = $this->station_id;
			$data['content_type_id'] = ContentType::findContentTypeIDByName('Ad');
			$data['connect_user_id'] = $userId;
			$data['ad_key']          = $this->adkey;
			$data['who']             = $this->who;
			$data['is_temp']         = 0;
			$data['is_ready']        = 0;
			$data['zettaid']         = $this->zettaid;
			
			$stationTimeZone = $this->station->getStationTimezone();
			$adStartDate     = getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp), "Y-m-d", $stationTimeZone);
			$adEndDate       = getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp) + 30 * 3600 * 24, "Y-m-d", $stationTimeZone);

			$connectContent = ConnectContent::create($data);
			$connectContent->addContentDate(0, $adStartDate, $adEndDate);
			
			// If we have a client then copy contents of that client.
			$client = ConnectContentClient::GetConnectContentByWho($this->original_who, $this->station_id);

			if ($client) {
				$connectContent->copyContentOfClient($client);
			}
			
			$connectContent->updateContentToTagsLink();
			$connectContent->searchAudioFileAndLink();

			// Update the preview tag with the correct "who" and the client found status.
			//$this->who = $who;
			$this->save();
			
			return $connectContent;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	public function findClientNameForTag()
	{
		// If the 'is_client_found' is true then we don't want to touch this preview tag anymore.
		if ($this->is_client_found === true) {
			return;
		}

		$isClientFound = false; // False unless proven otherwise.

		// The default "who" to use comes from the preview tag itself.
		$who = $this->who;

		// If we have a ZettaId then try to find the client by this ZettaId.
		if (!empty($this->zettaid)) {
			$lookup = ConnectClientLookup::getByZettaId($this->zettaid);

			// If we have a reference to the client in the client-lookup then try to find
			// the client in the content_clients table by the "client name" (full company name).
			if (!empty($lookup)) {
				// If we've found a client-look up then use the client name as the "who".
				$who = $lookup->client_name;

				// Try to find the client by the client-lookup client name.
				$client = ConnectContentClient::GetConnectContentByWho($who, $this->station->id);

				if (!empty($client)) {
					$who = $client->who;

					// Update the content with the client info.
					$tagTimestamp = getSecondsFromMili($this->tag_timestamp);

					$connectContent = ConnectContent::getConnectContentForTag($this->station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $this->adkey, $this->zettaid);
					$connectContent->copyContentOfClient($client, $who);

					// Mark this client found as "true".
					$isClientFound = true;
				}
			}
		}

		// Update the preview tag with the correct "who" and the client found status.
		$this->who             = $who;
		$this->is_client_found = $isClientFound;
		$this->save();
	}
	
	public function findConnectContentForTag() {
	
		try {
				
			$tagTimestamp = getSecondsFromMili($this->tag_timestamp);
				
			$saveChange = false;
				
			// search for connect content
			if (($this->content_type_id == ContentType::GetPromoContentTypeID() || $this->content_type_id == ContentType::GetAdContentTypeID()) && !empty($this->adkey)) {
				// look for airshr connect content
				$connectContentObj = ConnectContent::getConnectContentForTag($this->station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $this->adkey, $this->zettaid);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$this->who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$this->what = $connectContentObj->what;
					}
	
					$this->connect_content_id = $connectContentObj->id;
						
					$saveChange = true;
				}
			}
	
			if ($this->content_type_id == ContentType::GetTalkContentTypeID()) {
				// look for airshr connect content - talk
				$connectContentObj = ConnectContent::getConnectContentForTalkTag($this->station_id, $tagTimestamp);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$this->who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$this->what = $connectContentObj->what;
					}
						
					$this->connect_content_id = $connectContentObj->id;
						
					$saveChange = true;
				}
					
				$connectContentObj = ConnectContent::getConnectContentForIndividualTalk($this->station_id, $this->tag_timestamp);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$this->who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$this->what = $connectContentObj->what;
					}
						
					$this->connect_content_id = $connectContentObj->id;
						
					$saveChange = true;
				}
					
			}
	
			if ($this->content_type_id == ContentType::GetNewsContentTypeID()) {
					
				$connectContentObj = ConnectContent::getConnectContentForNewsTag($this->station_id, $tagTimestamp);
				if ($connectContentObj) {
					// replace who and what with connect data
					if (!empty($connectContentObj->who)) {
						$this->who = $connectContentObj->who;
					}
					if (!empty($connectContentObj->what)) {
						$this->what = $connectContentObj->what;
					}
						
					$this->connect_content_id = $connectContentObj->id;
						
					$saveChange = true;
				}
					
			}
				
			if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
	
				$coverartInfo = CoverArt::getCoverArtInfo($this->who, $this->what, $this->original_who, $this->original_what);
					
				if ($coverartInfo) {
	
					if (!empty($coverartInfo['artist']))
						$this->who = $coverartInfo['artist'];
	
					if (!empty($coverartInfo['track']))
						$this->what = $coverartInfo['track'];
	
					$this->coverart_id = $coverartInfo['id'];
						
					$saveChange = true;
				}
	
			}
				
			if ($saveChange) {
				$this->save();
			}
				
			return true;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	
	}
}
