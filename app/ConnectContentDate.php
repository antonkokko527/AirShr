<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectContentDate extends Model {
	
	protected $table = 'airshr_connect_content_dates';
	
	protected $fillable = array('content_id', 'start_date', 'end_date');
	
	public function content() {
		return $this->belongsTo('App\ConnectContent', 'content_id');
	}
}
