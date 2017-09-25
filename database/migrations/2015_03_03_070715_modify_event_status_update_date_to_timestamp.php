<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyEventStatusUpdateDateToTimestamp extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement('ALTER TABLE airshr_events MODIFY COLUMN event_data_status_updateon bigint(20)');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\DB::statement('ALTER TABLE airshr_events MODIFY COLUMN event_data_status_updateon timestamp');
	}

}
