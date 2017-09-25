<?php

namespace App\Http\Controllers;

use App\ConnectContentAgency;
use App\CoverArtRating;
use App\MusicRating;
use Aws\CloudFront\Exception\Exception;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use App\Analytics;
use App\ConnectUser;
use App\ContentType;
use App\ConnectContent;
use App\ConnectContentAction;
use App\ConnectContentAttachment;
use App\ConnectContentClient;
use App\ConnectContentProduct;
use App\ConnectContentBelongs;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Process\Process;

use App\Tag;
use App\WebSocketPub;
use App\User;
use App\Station;

use Request;
use File;

use abeautifulsite\SimpleImage;
use App\PreviewTag;

use App\CoverArt;
use App\Remote;

use App\Competition;
use GuzzleHttp\json_decode;
use Aws\Sqs\SqsClient;

class ConnectController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		if (!\Auth::User()->isInvestor()) {
			return new RedirectResponse(url('/content'));
		} else {
			return new RedirectResponse(url('/dashboard'));
		}
		
	}

	public function copyClientToAdUsingMI() {
		try {
			$adId = Request::input('ad_id');
			$materialId = Request::input('material_id');

			$ad = ConnectContent::findOrFail($adId);
			$material = ConnectContent::findOrFail($materialId);
			$client = ConnectContentClient::findOrFail($material->content_client_id);

			$ad->who = $client->who;
			$ad->what = $client->what;
			$ad->more = $client->more;
			$ad->action_id = $client->action_id;
			$ad->action_params = $client->action_params;
			$ad->map_address1 = $client->map_address1;
			$ad->map_address2 = $client->map_address2;
			$ad->map_address1_lat = $client->map_address1_lat;
			$ad->map_address1_lng = $client->map_address1_lng;
			$ad->content_client_id = $client->id;
			$ad->content_product_id = $client->product_id;

			// copy attachments
			$attachment_ids[] = $client->logo_attachment_id;
			$attachment_ids[] = $client->image_attachment1_id;
			$attachment_ids[] = $client->image_attachment2_id;
			$attachment_ids[] = $client->image_attachment3_id;

			foreach ($attachment_ids as $attachment_id) {
				if($attachment_id) {
					$attachment = ConnectContentAttachment::findOrFail($attachment_id);
					$newAttachment = $attachment->copyAttachment($adId);
				}
			}

			$ad->save();
			return $this->adDetailForMIRow($adId);
//			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('content' => $ad)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function copyClientToAd() {
		try {
			$adId = Request::input('ad_id');
			$clientId = Request::input('client_id');

			if(empty($adId)) {
				$ad = array();
			} else {
				$ad = ConnectContent::findOrFail($adId);
			}
			$client = ConnectContentClient::findOrFail($clientId);

			$ad['who'] = $client->who;
			$ad['what'] = $client->what;
			$ad['more'] = $client->more;
			$ad['content_type_id'] = ContentType::GetAdContentTypeID();
			$ad['action_id'] = $client->action_id;
			$ad['action_params'] = $client->action_params;
			$ad['map_address1'] = $client->map_address1;
			$ad['map_address2'] = $client->map_address2;
			$ad['map_address1_lat'] = $client->map_address1_lat;
			$ad['map_address1_lng'] = $client->map_address1_lng;
			$ad['content_client_id'] = $client->id;
			$ad['content_product_id'] = $client->product_id;

			if(empty($adId)) {
				$ad = ConnectContent::create($ad);
				$adId = $ad->id;
			} else {
				$ad->save();
			}

			// copy attachments
			$attachment_ids[] = $client->logo_attachment_id;
			$attachment_ids[] = $client->image_attachment1_id;
			$attachment_ids[] = $client->image_attachment2_id;
			$attachment_ids[] = $client->image_attachment3_id;

			foreach ($attachment_ids as $attachment_id) {
				if($attachment_id) {
					$attachment = ConnectContentAttachment::find($attachment_id);
					if($attachment) {
						$newAttachment = $attachment->copyAttachment($adId);
					}
				}
			}

			//Get product and client name
			$ad->client_name = $client->client_name;
			$ad->product_name = ConnectContentProduct::find($client->product_id)->product_name;

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('content' => $ad)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function copyClient() {
		try {
			$new_client_name = Request::input('new_client_name');

			if(empty($new_client_name)) {
				return response()->json(array('code' => -1, 'msg' => "Please fill in a client name"));
			}

			$existing_client = ConnectContentClient::clientExists(\Auth::User()->station->id, $new_client_name);

//			\Log::info($existing_client);

			if(!empty($existing_client)) {
				return response()->json(array('code' => -1, 'msg' => "Client with name '{$new_client_name}' already exists"));
			}

			$clientId = Request::input('client_id');

			$source_client = ConnectContentClient::findOrFail($clientId);

			$dest_client = $source_client->replicate();

			$dest_client->client_name = $new_client_name;

			// copy attachments
//			$source_logo = ConnectContentAttachment::find($source_client->logo_attachment_id);
//			$source_attachment1 = ConnectContentAttachment::find($source_client->image_attachment1_id);
//			$source_attachment2 = ConnectContentAttachment::find($source_client->image_attachment2_id);
//			$source_attachment3 = ConnectContentAttachment::find($source_client->image_attachment3_id);
//
//			if($source_logo) {
//				$dest_client->logo_attachment_id = $source_logo->copyAttachment()->id;
//			}
//			if($source_attachment1) {
//				$dest_client->image_attachment1_id = $source_attachment1->copyAttachment()->id;
//			}
//			if($source_attachment2) {
//				$dest_client->image_attachment2_id = $source_attachment2->copyAttachment()->id;
//			}
//			if($source_attachment3) {
//				$dest_client->image_attachment3_id = $source_attachment3->copyAttachment()->id;
//			}

			$dest_client->save();
//
//			//Get product and client name
//			$dest_client['client_name'] = $source_client->client_name;
//			$dest_client['product_id'] = $source_client->product_id;

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('new_client' => $dest_client)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	public function copyAdToAd() {
		try {
			$sourceId = Request::input('source_id');
			$destId = Request::input('dest_id');
			$source = ConnectContent::findOrFail($sourceId);
			$dest = ConnectContent::findOrFail($destId);

			$dest['who'] = $source->who;
			$dest['what'] = $source->what;
			$dest['more'] = $source->more;
			$dest['content_type_id'] = $source->content_type_id;
			$dest['action_id'] = $source->action_id;
			$dest['action_params'] = $source->action_params;
			$dest['map_address1'] = $source->map_address1;
			$dest['map_address2'] = $source->map_address2;
			$dest['map_address1_lat'] = $source->map_address1_lat;
			$dest['map_address1_lng'] = $source->map_address1_lng;

			$dest->save();

			$attachments = ConnectContentAttachment::where('content_id', '=', $sourceId);
			foreach ($attachments as $attachment) {
				$newAttachment = $attachment->copyAttachment($destId);
			}

			//Get product and client name

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' =>  $dest));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Create Material Instruction from preview tag
	 */
	public function createMaterialInstructionFromPreviewTag() {

		try {

			$previewTagId = Request::input('tag_id');

			$previewTagObject = PreviewTag::findOrFail($previewTagId);

			$user = \Auth::User();

			$data = array();

			$data['station_id'] = $user->station->id;
			$data['content_type_id'] = ContentType::findContentTypeIDByName('Material Instruction');
			$data['connect_user_id'] = $user->id;
			$data['who'] = $previewTagObject->who;
			$data['is_temp'] = 0;

			$mdContent = ConnectContent::create($data);

			$data['content_type_id'] = ContentType::findContentTypeIDByName('Ad');
			$data['ad_key'] = $previewTagObject->adkey;
			//$data['start_date'] = date("Y-m-d", $previewTagObject->tag_timestamp);
			//$data['end_date'] = date("Y-m-d", strtotime("+1 month", $previewTagObject->tag_timestamp));
			$data['is_temp'] = 0;

			$adContent = ConnectContent::create($data);
			$date_id = $adContent->addContentDate(0, date("Y-m-d", getSecondsFromMili($previewTagObject->tag_timestamp)), date("Y-m-d", strtotime("+1 month", getSecondsFromMili($previewTagObject->tag_timestamp))));
			ConnectContentBelongs::addBelongsInfo($mdContent->id, $adContent->id, 0, $date_id);

			$adContent->updateContentToTagsLink();

			$adContent->searchAudioFileAndLink();

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $mdContent->id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	public function brief($id = 0) {
		return view('airshrconnect.brief')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('contentID', $id);
	}

	public function script($id = 0) {
		return view('airshrconnect.script')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('contentID', $id);
	}

	public function scriptView($id = 0) {
		return view('airshrconnect.scriptview')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('contentID', $id);
	}

	public function musicRating($id = 0) {
		return view('airshrconnect.musicrating')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('contentID', $id);
	}

	public function saveMusicRating() {

		try {
			$musicRating = [];

			$musicRating['coverart_id'] = Request::input('coverart_id');
			$musicRating['author_id'] = \Auth::User()->id;
			$musicRating['station_id'] = \Auth::User()->station->id;
			$musicRating['start_date'] = Request::input('start_date');
			$musicRating['end_date'] = Request::input('end_date');

			$musicRatingId = Request::input('music_rating_id');

			if(!empty($musicRatingId)) {
				$musicRatingObj = CoverArtRating::find($musicRatingId);
				$musicRatingObj->coverart_id = $musicRating['coverart_id'];
				$musicRatingObj->author_id = $musicRating['author_id'];
				$musicRatingObj->station_id =  $musicRating['station_id'];
				$musicRatingObj->start_date = $musicRating['start_date'];
				$musicRatingObj->end_date = $musicRating['end_date'];
				
				$musicRatingObj->save();
			} else {
				$musicRatingObj = CoverArtRating::create($musicRating);
			}

			return array('code' => 0, 'musicRating' => $musicRatingObj);
		} catch (\Exception $e) {
			return array('code' => -1, 'msg' => $e->getMessage());
		}
		
	}

	public function endMusicRating() {

		try {
			$musicRatingId = Request::input('music_rating_id');

			if(!empty($musicRatingId)) {
				$timezone = \Auth::User()->station->getStationTimezone();

				$musicRatingObj = CoverArtRating::find($musicRatingId);

				$musicRatingObj->end_date = parseDateToMySqlFormat(Carbon::now($timezone)->toDateString());

				$musicRatingObj->save();
			}

			return array('code' => 0);
		} catch (\Exception $e) {
			return array('code' => -1, 'msg' => $e->getMessage());
		}

	}

	public function listMusicRatings() {
		$station_id = \Auth::User()->station->id;
		
		$musicRatings = \DB::table('airshr_coverart_ratings')
			->selectRaw('airshr_coverart_ratings.id as coverart_rating_id, airshr_coverart_ratings.*, airshr_coverarts.*')
			->where('airshr_coverart_ratings.station_id', '=', $station_id)
			->join('airshr_coverarts', 'airshr_coverart_ratings.coverart_id', '=', 'airshr_coverarts.id')
			->orderBy('airshr_coverart_ratings.end_date', 'desc')
			->get();
		
		return array('code' => 0, 'musicRatings' => $musicRatings);
	}
	
	/**
	 * Search music and return most played coverart
	 */
	public function searchMusic() {
		try {
			$artist = Request::input('artist');
			$track = Request::input('track');

			
			if(empty($artist) && empty($track)) {
				return array('code' => -1, 'msg' => 'Artist and track missing.');
			}
			
			$song = CoverArt::getCoverArtInfo($artist, $track);

			if(empty($song)) {
				return array('code' => -1, 'msg' => 'Song not found');
			}

			return array('code' => 0, 'song' => $song);
		} catch (Exception $ex) {
			return array('code' => -1, 'msg' => $ex->getMessage());
		}
	}

	public function getSong($id, $type) {
		try{
			$song = CoverArt::find($id);
			
			if(!empty($song)) {
				$song->attachments = $song->getCoverArtAttachmentsArray();

				$song->stream_url = $song->preview;
				$song->stream_duration = 30;
			}
	
			return array('code' => 0, 'song' => $song);
		} catch (Exception $ex) {
			return array('code' => -1, 'msg' => $ex->getMessage());
		}
	}

	public function dashboard() {
		if (!\Auth::User()->isAdminUser() && !\Auth::User()->isInvestor()) {
			die("Not authorized.");
			exit();
		}
		
		return view('dashboard.dashboard')
			->with('content_type_list', ContentType::$CONTENT_TYPES);
	}

	public function map() {
		if (!\Auth::User()->isAdminUser() && !\Auth::User()->isInvestor()) {
			die("Not authorized.");
			exit();
		}

		return view('dashboard.map')
			->with('content_type_list', ContentType::$CONTENT_TYPES);
	}

	public function musicRatingDashboard($id = 0) {
		if (!\Auth::User()->isAdminUser() && !\Auth::User()->isInvestor()) {
			die("Not authorized.");
			exit();
		}

		return view('dashboard.musicrating')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('id', $id);
	}

    public function getInternalShare($id) {
        try {
            $tag = Tag::findOrFail($id);

//            $stationId = \Auth::User()->station->id;

            $hash = $tag->createHashForTag();

            $url = \Config::get('app.AirShrShareURLBase') . $hash . '?station=true'; //$stationId;

            $audioUrl = \Config::get('app.AirShrAudioDownloadURLBase') . $hash;

//            $url = 'http://localhost:8000/share/' . $hash . '?station=' . $stationId;

            $tagDetail = $tag->getJSONArrayForTagDetail();

            return response()->json(array('code' => 0, 'hash' => $hash, 'data' => $tagDetail, 'url' => $url, 'audioUrl' => $audioUrl));
        }
        catch(\Exception $ex) {
            \Log::error($ex);
            return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

        }


    }

	
	public function getSongsWithData() {
		try {
			$startDate = Request::input('startDate', 0);
			$endDate = Request::input('endDate', 0);
			$artist = Request::input('artist', '');
			$title = Request::input('title', '');

			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();

			$now = Carbon::now($timezone);

			$numberOfWeeks = 8;

			if(!empty($startDate) && !empty($endDate)) {
				$startDate = Carbon::createFromFormat('Y-m-d', $startDate);
				$endDate = Carbon::createFromFormat('Y-m-d', $endDate);
			} else {
				$startDate = $now->copy()->subWeeks(3);
				$endDate = $now;
			}

			$dataStartDate = $now->copy()->subWeeks($numberOfWeeks)->startOfWeek();

			$dataStartDateMilli = $dataStartDate->timestamp * 1000;

			//This is a join of tags with coverarts and events.
			//We get the events for all tags that are songs in the past $numberOfWeeks weeks
			//We use this to count ratings for songs.
			$songData = \DB::table('airshr_tags')
				->select(\DB::raw('airshr_coverarts.artist as artist, airshr_coverarts.track as title, airshr_coverarts.id as coverart_id, airshr_coverarts.coverart_url, airshr_tags.id as tag_id, airshr_tags.*, airshr_events.*'))
				->where('airshr_tags.tag_timestamp', '>', $dataStartDateMilli)
				->where('airshr_tags.tag_timestamp', '<=', $now->timestamp * 1000)
				->where('airshr_tags.content_type_id', '=', 2)
				->where('airshr_tags.coverart_id', '<>', 0)
				->where('airshr_tags.station_id', '=', $station->id)
				->join('airshr_coverarts', 'airshr_coverarts.id', '=', 'airshr_tags.coverart_id')
				->join('airshr_events', 'airshr_tags.id', '=', 'airshr_events.tag_id');

			$tags = \DB::table('airshr_tags')
				->select(\DB::raw("count(airshr_tags.id) as count,
						airshr_coverarts.artist as artist,
						airshr_coverarts.track as title,
						FLOOR((airshr_tags.tag_timestamp - {$dataStartDateMilli})/(1000*60*60*24*7)) as week")) //1000*60*60*24*7 milliseconds in a week, dividing by that gives us week number
				->where('airshr_tags.content_type_id', '=', 2)
				->where('airshr_tags.coverart_id', '<>', 0)
				->where('airshr_tags.station_id', '=', $station->id)
				->join('airshr_coverarts', 'airshr_coverarts.id', '=', 'airshr_tags.coverart_id')
				->orderBy('week')
				->groupBy(\DB::raw('artist, title, week'))
				->get();

			if(!empty($artist)) {
				$songData = $songData->where('artist', 'LIKE', "%{$artist}%");
			}
			if(!empty($title)) {
				$songData = $songData->where('track', 'LIKE', "%{$title}%");
			}

			$songData = $songData->get();

			//Statistics calculations
			$songs = [];

			$resultSongs = [];

			//Go through each song data point (which corresponds to an event joined with a tag that has a coverart id)
			foreach($songData as $songDataPoint) {

				$songKey = $songDataPoint->artist . ' - ' . $songDataPoint->title;

				//Only get the songs that happened within the time range specified
				if($songDataPoint->tag_timestamp > $startDate->timestamp * 1000 && $songDataPoint->tag_timestamp < $endDate->timestamp * 1000) {

					//Add the song to our 'songs' map which contains unique songs grouped by artist-title
					if(!isset($songs[$songKey])) {
						$songs[$songKey] = [];
						$songs[$songKey]['artist'] = $songDataPoint->artist;
						$songs[$songKey]['title'] = $songDataPoint->title;
						$songs[$songKey]['coverart_id'] = $songDataPoint->coverart_id;
						$songs[$songKey]['coverart_url'] = $songDataPoint->coverart_url;

						//Setup the data structure for rating statistics
						for($i = 0; $i < $numberOfWeeks; $i++) {
							$songs[$songKey][$i] = [
								'love' => 0,
								'like' => 0,
								'hate' => 0,
								'rates' => 0,
								'airshrs' => 0,
								'play_count' => 0,
								'week' => $dataStartDate->copy()->addWeeks($i)->format('Y-m-d')
							];
						}

						$songs[$songKey]['total'] = [
							'love' => 0,
							'like' => 0,
							'hate' => 0,
							'rates' => 0,
							'airshrs' => 0,
							'play_count' => 0,
							'week' => $dataStartDate->format('Y-m-d')
						];

						//Retrieve the tag count for each week, which is the number of plays.
						foreach($tags as $tag) {
							if(cleanString($songDataPoint->artist) == cleanString($tag->artist) && cleanString($songDataPoint->title) == cleanString($tag->title)) {
								if($tag->week >= 0 && $tag->week < $numberOfWeeks) {
									$songs[$songKey][$tag->week]['play_count'] += $tag->count;
								}
								$songs[$songKey]['total']['play_count'] += $tag->count;

								if(!isset($songs[$songKey]['numberOfWeeks'])) {
									$songs[$songKey]['numberOfWeeks'] = abs($tag->week - $numberOfWeeks);
								}
							}
						}

					}

				}

				//Week number corresponds to the week since $numberOfWeeks weeks ago, so that the week $numberOfWeeks weeks ago is 0 and the next week is 1 and so forth.
				$currDate = Carbon::createFromTimestamp(floor($songDataPoint->tag_timestamp/1000), $timezone);
				$weekNumber = $currDate->diffInWeeks($dataStartDate);//floor(($songDataPoint->tag_timestamp - ($dataStartDate->timestamp * 1000))/(1000*60*60*24*7));

				if($weekNumber < 0) continue;

				//If the song exists in our 'songs' map, we want to calculate the ratings and airshrs for this song.
				//Store the data by week number
				if(isset($songs[$songKey])) {

					$option = $songDataPoint->rate_option;

					if( in_array($option, ['love', 'like', 'hate']) ) {
						$songs[$songKey][$weekNumber][$option]++;
						$songs[$songKey][$weekNumber]['rates']++;
						if($weekNumber < $numberOfWeeks - 1) {
							$songs[$songKey][$weekNumber + 1][$option]++;
							$songs[$songKey][$weekNumber + 1]['rates']++;
						}
						$songs[$songKey]['total'][$option]++;
					}

					$songs[$songKey][$weekNumber]['airshrs']++;
					$songs[$songKey]['total']['airshrs']++;

				}

			}

			//Filter out songs that don't have any ratings
			foreach($songs as $song) {
				if($song['total']['love'] != 0 || $song['total']['like'] != 0 || $song['total']['hate'] != 0)
					$resultSongs[] = $song;
			}

			return response()->json(array('code' => 0, 'songs' => $resultSongs, 'numberOfWeeks' => $numberOfWeeks));
		
		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

		}
	}

	public function getSpreadsheet() {
		try {
			$rawData = Request::input('data');
			$imagesRaw = Request::input('images');

//			$images = explode(',', $imagesRaw);

			$data = file_get_contents($rawData);

			//Storing xls into memory and then reading it using PHPExcel
			//http://stackoverflow.com/questions/9538626/load-excel-file-into-php-excel-from-variable
			$file = tempnam(sys_get_temp_dir(), 'excel_');
			$handle = fopen($file, "w");
			fwrite($handle, $data);

			libxml_use_internal_errors(true);
			$objPHPExcel = \PHPExcel_IOFactory::load($file);

			fclose($handle);
			unlink($file);

			$sheet = $objPHPExcel->getActiveSheet();

			$highestRowAndColumn = $sheet->getHighestRowAndColumn();

			//Setting Header Styles
			$headerStyle = [
				'alignment' => [
					'wrap' => true
				],
				'borders' => [
					'outline' => [
						'style' => \PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['argb' => 'FF000000'],
					],
				],
				'fill' => [
					'type' => \PHPExcel_Style_Fill::FILL_SOLID,
					'color' => ['argb' => 'FFE0E0E0']
				],
				'font' => [
					'size' => 16
				]
			];
			$sheet->getStyle('A1:'.$highestRowAndColumn['column'].'1')->applyFromArray($headerStyle);

			//Border for the table
			$borderStyle = [
				'borders' => [
					'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
				]
			];
			$sheet->getStyle('A1:'.$highestRowAndColumn['column'].$highestRowAndColumn['row'])->applyFromArray($borderStyle);

			$indexColumnIndex = 'A';
			$artistColumnIndex = 'B';
			$titleColumnIndex = 'C';

			$weekCommencingColumnIndex = '';

			if($highestRowAndColumn['column'] == 'M') {
				$weekCommencingColumnIndex = 'D';
				$loveColumnIndex = 'E';
				$likeColumnIndex = 'F';
				$overItColumnIndex = 'G';
				$posAccColumnIndex = 'H';
				$airshrColumnIndex = 'I';
				$ratedColumnIndex = 'J';
				$totalWeeksColumnIndex = 'K';
				$weeklyPlaysColumnIndex = 'L';
				$totalPlaysColumnIndex = 'M';
			} else {
				$loveColumnIndex = 'D';
				$likeColumnIndex = 'E';
				$overItColumnIndex = 'F';
				$posAccColumnIndex = 'G';
				$airshrColumnIndex = 'H';
				$ratedColumnIndex = 'I';
				$totalWeeksColumnIndex = 'J';
				$weeklyPlaysColumnIndex = 'K';
				$totalPlaysColumnIndex = 'L';
			}

			$centerStyle = [
				'alignment' => ['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
			];

			$sheet->getStyle($loveColumnIndex.'1:'.$posAccColumnIndex.$highestRowAndColumn['row'])->applyFromArray($centerStyle);


//            $coverartColumnIndex = 'B';

            //Loop through rows to get pos accs and over its
            //to highlight cells where the value is over a threshold
			$rowIterator = $sheet->getRowIterator();

            $greenFillStyle = [
                'fill' => [
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF7DD3AE']
                ]
            ];

            $redFillStyle = [
                'fill' => [
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFFA9898']
                ]
            ];

			$greyFillStyle = [
				'fill' => [
					'type' => \PHPExcel_Style_Fill::FILL_SOLID,
					'color' => ['argb' => 'FFE5E5E5']
				]
			];

            while($rowIterator->valid()) {
                $rowIndex = $rowIterator->current()->getRowIndex();

                //First heading row
                if($rowIndex == 1) {
                    $rowIterator->next();
                    continue;
                }

				if($rowIndex % 2 != 0) {
					$rowCells = $sheet->getStyle('A'.$rowIndex.':'.$highestRowAndColumn['column'].$rowIndex)->applyFromArray($greyFillStyle);
				}
				//For some reason index is getting stored as text. Cast to integer
				$indexCell = $sheet->getCell($indexColumnIndex.$rowIndex);
				$indexInt = (int) $indexCell->getValue();
				if($indexInt != 0) {
					$indexCell->setValue($indexInt);
				}
				
                //Over its and Pos Accs highlighting
                $overItCell = $sheet->getCell($overItColumnIndex.$rowIndex);
                $overItValue = $overItCell->getValue();

                if($overItValue > 20) {
					$overItCell->getStyle()->applyFromArray($redFillStyle);
                }

				//Pos acc
                $posAccCell = $sheet->getCell($posAccColumnIndex.$rowIndex);
                $posAccValue = $posAccCell->getValue();

                if($posAccValue > 40) {
					$posAccStyle = $posAccCell->getStyle()->applyFromArray($greenFillStyle);
                }


                //Coverart images

//                $artist = $sheet->getCell($artistColumnIndex.$rowIndex)->getValue();
//                $title = $sheet->getCell($titleColumnIndex.$rowIndex)->getValue();
//
//                $coverart = CoverArt::where('artist', '=', $artist)
//                    ->where('track', '=', $title)
//                    ->first();
//
//                $coverartURL ='';
//
//                if($coverart) {
//                    $coverartURL = $coverart->coverart_url;
//                }
//

//                $gdImage = imagecreatefromjpeg(str_replace('600x600', '100x100', isset($images[$rowIndex - 2]) ? $images[$rowIndex - 2] : ''));
//                // Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
//                $objDrawing = new \PHPExcel_Worksheet_MemoryDrawing();
//                $objDrawing->setName('Cover Art');
//				$objDrawing->setDescription('Cover Art');
//                $objDrawing->setImageResource($gdImage);
//                $objDrawing->setRenderingFunction(\PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
//                $objDrawing->setMimeType(\PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
//                $objDrawing->setHeight(50);
//                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
//                $objDrawing->setCoordinates($coverartColumnIndex.$rowIndex);
//
//                $sheet->getRowDimension($rowIndex)->setRowHeight(50);

                $rowIterator->next();
            }

            $columnIterator = $sheet->getColumnIterator();

            while($columnIterator->valid()) {
                $columnIndex = $columnIterator->current()->getColumnIndex();
                /*if($columnIndex == $coverartColumnIndex) {
                    $sheet->getColumnDimension($columnIndex)->setWidth(15);
                } else */

				switch($columnIndex) {
					case $indexColumnIndex:
						break;
					case $artistColumnIndex:
						$sheet->getColumnDimension($columnIndex)->setWidth(25);
						break;
					case $titleColumnIndex:
						$sheet->getColumnDimension($columnIndex)->setWidth(35);
						break;
					case $loveColumnIndex:
					case $likeColumnIndex:
					case $overItColumnIndex:
					case $airshrColumnIndex:
					case $ratedColumnIndex:
					case $totalWeeksColumnIndex:
					case $weeklyPlaysColumnIndex:
					case $totalPlaysColumnIndex:
						$sheet->getColumnDimension($columnIndex)->setWidth(12);
						break;
					case $weekCommencingColumnIndex:
					case $posAccColumnIndex:
						$sheet->getColumnDimension($columnIndex)->setWidth(18);
						break;
					default:
						$sheet->getColumnDimension($columnIndex)->setAutoSize(true);
				}

                $columnIterator->next();
            }

			$sheet->insertNewRowBefore(1, 3);

			$sheet->getCell('A1')->setValue(\Auth::User()->station->station_abbrev);
			$sheet->getCell('A1')->getStyle()->applyFromArray(['font' => ['size' => 18, 'color' => ['argb' => 'FFD61516']]]);
			$sheet->getCell('A2')->setValue('Music Ratings - ' . Carbon::now()->toDayDateTimeString());
			$sheet->getCell('A2')->getStyle()->applyFromArray(['font' => ['size' => 16]]);
//			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//			header('Content-Type: application/vnd.ms-excel');
//			header('Content-Disposition: attachment;filename="'.'test.xls'.'"');
//			header('Cache-Control: max-age=0');
//			$objWriter->save('php://output');

			//http://stackoverflow.com/questions/8566196/phpexcel-to-download
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="Music Ratings - '.Carbon::now()->toDateTimeString().'.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save("php://output");

//			return response()->json(array('code' => 0, 'songs' => print_r($objPHPExcel, true)));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

		}
	}

	public function getSongs() {
		try {
			$startDate = Request::input('startDate', 0);
			$endDate = Request::input('endDate', 0);
			$search = Request::input('search', '');

			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();

			$now = Carbon::now($timezone);

			if(!empty($startDate) && !empty($endDate)) {
				$startDate = Carbon::createFromFormat('Y-m-d', $startDate);
				$endDate = Carbon::createFromFormat('Y-m-d', $endDate);
			} else {
				$startDate = $now->copy()->subWeeks(2);
				$endDate = $now;
			}

			$songs = \DB::table('airshr_tags')
				->select(\DB::raw('count(airshr_tags.id) as play_count, airshr_coverarts.artist as artist, airshr_coverarts.track as title, airshr_coverarts.id as coverart_id, airshr_coverarts.coverart_url, airshr_coverarts.preview'))
				->where('airshr_tags.tag_timestamp', '>', $startDate->timestamp * 1000)
				->where('airshr_tags.tag_timestamp', '<=', $endDate->timestamp * 1000)
				->where('airshr_tags.content_type_id', '=', 2)
				->where('airshr_tags.coverart_id', '<>', 0)
				->where('airshr_tags.event_count', '>', 0)
//				->where('airshr_events.rate_option', '<>', 'no_rate')
                ->where('airshr_tags.station_id', '=', $station->id)
				->where('airshr_coverarts.itunes_available', '=', 1)
				->join('airshr_coverarts', 'airshr_coverarts.id', '=', 'airshr_tags.coverart_id')
//				->join('airshr_events', 'airshr_tags.id', '=', 'airshr_events.tag_id')
				->orderBy('play_count', 'DESC')
				->groupBy(\DB::raw('artist, title'))
				->take(50);

			if(!empty($search)) {
				$searchParts = [];
				if(strpos($search, '-') > 0) {
					$searchParts = explode('-', $search);
					for($i = 0; $i < count($searchParts); $i++) {
						$searchParts[$i] = trim($searchParts[$i]);
					}
				}

				$songs = $songs->where(function($q) use($search, $searchParts) {
					if(!empty($searchParts)) {
						$q->where(function($q2) use($searchParts) {
							$q2->where('artist', 'LIKE', "%{$searchParts[0]}%")
								->where('track', 'LIKE', "%{$searchParts[1]}%");
						});
						$q->orWhere(function($q2) use($searchParts) {
							$q2->where('artist', 'LIKE', "%{$searchParts[1]}%")
								->where('track', 'LIKE', "%{$searchParts[0]}%");
						});
					} else {
						$q->where('artist', 'LIKE', "%{$search}%")
							->orWhere('track', 'LIKE', "%{$search}%");
					}
				});
			}

			$songs = $songs->get();

            $lastUpdated = PHP_INT_MAX;

            $needToUpdate = false;

			for($i = 0; $i < count($songs); $i++) {

				$time = microtime(true);
				//Retrieve from music ratings cache/database if it already exists
				$musicRating = MusicRating::where('artist', '=', $songs[$i]->artist)
					->where('title', '=', $songs[$i]->title)
					->where('station_id', '=', $station->id)
					->first();

				if($musicRating) {
					$musicRatingId = $musicRating->id;

					$musicRatingData = json_decode($musicRating->data, true);

					$weeklyData = $musicRatingData['weeklyData'];
					$aggregateData = $musicRatingData['aggregateData'];

//					$date = Carbon::createFromFormat('Y-m-d', $weeklyData[count($weeklyData) - 1]['date'], $timezone)->addWeeks('12s');

					$watch = $musicRating->watch;

					$songs[$i]->musicRatingId = $musicRatingId;
					$songs[$i]->data = $weeklyData;
					$songs[$i]->aggregateData = $aggregateData;
					$songs[$i]->watch = $watch;

                    //Check the earliest updated music rating
                    if(isset($musicRating->last_updated)) {
                        if($musicRating->last_updated < $lastUpdated) {
                            $lastUpdated = $musicRating->last_updated;
                        }
                    }
                    else if($musicRating->updated_at->timestamp < $lastUpdated) {
                        $lastUpdated = $musicRating->updated_at->timestamp;
                    }
				}

				if($lastUpdated < $now->copy()->startOfDay()->timestamp) {
				    $needToUpdate = true;
                }
				\Log::info('Music Rating + ' . $songs[$i]->title);
				\Log::info(microtime(true) - $time);
			}

			return response()->json(array('code' => 0, 'songs' => $songs, 'lastUpdated' => $lastUpdated, 'needToUpdate' => $needToUpdate));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

		}
	}
	
	public function watchMusicRating($id) {
		try  {
			$musicRating = MusicRating::find($id);
			
			$musicRating->update(['watch' => $musicRating->watch == 1 ? 0 : 1]);
			
			return response()->json(array('code' => 0, 'watch' => $musicRating->watch));
		}
		catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

		}
	}

	public function getSongStatistics() {
		try {

			$totalTime = microtime(true);

			$artist = Request::input('artist');
			$title = Request::input('title');

			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();

			//Date related stuff
			$now = Carbon::now($timezone);
			$numberOfWeeks = 8;
			$date = $now->copy()->subWeeks($numberOfWeeks)->startOfWeek();
			$dateInMilli = $date->timestamp * 1000;

			//Result data initialisation
			$weeklyData = [];

			$aggregateData = [
				'loves' => 0,
				'likes' => 0,
				'hates' => 0,
				'airshrs' => 0,
				'week' => $date->format('Y-m-d'),
				'playCount' => 0,
			];

			//Music rating specific data
			$musicRatingId = 0;
			$watch = false;

			//Retrieve from music ratings cache/database if it already exists
			$musicRating = MusicRating::where('artist', '=', $artist)
				->where('title', '=', $title)
				->where('station_id', '=', $station->id)
				->first();

			if($musicRating) {
				$musicRatingId = $musicRating->id;

//				$musicRatingData = json_decode($musicRating->data, true);
//
//				$weeklyData = $musicRatingData['weeklyData'];
//				$aggregateData = $musicRatingData['aggregateData'];
//
//				$date = Carbon::createFromFormat('Y-m-d', $weeklyData[count($weeklyData) - 1]['date'], $timezone)->addWeeks('12s');

				$watch = $musicRating->watch;
			}
			
			//Query the database for music ratings data
			$events = \DB::table('airshr_events')
				->where('airshr_tags.tag_timestamp', '>', $dateInMilli)
				->where('airshr_events.record_timestamp', '>', $date->timestamp)
				->where('airshr_tags.who', '=', $artist)
				->whereIn('airshr_tags.what', [$title,  str_replace( "'", '’', $title)])
//				->where(\DB::raw("date(airshr_events.created_at) > '2016-06-03'"))
                ->where('airshr_tags.station_id', '=', $station->id)
				->join('airshr_tags', 'airshr_events.tag_id', '=', 'airshr_tags.id')
				->orderBy('airshr_tags.tag_timestamp')
				->get();

			//Get number of tags (plays) grouped by week
			$tagCounts = \DB::table('airshr_tags')
				->select(\DB::raw("COUNT(*) as count, FLOOR((airshr_tags.tag_timestamp - {$dateInMilli})/(1000*60*60*24*7)) as week")) //604800000 milliseconds in a week. Dividing gets the week number since start date.
				->where('airshr_tags.who', '=', $artist)
                ->where('airshr_tags.station_id', '=', $station->id)
				->whereIn('airshr_tags.what', [$title,  str_replace( "'", '’', $title)])
				->groupBy("week")
				->orderBy('week')
				->get();
			$tagCountsLast7Days = \DB::table('airshr_tags')
				->select(\DB::raw("COUNT(*) as count")) //604800000 milliseconds in a week. Dividing gets the week number since start date.
				->where('airshr_tags.who', '=', $artist)
                ->where('airshr_tags.station_id', '=', $station->id)
				->whereIn('airshr_tags.what', [$title,  str_replace( "'", '’', $title)])
				->where('tag_timestamp', '>', $now->copy()->subDays(7)->timestamp * 1000)
				->get();

			$weekIndex = 0;
			$isLastWeek = false;

			//Go through each week and calculate ratings
			while($date->lt($now)) {

				$startDate = $date->copy()->subWeek();
				$endDate = $date->copy()->addWeek()->startOfWeek();

				if($endDate->gte($now)) {
					$startDate = $now->copy()->subWeeks(2);
					$endDate = $now;
					$isLastWeek = true;
				}


				$currWeekData = [
					'loves' => 0,
					'likes' => 0,
					'hates' => 0,
					'rates' => 0,
					'lovePercent' => 0,
					'likePercent' => 0,
					'hatePercent' => 0,
					'posAcc' => 0,
					'score' => 0,
					'airshrs' => 0,
					'startDate' => $startDate->format('Y-m-d'),
					'endDate' => $endDate->format('Y-m-d'),
					'date' => $date->format('Y-m-d'),
					'playCount' => 0
				];

				$rateOptions = ['love', 'like', 'hate'];

				foreach ($events as $event) {

					if($event->tag_timestamp > $startDate->timestamp * 1000 && $event->tag_timestamp < $endDate->timestamp * 1000) {

						$option = $event->rate_option;
						if(in_array($event->rate_option, $rateOptions)) {
							$currWeekData[$option.'s']++;
							$currWeekData['rates']++;
							$aggregateData[$option.'s']++;
						}
						$currWeekData['airshrs']++;
						$aggregateData['airshrs']++;

					}

				}

				if($currWeekData['rates'] > 0) {

				    //Calculate the percentages of loves, likes and over its
                    //http://stackoverflow.com/questions/13483430/how-to-make-rounded-percentages-add-up-to-100

					$lovePercent = $currWeekData['loves'] / $currWeekData['rates'] * 100;
					$likePercent = $currWeekData['likes'] / $currWeekData['rates'] * 100;
					$hatePercent = $currWeekData['hates'] / $currWeekData['rates'] * 100;

                    $lovePercentRounded = floor($lovePercent);
                    $likePercentRounded = floor($likePercent);
                    $hatePercentRounded = floor($hatePercent);

                    $remainder = 100 - ($lovePercentRounded + $likePercentRounded + $hatePercentRounded);

                    $loveFractional = $lovePercent - floor($lovePercent);
                    $likeFractional = $likePercent - floor($likePercent);
                    $hateFractional = $hatePercent - floor($hatePercent);

                    $fractionals = [$loveFractional, $likeFractional, $hateFractional];
                    rsort($fractionals);

                    $i = 0;

                    while($remainder > 0) {
                        if($fractionals[$i] == $loveFractional) $lovePercentRounded += 1;
                        else if($fractionals[$i] == $likeFractional) $likePercentRounded += 1;
                        else if($fractionals[$i++] == $hateFractional) $hatePercentRounded += 1;

                        $remainder--;
                    }
                    $currWeekData['lovePercent'] = $lovePercentRounded;
                    $currWeekData['likePercent'] = $likePercentRounded;
                    $currWeekData['hatePercent'] = $hatePercentRounded;

					$currWeekData['posAcc'] = $currWeekData['lovePercent'] + $currWeekData['likePercent'];
					if($isLastWeek) $currWeekData['score'] = ($currWeekData['lovePercent'] * 10 + $currWeekData['likePercent'] * 5 + $currWeekData['hatePercent'] *  0) * sqrt(1 + $currWeekData['airshrs']);
				}

				foreach($tagCounts as $tagCount) {
					//Since tag counts are sorted by week, the first tag would be the earliest week
					//So we can use that to calculate the total number of weeks a song has played
					if(!isset($aggregateData['weeksPlayed'])) {
						$aggregateData['weeksPlayed'] = abs($tagCount->week - $numberOfWeeks);
					}

					//If the week is equal to the current week of data we want to store the tag count which
					//is the number of plays for that week. If it's the last week, then we also want to
					//include data from the week before so that it's the past 14 days.
					if($tagCount->week == $weekIndex || $tagCount->week - 1 == $weekIndex || $tagCount->week + 1 == $weekIndex) {
						$currWeekData['playCount'] = $tagCount->count;
						$aggregateData['playCount'] += $tagCount->count;
						break;
					}
				}

				if($isLastWeek) {
					$currWeekData['playCount'] = $tagCountsLast7Days[0]->count;
				}

				$weeklyData[$weekIndex++] = $currWeekData;

				$date->addWeek();

				if($isLastWeek) break;
			}


//			\Log::info('loop: ');
//			\Log::info(microtime(true) - $time);

			$data['weeklyData'] = $weeklyData;
			$data['aggregateData'] = $aggregateData;

			$musicRating = MusicRating::updateOrCreate(['id' => $musicRatingId],[
				'artist' => $artist,
				'title' => $title,
				'data' => json_encode($data),
				'watch' => $watch,
				'station_id' => $station->id,
                'last_updated' => time()
			]);

            \Log::info(time());

			\Log::info('Total: ');
			\Log::info(microtime(true) - $totalTime);

			return response()->json(['code' => 0, 'title' => str_replace( "'", '’', $title), 'data' => $weeklyData, 'aggregateData' => $aggregateData, 'musicRatingId' => $musicRating->id, 'watch' => $watch]);


		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));

		}
	}
	
	public function musicRatingStatistics() {
		try {
			
			$coverartRating = json_decode(Request::input('coverartRating'));
			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();

			$startDate = Carbon::createFromFormat('Y-m-d', $coverartRating->start_date, $timezone)->startOfDay();
			$endDate = Carbon::createFromFormat('Y-m-d', $coverartRating->end_date, $timezone)->startOfDay();

			$date = $startDate->copy();

			$weekCount = 0;
			$loves = [];
			$likes= [];
			$hates= [];
			$noRatings = [];
			$labels = [];

			$tagCount = 0;
			do {
				$labels[$weekCount] = $date->format('Y-m-d');

				$tags = \DB::table('airshr_tags')
					->where('coverart_id', '=', $coverartRating->coverart_id)
					->where('tag_timestamp', '>=', $date->timestamp * 1000)
					->where('tag_timestamp', '<=', $date->endOfWeek()->timestamp * 1000)
					->where('station_id' , '=', $station->id)
					->get();

				$tagIds = [];
				foreach($tags as $tag) {
					$tagIds[] = $tag->id;
					$tagCount++;
				}

				$events = \DB::table('airshr_events')
					->whereIn('tag_id', $tagIds)
					->get();

				$loves[$weekCount] = 0;
				$likes[$weekCount] = 0;
				$hates[$weekCount] = 0;
				$noRatings[$weekCount] = 0;
				
				foreach($events as $event) {
					switch($event->rate_option) {
						case 'love' :
							$loves[$weekCount]++;
							break;
						case 'like' :
							$likes[$weekCount]++;
							break;
						case 'hate' :
							$hates[$weekCount]++;
							break;
						default:
							$noRatings[$weekCount]++;
					}
				}

				$date->addWeek()->startOfWeek();
				$weekCount++;

			} while($date->lte($endDate) && $date->lte(Carbon::now($timezone)));


			return response()->json(array('code' => 0, 'weeks' => $labels, 'loves' => $loves, 'likes' => $likes, 'hates' => $hates, 'noRatings' => $noRatings, 'playCount' => $tagCount));
			
		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function emailMusicRating() {
		try {

			$html = Request::input('content');
			$emailRaw = Request::input('email');
			$song = Request::input('song');
			$dateRange = Request::input('dateRange');
			$sendHTML = Request::input('sendHTML');
			$image = Request::input('image');

			if(empty($image)) {
				$cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();

				$css = file_get_contents(\Config::get('app.url') . '/dashboard-assets/base/assets/css/site.css');
				$css .= file_get_contents(\Config::get('app.url') . '/dashboard-assets/global/css/bootstrap.min.css');
				$css .= file_get_contents(\Config::get('app.url') . '/dashboard-assets/global/css/bootstrap-extend.min.css');
				$css .= file_get_contents(\Config::get('app.url') . '/dashboard-assets/global/vendor/chartist-js/chartist.css');
				$css .= file_get_contents(\Config::get('app.url') . '/dashboard-assets/global/fonts/web-icons/web-icons.min.css');
				$css .= file_get_contents(\Config::get('app.url') . '/css/dashboard.css');
				$css .= file_get_contents(\Config::get('app.url') . '/css/musicratingdashboard.css');
				$css .= ' .col-sm-10 { width: 83.33333%; float: left; } .col-sm-2 { width: 16.66666%; float: left; } .btn { display: none; } .musicrating-top-content { height: 300px; } ';

				$finalHtml = $cssToInlineStyles->convert(
					$html,
					$css
				);

				return response()->json(['code' => 0, 'html' => $finalHtml]);
			}

			$imageData = base64_decode($image);

//			$im = imagecreatefromstring($data);
//			if ($im !== false) {
//				header('Content-Type: image/png');
//				imagepng($im, 'test.png');
//				imagedestroy($im);
//			}
			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();

			$lastUpdated = Carbon::now($timezone)->toDayDateTimeString();

			$emails = preg_split( "/(;|,)/", $emailRaw );

			foreach($emails as $email) {
				$data = [
					'image' => $imageData,
					'email' => trim($email),
					'song' => $song,
					'dateRange' => $dateRange,
					'lastUpdated' => $lastUpdated
				];

				\Mail::send('emails.musicrating', $data, function ($message) use ($data) {
					$message->from('connect@airshr.net', 'AirShr Connect')
						->to($data['email'])
						->subject("Music Rating for {$data['song']} between {$data['dateRange']}");
				});
			}

			return response()->json(array('code' => 0, 'msg' => 'Email Sent'));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}


	public function emailPopularTags() {
		try {
			$station = \Auth::User()->station;

			$date = Request::input('date');
			$hour =  Request::input('hour');
			$content_type_id =  Request::input('content_type_id');
			$email = Request::input('email');

			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return response()->json(array('code' => -2, 'msg' => 'Invalid Email'));
			}

			$timezone = $station->getStationTimezone();
			$date = Carbon::createFromFormat('Y-m-d H', ($date . ' ' . $hour), $timezone);

			$result_tags = \DB::table('airshr_tags')
				->whereBetween('tag_timestamp', [$date->timestamp * 1000, $date->copy()->addHour()->timestamp * 1000])
				->where('content_type_id', '=', $content_type_id)
				->where('station_id', '=', $station->id)
				->where('event_count', '>', 0)
				->where('is_competition', '=', 0)
				->orderBy('event_count', 'desc')
				->take(8)
				->get();

			$content_type_color = ContentType::getContentTypeColor($content_type_id);
			$content_type_name = ContentType::getContentTypeText($content_type_id);

			$data = array(
				'tags' => $result_tags,
				'date' => $date->format('l jS \\of F Y \\@ h:ia') . ' - ' . $date->copy()->addHour()->format('h:ia'),
				'content_type_color' => $content_type_color,
				'content_type_name' => $content_type_name,
				'timezone' => $timezone,
				'email' => $email
			);

			\Mail::send('emails.populartags', $data, function ($message) use ($data) {
				$message->from('connect@airshr.net', 'AirShr Connect')
					->to($data['email'])
					->subject("Popular {$data['content_type_name']} at {$data['date']}");
			});

//			return view('emails.populartags')
//				->with('tags', $result_tags)
//				->with('date', $date->format('l jS \\of F Y \\@ h:ia') . ' - ' . $date->copy()->addHour()->format('h:ia'))
//				->with('content_type_color', $content_type_color)
//				->with('content_type_name', $content_type_name)
//				->with('timezone', $timezone);
			return response()->json(array('code' => 0, 'msg' => 'Successfully sent e-mail!'));
		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function getCompetitionTags($start, $end) {
		$competition_tag_ids = \DB::table('airshr_tags')
			->select('id')
			->where('station_id', '=', \Auth::User()->station->id)
			->where('airshr_tags.tag_timestamp', '>=', $start->timestamp*1000)
			->where('airshr_tags.tag_timestamp', '<=', $end->timestamp*1000)
			->where('is_competition', '=', '1')
			->get();

		$tag_ids = [];
		foreach($competition_tag_ids as $competition_tag_id) {
			$tag_ids[] = $competition_tag_id->id;
		}

		return $tag_ids;
	}

	public function getVoteTags($start, $end) {

		$vote_tag_ids = \DB::table('airshr_tags')
			->select('id')
			->where('station_id', '=', \Auth::User()->station->id)
			->where('airshr_tags.tag_timestamp', '>=', $start->timestamp*1000)
			->where('airshr_tags.tag_timestamp', '<=', $end->timestamp*1000)
			->where('vote_generation_timestamp', '>', '0')
			->get();

		$tag_ids = [];
		foreach($vote_tag_ids as $vote_tag_id) {
			$tag_ids[] = $vote_tag_id->id;
		}

		return $tag_ids;
	}

	public function getPopularTags($date, $hour, $content_type_id) {
		try {
			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();
			$date = Carbon::createFromFormat('Y-m-d H', ($date. ' '. $hour), $timezone);

			$result_tags = \DB::table('airshr_tags')
				->whereBetween('tag_timestamp', [$date->timestamp * 1000, $date->copy()->addHour()->timestamp * 1000])
				->where('content_type_id', '=', $content_type_id)
				->where('station_id', '=', $station->id)
				->where('event_count', '>', 0)
				->where('is_competition', '=', 0)
				->orderBy('event_count', 'desc')
				->take(8)
				->get();

			return response()->json(array('code' => 0, 'results' => $result_tags));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Load Preview Log Tag List
	 */
	public function getDailyLogCounts() {

		$previewLogDate = Request::input('dailylog_date');

		if (empty($previewLogDate)) $previewLogDate = date("Y-m-d");
		else $previewLogDate = date("Y-m-d", strtotime($previewLogDate));

		try {

			$previewTags = PreviewTag::where('station_id', '=', \Auth::User()->station->id)
				->where('preview_date', '=', $previewLogDate)
				->get();

			$adID = ContentType::GetAdContentTypeID();
			$promoID = ContentType::GetPromoContentTypeID();
			$talkID = ContentType::GetTalkContentTypeID();
			$newsID = ContentType::GetNewsContentTypeID();
			$musicID = ContentType::GetMusicContentTypeID();
			$otherID = 100;
			$overallID = 0;


			$byCategory = array(
				$adID => array('complete' => 0, 'incomplete' => 0),
				$promoID => array('complete' => 0, 'incomplete' => 0),
				$talkID => array('complete' => 0, 'incomplete' => 0),
				$newsID => array('complete' => 0, 'incomplete' => 0),
				$musicID => array('complete' => 0, 'incomplete' => 0),
				$otherID => array('complete' => 0, 'incomplete' => 0),
				$overallID => array('complete' => 0, 'incomplete' => 0),
			);

			foreach ($previewTags as $previewTag) {
				
				$contentTypeID = $previewTag->content_type_id;

				if ($contentTypeID == $adID || $contentTypeID == $promoID || $contentTypeID == $talkID || $contentTypeID == $newsID || $contentTypeID == $musicID) {

				} else {
					$contentTypeID = $otherID;
				}

				if ($previewTag->hasEnoughConnectData()) {
					$byCategory[$contentTypeID]['complete']++;
					$byCategory[0]['complete']++;

				} else { //Missing some content data
					$byCategory[$contentTypeID]['incomplete']++;
					$byCategory[0]['incomplete']++;
				}

			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('statistics' => $byCategory)));


		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	
	public function getMomentsTodayAndMonth() {
		try {
			$station = \Auth::User()->station;

			$timezone = $station->getStationTimezone();
			$now = Carbon::now();
			$now->timezone = $timezone;

			$start = $now->copy()->startOfDay();
			$end = $now->copy()->endOfDay();

			$moments_today_count = \App\Event::where('station_id', '=', \Auth::User()->station->id)
//				->where('event_data_status', '=', 1)
				->where('record_timestamp', '>=', $start->timestamp)
				->where('record_timestamp', '<=', $end->timestamp)
				->count();
			
			$start = $now->copy()->startOfMonth();
			$end = $now;
			
			$moments_month_count = \App\Event::where('station_id', '=', \Auth::User()->station->id)
//				->where('event_data_status', '=', 1)
				->where('record_timestamp', '>=', $start->timestamp)
				->where('record_timestamp', '<=', $end->timestamp)
				->count();

			return response()->json(array('code' => 0, 'moments_today' => $moments_today_count, 'moments_month' => $moments_month_count));
			
		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
		
	}

	public function getNumberOfUsers() {
		try {
//			select date(created_at), count(*)
//			from airshr_users
//			where date(created_at) >= '2016-03-11'
//			group by 1

			$total_users = User::whereRaw('DATE(created_at) >= \'2016-03-11\'')
				->count();
			$yesterday = Carbon::now()->subDay()->format('Y-m-d');
			$total_users_up_to_yesterday = User::whereRaw('DATE(created_at) >= \'2016-03-11\' AND DATE(created_at) <= \''.$yesterday.'\'')
				->count();

			return response()->json(array('code' => 0, 'total_users' => $total_users, 'users_yesterday' => $total_users_up_to_yesterday));
		}
		catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function getMoments($input_date = 0) {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			if($input_date == 0) {
				$date = Carbon::now($timezone)->startOfDay();
			} else {
				$date = Carbon::createFromFormat('Y-m-d', $input_date, $timezone);
			}

			$day_start = $date->copy()->startOfDay();
			$day_end = $date->copy()->endOfDay();

			$moments = \DB::table('airshr_events')
				->select(\DB::raw('COUNT(*) as count'))
				->where('station_id', '=', \Auth::User()->station->id)
//				->where('event_data_status', '=', 1)
				->where('record_timestamp', '>=', $day_start->timestamp)
				->where('record_timestamp', '<=', $day_end->timestamp)
				->groupBy('hour')
				->get();

			return response()->json(array('code' => 0, 'moments' => $moments));
		}
		catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function getContentTypePercentages($input_start_date = 0, $input_end_date = 0) {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			if($input_start_date == 0) {
				$start = Carbon::createFromTimestamp(0);
				$end = Carbon::now();
			} else {
				$date = Carbon::createFromFormat('Y-m-d', $input_start_date, $timezone);
				$start = $date->copy()->startOfDay();
				$date = Carbon::createFromFormat('Y-m-d', $input_end_date, $timezone);
				$end = $date->copy()->endOfDay();
			}

			$competition_tag_ids = $this->getCompetitionTags($start, $end);
			$vote_tag_ids = $this->getVoteTags($start, $end);

			$events = \DB::table('airshr_events')
				->select(\DB::raw('COUNT(*) as count, airshr_events.content_type_id'))
//				->join('airshr_tags', 'airshr_events.tag_id', '=', 'airshr_tags.id')
//				->where('airshr_tags.competition_result_generated', '=', '0')
				->where('airshr_events.station_id', '=', \Auth::User()->station->id)
				->whereNotIn('airshr_events.tag_id', $competition_tag_ids)
				->whereNotIn('airshr_events.tag_id', $vote_tag_ids)
				->where('airshr_events.record_timestamp', '>=', $start->timestamp)
				->where('airshr_events.record_timestamp', '<=', $end->timestamp)
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) > 6')
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) < 22')
				->whereRaw("MOD(airshr_events.record_timestamp + {$offset}, 86400) >= 6*60*60") //"MOD(record_timestamp + {$offset}, 86400)" gets seconds since midnight
				->whereRaw("MOD(airshr_events.record_timestamp + {$offset}, 86400) < 22*60*60") // We then compare the hours from midnight to check if it is between the hours we want
				->groupBy('airshr_events.content_type_id')
				->get();

			$moments = 0;
			$music = $talk = $ad = $news = $traffic = $promo = 0;
			foreach($events as $event) {
				$moments += $event->count;
				switch($event->content_type_id) {
					case ContentType::GetMusicContentTypeID():
						$music = $event->count;
						break;
					case ContentType::GetNewsContentTypeID():
						$news = $event->count;
						break;
					case ContentType::GetAdContentTypeID():
						$ad = $event->count;
						break;
					case ContentType::GetTalkContentTypeID():
						$talk = $event->count;
						break;
					case ContentType::GetTrafficContentTypeID():
						$traffic = $event->count;
						break;
					case ContentType::GetPromoContentTypeID():
						$promo = $event->count;
						break;
				}
			}

			return response()->json(array('code' => 0, 'data' => array(
				'music' => $music,
				'news' => $news,
				'ad' => $ad,
				'talk' => $talk,
				'traffic' => $traffic,
				'promo' => $promo,
				'music_color' => ContentType::$CONTENT_TYPE_COLORS[ContentType::GetMusicContentTypeID()],
				'news_color' => ContentType::$CONTENT_TYPE_COLORS[ContentType::GetNewsContentTypeID()],
				'ad_color' => ContentType::$CONTENT_TYPE_COLORS[ContentType::GetAdContentTypeID()],
				'talk_color' => ContentType::$CONTENT_TYPE_COLORS[ContentType::GetTalkContentTypeID()],
				'traffic_color' => '#3583ca',
				'promo_color' => '#000000',
				'moments'=> $moments)));
		}
		catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	
	/**
	 * Get saved moments by month
	 */
	public function getMomentsByMonth() {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			$date = Carbon::now($timezone);

			$year_start = $date->copy()->startOfMonth()->subMonths(12);

			//If station is Nova we want to start at March 11 since this is when we launched with them
			if(\Auth::User()->station->id == 8 && $year_start->lt(Carbon::parse('2016-03-11'))) {
				$year_start = Carbon::parse('2016-03-11');
			}
			$year_end = $date;

			$moments = \DB::table('airshr_events')
				->select(\DB::raw('MONTH(FROM_UNIXTIME(record_timestamp)) AS month, 
				YEAR(FROM_UNIXTIME(record_timestamp)) AS year,
				WEEK(FROM_UNIXTIME(record_timestamp)) as week, 
				COUNT(*) as count'))
				->where('station_id', '=', \Auth::User()->station->id)
//				->where('event_data_status', '=', 1)
				->where('record_timestamp', '>=', $year_start->timestamp)
				->where('record_timestamp', '<=', $year_end->timestamp)
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) >= 6')
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) < 22')
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) >= 6*60*60") //"MOD(record_timestamp + {$offset}, 86400)" gets seconds since midnight
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) < 22*60*60") // We then compare the hours from midnight to check if it is between the hours we want
				->groupBy('week')
				->orderBy('year')
				->orderBy('month')
				->orderBy('week')
				->get();

			$total_moments = 0;
			$moments_this_month = 0;

			foreach($moments as $moment) {
				$total_moments += $moment->count;
				if($moment->month == $date->month && $moment->year == $date->year) {
					$moments_this_month += $moment->count;
				}
			}

			return response()->json(array('code' => 0,
				'moments' =>  $moments,
				'moments_this_month' => $moments_this_month,
				'total_moments' => $total_moments
				));
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Get downloads
	 */
	public function getDownloads() {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			$date = Carbon::now($timezone);

			$data = \DB::table('airshr_users')
				->select(\DB::raw('DATE(created_at) AS day, COUNT(*) as count'))
				->get();
			$today = \DB::table('airshr_users')
				->select(\DB::raw('DATE(created_at) AS day, COUNT(*) as count'))
				->whereRaw('DATE(created_at) = '. $date->toDateString())
				->get();
			$yesterday = \DB::table('airshr_users')
				->select(\DB::raw('DATE(created_at) AS day, COUNT(*) as count'))
				->whereRaw('date(created_at) = '. $date->subDay()->toDateString())
				->get();

			return response()->json(array('code' => 0, 'data' =>  $data, 'today' => $today, 'yesterday' => $yesterday));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Get saved competition and vote moments by date
	 */
	public function getCompetitionAndVoteMomentsByDate($start_date = 0, $end_date = 0) {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			if($start_date == 0) {
				$start = Carbon::now($timezone)->startOfDay();
			} else {
				$start = Carbon::createFromFormat('Y-m-d', $start_date, $timezone)->startOfDay();
			}

			if($end_date == 0) {
				$end = Carbon::now($timezone)->endOfDay();
			} else {
				$end = Carbon::createFromFormat('Y-m-d', $end_date, $timezone)->endOfDay();
			}

			$competition_tag_ids = $this->getCompetitionTags($start, $end);

			$vote_tag_ids = $this->getVoteTags($start, $end);

			$competition_data = \DB::table('airshr_events')
				->select(\DB::raw("FLOOR(MOD(airshr_events.record_timestamp + {$offset}, 86400)/3600) AS hour,
			COUNT(*) as count"))
				->whereIn('airshr_events.tag_id', $competition_tag_ids)
				->where('airshr_events.station_id', '=', \Auth::User()->station->id)
				->where('airshr_events.record_timestamp', '>=', $start->timestamp)
				->where('airshr_events.record_timestamp', '<=', $end->timestamp)
				->groupBy('hour')
				->get();

			$vote_data = \DB::table('airshr_events')
				->select(\DB::raw("FLOOR(MOD(airshr_events.record_timestamp + {$offset}, 86400)/3600) AS hour,
			COUNT(*) as count"))
				->whereIn('airshr_events.tag_id', $vote_tag_ids)
				->where('airshr_events.station_id', '=', \Auth::User()->station->id)
				->where('airshr_events.record_timestamp', '>=', $start->timestamp)
				->where('airshr_events.record_timestamp', '<=', $end->timestamp)
				->groupBy('hour')
				->get();

			$vote_raw = [];
			foreach ($vote_data as $data_point) {
				$vote_raw[$data_point->hour] = $data_point->count;
			}

			$vote = [];

			$i = 0;
			for ($hour = 6; $hour <= 22; $hour++) {
				if (array_key_exists($hour, $vote_raw)) {
					$vote[$i] = $vote_raw[$hour];
				} else {
					$vote[$i] = 0;
				}
				$i++;
			}

			$competition_raw = [];
			foreach ($competition_data as $data_point) {
				$competition_raw[$data_point->hour] = $data_point->count;
			}

			$competition = [];

			$i = 0;
			for ($hour = 6; $hour <= 22; $hour++) {
				if (array_key_exists($hour, $competition_raw)) {
					$competition[$i] = $competition_raw[$hour];
				} else {
					$competition[$i] = 0;
				}
				$i++;
			}
			
			return response()->json(array('code' => 0, 'vote' => $vote, 'competition' => $competition ));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Get saved moments by date
	 */
	public function getMomentsByDate($start_date = 0, $end_date = 0) {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();

			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			if($start_date == 0) {
				$start = Carbon::now($timezone)->startOfDay();
			} else {
				$start = Carbon::createFromFormat('Y-m-d', $start_date, $timezone)->startOfDay();
			}

			if($end_date == 0) {
				$end = Carbon::now($timezone)->endOfDay();
			} else {
				$end = Carbon::createFromFormat('Y-m-d', $end_date, $timezone)->endOfDay();
			}

			$competition_tag_ids = $this->getCompetitionTags($start, $end);
			$vote_tag_ids = $this->getVoteTags($start, $end);

			$data = \DB::table('airshr_events')
				->select(\DB::raw("FLOOR(MOD(airshr_events.record_timestamp + {$offset}, 86400)/3600) AS hour,
			COUNT(*) as count, airshr_events.content_type_id"))
				->whereNotIn('airshr_events.tag_id', array_merge($competition_tag_ids, $vote_tag_ids))
				->where('airshr_events.station_id', '=', \Auth::User()->station->id)
				->where('airshr_events.record_timestamp', '>=', $start->timestamp)
				->where('airshr_events.record_timestamp', '<=', $end->timestamp)
				->groupBy('airshr_events.content_type_id')
				->groupBy('hour')
				->orderBy('airshr_events.content_type_id')
				->get();

			$music_raw = [];
			$talk_raw = [];
			$ad_raw = [];
			$news_raw = [];
			$traffic_raw = [];
			$promo_raw = [];
			foreach($data as $data_point) {
				switch($data_point->content_type_id) {
					case ContentType::GetMusicContentTypeID() :
						$music_raw[$data_point->hour] = $data_point->count;
						break;
					case ContentType::GetTalkContentTypeID() :
						$talk_raw[$data_point->hour] = $data_point->count;
						break;
					case ContentType::GetAdContentTypeID() :
						$ad_raw[$data_point->hour] = $data_point->count;
						break;
					case ContentType::GetNewsContentTypeID() :
						$news_raw[$data_point->hour] = $data_point->count;
						break;
					case ContentType::GetTrafficContentTypeID() :
						$traffic_raw[$data_point->hour] = $data_point->count;
						break;
					case ContentType::GetPromoContentTypeID() :
						$promo_raw[$data_point->hour] = $data_point->count;
						break;
				}
			}

			$music = [];
			$talk = [];
			$ad = [];
			$news = [];
			$traffic = [];
			$promo = [];

			$i = 0;
			for($hour = 6; $hour <= 22; $hour++) {
				if(array_key_exists($hour, $music_raw)) {
					$music[$i] = $music_raw[$hour];
				} else {
					$music[$i] = 0;
				}
				if(array_key_exists($hour, $talk_raw)) {
					$talk[$i] = $talk_raw[$hour];
				} else {
					$talk[$i] = 0;
				}
				if(array_key_exists($hour, $ad_raw)) {
					$ad[$i] = $ad_raw[$hour];
				} else {
					$ad[$i] = 0;
				}
				if(array_key_exists($hour, $news_raw)) {
					$news[$i] = $news_raw[$hour];
				} else {
					$news[$i] = 0;
				}
				if(array_key_exists($hour, $traffic_raw)) {
					$traffic[$i] = $traffic_raw[$hour];
				} else {
					$traffic[$i] = 0;
				}
				if(array_key_exists($hour, $promo_raw)) {
					$promo[$i] = $promo_raw[$hour];
				} else {
					$promo[$i] = 0;
				}
				$i++;
			}

			return response()->json(array('code' => 0,
				'music' =>  $music,
				'talk' => $talk,
				'ad' => $ad,
				'news' => $news,
				'traffic' => $traffic,
				'promo' => $promo));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Get users by number of saved moments
	 */
	public function getUsersByClicks() {
		try {
			$station = \Auth::User()->station;
			$timezone = $station->getStationTimezone();
			$now = Carbon::now($timezone);
			$date = $now->copy()->subWeeks(11);

			//If station is Nova we want to start at March 11 since this is when we launched with them
//			if(\Auth::User()->station->id == 8 && $date->lt(Carbon::parse('2016-03-11'))) {
//				$date = Carbon::parse('2016-03-11');
//			}

			$start = $date->copy()->startOfWeek();
			$end = $start->copy()->endOfWeek();
			$single_clicks = array(0,0,0,0,0,0,0,0,0,0,0,0);
			$double_clicks = array(0,0,0,0,0,0,0,0,0,0,0,0);
			$more_clicks = array(0,0,0,0,0,0,0,0,0,0,0,0);
			$week_labels = array();

//			$time = microtime(true);
			for($i = 0; $i < 12; $i++) {
				$week_labels[] = $start->format('d M') . ' - ' . $end->format('d M');

				//Check if we stored in cache
				$cached = \DB::table('airshr_analytics')
					->where('type', '=', Analytics::LISTENER_ENGAGEMENT)
					->where('end_time', '>=', $end->timestamp)
					->where('start_time', '<=', $start->timestamp)
					->first();
				if($cached && $now->gt($end)) {
					$result = unserialize($cached->data);
					$single_clicks[$i] = $result['single_clicks'];
					$double_clicks[$i] = $result['double_clicks'];
					$more_clicks[$i] = $result['more_clicks'];
				}

				//If there isn't the result in cache, run the query
				else {
					$users = \DB::select(\DB::raw('SELECT COUNT(*) as count FROM airshr_events
										WHERE record_timestamp >= :start
										AND record_timestamp <= :end
										AND station_id = :station
										GROUP BY record_device_id'),
						array('start' => $start->timestamp,
							'end' => $end->timestamp,
							'station' => $station->id));

					foreach ($users as $user) {
						if ($user->count == 1) {
							$single_clicks[$i]++;
						} else if ($user->count == 2) {
							$double_clicks[$i]++;
						} else if ($user->count >= 3) {
							$more_clicks[$i]++;
						}
					}

					if($now->gt($end)) {
						Analytics::create(['type' => Analytics::LISTENER_ENGAGEMENT,
							'start_time' => $start->timestamp,
							'end_time' => $end->timestamp,
							'data' => serialize(array('single_clicks' => $single_clicks[$i],
								'double_clicks' => $double_clicks[$i],
								'more_clicks' => $more_clicks[$i]))
						]);
					}
				}

				$start->addWeek();
				$end = $start->copy()->endOfWeek();
			}
//			$elapsed = microtime(true) - $time;
//			\Log::info('Time: '. $elapsed);


			$result = array();

			return response()->json(array('code' => 0,
				'single_clicks' => $single_clicks,
				'double_clicks' => $double_clicks,
				'more_clicks' => $more_clicks,
				'labels' => $week_labels));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function getStreamTime() {
		try {
			$timezone = \Auth::User()->station->getStationTimezone();
			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			$data = \DB::table('airshr_streaming_status')
				->select(\DB::raw("FLOOR(MOD((airshr_streaming_status.status_timestamp/1000) + {$offset}, 86400)/3600) AS hour, COUNT(*) as count"))
				->where('streaming_status', '=', 'start')
				->groupBy('hour')
				->orderBy('hour')
				->get();

			return response()->json(array('code' => 0, 'data' => $data));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}


	/**
	 * Get saved moments by date
	 */
	public function getEventLocationsByDate($start_date = 0, $end_date = 0, $start_time=0, $end_time=24) {
		try {
			$station = \Auth::User()->station;

			$region = $station->getStationFirstRegion();
			$lat = $region->center_lat;
			$lng = $region->center_lng;

			$timezone = \Auth::User()->station->getStationTimezone();
			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			$date = Carbon::createFromFormat('Y-m-d', $start_date, $timezone);

//			$start = $date->copy()->startOfDay();
//			$end = $date->copy()->endOfDay();

			$start = Carbon::createFromFormat('Y-m-d', $start_date, $timezone)->startOfDay();
			$end = Carbon::createFromFormat('Y-m-d', $end_date, $timezone)->endOfDay();

			$events = \DB::table('airshr_events')
				->select(\DB::raw('event_lat as lat, event_lng as lng, record_timestamp as timestamp'))
				->where('station_id', '=', \Auth::User()->station->id)
//				->where('event_data_status', '=', 1)
				->where('record_timestamp', '>=', $start->timestamp)
				->where('record_timestamp', '<', $end->timestamp)
				->where('event_lat', '!=', 0)
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) >= {$start_time}*60*60") //"MOD(record_timestamp + {$offset}, 86400)" gets seconds since midnight
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) < {$end_time}*60*60") // We then compare the hours from midnight to check if it is between the hours we want
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) >= '.$start_time)
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) < '.$end_time)
				->get();


			return response()->json(array('code' => 0, 'data' => $events, 'center_lat' => $lat, 'center_lng' => $lng, 'radius' => $region->radius));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function getSourceOfListeners() {
//		# determining the number of Nova 1069 events that were from Streaming Radio
//		# if terrestrial_delay = 0, then the event was from Terrestrial radio
//		# if terrestrial_delay > 0 then the event was from Streaming radio
//		Select count(*)
//		from airshr_events
//		where terrestrial_delay > '0' and station_id = '8' and time(created_at) > '06:00' and time(created_at) < '22:00'
		try {
			$station = \Auth::User()->station;
			$timezone = $station->getStationTimezone();
			$station_time = new \DateTime('now', new \DateTimeZone($timezone));
			$offset = $station_time->getOffset();

			$stream = \DB::table('airshr_events')
				->where('terrestrial_delay', '>', 0)
				->where('station_id', '=', $station->id)
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) >= 6*60*60") //"MOD(record_timestamp + {$offset}, 86400)" gets second since midnight
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) < 22*60*60")// We then compare the hours from midnight to check if it is between the hours we want
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) >= 6')
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) < 22')
				->count();

			$terrestrial = \DB::table('airshr_events')
				->where('terrestrial_delay', '=', 0)
				->where('station_id', '=', $station->id)
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) >= 6*60*60")
				->whereRaw("MOD(record_timestamp + {$offset}, 86400) < 22*60*60")
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) >= 6')
//				->whereRaw('HOUR(CONVERT_TZ(FROM_UNIXTIME(record_timestamp), @@session.time_zone, \''.$timezone.'\')) < 22')
				->count();

			$streaming_users = \DB::select(\DB::raw('select count(*) as count from (select user_id, date(created_at), time(created_at), count(*)
					from airshr_streaming_status
					group by 1
					order by 4 desc) as T'));
			
			return response()->json(array('code' => 0, 'stream' => $stream, 'terrestrial' => $terrestrial, 'streaming_users' => $streaming_users));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Create new talk show
	 */
	public function createTalkShow() {

		try {
			$what = Request::input('what');
			$who = Request::input('who');
			$user = \Auth::User();

			$oldTalkShowContent = ConnectContent::where('what', '=', $what)
				->where('who', '=', $who)
				->where('station_id', '=', $user->station->id)
				->first();

			$talkShowContent = null;
			
			//If the combination of talk show and talent exists, we clone that talk show, changing information such as dates as required.
			if($oldTalkShowContent && !empty($who) && !empty($what)) {
				$talkShowContent = $oldTalkShowContent->replicate();
				$talkShowContent->connect_user_id = $user->id;
				$talkShowContent->who = $who;
				$talkShowContent->what = $what;
				$talkShowContent->start_date = Request::input('start_date');
				$talkShowContent->end_date = Request::input('end_date');
				$talkShowContent->start_time = getMySQLTimeFormat(Request::input('start_time'));
				$talkShowContent->end_time = getMySQLTimeFormat(Request::input('end_time'));

				$weekdays = Request::input('weekdays');
				$count = count($weekdays);

				$talkShowContent->content_weekday_0 = 0;
				$talkShowContent->content_weekday_1 = 0;
				$talkShowContent->content_weekday_2 = 0;
				$talkShowContent->content_weekday_3 = 0;
				$talkShowContent->content_weekday_4 = 0;
				$talkShowContent->content_weekday_5 = 0;
				$talkShowContent->content_weekday_6 = 0;
				for ($i = 0; $i < $count; $i++) {
					if($weekdays[$i] == 0) {
						$talkShowContent->content_weekday_0 = 1;
					}if($weekdays[$i] == 1) {
						$talkShowContent->content_weekday_1 = 1;
					}if($weekdays[$i] == 2) {
						$talkShowContent->content_weekday_2 = 1;
					}if($weekdays[$i] == 3) {
						$talkShowContent->content_weekday_3 = 1;
					}if($weekdays[$i] == 4) {
						$talkShowContent->content_weekday_4 = 1;
					}if($weekdays[$i] == 5) {
						$talkShowContent->content_weekday_5 = 1;
					}if($weekdays[$i] == 6) {
						$talkShowContent->content_weekday_6 = 1;
					}
				}

				$talkShowContent->save();

				//Clone attachments
				if($oldTalkShowContent->attachments) {
					foreach($oldTalkShowContent->attachments as $attachment) {
						$new_attachment = ConnectContentAttachment::find($attachment->id)->replicate();
						$new_attachment->content_id = $talkShowContent->id;
						$new_attachment->save();
					}
				}

			}
			else {
				$data = array();
				$data['station_id'] = $user->station->id;
				$data['content_type_id'] = ContentType::findContentTypeIDByName('Talk');
				$data['content_subtype_id'] = ContentType::GetTalkSubContentTalkShowTypeID();
				$data['connect_user_id'] = $user->id;
				$data['who'] = $who;
				$data['what'] = $what;
				$data['start_date'] = Request::input('start_date');
				$data['end_date'] = Request::input('end_date');
				$data['start_time'] = getMySQLTimeFormat(Request::input('start_time'));
				$data['end_time'] = getMySQLTimeFormat(Request::input('end_time'));

				$weekdays = Request::input('weekdays');
				$count = count($weekdays);
				for($i=0; $i < $count; $i++) {
					$data['content_weekday_' . $weekdays[$i]] = 1;
				}

				$talkShowContent = ConnectContent::create($data);
			}

			if ($talkShowContent) {
				if ($user->station->is_private) {
					$talkShowContent->updateContentToTagsLinkStatic();
				} else {
					$talkShowContent->updateContentToTagsLink();
				}
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $talkShowContent->id, 'content' => $talkShowContent)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}
	/**
	 * Create new talk show
	 */
	public function createMusicMix() {

		try {
			$what = Request::input('what');
			$who = Request::input('who');
			$user = \Auth::User();
			$data = array();
			$data['station_id'] = $user->station->id;
			$data['content_type_id'] = ContentType::findContentTypeIDByName('Music Mix');
			$data['connect_user_id'] = $user->id;
			$data['who'] = $who;
			$data['what'] = $what;
			$data['mix_title'] = Request::input('mix_title');
			$data['start_date'] = Request::input('start_date');
			$data['end_date'] = Request::input('end_date');
			$data['start_time'] = getMySQLTimeFormat(Request::input('start_time'));
			$data['end_time'] = getMySQLTimeFormat(Request::input('end_time'));

			$weekdays = Request::input('weekdays');
			$count = count($weekdays);
			for($i=0; $i < $count; $i++) {
				$data['content_weekday_' . $weekdays[$i]] = 1;
			}

			$musicMix = ConnectContent::create($data);

//
//			if ($musicMix) {
//				if ($user->station->is_private) {
//					$musicMix->updateContentToTagsLinkStatic();
//				} else {
//					$musicMix->updateContentToTagsLink();
//				}
//			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $musicMix->id, 'content' => $musicMix)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}
	/**
	 * Remove talk show
	 */
	public function removeEvent() {
		try {
			$id = Request::input('id');
			$event = ConnectContent::findOrFail($id);
			$event->removeConnectContent();

			return response()->json(array('code' => 0, 'msg' => 'Event Deleted', 'data' => array('id'=> $id)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Remove single event/talk show
	 */
	public function removeSingleEvent() {
		try {
			$id = Request::input('id');
			$before_end_date = Request::input('beforeEndDate');
			$after_start_date = Request::input('afterStartDate');
			$talk_show = ConnectContent::findOrFail($id);

			$before = $talk_show->replicate();
			$after = $talk_show->replicate();

			//We split the talk show timelines into before and after current date
			$before->end_date = $before_end_date;
			$after->start_date = $after_start_date;

			$before->save();
			$after->save();

			if (\Auth::User()->station->is_private) {
				$before->updateContentToTagsLinkStatic();
				$after->updateContentToTagsLinkStatic();
			} else {
				$before->updateContentToTagsLink();
				$after->updateContentToTagsLink();
			}
			
			//Replicate attachments for new before and after talk show recurrences
			$attachments = ConnectContentAttachment::where('content_id', $id)
				->get();
			foreach($attachments as $attachment) {
				$attachment_for_before = $attachment->replicate();
				$attachment_for_before->content_id= $before->id;
				$attachment_for_after = $attachment->replicate();
				$attachment_for_after ->content_id = $after->id;
				$attachment_for_before->save();
				$attachment_for_after->save();
				//Should we delete the original attachments?
			}

			$talk_show->removeConnectContent();

			return response()->json(array('code' => 0, 'msg' => 'Event Deleted', 'data' => array('id'=> $id, 'before' => $before, 'after' => $after, 'att'=> $attachments)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Update single event/talk show
	 */
	public function updateSingleEvent() {
		try {
			$id = Request::input('id');
			$before_end_date = Request::input('beforeEndDate');
			$after_start_date = Request::input('afterStartDate');
			$current_date = Request::input('currentDate');
			$original_event = ConnectContent::findOrFail($id);

			//Replicate the event for before, after and current
			$before = $original_event->replicate();
			$after = $original_event->replicate();

			$current = $original_event->replicate();

			//Update the time of this single current event
			$current->start_time = getMySQLTimeFormat(Request::input('start_time'));
			$current->end_time = getMySQLTimeFormat(Request::input('end_time'));
			$current->start_date = Request::input('start_date');
			$current->end_date = Request::input('end_date');
			$current->what = Request::input('what');
			$current->who = Request::input('who');

			//We split the talk show timelines into before and after current date
			$before->end_date = $before_end_date;
			$after->start_date = $after_start_date;

			$current->start_date = $current->end_date = $current_date;

			if($current->content_type_id == ContentType::GetMusicMixContentTypeID()) {
				$current->mix_title = Request::input('mix_title') ? Request::input('mix_title') : $current->mix_title;
			}

			$before->save();
			$after->save();
			$current->save();
			
			if (\Auth::User()->station->is_private) {
				$before->updateContentToTagsLinkStatic();
				$after->updateContentToTagsLinkStatic();
				$current->updateContentToTagsLinkStatic();
			} else {
				$before->updateContentToTagsLink();
				$after->updateContentToTagsLink();
				$current->updateContentToTagsLink();
			}

			//Replicate attachments for new before and after talk show recurrences
			$attachments = ConnectContentAttachment::where('content_id', $id)
				->get();
			foreach($attachments as $attachment) {
				$attachment_for_before = $attachment->replicate();
				$attachment_for_before->content_id= $before->id;
				$attachment_for_after = $attachment->replicate();
				$attachment_for_after ->content_id = $after->id;
				$attachment_for_current = $attachment->replicate();
				$attachment_for_current->content_id= $current->id;
				$attachment_for_before->save();
				$attachment_for_after->save();
				$attachment_for_current->save();
				//Should we delete the original attachments?
			}


			$original_event->removeConnectContent();

			$is_complete = false;
			$images = ConnectContentAttachment::where('content_id', '=', $current['id'])->whereIn('type', ['image', 'video', 'logo'])->first();

			if(count($images) > 0 && !empty($current['who']) && !empty($current['what']) && $current['action_id'] && !empty($current['action_params']) && $current['is_ready']) {
				$is_complete = true;
			}

			$current->is_complete = $is_complete;

			return response()->json(array('code' => 0, 'msg' => 'Talk Show Updated', 'data' => array('id'=> $id, 'before' => $before, 'after' => $after, 'current' => $current)));
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Ready talk show
	 */
	public function readyContent() {
		
		try {
			
			$content = ConnectContent::findOrFail(Request::input('id'));
			
			$isReady = $content->is_ready == 1 ? 0 : 1;
			
			$content->setContentAsReady($isReady);
						
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $content->id, 'content' => $content)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Update talk show
	 */
	public function updateEvent() {

		try {
			$event = ConnectContent::findOrFail(Request::input('id'));

			$event->who = Request::input('who');
			$event->what = Request::input('what');
			$event->start_date = Request::input('start_date');
			$event->end_date = Request::input('end_date');
			$event->start_time = getMySQLTimeFormat(Request::input('start_time'));
			$event->end_time = getMySQLTimeFormat(Request::input('end_time'));

			$weekdays = Request::input('weekdays');

			$event->content_weekday_0 = 0;
			$event->content_weekday_1 = 0;
			$event->content_weekday_2 = 0;
			$event->content_weekday_3 = 0;
			$event->content_weekday_4 = 0;
			$event->content_weekday_5 = 0;
			$event->content_weekday_6 = 0;
			$count = count($weekdays);
			for($i=0; $i < $count; $i++) {
				if($weekdays[$i] == 0) {
					$event->content_weekday_0 = 1;
				}
				if($weekdays[$i] == 1) {
					$event->content_weekday_1 = 1;
				}
				if($weekdays[$i] == 2) {
					$event->content_weekday_2 = 1;
				}
				if($weekdays[$i] == 3) {
					$event->content_weekday_3 = 1;
				}
				if($weekdays[$i] == 4) {
					$event->content_weekday_4 = 1;
				}
				if($weekdays[$i] == 5) {
					$event->content_weekday_5 = 1;
				}
				if($weekdays[$i] == 6) {
					$event->content_weekday_6 = 1;
				}
			}

			if($event->content_type_id == ContentType::GetTalkContentTypeID()) {
				$event->content_subtype_id = ContentType::GetTalkSubContentTalkShowTypeID();
			}

			if($event->content_type_id == ContentType::GetMusicMixContentTypeID()) {
				$event->mix_title = Request::input('mix_title') ? Request::input('mix_title') : $event->mix_title;
			}

			$event->save();
			//Clone attachments if the who and what exists
//			if($talkShowContent->attachments) {
//				foreach($talkShowContent->attachments as $attachment) {
//					$new_attachment = ConnectContentAttachment::find($attachment->id)->replicate();
//					$new_attachment->content_id = $connectContent->id;
//					$new_attachment->save();
//				}
//			}

			if($event->content_type_id == ContentType::GetTalkContentTypeID()) {
				$is_complete = false;
				$images = ConnectContentAttachment::where('content_id', '=', $event['id'])->whereIn('type', ['image', 'video', 'logo'])->first();

				if (count($images) > 0 && !empty($event['who']) && !empty($event['what']) && $event['action_id'] && !empty($event['action_params']) && $event['is_ready']) {
					$is_complete = true;
				}

				$event->is_complete = $is_complete;

				if ($event) {
					if (\Auth::User()->station->is_private) {
						$event->updateContentToTagsLinkStatic();
					} else {
						$event->updateContentToTagsLink();
					}
				}
			}
			

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $event->id, 'content' => $event)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Create Ad content from preview tag
	 */
	public function createAdFromPreviewTag() {

		try {

			$previewTagId = Request::input('tag_id');

			$previewTagObject = PreviewTag::findOrFail($previewTagId);

			$connectContent = $previewTagObject->createAdContentForTag(\Auth::User()->id);

			if (!$connectContent) {
				throw new \Exception("Unable to create new ad from preview tag.");	
			}
			
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $connectContent->id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Create Ad Content from tag
	 */
	public function createAdFromTag() {

		try {

			$tagId = Request::input('tag_id');

			$tagObject = Tag::findOrFail($tagId);

			$connectContent = $tagObject->createAdContentForTag(\Auth::User()->id);
			
			if (!$connectContent) {
				throw new \Exception("Unable to create new ad from tag.");
			}
			
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $connectContent->id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Create Ad content from audio file
	 */
	public function createAdFromAudio() {

		try {

			$attachment_id = Request::input('attachment_id');

			$attachmentObj = ConnectContentAttachment::findOrFail($attachment_id);

			$attachmentFileName = $attachmentObj->filename;

			$adKey = substr($attachmentFileName, 0, strripos($attachmentFileName, "."));
			$adKey = cleanupAdKey($adKey);

			$user = \Auth::User();

			$data = array();

			$data['station_id'] = $user->station->id;
			$data['content_type_id'] = ContentType::findContentTypeIDByName('Ad');
			$data['connect_user_id'] = $user->id;
			$data['ad_key'] = $adKey;
			$data['is_temp'] = 0;
			$data['audio_enabled'] = 1;

			$connectContent = ConnectContent::create($data);

			$attachmentObj->content_id = $connectContent->id;
			$attachmentObj->save();

			$connectContent = ConnectContent::findOrFail($connectContent->id);

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('attachment_id' => $attachmentObj->id, 'filename' => $attachmentFileName, 'url' => $attachmentObj->saved_path, 'content' => $connectContent)));


		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Create Tag from content
	 */
	public function createTagFromContent() {

		try {

			$content_id = Request::input('content_id');

			if (empty($content_id)) throw new \Exception('Content ID is missing');

			$content = ConnectContent::findOrFail($content_id);

			$currentUser = \Auth::User();

			$now = getCurrentMilisecondsTimestamp();

			$newTag = Tag::create([
				'tagger_id' 			=> $currentUser->id,
				'station_id'			=> $currentUser->station_id,
				'content_type_id'		=> $content->content_type_id,
				'tag_timestamp'			=> $now,
				'who'					=> $content->who,
				'what'					=> $content->what,
				'adkey'					=> $content->ad_key,
				'is_valid'				=> 1,
				'insert_timestamp'		=> $now,
				'insert_lag'			=> 0,
				'connect_content_id'	=> $content->id,
				'coverart_id'			=> 0,
				'tag_duration'			=> 0,
				'cart'					=> "",
				'original_who'			=> $content->who,
				'original_what'			=> $content->what
			]);

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('tag_id_list' => implode(", ", $content->tagIDListForContent()))));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Get news page
	 */
	public function news($id=0) {
		return view('airshrconnect.news')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_news', ContentType::GetNewsContentTypeID())
			->with('contentID', $id);
	}

	/**
	 * Get agency details
	 */
	public function getNews($content_type_id) {
		try {
			$station_id = \Auth::User()->station->id;
			$content = null;
			try {
				$content = ConnectContent::where('content_type_id', '=', $content_type_id)
					->where('station_id', '=', $station_id)
					->first();
			} catch(Exception $ex2) {

			}
			if($content === null) {
				$content = ConnectContent::create(['station_id'=>$station_id, 'content_type_id' => $content_type_id]);
			}

			return array('code' => 0, 'data' => array('content_id' => $content->id));
		} catch (Exception $ex) {
			return array('code' => -1, 'msg' => $ex->getMessage());
		}
	}

	/**
	 * Get ad page
	 */
	public function ad($id=0) {
		return view('airshrconnect.ad')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_ad', ContentType::GetAdContentTypeID())
			->with('contentID', $id);
	}

	/**
	 * Get agency details
	 */
	public function getAgencyDetails() {
		try {
			$agency_name = Request::input('agency_name');
			$station_id = \Auth::User()->station->id;
			$agency = ConnectContentAgency::where('station_id', '=', $station_id)
				->where('agency_name', '=', $agency_name)
				->first();

			return array('code' => 0, 'data' => $agency);
		} catch (Exception $ex) {
			return array('code' => -1, 'msg' => $ex->getMessage());
		}
	}

	/**
	 * Get client info
	 */
	public function clientInfo($id=0, $who='') {
		return view('airshrconnect.clientinfo')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_clientinfo', ContentType::GetClientInfoContentTypeID())
			->with('clientID', $id)
			->with('who', $who);
	}

	/**
	 * This is used for adding a new client from the daily log
	 * we want to pre-fill the trading name and client name with the 'who' of an ad
	 */
	public function postClientInfo() {

		$who = '';
		if(!empty(Request::input('who'))) {
			$who = Request::input('who');
		}

		return $this->clientInfo(0, $who);
	}

	/**
	 * Get client info
	 */
	public function getClientInfo($id) {
		try {
			$client = ConnectContentClient::find($id);
			$logo = ConnectContentAttachment::find($client->logo_attachment_id);
			$image_attachment1 = ConnectContentAttachment::find($client->image_attachment1_id);
			$image_attachment2 = ConnectContentAttachment::find($client->image_attachment2_id);
			$image_attachment3 = ConnectContentAttachment::find($client->image_attachment3_id);
			$product = ConnectContentProduct::find($client->product_id);
			$agency = ConnectContentAgency::find($client->content_agency_id);
			$executive = \App\ConnectContentExecutive::find($client->content_manager_user_id);

			//do we need to update attachment to have client id?
			$data = array();
			$data['client_id'] = $client->id;
			$data['client_name'] = $client->client_name;
			$data['client_type'] = $client->client_type;
			$data['client_contact_name'] = $client->client_contact_name;
			$data['client_contact_phone'] = $client->client_contact_phone;
			$data['client_contact_email'] = $client->client_contact_email;

			if(!empty($agency)) {
				$data['agency_name'] = $agency->agency_name;
				$data['agency_contact_name'] = $agency->agency_contact_name;
				$data['agency_contact_phone'] = $agency->agency_contact_phone;
				$data['agency_contact_email'] = $agency->agency_contact_email;
			}
			if(!empty($logo)) {
				$data['attachments'][] = $logo->getJSONArrayForAttachment();
			}
			if(!empty($image_attachment1)) {
				$data['attachments'][] = $image_attachment1->getJSONArrayForAttachment();
			}
			if(!empty($image_attachment2)) {
				$data['attachments'][] = $image_attachment2->getJSONArrayForAttachment();
			}
			if(!empty($image_attachment3)) {
				$data['attachments'][] = $image_attachment3->getJSONArrayForAttachment();
			}
			if(!empty($product)) {
				$data['product_name'] = $product->product_name;
			}
			if(!empty($executive)) {
				$data['client_executive'] = $executive->executive_name;
			}

			$data['map'] = array('address' => $client->map_address1,'lat' => $client->map_address1_lat,'lng' => $client->map_address1_lng);
			$data['who'] = $client->who;
			$data['what'] = $client->what;
			$data['more'] = $client->more;
			$data['is_ready'] = $client->is_ready;
			$data['action_id'] = $client->action_id;
			$data['action_params'] = json_decode($client->action_params);
			$data['content_color'] = ContentType::getContentTypeColor(ContentType::GetAdContentTypeID());
			$data['client_twitter'] = $client->client_twitter;

			$data['text_enabled'] = empty($client->who) ? 0 : 1;
			$data['logo_enabled'] = empty($client->logo_attachment_id) ? 0 : 1;
			$data['image_enabled'] = empty($client->image_attachment1_id) && empty($client->image_attachment2_id) && empty($client->image_attachment3_id) ? 0 : 1;

			return response(array('code' => 0, 'data' => $data));
		}  catch (\Exception $ex){
			return response(array('msg' => $ex->getMessage()));
		}

	}

	public function saveClientInline()
	{
		try{
			$field_name = Request::input('name');
			$field_val = Request::input('value');
			$field_id = Request::input('pk');
			$clientObj = ConnectContentClient::find($field_id);

			if($field_name == 'map_address1') {
				$map_address = $field_val;
				$geoInfo = getGEOFromAddress($map_address);
				if (!empty($geoInfo)) {
					$clientObj->map_address1_lat = $geoInfo['lat'];
					$clientObj->map_address1_lng = $geoInfo['lng'];
				} else {
					$clientObj->map_address1_lat = '';
					$clientObj->map_address1_lng = '';
				}
			}

			$clientObj->$field_name = $field_val;

			if($field_name == 'action_params') {
				$action_types = array();

				$actions = \App\ConnectContentAction::orderBy('id', 'desc')->get();

				foreach ($actions as $action) {
					$action_types[$action['id']] = $action['action_type'];
				}
				if ($field_val) {
					if ($clientObj->action_id == 0) {
						$clientObj->$field_name = '{"website":"' . $field_val . '"}';
					} else if ($action_types[$clientObj->action_id] == 'website' || $action_types[$clientObj->action_id] == 'get' || $action_types[$clientObj->action_id] == 'call') {
						$clientObj->$field_name = '{"website":"' . $field_val . '"}';
					} else if ($action_types[$clientObj->action_id] == 'phone' || $action_types[$clientObj->action_id] == 'sms') {
						$clientObj->$field_name = '{"phone":"' . $field_val . '"}';
					}
				} else {
					$clientObj->$field_name = '';
				}
			}

			$clientObj->save();

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('client_id' => $field_id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Ready talk show
	 */
	public function readyClientInfo() {

		try {
			$client = ConnectContentClient::findOrFail(Request::input('id'));

			$client->is_ready = $client->is_ready == 1 ? 0 : 1;

			$client->save();

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $client->id, 'content' => $client)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	public function getClientExecutiveList() {

		try {

			$station_id = \Auth::User()->station->id;
			$managers = \App\ConnectContentExecutive::where('station_id', '=', $station_id)
				->get();
			$list = array();
			foreach($managers as $manager) {
				$list[] = $manager->executive_name;
			}

			return array('code' => 0, 'data' => $list);
		} catch (Exception $ex) {
			return array('code' => -1, 'msg' => $ex->getMessage());
		}
	}
	/**
	 * Save posted client content to connect database
	 */
	protected function saveClientContent() {

		try {

			$connectClient = null;
			$client_id = Request::input('client_id');

			$newEntryMode = empty($client_id) ? true : false;

			if (!$newEntryMode) {
				$connectClient = ConnectContentClient::findOrFail($client_id);
			}


			$data = array();

			$data['client_name'] = Request::input('content_client');
			$data['station_id'] = \Auth::User()->station->id;

			$existingClient = ConnectContentClient::clientExists($data['station_id'], $data['client_name']);

			if ($existingClient && $existingClient->id != $client_id) {
				return response()->json(array('code' => -1, 'msg' => 'Client company name already exists.'));
			}

			$data['who'] = Request::input('who');

			$content_product = Request::input('content_product');
			if (!empty($content_product)) {
				$content_product_id = ConnectContentProduct::createOrFindProduct($data['station_id'], $content_product);
				if (!empty($content_product_id)) $data['product_id'] = $content_product_id;
			}
			else {
				$data['product_id'] = 0;
			}

			$data['client_type'] = Request::input('client_type');

//			$data['client_contact_name'] = Request::input('client_contact_name');
//			$data['client_contact_email'] = Request::input('client_contact_email');
//			$data['client_contact_phone'] = Request::input('client_contact_phone');

//			$data['content_agency_id'] = Request::input('content_agency');
//			$agencyData = array();
//			$agencyData['agency_name'] = Request::input('agency_name');
//			$agencyData['station_id'] = $data['station_id'];
//			$agencyData['agency_contact_name'] = Request::input('agency_contact_name');
//			$agencyData['agency_contact_phone'] = Request::input('agency_contact_phone');
//			$agencyData['agency_contact_email'] = Request::input('agency_contact_email');
//
//			$agency = ConnectContentAgency::agencyExists($agencyData['station_id'], $agencyData['agency_name']);
//
//			if($agency) {
//				$agency->update($agencyData);
//			} else {
//				$agency = ConnectContentAgency::create($agencyData);
//			}
//
//			$data['content_agency_id'] = $agency->id;

			$contentManagerName = Request::input('client_executive');
			$contentManagerId = \App\ConnectContentExecutive::createOrFindExecutive($data['station_id'], $contentManagerName);
			$data['content_manager_user_id'] = $contentManagerId;

//			$data['map_address1'] = Request::input('map_address1');

//			if (!empty($data['map_address1'])) {
//				$geoInfo = getGEOFromAddress($data['map_address1']);
//				if (!empty($geoInfo)) {
//					$data['map_address1_lat'] = $geoInfo['lat'];
//					$data['map_address1_lng'] = $geoInfo['lng'];
//				} else {
//					$data['map_address1_lat'] = '';
//					$data['map_address1_lng'] = '';
//				}
//			}
//			else {
//				$data['map_address1_lat'] = '';
//				$data['map_address1_lng'] = '';
//			}

//			$data['is_ready'] = Request::input('is_ready') == 'true' ? "1" : "0";

			$attachments = Request::input('attachments');

			if (!empty($attachments)) {
				foreach($attachments as $attachment) {
					if (empty($attachment['type'])) continue;
					if ($attachment['type'] == 'logo') {
						$data['logo_attachment_id'] = $attachment['content_attachment_id'];
						break;
					}
				}
			}

			$data['client_twitter'] = Request::input('client_twitter');

			if($data['client_twitter']) {
				if (strpos($data['client_twitter'], '@') != 0) {
					$data['client_twitter'] = '@' . $data['client_twitter'];
				}
			}

			if ($newEntryMode) {
				$connectClient = ConnectContentClient::create($data);
			} else {
				if (!$connectClient->update($data)) {
					throw new \Exception("Save error.");
				}
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('client_id' => $connectClient->id, 'client' => $connectClient)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Save posted content to connect database
	 */
	public function saveImages() {
		try {
			$is_client = Request::input('is_client');
			$coverartId = Request::input('coverart_id');
			
			$musicContent = (!empty($coverartId) && $coverartId > 0) ? true : false;
			
			if(!$is_client) {
				
				if (!$musicContent) {
					$content_id = Request::input('content_id');
					$connectContent = ConnectContent::findOrFail($content_id);
				} else {
					$connectContent = new \stdClass();
					$connectContent->id = 0;
					
					$coverArtObj = CoverArt::findOrFail($coverartId);
					
				}
			} else {
				$connectContent = new \stdClass();
				$connectContent->id = 0;
				$client = ConnectContentClient::find(Request::input('client_id'));
			}

			if (!$is_client && !$musicContent) {
				//remove previous attachment links - except audio
				\DB::table('airshr_connect_content_attachments')->where('content_id', $connectContent->id)->where('type', '<>', 'audio')->update(['content_id' => 0]);
			}

			$attachments = Request::input('attachments');

			//		\DB::table('airshr_connect_content_attachments')->where('content_id', $connectContent->id)->update(['content_id' => 0]);

			if (!empty($attachments)) {

				$attachmentContentIDs = array();

				$count = 0;

				foreach($attachments as $attachment) {

					if (empty($attachment['type'])) continue;

					// we only create new field when attachment is video link
					if ($attachment['type'] == 'video' && !empty($attachment['video_url'])) {

						$attachmentObj = ConnectContentAttachment::createAttachmentFromFile(\Auth::User()->station->id, null, 'video', '', '', '', $attachment['video_url'], $connectContent->id);

						if ($is_client){
							switch ($count) {
								case 0:
									$client->image_attachment1_id = $attachmentObj->id;
									$count++;
									break;
								case 1:
									$client->image_attachment2_id = $attachmentObj->id;
									$count++;
									break;
								case 2:
									$client->image_attachment3_id = $attachmentObj->id;
									$count++;
									break;
							}
						}  else if ($musicContent) {
							switch ($count) {
								case 0:
									$coverArtObj->attachment1 = $attachmentObj->id;
									$count++;
									break;
								case 1:
									$coverArtObj->attachment2 = $attachmentObj->id;
									$count++;
									break;
								case 2:
									$coverArtObj->attachment3 = $attachmentObj->id;
									$count++;
									break;
							}
						}
						
					} else {
						
						if (!empty($attachment['content_attachment_id'])) {
							
							$attachmentContentIDs[] = $attachment['content_attachment_id'];
							
							if($is_client) {
								if ($attachment['type'] == 'logo') {
									$client->logo_attachment_id = $attachment['content_attachment_id'];
								}
								else {
									switch($count) {
										case 0:
											$client->image_attachment1_id = $attachment['content_attachment_id'];
											$count++;
											break;
										case 1:
											$client->image_attachment2_id = $attachment['content_attachment_id'];
											$count++;
											break;
										case 2:
											$client->image_attachment3_id = $attachment['content_attachment_id'];
											$count++;
											break;
									}
								}
							} else if ($musicContent) {
								
								switch ($count) {
									case 0:
										$coverArtObj->attachment1 = $attachment['content_attachment_id'];
										$count++;
										break;
									case 1:
										$coverArtObj->attachment2 = $attachment['content_attachment_id'];
										$count++;
										break;
									case 2:
										$coverArtObj->attachment3 = $attachment['content_attachment_id'];
										$count++;
										break;
								}
							}
						}
						
						
						
					}
				}

				if (count($attachmentContentIDs) > 0) {
					if(!$is_client && !$musicContent) {
						\DB::table('airshr_connect_content_attachments')->whereIn('id', $attachmentContentIDs)->update(['content_id' => $connectContent->id]);
					}
				}
			}

			if(!$is_client) {
				if ($musicContent) {
					$coverArtObj->save();	
				} else {
					$connectContent->save();
				}
			} else {
				$client->save();
			}

			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Talk break page
	 */
	public function talkBreak($id = 0) {
		$is_new = 0;
		if($id){
			try {
				$content = ConnectContent::findOrFail($id);
			} catch (\Exception $ex) {
				return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
			}
		} else {
			$data = array();
			$data['content_type_id'] = ContentType::GetTalkContentTypeID();
			$data['content_subtype_id'] = ContentType::GetTalkSubContentIndividualSegmentTypeID();
			$data['station_id'] = \Auth::User()->station->id;
			$data['connect_user_id'] = \Auth::User()->id;
			$content = ConnectContent::create($data); //talk show, talk break type
			$is_new = 1;
		}
		return view('airshrconnect.talkbreak')
			->with('is_new', $is_new)
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_talkbreak', ContentType::GetTalkBreakContentTypeID())
			->with('content', $content);
	}


	/**
	 * Set competition
	 */
	public function setCompetition() {

		try {
			
			$contentId = Request::input('id');
			$tagId = Request::input('tagId');

			$tag = null;
			try {
				$tag = Tag::findOrFail($tagId); 
			} catch (\Exception $ex1) {}
			
			$content = null;
			try {
				$content = ConnectContent::findOrFail($contentId);
			} catch (\Exception $ex1) {}
				
			$bCreatedNew = false;
			if (empty($content)) {
				$content = ConnectContent::CreateEmptyTalkBreak(\Auth::User()->station->id, \Auth::User()->id);
				$bCreatedNew = true;
			} else if (!$content->isContentTalkBreak()) {		// if not talk break?
				$content = $content->createTalkBreakFromContent();
			}
			if (empty($content)) {
				throw new \Exception("Unable to create new talk break.");
			}
			if (!empty($tag)) {
				$tag->connect_content_id = $content->id;
				if ($bCreatedNew) {
					$tag->who = '';
					$tag->what = '';
				}
				$tag->save();
			}
						
			$content->is_competition = $content->is_competition == 1 ? 0 : 1;
			$content->save();

			$content->updateWhoAndWhatForTagsAndEvents();
			$content->sendCompetitonResultGenerationRequest(); // send competition generation request
			
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $content->id, 'content' => $content)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Set vote
	 */
	public function setVote() {

		try {
			
			$contentId = Request::input('id');
			$tagId = Request::input('tagId');
			
			$tag = null;
			try {
				$tag = Tag::findOrFail($tagId);
			} catch (\Exception $ex1) {}
				
			$content = null;
			try {
				$content = ConnectContent::findOrFail($contentId);
			} catch (\Exception $ex1) {}
				
			$bCreateNew = false;
			
			if (empty($content) || !$content->isContentTalkBreak()) {
				$content = ConnectContent::CreateEmptyTalkBreak(\Auth::User()->station->id, \Auth::User()->id);
				$bCreateNew = true;
			} /*else if (!$content->isContentTalkBreak()) {
				$content = $content->createTalkBreakFromContent();
			}*/
			
			if (empty($content)) {
				throw new \Exception("Unable to create new talk break.");
			}
			
			$content->is_vote = $content->is_vote == 1 ? 0 : 1;
			$content->save();
			
			$voteMatchedWithTag = false;
			
			if (!empty($tag)) {
				$voteMatchedWithTag = $tag->setTagWithVote($content);
			}
			
			if (!empty($tag) && $voteMatchedWithTag) {
				$tag->sendEventsUpdatePushForTag();
				$tag->applyForVoteGeneration();
			}
			
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $content->id, 'content' => $content)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Save posted content to connect database
	 */
	public function saveContent() {

		try {

			$connectContent = null;
			$contentTypeId = Request::input('content_type_id');

			// if submitted info is client, then use another function to save
			if ($contentTypeId == ContentType::GetClientInfoContentTypeID()) {
				return $this->saveClientContent();
			}

			$content_id = Request::input('content_id');

			$newEntryMode = empty($content_id) ? true : false;

			if (!$newEntryMode) {
				$connectContent = ConnectContent::findOrFail($content_id);
			}

			$adKey = Request::input('ad_key');
			$adKey = cleanupAdKey($adKey);

			if ($contentTypeId == ContentType::GetAdContentTypeID()) {
				$existingAdWithKey = ConnectContent::findAdContentOfKey(\Auth::User()->station->id, $adKey);
				if ($existingAdWithKey && $existingAdWithKey->id != $content_id) {
					return response()->json(array('code' => 100, 'msg' => 'Same Ad Key exists.', 'data' => array('contentID' => $existingAdWithKey->id)));
				}
			}

			$data = array();

			$data['station_id'] = \Auth::User()->station->id;
			$data['content_type_id'] = Request::input('content_type_id');
			$data['content_rec_type'] = Request::input('content_rec_type');
			$data['content_subtype_id'] = Request::input('content_subtype_id');
			$data['connect_user_id'] = \Auth::User()->id;
			$data['who'] = Request::input('who');
			$data['what'] = Request::input('what');

			$updateTagsForContent = false;
			// if who or what info has changed, update tags
			if (!$newEntryMode && ($connectContent->who != $data['who'] || $connectContent->what != $data['what'])) {
				$updateTagsForContent = true;
			}

			$data['more'] = Request::input('more');
			$data['description'] = Request::input('description');
			$data['ad_length'] = Request::input('ad_length');

			$content_client = Request::input('content_client');

			if (!empty($content_client)) {
				$content_client_id = ConnectContentClient::createOrFindClient($data['station_id'], $content_client);
				if (!empty($content_client_id)) $data['content_client_id'] = $content_client_id;
			}

			$content_product = Request::input('content_product');

			if (!empty($content_product)) {
				$content_product_id = ConnectContentProduct::createOrFindProduct($data['station_id'], $content_product);
				if (!empty($content_product_id)) $data['content_product_id'] = $content_product_id;
			}

			$data['content_line_number'] = Request::input('content_line_number');
			$data['content_contact'] = Request::input('content_contact');
			$data['content_email'] = Request::input('content_email');
			$data['content_phone'] = Request::input('content_phone');
			$data['content_instructions'] = Request::input('content_instructions');
			$data['content_voices'] = Request::input('content_voices');
			$data['content_agency_id'] = Request::input('content_agency');

			$data['content_manager_user_id'] = Request::input('content_manager_user_id');
			$data['atb_date'] = Request::input('atb_date');
			$data['start_date'] = parseDateToMySqlFormat(Request::input('start_date'));
			$data['end_date'] = parseDateToMySqlFormat(Request::input('end_date'));
			$data['ad_key'] = $adKey;
			$data['map_included'] = Request::input('map_included') == 'true' ? "1" : "0";
			$data['map_address1'] = Request::input('map_address1');
			$data['map_address2'] = Request::input('map_address2');

			if (!empty($data['map_address1'])) {
				$data['map_included'] = "1";
				$geoInfo = getGEOFromAddress($data['map_address1']);
				if (!empty($geoInfo)) {
					$data['map_address1_lat'] = $geoInfo['lat'];
					$data['map_address1_lng'] = $geoInfo['lng'];
				} else {
					$data['map_address1_lat'] = '';
					$data['map_address1_lng'] = '';
				}
			}
			else {
				$data['map_included'] = "0";
				$data['map_address1_lat'] = '';
				$data['map_address1_lng'] = '';
			}

			$data['session_name'] = Request::input('content_session_name');

			$data['action_id'] = Request::input('action_id');

			$action_params = Request::input('action_param');

			$data['action_params'] = empty($action_params) ? "" : json_encode($action_params);

			$data['text_enabled'] = Request::input('text_enabled') == 'true' ? "1" : "0";
			$data['audio_enabled'] = Request::input('audio_enabled') == 'true' ? "1" : "0";
			$data['image_enabled'] = Request::input('image_enabled') == 'true' ? "1" : "0";
			$data['action_enabled'] = Request::input('action_enabled') == 'true' ? "1" : "0";

			$data['is_ready'] = Request::input('is_ready') == 'true' ? "1" : "0";

			$contentVersion = Request::input('content_version');
			if (empty($contentVersion)) $contentVersion = 'new';

			$data['content_version'] = $contentVersion;

			$data['start_time'] = Request::input('start_time');
			$data['end_time'] = Request::input('end_time');

			$data['start_time'] = getMySQLTimeFormat($data['start_time']);
			$data['end_time'] = getMySQLTimeFormat($data['end_time']);

			$contentWeekDays = Request::input('content_weekdays');
			if (!empty($contentWeekDays)) {
				foreach($contentWeekDays as $key => $val) {
					$data['content_weekday_' . $key] = $val == 'false' ? 0 : 1 ;
				}
			}

			$data['is_competition'] = Request::input('is_competition') == 'true' ? "1" : "0";

			if ($newEntryMode) {
				$connectContent = ConnectContent::create($data);
			} else {
				if (!$connectContent->update($data)) {
					throw new \Exception("Save error.");
				}
			}

			if ($contentTypeId == ContentType::GetAdContentTypeID()) {
				$contentDates = Request::input('contentDates');
				if (!empty($contentDates)) {
					$connectContent->saveContentDatesArray($contentDates);
				}
			}

			if (\Auth::User()->station->is_private) {
				$connectContent->updateContentToTagsLinkStatic();
			} else {
				$connectContent->updateContentToTagsLink();
			}

			/*if ($contentTypeId == ContentType::GetTalkContentTypeID() && $data['content_subtype_id'] == ContentType::GetTalkSubContentIndividualSegmentTypeID()) {

				$contentAssociation = Request::input('content_association');
				$connectContent->updateContentToTagsLinkAssociation($contentAssociation);
			}*/


			// Remove previous attachments links 
			\DB::table('airshr_connect_content_attachments')->where('content_id', $connectContent->id)->update(['content_id' => 0]);

			$attachments = Request::input('attachments');

			if (!empty($attachments)) {

				$attachmentContentIDs = array();

				foreach($attachments as $attachment) {

					if (empty($attachment['type'])) continue;

					// we only create new field when attachment is video link
					if ($attachment['type'] == 'video' && !empty($attachment['video_url'])) {

						$attachmentObj = ConnectContentAttachment::createAttachmentFromFile(\Auth::User()->station->id, null, 'video', '', '', '', $attachment['video_url'], $connectContent->id);
						/*$videoInfo = getVideoURLDetails($attachment['video_url']);
						
						$width = 0;
						$height = 0;
						
						if (isset($videoInfo['width'])) {
							$width = $videoInfo['width'];
							unset($videoInfo['width']);
						}
						
						if (isset($videoInfo['height'])) {
							$height = $videoInfo['height'];
							unset($videoInfo['height']);
						}
						
						$attachmentObj = ConnectContentAttachment::create([
								'content_id' => $connectContent->id,
								'type' => 'video',
								'filename' => 'video',
								'saved_name' => 'video',
								'saved_path' => $attachment['video_url'],
								'width'		=> $width,
								'height'	=> $height,
								'extra'		=> json_encode($videoInfo)
						]);*/
					} else {
						if (!empty($attachment['content_attachment_id'])) $attachmentContentIDs[] = $attachment['content_attachment_id'];
					}
				}

				if (count($attachmentContentIDs) > 0) {
					\DB::table('airshr_connect_content_attachments')->whereIn('id', $attachmentContentIDs)->update(['content_id' => $connectContent->id]);
				}
			}


			$subContents = Request::input('subContents');
			$subContentsSync = Request::input('subContentsSync');
			$subContentsDate = Request::input('subContentsDate');

			// Material Instruction Specific Processing
			if ($data['content_type_id'] == ContentType::findContentTypeIDByName('Material Instruction')) {

				if (count($subContents) > 0) {
					\DB::table('airshr_connect_contents')->whereIn('id', $subContents)->update(['is_temp' => 0]);
					$connectContent->removeSubContents();
					$connectContent->setSubContents($subContents, empty($subContentsSync) ? array() : $subContentsSync, empty($subContentsDate) ? array() : $subContentsDate);
				}

				$connectContent->copyValuesToSubContents();
			}


			if ($updateTagsForContent) {
				//$connectContent->updateWhoAndWhatForTagsAndEvents();
			}

			// search audio file
			$connectContent->searchAudioFileAndLink();

			$responseData = array();
			$responseData['content_id'] = $connectContent->id;
			if (\Auth::User()->station->is_private) {
				$responseData['tag_id_list'] = implode(", ", $connectContent->tagIDListForContent());
			} else {
				$responseData['tag_id_list'] = "";
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $responseData));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * AirShr Connect OnAir Page
	 */
	public function onAir() {

		if (\Auth::User()->isInvestor()) {
			die("Not authorized.");
			exit();
		}
		
		return view('airshrconnect.onair')
			->with('WebSocketURL', \Config::get('app.WebSocketURL'))
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('talkbreak_autocomplete_list', ConnectContent::GetTalkBreakAutoCompleteList(\Auth::User()->station->id))
			->with('client_trading_name_list', \Auth::User()->station->contentClientTradingNameArray());

	}

	/**
	 * AirShr OnAir Initial Data
	 */
	public function onAirData() {

		$user = \Auth::User();

		$timestamp = Request::input('timestamp');
		$loadPreviewTags = Request::input('loadPreviewTags');

		if (empty($timestamp)) {
			$timestamp = 0;
			$prevTagTimestamp = getCurrentMilisecondsTimestamp();
		} else {
			$prevTagTimestamp = $timestamp;
		}

		$tags = Tag::getTodayTags($user->station->id, $timestamp);

		$result = array();

		$result['prev_tags'] = array();
		$result['past_tags'] = array();
		$result['current_tag'] = null;

		for ($i = 0; $i < count($tags) - 1; $i++) {
			$result['past_tags'][] = $tags[$i]->getArrayDataForOnAir();
		}

		if (count($tags) > 0) {
			$result['current_tag'] = $tags[count($tags) - 1]->getArrayDataForOnAir();
		}

		if (!empty($loadPreviewTags)) {
			$prevTags = PreviewTag::getTodayTags($user->station->id, $prevTagTimestamp);
			for ($i = 0; $i < count($prevTags); $i++) {
				$result['prev_tags'][] = $prevTags[$i]->getArrayDataForOnAir();
			}
		}

		return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $result));

	}

	/**
	 * Airshr connect content page
	 */

	public function content(){

		$currentUser = \Auth::User();
		
		if ($currentUser->isInvestor()) {
			return new RedirectResponse(url('/dashboard'));
		}
		
		$station = $currentUser->station;

		$contentTypeListForConnect = ContentType::$CONTENT_TYPES_FOR_CONNECT;

		// limit access to client info for non-admin users
		/*if (!$currentUser->isAdminUser()) {
			unset($contentTypeListForConnect[ContentType::GetClientInfoContentTypeID()]);
		}*/

		if ($currentUser->isClientManager()) {
			$contentTypeListForConnect = array();
			$contentTypeListForConnect[ContentType::GetClientInfoContentTypeID()] = ContentType::$CONTENT_TYPES_FOR_CONNECT[ContentType::GetClientInfoContentTypeID()];
		}

		$initialFormMode = Request::input('initialFormMode');
		$initialContentID = Request::input('initialContentID');
		$initialContentTypeID = Request::input('initialContentTypeID');
		$prevPage		 = Request::input('prevPage');

		if (empty($initialContentTypeID)) $initialContentTypeID = 0;
		if (empty($initialContentID)) $initialContentID = 0;

		return view('airshrconnect.content')
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', $contentTypeListForConnect)
			->with('ad_duration_list', ConnectContent::$AD_DURATION_LIST)
			->with('ad_percent_list', ConnectContent::$AD_PERCENT_LIST)
			->with('client_list', $station->contentClientsArray())
			->with('product_list', $station->contentProductsArray())
			->with('executive_list', $station->contentExecutives)
			->with('agency_list', $station->contentAgencies)
			->with('action_list', ConnectContentAction::all())
			->with('content_version_list', ConnectContent::$MATERIAL_INSTRUCTION_VERSION_LIST)
			->with('content_sub_type_list', ContentType::$CONTENT_SUB_TYPES)
			->with('WebSocketURL', \Config::get('app.WebSocketURL'))
			->with('station_info', json_encode($station->getJSONArrayForConnect()))
			->with('initialFormMode', $initialFormMode)
			->with('initialContentID', $initialContentID)
			->with('prevPage', $prevPage)
			->with('client_trading_name_list', \Auth::User()->station->contentClientTradingNameArray())
			->with('initialContentTypeID', $initialContentTypeID);

	}

	/**
	 * Returns talk shows
	 */
	public function talkShowList() {

		$station_id = \Auth::User()->station->id;
		$content_type_talk = ContentType::findContentTypeIDByName('Talk');
		$contents = ConnectContent::where('station_id', '=', $station_id)
									->where('content_type_id', '=', $content_type_talk)
									->where('content_subtype_id', '=', ContentType::GetTalkSubContentTalkShowTypeID())
									->whereNotNull('what')
									->groupBy('what')
									->get(['what']);

		$list = array();
		foreach($contents as $content) {
			$list[] = $content->what;
		}

		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $list));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Returns talk shows
	 */
	public function talentList() {

		$station_id = \Auth::User()->station->id;
		$content_type_talk = ContentType::findContentTypeIDByName('Talk');
		$contents = ConnectContent::where('station_id', '=', $station_id)
			->where('content_type_id', '=', $content_type_talk)
			->whereNotNull('who')
			->groupBy('who')
			->get(['who']);

		$list = array();
		foreach($contents as $content) {
			$list[] = $content->who;
		}

		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $list));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Returns connect client company list
	 */
	public function stationClientList() {

		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => \Auth::User()->station->contentClientsArray()));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Returns connect client company list
	 */
	public function tradingNameList() {

		try {

			$clients = ConnectContentClient::where('station_id', '=', \Auth::User()->station->id)->whereNotNull('who')->get();
			$tradingNames = array();
			foreach($clients as $client) {
				$tradingNames[] = $client->who;
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $tradingNames));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Returns connect client company list
	 */
	public function agencyList() {
		$station = \Auth::User()->station;
		$agencyList = ConnectContentAgency::where('station_id', '=', $station->id)->get();
		$agencies = array();
		foreach($agencyList as $agency) {
			$agencies[] = $agency->agency_name;
		}
		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $agencies));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Returns connect product list
	 */
	public function stationProductList() {

		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => \Auth::User()->station->contentProductsArray()));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Remote uploaded attachment
	 * @throws \Exception
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function removeFile() {

		try {

			$attachmentID = Request::input('attachment_id');

			if (empty($attachmentID)) throw new \Exception('Attachment ID is missing.');

			$attachment = ConnectContentAttachment::findOrFail($attachmentID);

			$content_id = $attachment->content_id;

			$client_id = Request::input('client_id');

			if($client_id) {
				$client = ConnectContentClient::find($client_id);
				if($attachmentID == $client->image_attachment1_id) {
					$client->image_attachment1_id = 0;
				}
				if($attachmentID == $client->image_attachment2_id) {
					$client->image_attachment2_id = 0;
				}
				if($attachmentID == $client->image_attachment3_id) {
					$client->image_attachment3_id = 0;
				}
				if($attachmentID == $client->logo_attachment_id) {
					$client->logo_attachment_id = 0;
				}
				$client->save();
			}

			$attachment->removeAttachment();

			if($content_id) {
				$content = ConnectContent::find($content_id);
				if($content) {
					$content->save();
				}
			}

			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Remove Connect Client info
	 */

	protected function removeClient() {

		try {

			$field_id = Request::input('pk');

			$contentObj = ConnectContentClient::find($field_id);

			$contentObj->removeConnectClient();

			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Remove Content From Parent
	 */
	public function removeContentFromParent(){
		try {

			$field_id = Request::input('pk');

			$contentObj = ConnectContent::find($field_id);

			if ($contentObj->is_temp) {
				$contentObj->removeConnectContent();
			} else {

				$parent_id = Request::input('parent_id');
				$child_content_date_id = Request::input('child_content_date_id');

				if (!ConnectContentBelongs::removeBelongsInfo($parent_id, $field_id, $child_content_date_id)) {
					throw new \Exception('Unable to remove.');
				}
			}
			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Remote Connect Content
	 */
	public function removeContent() {

		try {

			$field_id = Request::input('pk');

			$contentTypeID = Request::input('content_type');

			if ($contentTypeID == ContentType::GetClientInfoContentTypeID()) {
				return $this->removeClient();
			}

			$contentObj = ConnectContent::find($field_id);

			$contentObj->removeConnectContent();

			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}


	/**
	 * Sync sub content
	 */
	public function syncSubContent(){

		try {

			$content_id = Request::input('content_id');
			$parent_id = Request::input('parent_id');
			$content_sync = Request::input('content_sync');
			$child_content_date_id = Request::input('content_date_id');

			ConnectContentBelongs::setSyncMode($parent_id, $content_id, $child_content_date_id, $content_sync);

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('content_sync' => $content_sync)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Update temp ad for material design
	 */
	public function updateTempAd() {
		try {

			$field_name = Request::input('name');
			$field_val = Request::input('value');
			$field_id = Request::input('pk');
			$child_content_date_id = Request::input('child_content_date_id');
			$check_client_details = Request::input('check_client_details');
			$check_talkbreak_suggestion = Request::input('check_talkbreak_suggestion');

			$contentObj = ConnectContent::find($field_id);

			if ($field_name == 'start_date' || $field_name == 'end_date') {
				$field_val = parseDateToMySqlFormat($field_val);
				if ($field_name == 'start_date') {
					$date_id = $contentObj->addContentStartDate($child_content_date_id, $field_val);
				} else if ($field_name == 'end_date') {
					$date_id = $contentObj->addContentEndDate($child_content_date_id, $field_val);
				}
				if ($child_content_date_id != $date_id) {
					$parent_id = Request::input('parent_content_id');
					ConnectContentBelongs::setChildContentDate($parent_id, $field_id, $date_id);
				}

				$contentObj->updateContentToTagsLink();

				return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'date_id' => $date_id)));
			}

			if ($field_name == 'ad_key') {
				$field_val = cleanupAdKey($field_val);

				$existingAdWithKey = ConnectContent::findAdContentOfKey(\Auth::User()->station->id, $field_val);
				if ($existingAdWithKey && $existingAdWithKey->id != $field_id) {
					return response()->json(array('code' => 100, 'msg' => 'Duplicate Key Number', 'data' => array('existing_id' => $existingAdWithKey->id, 'pk' => $field_id, 'date_id' => $child_content_date_id)));
				}

				$overwrite_existing = Request::input('overwrite_existing');

				if (empty($overwrite_existing) || $overwrite_existing == '0') {    // should create new ad item

					$clonedContent = $contentObj->copyContent();
					$clonedContent->ad_key = $field_val;
					$clonedContent->save();

					$clonedContent->updateContentToTagsLink();

					$clonedContent->searchAudioFileAndLink();

					$clonedContentDate = null;

					if ($child_content_date_id) {
						$originalContentDate = $contentObj->getContentDate($child_content_date_id);
						if ($originalContentDate) {
							$clonedContentDate = $clonedContent->getContentDateByDateRange($originalContentDate->start_date, $originalContentDate->end_date);
						}
					}

					$clonedContentArray = $clonedContent->toArray();

					if ($clonedContentDate) {
						$start_date = strtotime($clonedContentDate->start_date);
						$clonedContentArray['start_date'] = $start_date === FALSE ? '' : date("d-m-Y", $start_date);

						$end_date = strtotime($clonedContentDate->end_date);
						$clonedContentArray['end_date'] = $end_date === FALSE ? '' : date("d-m-Y", $end_date);

						$clonedContentArray['child_content_date_id'] = $clonedContentDate->id;
					} else {
						$clonedContentArray['start_date'] = '';
						$clonedContentArray['end_date'] = '';
						$clonedContentArray['child_content_date_id'] = '0';
					}

					return response()->json(array('code' => 200, 'msg' => 'Success with Clone', 'data' => array('newObj' => $clonedContentArray, 'pk' => $field_id, 'date_id' => $child_content_date_id )));
				}
			}

			if ($field_name == 'content_rec_type' && $field_val == 'live') {
				$contentObj->audio_enabled = 1;
			}

			if($field_name == 'map_address1') {
				$map_address = $field_val;
				if(!empty($map_address)) {
					$geoInfo = getGEOFromAddress($map_address);
				}
				if (!empty($geoInfo)) {
					$contentObj->map_address1_lat = $geoInfo['lat'];
					$contentObj->map_address1_lng = $geoInfo['lng'];
				} else {
					$contentObj->map_address1_lat = '';
					$contentObj->map_address1_lng = '';
				}
			}
			
			// autocomplete for client name
			if ($field_name == 'who' && $check_client_details) {
				$client = ConnectContentClient::GetConnectContentByTradingName($field_val, $contentObj->station_id);
				if ($client) {  // client information is found, we copy them
					$contentObj->copyContentOfClient($client);
					$contentObj->updateWhoAndWhatForTagsAndEvents();
					return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'date_id' => $child_content_date_id, 'require_reload' => 1)));
				}
				
			}
			

			// autocomplete for talk break
			if (($field_name == 'what' || $field_name == 'who') && $check_talkbreak_suggestion) {
				$autoSuggestId = Request::input("autoSuggestContentId");
				
				if ($field_name == 'what') {
					$suggestedObjByText = ConnectContent::GetTalkBreakByWhat(\Auth::User()->station->id, $field_val);
				} else if ($field_name == 'who') {
					$suggestedObjByText = ConnectContent::GetTalkBreakByWho(\Auth::User()->station->id, $field_val);
				}
				
				$suggestedObjById = ConnectContent::find($autoSuggestId);
				
				$suggestedObj = null;
				
				if ($suggestedObjByText) {
					$suggestedObj = $suggestedObjByText;
					if ($suggestedObjById && ((strcasecmp($suggestedObjById->what, $field_val) == 0 && $field_name == 'what') || (strcasecmp($suggestedObjById->who, $field_val) == 0 && $field_name == 'who'))) {
						$suggestedObj = $suggestedObjById;
					}					
				}
				
				$tagId = Request::input('tagId');
				$tagForContent = Tag::find($tagId);
				
				// found autocomplete suggestion?
				if ($tagForContent) {
					$newContentObj = null;
					if ($suggestedObj) {
						$newContentObj = $suggestedObj->copyContent();
					} else if (!$contentObj->isContentTalkBreak()) {
						$newContentObj = $contentObj->createTalkBreakFromContent();
						if ($field_name == 'what') {
							$newContentObj->what = $field_val;
						} else if ($field_name == 'who') {
							$newContentObj->who = $field_val;
						}
						$newContentObj->save();
					}
					
					if ($newContentObj) {
						$tagForContent->connect_content_id = $newContentObj->id;
						$tagForContent->save();
						$newContentObj->updateWhoAndWhatForTagsAndEvents();
						$newContentObj->sendCompetitonResultGenerationRequest(); // send competition generation request
						return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'require_reload' => 1)));
					}
				}
			}
			
			// autocomplete for talk break - vote
			if ($field_name == 'vote_question'  && $check_talkbreak_suggestion) {
				$autoSuggestId = Request::input("autoSuggestContentId");
		
				$suggestedObjByText = ConnectContent::GetTalkBreakByVoteQuestion(\Auth::User()->station->id, $field_val);
			
				$suggestedObjById = ConnectContent::find($autoSuggestId);
			
				$suggestedObj = null;
			
				if ($suggestedObjByText) {
					$suggestedObj = $suggestedObjByText;
					if ($suggestedObjById && strcasecmp($suggestedObjById->vote_question, $field_val) == 0) {
						$suggestedObj = $suggestedObjById;
					}
				}
			
				$tagId = Request::input('tagId');
				$tagForContent = Tag::find($tagId);
			
				// found autocomplete suggestion?
				if ($tagForContent) {
					$newContentObj = null;
					if ($suggestedObj) {
						$newContentObj = $suggestedObj->copyContent();
					} else if (!$contentObj->isContentTalkBreak()) {
						$newContentObj = $contentObj->createTalkBreakFromContent();
						$newContentObj->vote_question = $field_val;
						$newContentObj->save();
					}
						
					if ($newContentObj) {
						if ($tagForContent->setTagWithVote($newContentObj)) {
							$newContentObj->sendEventUpdateNotificationForContent();
							$newContentObj->sendVoteResultGenerationRequest(); // send vote generation request
						}
						return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'require_reload' => 1)));
					}
				}
			}

			
			$contentObj->$field_name = $field_val;

			//Updating actions
			if($field_name == 'action_params') {
				$action_types = array();

				$actions = \App\ConnectContentAction::orderBy('id', 'desc')->get();

				foreach($actions as $action) {
					$action_types[$action['id']] = $action['action_type'] ;
				}
				if($field_val) {
					if ($contentObj->action_id == 0) {
						$contentObj->$field_name = '{"website":"' . $field_val . '"}';
					} else if ($action_types[$contentObj->action_id] == 'website' || $action_types[$contentObj->action_id] == 'get' || $action_types[$contentObj->action_id] == 'call') {
						$contentObj->$field_name = '{"website":"' . $field_val . '"}';
					} else if ($action_types[$contentObj->action_id] == 'phone' || $action_types[$contentObj->action_id] == 'sms') {
						$contentObj->$field_name = '{"phone":"' . $field_val . '"}';
					}
				}
				else {
					$contentObj->$field_name = '';
				}

			}

			$contentObj->save();
			
			// for vote
			if ($field_name == 'vote_question' || $field_name == 'vote_option_1' || $field_name == 'vote_option_2') {
				$contentObj->sendEventUpdateNotificationForContent();
			}
			
			if ($field_name == 'vote_duration_minutes') {
				$tagId = Request::input('tagId');
				$tagForContent = Tag::find($tagId);
				if ($tagForContent) {
					$tagForContent->updateVoteDurationForTag($contentObj);
				}
			}
			

			if ($field_name == 'ad_key') {
				$contentObj->updateContentToTagsLink();
				$contentObj->searchAudioFileAndLink();
			}

			if ($field_name == 'who' || $field_name == 'what') {
				$contentObj->updateWhoAndWhatForTagsAndEvents();
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'date_id' => $child_content_date_id, 'content' => $contentObj)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	public function listGooglePlay() {

		$who = Request::input('who');
		$what = Request::input('what');

		$result = [];

		$coverArtURL = sprintf(\Config::get("app.AirShrCoverArtListGooglePlayInternalURL"), rawurlencode($who), rawurlencode($what));

		$response = \Httpful\Request::get($coverArtURL)->send();

		if ($response->code == 200) {
			$result = json_decode($response->raw_body)->result;
		}

		return response()->json(array('code' => 0, 'songs' => $result));

	}
	
	public function listITunes() {

		$who = Request::input('who');
		$what = Request::input('what');

		$result = [];

		$coverArtURL = sprintf(\Config::get("app.AirShrCoverArtListITunesInternalURL"), rawurlencode($who), rawurlencode($what));

		$response = \Httpful\Request::get($coverArtURL)->send();

		if ($response->code == 200) {
			$result = json_decode($response->raw_body)->result;
		}

		return response()->json(array('code' => 0, 'songs' => $result));

	}

	public function updateMusicData() {
		try {

			$song = Request::input('song');
			$coverartID = Request::input('coverartID');
			$type = Request::input('type');

			$coverart = CoverArt::find($coverartID);

			$result = [];

			if (!empty($song['iTunesCoverArtUrl']))
				$result['coverart_url'] = $song['iTunesCoverArtUrl'];

			if (!empty($song['iTunesUrl']))
				$result['itunes_url'] = $song['iTunesUrl'];

			if (!empty($song['artist']))
				$result['artist'] = $song['artist'];

			if (!empty($song['title']))
				$result['track'] = $song['title'];

            if (!empty($song['iTunesArtist']))
                $result['itunes_artist'] = $song['iTunesArtist'];

            if (!empty($song['iTunesTitle']))
                $result['itunes_title'] = $song['iTunesTitle'];

			if (!empty($song['previewUrl']))
				$result['preview'] = $song['previewUrl'];

			if (!empty($song['googlePlayArtist']))
				$result['google_artist'] = $song['googlePlayArtist'];

			if (!empty($song['googlePlayTitle']))
				$result['google_title'] = $song['googlePlayTitle'];

			if (!empty($song['googlePlayUrl']))
				$result['google_music_url'] = $song['googlePlayUrl'];

			if (!empty($song['googlePlayCoverArtUrl']))
				$result['google_coverart_url'] = $song['googlePlayCoverArtUrl'];

			if (!empty($song['googlePlaySongId']))
				$result['google_music_song_id'] = $song['googlePlaySongId'];


			if ($type == 'itunes') {
				$result['itunes_available'] = 1;
				$result['itunes_ready'] = 1;
			}
			else {
				$result['google_available'] = 1;
				$result['google_ready'] = 1;
			}

			$coverArtLyricsURL = sprintf(\Config::get("app.AirShrCoverArtLyricsInternalURL"), rawurlencode($song['artist']), rawurlencode($song['title']));

			$response = \Httpful\Request::get($coverArtLyricsURL)->send();

			if ($response->code == 200) {
				$lyrics = json_decode($response->raw_body)->result;

				$result['lyrics'] = $lyrics;
			}

			$coverart->update($result);

			$coverart->updateWhoAndWhatForTagsAndEvents();

            //Delete the wrong entry from the asset service
            $coverArtUpdateURL = sprintf(\Config::get("app.AirShrCoverArtUpdateInternalURL"), $coverart->asset_id);

            $response = \Httpful\Request::delete($coverArtLyricsURL)->send();

			return response()->json(array('code' => 0, 'msg' => 'Success'));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	
	public function updateMusic() {
		try {

			$field_name = Request::input('name');
			$field_val = Request::input('value');
			$field_id = Request::input('pk');

			$require_reload = false;

			$coverart = CoverArt::find($field_id);
			
			$coverart->$field_name = $field_val;

			$coverart->save();
			
			$coverart->updateWhoAndWhatForTagsAndEvents();
			
//			if($field_name == 'track' || $field_name == 'artist') {
//				if(empty($field_val)) {
//					$coverart->clearCoverArt(CoverArt::ITUNES_TYPE);
//				}
//				else {
//					$coverart->updateCoverArtInfo($coverart->artist, $coverart->track);
//				}
//				$coverart->updateWhoAndWhatForTagsAndEvents();
//				$require_reload = true;
//			}
//
//			if($field_name == 'google_title' || $field_name == 'google_artist') {
//				if(empty($field_val)) {
//					$coverart->clearCoverArt(CoverArt::GOOGLE_PLAY_TYPE);
//				}
//				else {
//					$coverart->updateCoverArtInfo($coverart->google_artist, $coverart->google_title);
//				}
//				$coverart->updateWhoAndWhatForTagsAndEvents();
//				$require_reload = true;
//			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'coverart' => $coverart, 'require_reload' => $require_reload)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	
	public function updateMusicTag() {
		try {

			$field_name = Request::input('name');
			$field_val = Request::input('value');
			$field_id = Request::input('pk');
			$type = Request::input('type');
			
			$tagId = Request::input('tagId');

			if($type == 'preview') {
				$tag = PreviewTag::find($tagId);
			} else {
				$tag = Tag::find($tagId);
			}

			$tag->$field_name = $field_val;

			$tag->findConnectContentForTag();

			$coverart = $tag->coverart;

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('pk' => $field_id, 'coverart' => $coverart)));
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Copy Connect content
	 */
	public function copyContent() {

		try {

			$content_id = Request::input('content_id');

			if (empty($content_id)) {
				throw new \Exception('Content ID is missing.');
			}

			$content = ConnectContent::findOrFail($content_id);

			$newContent = $content->copyContent();

			if (!$newContent) {
				throw new \Exception('Error while copying the content.');
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $newContent->id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Create another version of material design
	 */
	public function copyWithNewVersion() {
		try {

			$version = Request::input('content_version');
			$content_id = Request::input('content_id');

			if (empty($content_id)) {
				throw new \Exception('Content ID is missing.');
			}

			if (empty($version)) {
				throw new \Exception('Version is missing.');
			}

			$content = ConnectContent::findOrFail($content_id);

			$newContent = $content->copyContentWithOtherVersion($version);

			if (!$newContent) {
				throw new \Exception('Error while copying the content.');
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('contentID' => $newContent->id)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Create temp ad for material design
	 */
	public function createTempAd() {

		try {

			$connectContent = ConnectContent::create([
				'station_id' 		=> \Auth::User()->station->id,
				'content_type_id'	=> ContentType::findContentTypeIDByName('Ad'),
				'connect_user_id'	=> \Auth::User()->id,
				'is_temp' 			=> 1
			]);

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('id' => $connectContent->id, 'ad_rec_type' => '')));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Show details of content client by name
	 */
	public function clientDetailByName() {
		try {

			$name = Request::input('client_name');

			$client = ConnectContentClient::clientExists(\Auth::User()->station->id, $name);

			if (!$client) {
				throw new \Exception('Client not found.');
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $client->getJSONArrayListForClientDetail()));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Show details of content client by trading name
	 */
	public function clientDetailByTradingName() {
		try {

			$name = Request::input('who');

			$client = ConnectContentClient::clientTradingNameExists(\Auth::User()->station->id, $name);

			if (!$client) {
				throw new \Exception('Client not found.');
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $client->getJSONArrayListForClientDetail()));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}
	/**
	 * Show details of content client
	 */
	protected function contentClientDetail($id) {

		try {

			$content = ConnectContentClient::findOrFail($id);

			$contentArray = $content->toArray();

			$contentArray['content_type_id'] = ContentType::GetClientInfoContentTypeID();
			$contentArray['content_product_id'] = $contentArray['product_id'];
			$contentArray['content_client_name'] = $contentArray['client_name'];
			$contentArray['content_product_name'] = $content->clientProduct ? $content->clientProduct->product_name : '';

			$attachmentsArray = array();

			$clientLogo = $content->clientLogo;

			if ($clientLogo) {
				$attachmentsArray[] = array(
					'url'	=>  $clientLogo->saved_path,
					'filename' => $clientLogo->filename,
					'type'		=> $clientLogo->type,
					'content_attachment_id' => $clientLogo->id
				);
			}


			$contentArray['attachments'] = $attachmentsArray;
			$contentArray['action_params'] = array();

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $contentArray));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * show details of ad for Material Instruction
	 */
	public function adDetailForMIRow($id) {

		try {

			$content = ConnectContent::findOrFail($id);

			$start_date = Request::input('start_date');
			$end_date = Request::input('end_date');

			$contentArray = $content->toArray();

			$contentDate = null;

			if (!empty($start_date) && !empty($end_date)) {
				$start_date = parseDateToMySqlFormat($start_date);
				$end_date = parseDateToMySqlFormat($end_date);
				$contentDate =  $content->addContentDateIfNotExist($start_date, $end_date);
			}

			if ($contentDate) {

				$start_date = strtotime($contentDate->start_date);
				$contentArray['start_date'] = $start_date === FALSE ? '' : date("d-m-Y", $start_date);

				$end_date = strtotime($contentDate->end_date);
				$contentArray['end_date'] = $end_date === FALSE ? '' : date("d-m-Y", $end_date);

				$contentArray['child_content_date_id'] = $contentDate->id;
			} else {
				$contentArray['start_date'] = '';
				$contentArray['end_date'] = '';

				$contentArray['child_content_date_id'] = 0;
			}

			/*$contentDate = $content->getContentDate();
			
			if ($contentDate) {
				$start_date = strtotime($contentDate->start_date);
				$contentArray['start_date'] = $start_date === FALSE ? '' : date("d-m-Y", $start_date);
				
				$end_date = strtotime($contentDate->end_date);
				$contentArray['end_date'] = $end_date === FALSE ? '' : date("d-m-Y", $end_date);
				
				$contentArray['child_content_date_id'] = $contentDate->id;
				
			} else {*/
			/*$contentArray['start_date'] = '';
            $contentArray['end_date'] = '';

            $contentArray['child_content_date_id'] = 0;*/
			//}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $contentArray));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Show details of content
	 */
	public function contentDetail($id) {

		try {

			$contentTypeID = Request::input('content_type');

			if (!empty($contentTypeID) && $contentTypeID == ContentType::GetClientInfoContentTypeID()) {
				return $this->contentClientDetail($id);
			}

			$content = ConnectContent::findOrFail($id);

			$contentArray = $content->toArray();

			$contentArray['content_creation_timestamp'] = strtotime($content->created_at);

			$contentArray['content_client_name'] = $content->contentClient ? $content->contentClient->client_name : '';
			$contentArray['content_product_name'] = $content->contentProduct ? $content->contentProduct->product_name : '';

			$start_date = strtotime($contentArray['start_date']);
			$contentArray['start_date'] = $start_date === FALSE ? '' : date("d-m-Y", $start_date);

			$end_date = strtotime($contentArray['end_date']);
			$contentArray['end_date'] = $end_date === FALSE ? '' : date("d-m-Y", $end_date);

			$contentArray['content_weekdays'] = $content->getContentWeekDaysArray();
			$contentArray['content_dates'] = $content->getContentDatesArray();

			$contentArray['content_color'] = ContentType::getContentTypeColor($content->content_type_id);
			$contentArray['action_params'] = empty($contentArray['action_params']) ? array() : json_decode($contentArray['action_params'], true);

			$attachmentsArray = array();

			foreach ($content->attachments as $attachment) {
				$newAttachmentRow = array(
					'url'	=> $attachment->saved_path,
					'filename' => $attachment->filename,
					'type'		=> $attachment->type,
					'content_attachment_id' => $attachment->id
				);

				if($attachment->type != 'audio') {
					$newAttachmentRow['width'] = $attachment->width + 0;
					$newAttachmentRow['height'] = $attachment->height + 0;
				}
				$attachmentsArray[] = $newAttachmentRow;
			}

			$contentArray['attachments'] = $attachmentsArray;

			if ($content->content_type_id == ContentType::findContentTypeIDByName('Material Instruction')) {

				$subContentsArray = array();

				$subContents = $content->getSubContents();

				foreach($subContents as $subContent) {

					$newSubContentArray = $subContent->toArray();

					if (!isset($newSubContentArray['content_sync'])) $newSubContentArray['content_sync'] = 0;
					if (!isset($newSubContentArray['child_content_date_id'])) $newSubContentArray['child_content_date_id'] = 0;

					$start_date = strtotime($newSubContentArray['start_date']);
					$newSubContentArray['start_date'] = $start_date === FALSE ? '' : date("d-m-Y", $start_date);

					$end_date = strtotime($newSubContentArray['end_date']);
					$newSubContentArray['end_date'] = $end_date === FALSE ? '' : date("d-m-Y", $end_date);

					$subContentsArray[] = $newSubContentArray;
				}

				$contentArray['subContents'] = $subContentsArray;

			}

			$contentStation = $content->station;

			if ($contentStation && $contentStation->is_private) {
				$contentArray['tag_id_list'] = implode(", ", $content->tagIDListForContent());
			}

			$data = array('code' => 0, 'msg' => 'Success', 'data' => $contentArray);
			//\Log::info(var_export($data,TRUE));
			return response()->json($data);

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * List Content Audio
	 */
	public function listAudio() {

		try {

			$attachments = ConnectContentAttachment::where('type', '=', 'audio')->where('station_id', '=', \Auth::User()->station->id);
			

			$search_content_sub_type_id = Request::input('search_content_sub_type_id');
			$search_ad_length = Request::input('search_ad_length');
			$search_atb_date = Request::input('search_atb_date');
			$search_line_number = Request::input('search_line_number');
			$search_start_date = Request::input('search_start_date');
			$search_end_date = Request::input('search_end_date');
			$search_ad_key = Request::input('search_ad_key');
			$search_ad_key = cleanupAdKey($search_ad_key);

			$search_manager_user_id = Request::input('search_manager_user_id');
			$search_agency_id = Request::input('search_agency_id');
			$search_content_client = Request::input('search_content_client');
			$search_content_product = Request::input('search_content_product');

			if (!empty($search_content_sub_type_id)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('content_subtype_id', '=', Request::input('search_content_sub_type_id'));
				});
			}

			if (!empty($search_ad_length)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('ad_length', '=', Request::input('search_ad_length'));
				});
			}

			if (!empty($search_atb_date)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('atb_date', '=', Request::input('search_atb_date'));
				});
			}

			if (!empty($search_line_number)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('content_line_number', '=', Request::input('search_line_number'));
				});
			}

			/*if (!empty($search_start_date)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('start_date', '=', parseDateToMySqlFormat(Request::input('search_start_date')));
				});
			}
			
			if (!empty($search_end_date)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('end_date', '=', parseDateToMySqlFormat(Request::input('search_end_date')));
				});
			}*/

			if (!empty($search_start_date)) {
				$attachments = $attachments->whereHas('content.contentDates', function($q) use($search_start_date) {
					$q->where('start_date', '=', parseDateToMySqlFormat($search_start_date));
				});
			}

			if (!empty($search_end_date)) {
				$attachments = $attachments->whereHas('content.contentDates', function($q) use($search_end_date) {
					$q->where('end_date', '=', parseDateToMySqlFormat($search_end_date));
				});
			}

			if (!empty($search_ad_key)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$adKey = Request::input('search_ad_key');
					$adKey = cleanupAdKey($adKey);
					$q->where('ad_key', '=', $adKey);
				});
			}

			if (!empty($search_manager_user_id)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('content_manager_user_id', '=', Request::input('search_manager_user_id'));
				});
			}

			if (!empty($search_agency_id)) {
				$attachments = $attachments->whereHas('content', function($q) {
					$q->where('content_agency_id', '=', Request::input('search_agency_id'));
				});
			}


			if (!empty($search_content_client)) {
				$attachments = $attachments->whereHas('content', function($q){
					$q->whereHas('contentClient', function($q2) {
						$q2->where('client_name', '=', Request::input('search_content_client'));
					});
				});
			}

			if (!empty($search_content_product)) {
				$attachments = $attachments->whereHas('content', function($q){
					$q->whereHas('contentProduct', function($q2) {
						$q2->where('product_name', '=', Request::input('search_content_product'));
					});
				});
			}


			$attachments = $attachments->with('content')->get();

			$resultData = array();

			foreach ($attachments as $attachment) {

				$newRow = array('attachment_id' => $attachment->id, 'filename' => $attachment->filename, 'url' => $attachment->saved_path, 'uploaded' => date("d M H:i", strtotime($attachment->created_at)), 'content' => $attachment->content);

				$resultData[] = $newRow;

			}

			return response()->json(array('data' => $resultData));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('data' => array()));
		}
	}


	/**
	 * List client items for datatables
	 */
	protected function listClients() {

		try {

			$clients = ConnectContentClient::where('station_id', '=', \Auth::User()->station->id);

			$search_manager_user_id = Request::input('search_manager_user_id');
			$search_agency_id = Request::input('search_agency_id');
			$search_content_client = Request::input('search_content_client');
			$search_content_product = Request::input('search_content_product');

			//For searching client trading name & company name by ad who
			$search_content_ad_who = Request::input('search_content_ad_who');

			if (!empty($search_manager_user_id)) {
				$clients = $clients->where('content_manager_user_id', '=', $search_manager_user_id);
			}

			if (!empty($search_agency_id)) {
				$clients = $clients->where('content_agency_id', '=',  $search_agency_id);
			}

			if (!empty($search_content_client)) {
				$clients = $clients->where('client_name', 'LIKE',  "%$search_content_client%");
			}

			if (!empty($search_content_ad_who)) {
				$client_words = explode(' ', $search_content_ad_who);

				//I am doing this query first so that the exact matches come before the fuzzy matches in the results list
				$clients_exact_match = \DB::table('airshr_connect_content_clients')
					->where('station_id', '=', \Auth::User()->station->id)
					->where(function($query) use ($search_content_ad_who) {
						$query->where('client_name', 'LIKE',  "%$search_content_ad_who%")
							->orWhere('who', 'LIKE',  "%$search_content_ad_who%");
					})->get();

				$clients_fuzzy_matches = [];

				$word_count = 0;
				foreach($client_words as $word) {
					$word_lower = strtolower($word);
					//If the
					if(in_array($word_lower, array('the', 'and', 'pty', 'ltd', 'p/l', 'of', 'a', 'in' ))) {
						continue;
					}
					$clients_fuzzy_raw = \DB::table('airshr_connect_content_clients')
						->where('station_id', '=', \Auth::User()->station->id)
						->where(function ($query) use ($word) {
							$query->where('who', 'LIKE', "% $word")
								->orWhere('who', 'LIKE', "$word %")
								->orWhere('who', 'LIKE', "% $word %")
								->orWhere('who', '=', $word)
								->orWhere('client_name', 'LIKE', "% $word")
								->orWhere('client_name', 'LIKE', "$word %")
								->orWhere('client_name', 'LIKE', "% $word %")
								->orWhere('client_name', '=', $word);
						})->get();

					foreach($clients_fuzzy_raw as $client) {
						$clients_fuzzy_matches[] = $client;
					}
				}

				$results = [];
				$clientsArray = array_unique(array_merge($clients_exact_match,$clients_fuzzy_matches), SORT_REGULAR);

				foreach($clientsArray as $client) {
					$results[] = array(
						'id'					=> $client->id,
						'client_name'			=> $client->client_name,
						'trading_name'			=> $client->who
					);
				}

				return response()->json(array('data' => $results));
			}

			if (!empty($search_content_product)) {
				$clients = $clients->whereHas('clientProduct', function($q){
					$q->where('product_name', '=', Request::input('search_content_product'));
				});
			}

			$clients = $clients->with('clientProduct')->get();

			return response()->json(array('data' => ConnectContentClient::getArrayListForClientsTable($clients)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('data' => array()));
		}

	}
	/**
	 * List content items for datatables
	 */
	public function listContent() {

		try {

			$search_content_type_id = Request::input('search_content_type_id');

			if ($search_content_type_id == ContentType::GetClientInfoContentTypeID()) {
				return $this->listClients();
			}

			$contents = ConnectContent::where('station_id', '=', \Auth::User()->station->id)->where('content_type_id', '=', $search_content_type_id)->where('is_temp', '=', 0);

			$search_content_sub_type_id = Request::input('search_content_sub_type_id');
			$search_content_rec_type = Request::input('search_content_rec_type');
			$search_ad_length = Request::input('search_ad_length');
			$search_atb_date = Request::input('search_atb_date');
			$search_line_number = Request::input('search_line_number');
			$search_start_date = Request::input('search_start_date');
			$search_end_date = Request::input('search_end_date');
			$search_ad_key = Request::input('search_ad_key');
			$search_ad_key = cleanupAdKey($search_ad_key);

			$search_manager_user_id = Request::input('search_manager_user_id');
			$search_agency_id = Request::input('search_agency_id');
			$search_content_client = Request::input('search_content_client');
			$search_content_product = Request::input('search_content_product');
			$search_created_date = Request::input('search_created_date');

			$search_content_version = Request::input('search_content_version');

			$search_session_name = Request::input('search_session_name');
			$search_start_time = Request::input('search_start_time');
			$search_end_time = Request::input('search_end_time');
			$search_content_weekdays = Request::input('search_content_weekdays');
			$search_content_who = Request::input('search_content_who');
			$search_content_what = Request::input('search_content_what');

			if ($search_content_type_id == ContentType::GetTalkContentTypeID()) {

				if (!empty($search_session_name)) {
					$contents = $contents->where('session_name', '=', $search_session_name);
				}

				if (!empty($search_start_time)) {
					$contents = $contents->where('start_time', '=', $search_start_time . ':00');
				}

				if (!empty($search_end_time)) {
					$contents = $contents->where('end_time', '=', $search_end_time . ':00');
				}

				if (!empty($search_content_who)) {
					$contents = $contents->where('who', '=', $search_content_who);
				}

				if (!empty($search_content_what)) {
					$contents = $contents->where('what', '=', $search_content_what);
				}

				if (!empty($search_content_weekdays)) {
					$whereRaw = '';
					foreach($search_content_weekdays as $key => $val) {
						$val = $val == 'false' ? false : true;
						if ($val) {
							if ($whereRaw != '') $whereRaw .= ' OR ';
							$whereRaw .= 'content_weekday_' . $key . '=1';
						}
					}
					/*if ($whereRaw == '') {
						for ($i = 0; $i < 7; $i++) {
							if ($whereRaw != '') $whereRaw .= ' AND ';
							$whereRaw .= 'content_weekday_' . $i . '=0';
						}
					}*/

					if ($whereRaw != '') {
						$contents = $contents->whereRaw('(' . $whereRaw . ')');
					}
				}
			}

			if (!empty($search_content_sub_type_id)) {
				$contents = $contents->where('content_subtype_id', '=', $search_content_sub_type_id);
			}


			if (!empty($search_content_rec_type)) {
				$contents = $contents->where('content_rec_type', '=', $search_content_rec_type);
			}

			if (!empty($search_ad_length)) {
				$contents = $contents->where('ad_length', '=', $search_ad_length);
			}

			if (!empty($search_atb_date)) {
				$contents = $contents->where('atb_date', '=', $search_atb_date);
			}

			if (!empty($search_line_number)) {
				$contents = $contents->where('content_line_number', '=', $search_line_number);
			}

			/*if (!empty($search_start_date)) {
				$contents = $contents->where('start_date', '=', parseDateToMySqlFormat($search_start_date));
			}
			
			if (!empty($search_end_date)) {
				$contents = $contents->where('end_date', '=', parseDateToMySqlFormat($search_end_date));
			}*/

			if (!empty($search_start_date)) {
				if ($search_content_type_id == ContentType::GetTalkContentTypeID()) {
					$contents = $contents->where('start_date', '=', parseDateToMySqlFormat($search_start_date));
				} else {
					$contents = $contents->whereHas('contentDates', function($q) use ($search_start_date) {
						$q->where('start_date', '=', parseDateToMySqlFormat($search_start_date));
					});
				}
			}

			if (!empty($search_end_date)) {
				if ($search_content_type_id == ContentType::GetTalkContentTypeID()) {
					$contents = $contents->where('end_date', '=', parseDateToMySqlFormat($search_end_date));
				} else {
					$contents = $contents->whereHas('contentDates', function($q) use ($search_end_date) {
						$q->where('end_date', '=', parseDateToMySqlFormat($search_end_date));
					});
				}
			}


			if (!empty($search_ad_key)) {
				$contents = $contents->where('ad_key', '=', $search_ad_key);
			}

			if (!empty($search_manager_user_id)) {
				$contents = $contents->where('content_manager_user_id', '=', $search_manager_user_id);
			}

			if (!empty($search_agency_id)) {
				$contents = $contents->where('content_agency_id', '=',  $search_agency_id);
			}

			if (!empty($search_content_client)) {
				$contents = $contents->whereHas('contentClient', function($q){
					$q->where('client_name', '=', Request::input('search_content_client'));
				});
			}

			if (!empty($search_content_product)) {
				$contents = $contents->whereHas('contentProduct', function($q){
					$q->where('product_name', '=', Request::input('search_content_product'));
				});
			}

			if (!empty($search_created_date)) {
				$search_created_date = parseDateToMySqlFormat($search_created_date);
				$contents = $contents->where('created_at', '>=', $search_created_date . ' 00:00:00')
					->where('created_at', '<=', $search_created_date . ' 23:59:59');
			}

			if (!empty($search_content_version)) {
				$contents = $contents->where('content_version', '=', $search_content_version);
			}


			if ($search_content_type_id == ContentType::findContentTypeIDByName('Material Instruction')) {
				$contents = $contents->with('contentClient')->with('contentProduct')->get();
			} else if ($search_content_type_id == ContentType::findContentTypeIDByName('Ad')) {
				$contents = $contents->with('contentDates')->get();
			} else {
				$contents = $contents->get();
			}



			$resultData = array();

			foreach ($contents as $content) {

				//if (!empty($content->content_parent_id)) continue;

				$newRow = array();

				/*$newRow['start'] = empty($content->start_date) ? '' : date("d-M", strtotime($content->start_date));
				$newRow['end'] = empty($content->end_date) ? '' : date("d-M", strtotime($content->end_date));*/

				if ($content->contentDates) {
					$startDateHTML = '';
					$endDateHTML = '';
					foreach ($content->contentDates as $content_date) {
						$startDateHTML .= date("d-M", strtotime($content_date->start_date)) . '<br/>';
						$endDateHTML .= date("d-M", strtotime($content_date->end_date)) . '<br/>';
					}
					$newRow['start'] = $startDateHTML;
					$newRow['end'] = $endDateHTML;
				} else {
					$newRow['start'] = '';
					$newRow['end'] = '';
				}


				//$newRow['type'] = empty(ContentType::$CONTENT_TYPES[$content->content_type_id]) ? '' : ContentType::$CONTENT_TYPES[$content->content_type_id];

				$newRow['type'] = (empty(ContentType::$CONTENT_SUB_TYPES[$content->content_type_id]) || empty(ContentType::$CONTENT_SUB_TYPES[$content->content_type_id][$content->content_subtype_id])) ? '' : ContentType::$CONTENT_SUB_TYPES[$content->content_type_id][$content->content_subtype_id];

				$newRow['content_rec_type'] = empty(ConnectContent::$CONTENT_REC_TYPE_LIST[$content->content_rec_type]) ? '' : ConnectContent::$CONTENT_REC_TYPE_LIST[$content->content_rec_type];
				$newRow['who'] = '<div class="twoline-ellipse">' . $content->who . '</div>';
				$newRow['what'] = '<div class="twoline-ellipse">' .$content->what . '</div>';
				$newRow['key'] = $content->ad_key;
				$newRow['duration'] = $content->ad_length;

				$newRow['audio_enabled'] = getEnabledSymbolHTML($content->audio_enabled);
				$newRow['text_enabled'] = getEnabledSymbolHTML($content->text_enabled);
				$newRow['image_enabled'] = getEnabledSymbolHTML($content->image_enabled);
				$newRow['action_enabled'] = getEnabledSymbolHTML($content->action_enabled);
				$newRow['is_ready'] = getCheckEnabledSymbolHTML($content->is_ready, $content->id);

				if ($search_content_type_id == ContentType::findContentTypeIDByName('Material Instruction')) {

					$newRow['client'] = empty($content->contentClient) ? '' : $content->contentClient->client_name;
					$newRow['product'] = empty($content->contentProduct) ? '' : $content->contentProduct->product_name;

					$newRow['atb_date'] = $content->atb_date;
					$newRow['line_number'] = $content->content_line_number;
					$newRow['created'] = date("d-M H:i", strtotime($content->created_at));

					$newRow['version'] = ConnectContent::GetContentVersionString($content->content_version);
				}

				$newRow['start_time'] = $content->start_time;
				$newRow['end_time'] = $content->end_time;

				$resultData[] = $newRow;

			}

			return response()->json(array('data' => $resultData));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('data' => array()));
		}

	}

	/**
	 * Play Audio file
	 */
	public function playAttachment($id) {

		try {

			$attachment = ConnectContentAttachment::findOrFail($id);

			if ($attachment->type != 'audio') throw new \Exception('File is not audio.');

			return view('airshrconnect.playaudio')
				->with('audio', $attachment->saved_path);


		} catch (\Exception $ex) {
			\Log::error($ex);
			return view('airshrconnect.playaudio')
				->with('error', 'Server error.');
		}
	}

	/**
	 * Audio Upload
	 */
	public function audioUpload() {

		try {

			if (!Request::hasFile('file')) throw new \Exception("File is not present.");

			$attachmentType = 'audio';

			$file = Request::file('file');

			if (!$file->isValid()) throw new \Exception("Uploaded file is invalid.");

			$fileExtension = strtolower($file->getClientOriginalExtension());
			$originalName = $file->getClientOriginalName();

			if (!in_array($fileExtension, array('mp3', 'wav'))) throw new \Exception("File type is invalid.");

			$attachmentObj = ConnectContentAttachment::createAttachmentFromFile(\Auth::User()->station->id, $file, $attachmentType, $originalName, $fileExtension, '');

			/*$newFileName = uniqid($attachmentType) . "." . $fileExtension;
			$relativePath = \Config::get('app.ContentUploadsDIR') . $attachmentType . "/";
			$fullPath = public_path($relativePath);
			
			if (!File::isDirectory($fullPath)) File::makeDirectory($fullPath, 0777, true);
			
			$relativeFileName = $relativePath . $newFileName;
				
			$file->move($fullPath, $newFileName);
				
			$original_saved_name = '';
			$original_saved_path = '';
			
			// needs converting?
			if ($fileExtension != 'mp3') {
					
				$convertedAudioFileName = uniqid('converted') . ".mp3";
				$convertedAudioRelativeFileName = $relativePath . $convertedAudioFileName;
					
				$audioConvertProcess = new Process("ffmpeg -i {$fullPath}{$newFileName} -codec:a libmp3lame -qscale:a 2 {$fullPath}{$convertedAudioFileName}");
				try{
					$audioConvertProcess->run();
					if (!$audioConvertProcess->isSuccessful()) {
						throw new \Exception('Convert process was not successful.');
					}
			
					$original_saved_name = $newFileName;
					$original_saved_path = $relativeFileName;
			
					$newFileName = $convertedAudioFileName;
					$relativeFileName = $convertedAudioRelativeFileName;
			
				} catch (\Exception $exx) {
					\Log::error($ex);
					return response()->json(array('code' => -1, 'msg' => 'Audio file can not be converted to mp3 format.'));
				}
			}
			
			$adKey = getCandidateAdKeyFromFileName($originalName);
			
			$attachmentObj = ConnectContentAttachment::create([
					'content_id' => 0,
					'type' => $attachmentType,
					'filename' => $originalName,
					'saved_name' => $newFileName,
					'saved_path' => $relativeFileName,
					'original_saved_name' => $original_saved_name,
					'original_saved_path' => $original_saved_path,
					'candidate_adkey' => $adKey
					]);*/


			$adContent = ConnectContent::findAdContentOfKey(\Auth::User()->station->id, $attachmentObj->candidate_adkey);

			if (!empty($adContent)) {
				$adContent->removeAttachmentAudio();

				// automatic tick of audio enabled
				if (!$adContent->audio_enabled) {
					$adContent->audio_enabled = 1;
					$adContent->save();
				}

				$attachmentObj->content_id = $adContent->id;
				$attachmentObj->save();

				$adContent = $adContent->toArray();
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('attachment_id' => $attachmentObj->id, 'filename' => $originalName, 'url' => $attachmentObj->saved_path, 'content' => $adContent)));


		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * File upload -  To S3 Cloud
	 */
	public function uploadFileToCloud() {

		try {

			if (!Request::hasFile('file')) throw new \Exception("File is not present.");

			$attachmentType = Request::input('attachment_type');

			if (empty($attachmentType)) throw new \Exception("Attachment type is missing.");

			$file = Request::file('file');

			if (!$file->isValid()) throw new \Exception("Uploaded file is invalid.");

			$fileExtension = strtolower($file->getClientOriginalExtension());
			$originalName = $file->getClientOriginalName();

			if ($attachmentType == 'image' || $attachmentType == 'logo') {
				if (!in_array($fileExtension, array('jpg', 'png', 'tiff', 'bmp', 'gif', 'jpeg', 'tif'))) throw new \Exception("File type is invalid.");
			} else if ($attachmentType == 'audio') {
				if (!in_array($fileExtension, array('mp3', 'wav'))) throw new \Exception("File type is invalid.");
			}

			$additionalImageInfoString = Request::input('additionalImageInfo');

			$attachmentObj = ConnectContentAttachment::createAttachmentFromFile(\Auth::User()->station->id, $file, $attachmentType, $originalName, $fileExtension, $additionalImageInfoString);

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('attachment_id' => $attachmentObj->id, 'filename' => $originalName, 'url' => $attachmentObj->saved_path)));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}


	/**
	 * File upload -  To local disk
	 */
	/*public function uploadFile() {
		
		try {

			if (!Request::hasFile('file')) throw new \Exception("File is not present.");
			
			$attachmentType = Request::input('attachment_type');
			
			if (empty($attachmentType)) throw new \Exception("Attachment type is missing.");
			
			$file = Request::file('file');
			
			if (!$file->isValid()) throw new \Exception("Uploaded file is invalid.");

			$fileExtension = strtolower($file->getClientOriginalExtension());
			$originalName = $file->getClientOriginalName();
			
			if ($attachmentType == 'image' || $attachmentType == 'logo') {
				if (!in_array($fileExtension, array('jpg', 'png', 'tiff', 'bmp', 'gif', 'jpeg', 'tif'))) throw new \Exception("File type is invalid.");	
			} else if ($attachmentType == 'audio') {
				if (!in_array($fileExtension, array('mp3', 'wav'))) throw new \Exception("File type is invalid.");
			}
			
			$newFileName = uniqid($attachmentType) . "." . $fileExtension;
			$relativePath = \Config::get('app.ContentUploadsDIR') . $attachmentType . "/";
			$fullPath = public_path($relativePath);
			
			if (!File::isDirectory($fullPath)) File::makeDirectory($fullPath, 0777, true);

			$relativeFileName = $relativePath . $newFileName;
			
			$file->move($fullPath, $newFileName);
			
			$width = 0;
			$height = 0;
			$original_saved_name = '';
			$original_saved_path = '';
			$original_moreinfo = '';
			
			if ($attachmentType == 'image' || $attachmentType == 'logo') {
				$sizeInfo = getimagesize($fullPath . $newFileName);
				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
				
				// crop and save crop information
				$additionalImageInfoString = Request::input('additionalImageInfo');
				
				if (!empty($additionalImageInfoString)) {
					$additionImageInfo = json_decode($additionalImageInfoString, true);
					
					$editorScale = isset($additionImageInfo['editorScale']) ? $additionImageInfo['editorScale'] : 1;
					$imageScale = isset($additionImageInfo['imageScaleFactor']) ? $additionImageInfo['imageScaleFactor'] : 1;
					
					//$totalScale = $editorScale * $imageScale;
					$totalScale = $editorScale;
					
					if ($totalScale == 0) $totalScale = 1;
					
					$cropAreaX = isset($additionImageInfo['cropAreaX']) ? $additionImageInfo['cropAreaX'] / $totalScale : 0;
					$cropAreaY = isset($additionImageInfo['cropAreaY']) ? $additionImageInfo['cropAreaY'] / $totalScale : 0;
					$cropAreaWidth = isset($additionImageInfo['cropAreaWidth']) ? $additionImageInfo['cropAreaWidth'] / $totalScale : $width / $totalScale;
					$cropAreaHeight = isset($additionImageInfo['cropAreaHeight']) ? $additionImageInfo['cropAreaHeight'] / $totalScale : $height / $totalScale;
					
					$zoomScale = isset($additionImageInfo['zoomScale']) ? $additionImageInfo['zoomScale'] : 1;
					
					//$cropAreaX = $cropAreaX * $zoomScale;
					//$cropAreaY = $cropAreaY * $zoomScale;
					//$cropAreaWidth = $cropAreaWidth * $zoomScale;
					//$cropAreaHeight = $cropAreaHeight * $zoomScale;
					
					$croppedImageFileName = uniqid('cropped') . "." . $fileExtension;
					$croppedImageRelativeFileName = $relativePath . $croppedImageFileName;
					
					$simpleImage = new SimpleImage($fullPath . $newFileName);
					
					$simpleImage->resize($simpleImage->get_width() * $zoomScale * $imageScale, $simpleImage->get_height() * $zoomScale * $imageScale);
					
					if ($simpleImage->crop($cropAreaX, $cropAreaY, $cropAreaWidth + $cropAreaX, $cropAreaHeight + $cropAreaY)->save($fullPath . $croppedImageFileName)) {
						
						$original_saved_name = $newFileName;
						$original_saved_path = $relativeFileName;
						$original_moreinfo = $additionalImageInfoString;
						
						$newFileName = $croppedImageFileName;
						$relativeFileName = $croppedImageRelativeFileName;
						
						$width = $cropAreaWidth;
						$height = $cropAreaHeight;
						
					}
				}
				
			} else if ($attachmentType == 'audio') {
				
				// needs converting?
				if ($fileExtension != 'mp3') {
					
					$convertedAudioFileName = uniqid('converted') . ".mp3";
					$convertedAudioRelativeFileName = $relativePath . $convertedAudioFileName;
					
					$audioConvertProcess = new Process("ffmpeg -i {$fullPath}{$newFileName} -codec:a libmp3lame -qscale:a 2 {$fullPath}{$convertedAudioFileName}");
					try{
						$audioConvertProcess->run();
						if (!$audioConvertProcess->isSuccessful()) {
							throw new \Exception('Convert process was not successful.');
						}
						
						$original_saved_name = $newFileName;
						$original_saved_path = $relativeFileName;
						
						$newFileName = $convertedAudioFileName;
						$relativeFileName = $convertedAudioRelativeFileName;
						
					} catch (\Exception $exx) {
						\Log::error($ex);
						return response()->json(array('code' => -1, 'msg' => 'Audio file can not be converted to mp3 format.'));
					}
				}
				
			}
			
			$attachmentObj = ConnectContentAttachment::create([
					'content_id' => 0,
					'type' => $attachmentType,
					'filename' => $originalName,
					'saved_name' => $newFileName,
					'saved_path' => $relativeFileName,
					'width'		=> $width, 
					'height'	=> $height,
					'original_saved_name' => $original_saved_name,
					'original_saved_path' => $original_saved_path,
					'original_moreinfo' => $original_moreinfo,
					'candidate_adkey' => getCandidateAdKeyFromFileName($originalName)
			]);
			
			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('attachment_id' => $attachmentObj->id, 'filename' => $originalName, 'url' => \Config::get('app.AirShrConnectBaseURL') . $relativeFileName)));
			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
		
	}*/


	public function attachmentImageMetaData($id){

		try {

			if (empty($id)) throw new \Exception('Image attachment ID is missing');

			$attachment = ConnectContentAttachment::findOrFail($id);

			if ($attachment->type != 'image' && $attachment->type != 'logo') throw new \Exception('Attachment is not Image');

			$metaInfo = empty($attachment->original_moreinfo) ? array() : json_decode($attachment->original_moreinfo, true);

			return response()->json(array(
				'code'		=> 0,
				'msg'		=> 'Success',
				'data'		=> array(
					'url'		=> $attachment->original_saved_path,
					'meta'		=> $metaInfo
				)
			));

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}


	public function updateImageMetaData() {

		try {

			$attachmentId = Request::input('attachmentId');

			if (empty($attachmentId)) throw new \Exception('Image attachment ID is missing');

			$attachment = ConnectContentAttachment::findOrFail($attachmentId);

			$attachmentType = $attachment->type;

			if ($attachmentType != 'image' && $attachmentType != 'logo') throw new \Exception('Attachment is not Image');

			$additionalImageInfoString = Request::input('additionalImageInfo');

			if (empty($additionalImageInfoString)) throw new \Exception('Image meta data is missing.');

			$fileExtension = substr($attachment->filename, strripos($attachment->filename, "."));

			/*$relativePath = \Config::get('app.ContentUploadsDIR') . $attachmentType . "/";
			$fullPath = public_path($relativePath);
			if (!File::isDirectory($fullPath)) File::makeDirectory($fullPath, 0777, true);*/

			$additionImageInfo = json_decode($additionalImageInfoString, true);


			// copy original image (s3) to local disk
			$tmpFileName = uniqid('tmp_' . $attachmentType) . $fileExtension;
			$tmpRelativePath = \Config::get('app.ContentUploadsDIR') . "tmp/";
			$tmpFullPath = public_path($tmpRelativePath);
			if (!\File::isDirectory($tmpFullPath)) \File::makeDirectory($tmpFullPath, 0777, true);
			if (!copy($attachment->original_saved_path, $tmpFullPath . $tmpFileName)) {
				throw new \Exception("Unable to create temporary image file.");
			}

			$relativePath = \Config::get('app.ContentUploadsS3DIR') . $attachmentType . "/";

			$simpleImage = new SimpleImage($tmpFullPath . $tmpFileName);

			$width = $simpleImage->get_width();
			$height = $simpleImage->get_height();

			$editorScale = isset($additionImageInfo['editorScale']) ? $additionImageInfo['editorScale'] : 1;
			$imageScale = isset($additionImageInfo['imageScaleFactor']) ? $additionImageInfo['imageScaleFactor'] : 1;

			//$totalScale = $editorScale * $imageScale;
			$totalScale = $editorScale;

			if ($totalScale == 0) $totalScale = 1;

			$cropAreaX = isset($additionImageInfo['cropAreaX']) ? $additionImageInfo['cropAreaX'] / $totalScale : 0;
			$cropAreaY = isset($additionImageInfo['cropAreaY']) ? $additionImageInfo['cropAreaY'] / $totalScale : 0;
			$cropAreaWidth = isset($additionImageInfo['cropAreaWidth']) ? $additionImageInfo['cropAreaWidth'] / $totalScale : $width / $totalScale;
			$cropAreaHeight = isset($additionImageInfo['cropAreaHeight']) ? $additionImageInfo['cropAreaHeight'] / $totalScale : $height / $totalScale;

			$zoomScale = isset($additionImageInfo['zoomScale']) ? $additionImageInfo['zoomScale'] : 1;

			/*$cropAreaX = $cropAreaX * $zoomScale;
			 $cropAreaY = $cropAreaY * $zoomScale;
			$cropAreaWidth = $cropAreaWidth * $zoomScale;
			$cropAreaHeight = $cropAreaHeight * $zoomScale;*/

			$croppedImageFileName = uniqid('cropped') . $fileExtension;
			$croppedImageRelativeFileName = $relativePath . $croppedImageFileName;

			$simpleImage->resize($width * $zoomScale * $imageScale, $height * $zoomScale * $imageScale);

			if ($simpleImage->crop($cropAreaX, $cropAreaY, $cropAreaWidth + $cropAreaX, $cropAreaHeight + $cropAreaY)->save($tmpFullPath . $croppedImageFileName)) {

				if (\Storage::disk('s3')->put( $croppedImageRelativeFileName, file_get_contents($tmpFullPath . $croppedImageFileName))) {
					$attachment->original_moreinfo = $additionalImageInfoString;
					$attachment->width = $cropAreaWidth;
					$attachment->height = $cropAreaHeight;
					$attachment->saved_name = $croppedImageFileName;
					$attachment->saved_path = \Config::get('app.ContentS3BaseURL') . $croppedImageRelativeFileName;

					$attachment->save();

					return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('attachment_id' => $attachment->id, 'url' => $attachment->saved_path)));

				} else {

					throw new \Exception("Unable to upload file to S3.");
				}

			} else {
				throw new \Exception('Unable to crop image.');
			}

		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}

	}

	/**
	 * Load Preview Log Tag List
	 */
	public function previewLogData() {

		$previewLogDate = Request::input('dailylog_date');

		if (empty($previewLogDate)) $previewLogDate = date("Y-m-d");
		else $previewLogDate = date("Y-m-d", strtotime($previewLogDate));

		$searchContentTypeId = Request::input('dailylog_content_type');
		$searchContentOnlyMissing = Request::input('dailylog_only_missing');


		try {

			$previewTags = PreviewTag::where('station_id', '=', \Auth::User()->station->id)
				->where('preview_date', '=', $previewLogDate)
				//->leftJoin('airshr_connect_content_attachments', 'adkey', '=', \DB::raw('REPLACE(REPLACE(REPLACE(REPLACE(IF(LOCATE(" CT", LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) > 0, LEFT(LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename))), LOCATE(" CT", LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) - 1), LEFT(filename, LENGTH(filename) - LOCATE(".", REVERSE(filename)))) , " ", ""), "/", ""), "-", ""), "_", "")'))
				->orderBy('airshr_preview_tags.id', 'asc')
				->with('connectContent')
				->with('coverart')
				->with('candidateAudio')
				->get();

			$adID = ContentType::GetAdContentTypeID();
			$promoID = ContentType::GetPromoContentTypeID();
			$talkID = ContentType::GetTalkContentTypeID();
			$newsID = ContentType::GetNewsContentTypeID();
			$musicID = ContentType::GetMusicContentTypeID();
			$otherID = 100;
			$overallID = 0;


			$byCategory = array(
				$adID => array('complete' => 0, 'incomplete' => 0),
				$promoID => array('complete' => 0, 'incomplete' => 0),
				$talkID => array('complete' => 0, 'incomplete' => 0),
				$newsID => array('complete' => 0, 'incomplete' => 0),
				$musicID => array('complete' => 0, 'incomplete' => 0),
				$otherID => array('complete' => 0, 'incomplete' => 0),
				$overallID => array('complete' => 0, 'incomplete' => 0),
			);

			$byCategoryUnique = array(
				$adID => array('complete' => 0, 'incomplete' => 0),
				$promoID => array('complete' => 0, 'incomplete' => 0),
				$musicID => array('complete' => 0, 'incomplete' => 0),
				$overallID  => array('complete' => 0, 'incomplete' => 0)
			);

			$zettaIDs = array();
			$adKeys = array();
			$songTitles = array();

			$resultTags = array();

			foreach ($previewTags as $previewTag) {

				$contentTypeMatch = false;
				$filterMissingTagMatch = false;

				$contentTypeID = $previewTag->content_type_id;

				if ($contentTypeID == $adID || $contentTypeID == $promoID || $contentTypeID == $talkID || $contentTypeID == $newsID || $contentTypeID == $musicID) {

				} else {
					$contentTypeID = $otherID;
				}

				if ($searchContentTypeId == 0) {
					$contentTypeMatch = true;
				} else if ($searchContentTypeId == $contentTypeID) {
					$contentTypeMatch = true;
				}

				if ($previewTag->hasEnoughConnectData()) {
					$byCategory[$contentTypeID]['complete']++;
					$byCategory[0]['complete']++;

					if (!$searchContentOnlyMissing) {
						$filterMissingTagMatch = true;
					}

				} else { //Missing some content data
					$byCategory[$contentTypeID]['incomplete']++;
					$byCategory[0]['incomplete']++;

					//This is to count for unique items
					switch($contentTypeID) {
						case $adID:
							if(!isset($zettaIDs[$previewTag->zettaid])) {
								$zettaIDs[$previewTag->zettaid] = 1;
								$byCategoryUnique[$contentTypeID]['incomplete']++;
								$byCategoryUnique[0]['incomplete']++;
							}
							break;
						case $promoID:
							if(!isset($adKeys[$previewTag->adkey])) {
								$adKeys[$previewTag->adkey] = 1;
								$byCategoryUnique[$contentTypeID]['incomplete']++;
								$byCategoryUnique[0]['incomplete']++;
							}
							break;
						case $musicID:
							if(!isset($songTitles[$previewTag->what])) {
								$songTitles[$previewTag->what] = 1;
								$byCategoryUnique[$contentTypeID]['incomplete']++;
								$byCategoryUnique[0]['incomplete']++;
							}
							break;

					}

					$filterMissingTagMatch = true;
				}

				if ($contentTypeMatch && $filterMissingTagMatch) {
					$resultTags[] = $previewTag;
				}

			}

			$resultArray = array();
			foreach($resultTags as $previewTag) {
				$tmpRow = $previewTag->getJSONArrayForPreviewLogList();
				if (!empty($previewTag->candidateAudio) && !empty($previewTag->candidateAudio->filename)) {
					$tmpRow['filename'] = $previewTag->candidateAudio->filename;
				} else {
					$tmpRow['filename'] = "";
				}

				$resultArray[] = $tmpRow;
			}

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => array('preview_tags' => $resultArray, 'statistics' => $byCategory, 'statistics_unique' => $byCategoryUnique)));


		} catch (\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * Print Connect Content
	 */
	public function printContent($id) {

		try {
			if (empty($id)) {
				throw new \Exception('Content ID is missing.');
			}

			$content = ConnectContent::findOrFail($id);

			if ($content->content_type_id != ContentType::GetMaterialInstructionContentTypeID()) {
				throw new \Exception('Content type is Invalid.');
			}

			$pdf = \App::make('dompdf.wrapper');

			$pdf->loadView('pdf.materialInstruction', array('content' => $content));

			return $pdf->download($content->getPrintFileName());

			//return view('pdf.materialInstruction')->with('content', $content);

		} catch (\Exception $ex){
			\Log::error($ex);
			return response($ex->getMessage(), 500);
		}

	}

	/**
	 * Print Connect Content
	 * Incomplete, just testing out layout
	 */
	public function printTalkRoster($week) {

		try {
			if (empty($week)) {
				throw new \Exception('Week is missing.');
			}
			$start_date = Carbon::parse($week)->startOfWeek();
			$end_date = $start_date->copy()->addDays(6);

			$user = \Auth::User();

			$station_id = $user->station->id;

			$talk_shows = \App\ConnectContent::where('content_type_id', ContentType::GetTalkContentTypeID())
				->where('station_id', $station_id)
				->where('start_date', '<', $end_date)
				->where('end_date', '>', $start_date)
				->get();

			$events = [];

			foreach($talk_shows as $talk_show) {
				$event['id'] = $talk_show['id'];
				$event['title'] = $talk_show['what'];
				$event['start'] = $talk_show['start_time'];
				$event['end'] = $talk_show['end_time'];
				$event['who'] = $talk_show['who'];

				$dow = [];
				if($talk_show['content_weekday_0']) {
					$dow[] = 0;
				}
				if($talk_show['content_weekday_1']) {
					$dow[] = 1;
				}
				if($talk_show['content_weekday_2']) {
					$dow[] = 2;
				}
				if($talk_show['content_weekday_3']) {
					$dow[] = 3;
				}
				if($talk_show['content_weekday_4']) {
					$dow[] = 4;
				}
				if($talk_show['content_weekday_5']) {
					$dow[] = 5;
				}
				if($talk_show['content_weekday_6']) {
					$dow[] = 6;
				}

				$event['dow'] = $dow;
				$event['is_ready'] = $talk_show['is_ready'];
				$event['className'] = $talk_show['is_ready'] ? '' : 'not-ready';
				$range['start'] = $talk_show->start_date;
				$range['end'] = $talk_show->end_date;
				$event['ranges'] = array($range);
				$event['url'] = 'javascript:void(0)';

				$events[] = $event;
			}

			$content = ConnectContent::findOrFail(3623);

			if ($content->content_type_id != ContentType::GetMaterialInstructionContentTypeID()) {
				throw new \Exception('Content type is Invalid.');
			}
//
//			$pdf = \App::make('dompdf.wrapper');
//
//			$pdf->loadView('pdf.talkroster', array('content' => $content));

			return view('pdf.talkroster')->with('content', $content)
				->with('talk_shows', $talk_shows);

//			return $pdf->download($content->getPrintFileName());

			//return view('pdf.materialInstruction')->with('content', $content);

		} catch (\Exception $ex){
			\Log::error($ex);
			return response($ex->getMessage(), 500);
		}

	}

	public function getCompetitionResultContent() {

		try {

			$tagID = Request::input("tag_id");

			if (empty($tagID)) throw new \Exception("Tag ID parameter is missing.");

			$tag = Tag::findOrFail($tagID);
			
			$stationTimeZone = $tag->station->getStationTimezone();
			
			$competition = Competition::where('tag_id', '=', $tagID)->first();

			if (!$competition) {
				throw new \Exception("Competition result is still pending.");
			}


			$userPhoneNumbers = empty($competition->picked_user_phones) ? array() : json_decode($competition->picked_user_phones, true);

			$userPhoneNumberListStr = "";

			$i = 1;
			foreach($userPhoneNumbers as $number) {
				$userPhoneNumberListStr .= "{$i}. {$number} <br/>";
				$i++;
			}

			return view('airshrconnect.competitionresult')
				->with('error', '')
				->with('competitionDateTime', getDateTimeStringInTimezone(getSecondsFromMili($competition->tag_start_timestamp), "H:i:s d.m.Y", $stationTimeZone))
				->with('total_applicants', $competition->event_users_num)
				->with('pick_count', $competition->picked_users_num)
				->with('user_list', $userPhoneNumberListStr);

		} catch (\Exception $ex) {
			\Log::error($ex);
			return view('airshrconnect.competitionresult')
				->with('error', $ex->getMessage());
		}

	}

	/**
	 * AirShr Connect Scheduler Page
	 */
	public function scheduler() {

		$user = \Auth::User();

		$station_id = $user->station->id;
//		$talk_shows = \App\ConnectContent::where('content_type_id', ContentType::GetTalkContentTypeID())
//			->orderBy('start_date')
//			->where('station_id', $station_id)
//			->get();

		return view('airshrconnect.scheduler')
			->with('WebSocketURL', \Config::get('app.WebSocketURL'))
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_talkshow', ContentType::GetTalkShowContentTypeID());
//			->with('talk_shows', $talk_shows);

	}


	/**
	 * AirShr Connect Music Mixes page
	 */
	public function musicMix() {

		return view('airshrconnect.musicmix')
			->with('WebSocketURL', \Config::get('app.WebSocketURL'))
			->with('content_type_list', ContentType::$CONTENT_TYPES)
			->with('content_type_list_for_connect', ContentType::$CONTENT_TYPES_FOR_CONNECT)
			->with('content_type_id_for_musicmix', ContentType::GetMusicMixContentTypeID());
			//->with('content_type_id_for_mixes', ContentType::GetTalkShowContentTypeID());
//			->with('talk_shows', $talk_shows);

	}


	/**
	 * AirShr Connect Scheduler Page
	 */
	public function getMusicMixesJSON() {

		$user = \Auth::User();

		$station_id = $user->station->id;

		$mixes = \App\ConnectContent::where('content_type_id', ContentType::GetMusicMixContentTypeID())
			->where('station_id', $station_id)
			->get();

		$events = [];

		foreach($mixes as $mix) {
			$event['id'] = $mix['id'];
			$event['title'] = $mix['what'];
			$event['start'] = $mix['start_time'];
			//When an event starts before midnight and ends after midnight
			if(strtotime($mix['start_time']) > strtotime($mix['end_time'])) {
				$seconds_after_midnight = strtotime($mix['end_time']) - strtotime('00:00:00');
				$minutes = (($seconds_after_midnight / 60) % 60);
				$hours = $seconds_after_midnight / 3600;
				$hours = 24 + $hours;
				$event['end'] = intval($hours) .':' .intval($minutes).':00';
			} else {
				$event['end'] = $mix['end_time'];
			}
			$event['who'] = $mix['who'];

			$dow = [];
			if($mix['content_weekday_0']) {
				$dow[] = 0;
			}
			if($mix['content_weekday_1']) {
				$dow[] = 1;
			}
			if($mix['content_weekday_2']) {
				$dow[] = 2;
			}
			if($mix['content_weekday_3']) {
				$dow[] = 3;
			}
			if($mix['content_weekday_4']) {
				$dow[] = 4;
			}
			if($mix['content_weekday_5']) {
				$dow[] = 5;
			}
			if($mix['content_weekday_6']) {
				$dow[] = 6;
			}

			$event['dow'] = $dow;
			$event['is_ready'] = $mix['is_ready'];
			$event['is_complete'] = false;
			$event['mix_title'] = $mix['mix_title'];
//			$images = ConnectContentAttachment::where('content_id', '=', $mix['id'])->whereIn('type', ['image', 'video', 'logo'])->first();
//
//			if(count($images) > 0 && !empty($mix['who']) && !empty($mix['what']) && $mix['action_id'] && !empty($mix['action_params']) && $mix['is_ready']) {
//				$event['is_complete'] = true;
//			}
			$event['className'] = $event['is_complete'] ? '' : 'not-ready';
			$range['start'] = $mix->start_date;
			$range['end'] = $mix->end_date;
			$event['ranges'] = array($range);
			$event['url'] = 'javascript:void(0)';
			$events[] = $event;
		}
		return $events;

	}

	/**
	 * Returns talk shows
	 */
	public function musicMixMetadataTitleList() {

		$station_id = \Auth::User()->station->id;
//		$content_type_sweeper = ContentType::findContentTypeIDByName('Sweeper');
		$contents = Tag::where('station_id', '=', $station_id)
			->whereNotNull('what')
			->groupBy('what')
			->get(['what']);

		$list = array();
		foreach($contents as $content) {
			$list[] = $content->what;
		}

		try {

			return response()->json(array('code' => 0, 'msg' => 'Success', 'data' => $list));

		} catch(\Exception $ex) {
			\Log::error($ex);
			return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
		}
	}

	/**
	 * AirShr Connect Scheduler Page
	 */
	public function getTalkShowsJSON() {

		$user = \Auth::User();

		$station_id = $user->station->id;

		$talk_shows = \App\ConnectContent::where('content_type_id', ContentType::GetTalkContentTypeID())
			->where('content_subtype_id', ContentType::GetTalkSubContentTalkShowTypeID())
			->where('station_id', $station_id)
			->get();

		$events = [];

		foreach($talk_shows as $talk_show) {
			$event['id'] = $talk_show['id'];
			$event['title'] = $talk_show['what'];
			$event['start'] = $talk_show['start_time'];
			//When an event starts before midnight and ends after midnight
			if(strtotime($talk_show['start_time']) > strtotime($talk_show['end_time'])) {
				$seconds_after_midnight = strtotime($talk_show['end_time']) - strtotime('00:00:00');
				$minutes = (($seconds_after_midnight / 60) % 60);
				$hours = $seconds_after_midnight / 3600;
				$hours = 24 + $hours;
				$event['end'] = intval($hours) .':' .intval($minutes).':00';
			} else {
				$event['end'] = $talk_show['end_time'];
			}
			$event['who'] = $talk_show['who'];

			$dow = [];
			if($talk_show['content_weekday_0']) {
				$dow[] = 0;
			}
			if($talk_show['content_weekday_1']) {
				$dow[] = 1;
			}
			if($talk_show['content_weekday_2']) {
				$dow[] = 2;
			}
			if($talk_show['content_weekday_3']) {
				$dow[] = 3;
			}
			if($talk_show['content_weekday_4']) {
				$dow[] = 4;
			}
			if($talk_show['content_weekday_5']) {
				$dow[] = 5;
			}
			if($talk_show['content_weekday_6']) {
				$dow[] = 6;
			}

			$event['dow'] = $dow;
			$event['is_ready'] = $talk_show['is_ready'];
			$event['is_complete'] = false;
			$images = ConnectContentAttachment::where('content_id', '=', $talk_show['id'])->whereIn('type', ['image', 'video', 'logo'])->first();

			if(count($images) > 0 && !empty($talk_show['who']) && !empty($talk_show['what']) && $talk_show['action_id'] && !empty($talk_show['action_params']) && $talk_show['is_ready']) {
				$event['is_complete'] = true;
			}
			$event['className'] = $event['is_complete'] ? '' : 'not-ready';
			$range['start'] = $talk_show->start_date;
			$range['end'] = $talk_show->end_date;
			$event['ranges'] = array($range);
			$event['url'] = 'javascript:void(0)';
			$events[] = $event;
		}
		return $events;

	}

	/**
	 * test function
	 */
	public function test(){
		
		/*$awsTime = getAWSTimeFromNTPServer();
		$nowTime = getCurrentMilisecondsTimestamp();
		
		print_r("AWS Time: " . date("Y-m-d H:i:s", getSecondsFromMili($awsTime)) . "<br/>");
		print_r("Server Time: " . date("Y-m-d H:i:s", getSecondsFromMili($nowTime)) . "<br/>");
		
		print_r("Delay: " . ($nowTime - $awsTime));*/
		
		/*$client = SqsClient::factory(array(
				'credentials' => array(
						'key'    => \Config::get('app.AWS_ACCESS_KEY'),
						'secret' => \Config::get('app.AWS_SECRET_KEY')
				),
				'region'  => \Config::get('app.AWS_REGION')
		));
			
		$messageResult = $client->receiveMessage(array(
						'QueueUrl'        => \Config::get('app.MatcherInSQSDebugQueueURL'),
						'WaitTimeSeconds' => 20,
						'AttributeNames'  => ['SentTimestamp']
		));

		$messageList  = $messageResult->getPath('Messages');
		
		if (empty($messageList)) {
			continue;
		}
		
		foreach ($messageList as $message) {

			$messageReceiptHandle = $message['ReceiptHandle'];
			$messageBody = $message['Body'];
			
			print_r($message['Attributes']['SentTimestamp']);
		}*/

		
		/*set_time_limit(0);
		
		$coverarts = CoverArt::where('id', '>', CoverArt::$COVERART_RECORD_START_ID)
								->whereRaw("(google_music_url = '' OR google_coverart_url = '' OR google_music_song_id = '')")
								->get();
		foreach ($coverarts as $coverArt) {
			$coverArt->getGoogleMusicCoverArt(true);
		}*/
		
		/*$coverArt = CoverArt::findOrFail(11182);
		$info = $coverArt->getGoogleMusicCoverArt();
		print_r("Google Music Coverart: " . json_encode($info) . "<br/>");*/
		
		//print_r(getTimezoneOffsetSecondsOfTimezone('Australia/Sydney'));
		//print_r(getDateTimeStringInTimezone(1457037899, "H:i:s", "Australia/Brisbane"));
		//print_r(getCurrentMilisecondsTimestampInTimezone('Australia/Brisbane'));
		
		//\App\AirShrArtisanQueue::QueueArtisanCommand('airshr:updatetageventcount', array('tagid' => 31), 'API_QUEUE');

		//print_r(getAWSTimeFromNTPServer());

		/*$date = new \DateTime('2016-02-18T13:42:35.658055684+11:00');
		print_r($date->getTimestamp() . floor($date->format('u') / 1000));*/

		//print_r(Station::GetNearByStationsList(-34.41867311675289, 150.88623946875));

		/*$attachments = ConnectContentAttachment::all();
		
		foreach ($attachments as $attachment) 
		{
			if ($attachment->type == 'video' && !empty($attachment->extra)) {
		
				$videoInfo = json_decode($attachment->extra, true);
				
				if (isset($videoInfo['vpreview'])) {
					$videoInfo['vpreview'] = convertHttpToHttps($videoInfo['vpreview']);

					$attachment->extra = json_encode($videoInfo);
					$attachment->save();
				}
			}
		}*/

		//echo convertHttpURLtoS3('https://s3-ap-southeast-2.amazonaws.com/airshr-production/record/FB1BB260-4B29-4708-8768-8A4D1AC2C220/1448583400.aiff');

		//$xml = \XmlParser::extract('<api><user followers="5"><id abc="ttt">1</id><email>crynobone@gmail.com</email></user><user followers="6"><id abc="asd">1</id><email>crynobone@gmail.com</email></user></api>');
		/*$user = $xml->parse([
				'id' => ['uses' => 'user.id'],
				'email' => ['uses' => 'user.email'],
				'followers' => ['uses' => 'user::followers'],
				'other' => ['uses' => 'user.id::abc']
				]);*/
		/*$user = $xml->parse([
				'user' => ['uses' => array('id' => ['uses' => 'user.id'])]
				]);
		print_r($user);*/

		//$xmltr = file_get_contents("C:/temp.xml");

		//$xmltr = mb_str_replace('xmlns="ScheduleSchemaGS"', 'xmlns="http://ScheduleSchemaGS.com"', $xmltr);
		//die(strpos($xmltr, 'xmlns="ScheduleSchemaGS"'));

		//$xml = simplexml_load_string($xmltr, 'SimpleXMLElement');
		//$xml->registerXPathNamespace('gs_s', 'http://schemasong.com');

		//$result = $xml->xpath("//Event");

		//$json = json_encode($xml);
		//$array = json_decode($json,TRUE);

		//print_r((string)$xml->Day->Event[0]->ProgramElement->xpath('gs_s:Song/gs_s:Title')[0]['name']);


		//print_r('<br/>');
		//print_r($array['Day']['Event']); 

		/*$station_id = 1;
		
		$prevContentType = apc_fetch("STATION_{$station_id}_TAG_CONTENT_TYPE");
		$prevCartNo = apc_fetch("STATION_{$station_id}_TAG_CART_NUMBER");
		$prevTagId = apc_fetch("STATION_{$station_id}_TAG_ID");
		$prevTagTimestamp = apc_fetch("STATION_{$station_id}_TAG_TIMESTAMP");
		$prevTagFirstPart = apc_fetch("STATION_{$station_id}_TAG_FIRSTPART");
		
		echo "Prev content type: {$prevContentType} <br/>";
		echo "Prev cart no: {$prevCartNo} <br/>";
		echo "Prev tag id: {$prevTagId} <br/>";
		echo "Prev timestamp: {$prevTagTimestamp} <br/>";
		echo "Prev tag first part: {$prevTagFirstPart} <br/>"; */


		//print_r(getVideoURLDetails('https://youtu.be/3Ioih_-jCZs'));

		/*$data = array(
				'competitionDateTime' => date("H:i:s d.m.Y"),
				'total_applicants'	=> 100,
				'pick_count' => 2,
				'user_list' => "1. new <br/> 2. brand"
		);

		\Mail::send('emails.competition', $data, function($message)
		{
			$message->from('connect@airshr.net', 'AirShr Connect')
					->to('dollah.singh.dev@gmail.com', 'Dollah Singh')
					->cc(['wegburu1026@gmail.com', 'johan.yang.dev@gmail.com'])
					->bcc(['webguru1026@gmail.com'])
					->subject("Competition Result");
		}); */


		/*\Mail::raw('Laravel with Mailgun is easy!', function($message)
		{
			$message->to('dollah.singh.dev@gmail.com');
		});*/



		/*$attachments = ConnectContentAttachment::all();
		
		foreach ($attachments as $attachment) {
			$attachment->candidate_adkey = getCandidateAdKeyFromFileName($attachment->filename);
			$attachment->save();
		}*/

		/*$contents = ConnectContent::all();
		
		foreach($contents as $content) {
			$content->searchAudioFileAndLink();
		}*/

		//User::createUserEventFromTag('004999010640000', '244740', "APA91bGu14GXf4ilz2aBmuq2c5bz8_fFM3CZx1bEDr4Bd_TdtquSTnOA9IS7k6W9vouNxvC2ICZ2Su2UVI5hFFnxVzffzWjRYaBYTydFoH26C8MylCPzupmea8Fe918c5J5PHvz4Uk0S", 'Android', false);


		/*$metaString = Request::input('meta');
		
		preg_match("/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\s+([\w\s\/'&\(\)\.\+\[\]:\d]+)\s-\s([\w\s\/'&\(\)\.\+\[\]-]*)\s-\s([\w\s\/'&\(\)\.\+\[\]-]*)/", $metaString, $match);
		if (!is_array($match) || count($match) < 6) {
			preg_match("/(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}:\d{2})\s+([\w\s\/'&\(\)\.\+\[\]:\d]+)-{0,1}([\w\s\/'&\(\)\.\+\[\]]*)-{0,1}([\w\s\/'&\(\)\.\+\[\]-]*)/", $metaString, $match);
			if (!is_array($match) || count($match) < 6) {	
				die("Unknown metadata format.");
				exit();
			}
		}
		
		
		
		echo "Meta: $metaString<br/>";
		echo "Date: {$match[1]}<br/>";
		echo "Time: {$match[2]}<br/>";
		echo "First: {$match[3]}<br/>";
		echo "Second: {$match[4]}<br/>";
		echo "Third: {$match[5]}<br/>";
		
		preg_match("/(MUS|COM|NWS|SEG|SWP|PRO|STP|IDC|VTK|PCN|TMP)\s+(\d{1,2}:\d{1,2}:\d{1,2})\s+(.*)/", $match[3], $firstPartMatch);
			
		if (!is_array($firstPartMatch) || count($firstPartMatch) < 4) {
			die("Unknown metadata format.");
			exit();
		}
			
		$firstPartType = trim($firstPartMatch[1]);
		$firstPartDuration = trim($firstPartMatch[2]);
		$firstPartContent = trim($firstPartMatch[3]);
		
		
		echo "Type: {$firstPartType}<br/>";
		echo "Duration: {$firstPartDuration}<br/>";
		echo "Content: {$firstPartContent}<br/>"; */

		//$tagIds = \Config::get('app.StartupEventTags');

		/*for ($i = 1; $i <= 10000; $i++) {
			Remote::create([
				'model_number' 	=> 'AUWH101ST',	
				'serial_number'	=> sprintf("W15060%05d", $i)
			]);
		}*/

		/*$users = User::all();
		foreach ($users as $user) {
			//$user->user_id = User::generateUserID();
			$user->countrycode = cleanupPhoneNumber($user->countrycode);
			$user->phone_number = cleanupPhoneNumber($user->phone_number);
			$user->save();
		}*/

		//echo User::generateUserID();

		/*echo getCurrentMilisecondsTimestamp() . '<br/>';
		echo getSecondsFromMili(getCurrentMilisecondsTimestamp());*/
		/*echo getCurrentMilisecondsTimestamp();
		echo '<br/>';
		echo microtime(false);*/
		//echo ConnectContent::getConnectContentForIndividualTalk(1, 100);


		/*$contents = ConnectContent::all();
		foreach ($contents as $content) {
			$action_params = $content->action_params;
			if (empty($action_params)) continue;
			try{
				$jsonDecoded = json_decode($action_params, true);
				if (isset($jsonDecoded['website'])) {
					$website = $jsonDecoded['website'];
					if (stripos($website, 'http') === FALSE || stripos($website, 'http') > 0) {
						$website = "http://" . $website;
						$jsonDecoded['website'] = $website;
						$content->action_params = json_encode($jsonDecoded);
						$content->save();
					}
				}
			} catch (\Exception $ex) {}
		}*/

		/*$contents = ConnectContent::all();
		
		foreach ($contents as $content) {
			$content->addContentDate(0, $content->start_date, $content->end_date);
		}
		
		$contents = ConnectContent::all();
		
		foreach ($contents as $content) {
			
			$subContents = $content->getSubContents();
			
			foreach ($subContents as $subContent) {
				$contentDate = $subContent->getContentDate();
				
				if ($contentDate) {
					ConnectContentBelongs::setChildContentDate($content->id, $subContent->id, $contentDate->id);
				}
			}
			
		}*/

		/*$subContents = ConnectContent::find(205)->getSubContents();
		print_r($subContents[0]->toArray());*/

		/*$attachments = ConnectContentAttachment::all();
		
		foreach ($attachments as $attachment) {
			if ($attachment->type == 'video') {
		
				$videoInfo = getVideoURLDetails($attachment->saved_path);
				
				$width = 0;
				$height = 0;
				
				if (isset($videoInfo['width'])) {
					$width = $videoInfo['width'];
					unset($videoInfo['width']);
				}
				
				if (isset($videoInfo['height'])) {
					$height = $videoInfo['height'];
					unset($videoInfo['height']);
				}
				
				$attachment->width = $width;
				$attachment->height = $height;
				$attachment->extra = json_encode($videoInfo);
				$attachment->save();
		
			}
		}*/

		/*$coverarts = CoverArt::whereNull('google_music_url')->get();
		
		foreach ($coverarts as $coverart) {
			try {
				$coverart->getGoogleMusicPlayURL();
			} catch (\Exception $ex) {}
		}*/

		/*$userId = Request::input('user_id');
		$password = Request::input('password');
		
		$user =  ConnectUser::find($userId);
		
		$user->password = bcrypt($password);
		
		$user->save();*/

		/*print_r(ConnectContent::getConnectContentForTagWitCart(1, 1, 1439944973 , 'g15R0773'));
		die();*/

		/*$data = array();
		
		$data['first_name'] = Request::input('first_name');
		$data['last_name'] = Request::input('last_name');
		$data['email'] = Request::input('email');
		$data['username'] = Request::input('username');
		$data['password'] = Request::input('password');
		$data['password'] = bcrypt($data['password']);
		$data['station_id'] = Request::input('station_id');
		$data['user_role'] = Request::input('user_role');
		
		ConnectUser::create($data); */

		/*$attachments = ConnectContentAttachment::all();
		
		foreach ($attachments as $attachment) {
			if ($attachment->type == 'image' || $attachment->type == 'logo') {
				
				if (empty($attachment->original_saved_name) || empty($attachment->original_saved_path)) {
					$attachment->original_saved_name = $attachment->saved_name;
					$attachment->original_saved_path = $attachment->saved_path;
					$attachment->save();
				}
				
			}
		}*/
		/*foreach ($attachments as $attachment) {
			if ($attachment->type == 'image' || $attachment->type == 'logo') {
				$sizeInfo = getimagesize(public_path($attachment->saved_path));
				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
				$attachment->width = $width;
				$attachment->height = $height;
				$attachment->save();
			} else if ($attachment->type == 'video') {
				$videoInfo = getVideoURLDetails($attachment->saved_path);
				
				$width = 0;
				$height = 0;
				
				if (isset($videoInfo['width'])) {
					$width = $videoInfo['width'];
					unset($videoInfo['width']);
				}
				
				if (isset($videoInfo['height'])) {
					$height = $videoInfo['height'];
					unset($videoInfo['height']);
				}
				
				$attachment->width = $width;
				$attachment->height = $height;
				$attachment->extra = json_encode($videoInfo);
				$attachment->save();
			}
		}*/

		//print_r(getimagesize('http://img.youtube.com/vi/rj18UQjPpGA/0.jpg'));

		/*$address = "Bunnings, Warrawong";
		print_r(getGEOFromAddress($address));*/

		/*$contents = ConnectContent::all();
	
		foreach($contents as $content) {
			$map_address = $content->map_address1;
			if (empty($map_address)) continue;
			$geoInfo = getGEOFromAddress($map_address);
			if (!empty($geoInfo)) {
				$content->map_address1_lat = $geoInfo['lat'];
				$content->map_address1_lng = $geoInfo['lng'];
			} else {
				$content->map_address1_lat = '';
				$content->map_address1_lng = '';
			}
			$content->save();
		}*/
	}

}
