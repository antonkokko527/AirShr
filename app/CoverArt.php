<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverArt extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_coverarts';
	
	protected $guarded = array();

	public static $COVERART_RECORD_START_ID = 21915;   // start offset for searching coverart. Can be used for flushing cache
	
	public static $LEVENSHTEIN_THRESHOLD = 2; //If greater or equal to the threshold, this will raise an exception

	const ITUNES_TYPE = 1;
	const GOOGLE_PLAY_TYPE = 2;
		
// 	public function getGoogleMusicPlayURL($writeToDB = false) {
		
// 		try {
			
// 			//if (empty($this->who) || empty($this->what)) return false;
			
// 			if (empty($this->artist) || empty($this->track)) return false;
			
// 			$searchQuery = $this->artist . ' - ' . $this->track;

// 			$results = $this->_searchGoogleMusicStore($searchQuery);

// 			if (!$results || count($results) <= 0) return false;
			
// 			if (empty($results[0])) return false;
			
// 			$this->google_music_url = $results[0];
			
// 			if ($writeToDB) {
// 				$this->save();
// 			}
			
// 			return $this->google_music_url;
			
// 		} catch (\Exception $ex) {
// 			\Log::error($ex);
// 			return false;
// 		}
// 	}

	
// 	protected function _searchGoogleMusicStore($searchQuery) {
		
// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// 		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0');
// 		curl_setopt($ch, CURLOPT_URL, 'https://play.google.com/store/search?c=music&docType=4&q=' . urlencode($searchQuery));

// 		$html = curl_exec($ch);
// 		if(strpos($html, 'We couldn\'t find anything for your search') !== FALSE) {
// 			return array();
// 		}

// 		$doc = new \DOMDocument();
// 		$doc->formatOutput = false;
// 		@$doc->loadHTML($html);
// 		$finder = new \DomXPath($doc);
		
// 		$links = array();

// 		$cardListElements = $finder->query("//*[contains(@class,'card-list')]");

// 		if (!$cardListElements->length) {
// 			return array();
// 		}

// 		foreach($cardListElements->item(0)->getElementsByTagName('div') as $div) {
// 			$xml = simplexml_load_string($doc->saveXML($div));
// 			$title = $xml->xpath("//*[contains(@class,'title')]");
// 			if(!empty($title) && isset($title[0]) && isset($title[0]->attributes()->href)) {
// 				/*$artist = $xml->xpath("//*[contains(@class,'subtitle-container')]");
// 				$price = $xml->xpath("//*[contains(@class,'price-container')]");
				
// 				if (empty($price)) continue;
				
// 				if(isset($price[0]->span[2])) {
// 					$price = (string)$price[0]->span[2];
// 				} else {
// 					$price = $price[0]->xpath("//button[contains(@class,'price')][contains(@class,'buy')]");
// 					$price = (string)$price[0]->span;
// 				}*/
		
// 				//$temp = new \stdClass();
// 				//$temp->url    = 'https://play.google.com' . $title[0]->attributes()->href;
// 				//$temp->artist = (string)$artist[0]->a;
// 				//$temp->title  = trim($title[0]);
// 				//$temp->price  = $price;
		
// 				$url = 'https://play.google.com' . $title[0]->attributes()->href;
				
// 				$links[] = $url;
// 			}
// 		}

// 		return $links;
		
