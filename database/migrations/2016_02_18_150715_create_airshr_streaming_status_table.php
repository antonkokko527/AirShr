<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrStreamingStatusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_streaming_status', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('user_id');
			$table->bigInteger('station_id');
			
			$table->enum('streaming_status', ['start', 'playing', 'stopped']);
			
			$table->string('user_lat', 20)->nullable();
			$table->string('user_lng', 20)->nullable();
			
			$table->bigInteger('status_timestamp');
		
			$table->timestamps();
		});
		
		Schema::table('airshr_stations', function($table)
		{
			$table->bigInteger('streaming_count')->default(0);
							
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_streaming_status');
		
		Schema::table('airshr_stations', function($table)
		{
			$table->dropColumn('streaming_count');
		
		});
	}

}
