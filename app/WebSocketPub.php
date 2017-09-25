<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class WebSocketPub extends Model {

	public static $IS_REDIS_BACKEND_SET = false;
	
	public static function publishPushMessage($payload) {
		
		try {
			//\Artisan::queue('airshr:pushwebsocketmessage', ['payload' => json_encode($payload)]);
			
			//AirShrArtisanQueue::QueueArtisanCommandToConnectQueue('airshr:pushwebsocketmessage2', ['payload' => json_encode($payload)]);
			
			self::publishPushMessageToResque($payload);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
	public static function publishPushMessageOnQueue($payload, $queue) {
	
		try {
			//\Artisan::queue('airshr:pushwebsocketmessage', ['payload' => json_encode($payload)]);
				
			//AirShrArtisanQueue::QueueArtisanCommandToConnectQueue('airshr:pushwebsocketmessage2', ['payload' => json_encode($payload)]);
			
			//AirShrArtisanQueue::QueueArtisanCommand('airshr:pushwebsocketmessage2', ['payload' => json_encode($payload)], $queue);
			
			/*\DB::table('airshr_websocket_messages')->insert([
				'queue'		=> $queue,
				'payload'	=> json_encode($payload),
				'created_at'	=> time()
			]);*/
			
			
			self::publishPushMessageToResque($payload);
				
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
	}
	
	public static function publishPushMessageToResque($payload) {
		
		try {
			
			if (!self::$IS_REDIS_BACKEND_SET) {
				\Resque::setBackend(\Config::get('app.Resque_Redis_Server_Host') . ':' . \Config::get('app.Resque_Redis_Server_Port'), 0, 'resque', \Config::get('app.Resque_Redis_Server_Password'));
				self::$IS_REDIS_BACKEND_SET = true;
			}
				
			$args = array();
			$args[] = json_encode($payload);
				
			\Resque::enqueue(\Config::get('app.Resque_Websocket_Queue'), 'worker', $args);
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
	}
	
}
