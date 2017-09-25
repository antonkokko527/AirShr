<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Event;
use App\Tag;
use App\TerrestrialStreamDelay;
use App\CoverArt;
use App\ConnectContent;
use App\User;

class AirShrTestConsole extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:testconsole';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Testing purpose.';

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
			
			set_time_limit(0);
			
			/*$this->info("Going through competition users...");
			
			$csvFile = "";
			$tagID = '';
			
			$file = fopen($csvFile, "r");
			
			while (! feof($file)) {
				$values = fgetcsv($file);
				
				$phoneNum = $values[0];
				$userID = $values[2];
				
				$this->info("Processing Phone number: " . $phoneNum);
				
				try {
					
					$mostRecentEvent = User::getMostRecentEventOfUser($userID);
					
					if (!$mostRecentEvent) {
						$this->info("No Event. Skipping....");
						continue;
					}
					
					$this->info("Most Recent Event: {$mostRecentEvent->id}, {$mostRecentEvent->record_device_id}, {$mostRecentEvent->push_token}, {$mostRecentEvent->device_type}");
										
					$newEvent = User::createUserEventFromTag($userID, $tagID, $mostRecentEvent->push_token, $mostRecentEvent->device_type, false, getCurrentMilisecondsTimestamp());
					
					if ($newEvent) {
						$this->info("Event created.");
						
						if ($mostRecentEvent->device_type == 'iOS') {  // send push notification in case of iOS, for android no need because new event can not be created in the current android
							\Artisan::call('airshr:sendeventupdatenotify', ['eventid' => $newEvent->id]);
							$this->info("Push notification sent.");
						}
						
					} else {
						$this->erro("Event can not be created.");
					}
					
				} catch(\Exception $ex2) {
					$this->error($ex2);
				}
			}
			
			fclose($file);*/
			
			$this->info("Updating content to audio attachment links...");
				
			$contents = ConnectContent::where('station_id', '=', '8')->get();
				
			foreach ($contents as $content) {
			
				$this->info("Processing content " . $content->id . " ...");
				$content->searchAudioFileAndLink();
				
			}
			
			/*$this->info("Updating coverart's image sizes...");
			
			$coverarts = CoverArt::all();
			
			foreach ($coverarts as $coverart) {
				
				$this->info("Processing coverart " . $coverart->id . " ...");
				
				if (!empty($coverart->coverart_url)) {
					$sizeInfo = getimagesize($coverart->coverart_url);
					$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
					$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
					$coverart->more_info = json_encode(['width' => $width, 'height' => $height]);
					$coverart->save();
				}
				
			}*/
									
			/*$this->info("Updating event's recent terrestrial...");
			
			$events = Event::where('recent_terrestrial_log', 0)
							->where('record_timestamp_ms', '>=', 1451606400000)
							->get();
			
			foreach ($events as $event) {
				$this->info("Processing event " . $event->id . " ...");
				
				// get recent terrestrial log
				$recentTerrestrialDelay = TerrestrialStreamDelay::getMostRecentTerrestrialDelayOfEvent($event);
				if ($recentTerrestrialDelay) {
					$event->recent_terrestrial_log = $recentTerrestrialDelay->terrestrial_stream_delay;
					$event->save();
				}
			}
			
			$this->info("Updating tag's real duration...");
			
			$tags = Tag::where('real_tag_duration', 0)
						->where('tag_timestamp', '>=', 1451606400000)
						->get();
			
			foreach ($tags as $tag) {
				$this->info("Processing tag " . $tag->id . " ...");
				
				$nextTag = $tag->getNextTag(false);
				
				if ($nextTag) {
					
					if ($nextTag->tag_timestamp - $tag->tag_timestamp > 0) {
						$tag->real_tag_duration = $nextTag->tag_timestamp - $tag->tag_timestamp;
						$tag->save();
					}
					
				}
			}*/
			
			$this->info('done');
			
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
