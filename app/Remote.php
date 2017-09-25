<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Remote extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_remotes';
	
	protected $guarded = array();
	
	public static function findRemoteByLastDigit($lastDigit) {
		try{
	
			$existingDevice = Remote::where('last_digits', '=', $lastDigit)
									->firstOrFail();
	
			return $existingDevice;
	
		}catch (\Exception $ex) {
			return null;
		}
	}
	
	public function updateFirstInstallInformation($remote_name, $initial_voltage) {
		try{
			$this->remote_name = $remote_name;
			$this->initial_voltage = $initial_voltage;
			$this->install_date = date('Y-m-d H:i:s');
			$this->installed = 1;
			$this->save();
		}catch(\Exception $ex) {
			\Log::error($ex);
		}
	}
	
}
