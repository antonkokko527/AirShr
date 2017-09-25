<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCandidateAdKeyToAttachment extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->string('candidate_adkey')->nullable()->index('candidate_adkey');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('airshr_connect_content_attachments', function($table)
		{
			$table->dropColumn('candidate_adkey');
				
		});
	}

}
