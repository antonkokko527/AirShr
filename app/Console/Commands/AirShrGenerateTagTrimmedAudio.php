<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Tag;

class AirShrGenerateTagTrimmedAudio extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:generatetagtrimmedaudio';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate trimmed audio for tag.';

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
			
			$tagId = $this->argument('tagId');
			
			$tag = Tag::findOrFail($tagId);
			
			$tag->generateTrimmedAudio();
			
						
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
			['tagId', InputArgument::REQUIRED, 'Tag ID'],
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
