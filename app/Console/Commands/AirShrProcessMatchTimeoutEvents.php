<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Event;

class AirShrProcessMatchTimeoutEvents extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:processmatchtimeoutevents';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check match timeout events and process them.';

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
		$this->info("Match timeout events processing has started...");
		
		try {

			$currentAWSTimestamp = getAWSTimeFromNTPServer();
			
			$this->info("AWS Timestamp: " . $currentAWSTimestamp);
			
			// for last 5 mins : 1 min ~ 5 mins
			$minRecordTimestamp = $currentAWSTimestamp - 5 * 60 * 1000;
			$maxRecordTimestamp = $currentAWSTimestamp - 1 * 60 * 1000;
			
			$eventIDs = \DB::table("airshr_events")->select("id")->whereRaw("record_timestamp_ms <= $maxRecordTimestamp AND record_timestamp_ms > $minRecordTimestamp AND event_data_status = 0 AND (event_data_status_updateon IS NULL OR event_data_status_updateon = 0) AND deleted_at IS NULL")->orderBy("record_timestamp_ms", "asc")->get();
			
			foreach ($eventIDs as $elem) {
				$eventID = $elem->id;
				$this->info("Processing Event: {$eventID}");	
				
				$event = Event::findOrFail($eventID);
				if ($event->event_data_status == 0) {  // still pending?
					$event->markEventAsTimeout();
				}
				
				$this->info("Processed Event.");
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		$this->info("Match timeout events processing has ended...");
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
