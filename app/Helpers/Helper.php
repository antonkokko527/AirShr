<?php 

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function LogInfoToFile($channel, $logFileDirectory, $logFileName, $message) {
	
	$log = new Logger($channel);
	
	if (!File::exists($logFileDirectory)) {
		if (!File::makeDirectory($logFileDirectory, 0777, true)) {
			return false;
		}
	}
	
	$log->pushHandler(new StreamHandler($logFileDirectory . "/" . $logFileName, Logger::INFO));
	$log->addInfo($message);

	return true;
}

function getFullPhoneNumber($countryCode, $phoneNumber) {
	
	$countryCode = trim($countryCode);
	$phoneNumber = trim($phoneNumber);
	
	$fullPhoneNumber = "+";
	
	$fullPhoneNumber .= $countryCode;
	
	if (substr($phoneNumber, 0, 1) == "0") $phoneNumber = substr($phoneNumber, 1);
	
	$fullPhoneNumber .= $phoneNumber;
	
	return $fullPhoneNumber;
}



function getEnabledSymbolHTML($val) {
	if ($val) {
		return '<span class="check-mark enabled"></span>';
	} else {
		return '<span class="check-mark disabled"></span>';
	}
}

function getCheckEnabledSymbolHTML($val, $id) {
	if ($val) {
		return '<i class="mdi mdi-checkbox-marked-circle enabled" data-pk="' . $id . '"></i>';
	} else {
		return '<i class="mdi mdi-information disabled" data-pk="' . $id . '"></i>';
	}
}


function getTodayStartTimestamp() {
	return strtotime(date("Y-m-d") . " 00:00:00");
}

function getTodayStartTimestampInTimezone($timezone = '') {
	if (empty($timezone)) $timezone = date_default_timezone_get();
	return parseDateTimeStringInTimezone(getCurrentTimeInTimezone("Y-m-d", $timezone) . " 00:00:00", $timezone);
}

function getTodayEndTimestamp() {
	return strtotime(date("Y-m-d") . " 23:59:59");
}

function getTodayEndTimestampInTimezone($timezone = '') {
	if (empty($timezone)) $timezone = date_default_timezone_get();
	return parseDateTimeStringInTimezone(getCurrentTimeInTimezone("Y-m-d", $timezone) . " 23:59:59", $timezone);
}

function getCurrentTimeInTimezone($format, $timezone = '') {
	if (empty($timezone)) $timezone = date_default_timezone_get();
	
	$date = new DateTime("now", new DateTimeZone($timezone) );
	return $date->format($format);
}

function getDateTimeStringInTimezone($timestamp, $format, $timezone = '') {
	if (empty($timezone)) $timezone = date_default_timezone_get();
	
	$date = new DateTime("now", new DateTimeZone($timezone) );
	$date->setTimestamp($timestamp);
	
	return $date->format($format);
}

function parseDateTimeStringInTimezone($datetime, $timezone = '') {
	
	if (empty($timezone)) $timezone = date_default_timezone_get();
	$date = new DateTime($datetime, new DateTimeZone($timezone) );
	
	if (!$date) return 0;
	return $date->getTimestamp();
}

function parseDateToMySqlFormat($val){
	if (empty($val)) return "";
	
	$timestamp = strtotime($val);
	
	if ($timestamp === FALSE) return "";
	
	return date("Y-m-d", $timestamp);
}

function formatDateByParse($format, $dateString) {
	$timestamp = strtotime($dateString);
	if ($timestamp === FALSE) return "";
	return date($format, $timestamp);
}

function parseTagDurationString($string) {
	preg_match("/(\d{1,2}):(\d{1,2}):(\d{1,2})/", $string, $matches);
	if (!is_array($matches) || count($matches) < 4) {
		return 0;
	}
	return $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
}

function cleanupAdKey($key) {
	$key = trim($key);
	
	$key = str_replace(" ", "", $key);
	$key = str_replace("/", "", $key);
	$key = str_replace("-", "", $key);
	$key = str_replace("_", "", $key);
	
	return $key;
}

function buildCallablePhoneNumber($phoneNumber) {
	$phoneNumber = trim($phoneNumber);

	$phoneNumber = str_replace(" ", "", $phoneNumber);
	$phoneNumber = str_replace("(", "", $phoneNumber);
	$phoneNumber = str_replace(")", "", $phoneNumber);
	$phoneNumber = str_replace("-", "", $phoneNumber);
	$phoneNumber = str_replace("_", "", $phoneNumber);

	return $phoneNumber;
}

