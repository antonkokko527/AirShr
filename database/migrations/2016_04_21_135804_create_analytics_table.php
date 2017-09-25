<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalyticsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::create('airshr_analytics', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->integer('type');
			$table->text('data');
			$table->integer('start_time');
			$table->integer('end_time');
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
		//
		Schema::drop('airshr_analytics');
	}

}
