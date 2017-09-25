<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

        $this->call('StationLogoSeeder');
		// $this->call('UserTableSeeder');
	}

}

class StationLogoSeeder extends Seeder {

    public function run()
    {
        DB::table('airshr_stations')
            ->where('station_name', 'nova-1069-brisbane')
            ->update(['station_logo' => 'https://s3-ap-southeast-2.amazonaws.com/airshr/images/nova-1069-brisbane/Nova1069.png']);

        DB::table('airshr_stations')
            ->where('station_name', 'nova-969-sydney')
            ->update(['station_logo' => 'https://s3-ap-southeast-2.amazonaws.com/airshr/images/nova-969-sydney/Nova969.png']);
    }
}