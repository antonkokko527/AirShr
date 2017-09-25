<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TerrestrialStreamDelay extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_terrestrial_delay_log';
	
	protected $fillable = array('event_id', 'station_id', 'event_timestamp', 'match_timestamp', 'terrestrial_stream_delay');

	public static function getMostRecentTerrestrialDelay($station_id) {
		
		$result = false;
		
		try {

			$delay = TerrestrialStreamDelay::where('station_id', '=', $station_id)
						->orderBy('event_timestamp', 'desc')
						->take(1)
						->get();
			
			if (isset($delay[0])) {
				$result = $delay[0];
			}
			
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
	}
	
	
	public static function getMostRecentTerrestrialDelayOfEvent($event) {
	
		$result = false;
	
		try {
	
			$delay = TerrestrialStreamDelay::where('station_id', '=', $event->station_id)
						->where('event_timestamp', '<=', $event->record_timestamp_ms)
						->orderBy('event_timestamp', 'desc')
						->take(1)
						->get();
				
			if (isset($delay[0])) {
				$result = $delay[0];
			}
				
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
	
		return $result;
	}
	
	public static function getMostRecentTerrestrialDelayOfTag($station_id, $tag_timestamp) {
	
		$result = false;
	
		try {
	
			$delay = TerrestrialStreamDelay::where('station_id', '=', $station_id)
						->where('event_timestamp', '<=', $tag_timestamp)
						->orderBy('event_timestamp', 'desc')
						->take(1)
						->get();
	
			if (isset($delay[0])) {
				$result = $delay[0];
			}
	
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
	
		return $result;
	}
	
}
