<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebsocketMessageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_websocket_messages', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('queue');
			$table->text('payload');
			$table->bigInteger('station_id')->default(0);
			$table->unsignedInteger('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_websocket_messages');
	}

}
