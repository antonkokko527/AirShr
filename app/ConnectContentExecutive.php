<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ConnectContentExecutive extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_executives';
	
	protected $fillable = array('station_id', 'executive_name');

	public static function createOrFindExecutive($station_id, $executive_name) {
		try {

			$existing = null;

			try {
				$existing =
					ConnectContentExecutive::where('station_id', '=', $station_id)
						->where('executive_name', '=', $executive_name)
						->firstOrFail();

			} catch (\Exception $ex2) {}


			if (!$existing) {
				$existing = ConnectContentExecutive::create([
					'station_id' => $station_id,
					'executive_name' => $executive_name
				]);
			}

			return $existing->id;

		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
}
