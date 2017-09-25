<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreInfoToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_users', function($table)
		{
			$table->string('user_id', 50);
			
			$table->enum('gender', ['','M','F'])->default('');
			$table->date('dob')->nullable();
			
			$table->string('city')->nullable();
			$table->string('first_event_lat')->nullable();
			$table->string('first_event_lng')->nullable();
			
			$table->string('email1')->nullable();
			$table->string('email2')->nullable();
			
			$table->string('contact_phone_countrycode', 5)->nullable();
			$table->string('contact_phone_number', 30)->nullable();
			
			$table->text('note')->nullable();
			
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
			$table->dropColumn('user_id');
			
			$table->dropColumn('gender');
			$table->dropColumn('dob');
			
			$table->dropColumn('city');
			$table->dropColumn('first_event_lat');
			$table->dropColumn('first_event_lng');
			
			$table->dropColumn('email1');
			$table->dropColumn('email2');
			
			$table->dropColumn('contact_phone_countrycode');
			$table->dropColumn('contact_phone_number');
			
			$table->dropColumn('note');
		});
	}

}
