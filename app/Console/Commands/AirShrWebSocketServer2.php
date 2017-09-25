<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrWebSocketServer2 extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:websocketserver2';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run Secure WebSocket Server.';

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
		
			require_once (__DIR__ . '/../../Library/WebSocket/SplClassLoader.php');
			
			$classLoader = new \SplClassLoader('WebSocket', __DIR__ . '/../../Library/WebSocket');
			$classLoader->register();
							
			$server = new \WebSocket\Server('0.0.0.0', \Config::get('app.WebSocketServerPort'), true);
			
			// server settings:
			$server->setMaxClients(10000);
			$server->setCheckOrigin(false);
			$server->setMaxConnectionsPerIp(5000);
			$server->setMaxRequestsPerMinute(60000);
			
			$server->registerApplication('status', \WebSocket\Application\StatusApplication::getInstance());
			$server->registerApplication('connect', \WebSocket\Application\ConnectApplication::getInstance());
			
			$server->run();
			
							
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
