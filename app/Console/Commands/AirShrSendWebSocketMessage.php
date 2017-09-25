<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrSendWebSocketMessage extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:pushwebsocketmessage';

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
		
			$payload = $this->argument('payload');
				
			$websocket = new \Hoa\Websocket\Client(
					new \Hoa\Socket\Client(\Config::get('app.WebSocketSever'))
			);
		
			$websocket->setHost('airshr.net');
			$websocket->connect();
				
			$websocket->send($payload);
		
			$websocket->close();
				
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
