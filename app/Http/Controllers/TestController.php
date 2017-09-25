<?php namespace App\Http\Controllers;

use Request;
use App\TerrestrialStreamDelay;

class TestController extends JSONController {

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
	 *  change terrestrial log
	 *
	 */
	public function changeLatestTerrestrialLog()
	{
		$lag = Request::input('lag');
		$time = Request::input('time');
		$station_id = Request::input('station_id');
		
		
		if (empty($lag)) {
			$this->setErrorCode("UNKNOWN_ERROR");
			return $this->sendJSONOutput();
		}
		
		if (empty($time)) {
			$time = time();
		} else {
			$time = strtotime($time);
			if ($time === FALSE) {
				$this->setErrorCode("UNKNOWN_ERROR");
				return $this->sendJSONOutput();
			}
		}
		
		if (empty($station_id)) {
			$station_id = 8;
		}
		
		TerrestrialStreamDelay::create([	
			'event_id'		=> 0,
			'station_id'	=> $station_id,
			'event_timestamp'	=> $time * 1000,
			'match_timestamp'	=> 0,
			'terrestrial_stream_delay' => $lag
		]);
		
		\DB::table('airshr_events')->where('record_timestamp_ms', '>=', $time * 1000)
								  ->update(['recent_terrestrial_log' => $lag]);
		
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
}
