<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrevTagsToEvent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_events', function($table)
		{
			$table->bigInteger('prev_content_type_id')->default(0);
			$table->string('prev_content_type')->nullable();
			
			$table->bigInteger('prev_tag_id')->default(0);
			
			$table->string('prev_who')->nullable();
			$table->string('prev_what')->nullable();
			$table->string('prev_how')->nullable();
			
			$table->string('prev_adkey')->nullable();
			$table->string('prev_logo_file')->nullable();
			$table->string('prev_screen_file')->nullable();
			$table->string('prev_audio_file')->nullable();
			$table->string('prev_coverart_url')->nullable();
			$table->string('prev_itunes_url')->nullable();
			
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
			$table->dropColumn('prev_content_type_id');
			$table->dropColumn('prev_content_type');
			$table->dropColumn('prev_tag_id');
			$table->dropColumn('prev_who');
			$table->dropColumn('prev_what');
			$table->dropColumn('prev_how');
			$table->dropColumn('prev_adkey');
			$table->dropColumn('prev_logo_file');
			$table->dropColumn('prev_screen_file');
			$table->dropColumn('prev_audio_file');
			$table->dropColumn('prev_coverart_url');
			$table->dropColumn('prev_itunes_url');
		});
	}

}
