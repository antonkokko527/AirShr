<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


$API_DOMAIN_NAME 		= env('API_DOMAIN_NAME', 		'webservice.airshr.net');
$CONNECT_DOMAIN_NAME 	= env('CONNECT_DOMAIN_NAME', 	'connect.airshr.net');
$SHARE_DOMAIN_NAME 		= env('SHARE_DOMAIN_NAME', 		'airshrd.com');


Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);



/**
 * Share Domain Roues
 */
Route::group(['domain' => $SHARE_DOMAIN_NAME], function () {
	Route::get('share/{hash}', 'ShareController@showContent');
	Route::get('share', 'ShareController@showContentDefault');
	Route::get('show-player/{id}', 'ShareController@showPlayer');
    Route::get('audioDownload/{hash}', 'ShareController@audioDownload');
	
	Route::get('F9oe5W', 'MainController@getApp');
	
	// Tag Related
	Route::resource('tag', 'TagController',
					['only' => ['show']]);
	
	Route::get('redirect', 'EventController@redirectLink');
});

/**
 * API Domain Routes
 */
Route::group(['domain' => $API_DOMAIN_NAME], function () {

	// For testing purpose
	Route::get('event/justfortest', 'EventController@test');
	
	// Event related
	Route::get('event/updateEventWithMatcher', 'EventController@updateEventWithMatcher');
	Route::post('event/createEventFromTag', 'EventController@createEventFromTag');

	Route::post('event/getPreviousSegment', 'EventController@createEventFromPreviousTag');
	
	Route::get('event/diagnostics/{id}', 'EventController@getEventDiagnostics');
	
	Route::get('event/diagnostics2/{id}', 'EventController@getEventDiagnostics2');
	
	Route::post('event/saveMyVote', 'EventController@saveMyVote');
	
	Route::post('event/saveMyRate', 'EventController@saveEventRate');
	
	Route::post('event/acceptClosestMatch', 'EventController@acceptClosestMatch');

	Route::resource('event', 'EventController',
					['only' => ['store', 'show', 'index', 'destroy']]);
	
	
	// Content Type related
	Route::resource('contentType', 'ContentTypeController',
					['only' => ['index']]);
	
	Route::get('station/getRegionStations', 'StationController@getRegionStations');
	
	// Stream and Region Related
	Route::post('station/saveVote', 'StationController@saveStationVote');
	Route::post('stream/updateStreamingStatus', 'StreamController@updateStreamingStatus');
	
	// Stations related
	Route::resource('station', 'StationController',
					['only' => ['index']]);
	
	
	// Main web service endpoint
	Route::get('main/firmware', 'MainController@getLatestFirmware');
	
	// Main web service endpoint
	Route::get('main/appVersion', 'MainController@getLatestAppVersionInfo');
	
	// User Related
	Route::post('user/requestVerification', 'UserController@requestVerification');
	Route::post('user/verify', 'UserController@verifyPhoneNumber');
	
	Route::get('user/justfortest', 'UserController@test');
	
	Route::resource('user', 'UserController',
					['only' => ['store']]);
	
	Route::get('tag/diagnostics/{id}', 'TagController@getTagEventsDiagnostics');
	
	// Tag Related
	Route::resource('tag', 'TagController',
					['only' => ['index']]);
	
	Route::get('coverart/info', 'EventController@getCoverartInfo');
});



//Route::get('brief', 'ScriptTestController@brief');
//
//Route::get('script', 'ScriptTestController@script');
//
Route::get('scriptView', 'ConnectController@scriptView');

/**
 *  Connect Domain Routes
 */