function cleanupPhoneNumber($phoneNumber) {
	$phoneNumber = trim($phoneNumber);
	
	$phoneNumber = str_replace(" ", "", $phoneNumber);
	$phoneNumber = str_replace("+", "", $phoneNumber);
	$phoneNumber = str_replace("-", "", $phoneNumber);
	$phoneNumber = str_replace("_", "", $phoneNumber);
	
	return $phoneNumber;
}

function getVideoURLDetails($url) {
	
	$resultInfo = array();
	
	$parsed = parse_url($url);
	
	try {
		if (!empty($parsed['host'])) {
			
			if (strpos(strtolower($parsed['host']), 'youtu.be') !== FALSE) {		// youtube shorten link? convert it to full link.
				$url = preg_replace('~^https?://youtu\.be/(.+)$~i', 'http://www.youtube.com/watch?v=$1', $url);
				$parsed = parse_url($url);
			}
			
			if (strpos(strtolower($parsed['host']), 'youtube.com') !== FALSE) {
				$resultInfo['vtype'] = 'youtube';
					
				preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
				
				if (isset($matches[1])) {
					$resultInfo['vid'] = $matches[1];
				}
				
				if (isset($resultInfo['vid'])) {
					$resultInfo['vpreview'] = "https://img.youtube.com/vi/" . $resultInfo['vid'] . "/0.jpg"; 
					
					/*$response = \Httpful\Request::get("http://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json")->send();
					if ($response->code == 200) {
						$result_json = $response->body;
						if (!empty($result_json)) {
							if (isset($result_json->width)) $resultInfo['width'] = $result_json->width;
							if (isset($result_json->height)) $resultInfo['height'] = $result_json->height;
						}
					}*/
					$sizeInfo = getimagesize($resultInfo['vpreview']);
					$resultInfo['width'] = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
					$resultInfo['height'] = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
				}
			} else if (strpos(strtolower($parsed['host']), 'vimeo.com') !== FALSE) {
				$resultInfo['vtype'] = 'vimeo';
				
				preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $url, $matches);
				
				if (isset($matches[5])) {
					$resultInfo['vid'] = $matches[5];
				}
				
				if (isset($resultInfo['vid'])) {
					
					$response = \Httpful\Request::get("http://vimeo.com/api/v2/video/" . $resultInfo['vid'] . ".json")->send();
					if ($response->code == 200) {
						$result_json = $response->body;
						if (!empty($result_json[0])) {
							/*if (isset($result_json[0]->width)) $resultInfo['width'] = $result_json[0]->width;
							if (isset($result_json[0]->height)) $resultInfo['height'] = $result_json[0]->height;*/
							if (isset($result_json[0]->thumbnail_large)) $resultInfo['vpreview'] = convertHttpToHttps($result_json[0]->thumbnail_large);
							
							if (isset($resultInfo['vpreview'])) {
								$sizeInfo = getimagesize($resultInfo['vpreview']);
								$resultInfo['width'] = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
								$resultInfo['height'] = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
							}
						}
					}
				}
			}
				
		}
	} catch (\Exception $ex) {}
	
	return $resultInfo;
}


function getGEOFromAddress($address) {
	
	$Geocoder = new \GoogleMapsGeocoder($address);
	
	$geoInfo = $Geocoder->geocode();
	
	if (empty($geoInfo)) return false;
	
	if (empty($geoInfo['status'])) return false;
	
	if ($geoInfo['status'] != 'OK') return false;
	
	if (empty($geoInfo['results']) || !is_array($geoInfo['results'])) return false;
	
	$info = $geoInfo['results'][0];
	
	if (empty($info['geometry'])) return false;
	
	if (empty($info['geometry']['location'])) return false;
	
	if (empty($info['geometry']['location']['lat']) || empty($info['geometry']['location']['lng'])) return false;
	
	return $info['geometry']['location'];	
}


function getStartTimestampOfDay($date) {
	if (!$date || $date == '' || $date == '0000-00-00') return 0;
	$timestamp = strtotime($date . ' 00:00:00');
	if ($timestamp === FALSE) return 0;
	return $timestamp * 1000;
}

function getEndTimestampOfDay($date) {
	if (!$date || $date == '' || $date == '0000-00-00') return 0;
	$timestamp = strtotime($date . ' 23:59:59');
	if ($timestamp === FALSE) return 0;
	return $timestamp * 1000;
}


