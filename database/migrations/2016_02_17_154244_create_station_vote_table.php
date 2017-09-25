<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationVoteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_station_votes', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('user_id');
			$table->bigInteger('station_id');
			
			$table->boolean('vote')->default(1);
		
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
		Schema::drop('airshr_station_votes');
	}

}
