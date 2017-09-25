<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ConnectContentAction extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_actions';
	
	protected $fillable = array('action_type', 'action_label');
		
	public function getJSONArray() {
		return array(
				'action_type'	=> $this->action_type,
				'action_label'	=> $this->action_label
		);
	}
}
