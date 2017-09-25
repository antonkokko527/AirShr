<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLngLngAndMoreToEventTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->string('event_lat', 20)->nullable();
			$table->string('event_lng', 20)->nullable();
				
			$table->string('remote_name', 50)->nullable();
			$table->float('remote_voltage')->nullable();
			
			$table->string('phone_model')->nullable();
			$table->string('phone_os')->nullable();
				
		});
		
		
		Schema::table('airshr_remotes', function($table)
		{
			$table->boolean('installed')->default(0);
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
			$table->dropColumn('event_lat');
			$table->dropColumn('event_lng');
			
			$table->dropColumn('remote_name');
			$table->dropColumn('remote_voltage');
			
			$table->dropColumn('phone_model');
			$table->dropColumn('phone_os');
		
		});
		
		Schema::table('airshr_remotes', function($table)
		{
			$table->dropColumn('installed');
		});
	}

}
