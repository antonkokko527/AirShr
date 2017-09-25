<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_tags', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->bigInteger('tagger_id')->default(0);
			$table->bigInteger('station_id');
			$table->bigInteger('content_type_id');
			$table->bigInteger('tag_timestamp')->index();
			
			$table->string('who')->nullable();
			$table->string('what')->nullable();
			$table->string('how')->nullable();
			
			$table->string('adkey')->nullable();
			$table->string('logo_file')->nullable();
			$table->string('screen_file')->nullable();
			$table->string('audio_file')->nullable();
			$table->string('coverart_url')->nullable();
			$table->string('itunes_url')->nullable();
			
			$table->boolean('is_valid')->default(0);
			
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
		Schema::drop('airshr_tags');
	}

}
