<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAtbDateToString extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\DB::statement("ALTER TABLE airshr_connect_contents MODIFY COLUMN atb_date varchar(255) DEFAULT NULL");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\DB::statement("ALTER TABLE airshr_connect_contents MODIFY COLUMN atb_date date DEFAULT NULL");
	}

}
