<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItunesSampleToEventAndTag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->string('itunes_sample_url')->nullable();
		});
		
		Schema::table('airshr_events', function($table)
		{
			$table->string('itunes_sample_url')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->dropColumn('itunes_sample_url');
			
		});
		
		Schema::table('airshr_events', function($table)
		{
			$table->dropColumn('itunes_sample_url');
		});
	}

}
