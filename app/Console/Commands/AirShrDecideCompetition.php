<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Setting;
use App\Event;
use App\User;
use App\ConnectContent;
use App\Station;
use App\ContentType;
use App\Tag;
use App\Competition;

class AirShrDecideCompetition extends Command {

	public static $COMPETITION_CHECKTIME_AFTER_ENDOFTAG = 20;
	public static $COMPETITION_WINNER_COUNT	= 15;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:decidecompetition';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate Competition Result.';

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
		
		$this->info("Competition decide process started.");
		
		try {
			
			foreach (Station::all() as $station) {

				if ($station->is_private) continue;		// only for public station

				$this->info("Station: {$station->station_short}");
				
				$currentTag = null;
				$now = getCurrentMilisecondsTimestamp();
				
				$last2Tags = Tag::getMostRecentTagByTimestamp($station->id, $now);
				
				if ($last2Tags['current']) {
					$currentTag = $last2Tags['current'];
				}
				
				if (!$currentTag) {
					$this->info("No current tag found. Skipping...");
					continue;
				}
				
				// load last check timestamp
				$lastTimestamp = Setting::getSettingVal('competition_check_timestamp_' . $station->id, null);
				if (!$lastTimestamp) {
					$lastTimestamp = $now;
					Setting::setSettingVal('competition_check_timestamp_' . $station->id, $lastTimestamp);
				}
					
				$this->info("Last Timestamp: " . $lastTimestamp);
								
				$tags = Tag::where('tag_timestamp', '>', $lastTimestamp)
							->where('station_id', '=', $station->id)
							->where('is_valid', '=', 1)
							->where('id', '<', $currentTag->id)
							->orderBy('tag_timestamp', 'asc')
							->with('events.userForEvent')
							->with('connectContent')
							->get();
				
				for ($i = 0; $i < count($tags); $i++) {
					$tag = $tags[$i];
					
					$this->info("Checking tag {$tag->id}: [{$tag->who}], [{$tag->what}]");
					
					$tagStartTimestamp = $tag->tag_timestamp;
					if ($i < count($tags) - 1) {
						$tagEndTimestamp = $tags[$i+1]->tag_timestamp;
					} else {
						$tagEndTimestamp = $currentTag->tag_timestamp;
					}
					
					// not old for predefined duration, skip
					if ($now - $tagEndTimestamp < self::$COMPETITION_CHECKTIME_AFTER_ENDOFTAG  * 1000) {
						$this->info("No more old tags. Breaking...");
						break;
					}
					
					$connectContent = $tag->connectContent;
					
					if ($tag->content_type_id == ContentType::findContentTypeIDByName("Talk") && $connectContent && $connectContent->is_ready && $connectContent->is_competition) {   // competition?
						
						$this->info("This is competition.");
						
						$eventsForTag = $tag->events;
						
						$users = array();
						
						foreach ($eventsForTag as $event) {
							
							if (empty($event->record_file)) continue; // from timemachine, continue
							
							$userForEvent = $event->userForEvent;
							
							if (!$userForEvent) continue;		// no user for this event, continue
							
							if (!isset($users[$userForEvent->id])) {
								$this->info("Adding user: +{$userForEvent->countrycode} {$userForEvent->phone_number}");
								$users[$userForEvent->id] = $userForEvent;
							}
						}
						
						$this->info(count($users) . " users for this tag event.");
						
						$pickCount = min(self::$COMPETITION_WINNER_COUNT, count($users));
						
						$this->info("Pick count: " . $pickCount);
						
						$pickedUserIdArray = array();
						$pickedUserPhoneNumArray = array();
						
						if ($pickCount > 0) {
							
							// pick random users
							$pickedUsersKeys = array_rand($users, $pickCount);
							
							if (!is_array($pickedUsersKeys)) {
								$pickedUsersKeys = array($pickedUsersKeys);
							}
							
							$pickedUserPhoneNumbers = "";
							$i = 1;
							foreach($pickedUsersKeys as $key) {
								$pickedUser = $users[$key];
								//$pickedUserPhoneNumbers .= "{$i}. {$pickedUser->countrycode} {$pickedUser->phone_number} <br/>";
								$pickedUserPhoneNumbers .= "{$i}. {$pickedUser->phone_number} <br/>";
								$pickedUserIdArray[] = $pickedUser->id;
								//$pickedUserPhoneNumArray[] = "{$pickedUser->countrycode} {$pickedUser->phone_number}";
								$pickedUserPhoneNumArray[] = "{$pickedUser->phone_number}";
								$i++; 
							}
							
							$data = array(
								'competitionDateTime' => date("H:i:s d.m.Y", getSecondsFromMili($tagStartTimestamp)),
								'total_applicants'	=> count($users),
								'pick_count' => $pickCount,
								'user_list' => $pickedUserPhoneNumbers		
							);
							
							$this->info("User list: " . $pickedUserPhoneNumbers);
							$this->info("Sent email for competition.");
							
							\Mail::queueOn(\Config::get('app.QueueForEmailAndSMS'), 'emails.competition', $data, function($message)
							{
								$message->from('connect@airshr.net', 'AirShr Connect')										
										->to('dev@airshr.com.au', 'AirShr Dev')
										->cc(['travis.winks@yahoo.com', 'deano@wavefm.com.au', 'deanokesby@gmail.com'])
										->bcc(['dollah.singh.dev@gmail.com'])
										->subject("Competition Result");
							});
							
						} else {				// send mail also - empty user
							
							$data = array(
									'competitionDateTime' => date("H:i:s d.m.Y", getSecondsFromMili($tagStartTimestamp)),
									'total_applicants'	=> count($users),
									'pick_count' => $pickCount,
									'user_list' => ""
							);
							
							\Mail::queueOn(\Config::get('app.QueueForEmailAndSMS'), 'emails.competition', $data, function($message)
							{
								$message->from('connect@airshr.net', 'AirShr Connect')
										->to('dev@airshr.com.au', 'AirShr Dev')
										->cc(['travis.winks@yahoo.com', 'deano@wavefm.com.au', 'deanokesby@gmail.com'])
										->bcc(['dollah.singh.dev@gmail.com'])
										->subject("Competition Result");
							});
							
						}
						
						Competition::create([
							'tag_id'				=>		$tag->id,
							'tag_start_timestamp'	=>		$tagStartTimestamp,
							'tag_end_timestamp'		=> 		$tagEndTimestamp,
							'competition_check_timestamp'	=> $now,
							'event_users_num'		=> 		count($users),
							'picked_users_num'		=> $pickCount,
							'picked_user_ids'		=> json_encode($pickedUserIdArray),
							'picked_user_phones'	=> json_encode($pickedUserPhoneNumArray)
						]);
						
						
					} else {
							
						$this->info("Not a competition");
					}
					
					$lastTimestamp = $tag->tag_timestamp;
				}
										
				Setting::setSettingVal('competition_check_timestamp_' . $station->id, $lastTimestamp);
				
			}
			
			
			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->error("Error: " . $ex->getMessage());
		}
		
		
		$this->info("Competition decide process ended.");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
