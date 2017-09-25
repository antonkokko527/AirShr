<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrCachedTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_cached_tags', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('station_id');
			$table->bigInteger('tag_id');
			$table->bigInteger('tag_timestamp');
			
			$table->boolean('broadcasted')->default(0);
		
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_cached_tags');
	}

}
