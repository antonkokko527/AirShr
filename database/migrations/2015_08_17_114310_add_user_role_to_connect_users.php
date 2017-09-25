<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserRoleToConnectUsers extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_user_roles', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->enum('role_name', ['Admin', 'Sales']);
		
			$table->timestamps();
			$table->softDeletes();
		});
		
		
		Schema::table('airshr_connect_users', function($table)
		{
			$table->bigInteger('user_role')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		
		Schema::drop('airshr_connect_user_roles');
		
		Schema::table('airshr_connect_users', function($table)
		{
			$table->dropColumn('user_role');
		});
	}

}
