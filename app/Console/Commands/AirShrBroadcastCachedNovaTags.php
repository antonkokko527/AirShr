<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Station;
use App\Tag;
use App\WebSocketPub;

class AirShrBroadcastCachedNovaTags extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:broadcastcachednovatags';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Broadcast cached Nova sydney tags to Brisbane.';

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

			$novaSydneyStation = Station::getStationObjectByName("nova-969-sydney");
			$novaBrisbaneStation = Station::getStationObjectByName("nova-1069-brisbane");
			$tagger = 0;
			
			if ($novaSydneyStation && $novaBrisbaneStation) {
				
				while(true) {
					
					$timeZoneDifference = getOffsetBetweenTimezones('Australia/Brisbane', 'Australia/Sydney');
					$daylightSaving = $timeZoneDifference < 0 ? true : false;
					
					$currentHourInBrisbaneTimezone = getCurrentTimeInTimezone('H', 'Australia/Brisbane');
					$currentDayOfWeekInBrisbane = getCurrentTimeInTimezone('N', 'Australia/Brisbane');
					$switchingPeriod = ((($currentHourInBrisbaneTimezone >= 16 || $currentHourInBrisbaneTimezone <= 4) && ($currentDayOfWeekInBrisbane >= 1 && $currentDayOfWeekInBrisbane <= 5)) || ($currentDayOfWeekInBrisbane == 6 && $currentHourInBrisbaneTimezone <= 5) || ($currentDayOfWeekInBrisbane == 7 && $currentHourInBrisbaneTimezone >= 13)) ? true : false;
					
					try {
	
						if ($daylightSaving && $switchingPeriod) {
							
							
							$currentTimestampms = getCurrentMilisecondsTimestamp();
							$fetchTimestampms = $currentTimestampms + $timeZoneDifference * 1000;
							
							$cacheInfo = \DB::table('airshr_cached_tags')->where('broadcasted', '=', '0')
																		 ->where('station_id', '=', $novaSydneyStation->id)
																		 ->where('tag_timestamp', '<=', $fetchTimestampms)
																		 ->where('tag_timestamp', '>=', $fetchTimestampms - 10 * 60 * 1000) // within 10 mins threshold
																		 ->orderBy('tag_timestamp', 'desc')
																		 ->first();
							
							
							if ($cacheInfo) {
								
								\DB::table('airshr_cached_tags')->where('id', $cacheInfo->id)->update(['broadcasted' => 1]);
								
								$tag = Tag::findOrFail($cacheInfo->tag_id);
								
								$tagTimestamp = $cacheInfo->tag_timestamp - $timeZoneDifference * 1000;
								
								$newTag = Tag::create([
										'tagger_id' 			=> $tagger,
										'station_id'			=> $novaBrisbaneStation->id,
										'content_type_id'		=> $tag->content_type_id,
										'tag_timestamp'			=> $tagTimestamp,
										'who'					=> $tag->original_who,
										'what'					=> $tag->original_what,
										'adkey'					=> $tag->adkey,
										'is_valid'				=> 1,
										'insert_timestamp'		=> $currentTimestampms,
										'insert_lag'			=> $tag->insert_lag,
										'connect_content_id'	=> 0,
										'coverart_id'			=> $tag->coverart_id,
										'tag_duration'			=> $tag->tag_duration,
										'cart'					=> $tag->cart,
										'original_who'			=> $tag->original_who,
										'original_what'			=> $tag->original_what
										]);
									
								$newTag->findConnectContentForTag();
								
								$newTag->applyForPreviousTagCompetitionGeneration();
								
								$newTag->generateTrimmedAudioForPreviousTag();
								
								$newTag->storeVoteRelatedTags();
								
								/*WebSocketPub::publishPushMessage(array(
									'event' 	=> 'NEWTAG',
									'tag'		=> $newTag->getArrayDataForOnAir()
								));*/
								
								WebSocketPub::publishPushMessageOnQueue(array(
									'event' 	=> 'NEWTAG',
									'tag'		=> $newTag->getArrayDataForOnAir()
								), \Config::get('app.QueueForNewTag'));
								
								$this->info("Tag Broadcasted: " . json_encode($newTag->getArrayDataForOnAir()));
							}
							
						}
						
						
					} catch(\Exception $ex2) {
						\Log::error($ex2);
						$this->error($ex2->getMessage());
					}
					
					sleep(1);
					
					unset($timeZoneDifference);
					unset($daylightSaving);
					unset($currentHourInBrisbaneTimezone);
					unset($currentDayOfWeekInBrisbane);
					unset($switchingPeriod);
					
					unset($currentTimestampms);
					unset($fetchTimestampms);
					unset($cacheInfo);
					
					unset($tag);
					unset($newTag);
					unset($tagTimestamp);
				}
			
			}
				
		} catch (\Exception $ex){
			\Log::error($ex);
			$this->error($ex->getMessage());
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
