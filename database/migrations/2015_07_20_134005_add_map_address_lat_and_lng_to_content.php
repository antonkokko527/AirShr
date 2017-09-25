<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMapAddressLatAndLngToContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->string('map_address1_lat')->nullable();
			$table->string('map_address1_lng')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->dropColumn('map_address1_lat');
			$table->dropColumn('map_address1_lng');
		});
	}

}
