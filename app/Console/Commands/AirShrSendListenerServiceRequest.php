<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Aws\Sqs\SqsClient;

class AirShrSendListenerServiceRequest extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:sendlistenerservicerequest';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send listener service request throug Amazon SQS.';

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
			
			$eventId = $this->argument('event_id');
			$s3_url = $this->argument('s3_url');
			$timestamp = $this->argument('timestamp');
			$stations = $this->argument('stations');

			$stationList = explode(",", $stations);
			
			$stationArray = array();
			
			foreach ($stationList as $station) {
				$station = trim($station);
				if (!empty($station)) $stationArray[] = $station;
			}

			$client = SqsClient::factory(array(
					'credentials' => array(
							'key'    => \Config::get('app.AWS_ACCESS_KEY'),
							'secret' => \Config::get('app.AWS_SECRET_KEY')
					),
					'region'  => \Config::get('app.AWS_REGION')
			));
			
			$client->sendMessage(array(
					'QueueUrl'    => \Config::get('app.MatcherInSQSQueueURL'),
					'MessageBody' => json_encode(array(
						'S3Url'			=> $s3_url,
						'timeStamp' 	=> $timestamp,
						'eventID'		=> $eventId,
						'stations'		=> $stationArray	
					))
			));
			
			/*$client->sendMessage(array(
					'QueueUrl'    => \Config::get('app.MatcherInSQSDebugQueueURL'),
					'MessageBody' => json_encode(array(
							'S3Url'			=> $s3_url,
							'timeStamp' 	=> $timestamp,
							'eventID'		=> $eventId,
							'stations'		=> $stationArray
					))
			));*/
			
			// for diagnostics
			\DB::table('airshr_events')->where('id', '=', $eventId)->update(['sqs_sent_on' => time()]);
			
			$this->info("SQS has been sent.");
			
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
			['event_id', InputArgument::REQUIRED, 'Event ID.'],
			['s3_url', InputArgument::REQUIRED, 'S3 record file url.'],
			['timestamp', InputArgument::REQUIRED, 'Event timestamp in miliseconds.'],
			['stations', InputArgument::OPTIONAL, 'Recommended station list.']
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
