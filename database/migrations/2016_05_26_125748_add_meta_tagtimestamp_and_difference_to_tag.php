<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMetaTagtimestampAndDifferenceToTag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_tags', function(Blueprint $table)
		{
			$table->bigInteger('meta_tag_timestamp');
			$table->bigInteger('meta_tag_timestamp_diff');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_tags', function(Blueprint $table)
		{
			$table->dropColumn('meta_tag_timestamp');
			$table->dropColumn('meta_tag_timestamp_diff');
		});
	}

}
