<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhonesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_phones', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
				
			$table->bigInteger('user_id')->index();
			$table->string('phone_model')->index();
			$table->string('phone_os')->index();
				
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
		Schema::drop('airshr_phones');
	}

}
