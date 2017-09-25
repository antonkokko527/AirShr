<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientTypeToClientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_clients', function(Blueprint $table)
		{
			$table->enum('client_type', ['direct', 'agency']);
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
			$table->dropColumn('client_type');
		});
	}

}
