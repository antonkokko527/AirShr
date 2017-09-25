<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrCoverartTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_coverarts', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->string('who')->index();
			$table->string('what')->index();
			
			$table->string('coverart_url', 1024)->nullable();
			$table->string('itunes_url', 1024)->nullable();
			
			$table->string('artist')->nullable();
			$table->string('track')->nullable();
			$table->string('preview', 1024)->nullable();
			
			$table->text('lyrics')->nullable();
			
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
		Schema::drop('airshr_coverarts');
	}

}
