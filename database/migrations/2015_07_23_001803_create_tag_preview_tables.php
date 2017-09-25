<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagPreviewTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_preview_logs', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->bigInteger('station_id');
			$table->date('preview_date');
			
			$table->enum('status', ['processing', 'completed', 'error']);
			$table->string('reason')->nullable();
			$table->string('file_path')->nullable();
			$table->bigInteger('file_lastmtime')->nullable();
			
			$table->timestamps();
			$table->softDeletes();
		});
		
		Schema::create('airshr_preview_tags', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->bigInteger('station_id');
			$table->bigInteger('content_type_id');
			$table->bigInteger('tag_timestamp')->index();
			
			$table->string('who')->nullable();
			$table->string('what')->nullable();
			$table->string('adkey')->nullable();
		
			$table->bigInteger('connect_content_id')->default(0);
			$table->bigInteger('coverart_id')->default(0);
			$table->integer('tag_duration')->default(0);
			$table->string('cart')->nullable();
						
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
		Schema::drop('airshr_preview_logs');
		Schema::drop('airshr_preview_tags');
	}

}
