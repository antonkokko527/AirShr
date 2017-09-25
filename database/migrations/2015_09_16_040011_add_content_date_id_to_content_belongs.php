<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContentDateIdToContentBelongs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_belongs', function($table)
		{
			$table->bigInteger('child_content_date_id')->default(0);
			$table->dropUnique('belongs_unique');
			$table->unique(['parent_content_id', 'child_content_id', 'child_content_date_id'], 'belongs_date_unique');
			
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
			$table->dropColumn('child_content_date_id');
			$table->unique(['parent_content_id', 'child_content_id'], 'belongs_unique');
			$table->dropUnique('belongs_date_unique');
		});
	}

}
