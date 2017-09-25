<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_regions', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			
			$table->string('region')->unique('region');
			$table->string('state')->nullable();
			
			$table->string('center_lat', 20)->nullable();
			$table->string('center_lng', 20)->nullable();
			$table->float('radius')->nullable();
			
			$table->boolean('metro_region')->default(0);
			
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
		
		Schema::drop('airshr_regions');
	}

}
