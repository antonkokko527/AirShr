<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHashtagRenderUrlToTagTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function($table)
		{
			$table->string('hash', 5)->nullable()->index();
			$table->string('render_url')->nullable();
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
			$table->dropColumn('hash');
			$table->dropColumn('render_url');
		});
	}

}
