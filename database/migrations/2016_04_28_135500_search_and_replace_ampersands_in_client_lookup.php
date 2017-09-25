<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SearchAndReplaceAmpersandsInClientLookup extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$clientlookups = DB::table('airshr_connect_client_lookups')->where('client_name', 'like', '%&amp;%')->get();

		foreach( $clientlookups as $clientlookup ) {
			$clientname = str_replace('&amp;', '&', $clientlookup->client_name);

			DB::table('airshr_connect_client_lookups')
			  ->where('id', $clientlookup->id)
			  ->update(['client_name' => $clientname])
			;
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}

}
