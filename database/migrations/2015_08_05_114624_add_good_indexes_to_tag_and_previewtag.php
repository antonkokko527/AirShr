<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoodIndexesToTagAndPreviewtag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->index('adkey');
			$table->index('connect_content_id');
		});
		
		Schema::table('airshr_preview_tags', function($table)
		{
			$table->index('adkey');
			$table->index('connect_content_id');
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
			$table->dropIndex('airshr_tags_adkey_index');
			$table->dropIndex('airshr_tags_connect_content_id_index');
		});
		
		Schema::table('airshr_preview_tags', function($table)
		{
			$table->dropIndex('airshr_preview_tags_adkey_index');
			$table->dropIndex('airshr_preview_tags_connect_content_id_index');
		});
	}

}
