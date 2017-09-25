<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectUser extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use SoftDeletes;
	
	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'airshr_connect_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['first_name', 'last_name', 'password', 'station_id', 'email', 'username', 'user_role'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	protected $dates = ['deleted_at'];
	
	
	public function station() {
		return $this->belongsTo('App\Station', 'station_id');
	}
	
	public function userRole() {
		return $this->belongsTo('App\ConnectUserRole', 'user_role');
	}
	
	public function isAdminUser(){
		$userRole = $this->userRole;
		
		if (!$userRole) return false;
		
		if ($userRole->role_name == 'Admin') return true;
		
		return false;
	}
	
	public function isClientManager() {
		$userRole = $this->userRole;
		
		if (!$userRole) return false;
		
		if ($userRole->role_name == 'ClientManager') return true;
		
		return false;
	}
	
	public function isInvestor() {
		$userRole = $this->userRole;
		
		if (!$userRole) return false;
		
		if ($userRole->role_name == 'Investor') return true;
		
		return false;
	}
}
