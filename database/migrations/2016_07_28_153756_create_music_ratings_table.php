<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMusicRatingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_music_ratings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->string('artist');
			$table->string('title');
			$table->text('data');
			$table->tinyInteger('watch')->default(0);
			$table->integer('station_id');
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
		Schema::drop('airshr_music_ratings');
	}

}
