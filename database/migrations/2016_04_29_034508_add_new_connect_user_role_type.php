<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewConnectUserRoleType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement("ALTER TABLE airshr_connect_user_roles CHANGE COLUMN role_name role_name ENUM('Admin', 'Sales', 'ClientManager', 'Investor')");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\DB::statement("ALTER TABLE airshr_connect_user_roles CHANGE COLUMN role_name role_name ENUM('Admin', 'Sales', 'ClientManager')");
	}

}
