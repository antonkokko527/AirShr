<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_regions';
	
	protected $guarded = array();
	
	
	public function regionStations() {
		return $this->belongsToMany('App\Station', 'airshr_station2regions', 'region_id', 'station_id');
	}

}
