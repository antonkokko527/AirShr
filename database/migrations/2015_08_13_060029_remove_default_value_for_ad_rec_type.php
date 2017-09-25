<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDefaultValueForAdRecType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement("ALTER TABLE airshr_connect_contents CHANGE COLUMN content_rec_type content_rec_type ENUM('live', 'rec', 'sim_live')");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\DB::statement("ALTER TABLE airshr_connect_contents CHANGE COLUMN content_rec_type content_rec_type ENUM('live', 'rec', 'sim_live') DEFAULT 'rec'");
	}

}
