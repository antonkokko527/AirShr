<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcrDebugDetailsToEvents extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_events', function(Blueprint $table)
		{
			$table->boolean('acr_recognition_success')->default(0);
			$table->bigInteger('acr_response_duration')->default(0);
			$table->text('acr_response_content')->nullable();
			$table->string('acr_channel_selected')->nullable();
			$table->string('acr_music_selected')->nullable();
			$table->bigInteger('acr_tag_timestamp')->default(0);	
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_events', function(Blueprint $table)
		{
			$table->dropColumn('acr_recognition_success');
			$table->dropColumn('acr_response_duration');
			$table->dropColumn('acr_response_content');
			$table->dropColumn('acr_channel_selected');
			$table->dropColumn('acr_music_selected');
			$table->dropColumn('acr_tag_timestamp');
		});
	}

}
