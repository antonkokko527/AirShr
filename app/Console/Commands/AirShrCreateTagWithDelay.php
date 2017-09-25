<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\MetaParsers\NovaParser;
use App\Tag;
use App\Station;

class AirShrCreateTagWithDelay extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:createtagwithdelay';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create tag with delay.';

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
			
			$stationName = $this->argument('station');
			$who = $this->argument('who');
			$what = $this->argument('what');
			$originalWho = $this->argument('original_who');
			$originalWhat = $this->argument('original_what');
			$adkey = $this->argument('adkey');
			$contentType = $this->argument('content_type');
			$prevTagId = $this->argument('prevtag_id');
			$tagDuration = $this->argument('tag_duration');
			$prevTalkSignal = $this->argument('prevtalk_signal');
			$checkReserve = $this->argument('check_reserve');
			
			if (empty($tagDuration)) $tagDuration = 0;
			
			if (!empty($prevTagId)) {		// check matching prev tag is still in play
				
				$prevTag = NovaParser::getPrevTag($stationName);
				
				if ($prevTag && $prevTag->id != $prevTagId) {
					return;
				}
				
			}
			
			if (!empty($prevTalkSignal)) {	// check matching prev talk signal is still on
				
				if (NovaParser::getPrevTalkSignal($stationName) != $prevTalkSignal) {
					return;
				}
			}
			
			
			if (!empty($checkReserve)) { // check reserved tag for creation
				
				$reserved = NovaParser::getReservedTag($checkReserve);
				
				if (empty($reserved)) return;
				
				if ($reserved['who'] != $who || $reserved['what'] != $what || $reserved['content_type'] != $contentType) return;

				// remove reserved one
				NovaParser::removeReservedTag($checkReserve);
			}
			
			$station = Station::getStationObjectByName($stationName);
			
			Tag::CreateManualTag($station->id, 0, $who, $what, $originalWho, $originalWhat, $adkey, $contentType, $stationName, $tagDuration, 0);
			
						
			
		} catch (\Exception $ex) {
			\Log::error($ex);
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
			['station', InputArgument::REQUIRED, 'station name'],
			['who', 	InputArgument::REQUIRED, 'who'],
			['what', 	InputArgument::REQUIRED, 'what'],
			['original_who', 	InputArgument::REQUIRED, 'original who'],
			['original_what', 	InputArgument::REQUIRED, 'original what'],
			['adkey', 	InputArgument::REQUIRED, 'ad key'],
			['content_type', 	InputArgument::REQUIRED, 'Content Type'],
			['tag_duration', 	InputArgument::OPTIONAL, 'Tag Duration'],
			['prevtag_id', 	InputArgument::OPTIONAL, 'Previous tag id'],
			['prevtalk_signal', 	InputArgument::OPTIONAL, 'Previous talk signal'],
			['check_reserve', 	InputArgument::OPTIONAL, 'Check if it is reserved for station'],
			
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
