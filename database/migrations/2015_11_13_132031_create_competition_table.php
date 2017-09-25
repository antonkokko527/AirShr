<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_competitions', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
				
			$table->bigInteger('tag_id');
			$table->bigInteger('tag_start_timestamp');
			$table->bigInteger('tag_end_timestamp');
			$table->bigInteger('competition_check_timestamp');
			
			$table->integer('event_users_num');
			$table->integer('picked_users_num');
			
			$table->text('picked_user_ids')->nullable();
			$table->text('picked_user_phones')->nullable();
					
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
		Schema::drop('airshr_competitions');
	}

}
