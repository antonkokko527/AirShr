<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_users', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->string('unique_token', 100)->unique('unique_token');
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
			$table->string('email', 100)->unique('email');
			$table->string('password', 60);
			$table->rememberToken();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_users');
	}

}
