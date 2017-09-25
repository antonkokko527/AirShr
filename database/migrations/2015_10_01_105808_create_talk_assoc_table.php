<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTalkAssocTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_talk2previews', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->date('assoc_date')->index();
			$table->bigInteger('preview_tag_id');
			$table->bigInteger('preview_tag_timestamp');
			$table->bigInteger('current_tag_timestamp');
			$table->integer('position');
			$table->bigInteger('connect_content_id');
						
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
		Schema::drop('airshr_connect_talk2previews');
	}

}
