<?php namespace App\Commands;

use App\Commands\Command;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class AirShrRunQueuedArtisanCommand extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	protected $artisanCommand;
	protected $artisanArguments;
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($command, $arguments)
	{
		$this->artisanCommand = $command;
		$this->artisanArguments = $arguments;
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		\Artisan::call($this->artisanCommand, $this->artisanArguments);
	}

}