// 	}
	
	public function getMoreInfoArray() {
		$result = array();
		if (empty($this->more_info)) return $result;
		$result = json_decode($this->more_info, true);
		if (!$result) return array();
		return $result;		
	}
	
	public function getCoverArtAttachmentsArray() {
		
		$results = array();
		
		// add actual coverart image
		if (!empty($this->coverart_url) || !empty($this->google_coverart_url)) {
			$sizeInfo = $this->getMoreInfoArray();
			$width = !empty($sizeInfo) && !empty($sizeInfo['width']) ? $sizeInfo['width'] : 0;
			$height = !empty($sizeInfo) && !empty($sizeInfo['height']) ? $sizeInfo['height'] : 0;
			
			$results[] = array('type' => 'image', 'url' => $this->coverart_url, 'width' => $width + 0, 'height' => $height + 0, 'display' => 'natural', 'background' => 'blur', 'google_url' => $this->google_coverart_url, 'content_attachment_id' => 0, 'not_editable' => 1);
		}
		
		
		// add additional attachments
		$additionalAttachmentIds = array();
		if (!empty($this->attachment1)) {
			$additionalAttachmentIds[] = $this->attachment1; 
		}
		if (!empty($this->attachment2)) {
			$additionalAttachmentIds[] = $this->attachment2;
		}
		if (!empty($this->attachment3)) {
			$additionalAttachmentIds[] = $this->attachment3;
		}
		
		if (count($additionalAttachmentIds) > 0) {
			try {
				$attachments = ConnectContentAttachment::whereIn('id', $additionalAttachmentIds)->get();
				foreach ($attachments as $attachment) {
					$results[] = $attachment->getJSONArrayForAttachment();
				}
			} catch(\Exception $ex) { }
		}
//		else if(!empty($this->youtube_video_id)) {
//		    $videoUrl = 'https://www.youtube.com/watch?v='. $this->youtube_video_id;
//            $resultArray = array('type' => 'video', 'url' => $videoUrl, 'content_attachment_id' => 0);
//
//            $videoInfo = getVideoURLDetails($videoUrl);
//
//            if (!empty($videoInfo)) {
//                $resultArray = array_merge($resultArray, $videoInfo);
//            }
//
//            $resultArray['display'] = 'fill';
//            $resultArray['background'] = '#000000';
//
//            $results[] = $resultArray;
//        }
		
		return $results;
	}

	public function sendEventUpdateNotificationForContent($onlyRecentOne = true) {

		$minimumTimestamp = getCurrentMilisecondsTimestamp() - 14 * 3600 * 24 * 1000;

		$tags = Tag::where('coverart_id', $this->id)->with('events');

		if ($onlyRecentOne) $tags = $tags->where('tag_timestamp', '>=', $minimumTimestamp);

		$tags = $tags->get();

		foreach($tags as $tag) {
			// send updated push notification
			$eventsForTag = $tag->events;
			foreach($eventsForTag as $event) {
				$event->sendEventUpdatePushNotification();
			}
		}
	}

	public function updateWhoAndWhatForTagsAndEvents($onlyRecentOne = true) {

		try {

			if ($onlyRecentOne) {

				$minimumTimestamp = getCurrentMilisecondsTimestamp() - 21 * 3600 * 24 * 1000;

				\DB::table('airshr_tags')->where('coverart_id', $this->id)
					->where('tag_timestamp', '>=', $minimumTimestamp)
					->update(['who' => $this->artist, 'what' => $this->track]);

				\DB::table('airshr_preview_tags')->where('coverart_id', $this->id)
					->where('tag_timestamp', '>=', $minimumTimestamp)
					->update(['who' => $this->artist, 'what' => $this->track]);
			} else {
				\DB::table('airshr_tags')->where('coverart_id', $this->id)
					->update(['who' => $this->artist, 'what' => $this->track]);

				\DB::table('airshr_preview_tags')->where('coverart_id', $this->id)
					->update(['who' => $this->artist, 'what' => $this->track]);
			}

			$this->sendEventUpdateNotificationForContent();

		} catch (\Exception $ex) {
			\Log::error($ex);
		}

	}
// 	public function getGoogleMusicCoverArt($writeToDB = false) {
		
// 		try {
			
// 			$resultInfo = array();
			
// 			if (empty($this->artist) || empty($this->track)) return false;
			
// 			$searchQuery = $this->artist . ' - ' . $this->track;
						
// 			$ch = curl_init();
// 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// 			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0');
// 			curl_setopt($ch, CURLOPT_URL, 'https://play.google.com/store/search?c=music&docType=4&q=' . urlencode($searchQuery));
	
// 			$html = curl_exec($ch);
// 			if(strpos($html, 'We couldn\'t find anything for your search') !== FALSE) {
// 				return false;
// 			}
	
