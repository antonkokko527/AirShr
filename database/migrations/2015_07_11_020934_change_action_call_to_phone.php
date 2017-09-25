<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeActionCallToPhone extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement("ALTER TABLE airshr_connect_content_actions CHANGE COLUMN action_type action_type ENUM('book', 'phone', 'claim', 'get', 'website', 'contact') DEFAULT 'get'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\DB::statement("ALTER TABLE airshr_connect_content_actions CHANGE COLUMN action_type action_type ENUM('book', 'call', 'claim', 'get', 'website', 'contact') DEFAULT 'get'");
	}

}
