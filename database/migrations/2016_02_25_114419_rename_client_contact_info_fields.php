<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameClientContactInfoFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_clients', function($table)
		{
			$table->renameColumn('content_contact', 'client_contact_name');
			$table->renameColumn('content_email', 'client_contact_email');
			$table->renameColumn('content_phone', 'client_contact_phone');
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
			$table->renameColumn('client_contact_name', 'content_contact');
			$table->renameColumn('client_contact_email', 'content_email');
			$table->renameColumn('client_contact_phone', 'content_phone');
		});
	}

}
