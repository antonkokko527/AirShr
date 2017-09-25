<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleMusicUrlToCoverarts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function($table)
		{
			$table->string('google_music_url', 1024)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_coverarts', function($table)
		{
			$table->dropColumn('google_music_url');
		});
	}

}
