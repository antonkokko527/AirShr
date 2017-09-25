<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsFieldsToCoverart extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->bigInteger('attachment1')->default(0);
			$table->bigInteger('attachment2')->default(0);
			$table->bigInteger('attachment3')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_coverarts', function(Blueprint $table)
		{
			$table->dropColumn('attachment1');
			$table->dropColumn('attachment2');
			$table->dropColumn('attachment3');
		});
	}

}
