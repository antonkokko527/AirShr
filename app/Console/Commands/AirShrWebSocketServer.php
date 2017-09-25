<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class AirShrWebSocketServer extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:websocketserver';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run WebSocket Server.';

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
						
			$context = \Hoa\Stream\Context::getInstance('airshrnet_ssl_socket');
			
			$context->setOptions(array(
				'ssl' => array(
						'local_cert' => base_path() . \Config::get('app.WebSocketPEMFile'),
						'passphrase' => '',
						'allow_self_signed' => true,
						'verify_peer' => false
				)
			));
			
			$websocket = new \Hoa\Websocket\Server(
			    new \Hoa\Socket\Server(\Config::get('app.WebSocketSever'), 30, -1, "airshrnet_ssl_socket")
			);
			
			$websocket->on('open', function (\Hoa\Core\Event\Bucket $bucket) {
				echo 'new connection', "\n";
				return;
			});
			
			$websocket->on('message', function (\Hoa\Core\Event\Bucket $bucket) {
				try {
					$data = $bucket->getData();
					echo '> message ', $data['message'], "\n";
					$messageContent = !empty($data['message']) ? json_decode($data['message'], true) : array();
					if (isset($messageContent['ping']) && $messageContent['ping'] == 'pong') {
						echo "ping pong message. skip broadcasting.";
					}  else {
						$bucket->getSource()->broadcast($data['message']);
						echo "broadcasted.";
					}
					echo '< echo', "\n";
				} catch (\Exception $ex1) {
					echo "Exception: " . $ex1->getMessage();
				}
				return;
			});
			
			$websocket->on('close', function (\Hoa\Core\Event\Bucket $bucket) {
				echo 'connection closed', "\n";
				return;
			});
			
			$this->info("Socket server is running..." . \Config::get('app.WebSocketServer'));
			
			$websocket->run();
			
			
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
