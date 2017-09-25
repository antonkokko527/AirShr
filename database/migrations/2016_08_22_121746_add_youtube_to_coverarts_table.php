<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddYoutubeToCoverartsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->string('youtube_video_id');
            $table->string('youtube_title');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
            $table->dropColumn('youtube_video_id');
            $table->dropColumn('youtube_title');
		});
	}

}
