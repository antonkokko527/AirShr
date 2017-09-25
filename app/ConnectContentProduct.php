<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ConnectContentProduct extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_products';
	
	protected $fillable = array('station_id', 'product_name');

	
	public static function createOrFindProduct($station_id, $product_name) {
		
		try {
			
			$existing = null;
			
			try {
				$existing = ConnectContentProduct::where('station_id', '=', $station_id)->where('product_name', '=', $product_name)->firstOrFail();
				
			} catch (\Exception $ex2) {}
			
	
			if (!$existing) {
				$existing = ConnectContentProduct::create([
							'station_id' => $station_id,
							'product_name' => $product_name
						]); 
			}
			
			return $existing->id;			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
}
