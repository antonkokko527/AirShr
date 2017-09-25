<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ConnectContentAgency extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_agencies';
	
	protected $fillable = array('station_id', 'agency_name', 'agency_contact_name', 'agency_contact_phone', 'agency_contact_email');

	public static function agencyExists($station_id, $agency_name) {

		try {

			$existing = null;

			try {
				$existing = ConnectContentAgency::where('station_id', '=', $station_id)->where('agency_name', '=', $agency_name)->firstOrFail();
			} catch (\Exception $ex2) {}


			if ($existing) {
				return $existing;
			}

			return false;

		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}

	}
}
