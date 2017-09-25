<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model {
	
	protected $table = 'airshr_competitions';
	
	protected $fillable = array('tag_id', 'tag_start_timestamp', 'tag_end_timestamp', 'competition_check_timestamp', 'event_users_num', 'picked_users_num', 'picked_user_ids', 'picked_user_phones');
}
