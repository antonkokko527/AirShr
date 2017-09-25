<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductAgencyOthersToConnectContent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_contents', function($table)
		{
			$table->bigInteger('content_product_id')->nullable();
			$table->integer('content_line_number')->nullable();
			
			$table->string('content_contact')->nullable();
			$table->string('content_email')->nullable();
			$table->string('content_phone')->nullable();
			$table->string('content_instructions')->nullable();
			$table->string('content_voices')->nullable();
			
			$table->bigInteger('content_agency_id')->nullable();
				
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
			$table->dropColumn('content_product_id');
			$table->dropColumn('content_line_number');
			
			$table->dropColumn('content_contact');
			$table->dropColumn('content_email');
			$table->dropColumn('content_phone');
			$table->dropColumn('content_instructions');
			$table->dropColumn('content_voices');
			
			$table->dropColumn('content_agency_id');
			
		});
	}

}
