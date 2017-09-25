<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use \App\Commands\AirShrRunQueuedArtisanCommand;

class AirShrArtisanQueue extends Model {

	public static function QueueArtisanCommand($command, $arguments, $queue) {
		
		\Queue::pushOn($queue, new AirShrRunQueuedArtisanCommand($command, $arguments));
		
	}
	
	public static function QueueArtisanCommandToAPIQueue($command, $arguments) {

		self::QueueArtisanCommand($command, $arguments, \Config::get('app.QueueForAPI'));
		
	}
	
	public static function QueueArtisanCommandToConnectQueue($command, $arguments) {
	
		self::QueueArtisanCommand($command, $arguments, \Config::get('app.QueueForConnect'));
	
	}
	
	public static function QueueArtisanCommandWithDelay($command, $arguments, $queue, $delay) {
		\Queue::laterOn($queue, $delay, new AirShrRunQueuedArtisanCommand($command, $arguments));
	}
}
