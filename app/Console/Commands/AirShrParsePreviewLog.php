<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use App\PreviewLog;
use App\PreviewTag;
use App\Station;
use App\ContentType;
use App\CoverArt;
use App\ConnectContent;
use App\ConnectClientLookup;

use File;

class AirShrParsePreviewLog extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'airshr:parsewollongongpreviewlog';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Parse preview log for wollongong station.';

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
		$parseDate = $this->argument('date');
		$forceReparse = $this->argument('forceReparse');
		
		$forceReparse = ($forceReparse == '1' ? true : false);
		
		if (empty($parseDate)) {
			$now = time();
			$today = date("Y-m-d", $now);
		
			$tomorrowTimestamp = strtotime("+1 day", $now);
			$tomorrow = date("Y-m-d", $tomorrowTimestamp);
				
			$preview_date = $tomorrow;
			$preview_date_timestamp = $tomorrowTimestamp;
			
		} else {
			$parseDateTimestamp = strtotime($parseDate);
			
			if ($parseDateTimestamp === FALSE) {
				throw new \Exception('Date parameter is in invalid format. (yyyy-mm-dd)');
			}
			
			$preview_date_timestamp = $parseDateTimestamp;
			$preview_date = date("Y-m-d", $preview_date_timestamp);
		}
		
		$this->info("Preview log parsing has started for " . $preview_date);
		
		
		foreach (Station::all() as $station) {
		
			if ($station->is_private) continue;		// only for public station
			
			$this->info("Preview log parsing for " . $station->station_abbrev . " has started.");
			
			if ($station->station_name == 'wollongongwave') {
				//$this->parseDailyLogForWollongong($preview_date, $preview_date_timestamp, $station, $forceReparse);
			} else if ($station->station_name == 'nova-1069-brisbane' || $station->station_name == 'nova-969-sydney') {
				$this->parseClientLookup($preview_date, $preview_date_timestamp, $station, $forceReparse );
				$this->parseDailyLogForNova($preview_date, $preview_date_timestamp, $station, $forceReparse);
			}
			
			$this->info("Preview log parsing for " . $station->station_abbrev . " has ended.");
		}
		
		
		
		$this->info("Preview log parsing has ended.");
	}

	/**
	 * Parse daily log for Wollongong
	 */
	private function parseDailyLogForWollongong($preview_date, $preview_date_timestamp, $stationObj, $forceReparse = false) {
		
		$previewLogObj = null;
		
		try {
						
			$station_id = $stationObj->id;
				
			$previewLogFile = "/home/wollongong/preview/" . date("dmy", $preview_date_timestamp) . ".EVT";
			//$previewLogFile = "C:/" . date("dmy", $preview_date_timestamp) . ".EVT";
				
			$this->info("Preview Log file: " . $previewLogFile);
		
			if (!File::exists($previewLogFile)) {
		
				$this->info("Preview log file does not exist. Trying with lower extension.");
		
				$previewLogFile = "/home/wollongong/preview/" . date("dmy", $preview_date_timestamp) . ".evt";
		
				$this->info("Preview Log file: " . $previewLogFile);
		
				if (!File::exists($previewLogFile)) {
					throw new \Exception('Preview log file does not exist.');
				}
			}
				
			$previewLogLastMTime = File::lastModified($previewLogFile);
				
			$previewLogObj = PreviewLog::where('station_id', '=', $stationObj->id)
										->where('preview_date', '=', $preview_date)
										->first();
		
			if ($previewLogObj && $previewLogObj->file_lastmtime >= $previewLogLastMTime && !$forceReparse) {
				throw new \Exception('Latest preview log file has already been processed.');
			}
		
			if (!$previewLogObj) {
				$previewLogObj = new PreviewLog();
				$previewLogObj->station_id = $stationObj->id;
				$previewLogObj->preview_date = $preview_date;
				$previewLogObj->status = 'processing';
				$previewLogObj->file_path = $previewLogFile;
			}
				
			$previewLogObj->file_lastmtime = $previewLogLastMTime;
			$previewLogObj->save();
		
			$this->info("Clear all preview tags for " . $preview_date);
				
			PreviewTag::where('station_id', '=', $stationObj->id)
							->where('preview_date', '=', $preview_date)
							->forceDelete();
				
			$this->info("Clearing done.");
				
			$this->info('Parsing tags one by one...');
				
			$fHandle = fopen($previewLogFile, "r");
				
			if (!$fHandle) throw new \Exception('Unable to open preview file.');
				
			$prevType = '';
			$prevTagTimestamp = 0;
			$prevWho = '';
			$prevWhat = '';
			$prevTagDuration = 0;
			$prevCart = '';
			
			$stationTimeZone = $stationObj->getStationTimezone();
				
			while (($line = fgets($fHandle)) !== false) {
		
				$cells = explode("|", trim($line));
		
				$lineNumber = isset($cells[16]) ? trim($cells[16]) : '';
		
				$this->info("Line number: " . $lineNumber);
		
				$lineType = isset($cells[0]) ? strtoupper(trim($cells[0])) : '';
		
				if (strtoupper($lineType) != 'PLY' && strtoupper($lineType) != 'STP' && strtoupper($lineType) != 'LMS') continue;				// only take care of PLY, STP, and LMS line
		
				$tagTime = 	isset($cells[1]) ? trim($cells[1]) : '';
		
				if (empty($tagTime)) continue;  							// play time is missing, skip
		
				//$tagTimestamp = strtotime($preview_date . ' ' . $tagTime);
				$tagTimestamp = parseDateTimeStringInTimezone($preview_date . ' ' . $tagTime, $stationTimeZone);
				
				if ($tagTimestamp === false) continue;
		
				$tagTimestamp_ms = $tagTimestamp * 1000;
		
				$contentTypeCell = isset($cells[15]) ? strtoupper(trim($cells[15])) : '';
		
				$contentCell = isset($cells[2]) ? $cells[2] : '';
		
				$firstPartContent = substr($contentCell, 0, 30);
				$secondPart = substr($contentCell, 30);
		
				if (!$secondPart) $secondPart = '';
		
				$firstPartContent = trim($firstPartContent);
				$secondPart = trim($secondPart);
		
				$contentType = '';
				$who = '';
				$what = '';
				$original_who = '';
				$original_what = '';
				$adkey = '';
				$connectContentId = 0;
				$coverartId = 0;
				$tagDuration = 0;
		
				$durationCell = isset($cells[3]) ? trim($cells[3]) : '';
				$tagDuration = parseTagDurationString($durationCell);
		
				$cart = isset($cells[5]) ? trim($cells[5]) : '';
		
				if ($lineType == 'LMS') {					// Comment line
						
					$what = $firstPartContent;
					$original_what = $what;
					$contentType = 'Comment';
						
						
				} else if ($lineType == 'STP') {   			// STOPPED line
						
					if ($prevType == 'INT-WTHR' || $prevType == 'INT-NEWS' || $prevType == 'INT-TRAFFIC') {		// if previous segment is one of them, include this stop to prev segment
		
						$tagTimestamp = $prevTagTimestamp == 0 ? $tagTimestamp : $prevTagTimestamp;
						$tagTimestamp_ms = $tagTimestamp * 1000;
		
						$who = $prevWho;
						$what = $prevWhat;
						$original_what = $what;
						$original_who = $who;
						$tagDuration += $prevTagDuration;
						$cart = $prevCart;
		
						$contentType = 'News';
		
						// reset prev type
						$prevType = '';
						$prevTagTimestamp = 0;
						$prevWho = '';
						$prevWhat = '';
						$prevTagDuration = 0;
						$prevCart = '';
					} else if ($prevType == 'BREKKIE TOH') {
		
						$tagTimestamp = $prevTagTimestamp == 0 ? $tagTimestamp : $prevTagTimestamp;
						$tagTimestamp_ms = $tagTimestamp * 1000;
		
						$who = $prevWho;
						$what = $prevWhat;
						$original_what = $what;
						$original_who = $who;
						$tagDuration += $prevTagDuration;
						$cart = $prevCart;
		
						$contentType = 'Talk';
		
						// reset prev type
						$prevType = '';
						$prevTagTimestamp = 0;
						$prevWho = '';
						$prevWhat = '';
						$prevTagDuration = 0;
						$prevCart = '';
		
					} else {				// otherwise, regard it talk
						$contentType = 'Talk';
					}
						
				} else if ($lineType == 'PLY') {
		
		
					if ($contentTypeCell == 'MUS') {				// music
						$contentType = 'Music';
						$what = $firstPartContent;
						$who = $secondPart;
						$original_what = $what;
						$original_who = $who;
						// for music, call cover art web service
						$coverartInfo = CoverArt::getCoverArtInfo($who, $what);
		
						if ($coverartInfo) {
		
							if (!empty($coverartInfo['artist']))
								$who = $coverartInfo['artist'];
		
							if (!empty($coverartInfo['track']))
								$what = $coverartInfo['track'];
		
							$coverartId = $coverartInfo['id'];
						}
		
					} else if ($contentTypeCell == 'COM') {			// Ad
		
						if (strtoupper($firstPartContent) == 'LIVE READ') {		// special exception
								
							$contentType = 'Talk';
							$who = $firstPartContent;
							$original_who = $who;
								
						} else {
		
							$contentType = 'Ad';
							$who = $firstPartContent;
							$original_who = $who;
							$adkey = cleanupAdKey($secondPart);
								
							if (!empty($adkey)) {
								// look for airshr connect content
								$connectContentObj = ConnectContent::getConnectContentForTag($station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $adkey);
								if ($connectContentObj) {
									// replace who and what with connect data
									if (!empty($connectContentObj->who)) {
										$who = $connectContentObj->who;
									}
									if (!empty($connectContentObj->what)) {
										$what = $connectContentObj->what;
									}
										
									$connectContentId = $connectContentObj->id;
								}
							}
		
						}
					} else if ($contentTypeCell == 'VTK') {		// talk
		
						$contentType = 'Talk';
						$what = $firstPartContent;
						$who = $secondPart;
						$original_what = $what;
						$original_who = $who;
		
					} else if ($contentTypeCell == 'AUD') {		// normal audio - parse cart
		
						$cartUpper = strtoupper($cart);
		
						if (strpos($cartUpper, 'PR-') === 0) {		// Promotion
								
							$contentType = 'Promotion';
							$who = $firstPartContent;
							$original_who = $who;
							$adkey = cleanupAdKey($secondPart);
							$cartToCompare = cleanupAdKey($cart);
								
							if (!empty($cartToCompare)) {
								// look for airshr connect content
								$connectContentObj = ConnectContent::getConnectContentForTagWitCart($station_id, ContentType::findContentTypeIDByName('Ad'), $tagTimestamp, $cartToCompare);
								if ($connectContentObj) {
									// replace who and what with connect data
									if (!empty($connectContentObj->who)) {
										$who = $connectContentObj->who;
									}
									if (!empty($connectContentObj->what)) {
										$what = $connectContentObj->what;
									}
										
									$connectContentId = $connectContentObj->id;
								}
							}
		
						} else if (strpos($cartUpper, 'SW-') === 0 || strpos($cartUpper, 'JJ-') === 0 || strpos($cartUpper, 'TOH-') === 0 || strpos($cartUpper, 'TAG-') === 0) {	 //starting with SW, JJ, TOH, or TAG, then sweeper
								
							if (strtoupper($firstPartContent) == 'BREKKIE TOH') {
								$prevType = 'BREKKIE TOH';
								$prevTagDuration = $tagDuration;
								$prevTagTimestamp = $tagTimestamp;
								$prevWho = $secondPart;
								$prevWhat = $firstPartContent;
								$prevCart = $cart;
								continue;
							} else {
								$contentType = 'Sweeper';
								$what = $firstPartContent;
								$who = $secondPart;
								$original_what = $what;
								$original_who = $who;
							}
								
						} else if  (strpos($cartUpper, 'BBVT-') === 0) {  // starting with BBVT, talk
								
							$contentType = 'Talk';
							$what = $firstPartContent;
							$who = $secondPart;
							$original_what = $what;
							$original_who = $who;
								
						} else if (strpos($cartUpper, 'INT-WTHR') === 0 || strpos($cartUpper, 'INT-NEWS') === 0 || strpos($cartUpper, 'INT-TRAFFIC') === 0) {  // include next stop segment and regard it as news
								
							$prevType = 'INT-NEWS';
							$prevTagDuration = $tagDuration;
							$prevTagTimestamp = $tagTimestamp;
							$prevWho = $secondPart;
							$prevWhat = $firstPartContent;
							$prevCart = $cart;
								
							continue;
						} else {
							continue;
						}
		
					}
		
		
				}
		
				// reset prev type
				$prevType = '';
				$prevTagTimestamp = 0;
				$prevWho = '';
				$prevWhat = '';
				$prevTagDuration = 0;
				$prevCart = '';
		
		
				$this->info(json_encode(array(
						'ContentType' => $contentType,
						//'Tag Timestamp' => $tagTimestamp . ": " . date("Y-m-d H:i:s", $tagTimestamp),
						'Tag Timestamp' => $tagTimestamp . ": " . getDateTimeStringInTimezone($tagTimestamp, "Y-m-d H:i:s", $stationTimeZone),
						'Tag Duration'	=> $tagDuration,
						'Who'	=> $who,
						'What'	=> $what,
						'AdKey'	=> $adkey,
						'Cart' => $cart,
						'Connect content' => $connectContentId,
						'Cover art'	=> $coverartId,
		
				)));

				if ($contentType == 'Talk') {
					
					// look for airshr connect content - talk
					$connectContentObj = ConnectContent::getConnectContentForTalkTag($stationObj->id, $tagTimestamp);
					if ($connectContentObj) {
						// replace who and what with connect data
						if (!empty($connectContentObj->who)) {
							$who = $connectContentObj->who;
						}
						if (!empty($connectContentObj->what)) {
							$what = $connectContentObj->what;
						}
							
						$connectContentId = $connectContentObj->id;
					}
				}
		
				// insert new tag
				try {
					$newTag = PreviewTag::create([
							'station_id'			=> $stationObj->id,
							'content_type_id'		=> ContentType::findContentTypeIDByName($contentType),
							'tag_timestamp'			=> $tagTimestamp_ms,
							'who'					=> $who,
							'what'					=> $what,
							'adkey'					=> $adkey,
							'connect_content_id'	=> $connectContentId,
							'coverart_id'			=> $coverartId,
							'tag_duration'			=> $tagDuration,
							'cart'					=> $cart,
							'preview_date'			=> $preview_date,
							'original_who'			=> $original_who,
							'original_what'			=> $original_what
							]);
					
					// create content if not found
					if (($contentType == 'Ad' || $contentType == 'Promotion') && empty($connectContentId) && !empty($adkey)) {
						$newTag->createAdContentForTag();
					}
					
				} catch (\Exception $exx) {
					$this->error($exx->getMessage());
				}
		
			}
				
			fclose($fHandle);
				
		} catch(\Exception $ex) {
			$this->error($ex);
			/*if ($previewLogObj) {
			 $previewLogObj->status = 'error';
			$previewLogObj->reason = $ex->getMessage();
			$previewLogObj->save();
			}*/
		}
	}
	
	private function parseClientLookup($preview_date, $preview_date_timestamp, $stationObj, $forceReparse = false )
	{
		switch ($stationObj->station_name) {
		case 'nova-1069-brisbane':
			$sourceFile = '/home/nova/brisbane/new_spots.csv';
			break;

		case 'nova-969-sydney':
			$sourceFile = '/home/nova/sydney/new_spots.csv';
			break;

		default:
			throw new \Exception("Unknown station: '{$stationObj->station_name}'");
		}

		$handle = fopen($sourceFile, "r");

		if ($handle === FALSE) {
			throw new \Exception("Unable to open file: '{$sourceFile}' for parsing.");
		}

		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			$thirdpartyId = isset($data[0]) ? trim( $data[0] ) : '';
			$title        = isset($data[1]) ? trim( $data[1] ) : '';
			$sponsor      = isset($data[2]) ? trim( $data[2] ) : '';
			$product      = isset($data[3]) ? trim( $data[3] ) : '';

			// The $thirdpartyId should:
			//  a) not be empty.
			//  b) not just be just alphabetic.
			//  c) be alpha-numeric or just numeric.
			//  d) not be longer than 10 characters.
			//
			if (empty($thirdpartyId) || preg_match('/^[a-zA-Z]+$/', $thirdpartyId) || !preg_match('/^[a-zA-Z0-9]+$/', $thirdpartyId) || strlen($thirdpartyId) > 10) {
				continue;
			}

			$clientLookup = ConnectClientLookup::getByZettaId($thirdpartyId);

			if (empty($clientLookup)) {
				$sponsor = str_replace('&amp;', '&', $sponsor);
				$product = str_replace('&amp;', '&', $product);

				$data = [
					'zettaid'     => $thirdpartyId,
					'ad_key'      => $title,
					'client_name' => $sponsor,
					'product'     => $product,
				];

				ConnectClientLookup::create($data);
			}
		}

		fclose($handle);
	}
	
	/**
	 * Parse daily log for Nova
	 */
	private function parseDailyLogForNova($preview_date, $preview_date_timestamp, $stationObj, $forceReparse = false) {
		
		$previewLogObj = null;
		
		$isBrisbane = $stationObj->station_name == 'nova-1069-brisbane' ? true : false;
		
		try {
		
			$station_id = $stationObj->id;
			
			if ($isBrisbane) {
				$previewLogFile = "/home/nova/brisbane/dailylog-" . date("Ymd", $preview_date_timestamp) . ".xml";
			} else {
				$previewLogFile = "/home/nova/sydney/dailylog-" . date("Ymd", $preview_date_timestamp) . ".xml";
			}

		
			$this->info("Preview Log file: " . $previewLogFile);
		
			if (!File::exists($previewLogFile)) {
		
				$this->info("Preview log file does not exist. Trying with upper extension.");
				
				if ($isBrisbane) {
					$previewLogFile = "/home/nova/brisbane/dailylog-" . date("Ymd", $preview_date_timestamp) . ".XML";
				} else {
					$previewLogFile = "/home/nova/sydney/dailylog-" . date("Ymd", $preview_date_timestamp) . ".XML";
				}
		
				$this->info("Preview Log file: " . $previewLogFile);
		
				if (!File::exists($previewLogFile)) {
					throw new \Exception('Preview log file does not exist.');
				}
			}
		
			$previewLogLastMTime = File::lastModified($previewLogFile);
		
			$previewLogObj = PreviewLog::where('station_id', '=', $stationObj->id)
								->where('preview_date', '=', $preview_date)
								->first();
		
			if ($previewLogObj && $previewLogObj->file_lastmtime >= $previewLogLastMTime && !$forceReparse) {
				throw new \Exception('Latest preview log file has already been processed.');
			}
		
			$xmlContent = file_get_contents($previewLogFile);
			$xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOWARNING );
			
			if (!$previewLogObj) {
				$previewLogObj = new PreviewLog();
				$previewLogObj->station_id = $stationObj->id;
				$previewLogObj->preview_date = $preview_date;
				$previewLogObj->status = 'processing';
				$previewLogObj->file_path = $previewLogFile;
			}
		
			$previewLogObj->file_lastmtime = $previewLogLastMTime;
			$previewLogObj->save();
		
			$this->info("Clear all preview tags for " . $preview_date);
		
			PreviewTag::where('station_id', '=', $stationObj->id)
						->where('preview_date', '=', $preview_date)
						->forceDelete();
		
			$this->info("Clearing done.");
		
			$this->info('Parsing tags one by one...');
		
						
			$stationTimeZone = $stationObj->getStationTimezone();
			
			foreach ($xml->Day->Event as $event) {
				
				$contentType = '';
				$who = '';
				$what = '';
				$original_who = '';
				$original_what = '';
				$adkey = '';
				$connectContentId = 0;
				$coverartId = 0;
				$tagDuration = 0;
				$cart = '';
				$zettaid = '';
				
				$tagDuration = (int)$event['runtime'][0];
				
				$tagTimestamp = 0;
				$tagTimestamp_ms = 0;
				
				//$tagTimestamp = strtotime($preview_date . ' ' . (string)$event['scheduledTime'][0]);
				$tagTimestamp = parseDateTimeStringInTimezone($preview_date . ' ' . (string)$event['scheduledTime'][0], $stationTimeZone);
				$tagTimestamp_ms = $tagTimestamp * 1000;
				
				$entryType = (string)$event['entryType'][0];

				$contentType = ContentType::getFromEntryType($entryType);

				if (empty($contentType)) {
					continue;
				}

				switch ($contentType) {
					case 'Music':
						try {
							$what = (string)$event->ProgramElement->xpath('gs_s:Song/gs_s:Title')[0]['name'];
							$who  = (string)$event->ProgramElement->xpath('gs_s:Song/gs_s:Artist')[0]['name'];
						} catch (\Exception $ex) {}
						
						$original_what = $what;
						$original_who = $who;
						
						break;

					case 'Sweeper':
						try {
							$what = (string)$event->ProgramElement->xpath('gs_l:Link/gs_l:Title')[0]['name'];
						} catch (\Exception $ex) {}

						$original_what = $what;

						$subContentType = ContentType::getFromWhat($what, $contentType);

						switch ($subContentType) {
							case 'Ad':
							case 'Promotion':
								$adkey = cleanupAdKey($what);
								break;

							case 'Talk':
							case 'News':
							case 'Traffic':
							case 'Sport':
								break;
						}

						$contentType = $subContentType;

						break;

					case 'Ad':
						try {
							$who     = (string)$event->xpath('gs_spot:Spot')[0]['sponsor1'];
							$adkey   = cleanupAdKey((string)$event->xpath('gs_spot:Spot')[0]['title']);
							$zettaid = (string)$event->xpath('gs_spot:Spot')[0]['ID'];
						} catch (\Exception $ex) {}

						$original_who = $who;

						break;

					case 'Talk':
						try {
							$what = (string)$event->ProgramElement->xpath('gs_b:Break')[0]['title'];
						} catch (\Exception $ex) {}
						
						$original_what = $what;

						break;
				}

				/*if ($entryType == 'Song') {
					
					$contentType = 'Music';
					
					try {
						$what = (string)$event->ProgramElement->xpath('gs_s:Song/gs_s:Title')[0]['name'];
						$who = (string)$event->ProgramElement->xpath('gs_s:Song/gs_s:Artist')[0]['name'];
					} catch (\Exception $ex) {}
					
					$original_what = $what;
					$original_who = $who;
					
					// for music, call cover art web service
					//$coverartInfo = CoverArt::getCoverArtInfo($who, $what);
					//
					//if ($coverartInfo) {
					//
					//	if (!empty($coverartInfo['artist']))
					//		$who = $coverartInfo['artist'];
					//
					//	if (!empty($coverartInfo['track']))
					//		$what = $coverartInfo['track'];
					//
					//	$coverartId = $coverartInfo['id'];
					//}					
				} 
				else
				if ($entryType == 'Link' || $entryType == 'SpecificLink') {
					
					$contentType = 'Sweeper';
					
					try {
						$what = (string)$event->ProgramElement->xpath('gs_l:Link/gs_l:Title')[0]['name'];
					} catch (\Exception $ex) {}
					
					$original_what = $what;
					
					$descriptionUpperCase = strtoupper($what);
					
					if (strpos($descriptionUpperCase, "PROMO") === 0 || strpos($descriptionUpperCase, "PRM") === 0) {  // promo starts with PROMO or PRM
							
						$contentType = "Promotion";
						$adkey = cleanupAdKey($what);
						
					} else if (strpos($descriptionUpperCase, "CRE") === 0 || strpos($descriptionUpperCase, "CREDIT") === 0 || strpos($descriptionUpperCase, "SF-COLOUR") === 0 || strpos($descriptionUpperCase, "VH-COLOUR") === 0) {  // Starts with cre or credit - Ad
						
						$contentType = "Ad";
						$adkey = cleanupAdKey($what);
						
					} else if (strpos($descriptionUpperCase, "BED") === 0 || strpos($descriptionUpperCase, "SEG") === 0 || strpos($descriptionUpperCase, "INT") === 0 || strpos($descriptionUpperCase, "VT") === 0 || strpos($descriptionUpperCase, "OOB") === 0 || strpos($descriptionUpperCase, "TOH") === 0 || strpos($descriptionUpperCase, "ELM") === 0 || strpos($descriptionUpperCase, "KTM") === 0 || strpos($descriptionUpperCase, "AKL") === 0 || strpos($descriptionUpperCase, "TWS") === 0) {  // Starts with BED - talk
							
						$contentType = 'Talk';
							
					} else if (strpos($descriptionUpperCase, "NEWS") === 0 ||  strpos($descriptionUpperCase, "BRIS NOVA NEWS") === 0) {  // Starts with news, traffic, sports - news
							
						$contentType = 'News';
						
					} else if (strpos($descriptionUpperCase, "TRAFFIC") === 0 || strpos($descriptionUpperCase, "TRAF") === 0) {
						
						$contentType = 'Traffic';
						
					} else if (strpos($descriptionUpperCase, "SPORTS") === 0) {
						
						$contentType = 'Sport';
					}
					
					
				} else if ($entryType == 'Spot') {
					
					$contentType = 'Ad';
					
					try {
						$who = (string)$event->xpath('gs_spot:Spot')[0]['sponsor1'];
						$adkey = cleanupAdKey((string)$event->xpath('gs_spot:Spot')[0]['title']);
					} catch (\Exception $ex) {}

					$original_who = $who;
					
					
				} else if ($entryType == 'Break') {
					
					$contentType = 'Talk';
					try {
						$what = (string)$event->ProgramElement->xpath('gs_b:Break')[0]['title'];
					} catch (\Exception $ex) {}
					
					$original_what = $what;
					
				} else {
					continue;
				}*/
				
				$this->info(json_encode(array(
						'ContentType' => $contentType,
						//'Tag Timestamp' => $tagTimestamp . ": " . date("Y-m-d H:i:s", $tagTimestamp),
						'Tag Timestamp' => $tagTimestamp . ": " . getDateTimeStringInTimezone($tagTimestamp, "Y-m-d H:i:s", $stationTimeZone),
						'Tag Duration'	=> $tagDuration,
						'Who'	=> $who,
						'What'	=> $what,
						'AdKey'	=> $adkey,
						'Cart' => '',
						'Connect content' => $connectContentId,
						'Cover art'	=> $coverartId,
						'Zetta Id' => $zettaid,
				)));
				
				// Create a new preview tag.
				try {
					$newTag = PreviewTag::create([
						'station_id'         => $stationObj->id,
						'content_type_id'    => ContentType::findContentTypeIDByName($contentType),
						'tag_timestamp'      => $tagTimestamp_ms,
						'who'                => $who,
						'what'               => $what,
						'adkey'              => $adkey,
						'connect_content_id' => $connectContentId,
						'coverart_id'        => $coverartId,
						'tag_duration'       => $tagDuration,
						'cart'               => $cart,
						'preview_date'       => $preview_date,
						'original_who'       => $original_who,
						'original_what'      => $original_what,
						'zettaid'            => $zettaid,
					]);
					
					// Connect the preview tag to existing content if it exists.
					$newTag->findConnectContentForTag();

					// create content if not found
					if (($contentType == 'Ad' || $contentType == 'Promotion')) {
						if (empty($newTag->connect_content_id)) {
							$newTag->createAdContentForTag();
						}

						$newTag->findClientNameForTag();
					}

					if (!empty($newTag->connect_content_id)) {
						ConnectContent::find($newTag->connect_content_id)->save();
					}
					
				} catch (\Exception $exx) {
					$this->error($exx->getMessage());
				}
				
			}
		
		} catch(\Exception $ex2) {
			$this->error($ex2);
			/*if ($previewLogObj) {
			 $previewLogObj->status = 'error';
			$previewLogObj->reason = $ex->getMessage();
			$previewLogObj->save();
			}*/
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
			['date', InputArgument::OPTIONAL, 'Date for preview log parsing'],
			['forceReparse', InputArgument::OPTIONAL, 'True to force reparse']
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
