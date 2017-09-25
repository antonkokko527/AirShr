<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreInfoToClients extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_clients', function($table)
		{
			$table->bigInteger('product_id');
			
			$table->string('content_contact')->nullable();
			$table->string('content_email')->nullable();
			$table->string('content_phone')->nullable();
		
			$table->string('who')->nullable();
			$table->string('map_address1')->nullable();
			$table->string('map_address1_lat')->nullable();
			$table->string('map_address1_lng')->nullable();
			
			$table->bigInteger('content_agency_id')->nullable();
			$table->bigInteger('content_manager_user_id')->nullable();
			
			$table->bigInteger('logo_attachment_id')->nullable();
			
			$table->boolean('is_ready')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_clients', function($table)
		{
			$table->dropColumn('product_id');
			
			$table->dropColumn('content_contact');
			$table->dropColumn('content_email');
			$table->dropColumn('content_phone');
			
			$table->dropColumn('who');
			$table->dropColumn('map_address1');
			$table->dropColumn('map_address1_lat');
			$table->dropColumn('map_address1_lng');
			
			$table->dropColumn('content_agency_id');
			$table->dropColumn('content_manager_user_id');
			
			$table->dropColumn('is_ready');
			
		});
	}

}
