<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWidthHeightExtraToConnectAttachment extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->integer('width')->nullable();
			$table->integer('height')->nullable();
			
			$table->string('extra', 1024)->nullable();
				
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
			$table->dropColumn('width');
			$table->dropColumn('height');
			
			$table->dropColumn('extra');
				
		});
	}

}