function getTimezoneOffsetSeconds() {
	$currentTime = date("Y-m-d H:i:s");
	$currentUnixTime = gmdate("Y-m-d H:i:s");
	return (strtotime($currentTime) - strtotime($currentUnixTime)) + 0;
}

function getTimezoneOffsetSecondsOfTimezone($timezone = '') {
	if (empty($timezone)) $timezone = date_default_timezone_get();
	return getOffsetBetweenTimezones($timezone, "UTC");
}

function getMySQLTimeFormat($time) {
	return date("H:i:s", strtotime($time));
}

function getCurrentMilisecondsTimestamp() {
	return round(microtime(true) * 1000);
}

function getCurrentMilisecondsTimestampInTimezone($timezone = '') {
	return parseDateTimeStringInTimezone("now", $timezone) * 1000;	
}

function getSecondsFromMili($miliseconds) {
	return floor($miliseconds / 1000);
}

function refactorWebsiteURL($website) {
	if (stripos($website, 'http') === FALSE || stripos($website, 'http') > 0) {
		$website = "http://" . $website;
	}
	return $website;
}

function cleanString($string) {
	return preg_replace("/[^A-Za-z0-9 ]/", '', strtolower($string));
}

function refactorActionParams($action_params) {
	try{
		// refactor website url
		$jsonDecoded = json_decode($action_params, true);
		if (isset($jsonDecoded['website'])) {
			$website = $jsonDecoded['website'];
			$jsonDecoded['website'] = refactorWebsiteURL($website);
			$action_params = json_encode($jsonDecoded);
		}
		// refactor phone number
		$jsonDecoded = json_decode($action_params, true);
		if (isset($jsonDecoded['phone'])) {
			$phone = $jsonDecoded['phone'];
			$jsonDecoded['phone'] = buildCallablePhoneNumber($phone);
			$action_params = json_encode($jsonDecoded);
		}
	} catch (\Exception $ex) {}
	return $action_params;
}


function getCandidateAdKeyFromFileName($filename) {
	
	$extPos = strripos($filename, ".");
	
	if ($extPos === FALSE) {
		$adKey = $filename;
	} else {
		$adKey = substr($filename, 0, $extPos);
	}
	
	$cartNoPosition = strripos($adKey, " CT");
		
	if ($cartNoPosition !== FALSE && $cartNoPosition > 0) {
		$adKey = substr($adKey, 0, $cartNoPosition);
	}
		
	$adKey = cleanupAdKey($adKey);
	
	return $adKey;
}

function convertHttpToHttps($url) {

	$httpPos = strpos($url, 'http');
	$httpsPos = strpos($url, 'https');
	
	if ($httpsPos === 0) return $url;
	
	if ($httpPos === 0) {
		$url = 'https' . substr($url, 4);
	}
	
	return $url;
}

function convertHttpURLtoS3($url) {
	
	return str_replace(\Config::get('app.AIRSHR_S3_HTTPS_SCHEME_BASE'), \Config::get('app.AIRSHR_S3_SCHEME_BASE'), convertHttpToHttps($url));
	
}


function getAWSTimeFromNTPServer() {
	
	$nowTime = getCurrentMilisecondsTimestamp();
	
	try {
		$response = \Httpful\Request::get(\Config::get('app.NTPLiteTime_Service_URL') . $nowTime)->send();
		if ($response->code == 200) {
			
			$dateString = trim($response->body);
			
			$strPlusPos = strripos($dateString, "+");
			$strDotPos = strripos($dateString, ".");
			
			$dateString = substr($dateString, 0, $strDotPos) . substr($dateString, $strDotPos, 7) . substr($dateString, $strPlusPos);
			
			$date = new \DateTime($dateString);
			$serverTime = $date->getTimestamp() . floor($date->format('u') / 1000);
			
			$nowTime = $serverTime -  (int)(( getCurrentMilisecondsTimestamp() - $nowTime ) / 2);
			
		}
	} catch (\Exception $ex) {
		
	}
	
	return $nowTime;
}


function getOffsetBetweenTimezones($timezone1, $timezone2, $time = 'now') {
	
	$origin_dtz = new DateTimeZone($timezone1);
	$remote_dtz = new DateTimeZone($timezone2);
	
	$date1 = new DateTime($time, $origin_dtz);
	$date2 = new DateTime($time, $remote_dtz);
	
	$offset = $origin_dtz->getOffset($date1) - $remote_dtz->getOffset($date2);
	return $offset;
}
