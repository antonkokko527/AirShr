<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreDiagnoInfoToEventTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->bigInteger('app_request_received_on')->default(0);
			$table->bigInteger('sqs_sent_on')->default(0);
			$table->bigInteger('sqs_response_sent_on')->default(0);
			$table->bigInteger('sqs_processed_on')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->dropColumn('app_request_received_on');
			$table->dropColumn('sqs_sent_on');
			$table->dropColumn('sqs_response_sent_on');
			$table->dropColumn('sqs_processed_on');
		});
	}

}
