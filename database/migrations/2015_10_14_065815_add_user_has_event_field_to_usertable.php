<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserHasEventFieldToUsertable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_users', function($table)
		{
			$table->boolean('has_event')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_users', function($table)
		{
			$table->dropColumn('has_event');
			
		});
	}

}
