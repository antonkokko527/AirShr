<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirewareTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_firmwares', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->string('firmware_version', 10);
			$table->bigInteger('firmware_version_num');
			$table->string('min_app_version', 10);
			$table->string('description')->nullable();
			$table->string('firmware_file_ios', 255);
			$table->string('firmware_file_android', 255);
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
		Schema::drop('airshr_firmwares');
	}

}
