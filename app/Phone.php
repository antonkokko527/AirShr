<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Phone extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_phones';
	
	protected $guarded = array();
	
	
	public static function findUserPhoneInfo($user_id, $phone_model, $phone_os) {
	
		try {
	
			$existing = null;
	
			try {
				$existing = Phone::where('user_id', '=', $user_id)
										->where('phone_model', '=', $phone_model)
										->where('phone_os', '=', $phone_os)
										->firstOrFail();
	
			} catch (\Exception $ex2) {}
	
			return $existing;
	
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	
	}
	
	public static function addUserPhoneInfo($user_id, $phone_model, $phone_os) {
	
		try {
	
			$existing = Phone::findUserPhoneInfo($user_id, $phone_model, $phone_os);
	
			if ($existing) return $existing;
	
			$existing = Phone::create(
					[
					'user_id' => $user_id,
					'phone_model' => $phone_model,
					'phone_os'	=> $phone_os
					]
			);
	
			return $existing;
	
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
}
