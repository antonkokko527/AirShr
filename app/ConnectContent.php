<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\ConnectContentAction;

class ConnectContent extends Model {

	use SoftDeletes;
	
	public static $STATION_DEFAULT_CONTENTS = array();
	
	public static $COMPETITION_CHECKTIME_AFTER_ENDOFTAG = 30;
	public static $COMPETITION_WINNER_COUNT	= 15;
		
	public static $AD_DURATION_LIST = array(
			10 	=> '10s',
			15	=> '15s',
			20	=> '20s',
			30 	=> '30s',
			45	=> '45s',
			60	=> '60s'
	);
	
	public static $AD_PERCENT_LIST = array(
			5	=> '5%',
			10	=> '10%',	
			15	=> '15%',
			20	=> '20%',
			25	=> '25%',
			30	=> '30%',
			33	=> '33% (1/3)',
			35	=> '35%',
			40	=> '40%',
			45	=> '45%',
			50	=> '50%',
			55	=> '55%',
			60	=> '60%',
			65	=> '65%',
			66	=> '66% (2/3)',
			70	=> '70%',
			75	=> '75%',
			80	=> '80%',
			85	=> '85%',
			90	=> '90%',
			95	=> '95%',
			100	=> '100%',
			200	=> 'Even'
	);
	
	public static $CONTENT_REC_TYPE_LIST = array(
			'rec'	=> 'Rec',
			'live'	=> 'Live',
			'sim_live'	=> 'SimLive'	
	);
	
	public static $MATERIAL_INSTRUCTION_VERSION_LIST = array(

			'new'							=> 'New',
			'add_rotation'					=> 'Add to Rotation',
			'extend_existing'				=> 'Extend Existing',
			'replace_existing'				=> 'Replace Existing',
			'repeat_material'				=> 'Repeat Material',
			'amended_details'				=> 'Amended Details',
			
	);
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_contents';
	
	protected $casts = [
	    'is_vote' => 'integer',
		'is_ready' => 'integer'
	];

	protected $guarded = array();
	
	public function contentDates() {
		return $this->hasMany('App\ConnectContentDate', 'content_id', 'id');
	}
	
	public function getContentWeekDaysArray() {
		return array(
			0		=> $this->content_weekday_0,
			1		=> $this->content_weekday_1,
			2		=> $this->content_weekday_2,
			3		=> $this->content_weekday_3,
			4		=> $this->content_weekday_4,
			5		=> $this->content_weekday_5,
			6		=> $this->content_weekday_6
		);
	}
	
	public function getContentDatesArray() {
		
		$resultsArray = array();
		
		foreach ($this->contentDates as $contentDate) {
			$start_date = strtotime($contentDate->start_date);
			$end_date = strtotime($contentDate->end_date);
			
			$resultsArray[] = array(
				'start_date' => $start_date === FALSE ? '' : date("d-m-Y", $start_date),
				'end_date' => $end_date === FALSE ? '' : date("d-m-Y", $end_date),
				'date_id' => $contentDate->id
			);
		}
		
		return $resultsArray;
	}
	
	public function saveContentDatesArray($contentDates) {
		foreach ($contentDates as $contentDate) {
			if (empty($contentDate['date_id']) && empty($contentDate['start_date']) && empty($contentDate['end_date'])) continue;
			$this->addContentDate($contentDate['date_id'], parseDateToMySqlFormat($contentDate['start_date']), parseDateToMySqlFormat($contentDate['end_date']));
		}		
	}
	
	public function getContentDate($contentDateId = 0) {
		try {
			if ($contentDateId) {
				$contentDate = ConnectContentDate::findOrFail($contentDateId);
				return $contentDate;	
			} else {
				$contentDates = $this->contentDates;
				foreach($contentDates as $contentDate) {
					return $contentDate;
				}
				return null;
			}
		} catch (\Exception $ex) {
			return null;
		}
	}
	
	public function getContentDateByDateRange($start_date, $end_date) {
		foreach ($this->contentDates as $contentDate) {
			if ($contentDate->start_date . '' == $start_date . '' && $contentDate->end_date . '' == $end_date) {
				return $contentDate;
			}
		}
		return null;
	}
	
	public function attachments() {
		return $this->hasMany('App\ConnectContentAttachment', 'content_id', 'id');
	}
	
	public function actionDetail() {
		return $this->belongsTo('App\ConnectContentAction', 'action_id');
	}
	
	public function subContents() { 
		return $this->belongsToMany('App\ConnectContent', 'airshr_connect_content_belongs', 'parent_content_id', 'child_content_id');	
	}
	
	public function getSubContents() {
		$subContents = $this->subContents;
		
		if ($subContents && $subContents->count() > 0) {
			// get dates
			$subContentsDate = array();
			
			$dateRows = \DB::table('airshr_connect_content_belongs')->where('parent_content_id', $this->id)
								->leftJoin('airshr_connect_content_dates', 'airshr_connect_content_dates.id', '=', 'airshr_connect_content_belongs.child_content_date_id')
								->get();
			
			foreach ($dateRows as $dateRow) {
				if (!isset($subContentsDate[$dateRow->child_content_id])) $subContentsDate[$dateRow->child_content_id] = array();
				$subContentsDate[$dateRow->child_content_id][] = $dateRow;
			}
			
			for ($i = 0; $i < $subContents->count(); $i++) {
				$subContent = $subContents[$i];
				if (isset($subContentsDate[$subContent->id]) && count($subContentsDate[$subContent->id]) > 0) {
					$dateRow = array_shift($subContentsDate[$subContent->id]);
					$subContents[$i]->start_date = $dateRow->start_date;
					$subContents[$i]->end_date = $dateRow->end_date;
					$subContents[$i]->content_sync =  $dateRow->content_sync;
					$subContents[$i]->child_content_date_id = $dateRow->child_content_date_id;
				}
			}
		}
		return $subContents;
	}
	
	public function parentContents() {
		return $this->belongsToMany('App\ConnectContent', 'airshr_connect_content_belongs', 'child_content_id', 'parent_content_id');
	}
	
