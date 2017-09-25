<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrProcessWebSocketMessages extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:processwebsocketmessages';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process web socket messages in the queue.';

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
			
			require_once(__DIR__ . '/../../Library/WebSocket/SplClassLoader.php');
			
			$classLoader = new \SplClassLoader('WebSocket', __DIR__ . '/../../Library/WebSocket');
			$classLoader->register();
			
			$client = new \WebSocket\WebsocketClient();
			
			$connectRetryCount = 5;
			$bConnected = false;
			
			while (!$bConnected && $connectRetryCount >= 0) {
				try {
					$bConnected = $client->connect(\Config::get('app.WebSocketSecureSever'), \Config::get('app.WebSocketServerPort'), '/connect');
				} catch (\Exception $exCon){ 
					$this->error("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
					$this->error($exCon);
				}
				
				if (!$bConnected) {
					sleep(5);
					$connectRetryCount--;
				}
			}
			
			if (!$bConnected) {
				throw new \Exception("Can not connect to the websocket server.");
			} else {
				$this->info("Now connected to the websocket server.");
			}
			
			//$client->sendData('{"ping" : "pong"}'); // send test data for checking the connectivity
			
			while (true) {

				$message = \DB::table("airshr_websocket_messages")->where('queue', '=', $queue)->select("id", "payload")->orderBy('id', 'asc')->first();
				
				if ($message) {
					
					if (!$client->checkConnection()) {
						$this->error("Connection is lost or bad.");
						throw new \Exception("Web Socket Servcer connection is lost or bad.");
					} else {
						$this->info("Connection is still good.");
					}
					
					$sendResult = $client->sendData($message->payload);
					
					if (!$sendResult) {
						throw new \Exception("Websocket message can not be sent.");
					}
					
					$this->info("[" . date("Y-m-d H:i:s") . ":" . time() . "]");
					$this->info("Message Sent: " . $message->payload);
					
					\DB::table("airshr_websocket_messages")->where("id", $message->id)->delete();
					
					$this->info("Removed record");
					
					unset($sendResult);
				}
				
				unset($message);
				
				// check connection
				/*if ($client->checkConnection()) {
					$this->info("Connect is good.");
				} else {
					$this->info("Connection is not good.");
				}*/
				
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
			['queue', InputArgument::REQUIRED, 'Queue name.'],
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
