<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Tag;
use App\WebSocketPub;

class AirShrUpdateTagVoteOptionCount extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:updatetagvoteoptioncount';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update tag vote option count and send websocket push notification.';

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
			$voteSelection = $this->argument('voteOption');
			
			$tag = Tag::findOrFail($tagid);
			
			if ($voteSelection == 1) {
				$tag->vote_option1_count = $tag->vote_option1_count + 1; 
			} else if ($voteSelection == 2) {
				$tag->vote_option2_count = $tag->vote_option2_count + 1;
			}
						
			$tag->save();
				
			/*$tagCountRequest = \DB::table('airshr_tag_vote_count_update_requests')->where('tag_id', $tag->id)->first();
				
			if ($tagCountRequest) {
				\DB::table('airshr_tag_vote_count_update_requests')->where('tag_id', $tag->id)->update(['vote_option1_count' => $tag->vote_option1_count, 'vote_option2_count' => $tag->vote_option2_count]);
			} else {
				\DB::table('airshr_tag_vote_count_update_requests')->insert([
						'tag_id'		=> $tag->id,
						'station_id'	=> $tag->station_id,
						'vote_option1_count' => $tag->vote_option1_count,
						'vote_option2_count' => $tag->vote_option2_count
						]);
			}*/
			
			$payload = array(
					'event' 	=> 'TAG_VOTE_COUNT_UPDATE',
					'tag'		=> array('id' => $tag->id, 'vote_option1_count' => $tag->vote_option1_count, 'vote_option2_count' => $tag->vote_option2_count,  'station_id' => $tag->station_id)
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
			['tagid', 			InputArgument::REQUIRED, 'Tag ID'],
			['voteOption', 		InputArgument::REQUIRED, 'Vote option']
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
