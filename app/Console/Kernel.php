<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
		'App\Console\Commands\AirShrWebSocketServer',
		'App\Console\Commands\AirShrSendWebSocketMessage',
		'App\Console\Commands\AirShrSendEventUpdatePush',
		'App\Console\Commands\AirShrParsePreviewLog',
		'App\Console\Commands\AirShrRemoveOldPreviewLog',
		'App\Console\Commands\AirShrDecideCompetition',
		'App\Console\Commands\AirShrCheckLiveMetaData',
		'App\Console\Commands\AirShrUpdateTagEventCount',
		'App\Console\Commands\AirShrMoveDiskAttachmentsToS3',
		'App\Console\Commands\AirShrSendListenerServiceRequest',
		'App\Console\Commands\AirShrParseListenerServiceResult',
		'App\Console\Commands\AirShrWebSocketServer2',
		'App\Console\Commands\AirShrSendWebSocketMessage2',
		'App\Console\Commands\AirShrBroadcastCachedNovaTags',
		'App\Console\Commands\AirShrRemoveCachedNovaTags',
		'App\Console\Commands\AirShrProcessMatchTimeoutEvents',
		'App\Console\Commands\AirShrGenerateTagCompetitionResult',
		'App\Console\Commands\AirShrSendSMSMessage',
		'App\Console\Commands\AirShrProcessWebSocketMessages',
		'App\Console\Commands\AirShrUpdateEventWithMatcherResponse',
		'App\Console\Commands\AirShrSendTagCountUpdateWebSocket',
		'App\Console\Commands\AirShrTestConsole',
		'App\Console\Commands\AirShrGenerateVoteResultForTag',
		'App\Console\Commands\AirShrUpdateTagVoteOptionCount',
		'App\Console\Commands\AirShrSetCompetitionTags',
		'App\Console\Commands\AirShrCreateTagWithDelay',
		'App\Console\Commands\AirShrSendRatingNotificationToUsers',
		'App\Console\Commands\AirShrGenerateTagTrimmedAudio'
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('inspire')
				 ->hourly();
	}

}