Route::group(['domain' => $CONNECT_DOMAIN_NAME], function () {

	// Tag related
	/*Route::post('tag/metaTagFromWollongong', 'TagController@metaTagFromWollongong');
	Route::post('tag/metaTagFromStation', 'TagController@metaTagFromStation');
	Route::post('tag/metaTalkSignalFromStation', 'TagController@metaTalkSignalFromStation');*/
	
	Route::resource('tag', 'TagController',
					['only' => ['store', 'show', 'index']]);
	
	// Test
	Route::get('test/setTerrestrialLog', 'TestController@changeLatestTerrestrialLog');
	
	// AirShr Connect
	Route::get('/', 'ConnectController@index');
	Route::get('/home', 'ConnectController@index');
	Route::get('/connect/test', 'ConnectController@test');
	
	// Promotion
	Route::get('/getApp', 'MainController@getApp');
	Route::post('/sendAppLink', 'UserController@sendAppLink');
	Route::get('/sendAppLink', 'UserController@sendAppLink');

	// Section for authenticated user
	Route::group(['middleware' => ['auth']], function() {
	
		Route::get('dashboard', 'ConnectController@dashboard');

		Route::get('dashboard/map', 'ConnectController@map');

		Route::get('dashboard/musicRatings', 'ConnectController@musicRatingDashboard');

		Route::get('dashboard/musicRatings/{id}', 'ConnectController@musicRatingDashboard');

		Route::get('brief', 'ConnectController@brief');

		Route::get('script', 'ConnectController@script');

//		Route::get('scriptView', 'ConnectController@scriptView');

		Route::get('getMomentsByDate/{date}', 'ConnectController@getMomentsByDate');

		Route::get('getMomentsByDate/{startDate}/{endDate}', 'ConnectController@getMomentsByDate');

        Route::get('getInternalShare/{id}', 'ConnectController@getInternalShare');
		
		Route::post('musicRatingStatistics', 'ConnectController@musicRatingStatistics');

		Route::post('getSongStatistics', 'ConnectController@getSongStatistics');

		Route::get('getSongStatistics', 'ConnectController@getSongStatistics');

		Route::get('getSongs', 'ConnectController@getSongs');

		Route::get('getSongsWithData', 'ConnectController@getSongsWithData');

		Route::get('watchMusicRating/{id}', 'ConnectController@watchMusicRating');
		
		Route::get('getPopularTags/{date}/{hour}/{contentTypeId}', 'ConnectController@getPopularTags');

		Route::post('emailPopularTags', 'ConnectController@emailPopularTags');

		Route::post('emailMusicRating', 'ConnectController@emailMusicRating');
		
		Route::post('getSpreadsheet', 'ConnectController@getSpreadsheet');

		Route::get('getCompetitionAndVoteMoments/{date}', 'ConnectController@getCompetitionAndVoteMomentsByDate');

		Route::get('getCompetitionAndVoteMoments/{startDate}/{endDate}', 'ConnectController@getCompetitionAndVoteMomentsByDate');

		Route::get('getDailyLogCounts', 'ConnectController@getDailyLogCounts');

		Route::get('setCompetitionTags', 'ConnectController@setCompetitionTags');
		
		Route::get('getMomentsByMonth', 'ConnectController@getMomentsByMonth');

		Route::get('getMomentsTodayAndMonth', 'ConnectController@getMomentsTodayAndMonth');

		Route::get('getDownloads', 'ConnectController@getDownloads');

		Route::get('getUsersByClicks', 'ConnectController@getUsersByClicks');

		Route::get('getSourceOfListeners', 'ConnectController@getSourceOfListeners');

		Route::get('getEventLocationsByDate/{date}', 'ConnectController@getEventLocationsByDate');
		
		Route::get('getEventLocationsByDate/{start_date}/{end_date}/{start_time}/{end_time}', 'ConnectController@getEventLocationsByDate');

		Route::get('getNumberOfUsers', 'ConnectController@getNumberOfUsers');

		Route::get('getStreamTime','ConnectController@getStreamTime');

		Route::get('getContentTypePercentages/{startDate}/{endDate}', 'ConnectController@getContentTypePercentages');

		Route::get('getContentTypePercentages', 'ConnectController@getContentTypePercentages');
		
		Route::get('content', 'ConnectController@content');
	
		//Route::post('content/upload', 'ConnectController@uploadFile');
		Route::post('content/upload', 'ConnectController@uploadFileToCloud');
	
		Route::post('content/audioUpload', 'ConnectController@audioUpload');
	
		Route::post('content/removeAttachment', 'ConnectController@removeFile');
	
		Route::get('content/playAttachment/{id}', 'ConnectController@playAttachment');
	
		Route::post('content/save', 'ConnectController@saveContent');

		Route::post('content/saveClientInfo', 'ConnectController@saveClientContent');

		Route::post('content/saveClientInline', 'ConnectController@saveClientInline');

		Route::post('content/saveImages', 'ConnectController@saveImages');
	
		Route::post('content/list', 'ConnectController@listContent');
	
		Route::post('content/material/copyWithNewVersion', 'ConnectController@copyWithNewVersion');

		Route::post('content/copyClientToAdUsingMI', 'ConnectController@copyClientToAdUsingMI');

		Route::post('content/copyClientToAd', 'ConnectController@copyClientToAd');

		Route::post('content/copyClient', 'ConnectController@copyClient');
	
		Route::post('content/copyContent', 'ConnectController@copyContent');
		
		Route::post('content/copyAdToAd', 'ConnectController@copyAdToAd');
	
		Route::post('content/syncSubContent', 'ConnectController@syncSubContent');

		Route::get('content/print/{id}', 'ConnectController@printContent');

		Route::get('content/printTalk/{week}', 'ConnectController@printTalkRoster');
	
		Route::post('content/listAudio', 'ConnectController@listAudio');
	
		Route::get('content/show/{id}', 'ConnectController@contentDetail');
	
		Route::get('content/material/showAdDetails/{id}', 'ConnectController@adDetailForMIRow');
	
		Route::post('content/material/newAd', 'ConnectController@createTempAd');

		Route::get('content/news/{id}', 'ConnectController@news');

		Route::get('content/news/', 'ConnectController@news');

		Route::get('content/getNews/{id}', 'ConnectController@getNews');

		Route::get('content/ad/{id}', 'ConnectController@ad');

		Route::get('content/ad/', 'ConnectController@ad');
	
		Route::get('content/clientList', 'ConnectController@stationClientList');

		Route::get('content/tradingNameList', 'ConnectController@tradingNameList');

		Route::get('content/agencyList', 'ConnectController@agencyList');

		Route::get('content/talkShowList', 'ConnectController@talkShowList');
	
		Route::get('content/talentList', 'ConnectController@talentList');

		Route::get('content/getTalkShows', 'ConnectController@getTalkShowsJSON');

		Route::get('content/talkBreak', 'ConnectController@talkBreak');
		
		Route::get('content/talkBreak/{id}', 'ConnectController@talkBreak');

		Route::post('content/getAgencyDetails', 'ConnectController@getAgencyDetails');

		Route::get('content/getClientExecutiveList', 'ConnectController@getClientExecutiveList');

		Route::post('content/client/byname', 'ConnectController@clientDetailByName');

		Route::post('content/client/bytradingname', 'ConnectController@clientDetailByTradingName');

		Route::get('content/clientInfo/{id}', 'ConnectController@clientInfo');

		Route::get('content/clientInfo/', 'ConnectController@clientInfo');

		Route::post('content/clientInfo/', 'ConnectController@postClientInfo');

		Route::get('content/getClientInfo/{id}', 'ConnectController@getClientInfo');

		Route::post('content/readyClientInfo/', 'ConnectController@readyClientInfo');

		Route::get('content/productList', 'ConnectController@stationProductList');
	
		Route::post('content/material/updateAd', 'ConnectController@updateTempAd');

		Route::post('content/updateMusic', 'ConnectController@updateMusic');

		Route::post('content/updateMusicData', 'ConnectController@updateMusicData');
		
		Route::post('listGooglePlay', 'ConnectController@listGooglePlay');

		Route::get('listGooglePlay', 'ConnectController@listGooglePlay');

		Route::post('listITunes', 'ConnectController@listITunes');

		Route::get('listITunes', 'ConnectController@listITunes');

		Route::post('content/updateMusicTag', 'ConnectController@updateMusicTag');

		Route::post('content/updateEvent', 'ConnectController@updateEvent');

		Route::post('content/updateSingleEvent', 'ConnectController@updateSingleEvent');
	
		Route::post('content/readyContent', 'ConnectController@readyContent');

		Route::post('content/setCompetition', 'ConnectController@setCompetition');

		Route::post('content/setVote', 'ConnectController@setVote');

		Route::post('content/removeEvent', 'ConnectController@removeEvent');
	
		Route::post('content/removeSingleEvent', 'ConnectController@removeSingleEvent');
	
		Route::post('content/removeContent', 'ConnectController@removeContent');

		Route::post('content/removeClient', 'ConnectController@removeClient');
	
		Route::post('content/removeContentFromParent', 'ConnectController@removeContentFromParent');
	
		Route::post('content/createAdFromAudio', 'ConnectController@createAdFromAudio');
	
		Route::post('content/createAdFromPreviewTag', 'ConnectController@createAdFromPreviewTag');
	
		Route::post('content/createAdFromTag', 'ConnectController@createAdFromTag');
	
		Route::post('content/createMaterialInstructionFromPreviewTag', 'ConnectController@createMaterialInstructionFromPreviewTag');
	
		Route::post('content/createTalkShow', 'ConnectController@createTalkShow');
	
		Route::get('content/air', 'ConnectController@onAir');
	
		Route::post('content/air/airData', 'ConnectController@onAirData');
	
		Route::post('content/previewlog', 'ConnectController@previewLogData');
	
		Route::get('content/attachment/imageInfo/{id}', 'ConnectController@attachmentImageMetaData');
	
		Route::post('content/attachment/updateImageInfo', 'ConnectController@updateImageMetaData');
	
		Route::post('content/createTagFromContent', 'ConnectController@createTagFromContent');
	
		Route::get('content/getCompetitionResultContent', 'ConnectController@getCompetitionResultContent');
	
		Route::get('content/scheduler', 'ConnectController@scheduler');

		Route::get('content/musicMix', 'ConnectController@musicMix');
		
		Route::get('content/getMusicMixes', 'ConnectController@getMusicMixesJSON');

		Route::post('content/createMusicMix', 'ConnectController@createMusicMix');
		
		Route::get('content/musicMixTitleList', 'ConnectController@musicMixMetadataTitleList');

		Route::get('content/musicMixWhatList', 'ConnectController@musicMicWhatList');

		Route::get('content/musicMixWhoList', 'ConnectController@musicMixWhoList');

		Route::get('content/musicRating', 'ConnectController@musicRating');
		
		Route::get('content/searchMusic', 'ConnectController@searchMusic');

		Route::get('content/getSong/{id}', 'ConnectController@getSong');

		Route::get('content/getSong/{id}/{type}', 'ConnectController@getSong');
		
		Route::get('content/listMusicRatings', 'ConnectController@listMusicRatings');

		Route::post('content/saveMusicRating', 'ConnectController@saveMusicRating');

		Route::post('content/endMusicRating', 'ConnectController@endMusicRating');

		Route::get('connect/setProfanityDelay', 'StationController@setProfanityDelay');
		
		Route::post('content/createManualTag', 'TagController@createManualTag');

		
	});
	
});
