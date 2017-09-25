<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrSendWebSocketMessage2 extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:pushwebsocketmessage2';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send push notification to all connected clients.';

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
				
			require_once(__DIR__ . '/../../Library/WebSocket/SplClassLoader.php');
				
			$classLoader = new \SplClassLoader('WebSocket', __DIR__ . '/../../Library/WebSocket');
			$classLoader->register();
						
			$payload = $this->argument('payload');

			$client = new \WebSocket\WebsocketClient();
			
			$client->connect(\Config::get('app.WebSocketSecureSever'), \Config::get('app.WebSocketServerPort'), '/connect');

			$client->sendData($payload);
			
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
			['payload', InputArgument::REQUIRED, 'Payload to send'],
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
