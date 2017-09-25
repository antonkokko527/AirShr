<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgencyContactDetails extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_agencies', function($table)
		{
			$table->string('agency_contact_name')->nullable();
			$table->string('agency_contact_phone')->nullable();
			$table->string('agency_contact_email')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_agencies', function($table)
		{
			$table->dropColumn('agency_contact_name');
			$table->dropColumn('agency_contact_phone');
			$table->dropColumn('agency_contact_email');
		});
	}

}
