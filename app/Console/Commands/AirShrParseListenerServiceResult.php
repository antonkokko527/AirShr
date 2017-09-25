<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Aws\Sqs\SqsClient;
use App\Event;
use App\AirShrArtisanQueue;

class AirShrParseListenerServiceResult extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:parselistenerserviceresult';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Parse SQS message for getting any listener service response.';

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
		
		$queueCount = 50;
		$index = 0;
		
		try {
			
			$client = SqsClient::factory(array(
					'credentials' => array(
							'key'    => \Config::get('app.AWS_ACCESS_KEY'),
							'secret' => \Config::get('app.AWS_SECRET_KEY')
					),
					'region'  => \Config::get('app.AWS_REGION')
			));
			
			
			while (true) {
			
				$messageResult = $client->receiveMessage(array(
						'QueueUrl'        => \Config::get('app.MatcherOutSQSQueueURL'),
						'WaitTimeSeconds' => 20,
						'AttributeNames'  => ['SentTimestamp']
				));

				$messageList  = $messageResult->getPath('Messages');
				
				if (empty($messageList)) {
					continue;
				}
				
				foreach ($messageList as $message) {

					$messageReceiptHandle = $message['ReceiptHandle'];
					$messageBody = $message['Body'];
					$sentTime = $message['Attributes']['SentTimestamp'];
					
					$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]" . "Message Received: {$messageReceiptHandle} \n {$messageBody}");
					
					try {
						
						$client->deleteMessage(array(
								'QueueUrl'        => \Config::get('app.MatcherOutSQSQueueURL'),
								'ReceiptHandle' => $messageReceiptHandle
						));
						
						$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]" . "Message removed from queue.");
						
						$result_json = json_decode($messageBody);
						
						//$event = Event::findOrFail($result_json->data->eventID);

						//$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]" . "Event with ID has been found.");
						
						$result = array(); 
						
						$result['success'] = false;
						$result['msg'] = "";
						
						if ($result_json->status == 'success'){
						
							if ($result_json->data->foundMatch){
								$result['success'] = true;
								$result['station_name'] = $result_json->data->bestMatch->station;
								$result['timestamp'] = $result_json->data->bestMatch->matchTime;
								$result['match_percent'] = $result_json->data->bestMatch->matchPercentage;
								$result['match_time'] = $result_json->data->bestMatch->matchTime;
							} else {
								$result['msg'] = 'Not found match';
								if (isset($result_json->data->bestMatch)) {
									if (isset($result_json->data->bestMatch->station)) $result['station_name'] = $result_json->data->bestMatch->station;
									if (isset($result_json->data->bestMatch->matchTime)) $result['timestamp'] = $result_json->data->bestMatch->matchTime;
									if (isset($result_json->data->bestMatch->matchPercentage)) $result['match_percent'] = $result_json->data->bestMatch->matchPercentage;
									if (isset($result_json->data->bestMatch->matchTime)) $result['match_time'] = $result_json->data->bestMatch->matchTime;
								}
							}
						
						} else {
							$result['msg'] = 'Status response is not success';
						}
						
						//$event->updateEventWithListenerResult($result, time(), $sentTime);						

						//$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]" . "Event has been updated with listener result.");
						
						$queue = "API_EVENT_PROCESS_QUEUE_{$index}";
						
						//AirShrArtisanQueue::QueueArtisanCommand("airshr:updateeventwithmatcher", ['eventId' => $result_json->data->eventID, 'response' => json_encode($result), 'sentTimestamp' => $sentTime], $queue);
						
						\DB::table('airshr_event_match_responses')->insert([
							'queue'		=> $queue,
							'event_id'	=> $result_json->data->eventID,
							'payload'	=> json_encode($result),
							'sent_time'	=> $sentTime
						]);
												
						$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]" . "Put on Queue - {$queue}.");
						
						$index = ($index + 1) % $queueCount;
						
						unset($result);
						unset($result_json);
						unset($messageReceiptHandle);
						unset($messageBody);
						unset($queue);
						
					} catch (\Exception $ex1) {
						$this->error("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
						$this->error($ex1);
					}
					
				}
				
				unset($messageResult);
			
			}
			
		} catch (\Exception $ex){
			$this->error("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
			$this->error($ex);
			//\Log::error($ex);
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
