<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\Event;

class AirShrUpdateEventWithMatcherResponse extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:updateeventwithmatcher';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update event with listener results.';

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
			
			$queue = $this->argument('queue');
			if (empty($queue)) throw new \Exception("Queue is not specified. Exiting....");
			
			while (true) {
			
				$response = \DB::table("airshr_event_match_responses")->where('queue', '=', $queue)->orderBy('event_id', 'asc')->first();
			
				if ($response) {
					
					$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
					$this->info("Event ({$response->event_id}) -  Match Response: " . $response->payload);
					
					try {
						$event = Event::findOrFail($response->event_id);
						$event->updateEventWithListenerResult(json_decode($response->payload, true), time(), $response->sent_time);
						$this->info("Processed");
						unset($event);
					} catch (\Exception $ex2) {
						$this->error("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
						$this->error($ex2);
					}
										
					\DB::table("airshr_event_match_responses")->where("id", $response->id)->delete();
					$this->info("Removed record");
					
				}
			
				unset($response);
					
			}
			
		} catch (\Exception $ex){
			$this->error("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
			$this->error($ex);
			//\Log::error($ex);
		}
		
		
		/*try {
			
			$eventId = $this->argument("eventId");
			$response = $this->argument("response");
			$sentTimestamp = $this->argument("sentTimestamp");
			
			if (empty($eventId)) {
				throw new \Exception("Event ID is empty");
			}
			
			if (empty($response)) {
				throw new \Exception("Response is empty");
			}
			
			if (empty($sentTimestamp)) {
				throw new \Exception("Timestamp is empty");
			}
			
			$this->info("EventID: $eventId");
			$this->info("Response: $response");
			$this->info("Sent on: $sentTimestamp");
			
			$event = Event::findOrFail($eventId);
			
			$event->updateEventWithListenerResult(json_decode($response, true), time(), $sentTimestamp);
			
			$this->info("Event is updated.");
			
		} catch (\Exception $ex){
			\Log::error($ex);
			$this->error($ex);
		}*/
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['queue', InputArgument::REQUIRED, 'Queue name.']
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
