<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectClientLookups extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_client_lookups', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			$table->char('zettaid', 32)->unique('zettaid');
			$table->char('ad_key')->nullable()->default(null);
			$table->char('client_name')->nullable()->default(null);
			$table->char('product')->nullable()->default(null);
			//$table->boolean('is_found')->default(false);
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
		Schema::drop('airshr_connect_client_lookups');		
	}

}
