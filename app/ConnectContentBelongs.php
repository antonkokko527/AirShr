<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectContentBelongs extends Model {
	
	protected $table = 'airshr_connect_content_belongs';
	
	protected $fillable = array('parent_content_id', 'child_content_id', 'content_sync', 'child_content_date_id');

	public static function findBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id = 0) {
		
		try {
			
			$existing = null;
			
			try {
				$existing = ConnectContentBelongs::where('parent_content_id', '=', $parent_content_id)->where('child_content_id', '=', $child_content_id)->where('child_content_date_id', '=', $child_content_date_id)->firstOrFail();
				
			} catch (\Exception $ex2) {}
			
			return $existing;			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	
	
	public static function addBelongsInfo($parent_content_id, $child_content_id, $sync = 0, $child_content_date_id = 0) {
		
		try {
				
			$existing = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id);
				
			if ($existing) return $existing;
			
			$existing = ConnectContentBelongs::create(
				[
				'parent_content_id' => $parent_content_id,
				'child_content_id' => $child_content_id,
				'content_sync'	=> $sync,
				'child_content_date_id' => $child_content_date_id
				]
			);
		
			return $existing;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public static function removeBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id = 0) {
		
		try {
		
			$existing = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id);
		
			if (!$existing) return true;
			
			$existing->delete();
			
			return true;
		
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
	
	
	public static function setSyncMode($parent_content_id, $child_content_id, $child_content_date_id, $content_sync) {
		
		try {
			$exisiting = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id);
			
			if (!$exisiting) return true;
			
			// if sync is enabled, disable other syncs with other MI
			if ($content_sync) {
				\DB::table('airshr_connect_content_belongs')->where('child_content_id', $child_content_id)->update(['content_sync' => 0]);
			}
			
			$exisiting->content_sync = $content_sync;
			
			$exisiting->save();
			
			return true;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}
	
	public static function getSyncMode($parent_content_id, $child_content_id) {
		
		$exisiting = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id);
		
		if (!$exisiting) return false;
		
		return $exisiting->content_sync;
		
	}
	
	
	public static function getChildContentDate($parent_content_id, $child_content_id, $child_content_date_id) {
		
		try {
			$exisiting = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id, $child_content_date_id);
			if (!$exisiting) return null;
			$contentDate = ConnectContentDate::findOrFail($exisiting->child_content_date_id);
			return $contentDate;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public static function setChildContentDate($parent_content_id, $child_content_id, $child_content_date_id) {
		try {
			$exisiting = ConnectContentBelongs::findBelongsInfo($parent_content_id, $child_content_id);
			if (!$exisiting) return false;
			$exisiting->child_content_date_id = $child_content_date_id;
			$exisiting->save();
			return true;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
}
