<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class PhoneVerification extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_phone_verifications';
	
	protected $fillable = array('countrycode', 'phone_number', 'verification_code', 'msg_id', 'is_valid');
	
}
