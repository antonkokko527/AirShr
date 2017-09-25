<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdRecTypePercentToContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->bigInteger('content_parent_id')->nullable();
			$table->enum('content_rec_type', ['live', 'rec'])->default('rec');
			$table->float('content_percent')->nullable();
			
			$table->boolean('ready_to_print')->default(0);
			$table->boolean('is_temp')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->dropColumn('content_parent_id');
			$table->dropColumn('content_rec_type');
			$table->dropColumn('content_percent');
			
			$table->dropColumn('ready_to_print');
			$table->dropColumn('is_temp');
		});
	}

}
