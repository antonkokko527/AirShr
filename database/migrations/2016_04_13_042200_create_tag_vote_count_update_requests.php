<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagVoteCountUpdateRequests extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_tag_vote_count_update_requests', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->bigInteger('tag_id')->default(0);
			$table->bigInteger('station_id')->default(0);
			$table->bigInteger('vote_option1_count')->default(0);
			$table->bigInteger('vote_option2_count')->default(0);
			$table->unsignedInteger('updated_at');
				
			$table->unique('tag_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_tag_vote_count_update_requests');
	}

}
