<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleItunesReadyAvailableToCoverarts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->tinyInteger('itunes_ready')->default(1);
			$table->tinyInteger('google_ready')->default(1);
			$table->tinyInteger('itunes_available')->default(1);
			$table->tinyInteger('google_available')->default(1);
			$table->string('google_artist');
			$table->string('google_title');
			$table->string('asset_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->dropColumn('itunes_ready');
			$table->dropColumn('google_ready');
			$table->dropColumn('itunes_available');
			$table->dropColumn('google_available');
			$table->dropColumn('google_artist');
			$table->dropColumn('google_title');
			$table->dropColumn('asset_id');

		});
	}

}
