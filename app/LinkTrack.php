<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkTrack extends Model {
	
	protected $table = 'airshr_link_tracks';
	
	protected $fillable = array('event_id', 'click_timestamp', 'url');
}
