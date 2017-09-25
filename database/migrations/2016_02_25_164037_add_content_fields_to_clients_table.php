<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentFieldsToClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_clients', function(Blueprint $table)
		{
			$table->string('what')->nullable();
			$table->string('more')->nullable();
			$table->integer('action_id')->default(0);
			$table->string('action_params')->nullable();
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
			$table->dropColumn('what');
			$table->dropColumn('more');
			$table->dropColumn('action_id');
			$table->dropColumn('action_params');
		});
	}

}
