<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirshrRemoteTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('airshr_remotes', function(Blueprint $table)
		{
			$table->bigIncrements('id')->unsigned();
			
			$table->string('model_number', 30);
			$table->string('serial_number', 50)->unique('serial_number');
			$table->string('last_digits', 4)->nullable();
			$table->string('remote_name', 50)->nullable();
			$table->date('purchase_date')->nullable();
			$table->enum('purchase_method', ['Free'])->default('Free');
			$table->string('purpose')->nullable();
			
			$table->float('initial_voltage')->nullable();
			$table->dateTime('install_date')->nullable();
									
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
		Schema::drop('airshr_remotes');
	}

}
