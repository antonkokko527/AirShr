<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectClientLookup extends Model
{
	use SoftDeletes;

	protected $fillable = array('zettaid', 'ad_key', 'client_name', 'product', 'is_found');

	protected $table = 'airshr_connect_client_lookups';

	public static function getByZettaId($zettaId)
	{
		return self::where('zettaid', '=', $zettaId)->first();
	}
}
