<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreIndexesToTagAndTerrestrial extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->index('station_id');
		});
		
		Schema::table('airshr_terrestrial_delay_log', function($table)
		{
			$table->index('station_id');
			$table->index('event_timestamp');
		});
		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->dropIndex('airshr_tags_station_id_index');
		});
		
		Schema::table('airshr_terrestrial_delay_log', function($table)
		{
			$table->dropIndex('airshr_terrestrial_delay_log_station_id_index');
			$table->dropIndex('airshr_terrestrial_delay_log_event_timestamp_index');
		});
	}

}
