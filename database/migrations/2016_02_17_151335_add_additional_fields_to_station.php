<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalFieldsToStation extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_stations', function($table)
		{
			$table->string('station_tagline')->nullable();
			$table->enum('station_band', ['FM', 'AM'])->default('FM');
			$table->enum('station_type', ['Commercial', 'Community', 'Public'])->default('Commercial');
			
			$table->boolean('airshr_enabled')->default(0);
			$table->boolean('stream_enabled')->default(0);
			
			$table->string('stream_url')->nullable();
			
			$table->string('station_homepage')->nullable();
			$table->string('station_twitterhandle')->nullable();
			
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
			$table->dropColumn('station_tagline');
			$table->dropColumn('station_band');
			$table->dropColumn('station_type');
			
			$table->dropColumn('airshr_enabled');
			$table->dropColumn('stream_enabled');
			
			$table->dropColumn('stream_url');
			
			$table->dropColumn('station_homepage');
			$table->dropColumn('station_twitterhandle');
		
		});
	}

}
