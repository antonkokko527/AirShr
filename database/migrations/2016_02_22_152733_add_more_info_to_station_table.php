<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreInfoToStationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_stations', function($table)
		{
			$table->string('call_sign')->nullable();
			$table->string('network_name')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_stations', function($table)
		{
			$table->dropColumn('call_sign');
			$table->dropColumn('network_name');
		});
	}

}
