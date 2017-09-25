<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;
	
	public static $USER_ID_LENGTH = 20;
	public static $USER_ID_CHARACTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'airshr_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = array();

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	
	public static function generateUserID() {
		$userID = '';
		for ($i = 0; $i < self::$USER_ID_LENGTH; $i++) {
			$userID .= self::$USER_ID_CHARACTERS[rand(0, strlen(self::$USER_ID_CHARACTERS) -1)];
		}
		return $userID;
	}
	
	public static function findUserByUniqueID($userID) {
		try{
		
			$existingUser = User::where('user_id', '=', $userID)
								->firstOrFail();
		
			return $existingUser;
		
		}catch (\Exception $ex) {
			return null;
		}
	}
	
	public static function findUserByPhoneNumber($countrycode, $phone_number) {
		try{
		
			$existingUser = User::where('countrycode', '=', $countrycode)
								->where('phone_number', '=', $phone_number)
								->firstOrFail();
				
			return $existingUser;
				
		}catch (\Exception $ex) {
			return null;
		}
	}
	
	public static function createOrFindUserByPhoneNumber($countrycode, $phone_number, $device_id = '') {
		
		try {
				
			$existingUser = User::findUserByPhoneNumber($countrycode, $phone_number);
			
			if ($existingUser) {
				$existingUser->device_id = $device_id;
				$existingUser->save();
				return $existingUser;
			}
			
			$newUser = User::create([
								'countrycode'	=> $countrycode,
								'phone_number'	=> $phone_number,
								'device_id'		=> $device_id,
								'user_id'		=> User::generateUserID()
						]);
			
			return $newUser;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public function getJSONArrayForUserVerification() {
		return array(
			'user_id'		=> $this->user_id,
			'countrycode'	=> $this->countrycode,
			'phone_number'	=> $this->phone_number	
		);
	}
	
	public function saveFirstEventInfo($lat, $lng) {
		try {
		
			$this->first_event_lat = $lat;
			$this->first_event_lng = $lng;
			$this->has_event = 1;
			$this->save();
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
	
	public static function getMostRecentEventOfUser($user_id, $content_type = 0, $checkTrash = false) {
		
		$result = false;
		
		try {
		
			if ($checkTrash) {
				$event = Event::withTrashed()->where('record_device_id', '=', $user_id);
			} else {
				$event = Event::where('record_device_id', '=', $user_id);
			}
			
			if ($content_type == 0) {
				$event = $event->orderBy('record_timestamp_ms', 'desc')
							->first();
			} else {
				$event = $event->where('content_type_id', '=', $content_type)
							->orderBy('record_timestamp_ms', 'desc')
							->first();
			}
		
			if ($event) {
				$result = $event;
			}
			
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
	}
	
	public function getUserDeviceToken() {
		
		$result = array('token' => '', 'type' => '');
				
		$mostRecentEvent = User::getMostRecentEventOfUser($this->user_id, 0, true);
		
		if (!$mostRecentEvent) return $result;
		
		$result['token'] = $mostRecentEvent->push_token;
		$result['type'] = $mostRecentEvent->device_type;
		
		return $result;
	}
	
	public static function createUserEventFromTag($device_id, $tag_id, $push_token, $device_type, $increaseTagCount, $record_timestamp_ms = 0, $app_version = '') {
		
		$newEvent = null;
		
		try {
			
			$tag = Tag::findOrFail($tag_id);
		
			$creation_time = time();
			
			$matchTimestamp = $tag->tag_timestamp;
			
			$terrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelayOfTag($tag->station_id, $tag->tag_timestamp);
			
			$terrestrialDelayMiliSeconds = 0;
			
			if ($terrestrialDelay) {
				$terrestrialDelayMiliSeconds = $terrestrialDelay->terrestrial_stream_delay;
				$matchTimestamp += $terrestrialDelayMiliSeconds;
			}
			// create new event record
			$newEvent = Event::create([
					'user_id' 			=> 0,     // currently just set to 0
					'record_file' 		=> '',
					'record_timestamp'	=> $record_timestamp_ms == 0 ? getSecondsFromMili($tag->tag_timestamp) : getSecondsFromMili($record_timestamp_ms),
					'record_timestamp_ms' => $record_timestamp_ms == 0 ? $tag->tag_timestamp : $record_timestamp_ms,
					'record_device_id'	=> $device_id,
					'push_token'		=> $push_token,
					'device_type'		=> $device_type,
					'station_id'		=> $tag->station_id,
					'content_type_id'	=> $tag->content_type_id,
					'tag_id'			=> $tag_id,
					'event_data_status'	=> '1',
					'event_data_status_updateon' => $creation_time,
					'match_percent'		=> '1.0',
					'match_time'		=> $matchTimestamp,
					'button_press_type' => 'None',
					'terrestrial_delay'	=> $terrestrialDelayMiliSeconds,
					'recent_terrestrial_log' => $terrestrialDelayMiliSeconds,
					'app_version'		=> $app_version
			]);
			
			$tag->createHashForTag(); // create hash for tag linked with event
		
			if ($increaseTagCount) {
				$tag->increaseEventCount();
			}
				
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $newEvent;
	}
}
