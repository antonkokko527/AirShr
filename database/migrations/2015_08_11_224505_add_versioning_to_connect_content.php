<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersioningToConnectContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->enum('content_version', ['new', 'add_rotation', 'extend_existing', 'replace_existing', 'repeat_material', 'amended_details'])->default('new');
			$table->bigInteger('content_original_version_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->dropColumn('content_version');
			$table->dropColumn('content_original_version_id');
		});
	}

}
