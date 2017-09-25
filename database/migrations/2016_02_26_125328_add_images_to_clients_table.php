<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImagesToClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_clients', function(Blueprint $table)
		{
			$table->bigInteger('image_attachment1_id');
			$table->bigInteger('image_attachment2_id');
			$table->bigInteger('image_attachment3_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_clients', function(Blueprint $table)
		{
			$table->dropColumn('image_attachment1_id');
			$table->dropColumn('image_attachment2_id');
			$table->dropColumn('image_attachment3_id');
		});
	}

}
