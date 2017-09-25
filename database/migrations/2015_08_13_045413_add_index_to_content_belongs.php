<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToContentBelongs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_belongs', function($table)
		{
			$table->unique(['parent_content_id', 'child_content_id'], 'belongs_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_belongs', function($table)
		{
			$table->dropUnique('belongs_unique');
		});
	}

}
