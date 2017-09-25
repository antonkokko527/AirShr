<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerrestrialDelayLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_terrestrial_delay_log', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
				
			$table->bigInteger('event_id')->default(0);
			$table->bigInteger('station_id')->default(0);
			
			$table->bigInteger('event_timestamp');
			$table->bigInteger('match_timestamp');
			
			$table->bigInteger('terrestrial_stream_delay');
			
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
		Schema::drop('airshr_terrestrial_delay_log');
	}

}
