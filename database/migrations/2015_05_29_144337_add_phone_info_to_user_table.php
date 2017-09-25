<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneInfoToUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_users', function($table)
		{
			$table->string('countrycode', 5);
			$table->string('phone_number', 30);
			
			$table->unique(array('countrycode', 'phone_number'));
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
			$table->dropColumn('countrycode');
			$table->dropColumn('phone_number');
			
			$table->dropUnique('airshr_users_countrycode_phone_number_unique');
			
		});
	}

}
