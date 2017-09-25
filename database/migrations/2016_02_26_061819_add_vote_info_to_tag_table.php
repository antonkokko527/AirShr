<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoteInfoToTagTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->bigInteger('vote_expiry_timestamp')->default(0);
			$table->boolean('vote_expired')->default(1);
			$table->bigInteger('vote_option1_count')->default(0);
			$table->bigInteger('vote_option2_count')->default(0);
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
			$table->dropColumn('vote_expiry_timestamp');
			$table->dropColumn('vote_expired');
			$table->dropColumn('vote_option1_count');
			$table->dropColumn('vote_option2_count');
		});
	}

}
