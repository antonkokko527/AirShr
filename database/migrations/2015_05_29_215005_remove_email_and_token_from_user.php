<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEmailAndTokenFromUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_users', function($table)
		{
			$table->dropUnique('email');
			$table->dropUnique('unique_token');
			
			$table->dropColumn('email');
			$table->dropColumn('unique_token');
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
			$table->string('unique_token', 100)->unique('unique_token');
			$table->string('email', 100)->unique('email');
			
		});
	}

}
