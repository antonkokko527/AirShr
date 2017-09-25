<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\MetaParsers\NovaParser;
use Cache;

if (!defined('DEFAULT_ITUNES_PREVIEW_DURATION')) {
	define('DEFAULT_ITUNES_PREVIEW_DURATION', 30);
}

class Tag extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_tags';
	
	protected $fillable = array('tagger_id', 'station_id', 'content_type_id', 'tag_timestamp', 'who', 'what', 'adkey', 'is_valid', 'insert_timestamp', 'insert_lag', 'connect_content_id', 'coverart_id', 'tag_duration', 'cart', 'event_count', 'original_who', 'original_what', 'hash', 'render_url', 'is_manual', 'meta_tag_timestamp', 'meta_tag_timestamp_diff');

	public static $TAG_HASH_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyz';
	
	public static $TAG_HASH_LENGTH = 5;
	
	public static $IS_REDIS_BACKEND_SET = false;
	
	public static function getMostRecentTagByTimestamp($station_id, $timestamp) {
		
		$result = array(
			'current' => false,
			'prev' => false	
		);
		
		try {

			$tags = Tag::where('station_id', '=', $station_id)
						->where('tag_timestamp', '<=', $timestamp)
						->where('is_valid', '=', 1)
						->orderBy('tag_timestamp', 'desc')
						->take(2)
						->get();
			
			if (isset($tags[0])) {
				$result['current'] = $tags[0];
			}
			
			if (isset($tags[1])) {
				$result['prev'] = $tags[1];
			}
			
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
		
		
		return $result;
	}
	
	public static function GetRecentTagOfStationByTimestamp($station_id, $timestamp, $ignore_sweeper = true) {
		
		$result = null;
		
		try {
		
			$tags = Tag::where('station_id', '=', $station_id)
						->where('tag_timestamp', '<=', $timestamp)
						->where('is_valid', '=', 1);
			
			if ($ignore_sweeper) {
				$tags = $tags->where('content_type_id', '<>', ContentType::GetSweeperContentTypeID());
			}
			
			$tags = $tags->orderBy('tag_timestamp', 'desc')
						 ->take(1)
						 ->get();
				
			if (isset($tags[0])) {
				$result = $tags[0];
			}
				
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
	}
	
	public static function getJustNextTag($tag_id) {
		
		$result = null;
		
		try {
			
			$currentTag = Tag::findOrFail($tag_id);
			
			$tags = Tag::where('station_id', '=', $currentTag->station_id)
						->where('tag_timestamp', '>', $currentTag->tag_timestamp)
						->where('is_valid', '=', 1)
						->orderBy('tag_timestamp', 'asc')
						->take(1)
						->get();
			
			if (isset($tags[0])) {
				$result = array(
					'current' => $currentTag,
					'next'	  => $tags[0]	
				);
			}
			
		} catch (\Exception $ex) {
			$result = null;
			\Log::error($ex);
		}
		
		
		return $result;
		
	}
	
	public static function getJustNextTagForTag($station_id, $tag_timestamp) {
	
		$result = null;
	
		try {
				
			$tags = Tag::where('station_id', '=', $station_id)
				->where('tag_timestamp', '>', $tag_timestamp)
				->where('is_valid', '=', 1)
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
	
	public function getNextTag($ignore_sweeper = true) {
		
		$result = null;
		
		try {
		
			$tags = Tag::where('station_id', '=', $this->station_id)
						->where('tag_timestamp', '>', $this->tag_timestamp)
						->where('is_valid', '=', 1);
				
			if ($ignore_sweeper) {
				$tags = $tags->where('content_type_id', '<>', ContentType::GetSweeperContentTypeID());
			}
				
			$tags = $tags->orderBy('tag_timestamp', 'asc')
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
	
	public function getJustPrevTag($ignore_sweeper = true) {
		
		$result = null;
		
		try {
		
			$tags = Tag::where('station_id', '=', $this->station_id)
						->where('tag_timestamp', '<', $this->tag_timestamp)
						->where('is_valid', '=', 1);
			
			if ($ignore_sweeper) {
				$tags = $tags->where('content_type_id', '<>', ContentType::GetSweeperContentTypeID());
			}
			
			$tags = $tags->orderBy('tag_timestamp', 'desc')
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
	
	public function getTagDuration($check_meta_duration = true) {
		
		//$tagDuration = $this->tag_duration * 1000 + 0;
		$tagDuration = $this->real_tag_duration;
						
		// tag duration is 0? not included in meta tag?
		/*if ($tagDuration <= 0) {
			$nextTag = Tag::getJustNextTagForTag($this->station_id, $this->tag_timestamp);
			if ($nextTag) {
				$tagDuration = $nextTag->tag_timestamp - $this->tag_timestamp;
			}
		}*/
		
		// if still 0, use the tag duration included in meta data
		if ($tagDuration <= 0 && $check_meta_duration) {
			$tagDuration = $this->tag_duration * 1000 + 0;
		} 
		
		return $tagDuration;
	}
	
	public static function getCurrentTag($station_id) {
		
		try {
				
			$currentTag = Tag::where('station_id', '=', $station_id)
								->where('is_valid', '=', 1)
								->orderBy('tag_timestamp', 'desc')
								->firstOrFail();
				
			return $currentTag;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	public static function getTodayTags($station_id, $timestamp = 0) {
		
		try {
			
			$stationObj = Station::findOrFail($station_id);
			
			$stationTimeZone = $stationObj->getStationTimezone();
			
			/*$todayStartTimestamp = getTodayStartTimestamp() * 1000;
			$todayEndTimestamp = getTodayEndTimestamp() * 1000;*/
			
			$todayStartTimestamp = getTodayStartTimestampInTimezone($stationTimeZone) * 1000;
			$todayEndTimestamp = getTodayEndTimestampInTimezone($stationTimeZone) * 1000;
			
			$tags = Tag::where('station_id', '=', $station_id)
						->where('tag_timestamp', '>=', $todayStartTimestamp)
						->where('tag_timestamp', '<=', $todayEndTimestamp)
						->where('tag_timestamp', '>', $timestamp)
						->where('is_valid', '=', 1)
						->orderBy('tag_timestamp', 'asc')
						->with('connectContent')
						->with('coverart')
						->get();
			
			return $tags;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return array();
		} 
		
	}
	
	public function hasEnoughConnectData() {
	
		if ($this->content_type_id == ContentType::GetAdContentTypeID() || $this->content_type_id == ContentType::GetPromoContentTypeID()) {
			$connectContent = $this->connectContent;
			if (!$connectContent || !$connectContent->is_ready) return false;
			return true;
		} else if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
			$coverart = $this->coverart;
			if (!$coverart || empty($coverart->coverart_url) || empty($coverart->preview) || empty($coverart->lyrics)) {
				return false;
			}
			return true;
		}
	
		return true;
	
	}
	
	public function getArrayDataForOnAir() {
	
		$result = array();
	
		$result['id'] = $this->id;
		$result['tag_timestamp'] = $this->tag_timestamp;
		$result['who'] = $this->who;
		$result['what'] = $this->what;
		$result['content_type_id'] = $this->content_type_id;
		$result['content_type_color'] = ContentType::getContentTypeColor($this->content_type_id);
		$result['station_id'] = $this->station_id;
		$result['event_count'] = $this->event_count + 0;
		$result['tag_duration'] = $this->tag_duration;
		$result['connect_content_id'] = $this->connect_content_id;
		$result['coverart_id'] = $this->coverart_id;
		$result['hasConnectData'] = $this->hasEnoughConnectData() ? '1' : '0';
		$result['is_competition'] = $this->isCompetitionTag() ? '1' : '0';
		$result['is_vote'] = $this->isVoteTag() ? '1' : '0';
		$result['is_manual'] = $this->is_manual;
		
		$result['vote_question'] = $this->connectContent ? $this->connectContent->vote_question . "" : "";
		
		return $result;
	
	}
	
	public function isCompetitionTag() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return false;
		if ($connectContent->is_competition && $connectContent->isContentTalkBreak()) return true;
		return false;
	}
	
	public function isVoteTag() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return false;
		if ($connectContent->is_vote && $connectContent->isContentTalkBreak()) return true;
		return false;
	}
	
	public function isTagConnectContentLive() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return false;
		if ($connectContent->is_ready) return true;
		return false;
	}
	
	public function getVoteDuration() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return 0;
		return $connectContent->vote_duration_minutes;
	}
	
	public function isCompetitionLive() {
		$connectContent = $this->connectContent;
		if (!$connectContent) return false;
		if ($connectContent->is_ready) return true;
		return false;
	}
	
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
	
	public function events() {
		return $this->hasMany('App\Event', 'tag_id', 'id');
	}
	
	public static function getArrayListForMetaList($items) {
		$result = array();
		foreach($items as $tag) {
			$result[] = $tag->getArrayForMetaInfo();
		}
		return $result;
	}
	
	
	public function getArrayForMetaInfo() {
		
		return array(
			'id'					=> $this->id,
			'station_id'			=> $this->station_id,
			'station_name'			=> Station::getStationNameById($this->station_id),
			'station_abbrev'		=> Station::getStationAbbrevById($this->station_id),
			'content_type_id'		=> $this->content_type_id,
			'content_type'			=> ContentType::getContentTypeText($this->content_type_id),
			'content_color'			=> ContentType::getContentTypeColor($this->content_type_id),
			'tag_timestamp'			=> getSecondsFromMili($this->tag_timestamp),
			'who'					=> $this->who,
			'what'					=> $this->what
		);
	}
	
	public function increaseEventCount2() {
		
		try {
			
			$this->event_count = $this->event_count + 1;
			$this->save();
			
			// send tag count update notification
			WebSocketPub::publishPushMessage(array(
				'event' 	=> 'TAG_COUNT_UPDATE',
				'tag'		=> array('id' => $this->id, 'count' => $this->event_count, 'station_id' => $this->station_id)
			));
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
	}
	
	public function getTagEventsDiagnostics() {
		
		$result = array();
		
		foreach ($this->events as $event) {
			$result[] = $event->getJSONArrayForDiagnostics2();
		}
		
		return $result;
	}
	
	public function increaseEventCount() {
	
		try {
				
			//\Artisan::queue('airshr:updatetageventcount', ['tagid' => $this->id]);
			//AirShrArtisanQueue::QueueArtisanCommandToConnectQueue('airshr:updatetageventcount', ['tagid' => $this->id]);
			//AirShrArtisanQueue::QueueArtisanCommand('airshr:updatetageventcount', ['tagid' => $this->id], \Config::get('app.QueueForTagEventCountUpdate'));
			
			
			if (!self::$IS_REDIS_BACKEND_SET) {
				\Resque::setBackend(\Config::get('app.Resque_Redis_Server_Host') . ':' . \Config::get('app.Resque_Redis_Server_Port'), 0, 'resque', \Config::get('app.Resque_Redis_Server_Password'));
				self::$IS_REDIS_BACKEND_SET = true;
			}
			
			$args = array();
			$args[] = $this->id;
			
			\Resque::enqueue(\Config::get('app.Resque_TagEventCount_Queue'), 'worker', $args);
			
				
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	
	}
	
	public static function IncreaseEventVoteOptionCount($tagId, $voteOption) {
		
		try {
			
			if (!self::$IS_REDIS_BACKEND_SET) {
				\Resque::setBackend(\Config::get('app.Resque_Redis_Server_Host') . ':' . \Config::get('app.Resque_Redis_Server_Port'), 0, 'resque', \Config::get('app.Resque_Redis_Server_Password'));
				self::$IS_REDIS_BACKEND_SET = true;
			}
			
			$args = array();
			$args[] = $tagId;
			$args[] = $voteOption;
			
			\Resque::enqueue(\Config::get('app.Resque_TagVoteCount_Queue'), 'worker', $args);
			
			//AirShrArtisanQueue::QueueArtisanCommand('airshr:updatetagvoteoptioncount', ['tagid' => $tagId, 'voteOption' => $voteOption], \Config::get('app.QueueForTagEventCountUpdate'));
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
	public function createHashForTag() {
		
		try {
		
			if (!empty($this->hash)) return $this->hash;
			
			// create new one
			$newHash = '';
			for ($i = 0; $i < self::$TAG_HASH_LENGTH; $i++) {
				$newHash .= self::$TAG_HASH_CHARACTERS[rand(0, strlen(self::$TAG_HASH_CHARACTERS) -1)];
			}

			$this->hash = $newHash;
			$this->save();
			
			return $this->hash;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}
	
	public function saveAudioRenderFile() {
		
		try {
		
			if (!empty($this->render_url)) return $this->render_url;

			$tagHash = $this->createHashForTag();
				
			if ($tagHash === FALSE) {
				throw new \Exception('Tag hash does not exist nor can not be created.');
			}
			
			
			$tagTimestamps = $this->getTagStartAndEndTimestamp();
			
			$station_name = Station::getStationNameById($this->station_id);
			
			$renderURL = sprintf(\Config::get('app.TagAudioRenderURL'), $station_name, $tagTimestamps['start'], $tagTimestamps['end'], "audio_" . $tagHash . ".mp3");
			
			$response = \Httpful\Request::get($renderURL)->send();
			
			if ($response->code == 200){
				$result_json = json_decode($response->body, true);
				$this->render_url = $result_json['mp3-url'];
				$this->save();
			} else {
				throw new \Exception('Audio Renderer response is wrong.');
			}
			
			return $this->render_url;
			
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}
	
	public function getTagRenderURL() {
		
		if ($this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {
			$coverartForTag = $this->coverart;
			if (!empty($coverartForTag) && !empty($coverartForTag->preview)) {
				return $coverartForTag->preview;
			}
		} else {
						
			return $this->render_url;
		}
		
		return "";
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
				'original_what'			=> $this->original_what,
				'tag_timestamp'			=> $this->tag_timestamp
				//'render_url'			=> $this->getTagRenderURL()
		);
	
		$audioInfo = $this->getAudioInfoForTag();
	
		$resultArray['stream_url'] = $audioInfo['audioURL'];
		$resultArray['stream_duration'] = $audioInfo['audioDuration'];
	
		// if connect audio exists, replace render url with it
		/*$connectContentForTag = $this->connectContent;
			
		if (!empty($connectContentForTag)) {
			$audioAttachment = $connectContentForTag->getAudioAttachment();
			if (!empty($audioAttachment)) {
				$resultArray['render_url'] = $resultArray['stream_url'];
			}
		}*/

		$resultArray['render_url'] = $audioInfo['audioURL'];
		
		return $resultArray;
	}
	
	
	public function getAudioInfoForTag( $allowMusic = true ) {
	
		$result = array('audioURL' => '', 'audioDuration' => 0);
				
		$coverartForTag = $this->coverart;
		$connectContentForTag = $this->connectContent;
			
		$station_name = Station::getStationNameById($this->station_id);
			
		if ($allowMusic && $this->content_type_id == ContentType::$MUSIC_CONTENT_TYPE_ID) {   // if content is music, then itunes preview audio
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
				
				$tagTimestamps = $this->getTagStartAndEndTimestamp();
				
				/*$startTimeStamp = $this->tag_timestamp;
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
				
				$duration = $endTimestamp - $startTimeStamp;*/
					
				//$result['audioURL'] = \Config::get('app.AudioStreamURL') . "/" . $station_name . "/" . $tagTimestamps['start'] . "-" . $tagTimestamps['end'] . ".m3u8";
				
				if (!empty($this->trimmed_audio)) {
					$result['audioURL'] = $this->trimmed_audio;
					$result['audioDuration'] = getSecondsFromMili($tagTimestamps['duration']);
				} else {
					$result['audioURL'] = '';
					$result['audioDuration'] = 0;
				}
				
				
			}
		}
				
		return $result;
	
	}
	
	public function getTagStartAndEndTimestamp() {
		
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
		
		return array(
					'start'	=> $startTimeStamp,
					'end' => $endTimestamp,
					'duration'	=> $duration
				);
		
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

			if(!empty($coverartForTag)) {
				$resultArray['google_ready'] = $coverartForTag->google_ready;
				$resultArray['itunes_ready'] = $coverartForTag->itunes_ready;
				$resultArray['google_available'] = $coverartForTag->google_available;
				$resultArray['itunes_available'] = $coverartForTag->itunes_available;
                $resultArray['itunes_artist'] = $coverartForTag->itunes_artist;
                $resultArray['itunes_title'] = $coverartForTag->itunes_title;
			}
			
		}
		
		$resultArray['vote_expiry_timestamp'] = $this->vote_expiry_timestamp + 0;
		$resultArray['vote_expired'] = $this->vote_expired ? '1' : '0';
		$resultArray['vote_option1_count'] = $this->vote_option1_count + 0;
		$resultArray['vote_option2_count'] = $this->vote_option2_count + 0;
		
		return $resultArray;
	}
	
	
	public function createAdContentForTag($userId = 0) {
	
		try {
				
			if (empty($this->adkey)) return null;
			if (!empty($this->connect_content_id)) return null;
	
			$stationTimeZone = $this->station->getStationTimezone();
			$adStartDate = getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp), "Y-m-d", $stationTimeZone);
			$adEndDate = getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp) + 30 * 3600 * 24, "Y-m-d", $stationTimeZone);

			// search for client
			$client = ConnectContentClient::GetConnectContentByWho($this->original_who, $this->station_id);
			
			$data = array();
				
			$data['station_id'] = $this->station_id;
			$data['content_type_id'] = ContentType::findContentTypeIDByName('Ad');
			$data['connect_user_id'] = $userId;
			$data['ad_key'] = $this->adkey;
			$data['who'] = $this->who;
			$data['what'] = $this->what;
			$data['is_temp'] = 0;
			$data['is_ready'] = ($client && $client->is_ready) ? 1 : 0;
				
			$connectContent = ConnectContent::create($data);
			$connectContent->addContentDate(0, $adStartDate, $adEndDate);
							
			if ($client) { // copy contents of client
				$connectContent->copyContentOfClient($client);
			}
				
			$connectContent->updateContentToTagsLink();
			$connectContent->searchAudioFileAndLink();
			
			return $connectContent;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	
	}
	
	public function generateTrimmedAudioForPreviousTag() {
		$prevTag = $this->getJustPrevTag(false);
		
		if ($prevTag && $prevTag->content_type_id != ContentType::GetMusicContentTypeID()) {
			//$prevTag->generateTrimmedAudio();
			
			// after 5 seconds, call trimmer url
			AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:generatetagtrimmedaudio', ['tagId' => $prevTag->id], \Config::get('app.QueueForAudioTrimGeneration'), 5);
		}
	}
	
	public function generateTrimmedAudio() {
		
		try {
			
			if ($this->content_type_id == ContentType::GetMusicContentTypeID()) {
				return;
			}
			
			$stationName = Station::getStationNameById($this->station_id);
			
			if (empty($stationName)) return;
			
			$tagDuration = $this->getTagDuration();

			$startTimestamp = $this->tag_timestamp;
			$endTimestamp = $startTimestamp + $tagDuration;

			//$tagTimestamps = $this->getTagStartAndEndTimestamp();
			
			$audioTrimURL = sprintf(\Config::get("app.AirShrAudioServiceInternalURL") . "audio/%s?timeFrom=%s&timeTo=%s", $stationName, $startTimestamp, $endTimestamp);
			
			if (!empty($this->adkey)) {
				$audioTrimURL .= "&type=ad&name=" . urlencode($this->adkey);
			}
			
			$success = false;
			$count = 1;
			
			while (!$success && $count < 10) {
				
				\Log::info("Audio Trim API URL: " . $audioTrimURL);
				
				$response = \Httpful\Request::get($audioTrimURL)->send();
				
				if ($response->code == 200){ 
	
					$result_json = json_decode($response->raw_body);
					
					if ($result_json && !empty($result_json->url)) {
						
						$this->trimmed_audio = $result_json->url;
						
						$this->save();
						
						$success = true;
					}
					
				}
				
				if ($success) break;
				else {
					$count++;
					sleep(10);
				}
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
	}
	
	
	public function applyForPreviousTagCompetitionGeneration() {
		$prevTag = $this->getJustPrevTag(false);
		if ($prevTag) {
			
			// update previous tag real duration
			$prevTag->updateRealTagDuration($this->tag_timestamp);

			// apply for competition generation
			$prevTag->applyForCompetitionGeneration();
		}		
	}
	
	public function updateRealTagDuration($nextTagTimestamp) {
		try {
			
			if ($nextTagTimestamp - $this->tag_timestamp > 0) {
				$this->real_tag_duration = $nextTagTimestamp - $this->tag_timestamp;
				$this->save();
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
	public function setTagWithVote($content) {
		
		if ($this->vote_expired && $this->vote_expiry_timestamp > 0) {		// vote report is already generated?
			return false;
		}
		
		if (!$content->is_vote) return false;
		
		$this->vote_expired = 0;
		
		if ($content->vote_duration_minutes > 0) {
			$this->vote_expiry_timestamp = time() + $content->vote_duration_minutes * 60;
		}
		
		$this->connect_content_id = $content->id;
		
		// no who and what for vote tags.
		$this->who = '';
		$this->what = '';
		
		$this->save();
		
		return true;
	}
	
	public function updateVoteDurationForTag($content) {
		
		if ($this->vote_expired && $this->vote_expiry_timestamp > 0) {		// vote report is already generated?
			return false;
		}
		
		if (!$content->is_vote) return false;
		
		if ($content->vote_duration_minutes > 0) {
			$this->vote_expired = 0;
			$this->vote_expiry_timestamp = time() + $content->vote_duration_minutes * 60;
			$this->save();
			
			$this->applyForVoteGeneration();
		}
		
		return true;
	}
	
	public static function StoreCurrentVoteDetails($stationID, $voteTagId, $voteStartTimestamp, $voteEndTimestap) {
		
		Cache::put($stationID . "_CURRENT_VOTE_INFO", ['vote_tag_id' => $voteTagId, 'vote_start_timestamp' => $voteStartTimestamp, 'vote_end_timestamp' => $voteEndTimestap], 60 * 24 * 1);  // cache it for 1 days
		
	}
	
	public static function RemoveCurrentVoteDetails($stationID, $voteTagId) {
		
		$voteDetails = static::GetCurrentVoteDetails($stationID);
		
		if (!empty($voteDetails) && !empty($voteDetails['vote_tag_id']) && $voteDetails['vote_tag_id'] == $voteTagId) {
			Cache::forget($stationID . "_CURRENT_VOTE_INFO");
		}
		
	}
	
	public static function GetCurrentVoteDetails($stationID) {
		
		return Cache::get($stationID . "_CURRENT_VOTE_INFO");
	}
	
	public function storeVoteRelatedTags() {
		
		try {
			//skip sweeper tags
			if ($this->content_type_id == ContentType::GetSweeperContentTypeID()) {
				return;
			}
						
			$currentVoteDetails = static::GetCurrentVoteDetails($this->station_id);
			
			if (empty($currentVoteDetails)) return;
			
			// not during vote? 
			if ($this->tag_timestamp <= $currentVoteDetails['vote_start_timestamp'] || $this->tag_timestamp >= $currentVoteDetails['vote_end_timestamp'] ) {
				return;
			}
			
			// same tag with vote? skipe
			if ($currentVoteDetails['vote_tag_id'] == $this->id) {
				return;
			}
						
			\DB::table('airshr_vote_related_tags')->insert(['tag_id' => $this->id, 'tag_timestamp' => $this->tag_timestamp, 'vote_tag_id' => $currentVoteDetails['vote_tag_id'], 'vote_tag_timestamp' => $currentVoteDetails['vote_start_timestamp']]);
			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		
	}
	
	
	public function applyForVoteGeneration() {
		
		if (!$this->isVoteTag() || !$this->isTagConnectContentLive()) return;
		
		if ($this->vote_expired) return;
		
		$voteDuration = $this->getVoteDuration();
		
		if ($voteDuration == 0) return;
		
		// store current vote details in the cache
		static::StoreCurrentVoteDetails($this->station_id, $this->id, $this->tag_timestamp, $this->tag_timestamp + $voteDuration * 60 * 1000);
		
		AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:generatevoteresultfortag', ['tagId' => $this->id], \Config::get('app.QueueForCompetition'), $voteDuration * 60 + 2);
	}
	
	public function applyForCompetitionGeneration() {

		if ($this->competition_result_generated) return;			// already generated? return

		$tagDuration = $this->getTagDuration(false);		
		if ($tagDuration == 0) {   // if this tag is Now playing tag? 
			return;
		}
		
		if (!$this->isCompetitionTag() || !$this->isCompetitionLive()) return;   // not competition, return
		
		$currentTimestamp = getCurrentMilisecondsTimestamp();
		
		if ($this->tag_timestamp + $tagDuration + ConnectContent::$COMPETITION_CHECKTIME_AFTER_ENDOFTAG * 1000 <= $currentTimestamp) {  // we should run it now	
			$this->generateCompetitionResult();
		} else {
			//AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:generatetagcompetitionresult', ['tagId' => $this->id], \Config::get('app.QueueForConnect'), ConnectContent::$COMPETITION_CHECKTIME_AFTER_ENDOFTAG);
			AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:generatetagcompetitionresult', ['tagId' => $this->id], \Config::get('app.QueueForCompetition'), ConnectContent::$COMPETITION_CHECKTIME_AFTER_ENDOFTAG);
		}
	}

	public function generateVoteResult() {
		
		try {
			
			if (!$this->isVoteTag() || !$this->isTagConnectContentLive()) return;
			if ($this->vote_expired) return;
			
			$voteDuration = $this->getVoteDuration();
			if ($voteDuration == 0) return;
			
			$now = time();

			if ($now < $this->vote_expiry_timestamp) return;
			
			
			/*$option1_count = \DB::table('airshr_events')
							->where('tag_id', $this->id)
							->where('vote_selection', '=', '1')
							->count();
			
			$option2_count = \DB::table('airshr_events')
							->where('tag_id', $this->id)
							->where('vote_selection', '=', '2')
							->count();*/
			
			$this->vote_expired = 1;
			//$this->vote_option1_count = $option1_count;
			//$this->vote_option2_count = $option2_count;
			$this->vote_generation_timestamp = time();
			
			$this->save();
			
			static::RemoveCurrentVoteDetails($this->station_id, $this->id);
			
			// update event push notification
			$this->sendEventsUpdatePushForTag();
			
			
		} catch (\Exception $ex) {
			
			\Log::error($ex);
			
		}
		
	}
	
	public function generateCompetitionResult() {

		try {
			if ($this->competition_result_generated) return;			// already generated? return
			
			$tagDuration = $this->getTagDuration(false);
			if ($tagDuration == 0) {   // if this tag is Now playing tag?
				return;
			}
			
			if (!$this->isCompetitionTag() || !$this->isCompetitionLive()) return;   // not competition, return
			
			$stationTimeZone = $this->station->getStationTimezone();
			$stationId = $this->station->id;
			$currentDayOfWeek = getCurrentTimeInTimezone('N', $stationTimeZone);
			
			$eventsForTag = $this->events;
			
			$users = array();
			
			foreach ($eventsForTag as $event) {
					
				if (empty($event->record_file)) continue; // from timemachine, continue
					
				$userForEvent = $event->userForEvent;
					
				if (!$userForEvent) continue;		// no user for this event, continue
					
				if (!isset($users[$userForEvent->id])) {
					$users[$userForEvent->id] = $userForEvent;
				}
			}
							
			$pickCount = min(ConnectContent::$COMPETITION_WINNER_COUNT, count($users));
							
			$pickedUserIdArray = array();
			$pickedUserPhoneNumArray = array();
			$data = array();
			
			if ($pickCount > 0) {
					
				// pick random users
				$pickedUsersKeys = array_rand($users, $pickCount);
					
				if (!is_array($pickedUsersKeys)) {
					$pickedUsersKeys = array($pickedUsersKeys);
				}
					
				$pickedUserPhoneNumbers = "";
				$i = 1;
				foreach($pickedUsersKeys as $key) {
					$pickedUser = $users[$key];
					//$pickedUserPhoneNumbers .= "{$i}. {$pickedUser->countrycode} {$pickedUser->phone_number} <br/>";
					$pickedUserPhoneNumbers .= "{$i}. {$pickedUser->phone_number} <br/>";
					$pickedUserIdArray[] = $pickedUser->id;
					//$pickedUserPhoneNumArray[] = "{$pickedUser->countrycode} {$pickedUser->phone_number}";
					$pickedUserPhoneNumArray[] = "{$pickedUser->phone_number}";
					$i++;
				}
					
				$data = array(
						'competitionDateTime' => getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp), "Y-m-d H:i:s", $stationTimeZone),
						'total_applicants'	=> count($users),
						'pick_count' => $pickCount,
						'user_list' => $pickedUserPhoneNumbers
				);
									
			} else {				// send mail also - empty user
					
				$data = array(
						'competitionDateTime' => getDateTimeStringInTimezone(getSecondsFromMili($this->tag_timestamp), "Y-m-d H:i:s", $stationTimeZone),
						'total_applicants'	=> count($users),
						'pick_count' => $pickCount,
						'user_list' => ""
				);
									
			}
			
			Competition::create([
				'tag_id'				=>		$this->id,
				'tag_start_timestamp'	=>		$this->tag_timestamp,
				'tag_end_timestamp'		=> 		$this->tag_timestamp + $tagDuration,
				'competition_check_timestamp'	=> getCurrentMilisecondsTimestamp(),
				'event_users_num'		=> 		count($users),
				'picked_users_num'		=> $pickCount,
				'picked_user_ids'		=> json_encode($pickedUserIdArray),
				'picked_user_phones'	=> json_encode($pickedUserPhoneNumArray)
			]);
				
				
			$this->competition_result_generated = 1;
			$this->save();
			
			// notifications
			$bccEmailList = array();
			if ($currentDayOfWeek == 6 || $currentDayOfWeek == 7) { // weekends?
				$bccEmailList = Setting::getSettingsValAsJSON('competition_notification_weekends_email_list_' . $stationId);
			} else {
				$bccEmailList = Setting::getSettingsValAsJSON('competition_notification_email_list_' . $stationId);
			}
			
			// send competition result email
			\Mail::queueOn(\Config::get('app.QueueForEmailAndSMS'), 'emails.competition', $data, function($message) use ($bccEmailList)
			{
				$message->from('connect@airshr.net', 'AirShr Connect')
								->to('opher@airshr.com.au', 'AirShr')
								//->bcc(['dollah.singh.dev@gmail.com'])
								->subject("Competition Result");
				
				if (!empty($bccEmailList) && count($bccEmailList) > 0){
					$message->cc($bccEmailList);
				}
			});
			
			// send competition result SMS
			$notificationNumbers = array();
			if ($currentDayOfWeek == 6 || $currentDayOfWeek == 7) { // weekends? 
				$notificationNumbers = Setting::getSettingsValAsJSON('competition_notification_weekends_phone_list_' . $stationId);
			} else { 
				$notificationNumbers = Setting::getSettingsValAsJSON('competition_notification_phone_list_' . $stationId);
			}
			
			if (!empty($notificationNumbers) && count($notificationNumbers) > 0) {
				
				$smsMessageContent = "Competition Result\n";
				$smsMessageContent .= $data['competitionDateTime'] . "\n";
				$smsMessageContent .= "\nTotal applicants: {$data['total_applicants']}\n";
				$smsMessageContent .= "{$data['pick_count']} Random Participants\n(Confidential)\n";
				$smsMessageContent .= "\n" . str_replace("<br/>", "\n", $data['user_list']);
				
				foreach ($notificationNumbers as $to) {
					
					/*AirShrArtisanQueue::QueueArtisanCommandToConnectQueue("airshr:sendsmsmessage", [
						'from'			=> \Config::get('app.Sinch_SMS_From'),
						'message'		=> $smsMessageContent,
						'to'			=> $to
					]);*/
					
					AirShrArtisanQueue::QueueArtisanCommand("airshr:sendsmsmessage", [
						'from'			=> \Config::get('app.Sinch_SMS_From'),
						'message'		=> $smsMessageContent,
						'to'			=> $to
					], \Config::get('app.QueueForEmailAndSMS'));
					
				}
				
			}
	
			// set is_competition flag for surrounding tags after 10 minutes
			AirShrArtisanQueue::QueueArtisanCommandWithDelay('airshr:setcompetitiontags', [], \Config::get('app.QueueForCompetition'), 800);
			
		} catch (\Exception $ex){
			
			\Log::error($ex);
			
		}
	}
	
	public static function CreateManualTag($station_id, $tagger, $who, $what, $original_who, $original_what, $adkey, $contentType, $stationName = '', $tagDuration = 0, $isManual = 1) {
		
		try {
			
			$tagTimestamp_ms = getCurrentMilisecondsTimestamp();
			$tagTimestamp = getSecondsFromMili($tagTimestamp_ms);
			
			$newTag = Tag::create([
						'tagger_id' 			=> $tagger,
						'station_id'			=> $station_id,
						'content_type_id'		=> ContentType::findContentTypeIDByName($contentType),
						'tag_timestamp'			=> $tagTimestamp_ms,
						'who'					=> $who,
						'what'					=> $what,
						'adkey'					=> $adkey,
						'is_valid'				=> 1,
						'insert_timestamp'		=> $tagTimestamp_ms,
						'insert_lag'			=> 0,
						'connect_content_id'	=> 0,
						'coverart_id'			=> 0,
						'tag_duration'			=> $tagDuration,
						'cart'					=> '',
						'original_who'			=> $original_who,
						'original_what'			=> $original_what,
						'is_manual'				=> $isManual
					]);
			
			
			$newTag->findConnectContentForTag();
			$newTag->applyForPreviousTagCompetitionGeneration();
			
			$newTag->generateTrimmedAudioForPreviousTag(); 
			
			NovaParser::storePrevTag($newTag, $stationName);
			
			$newTag->storeVoteRelatedTags();

			/*WebSocketPub::publishPushMessage(array(
				'event' 	=> 'NEWTAG',
				'tag'		=> $newTag->getArrayDataForOnAir()
			));*/
			
			WebSocketPub::publishPushMessageOnQueue(array(
				'event' 	=> 'NEWTAG',
				'tag'		=> $newTag->getArrayDataForOnAir()
			), \Config::get('app.QueueForNewTag'));
			
			return $newTag;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	
	public function findConnectContentForTag() {
		
		try {
			
			$tagTimestamp = getSecondsFromMili($this->tag_timestamp);
			
			$saveChange = false;
			
			// search for connect content
			if (($this->content_type_id == ContentType::GetPromoContentTypeID() || $this->content_type_id == ContentType::GetAdContentTypeID()) && !empty($this->adkey)) {
				// look for airshr connect content
				$connectContentObj = ConnectContent::getConnectContentForTag($this->station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $this->adkey);
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
			
			if ($this->content_type_id == ContentType::GetTrafficContentTypeID()) {
					
				$connectContentObj = ConnectContent::getConnectContentForTrafficTag($this->station_id, $tagTimestamp);
				
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
					
					//if (CoverArt::eligibleForRating($this->coverart_id, $tagTimestamp, $this->station_id)) {
						$this->is_rating = 1;
					//}
					
					$saveChange = true;
				} else {
					$this->is_rating = 1;
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
	
	public function sendEventsUpdatePushForTag() {
		$eventsForTag = $this->events;
		foreach($eventsForTag as $event) {
			$event->sendEventUpdatePushNotification();
		}
	}
	
	public static function MergeTagWithPrev($prevTagId, $newTagTimestamp, $newTagDuration) {
		
		$prevTag = Tag::find($prevTagId);
		
		if (!$prevTag) return;
		
		$prevTag->real_tag_duration = $newTagTimestamp - $prevTag->tag_timestamp;
		$prevTag->tag_duration += $newTagDuration;
		
		$prevTag->save();
	}
}
