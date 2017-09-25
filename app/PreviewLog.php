<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreviewLog extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_preview_logs';
	
	protected $fillable = array('station_id', 'preview_date', 'status', 'reason', 'file_path', 'file_lastmtime');
	
	public function station()
	{
		return $this->belongsTo('App\Station', 'station_id');
	}
}
