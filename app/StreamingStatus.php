<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class StreamingStatus extends Model {
	
	protected $table = 'airshr_streaming_status';
	
	protected $guarded = array();
	
	
	public static function addStreamingStatus($userId, $stationId, $streamingStatus, $userLat, $userLng) {
		
		try {
				
			$newStreamingStatus = StreamingStatus::create([
									'user_id'			=> $userId,
									'station_id'		=> $stationId,
									'streaming_status'	=> $streamingStatus,
									'user_lat'			=> $userLat,
									'user_lng'			=> $userLng,
									'status_timestamp'	=> getAWSTimeFromNTPServer()
								  ]);
			
			
			
			return $newStreamingStatus;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
}
