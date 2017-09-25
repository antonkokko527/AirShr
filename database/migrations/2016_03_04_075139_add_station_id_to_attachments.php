<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStationIdToAttachments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->bigInteger('station_id')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->dropColumn('station_id');
		});
	}

}