// 			$doc = new \DOMDocument();
// 			$doc->formatOutput = false;
// 			@$doc->loadHTML($html);
// 			$finder = new \DomXPath($doc);
	
// 			$cardListElements = $finder->query("//*[contains(@class,'card-list')]");
	
// 			if (!$cardListElements->length) {
// 				return false;
// 			}
	
			
// 			$div = $cardListElements->item(0)->getElementsByTagName('div')->item(0);
// 			$xml = simplexml_load_string($doc->saveXML($div));
			
// 			$title = $xml->xpath("//*[contains(@class,'title')]");
// 			if(!empty($title) && isset($title[0]) && isset($title[0]->attributes()->href)) {
// 				$this->google_music_url = 'https://play.google.com' . $title[0]->attributes()->href;
// 				$resultInfo['google_music_url'] = $this->google_music_url;
// 			}
			
// 			$img_attrs = array();
// 			$img = null;
	
// 			if($xml->xpath("//img[contains(@class, 'cover-image')]")) {
// 				$img_attrs = $xml->xpath("//img[contains(@class, 'cover-image')]")[0]->attributes();
// 			}
// 			foreach($img_attrs as $key => $value) {
// 				if($key == 'data-cover-large') {
// 					$img = $value;
// 				}
// 			}
	
// 			if($img) {
// 				$this->google_coverart_url = substr($img, 0, -3) . '600';
// 				$resultInfo['google_coverart_url'] = $this->google_coverart_url;
// 			}
	
// 			//Getting song preview button
// 			$song_link = 'https://play.google.com'. $xml->xpath("//a[contains(@class, 'card-click-target')]")[0]->attributes()['href'];	
// 			$data_track_docid= substr($song_link, strpos($song_link, 'song'));
			
// 			if (!empty($data_track_docid)) {
// 				$this->google_music_song_id = $data_track_docid;
// 				$resultInfo['google_music_song_id'] = $this->google_music_song_id;
// 			}
			
// 			if ($writeToDB) {	
// 				$this->save();
// 			}
	
// 			return $resultInfo;
		
// 		} catch (\Exception $ex) {
// 			\Log::error($ex);
// 			return false;
// 		}
// 	}
	
	/*
	 * Call Battle FM service to get cover art or return existing cover art information
	*/
// 	public static function getCoverArtInfo2($who, $what) {
	
// 		// for pruning cache
// 		$initialID = self::$COVERART_RECORD_START_ID;
		
// 		$coverArtObj = null;
	
// 		try {
				
// 			$coverArtObj = CoverArt::where('who', '=', $who)
// 									->where('what', '=', $what)
// 									->where('id', '>', $initialID)
// 									->orderBy('id', 'DESC')
// 									->firstOrFail();
				
// 		} catch (\Exception $ex) {}
	
// 		if ($coverArtObj) return $coverArtObj->toArray();
	
// 		$result = array('coverart_url' => '', 'itunes_url' => '', 'artist' => '', 'track' => '', 'preview' => '', 'lyrics' => '', 'who' => $who, 'what' => $what, 'google_music_url' => '', 'google_coverart_url' => '', 'google_music_song_id' => '');
	
// 		$dataFetched = false;

// 		try {
// 			$response = \Httpful\Request::get(\Config::get("app.CoverArtInfoBaseURL") . "?title=" . rawurlencode($what) . "&artist=" . rawurlencode($who))->send();
	
// 			if ($response->code == 200){
// 				$result_json = json_decode($response->body);
	
// 				$dataFetched = true;
	
// 				$result['coverart_url'] = $result_json->cover;
// 				$result['itunes_url'] = $result_json->url;
	
// 				if (!empty($result_json->artist))
// 					$result['artist'] = $result_json->artist;
	
// 				if (!empty($result_json->track))
// 					$result['track'] = $result_json->track;
	
// 				if (!empty($result_json->preview))
// 					$result['preview'] = $result_json->preview;
	
// 				if (!empty($result_json->lyrics))
// 					$result['lyrics'] = $result_json->lyrics;
// 			}
	