	public function tagsForContent() {
		return $this->hasMany('App\Tag', 'connect_content_id', 'id');
	}
	
	public function tagIDListForContent() {
		$results = array();
		foreach($this->tagsForContent as $tag) {
			$results[] = $tag->id;	
		}
		return $results;
	}
	
	public function previewTagsForContent() {
		return $this->hasMany('App\PreviewTag', 'connect_content_id', 'id');
	}
	
	public function station() {
		return $this->belongsTo('App\Station', 'station_id');
	}
	
	public function executive() {
		return $this->hasOne('App\ConnectContentExecutive', 'id', 'content_manager_user_id');
	}
	
	public function sendEventUpdateNotificationForContent($onlyRecentOne = true) {
		
		$minimumTimestamp = getCurrentMilisecondsTimestamp() - 7 * 3600 * 24 * 1000;
		
		$tags = Tag::where('connect_content_id', $this->id)->with('events');
		
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
	
	public function sendCompetitonResultGenerationRequest() {
		
		if (!$this->is_competition || !$this->is_ready || !$this->isContentTalkBreak()) return; // only for competition live content
		$tags = $this->tagsForContent;
		foreach($tags as $tag) {
			$tag->applyForCompetitionGeneration();
		}
	}
	
	public function sendVoteResultGenerationRequest() {
		if (!$this->is_vote || !$this->is_ready || !$this->isContentTalkBreak()) return; // only for vote live content
		$tags = $this->tagsForContent;
		foreach($tags as $tag) {
			$tag->applyForVoteGeneration();
		}
	}
	
	public function updateWhoAndWhatForTagsAndEvents($onlyRecentOne = true) {
		
		try {
			
			if ($onlyRecentOne) {
				
				$minimumTimestamp = getCurrentMilisecondsTimestamp() - 7 * 3600 * 24 * 1000;
				
				\DB::table('airshr_tags')->where('connect_content_id', $this->id)
									->where('tag_timestamp', '>=', $minimumTimestamp)
									->update(['who' => $this->who, 'what' => $this->what]);
			
				\DB::table('airshr_preview_tags')->where('connect_content_id', $this->id)
									->where('tag_timestamp', '>=', $minimumTimestamp)
									->update(['who' => $this->who, 'what' => $this->what, 'is_client_found' => true]);
			} else {
				\DB::table('airshr_tags')->where('connect_content_id', $this->id)
									->update(['who' => $this->who, 'what' => $this->what]);
					
				\DB::table('airshr_preview_tags')->where('connect_content_id', $this->id)
									->update(['who' => $this->who, 'what' => $this->what]);
			}
			
			$this->sendEventUpdateNotificationForContent();
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
	}
	
	public function getAudioAttachment() {
		$attachments = $this->attachments;
		
		$audioAttachment = null;
		
		foreach($attachments as $attachment) {
			if ($attachment->type == 'audio') {
				$audioAttachment = $attachment;
				break;
			}
		}
		
		return $audioAttachment;
	}
	
	public function getLogoAttachment() {
		$attachments = $this->attachments;
	
		$logoAttachment = null;
	
		foreach($attachments as $attachment) {
			if ($attachment->type == 'logo') {
				$logoAttachment = $attachment;
				break;
			}
		}
	
		return $logoAttachment;
	}
	
	public function getExtraAttachments() {
		$otherAttachments = array();
		
		$attachments = $this->attachments;
	
		foreach($attachments as $attachment) {
			//if ($attachment->type == 'logo' || $attachment->type == 'audio') continue;
			if ($attachment->type == 'audio') continue;
			$otherAttachments[] = $attachment;
		}
	
		return $otherAttachments;
	}
	
	public function getArrayDataForApp() {
		
		$result = array();
		
		$result['ad_length'] = $this->ad_length + 0;
        $result['ad_key'] = $this->ad_key;
		$result['is_competition'] = $this->is_competition;
		$result['is_vote'] = $this->is_vote;
		
		if ($this->is_vote) {
			$result['vote_question'] = $this->vote_question;
			$result['vote_option_1'] = $this->vote_option_1;
			$result['vote_option_2'] = $this->vote_option_2;
			$result['vote_duration_minutes'] = $this->vote_duration_minutes;
		}
		
		/*$result['map_included'] = $this->map_included;
		$result['map_address1'] = $this->map_address1;
		$result['map_address2'] = $this->map_address2;*/
		
		// add action info
		$connectContentAction = $this->actionDetail;
		if ($connectContentAction != null) {
			$result['action'] = $connectContentAction->getJSONArray();
			$result['action_params'] = empty($this->action_params) ? array() : json_decode(refactorActionParams($this->action_params), true);
		}
		
		$attachments = $this->getExtraAttachments();
	
		$attachmentsArray = array();
		
		foreach ($attachments as $attachment) {
			$attachmentsArray[] = $attachment->getJSONArrayForAttachment();
		}
		
		$result['attachments'] = $attachmentsArray;
		
		// location info
		if (!empty($this->map_address1) && !empty($this->map_address1_lat) && !empty($this->map_address1_lng)) {
			$result['map'] = array(
				'address'			=> $this->map_address1,
				'lat'				=> $this->map_address1_lat + 0,
				'lng'				=> $this->map_address1_lng + 0	
			);
		}
		
		return $result;
		
	}
	
	public function updateContentToTagsLinkAssociation($association) {
		
		try {
			$this->removeContentToTagsLink(false);
			
			$assocDate = date("Y-m-d");
			ConnectContent2Preview::where('assoc_date', $assocDate)
								  ->where('connect_content_id', $this->id)
								  ->delete();
			
			$pastTagIdArray = array();
			
			$currentTag = Tag::getCurrentTag($this->station_id);
			
			foreach ($association as $item) {
				if ($item['assoc_type'] == 'current' || $item['assoc_type'] == 'past') {
					$pastTagIdArray[] = $item['assoc_id'];
				} else if ($item['assoc_type'] == 'prev') {
					
					if ($currentTag && $item['assoc_timestamp']) {
					
						$talkTagCount = PreviewTag::getTalkCountBetweenTimestamp($this->station_id, $currentTag->tag_timestamp, $item['assoc_timestamp']);
						
						ConnectContent2Preview::create([
							'assoc_date'			=> $assocDate,
							'preview_tag_id'		=> $item['assoc_id'],
							'preview_tag_timestamp'	=> $item['assoc_timestamp'],
							'current_tag_timestamp'	=> $currentTag->tag_timestamp,
							'position'				=> $talkTagCount,
							'connect_content_id'	=> $this->id
						]);

					}
				}
			}
			
			if (count($pastTagIdArray) > 0) {
				\DB::table('airshr_tags')->whereIn('id', $pastTagIdArray)->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
				
				// set who and what of tags - event update notification
				$this->sendEventUpdateNotificationForContent();
			}
			
		} catch (\Exception $ex) {
			
		}
		
	}
	
	public function removeContentToTagsLink($sendUpdatePush = true, $onlyRecentOne = true) {
		
		$minimumTimestamp = getCurrentMilisecondsTimestamp() - 7 * 3600 * 24 * 1000;
		
		if ($onlyRecentOne) {
			
			// Reset who and what of tags and preview tags
			\DB::table('airshr_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->where('tag_timestamp', '>=', $minimumTimestamp)->update(array('who' => \DB::raw('original_who'), 'what' => \DB::raw('original_what')));
			\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->where('tag_timestamp', '>=', $minimumTimestamp)->update(array('who' => \DB::raw('original_who'), 'what' => \DB::raw('original_what')));

			if ($sendUpdatePush) $this->sendEventUpdateNotificationForContent();
			
			// Remove previous tag -> connect content links
			\DB::table('airshr_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->where('tag_timestamp', '>=', $minimumTimestamp)->update(['connect_content_id' => 0]);
			\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->where('tag_timestamp', '>=', $minimumTimestamp)->update(['connect_content_id' => 0]);
			
		} else {
			
			// Reset who and what of tags and preview tags
			\DB::table('airshr_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(array('who' => \DB::raw('original_who'), 'what' => \DB::raw('original_what')));
			\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(array('who' => \DB::raw('original_who'), 'what' => \DB::raw('original_what')));
			
			if ($sendUpdatePush) $this->sendEventUpdateNotificationForContent();
				
			// Remove previous tag -> connect content links
			\DB::table('airshr_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(['connect_content_id' => 0]);
			\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(['connect_content_id' => 0]);	
		}
		
	}
	
	public function updateContentToTagsLinkStatic() {
		
		try {
		
			if (!$this->is_temp) {				// ignore temporary contents
		
				$tagIDList = $this->tagIDListForContent();
				
				if (count($tagIDList) > 0) {
					
					\DB::table('airshr_tags')->where('station_id', $this->station_id)
											 ->whereIn('id', $tagIDList)
											 ->where('connect_content_id', $this->id)
											 ->update(['who' => $this->who, 'what' => $this->what, 'content_type_id' => $this->content_type_id, 'adkey' => $this->ad_key]);
				
					$this->sendEventUpdateNotificationForContent();
				}
			}
				
		} catch (\Exception $ex) {}
		
	}
	
	public function updateContentToTagsLink($removePrevLink = true) {
		
		try {

			$minimumTimestamp = getCurrentMilisecondsTimestamp() - 7 * 3600 * 24 * 1000;
			
			if (!$this->is_temp) {				// ignore temporary contents
				
				if ($this->content_type_id == ContentType::findContentTypeIDByName('Ad')) {				// only care for ad content
					// propagate change in connect content to tags & preview tags
					if ($removePrevLink) {
						$this->removeContentToTagsLink(false);
					}
					
					if (!empty($this->ad_key)) {
						
						//$contentDates = $this->contentDates;
						
						/*foreach($contentDates as $contentDate) {
							
							$startTimestamp = getStartTimestampOfDay($contentDate->start_date);
							$endTimestamp = getEndTimestampOfDay($contentDate->end_date);*/

							//update tags with new - Ad
							\DB::table('airshr_tags')->where('station_id', $this->station_id)
													->where('content_type_id', ContentType::findContentTypeIDByName('Ad'))
													->where('adkey', $this->ad_key)
													->where('tag_timestamp', '>=', $minimumTimestamp)
													/*->where('tag_timestamp', '>=', $startTimestamp)
													->where('tag_timestamp', '<=', $endTimestamp) */
													->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
								
							$adKeyWithoutLastCharacter = substr($this->ad_key, 0, strlen($this->ad_key) - 1);
							if ($adKeyWithoutLastCharacter != '') {
								//update tags with new - Promotion
								\DB::table('airshr_tags')->where('station_id', $this->station_id)
														->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
														->whereRaw('cart IS NOT NULL')
														->where('cart', '<>', '')
														->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cart, ' ', ''), '/', ''), '-', ''), '_', '') = '" . $adKeyWithoutLastCharacter . "'")
														->where('tag_timestamp', '>=', $minimumTimestamp)
														/*->where('tag_timestamp', '>=', $startTimestamp)
														->where('tag_timestamp', '<=', $endTimestamp)*/
														->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
							}
							
							\DB::table('airshr_tags')->where('station_id', $this->station_id)
														->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
														->where('adkey', $this->ad_key)
														->where('tag_timestamp', '>=', $minimumTimestamp)
														/*->where('tag_timestamp', '>=', $startTimestamp)
														 ->where('tag_timestamp', '<=', $endTimestamp) */
														->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
							
								
							// update preview tags with new - Ad
							\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
															->where('content_type_id', ContentType::findContentTypeIDByName('Ad'))
															->where('adkey', $this->ad_key)
															->where('tag_timestamp', '>=', $minimumTimestamp)
															/*->where('tag_timestamp', '>=', $startTimestamp)
															->where('tag_timestamp', '<=', $endTimestamp)*/
															->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
								
							if ($adKeyWithoutLastCharacter != '') {
								//update preview tags with new - Promotion
								\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
																->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
																->whereRaw('cart IS NOT NULL')
																->where('cart', '<>', '')
																->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cart, ' ', ''), '/', ''), '-', ''), '_', '') = '" . $adKeyWithoutLastCharacter . "'")
																->where('tag_timestamp', '>=', $minimumTimestamp)
																/*->where('tag_timestamp', '>=', $startTimestamp)
																->where('tag_timestamp', '<=', $endTimestamp)*/
																->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
							}
							
							
							\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
															->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
															->where('adkey', $this->ad_key)
															->where('tag_timestamp', '>=', $minimumTimestamp)
															/*->where('tag_timestamp', '>=', $startTimestamp)
															 ->where('tag_timestamp', '<=', $endTimestamp)*/
															->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
							
						//}
						
						
						$this->sendEventUpdateNotificationForContent();
					}
					
				} else if ($this->content_type_id == ContentType::GetTalkContentTypeID() && $this->content_subtype_id == ContentType::GetTalkSubContentTalkShowTypeID()) {   // for talk content - only talk show
					
					if ($removePrevLink) {
						$this->removeContentToTagsLink(false);
					}

					if ($this->start_date == '' || $this->start_date == '0000-00-00') return;
					if ($this->end_date == '' || $this->end_date == '0000-00-00') return;
					if ($this->start_time == '') return;
					if ($this->end_time == '' || $this->end_time == '00:00:00') return;
					
					$weekDays = $this->getContentEnabledWeekDays();
					
					if (count($weekDays) <= 0) return;
					
					$seekDayCheckRaw = '';
					$timezoneOffset = getTimezoneOffsetSeconds();
					
					$stationForContent = $this->station;
					if ($stationForContent) {
						$timezoneOffset = getTimezoneOffsetSecondsOfTimezone($stationForContent->getStationTimezone());
					}
					
					foreach($weekDays as $weekDay) {
						if ($seekDayCheckRaw != '') $seekDayCheckRaw .= ' OR ';
						$seekDayCheckRaw .= " (DAYOFWEEK(FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%Y-%m-%d')) = " . ($weekDay + 1) . ") ";
					}
										
					
					//update tags with new - Talk
					\DB::table('airshr_tags')->where('station_id', $this->station_id)
											->where('content_type_id', ContentType::GetTalkContentTypeID())
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%Y-%m-%d') >= '" . $this->start_date . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%Y-%m-%d') <= '" . $this->end_date . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%H:%i:%s') >= '" . $this->start_time . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%H:%i:%s') <= '" . $this->end_time . "'")
											->whereRaw("(" . $seekDayCheckRaw . ")")
											->where('tag_timestamp', '>=', $minimumTimestamp)
											->where('connect_content_id', 0)
											->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
					
					\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
											->where('content_type_id', ContentType::GetTalkContentTypeID())
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%Y-%m-%d') >= '" . $this->start_date . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%Y-%m-%d') <= '" . $this->end_date . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%H:%i:%s') >= '" . $this->start_time . "'")
											->whereRaw("FROM_UNIXTIME(FLOOR(tag_timestamp / 1000.0)+" . $timezoneOffset . ", '%H:%i:%s') <= '" . $this->end_time . "'")
											->whereRaw("(" . $seekDayCheckRaw . ")")
											->where('tag_timestamp', '>=', $minimumTimestamp)
											->where('connect_content_id', 0)
											->update(['connect_content_id' => $this->id, 'who' => $this->who, 'what' => $this->what]);
					
					// event update notification
					$this->sendEventUpdateNotificationForContent();
					
				}
				
			}
			
		} catch (\Exception $ex) {}
		
	}
	
	public function save(array $options = array())
	{
		$saveResult = parent::save($options);

		/*if (!$this->is_temp) {
			try {
				if ($this->content_type_id == ContentType::findContentTypeIDByName('Ad')) {
					// propagate change in connect content to tags & preview tags
					
					// Remove previous tag -> connect content links
					\DB::table('airshr_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(['connect_content_id' => 0]);
					\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)->where('connect_content_id', $this->id)->update(['connect_content_id' => 0]);
					
					$startTimestamp = strtotime($this->start_date);
					$endTimestamp = strtotime($this->end_date);
					
					if ($startTimestamp === FALSE) $startTimestamp = 0;
					if ($endTimestamp === FALSE) $endTimestamp = 0;
					
					if (!empty($this->ad_key)) {					
						//update tags with new - Ad
						\DB::table('airshr_tags')->where('station_id', $this->station_id)
												 ->where('content_type_id', ContentType::findContentTypeIDByName('Ad'))
												 ->where('adkey', $this->ad_key)
												 ->where('tag_timestamp', '>=', $startTimestamp)
												 ->where('tag_timestamp', '<=', $endTimestamp)
												 ->update(['connect_content_id' => $this->id]);
						
						$adKeyWithoutLastCharacter = substr($this->ad_key, 0, strlen($this->ad_key) - 1);
						if ($adKeyWithoutLastCharacter != '') {
							//update tags with new - Promotion
							\DB::table('airshr_tags')->where('station_id', $this->station_id)
													 ->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
													 ->whereRaw('cart IS NOT NULL')
													 ->where('cart', '<>', '')
													 ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cart, ' ', ''), '/', ''), '-', ''), '_', '') = '" . $adKeyWithoutLastCharacter . "'")
													 ->where('tag_timestamp', '>=', $startTimestamp)
													 ->where('tag_timestamp', '<=', $endTimestamp)
													 ->update(['connect_content_id' => $this->id]);
						}
						
						// update preview tags with new - Ad
						\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
												 ->where('content_type_id', ContentType::findContentTypeIDByName('Ad'))
												 ->where('adkey', $this->ad_key)
												 ->where('tag_timestamp', '>=', $startTimestamp)
												 ->where('tag_timestamp', '<=', $endTimestamp)
												 ->update(['connect_content_id' => $this->id]);
						
						if ($adKeyWithoutLastCharacter != '') {
							//update preview tags with new - Promotion
							\DB::table('airshr_preview_tags')->where('station_id', $this->station_id)
													->where('content_type_id', ContentType::findContentTypeIDByName('Promotion'))
													->whereRaw('cart IS NOT NULL')
													->where('cart', '<>', '')
													->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cart, ' ', ''), '/', ''), '-', ''), '_', '') = '" . $adKeyWithoutLastCharacter . "'")
													->where('tag_timestamp', '>=', $startTimestamp)
													->where('tag_timestamp', '<=', $endTimestamp)
													->update(['connect_content_id' => $this->id]);
						}
					
					}
				
				}
			} catch (\Exception $ex) {}
		}*/
		
		try {

			//Audio
			if (!$this->audio_enabled) {
				// automatic tick - audio if audio is uploaded
				$audioAttachment = $this->getAudioAttachment();
				if ($audioAttachment) {
					$this->audio_enabled = 1;
					parent::save($options);
				}	
			}
			
			//Text
			if(!empty($this->who) && !empty($this->what)) {
				$this->text_enabled = 1;
			}
			else {
				$this->text_enabled = 0;
			}

			$attachments = ConnectContentAttachment::where('content_id', '=', $this->id)->where('type', '!=', 'audio')->count();
			//Image/Logo/Video
			if($attachments > 0) {
				$this->image_enabled = 1;
			}
			else {
				$this->image_enabled = 0;
			}
			
			//Action
			if(!empty($this->action_id) && !empty($this->action_params)) {
				$this->action_enabled = 1;
			}
			else {
				$this->action_enabled = 0;
			}
			
			parent::save($options);
			
		} catch (\Exception $ex) {}
		
		return $saveResult;
	}
	
	public static function getConnectContentForTag($station_id, $content_type_id, $tag_timestamp, $adKey, $zettaid = null) {
		
		try {
			
			$time = date("Y-m-d", $tag_timestamp);
			
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', $content_type_id)
										/*->where('is_ready', '=', 1)*/
										/*->whereHas('contentDates', function ($q) use ($time){
											$q->where('start_date', '<=', $time)
											  ->where('end_date', '>=', $time);
										})*/
										->where('is_temp', '=', 0)
										//->where('ad_key', '=', $adKey)
										->where(function($q) use($adKey, $zettaid) {
											$q->where('ad_key', '=', $adKey);
											if(!empty($zettaid)) {
												$q->orWhere('zettaid', '=', $zettaid);
											}
										})
										->orderBy('created_at', 'desc')
										->firstOrFail();
			
			return $resultObj;										
			
		} catch (\Exception $ex) {
			//\Log::error($ex);
			return null;
		}
		
	}
	
	public static function getConnectContentForTalkTag($station_id, $tag_timestamp) {
		
		try {
				
			/*$tagDate = date("Y-m-d", $tag_timestamp);
			$tagTime = date("H:i:s", $tag_timestamp);
			$tagWeekDay = date("w", $tag_timestamp);*/
			
			$station = Station::findOrFail($station_id);
			$stationTimeZone = $station->getStationTimezone();
			
			$tagDate = getDateTimeStringInTimezone($tag_timestamp, "Y-m-d", $stationTimeZone);
			$tagTime = getDateTimeStringInTimezone($tag_timestamp, "H:i:s", $stationTimeZone);
			$tagWeekDay = getDateTimeStringInTimezone($tag_timestamp, "w", $stationTimeZone);
							
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
										->where('content_subtype_id', '=', ContentTYpe::GetTalkSubContentTalkShowTypeID())
										->where('start_date', '<=', $tagDate)
										->where('end_date', '>=', $tagDate)
										->where('start_time', '<=', $tagTime)
										->where('end_time', '>=', $tagTime)
										->where('content_weekday_' . $tagWeekDay,  '=', 1)
										->where('is_temp', '=', 0)
										->orderBy('created_at', 'desc')
										->firstOrFail();
				
			return $resultObj;
				
		} catch (\Exception $ex) {
			//\Log::error($ex);
			return null;
		}
		
	}

	public static function getConnectContentForNewsTag($station_id, $tag_timestamp) {
		
		try {
				
			$tagDate = date("Y-m-d", $tag_timestamp);
			$tagTime = date("H:i:s", $tag_timestamp);
			$tagWeekDay = date("w", $tag_timestamp);
							
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', ContentType::GetNewsContentTypeID())
										->first();
				
			return $resultObj;
				
		} catch (\Exception $ex) {
			//\Log::error($ex);
			return null;
		}
		
	}
	
	public static function getConnectContentForTrafficTag($station_id, $tag_timestamp) {
	
		try {
				
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
						->where('content_type_id', '=', ContentType::GetTrafficContentTypeID())
						->first();
	
			return $resultObj;
	
		} catch (\Exception $ex) {
			//\Log::error($ex);
			return null;
		}
	
	}
	
	public static function getConnectContentForIndividualTalk($station_id, $tag_timestamp_ms) {
		
		try {
		
			$tag_timestamp = getSecondsFromMili($tag_timestamp_ms);
			$tagDate = date("Y-m-d", $tag_timestamp);
			
			$talkToPreviewAssoc = ConnectContent2Preview::where('assoc_date', '=', $tagDate)
														->whereRaw("position = (SELECT COUNT(*) FROM airshr_tags WHERE station_id=" . $station_id . " AND content_type_id = " . ContentType::GetTalkContentTypeID() . " AND tag_timestamp > current_tag_timestamp AND tag_timestamp <= " . $tag_timestamp_ms . ")")
														->orderBy('id', 'desc')
														->firstOrFail();
			
			$connectContent = ConnectContent::findOrFail($talkToPreviewAssoc->connect_content_id);
			
			//$talkToPreviewAssoc->delete();
			
			return $connectContent;
		
		} catch (\Exception $ex) {
			//\Log::error($ex);
			return null;
		}
	}
	
	public static function getConnectContentForTagWitCart($station_id, $content_type_id, $tag_timestamp, $cartno) {
	
		try {
			
			if ($cartno == '') return null;
				
			$time = date("Y-m-d", $tag_timestamp);
				
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', $content_type_id)
										/*->where('is_ready', '=', 1)*/
										->where('ad_key', '<>', '')
										->whereRaw('ad_key IS NOT NULL')
										->whereRaw("SUBSTR(ad_key, 1, LENGTH(ad_key)-1) = '" . $cartno . "'")
										/*->whereHas('contentDates', function ($q) use ($time){
											$q->where('start_date', '<=', $time)
											->where('end_date', '>=', $time);
										})*/->where('is_temp', '=', 0)
										->orderBy('created_at', 'desc')
										->firstOrFail();
				
			return $resultObj;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	
	}
	
	public function contentClient() {
		return $this->belongsTo('App\ConnectContentClient', 'content_client_id');
	}
	
	public function contentProduct() {
		return $this->belongsTo('App\ConnectContentProduct', 'content_product_id');
	}
	
	public function removeConnectContent() {
		$attachments = $this->attachments;
		foreach ($attachments as $attachment) {
			$attachment->removeAttachment();
		}
		/*$subContents = $this->subContents;
		foreach ($subContents as $subContent) {
			$subContent->removeConnectContent();
		}*/
		$this->removeSubContents();
		
		return $this->delete();
	}
	
	public function delete(){
		
		$this->removeContentToTagsLink();
		
		$deleteResult = parent::delete();
				
		return $deleteResult;
	}
	
	public function scopeAds($query) {
		return $query->where('content_type_id', '=', ContentType::findContentTypeIDByName('Ad'));
	}
	
	public static function findAdContentOfKey($station_id, $adKey) {
		
		$resultObj = null;

		if($adKey == '' || $adKey === null) {
			return $resultObj;
		}
		
		try {
			$resultObj = ConnectContent::ads()->where('station_id', '=', $station_id)
												->where('ad_key', '=', $adKey)
												->where('is_temp', '=', 0)
												->orderBy('updated_at', 'desc')
												->firstOrFail();
		} catch (\Exception $ex) {}
		
		return $resultObj;
		
	}
	
	public function removeSubContents() {
		\DB::table('airshr_connect_content_belongs')->where('parent_content_id', '=', $this->id)->delete();
	}
	
	public function setSubContents($subContentIds, $subContentSyncList = array(), $subContentDateIdList = array()) {
		foreach ($subContentIds as $ind => $subContentId) {			
			ConnectContentBelongs::addBelongsInfo($this->id, $subContentId, isset($subContentSyncList[$ind]) ? $subContentSyncList[$ind] : 0, isset($subContentDateIdList[$ind]) ? $subContentDateIdList[$ind] : 0);
		}
	}
	
	public function copyValuesToSubContents() {
		
		$subContents = $this->getSubContents();
		
		$excludeAttributesArray = array('id', 'content_type_id', 'ad_length', 'start_date', 'end_date', 'content_instructions', 'content_version', 'content_original_version_id', 
							'ad_key', 'audio_enabled', 'content_parent_id', 'content_rec_type', 'content_percent', 'created_at', 'updated_at', 'deleted_at');
		
		$mandatoryAttributesArray = array('content_subtype_id', 'content_client_id', 'content_manager_user_id', 'atb_date', 'content_product_id', 'content_line_number', 
							'content_contact', 'content_email', 'content_phone');

		
		$thisValues = $this->attributes;
		$mandatoryValues = array();
		
		$thisAttachments = $this->attachments;
		
		foreach ($thisValues as $key => $val) {
			if (in_array($key, $mandatoryAttributesArray)) {
				$mandatoryValues[$key] = $val;
			}
			if (in_array($key, $excludeAttributesArray)) {
				unset($thisValues[$key]);	
			}
		}
		
		foreach ($subContents as $subContent) {
			
			$contentSync = $subContent->content_sync;
			
			unset($subContent->content_sync);
			unset($subContent->child_content_date_id);
			unset($subContent->start_date);
			unset($subContent->end_date);
			
			if (!$contentSync) {
				if (count($mandatoryValues) > 0) {
					$subContent->fill($mandatoryValues);
					$subContent->save();
					continue;
				}
			}
			
			$subContent->fill($thisValues);
			$subContent->save();
			
			// Remove previous attachments links except audio
			\DB::table('airshr_connect_content_attachments')->where('content_id', $subContent->id)->where('type', '<>', 'audio')->update(['content_id' => 0]);
			
			// add new attachments links
			foreach ($thisAttachments as $superAttachment) {
				if ($superAttachment->type == 'audio') continue;
				$newAttachment = $superAttachment->copyAttachment($subContent->id);
			}
		}
	}
	
	public function removeAttachmentAudio() {
		foreach ($this->attachments as $attachment) {
			if ($attachment->type == 'audio') {
				$attachment->content_id = 0;
				$attachment->save();
				break;
			}
		}
	}
	
	public static function GetContentVersionString($version) {
		if (isset(ConnectContent::$MATERIAL_INSTRUCTION_VERSION_LIST[$version]))  return ConnectContent::$MATERIAL_INSTRUCTION_VERSION_LIST[$version];
		return '';
	}
	
	public static function GetRecTypeString($rec_type) {
		if (isset(ConnectContent::$CONTENT_REC_TYPE_LIST[$rec_type]))  return ConnectContent::$CONTENT_REC_TYPE_LIST[$rec_type];
		return '';
	}
	
	public static function GetPercentString($percent) {
		$percent = intval($percent);
		if (isset(ConnectContent::$AD_PERCENT_LIST[$percent]))  return ConnectContent::$AD_PERCENT_LIST[$percent];
		return '';
	}
	
	public function copyContent() {
		
		try {
			$newObject = $this->replicate();

			$newObject->ad_key = '';

			if (!$newObject->save()) {
				throw new \Exception('Save Failed.');
			}
			
			// copy attachments
			$attachments = $this->attachments;
			foreach ($attachments as $attachment) {
				$newAttachment = $attachment->copyAttachment($newObject->id);
			}
			
			// copy sub contents if any (in case of material instruction)
			$subContents = $this->getSubContents();
			foreach ($subContents as $subContent) {
				ConnectContentBelongs::addBelongsInfo($newObject->id, $subContent->id, $subContent->content_sync, $subContent->child_content_date_id);
			}
			
			// copy content dates
			$contentDates = $this->contentDates;
			foreach ($contentDates as $contentDate) {
				$newObject->addContentDate(0, $contentDate->start_date, $contentDate->end_date);
			}
			
			return $newObject;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		} 		
	}

	public function copyContentWithOtherVersion($version) {
		try {
			$newObject = $this->copyContent();
			$newObject->content_version = $version;
			$newObject->content_original_version_id = $this->id;
			$newObject->save();
			return $newObject;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public function getPrintFileName() {
		
		if ($this->content_type_id == ContentType::GetMaterialInstructionContentTypeID()) {
			
			$fileName = "MI";

			$client = $this->contentClient;
			$product = $this->contentProduct;
			
			if ($client) {
				$fileName .= " - " . $client->client_name;
			}
			
			if ($product) {
				$fileName .= " - " . $product->product_name;
			}
			
			$firstAdWithKey = null;
			
			$subContents = $this->getSubContents();
			
			foreach ($subContents as $ad) {
				if (!empty($ad->ad_key)) {
					$firstAdWithKey = $ad;
					break;
				}
			}
			
			if ($firstAdWithKey) {
				
				$fileName .= " - " . $firstAdWithKey->ad_key;
				$fileName .= " - " . $firstAdWithKey->ad_length;
				
				if ($firstAdWithKey->start_date) {
					$fileName .= " - " . formatDateByParse("d-m-Y", $firstAdWithKey->start_date);
				}
			}
			
			$fileName .= ".pdf";
			
			return $fileName;
		}
		
		return "Pdf.pdf";
	}
	
	
	public function addContentDateIfNotExist($start_date, $end_date) {
		
		try {
			$contentDate = $this->getContentDateByDateRange($start_date, $end_date);
			
			if ($contentDate) return $contentDate;
	
			$contentDate = ConnectContentDate::create([
					'content_id' => $this->id,
					'start_date' => $start_date,
					'end_date'	=> $end_date
					]);
			
			return $contentDate;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	public function addContentDate($date_id, $start_date, $end_date) {
		
		try {
			if ($date_id) {
				$contentDate = ConnectContentDate::find($date_id);
				$contentDate->start_date = $start_date;
				$contentDate->end_date = $end_date;
				$contentDate->save();
			} else {
				$contentDate = ConnectContentDate::create([
							'content_id' => $this->id,
							'start_date' => $start_date,
							'end_date'	=> $end_date
						]);
			}
			return $contentDate->id;			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}
	
	public function addContentStartDate($date_id, $start_date) {
		
		try {
			if ($date_id) {
				$contentDate = ConnectContentDate::find($date_id);
				$contentDate->start_date = $start_date;
				$contentDate->save();
			} else {
				$contentDate = ConnectContentDate::create([
						'content_id' => $this->id,
						'start_date' => $start_date
						]);
			}
			return $contentDate->id;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
	
	public function addContentEndDate($date_id, $end_date) {
		
		try {
			if ($date_id) {
				$contentDate = ConnectContentDate::find($date_id);
				$contentDate->end_date = $end_date;
				$contentDate->save();
			} else {
				$contentDate = ConnectContentDate::create([
						'content_id' => $this->id,
						'end_date' => $end_date
						]);
			}
			return $contentDate->id;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}
	
	
	public function getContentEnabledWeekDays() {
		$results = array();
		for ($i = 0; $i < 7; $i++) {
			if ($this->getAttribute('content_weekday_' . $i) == 1) {
				$results[] = $i;
			}
		}
		return $results;
	}
	
	public function searchAudioFileAndLink() {
		
		// for ad only
		if ($this->content_type_id != ContentType::findContentTypeIDByName('Ad') || empty($this->ad_key)) return;
		
		$audioAttachment = $this->getAudioAttachment();
		
		// if audio attachment is not existing
		if (!$audioAttachment) {
			
			$adKey = $this->ad_key;
			
			try {
				/*$matchingAttachment = ConnectContentAttachment::where('type', '=', 'audio')
															->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(IF(LOCATE(" CT", LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) > 0, LEFT(LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename))), LOCATE(" CT", LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) - 1), LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) , " ", ""), "/", ""), "-", ""), "_", "") = ' . \DB::connection()->getPdo()->quote($adKey))
															->first();*/
				$matchingAttachment = ConnectContentAttachment::where('candidate_adkey', '=', $adKey)
															->where('type', '=', 'audio')
															->where('station_id', '=', $this->station_id)
															->first();
				
				if ($matchingAttachment) {
					if (empty($matchingAttachment->content_id)) {
						$matchingAttachment->content_id = $this->id;
						$matchingAttachment->save();	
					} else {
						$newAttachment = $matchingAttachment->copyAttachment($this->id);
					}
					$this->audio_enabled = 1;
					$this->save();
				}
				
			} catch (\Exception $ex) {
				\Log::error($ex);
			}
		}
	}
	
	public function copyContentOfClient($client, $who=null) {
		
		try {
			
			$this->who = empty($who) ? $client->who : $who;
			if (empty($this->what) && !empty($client->what)) {
				$this->what = $client->what;
			}
			$this->more = $client->more;
			$this->content_client_id = $client->id;
			$this->content_manager_user_id = $client->content_manager_user_id;
			$this->map_address1 = $client->map_address1;
			$this->map_address1_lat = $client->map_address1_lat;
			$this->map_address1_lng = $client->map_address1_lng;
			$this->action_id = $client->action_id;
			$this->action_params = $client->action_params;
			$this->content_product_id = $client->product_id;
			$this->content_contact = $client->client_contact_name;
			$this->content_email = $client->client_contact_email;
			$this->content_phone = $client->client_contact_phone;
			$this->content_agency_id = $client->content_agency_id;
			
			// remove previous attachments
			$attachments = $this->attachments;
			foreach ($attachments as $attachment) {
				if ($attachment->type == 'audio') continue; // do not remove audio
				$attachment->removeAttachment();
			}
			
			// copy attachments
			if (!empty($client->logo_attachment_id)) {
				ConnectContentAttachment::CopyAttachmentById($client->logo_attachment_id, $this->id);
			}
			
			if (!empty($client->image_attachment1_id)) {
				ConnectContentAttachment::CopyAttachmentById($client->image_attachment1_id, $this->id);
			}
			
			if (!empty($client->image_attachment2_id)) {
				ConnectContentAttachment::CopyAttachmentById($client->image_attachment2_id, $this->id);
			}
			
			if (!empty($client->image_attachment3_id)) {
				ConnectContentAttachment::CopyAttachmentById($client->image_attachment3_id, $this->id);
			}

			$this->save();

			return true;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;	
		}
	}
	
	public function isContentTalkBreak() {
		if ($this->content_type_id == ContentType::GetTalkContentTypeID() && $this->content_subtype_id == ContentType::GetTalkSubContentIndividualSegmentTypeID()) {
			return true;
		}	
		return false;
	}
	
	public static function GetStationDefaultContent($station_id) {
		
		try {

			if (empty($station_id)) return null;
			
			if (isset(self::$STATION_DEFAULT_CONTENTS[$station_id])) {
				return self::$STATION_DEFAULT_CONTENTS[$station_id];
			}
			
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('is_station_default', '=', '1')
										->where('is_temp', '=', 0)
										->with('actionDetail')
										->with('attachments')
										->orderBy('created_at', 'desc')
										->firstOrFail();
			
			self::$STATION_DEFAULT_CONTENTS[$station_id] = $resultObj;
			
			return $resultObj;
				
		} catch (\Exception $ex) {
			//\Log::error($ex);
			self::$STATION_DEFAULT_CONTENTS[$station_id] = false;
			return null;
		}
	}
	
	
	public static function CreateEmptyTalkBreak($station_id, $user_id, $who = '', $what = '') {
		
		try {
				
			$resultObj = ConnectContent::create([
						'station_id'		=> $station_id,
						'content_type_id'	=> ContentType::GetTalkContentTypeID(),
						'content_subtype_id'	=> ContentType::GetTalkSubContentIndividualSegmentTypeID(),
						'connect_user_id'		=> $user_id,
						'who'					=> $who,
						'what'					=> $what,
						'is_ready'				=> 0
					]);
		
			return $resultObj;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public function createTalkBreakFromContent() {
		
		try {
		
			$resultObj = $this->copyContent();
			$resultObj->content_type_id = ContentType::GetTalkContentTypeID();
			$resultObj->content_subtype_id = ContentType::GetTalkSubContentIndividualSegmentTypeID();
			$resultObj->save();
			
			return $resultObj;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	public function getJSONArrayForAutoComplete() {
		return array(
			'id'				=> $this->id,
			'who'				=> $this->who,
			'what'				=> $this->what,
			'is_competition'	=> $this->is_competition,
			'is_vote'			=> $this->is_vote,
			'vote_question'		=> $this->vote_question,
			'vote_option_1'		=> $this->vote_option_1,
			'vote_option_2'		=> $this->vote_option_2
		);
	}
	
	public function setContentAsReady($ready) {
		
		$this->is_ready = $ready;
		$this->save();
		
		if ($this->is_ready) {
			$this->sendEventUpdateNotificationForContent(); // send event update push notification
			$this->sendCompetitonResultGenerationRequest(); // send competition generation request
			$this->sendVoteResultGenerationRequest();		// send vote generation request
		}
			
	}
	
	public static function GetTalkBreakAutoCompleteList($station_id) {
		
		$resultArray = array();
		
		try {

			$talkBreaks = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
										->where('content_subtype_id', '=', ContentType::GetTalkSubContentIndividualSegmentTypeID())
										->whereRaw("((who IS NOT NULL AND who <> '') OR (what IS NOT NULL AND what <> '') OR (vote_question IS NOT NULL AND vote_question <> ''))")
										->groupBy('who')
										->groupBy('what')
										->groupBy('vote_question')
										->groupBy('is_vote')
										->groupBy('is_competition')
										->orderBy('created_at', 'desc')
										->get();
			
			foreach ($talkBreaks as $item) {
				$resultArray[] = $item->getJSONArrayForAutoComplete();
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $resultArray;
	}
	
	public static function GetTalkBreakByVoteQuestion($station_id, $voteQuestion) {
	
		$resultObj = null;
	
		try {
	
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
										->where('content_subtype_id', '=', ContentType::GetTalkSubContentIndividualSegmentTypeID())
										->where('vote_question', '=', $voteQuestion)
										->where('is_vote', 1)
										->orderBy('created_at', 'desc')
										->first();
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	
		return $resultObj;
	
	}
	
	public static function GetTalkBreakByWhat($station_id, $what) {
		
		$resultObj = null;
		
		try {
		
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
											->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
											->where('content_subtype_id', '=', ContentType::GetTalkSubContentIndividualSegmentTypeID())
											->where('what', '=', $what)
											->orderBy('created_at', 'desc')
											->first();
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $resultObj;
		
	}
	
	public static function GetTalkBreakByWho($station_id, $who) {
	
		$resultObj = null;
	
		try {
	
			$resultObj = ConnectContent::where('station_id', '=', $station_id)
										->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
										->where('content_subtype_id', '=', ContentType::GetTalkSubContentIndividualSegmentTypeID())
										->where('who', '=', $who)
										->orderBy('created_at', 'desc')
										->first();
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	
		return $resultObj;
	
	}
}
