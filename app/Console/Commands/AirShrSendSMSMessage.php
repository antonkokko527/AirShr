<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AirShrSendSMSMessage extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:sendsmsmessage';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send SMS message using Sencha.';

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
			
			$from = $this->argument('from');
			$message = $this->argument('message');
			$to = $this->argument('to');
			
			$payload = array(
					'From'	=> $from,
					'Message' => $message
			);
				
			$request = \Httpful\Request::post(\Config::get("app.Sinch_SMS_API_URL") . $to, json_encode($payload), "application/json");
				
			$request->authenticateWith("application\\" . \Config::get('app.Sinch_API_KEY'), \Config::get('app.Sinch_API_SECRET'));
				
			$request->addHeader("X-Timestamp", date("c"));
			//$request->addHeader("Authorization", "Application " . \Config::get('app.Sinch_API_KEY') . ":" . \Config::get('app.Sinch_API_SECRET'));
				
			$response = $request->send();
			
			if ($response->code == 200){
				$this->info("SMS success");
			} else {
				$this->info("SMS failed");
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->error($ex->getMessage());
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
			['from', InputArgument::REQUIRED, 'From'],
			['message', InputArgument::REQUIRED, 'Message'],
			['to', InputArgument::REQUIRED, 'To phone number'],
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
