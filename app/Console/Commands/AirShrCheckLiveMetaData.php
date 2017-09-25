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

class AirShrCheckLiveMetaData extends Command {

	public static $NEXT_TAG_ARRIVAL_MAX_SECONDS	= 600;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:checklivemetadata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check live meta data availability.';

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
		
		$this->info("Checking Live meta data has started.");
		
		try {
				
			foreach (Station::all() as $station) {
		
				if ($station->is_private) continue;		// only for public station
		
				$expectedTagInterval = Setting::getSettingVal('next_tag_arrival_max_interval_' . $station->id, null);
				if ($expectedTagInterval == null) {
					$expectedTagInterval = self::$NEXT_TAG_ARRIVAL_MAX_SECONDS;
				}
				
				$this->info("Station: {$station->station_short}");
				
				$stationName = $station->station_abbrev;
		
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
				$metadataOn = Setting::getSettingVal('meta_data_on_' . $station->id, null);
				if ($metadataOn == null) {
					$metadataOn = 1;
					Setting::setSettingVal('meta_data_on_' . $station->id, $metadataOn);
				}
				
				$updateMetaDataStatus = false;
				
				if ($metadataOn == 1) {
					$this->info("Meta data live status was ON.");
				} else if ($metadataOn == -1) {
					$this->info("Meta data live status was OFF.");
				}
				
				$timeDifference = $now - $currentTag->tag_timestamp;
				
				if ($timeDifference > $expectedTagInterval * 1000 && $metadataOn == 1) {
					$this->info("Meta tag seems not coming for some duration. sending email...");
					$metadataOn = -1;
					$updateMetaDataStatus = true;
										
					// send email
					$data = array(
							'downtime'		=> getSecondsFromMili($timeDifference),
							'checktime'		=> date("d M, Y H:i:s", getSecondsFromMili($now)),
							'stationname'	=> $stationName
					);
					\Mail::queueOn(\Config::get('app.QueueForEmailAndSMS'), 'emails.metadatastatusoff', $data, function($message) use ($stationName)
					{
						$message->from('connect@airshr.net', 'AirShr Connect')
								->to('dev@airshr.com.au', 'AirShr Dev')
								//->bcc(['dollah.singh.dev@gmail.com'])
								->subject("Emergency Alert: Meta data extractor for {$stationName} seems down.");
					});
					
					
				} else if ($timeDifference < $expectedTagInterval * 1000 && $metadataOn == -1) {
					$this->info("Meta tag seems coming again. sending email...");
					$metadataOn = 1;
					$updateMetaDataStatus = true;
					
					// send email
					$data = array(
							'checktime'		=> date("d M, Y H:i:s", getSecondsFromMili($now)),
							'stationname'	=> $stationName
					);
					\Mail::queueOn(\Config::get('app.QueueForEmailAndSMS'), 'emails.metadatastatuson', $data, function($message) use ($stationName)
					{
						$message->from('connect@airshr.net', 'AirShr Connect')
								->to('dev@airshr.com.au', 'AirShr Dev')
								//->bcc(['dollah.singh.dev@gmail.com'])
								->subject("Notification: Meta data extractor for {$stationName} seems running again.");
					});
					
				}
				
				if ($updateMetaDataStatus) {
					Setting::setSettingVal('meta_data_on_' . $station->id, $metadataOn);
				}
		
			}
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->error("Error: " . $ex->getMessage());
		}
		
		$this->info("Checking Live meta data has ended.");
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
