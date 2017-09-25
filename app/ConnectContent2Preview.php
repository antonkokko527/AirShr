<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectContent2Preview extends Model {
	
	protected $table = 'airshr_connect_talk2previews';
	
	protected $fillable = array('assoc_date', 'preview_tag_id', 'preview_tag_timestamp', 'current_tag_timestamp', 'position', 'connect_content_id');
}