// 		} catch (\Exception $ex) {
// 			\Log::error($ex);
// 		}
	
	
// 		if (!$dataFetched) return null;

// 		//If there already exists a song in the coverarts table that points to the same itunes_url and is the
// 		//same track as returned by Gordon's coverart service, then we know it's the same song, so return that
// 		//rather than creating a new coverart.
// 		try {

// 			$coverArtObj = Coverart::where('id', '>', $initialID)
// 				->where('track', '=', $result['track'])
// 				->where('artist', '=', $result['artist'])
// 				->orderBy('id', 'DESC')
// 				->firstOrFail();

// 		} catch (\Exception $ex) { }

// 		if ($coverArtObj) return $coverArtObj->toArray();

// 		$coverArtObj = null;
		
// 		try {

// 			if (!empty($result['coverart_url'])) {
// 				$sizeInfo = getimagesize($result['coverart_url']);
// 				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
// 				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
// 				$result['more_info'] = json_encode(['width' => $width, 'height' => $height]);
// 			}
			
// 			$coverArtObj = CoverArt::create($result);
// 			$result['id'] = $coverArtObj->id;
				
// 		} catch (\Exception $ex) {
// 			\Log::error($ex);
// 			return null;
// 		}
		
// 		if (empty($coverArtObj)) return null;
		
// 		try {
		
// 			/*$googleMusicURL = $coverArtObj->getGoogleMusicPlayURL(false);
// 			if ($googleMusicURL !== false) {
// 				$result['google_music_url'] = $googleMusicURL;
// 			}*/
			
// 			$info = $coverArtObj->getGoogleMusicCoverArt(false);
			
// 			if($info !== false) {
// 				if (!empty($info['google_coverart_url'])) $result['google_coverart_url'] = $info['google_coverart_url']; 
// 				if (!empty($info['google_music_song_id'])) $result['google_music_song_id'] = $info['google_music_song_id'];
// 				if (!empty($info['google_music_url'])) $result['google_music_url'] = $info['google_music_url'];
// 			}
			
// 			$coverArtObj->save();
			
// 		} catch (\Exception $ex) {
			
// 		}
		
