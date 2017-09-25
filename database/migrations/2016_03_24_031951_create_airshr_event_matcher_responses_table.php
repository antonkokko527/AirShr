<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrEventMatcherResponsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_event_match_responses', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('queue');
			$table->bigInteger('event_id');
			$table->text('payload');
			$table->bigInteger('sent_time')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_event_match_responses');
	}

}
