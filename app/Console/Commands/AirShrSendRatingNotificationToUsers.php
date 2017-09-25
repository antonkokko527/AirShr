<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\User;
use App\ContentType;

class AirShrSendRatingNotificationToUsers extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:sendratingnotification';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

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
			
			$this->info("Sending music rating push notification has started...");
			
			$users = \DB::select("SELECT DISTINCT record_device_id FROM airshr_events WHERE id IN (SELECT id FROM airshr_events WHERE app_version IS NOT NULL AND app_version <> '' )");
			
			$sentCount = 0;
			$sentNumbers = array();
			
			foreach ($users as $userInfo) {
				
				$user = User::findUserByUniqueID($userInfo->record_device_id);
				
				if (!$user) continue;
				
				$this->info("User:  " . $user->phone_number);
				
				$tokenInfo = $user->getUserDeviceToken();

				$this->info("Device type: {$tokenInfo['type']},  Token: {$tokenInfo['token']}");
								
				if (empty($tokenInfo['type']) || empty($tokenInfo['token'])) continue;
				
				$recentMusicEvent = User::getMostRecentEventOfUser($user->user_id, ContentType::GetMusicContentTypeID(), false);
				
				if (!$recentMusicEvent) {
					$this->info("No recent music event for this user. Skip.");
					continue;
				}
				
				if ($recentMusicEvent->rate_option != 'no_rate') {
					$this->info("Already rated this song. Skip.");
					continue;
				}
				
				$recentMusicTag = $recentMusicEvent->tagForEvent;
				
				if (!$recentMusicTag) {
					$this->info("No music tag for this event. Skip.");
					continue;
				}
				
				
				//$alertMsg = "You recently AirShr'd {$recentMusicTag->what} by {$recentMusicTag->who}. Tap here to rate it and check out the lyrics.";
				$alertMsg = "You recently AirShr'd {$recentMusicTag->what} by {$recentMusicTag->who}. We've just sent you the lyrics!";
				
				
				$this->info("Alert: " . $alertMsg);
				
				$this->info("Sending push notification.");
				
				\Artisan::call('airshr:sendeventupdatenotify', ['eventid' => $recentMusicEvent->id, 'push_alert' => $alertMsg, 'push_action' => '', 'dev' => '', 'push_token' => $tokenInfo['token'], 'push_device_type' => $tokenInfo['type']]);
				
				$this->info("Notification sent.");

				$sentCount++;
				
				$sentNumbers[] = $user->phone_number;
			}
			
			$this->info("Sent count: $sentCount");
			$this->info("Sent numbers are listed below:");
			
			foreach ($sentNumbers as $number) {
				$this->info($number);
			}
			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		
		$this->info("Sending rating push notification has ended.");
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