// 		return $result;
// 	}
//
//	//Get either the most played song or the last played song with the who and what on a station
//	public static function getLatestPlayedSong($who, $what, $station_id = 0) {
//		
//		// for pruning cache
//		$initialID = self::$COVERART_RECORD_START_ID;
//
//		$coverArtObj = null;
//
//		try {
//			$songs = \DB::table('airshr_coverarts')
//				->where('station_id', '=', $station_id)
//				->where('artist', 'LIKE', "%{$who}%")
//				->where('track', 'LIKE', "%{$what}%")
//				->where('airshr_coverarts.id', '>', $initialID)
//				->leftJoin('airshr_tags', 'airshr_tags.coverart_id', '=', 'airshr_coverarts.id')
//				->groupBy('airshr_coverarts.id')
//				->orderBy('airshr_tags.tag_timestamp', 'DESC')
//				->get();
//
//			$coverArtObj = CoverArt::find($songs[0]->coverart_id);
//
//
//		} catch (\Exception $ex) {
//		}
//
//		if ($coverArtObj) return $coverArtObj;
//
//		$result = array('coverart_url' => '', 'itunes_url' => '', 'artist' => '', 'track' => '', 'preview' => '', 'lyrics' => '', 'who' => $who, 'what' => $what, 'google_music_url' => '', 'google_coverart_url' => '', 'google_music_song_id' => '');
//
//		$dataFetched = false;
//
//		try {
//			$response = \Httpful\Request::get(\Config::get("app.CoverArtInfoBaseURL") . "?title=" . rawurlencode($what) . "&artist=" . rawurlencode($who))->send();
//
//			if ($response->code == 200){
//				$result_json = json_decode($response->body);
//
//				$dataFetched = true;
//
//				$result['coverart_url'] = $result_json->cover;
//				$result['itunes_url'] = $result_json->url;
//
//				if (!empty($result_json->artist))
//					$result['artist'] = $result_json->artist;
//
//				if (!empty($result_json->track))
//					$result['track'] = $result_json->track;
//
//				if (!empty($result_json->preview))
//					$result['preview'] = $result_json->preview;
//
//				if (!empty($result_json->lyrics))
//					$result['lyrics'] = $result_json->lyrics;
//			}
//
//		} catch (\Exception $ex) {
//			\Log::error($ex);
//		}
//
//
//		if (!$dataFetched) return null;
//
//		//If there already exists a song in the coverarts table that points to the same itunes_url and is the
//		//same track as returned by Gordon's coverart service, then we know it's the same song, so return that
//		//rather than creating a new coverart.
//		try {
//
//			$songs = \DB::table('airshr_coverarts')
//				->where('station_id', '=', $station_id)
//				->where('airshr_coverarts.id', '>', $initialID)
//				->where('airshr_coverarts.itunes_url', '=', $result['itunes_url'])
//				->where('airshr_coverarts.track', '=', $result['track'])
//				->where('airshr_coverarts.artist', '=', $result['artist'])
//				->leftJoin('airshr_tags', 'airshr_tags.coverart_id', '=', 'airshr_coverarts.id')
//				->groupBy('airshr_coverarts.id')
//				->orderBy('airshr_tags.tag_timestamp', 'DESC')
//				->get();
//
//			$coverArtObj = CoverArt::find($songs[0]->coverart_id);
//
//		} catch (\Exception $ex) { }
//
//		if ($coverArtObj) return $coverArtObj;
//
//		$coverArtObj = null;
//
//
//
//		try {
//
//			if (!empty($result['coverart_url'])) {
//				$sizeInfo = getimagesize($result['coverart_url']);
//				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
//				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
//				$result['more_info'] = json_encode(['width' => $width, 'height' => $height]);
//			}
//
//			$coverArtObj = CoverArt::create($result);
//			$result['id'] = $coverArtObj->id;
//
//		} catch (\Exception $ex) {
//			\Log::error($ex);
//			return null;
//		}
//
//		if (empty($coverArtObj)) return null;
//
//		try {
//
//			/*$googleMusicURL = $coverArtObj->getGoogleMusicPlayURL(false);
//			if ($googleMusicURL !== false) {
//				$result['google_music_url'] = $googleMusicURL;
//			}*/
//
//			$info = $coverArtObj->getGoogleMusicCoverArt(false);
//
//			if($info !== false) {
//				if (!empty($info['google_coverart_url'])) $result['google_coverart_url'] = $info['google_coverart_url'];
//				if (!empty($info['google_music_song_id'])) $result['google_music_song_id'] = $info['google_music_song_id'];
//				if (!empty($info['google_music_url'])) $result['google_music_url'] = $info['google_music_url'];
//			}
//
//			$coverArtObj->save();
//
//		} catch (\Exception $ex) {
//
//		}
//
//		return $coverArtObj;
//	}

	public function clearCoverArt($type) {
		
		if($type == CoverArt::ITUNES_TYPE) {
			$result = [
				'itunes_ready' => 0
			];

			$this->update($result);

		} else if($type == CoverArt::GOOGLE_PLAY_TYPE) {
			$result = [
				'google_ready' => 0
			];

			$this->update($result);
		}
		
	}

	public static function searchAssetService($who, $what) {

		$dataFetched = false;

		$result = [
			'coverart_url' => '',
			'itunes_url' => '',
			'artist' => '',
			'track' => '',
			'preview' => '',
			'lyrics' => '',
			'google_music_url' => '',
			'google_coverart_url' => '',
			'google_music_song_id' => '',
			'google_ready' => 1,
			'itunes_ready' => 1,
            'itunes_artist' => '',
            'itunes_title' => ''
		];

		try {

			$coverArtURL = sprintf(\Config::get("app.AirShrCoverArtInternalURL"), rawurlencode($who), rawurlencode($what));

			$response = \Httpful\Request::get($coverArtURL)->send();

			if ($response->code == 200){

				$result_json = json_decode($response->raw_body);

				if ($result_json && $result_json->status == 'ok' && $result_json->result) {

					$dataFetched = true;

					$result_json = $result_json->result;

					if (!empty($result_json->iTunesCoverArtUrl))
						$result['coverart_url'] = $result_json->iTunesCoverArtUrl;

					if (!empty($result_json->iTunesUrl))
						$result['itunes_url'] = $result_json->iTunesUrl;

					if (!empty($result_json->artist))
						$result['artist'] = $result_json->artist;

					if (!empty($result_json->title))
						$result['track'] = $result_json->title;

					if (!empty($result_json->previewUrl))
						$result['preview'] = $result_json->previewUrl;

					if (!empty($result_json->lyrics)) {
                        $result['lyrics'] = $result_json->lyrics;
                        $result['lyricfind_available'] = 1;
                    } else {
                        $result['lyricfind_available'] = 0;
                    }

					if (!empty($result_json->googlePlayArtist))
						$result['google_artist'] = $result_json->googlePlayArtist;

					if (!empty($result_json->googlePlayTitle))
						$result['google_title'] = $result_json->googlePlayTitle;

					if (!empty($result_json->googlePlayUrl))
						$result['google_music_url'] = $result_json->googlePlayUrl;

					if (!empty($result_json->googlePlayCoverArtUrl))
						$result['google_coverart_url'] = $result_json->googlePlayCoverArtUrl;

					if (!empty($result_json->googlePlaySongId))
						$result['google_music_song_id'] = $result_json->googlePlaySongId;

                    if (!empty($result_json->iTunesArtist))
                        $result['itunes_artist'] = $result_json->iTunesArtist;

                    if (!empty($result_json->iTunesTitle))
                        $result['itunes_title'] = $result_json->iTunesTitle;

					if (!empty($result_json->id))
						$result['asset_id'] = $result_json->id;

					if (!empty($result_json->meta))
						$result['meta'] = $result_json->meta;
					
					$hasGoogleContent =  !empty($result['google_music_url']);
					
					if (!empty($result_json->googlePlayArtistDistance))
						$result['google_ready'] = ($result_json->googlePlayArtistDistance < self::$LEVENSHTEIN_THRESHOLD && $hasGoogleContent) ? 1 : 0;

					if (!empty($result_json->googlePlayTitleDistance)) 
						$result['google_ready'] = ($result_json->googlePlayTitleDistance < self::$LEVENSHTEIN_THRESHOLD && $hasGoogleContent) ? $result['google_ready'] : 0;

					$result['google_available'] = $hasGoogleContent ? 1 : 0;

					$hasITunesContent = !empty($result['itunes_url']);
					
					if (!empty($result_json->iTunesArtistDistance))
						$result['itunes_ready'] = ($result_json->iTunesArtistDistance < self::$LEVENSHTEIN_THRESHOLD && $hasITunesContent) ? 1 : 0;

					if (!empty($result_json->iTunesTitleDistance))
						$result['itunes_ready'] = ($result_json->iTunesTitleDistance < self::$LEVENSHTEIN_THRESHOLD && $hasITunesContent) ? $result['itunes_ready'] : 0;

					$result['itunes_available'] = $hasITunesContent ? 1 : 0;

                    $result['youtube_video_id'] = $result_json->youTubeVideoId;

                    $result['youtube_title'] = $result_json->youTubeTitle;
				}
			}

		} catch (\Exception $ex) {
			\Log::error($ex);
		}

		if(!$dataFetched) return null;

		return $result;
	}

	public function updateCoverArtInfo($who, $what) {
		
		$result = $this->searchAssetService($who, $what);

		if(!$result) return;

		try {

			if (!empty($result['coverart_url'])) {
				$sizeInfo = getimagesize($result['coverart_url']);
				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
				$result['more_info'] = json_encode(['width' => $width, 'height' => $height]);
			}

			//Extract meta and asset_id from result and unset them
			$meta = $result['meta'];
			$assetId = $result['asset_id'];

			unset($result['meta']);

			$this->update($result);

			if(!empty($assetId)) {

				$coverArtUpdateURL = sprintf(\Config::get("app.AirShrCoverArtUpdateInternalURL"), $assetId);

				$aliasExists = false;
				foreach($meta->aliases as $alias) {
					if($alias->artist == $this->who && $alias->title == $this->what) $aliasExists = true;
				}
				
				if(!$aliasExists) $meta->aliases[] = ['artist' => $this->who, 'title' => $this->what];

				$attributes = ['meta' => $meta];

				$response = \Httpful\Request::put($coverArtUpdateURL)
					->sends('application/json')
					->body(json_encode($attributes))
					->send();

			}

		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}

	}

	public static function eligibleForRating($coverartId, $tagTimestamp, $stationId) {
		
		try {
			$station = Station::findOrFail($stationId);
			$stationTimeZone = $station->getStationTimezone();
			$tagDate = getDateTimeStringInTimezone($tagTimestamp, "Y-m-d", $stationTimeZone);
			
			$coverArtRating =  CoverArtRating::where('station_id', $stationId)
											->where('start_date', '<=', $tagDate)
											->where('end_date', '>=', $tagDate)
											->where('coverart_id', '=', $coverartId)
											->count();
			if ($coverArtRating > 0) return true;
			return false;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}

	/**
	 * Get cover art information based on who and what
	 * 
	 * @param string $who
	 * @param string $what
	 * @return object|null 
	 */
	public static function getCoverArtInfo($who, $what, $originalWho = '', $originalWhat = '') {
	
		// for pruning cache
		$initialID = self::$COVERART_RECORD_START_ID;
		
		$coverArtObj = null;

		$assetId = 0;

		$meta = '';

		//Skip empty artist to avoid searching asset service when artist is null or unknown (usually a beat mix on nova)
		if(empty($who) || strtolower($who) == 'various artists' || strtolower($who) == 'unknown artist') {
			return null;
		}
		
		if(empty($originalWho)) {
			$originalWho = $who;
		}
		
		if(empty($originalWhat)) {
			$originalWhat = $what;
		}

		$result = self::searchAssetService($who, $what);

		$result['who'] = $originalWho;
		$result['what'] = $originalWhat;

		if (!$result) return null;
	
		//If there already exists a song in the coverarts table that is the
		//same track as returned by Asset Service, then we know it's the same song, so return that
		//rather than creating a new coverart.
		try {

			$coverArtObj = Coverart::where('id', '>', $initialID)
									->where('track', '=', $result['track'])
									->where('artist', '=', $result['artist'])
									->orderBy('id', 'DESC')
									->firstOrFail();

		} catch (\Exception $ex) { }

		if ($coverArtObj) return $coverArtObj->toArray();
	
		$coverArtObj = null;
	
		try {
	
			if (!empty($result['coverart_url'])) {
				$sizeInfo = getimagesize($result['coverart_url']);
				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
				$result['more_info'] = json_encode(['width' => $width, 'height' => $height]);
			}

			//Extract meta and asset_id from result and unset them
			$meta = $result['meta'];
			$assetId = $result['asset_id'];

			unset($result['meta']);

			//Add the result to the coverart table
			$coverArtObj = CoverArt::create($result);
			$result['id'] = $coverArtObj->id;

			//Update aliases with the original who and what
			if(!empty($assetId)) {

				$coverArtUpdateURL = sprintf(\Config::get("app.AirShrCoverArtUpdateInternalURL"), $assetId);

				$aliasExists = false;
				foreach($meta->aliases as $alias) {
					if($alias->artist == $originalWho && $alias->title == $originalWhat) $aliasExists = true;
				}

				if(!$aliasExists) $meta->aliases[] = ['artist' => $originalWho, 'title' => $originalWhat];

				$attributes = ['meta' => $meta];

				$response = \Httpful\Request::put($coverArtUpdateURL)
					->sends('application/json')
					->body(json_encode($attributes))
					->send();

			}
	
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	
		if (empty($coverArtObj)) return null;
			
		return $result;
	}
}
