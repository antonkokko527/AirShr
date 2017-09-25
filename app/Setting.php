<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_settings';
	
	protected $fillable = array('id', 'conf_name', 'conf_val');

	public static function getSettingVal($conf_name, $default = NULL) {
		try {
			$setting = Setting::where('conf_name', $conf_name)->firstOrFail();
			return $setting->conf_val;
		} catch (\Exception $ex) {
			return $default;
		}
	}
	
	public static function getSettingsValAsJSON($conf_name) {
		$results = array();
		$val = self::getSettingVal($conf_name);
		if ($val) {
			$results = json_decode($val, true);
		}
		return $results;
	}
	
	public static function setSettingVal($conf_name, $conf_val) {
		try {
			$setting = Setting::where('conf_name', '=', $conf_name)->first();
			if ($setting) {
				$setting->conf_val = $conf_val;
				$setting->save();
			} else {
				$setting = Setting::create([
							'conf_name' => $conf_name,
							'conf_val'	=> $conf_val
						]);
			}
		} catch (\Exception $ex) {
			return false;
		}
		return true;
	}
}
