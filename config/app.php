<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Application Debug Mode
	|--------------------------------------------------------------------------
	|
	| When your application is in debug mode, detailed error messages with
	| stack traces will be shown on every error that occurs within your
	| application. If disabled, a simple generic error page is shown.
	|
	*/

	'debug' => env('APP_DEBUG'),

	/*
	|--------------------------------------------------------------------------
	| Application URL
	|--------------------------------------------------------------------------
	|
	| This URL is used by the console to properly generate URLs when using
	| the Artisan command line tool. You should set this to the root of
	| your application so that it is used when running Artisan tasks.
	|
	*/

	'url' => 'http://localhost',

	/*
	|--------------------------------------------------------------------------
	| Application Timezone
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default timezone for your application, which
	| will be used by the PHP date and date-time functions. We have gone
	| ahead and set this to a sensible default for you out of the box.
	|
	*/

	'timezone' => 'Australia/Sydney',

	/*
	|--------------------------------------------------------------------------
	| Application Locale Configuration
	|--------------------------------------------------------------------------
	|
	| The application locale determines the default locale that will be used
	| by the translation service provider. You are free to set this value
	| to any of the locales which will be supported by the application.
	|
	*/

	'locale' => 'en',

	/*
	|--------------------------------------------------------------------------
	| Application Fallback Locale
	|--------------------------------------------------------------------------
	|
	| The fallback locale determines the locale to use when the current one
	| is not available. You may change the value to correspond to any of
	| the language folders that are provided through your application.
	|
	*/

	'fallback_locale' => 'en',

	/*
	|--------------------------------------------------------------------------
	| Encryption Key
	|--------------------------------------------------------------------------
	|
	| This key is used by the Illuminate encrypter service and should be set
	| to a random, 32 character string, otherwise these encrypted strings
	| will not be safe. Please do this before deploying an application!
	|
	*/

	'key' => env('APP_KEY', 'SomeRandomString'),

	'cipher' => MCRYPT_RIJNDAEL_128,

	/*
	|--------------------------------------------------------------------------
	| Logging Configuration
	|--------------------------------------------------------------------------
	|
	| Here you may configure the log settings for your application. Out of
	| the box, Laravel uses the Monolog PHP logging library. This gives
	| you a variety of powerful log handlers / formatters to utilize.
	|
	| Available Settings: "single", "daily", "syslog", "errorlog"
	|
	*/

	'log' => 'daily',

	/*
	|--------------------------------------------------------------------------
	| Autoloaded Service Providers
	|--------------------------------------------------------------------------
	|
	| The service providers listed here will be automatically loaded on the
	| request to your application. Feel free to add your own services to
	| this array to grant expanded functionality to your applications.
	|
	*/

	'providers' => [

		/*
		 * Laravel Framework Service Providers...
		 */
		'Illuminate\Foundation\Providers\ArtisanServiceProvider',
		'Illuminate\Auth\AuthServiceProvider',
		'Illuminate\Bus\BusServiceProvider',
		'Illuminate\Cache\CacheServiceProvider',
		'Illuminate\Foundation\Providers\ConsoleSupportServiceProvider',
		'Illuminate\Routing\ControllerServiceProvider',
		'Illuminate\Cookie\CookieServiceProvider',
		'Illuminate\Database\DatabaseServiceProvider',
		'Illuminate\Encryption\EncryptionServiceProvider',
		'Illuminate\Filesystem\FilesystemServiceProvider',
		'Illuminate\Foundation\Providers\FoundationServiceProvider',
		'Illuminate\Hashing\HashServiceProvider',
		'Illuminate\Mail\MailServiceProvider',
		'Illuminate\Pagination\PaginationServiceProvider',
		'Illuminate\Pipeline\PipelineServiceProvider',
		'Illuminate\Queue\QueueServiceProvider',
		'Illuminate\Redis\RedisServiceProvider',
		'Illuminate\Auth\Passwords\PasswordResetServiceProvider',
		'Illuminate\Session\SessionServiceProvider',
		'Illuminate\Translation\TranslationServiceProvider',
		'Illuminate\Validation\ValidationServiceProvider',
		'Illuminate\View\ViewServiceProvider',

		/*
		 * Application Service Providers...
		 */
		'App\Providers\AppServiceProvider',
		'App\Providers\BusServiceProvider',
		'App\Providers\ConfigServiceProvider',
		'App\Providers\EventServiceProvider',
		'App\Providers\RouteServiceProvider',

		/*
		 * Other Customer Service Providers...
		 */
		'Barryvdh\DomPDF\ServiceProvider',
		'Orchestra\Parser\XmlServiceProvider',
	],

	/*
	|--------------------------------------------------------------------------
	| Class Aliases
	|--------------------------------------------------------------------------
	|
	| This array of class aliases will be registered when this application
	| is started. However, feel free to register as many as you wish as
	| the aliases are "lazy" loaded so they don't hinder performance.
	|
	*/

	'aliases' => [

		'App'       => 'Illuminate\Support\Facades\App',
		'Artisan'   => 'Illuminate\Support\Facades\Artisan',
		'Auth'      => 'Illuminate\Support\Facades\Auth',
		'Blade'     => 'Illuminate\Support\Facades\Blade',
		'Bus'       => 'Illuminate\Support\Facades\Bus',
		'Cache'     => 'Illuminate\Support\Facades\Cache',
		'Config'    => 'Illuminate\Support\Facades\Config',
		'Cookie'    => 'Illuminate\Support\Facades\Cookie',
		'Crypt'     => 'Illuminate\Support\Facades\Crypt',
		'DB'        => 'Illuminate\Support\Facades\DB',
		'Eloquent'  => 'Illuminate\Database\Eloquent\Model',
		'Event'     => 'Illuminate\Support\Facades\Event',
		'File'      => 'Illuminate\Support\Facades\File',
		'Hash'      => 'Illuminate\Support\Facades\Hash',
		'Input'     => 'Illuminate\Support\Facades\Input',
		'Inspiring' => 'Illuminate\Foundation\Inspiring',
		'Lang'      => 'Illuminate\Support\Facades\Lang',
		'Log'       => 'Illuminate\Support\Facades\Log',
		'Mail'      => 'Illuminate\Support\Facades\Mail',
		'Password'  => 'Illuminate\Support\Facades\Password',
		'Queue'     => 'Illuminate\Support\Facades\Queue',
		'Redirect'  => 'Illuminate\Support\Facades\Redirect',
		'Redis'     => 'Illuminate\Support\Facades\Redis',
		'Request'   => 'Illuminate\Support\Facades\Request',
		'Response'  => 'Illuminate\Support\Facades\Response',
		'Route'     => 'Illuminate\Support\Facades\Route',
		'Schema'    => 'Illuminate\Support\Facades\Schema',
		'Session'   => 'Illuminate\Support\Facades\Session',
		'Storage'   => 'Illuminate\Support\Facades\Storage',
		'URL'       => 'Illuminate\Support\Facades\URL',
		'Validator' => 'Illuminate\Support\Facades\Validator',
		'View'      => 'Illuminate\Support\Facades\View',
		
		'PDF' => 'Barryvdh\DomPDF\Facade',
		'XmlParser' => 'Orchestra\Parser\Xml\Facade'
	],
	
	
	
	/** Application Specific Configuration **/
	'AirShrCoverArtInternalURL'	=> 'http://coverart.airshr-internal.net/api/song/search?query=artist:%s,title:%s',
	'AirShrCoverArtUpdateInternalURL' => 'http://coverart.airshr-internal.net/api/song/%s',
	'AirShrCoverArtListGooglePlayInternalURL' => 'http://coverart.airshr-internal.net/api/song/searchGooglePlay?query=artist:%s,title:%s',
	'AirShrCoverArtListITunesInternalURL' => 'http://coverart.airshr-internal.net/api/song/searchITunes?query=artist:%s,title:%s',
	'AirShrCoverArtLyricsInternalURL' => 'http://coverart.airshr-internal.net/api/song/searchLyrics?query=artist:%s,title:%s',
	
	'AirShrAudioServiceInternalURL'	=> 'http://audio-service.airshr-internal.net/api/',
	
	//http://coverart.airshr-internal.net,
	//http://10.0.2.2:8001

	'ListenerServiceEndpoint' => 'http://dev-matcher.airshr.net/stationmatch',
	'CoverArtInfoBaseURL'	  => 'http://dev-coverer.airshr.net:8080/song',
	'ItunesSampleURLService'  => 'http://dev-matcher.airshr.net:8080/preview',
	'PushCertPassPhrase'	  => 'password',
	'ListenerServiceTimeout'  => 180,
	'Google_API_Key'		  => 'AIzaSyBgua5T9HOWlu6XLuWkjinsrZW2660ko28',
	'AudioStreamURL'		  => 'http://dev-trimmer.airshr.net/play',
	'EventMaxDuration'		  => 3600,
	
	'MatcherInSQSQueueURL'	  => 'https://sqs.ap-southeast-2.amazonaws.com/422020583532/prod-matcher-in',
	'MatcherOutSQSQueueURL'	  => 'https://sqs.ap-southeast-2.amazonaws.com/422020583532/prod-matcher-out',
	'MatcherInSQSDebugQueueURL' => 'https://sqs.ap-southeast-2.amazonaws.com/422020583532/debug-matcher-in',
	
	'AWS_ACCESS_KEY'		  => 'AKIAIFAVEZNUOKZD4W5Q',
	'AWS_SECRET_KEY'		  => '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',
	'AWS_REGION'			  => 'ap-southeast-2',
	
	'AIRSHR_S3_SCHEME_BASE'	  => 's3://airshr-production/',
	'AIRSHR_S3_HTTPS_SCHEME_BASE'	=> 'https://s3-ap-southeast-2.amazonaws.com/airshr-production/',
	
	'FirmwareUploadsDIR'	  => '/uploads/firmware/',
	'ContentUploadsDIR'		  => '/uploads/content/',
	'ContentUploadsS3DIR'	  => 'airshrconnect/',
	'ContentS3BaseURL'		  => 'https://s3-ap-southeast-2.amazonaws.com/airshr-production/',
	
	'NTPLiteTime_Service_URL'	=> 'http://dev-time.airshr.net/time/',
	 
	
	'TagAudioRenderURL'	      => 'http://dev-trimmer.airshr.net/render/%s/%s-%s.mp3?file=%s',

	'EventLinkTrackURL'		  => 'http://airshrd.com/redirect?url=%s&event_id=%s',
	
	'ConnectWebAppVersion'	  => '1.206',
	
	'DefaultCountryCode'	  => '61',
	
	'Sinch_SMS_API_URL'		  => 'https://messagingApi.Sinch.com/v1/Sms/',
	'Sinch_SMS_From'		  => 'AirShr',
	'Sinch_API_KEY'			  => '6fee6888-0f4f-4f82-8b45-b737ca3f833d',
	'Sinch_API_SECRET'		  => 'MzASSFgxCU6neyjKRKVzXw==',
	
	'QueueForAPI'			  => 'API_QUEUE',
	'QueueForConnect'	      => 'CONNECT_QUEUE',
	
	'QueueForTagEventCountUpdate'	=> 'CONNECT_TAG_COUNT_QUEUE',
	'QueueForNewTag'				=> 'CONNECT_NEW_TAG_QUEUE',
	'QueueForCompetition'			=> 'CONNECT_COMP_QUEUE',
	'QueueForTagInsert'				=> 'CONNECT_TAG_INSERT_QUEUE',
	'QueueForEmailAndSMS'			=> 'CONNECT_EMAIL_SMS_QUEUE',
	'QueueForEventUpdatePush'		=> 'CONNECT_EVENT_PUSH_QUEUE',
	'QueueForAudioTrimGeneration'	=> 'CONNECT_AUDIO_TRIM_QUEUE',
	
	'WebSocketServerPort'	  => '9432',
	'WebSocketURL'			  => 'wss://connect.airshr.net:9432/connect',
	'WebSocketSever'		  => 'tcp://0.0.0.0:9432',
	'WebSocketSecureSever'	  => 'tls://connect.airshr.net:9432',
	'WebSocketSecureSeverLocal'	  => 'tls://127.0.0.1:9432',
	'WebSocketPEMFile'		  => '/cert/airshrnet.pem',
	
	'Resque_Redis_Server_Host'		=> 'connect.airshr.net',
	'Resque_Redis_Server_Port'		=> '36379',
	'Resque_Redis_Server_Password'		=> 'airshrredisP@ssw0rd',
	'Resque_Websocket_Queue'			=> 'airshr:websocket_message_requests', 
	'Resque_TagEventCount_Queue'			=> 'airshr:tag_count_update',
	'Resque_TagVoteCount_Queue'			=> 'airshr:vote_count_update',
	
	'StartupEventTags'		  => array('236662', '237022', '263318', '237017'),
	
	'AirShrShareURLBase'	  => 'http://airshrd.com/share/',
    'AirShrAudioDownloadURLBase'	  => 'http://airshrd.com/audioDownload/',
	
	'AirShrConnectBaseURL'	  => 'https://connect.airshr.net',
	
	'AirShrGooglePlayStoreURL'	=> 'https://play.google.com/store/apps/details?id=com.airshr.androidapp',
	'AirShrAppStoreURL'			=> 'https://itunes.apple.com/au/app/airshr/id970256863?mt=8',
	//'AirShrGetAppURL'			=> 'http://goo.gl/F9oe5W'
	'AirShrGetAppURL'			=> 'http://airshrd.com/F9oe5W'
];
