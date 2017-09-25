<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectContentProductAccountAgency extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_content_products', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('station_id');
			
			$table->string('product_name');
			
			$table->timestamps();
			$table->softDeletes();
		});
		
		Schema::create('airshr_connect_content_executives', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('station_id');
				
			$table->string('executive_name');
				
			$table->timestamps();
			$table->softDeletes();
		});
		
		Schema::create('airshr_connect_content_agencies', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
		
			$table->bigInteger('station_id');
		
			$table->string('agency_name');
		
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_connect_content_products');
		Schema::drop('airshr_connect_content_executives');
		Schema::drop('airshr_connect_content_agencies');
	}

}
