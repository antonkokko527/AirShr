<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItunesArtistTitleToCoverartsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
            $table->string('itunes_artist');
            $table->string('itunes_title');
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
            $table->dropColumn('itunes_artist');
            $table->dropColumn('itunes_title');
		});
	}

}
