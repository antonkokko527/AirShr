<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentParentRelationshipTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_connect_content_belongs', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
	
			$table->bigInteger('parent_content_id');
			$table->bigInteger('child_content_id');
		
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('airshr_connect_content_belongs');
	}

}
