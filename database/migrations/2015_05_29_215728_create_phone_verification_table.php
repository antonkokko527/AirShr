<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneVerificationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_phone_verifications', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->string('countrycode', 5);
			$table->string('phone_number', 30);
			$table->string('verification_code', 4);
			$table->string('msg_id', 20)->nullable();
			
			$table->boolean('is_valid')->default(1);
			
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
		Schema::drop('airshr_phone_verifications');
	}

}
