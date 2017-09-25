<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldsForTakToAirshrContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->string('session_name')->nullable();
			$table->time('start_time')->nullable();
			$table->time('end_time')->nullable();
			$table->boolean('content_weekday_1')->default(0);
			$table->boolean('content_weekday_2')->default(0);
			$table->boolean('content_weekday_3')->default(0);
			$table->boolean('content_weekday_4')->default(0);
			$table->boolean('content_weekday_5')->default(0);
			$table->boolean('content_weekday_6')->default(0);
			$table->boolean('content_weekday_0')->default(0);
			$table->boolean('is_competition')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->dropColumn('session_name');
			$table->dropColumn('start_time');
			$table->dropColumn('end_time');
			$table->dropColumn('content_weekday_1');
			$table->dropColumn('content_weekday_2');
			$table->dropColumn('content_weekday_3');
			$table->dropColumn('content_weekday_4');
			$table->dropColumn('content_weekday_5');
			$table->dropColumn('content_weekday_6');
			$table->dropColumn('content_weekday_0');
			$table->dropColumn('is_competition');
		});
	}

}
