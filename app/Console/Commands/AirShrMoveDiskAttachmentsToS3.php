<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\ConnectContentAttachment;

class AirShrMoveDiskAttachmentsToS3 extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:movediskattachmentstos3';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Migration script for moving disk stored connect attachments to S3.';

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
		ini_set('max_execution_time', 0);
		set_time_limit(0);
		
		$offset = 0;
		
		$this->info("Migration has started from offset: {$offset}");
		
		try {
			
			$attachments = ConnectContentAttachment::skip($offset)->take(13072)->get();
			//$attachments = ConnectContentAttachment::where('id', 1)->get();
			
			$index = 0;
			
			foreach($attachments as $attachment) {
				
				try {
					$index++;
					
					$this->info("Processing: {$index}");
	
					$relativePath = \Config::get('app.ContentUploadsS3DIR') . $attachment->type . "/";
					
					if ($attachment->type == 'video') {
						$this->info("This is video. Skipping...");
					} else {
						
						$savedPath = $attachment->saved_path;
						
						if (!empty($attachment->saved_path)) {
							$this->info("Uploading file (final).");
							
							if (!\Storage::disk('s3')->put($relativePath . $attachment->saved_name, file_get_contents(public_path($attachment->saved_path)))) {
								$this->error("Upload has failed.");
							} else {
								$this->info("Upload success");
								$attachment->saved_path = \Config::get('app.ContentS3BaseURL') . $relativePath . $attachment->saved_name;
							} 
						}
						
						
						if (!empty($attachment->original_saved_path)) {
							
							if ($attachment->original_saved_path != $savedPath) {
								$this->info("Uploading file (original).");
							
								if (!\Storage::disk('s3')->put($relativePath . $attachment->original_saved_name, file_get_contents(public_path($attachment->original_saved_path)))) {
									$this->error("Upload has failed.");
								} else {
									$this->info("Upload success");
									$attachment->original_saved_path = \Config::get('app.ContentS3BaseURL') . $relativePath . $attachment->original_saved_name;
								}
							} else {
								$attachment->original_saved_path = $attachment->saved_path;
							}
						}
						
						$attachment->save();
					}
				} catch (\Exception $ex2) {
					$this->error($ex2->getMessage());
				}
				
			}
		
		} catch (\Exception $ex) {
			$this->error($ex->getMessage());
		}
		
		
		$this->info("Migration has ended.");
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
