<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class User2Remote extends Model {
	
	protected $table = 'airshr_user2remotes';
	
	protected $fillable = array('user_id', 'remote_id');

	
	public static function findUserToRemoteInfo($user_id, $remote_id) {
	
		try {
				
			$existing = null;
				
			try {
				$existing = User2Remote::where('user_id', '=', $user_id)
										->where('remote_id', '=', $remote_id)
										->firstOrFail();
	
			} catch (\Exception $ex2) {}
				
			return $existing;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	
	}
	
	
	
	public static function addUserRemoteInfo($user_id, $remote_id) {
	
		try {
	
			$existing = User2Remote::findUserToRemoteInfo($user_id, $remote_id);
	
			if ($existing) return $existing;
				
			$existing = User2Remote::create(
					[
						'user_id' => $user_id,
						'remote_id' => $remote_id
					]
			);
	
			return $existing;
	
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
}
