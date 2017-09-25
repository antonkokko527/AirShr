<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastUpdatedToMusicRatingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_music_ratings', function(Blueprint $table)
		{
			$table->bigInteger('last_updated');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_music_ratings', function(Blueprint $table)
		{
			$table->dropColumn('last_updated');
		});
	}

}
