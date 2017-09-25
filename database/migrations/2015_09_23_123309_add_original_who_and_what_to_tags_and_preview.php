<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalWhoAndWhatToTagsAndPreview extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->string('original_who')->nullable();
			$table->string('original_what')->nullable();
		});
		
		Schema::table('airshr_preview_tags', function($table)
		{
			$table->string('original_who')->nullable();
			$table->string('original_what')->nullable();
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
			$table->dropColumn('original_who');
			$table->dropColumn('original_what');
		});
		
		Schema::table('airshr_preview_tags', function($table)
		{
			$table->dropColumn('original_who');
			$table->dropColumn('original_what');
		});
	}

}
