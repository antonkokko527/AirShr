<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreCropInfoToContentAttachments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->string('original_saved_name');
			$table->string('original_saved_path');
			$table->text('original_moreinfo');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->dropColumn('original_saved_name');
			$table->dropColumn('original_saved_path');
			$table->dropColumn('original_moreinfo');
		});
	}

}
