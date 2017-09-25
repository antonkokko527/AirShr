<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_events', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->bigInteger('user_id')->default(0);
			
			$table->string('record_file');
			$table->bigInteger('record_timestamp');
			$table->string('record_device_id')->nullable();
			$table->string('push_token')->nullable();
						
			$table->bigInteger('station_id')->default(0);
			$table->string('station_name')->nullable();
			
			$table->bigInteger('content_type_id')->default(0);
			$table->string('content_type')->nullable();
			
			$table->bigInteger('tag_id')->default(0);
			
			$table->string('who')->nullable();
			$table->string('what')->nullable();
			$table->string('how')->nullable();
			
			$table->string('adkey')->nullable();
			$table->string('logo_file')->nullable();
			$table->string('screen_file')->nullable();
			$table->string('audio_file')->nullable();
			$table->string('coverart_url')->nullable();
			$table->string('itunes_url')->nullable();

			$table->boolean('event_data_status')->default(0);
			$table->timestamp('event_data_status_updateon')->nullable();
			
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
		Schema::drop('airshr_events');
	}

}
