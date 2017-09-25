<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationToRegionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_station2regions', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('station_id');
			$table->bigInteger('region_id');
		
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
		Schema::drop('airshr_station2regions');
	}

}
