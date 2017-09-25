<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\PreviewTag;

class AirShrRemoveOldPreviewLog extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:removeoldpreviewlogs';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Removes old preview logs.';

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
			
			$now = time();
			
			$date = date("Y-m-d", strtotime("-14 days", $now));
			
			$this->info("Removing preview tags that are created before " . $date . "...");
			
			$timestamp = strtotime($date . " 00:00:00");
			
			PreviewTag::where('tag_timestamp', '<', $timestamp * 1000)->forceDelete();
			
			$this->info("Removed.");
		
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
