<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLyricfindAvailableToCoverartsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->string('lyricfind_available');
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
			$table->dropColumn('lyricfind_available');
		});
	}

}
