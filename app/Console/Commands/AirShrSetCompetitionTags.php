<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Station;
use App\ContentType;

class AirShrSetCompetitionTags extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:setcompetitiontags';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sets the is_competition flag to true for all competition tags and its surrounding tags';

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
		set_time_limit(0);
		
		try {
			
			foreach (Station::all() as $station) {
			
				if ($station->is_private) continue;		// only for public station
				if (!$station->airshr_enabled) continue;	// if airshr is not enabled, skip
					
				$this->info("Setting competiton flag for station " . $station->station_abbrev . " has started.");
					
				//Get the competition tags that haven't been marked with is_competition = 1 yet
				$competition_tags = \DB::table('airshr_tags')
										->where('competition_result_generated', '<>', '0')
										->where('station_id', '=', $station->id)
										->where('is_competition', '=', '0');
				
				$competition_tags->update(array('is_competition' => 1));
				$competition_tags = $competition_tags->get();
				
				foreach ($competition_tags as $competition_tag) {
					//Get the previous 2 tags that happened before the competition
					$previousTags = \DB::table('airshr_tags')
										->where('station_id', '=', $station->id)
										->where('tag_timestamp', '<', $competition_tag->tag_timestamp)
										->where('event_count', '<>', '0')
										->where('id', '<>', $competition_tag->id)
										->orderBy('tag_timestamp', 'desc')
										->take(2)
										->update(array('is_competition' => 1));
				
					//Try to find the next talk tag that happened within 10 minutes after the competition
					$nextTalkTag = \DB::table('airshr_tags')//Find the next talk tag that happened within 10 minutes after the competition
										->where('station_id', '=', $station->id)
										->where('tag_timestamp', '>', $competition_tag->tag_timestamp)
										->whereRaw("(tag_timestamp - {$competition_tag->tag_timestamp}) < (10 * 60 * 1000)")
										->where('content_type_id', '=', ContentType::GetTalkContentTypeID())
										->orderBy('tag_timestamp', 'asc')
										->first();
				
					//Get the tags that happened after the competetion
					if ($nextTalkTag) { //If that talk tag exists, get all the tags between the competition and the next talk tag
						$nextTags = \DB::table('airshr_tags')
										->where('station_id', '=', $station->id)
										->where('tag_timestamp', '>', $competition_tag->tag_timestamp)
										->where('tag_timestamp', '<=', $nextTalkTag->tag_timestamp)
										->orderBy('tag_timestamp', 'asc')
										->update(array('is_competition' => 1));
					} else { //Otherwise, get all the tags that happened within 10 minutes after competition
						$nextTags = \DB::table('airshr_tags')
										->where('station_id', '=', $station->id)
										->where('tag_timestamp', '>', $competition_tag->tag_timestamp)
										->whereRaw("(tag_timestamp - {$competition_tag->tag_timestamp}) < (10 * 60 * 1000)")
										->orderBy('tag_timestamp', 'asc')
										->update(array('is_competition' => 1));
					}
				
				}
					
				$this->info("Setting competiton flag for station " . $station->station_abbrev . " has ended.");
			}
			
			
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
