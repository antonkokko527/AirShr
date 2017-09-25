<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserToRemoteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_user2remotes', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
	
			$table->bigInteger('user_id');
			$table->bigInteger('remote_id');
		
			$table->unique(['user_id', 'remote_id'], 'user_id_remote_id_unique');
			
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_user2remotes');
	}

}
