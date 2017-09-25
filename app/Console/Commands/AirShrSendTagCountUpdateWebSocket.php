<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrSendTagCountUpdateWebSocket extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:sendtagcountupdatewebsocket';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send websocket message for tag count update.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		try {
			
			while (true) {
			
				// Tag count update websocket message
				$request = \DB::table("airshr_tag_count_update_requests")->first();
				
				if ($request) {

					\DB::table("airshr_tag_count_update_requests")->where("id", $request->id)->delete();
					
					$payload = array(
							'event' 	=> 'TAG_COUNT_UPDATE',
							'tag'		=> array('id' => $request->tag_id, 'count' => $request->count, 'station_id' => $request->station_id)
					);
						
					\DB::table('airshr_websocket_messages')->insert([
							'queue'		=> \Config::get('app.QueueForTagEventCountUpdate'),
							'payload'	=> json_encode($payload),
							'created_at'	=> time()
					 ]);

				}
				
				// Tag Vote count update websocket message
				$request = \DB::table("airshr_tag_vote_count_update_requests")->first();
				
				if ($request) {
				
					\DB::table("airshr_tag_vote_count_update_requests")->where("id", $request->id)->delete();
						
					$payload = array(
							'event' 	=> 'TAG_VOTE_COUNT_UPDATE',
							'tag'		=> array('id' => $request->tag_id, 'vote_option1_count' => $request->vote_option1_count, 'vote_option2_count' => $request->vote_option2_count,  'station_id' => $request->station_id)
					);
				
					\DB::table('airshr_websocket_messages')->insert([
							'queue'		=> \Config::get('app.QueueForTagEventCountUpdate'),
							'payload'	=> json_encode($payload),
							'created_at'	=> time()
					]);
				
				}
				
			
				unset($request);
						
				sleep(1);
			}
			
		} catch (\Exception $ex) {
			$this->error($ex);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
		
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			
		];
	}

}
