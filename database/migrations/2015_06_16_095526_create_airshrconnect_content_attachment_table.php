<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrconnectContentAttachmentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_content_attachments', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
				
			$table->bigInteger('content_id');

			$table->enum('type', ['image', 'video', 'logo', 'audio'])->default('image');
			
			$table->string('filename');
			$table->string('saved_name');
			$table->string('saved_path');
				
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
		Schema::drop('airshr_connect_content_attachments');
	}

}
