<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrconnectContentActionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_content_actions', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->enum('action_type', ['book', 'call', 'claim', 'get', 'website', 'contact'])->default('book');
			$table->string('action_label');
			
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
		Schema::drop('airshr_connect_content_actions');
	}

}
