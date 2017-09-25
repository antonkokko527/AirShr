<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRatingAndVersionToEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->enum('rate_option', ['hate', 'like', 'love', 'no_rate'])->default('no_rate');
			$table->bigInteger('rate_timestamp')->default(0);
			$table->string('app_version', 10)->nullable();
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->dropColumn('rate_option');
			$table->dropColumn('rate_timestamp');
			$table->dropColumn('app_version');
		});
	}

}
