<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrconnectContentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_contents', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->bigInteger('station_id');
			$table->bigInteger('content_type_id');
			$table->bigInteger('content_subtype_id')->default(0);
			$table->bigInteger('connect_user_id')->default(0);
			
			$table->string('who')->nullable();
			$table->string('what')->nullable();
			$table->text('more')->nullable();
			$table->string('description')->nullable();
			
			$table->integer('ad_length')->nullable();
			
			$table->bigInteger('content_client_id')->nullable();
			$table->bigInteger('content_manager_user_id')->nullable();
			
			$table->date('atb_date')->nullable();
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			
			$table->string('ad_key')->nullable();
			
			$table->boolean('map_included')->default(0);
			$table->string('map_address1')->nullable();
			$table->string('map_address2')->nullable();
			
			$table->integer('action_id')->default(0);
			$table->string('action_params')->nullable();
			
			$table->boolean('text_enabled')->default(0);
			$table->boolean('audio_enabled')->default(0);
			$table->boolean('image_enabled')->default(0);
			$table->boolean('action_enabled')->default(0);
			
			$table->boolean('is_ready')->default(0);
			
			
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
		Schema::drop('airshr_connect_contents');
	}

}
