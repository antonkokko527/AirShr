<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppVersionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_app_versions', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->string('app_version', 10);
			$table->bigInteger('app_version_num');
			$table->enum('app_device_type', ['iOS', 'Android']);
			$table->string('description')->nullable();
			$table->string('update_link', 255);
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
		Schema::drop('airshr_app_versions');
	}

}
