<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Tag;
use App\WebSocketPub;

class AirShrUpdateTagEventCount extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:updatetageventcount';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update tag event count and send websocket push notification.';

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
		
			$tagid = $this->argument('tagid');
			
			$tag = Tag::findOrFail($tagid);
			$tag->event_count = $tag->event_count + 1;
			$tag->save();
			
			/*$this->call('airshr:pushwebsocketmessage2', ['payload' => json_encode(array(
					'event' 	=> 'TAG_COUNT_UPDATE',
					'tag'		=> array('id' => $tag->id, 'count' => $tag->event_count, 'station_id' => $tag->station_id)
			))]);*/
			
			/*$payload = array(
					'event' 	=> 'TAG_COUNT_UPDATE',
					'tag'		=> array('id' => $tag->id, 'count' => $tag->event_count, 'station_id' => $tag->station_id)
			);
			
			\DB::table('airshr_websocket_messages')->insert([
				'queue'		=> \Config::get('app.QueueForTagEventCountUpdate'),
				'payload'	=> json_encode($payload),
				'created_at'	=> time()
			]);*/
			
			/*$tagCountRequest = \DB::table('airshr_tag_count_update_requests')->where('tag_id', $tag->id)->first();
			
			if ($tagCountRequest) {
				\DB::table('airshr_tag_count_update_requests')->where('tag_id', $tag->id)->update(['count' => $tag->event_count]);
			} else {
				\DB::table('airshr_tag_count_update_requests')->insert([
							'tag_id'		=> $tag->id,
							'station_id'	=> $tag->station_id,
							'count'			=> $tag->event_count
						]);
			}*/
			
			
			
			$payload = array(
					'event' 	=> 'TAG_COUNT_UPDATE',
					'tag'		=> array('id' => $tag->id, 'count' => $tag->event_count, 'station_id' => $tag->station_id)
			);
			
			WebSocketPub::publishPushMessage($payload);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
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
			['tagid', InputArgument::REQUIRED, 'Tag ID'],
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
