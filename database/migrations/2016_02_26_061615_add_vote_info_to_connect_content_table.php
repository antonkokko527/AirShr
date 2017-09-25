<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoteInfoToConnectContentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->boolean('is_vote')->default(0);
			$table->string('vote_question')->nullable();
			$table->string('vote_option_1')->nullable();
			$table->string('vote_option_2')->nullable();
			$table->tinyInteger('vote_duration_minutes')->default(0);
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
			$table->dropColumn('is_vote');
			$table->dropColumn('vote_question');
			$table->dropColumn('vote_option_1');
			$table->dropColumn('vote_option_2');
			$table->dropColumn('vote_duration_minutes');
		});
	}

}
