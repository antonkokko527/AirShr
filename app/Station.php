<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Station extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_stations';
	
	protected $guarded = array();

	public static $STATION_LIST = array(
			1 	=> array(
					'id'							=> 1,
					'station_name' 					=> 'wollongongwave',
					'station_description'			=> 'Wollongong WaveFM',
					'station_abbrev'				=> 'WaveFM 96.5',
					'station_short'					=> 'WaveFM',
					'station_frequency'				=> '96.5'
				   ),
			7	=> array(
					'id'							=> 7,
					'station_name' 					=> 'AirShr',
					'station_description'			=> 'AirShr Station',
					'station_abbrev'				=> 'AirShr',
					'station_short'					=> 'AirShr',
					'station_frequency'				=> ''
				   ),
			8	=> array(
					'id'							=> 8,
					'station_name' 					=> 'nova-1069-brisbane',
					'station_description'			=> 'Nova Brisbane',
					'station_abbrev'				=> 'Nova 1069',
					'station_short'					=> 'Nova',
					'station_frequency'				=> '106.9'
				   ),
			9	=> array(
					'id'							=> 9,
					'station_name' 					=> 'nova-969-sydney',
					'station_description'			=> 'Nova Sydney',
					'station_abbrev'				=> 'Nova 96.9',
					'station_short'					=> 'Nova',
					'station_frequency'				=> '96.9'
				   ),
			4	=> array(
					'id'							=> 4,
					'station_name' 					=> 'abc-702',
					'station_description'			=> 'ABC 702',
					'station_abbrev'				=> 'ABC702',
					'station_short'					=> 'ABC702',
					'station_frequency'				=> '702'
			),
			6	=> array(
					'id'							=> 6,
					'station_name' 					=> 'kiis-1065',
					'station_description'			=> 'KIIS 1065',
					'station_abbrev'				=> 'KIIS1065',
					'station_short'					=> 'KIIS1065',
					'station_frequency'				=> '1065'
			),
			42	=> array(
					'id'							=> 42,
					'station_name' 					=> '2ue',
					'station_description'			=> '2UE News Talk',
					'station_abbrev'				=> '2UE 954',
					'station_short'					=> '2UE 954',
					'station_frequency'				=> '95.4'
			),
			43	=> array(
					'id'							=> 43,
					'station_name' 					=> '2day-fm-sydney',
					'station_description'			=> '2Day FM Sydney',
					'station_abbrev'				=> '2Day FM 104.1',
					'station_short'					=> '2Day FM',
					'station_frequency'				=> '104.1'
			)
	);
	
	public static $DEFAULT_MATCH_STATION_ID = 8;
	
	public static function getStationInfoById($station_id) {
		if (empty($station_id)) return array();
		if (isset(Station::$STATION_LIST[$station_id])) {
			return Station::$STATION_LIST[$station_id];
		}
		return array();
	}
	
	public static function getStationInfoByName($station_name) {
		if (empty($station_name)) return array();
		foreach(Station::$STATION_LIST as $id => $row) {
			if ($row['station_name'] == $station_name) {
				return $row;
			}
		}
		return array();
	}
	
	public static function getStationNameById($station_id) {
		$stationInfo = Station::getStationInfoById($station_id);
		if (empty($stationInfo)) return '';
		return $stationInfo['station_name'];
	}
	
	public static function getStationAbbrevById($station_id) {
		$stationInfo = Station::getStationInfoById($station_id);
		if (empty($stationInfo)) return '';
		return $stationInfo['station_abbrev'];
	}
	
	
	public static function getStationObjectById($station_id) {
		$stationInfo = Station::getStationInfoById($station_id);
		if (empty($stationInfo)) return false;
		$returnObj = new Station($stationInfo);	
		return $returnObj;
	}
	
	public static function getStationObjectByName($station_name) {
		$stationInfo = Station::getStationInfoByName($station_name);
		if (empty($stationInfo)) return false;
		$returnObj = new Station($stationInfo);
		return $returnObj;
	}
	
	public static function getStationByName($name) {
		
		try{
			$station = Station::where('station_name', '=', $name)->firstOrFail();
			return $station;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
	
	public static function getStationById($id) {
	
		try{
			$station = Station::findOrFail($id);
			return $station;
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
	}
	
	public function stationRegions() {
		return $this->belongsToMany('App\Region', 'airshr_station2regions', 'station_id', 'region_id');
	}
	
	public function getStationFirstRegion() {
		
		$regions = $this->stationRegions;
		
		if ($regions && $regions->count() > 0) {
			return $regions[0];
		}
		
		return null;
	}
	
	public function getStationTimezone() {
		
		$firstRegion = $this->getStationFirstRegion();
		
		if ($firstRegion && !empty($firstRegion->timezone)) return $firstRegion->timezone;
		
		return date_default_timezone_get();
	}
	
	public function getStationFirstRegionName() {
		$firstRegion = $this->getStationFirstRegion();
		if ($firstRegion) return $firstRegion->region;
		return '';
	}
	
	public function getStationCurrentTime($format) {
		$timezone = $this->getStationTimezone();
		return getCurrentTimeInTimezone($format, $timezone);
	}
	
	public function contentClients() {
		return $this->hasMany('App\ConnectContentClient', 'station_id');
	}
	
	public function contentClientsArray() {
		$clientArray = array();
		foreach ($this->contentClients as $client) {
			$clientArray[] = $client->client_name;
		}
		return $clientArray;
	}
	
	public function contentClientTradingNameArray() {
	
		$tradingNames = array();
		try {
			$clients = ConnectContentClient::where('station_id', '=', $this->id)->whereNotNull('who')->where('who', '<>', '')->get();
			foreach($clients as $client) {
				$tradingNames[] = $client->who;
			}
		} catch(\Exception $ex) {
			\Log::error($ex);
		}
		
		return $tradingNames;
	}
	
	public function contentProducts() {
		return $this->hasMany('App\ConnectContentProduct', 'station_id');
	}
	
	public function contentProductsArray() {
		$productArray = array();
		foreach ($this->contentProducts as $product) {
			$productArray[] = $product->product_name;
		}
		return $productArray;
	}
	
	public function stationConnectUsers() {
		return $this->hasMany('App\ConnectUser', 'station_id');
	}
	
	public function contentExecutives() {
		return $this->hasMany('App\ConnectContentExecutive', 'station_id');
	}
	
	public function contentAgencies() {
		return $this->hasMany('App\ConnectContentAgency', 'station_id');
	}
	
	public function getJSONArrayForConnect() {
		return array(
			'id' 			=> $this->id,
			'station_name'	=> $this->station_name,
			'is_private'	=> $this->is_private	
		);
	}
	
	public static function UpdateUserStationVote($userID, $voteStationIdList) {
		
		try{
				
			\DB::table('airshr_station_votes')->where('user_id', $userID)->delete();
			
			$inserts = array();
			
			foreach ($voteStationIdList as $stationID) {
				$inserts[] = ['user_id' => $userID, 'station_id' => $stationID, 'vote' => 1];	
			}
			
			if (count($inserts) > 0) {
				\DB::table('airshr_station_votes')->insert($inserts);
			}
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
		return true;
		
	}
	
	public static function GetNearByStationsList($lat = 0, $lng = 0) {

		try{
			
			$query = Station::where('is_private', '=', '0')
							->join('airshr_station2regions', 'airshr_stations.id', '=', 'airshr_station2regions.station_id')
							->join('airshr_regions', 'airshr_station2regions.region_id', '=', 'airshr_regions.id');
			
			$query->addSelect('airshr_stations.*');
			$query->addSelect(\DB::raw("((ACOS(SIN({$lat} * PI() / 180) * SIN(CAST(center_lat AS DECIMAL(20, 15)) * PI() / 180) + COS({$lat} * PI() / 180) * COS(CAST(center_lat AS DECIMAL(20, 15)) * PI() / 180) * COS(({$lng} - CAST(center_lng AS DECIMAL(20, 15))) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) * 1.60934 as distance_km"));
			$query->addSelect(\DB::raw('airshr_regions.radius as radius'));
			$query->addSelect('airshr_regions.region');
			$query->addSelect('airshr_regions.state');
			
			$query->havingRaw(\DB::raw('distance_km <= radius'));
			
			$stations = $query->orderBy('distance_km', 'asc')->get();
			
			return $stations;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
	}
	
	public static function GetRegionStationArrayByUserLocation($userID, $lat = 0, $lng = 0) {
		
		$result = array();
		
		try {
				
			$stations = self::GetNearByStationsList($lat, $lng);
			$userVotes = self::GetUserStationVoteList($userID);
			
			foreach ($stations as $station) {
				$newRow = $station->getJSONArrayForApp();
				
				$newRow['station_region'] = array(
					'name' => $station->region,
					'state' => $station->state	
				);
				
				if (isset($userVotes[$station->id]) && $userVotes[$station->id] == 1) {
					$newRow['voted'] = '1';
				} else {
					$newRow['voted'] = '0';
				}
				
				$result[] = $newRow;
			}
			
				
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
	}
	
	
	public static function GetUserStationVoteList($userID) {
		
		$result = array();
		
		try {
			
			$votes = \DB::table('airshr_station_votes')->where('user_id', '=', $userID)->get();
			
			foreach ($votes as $vote) {
				$result[$vote->station_id] = $vote->vote;
			}
			
		} catch (\Exception $ex) {
			\Log::error($ex);
		}
		
		return $result;
		
	}

	
	public static function getArrayListForStationList($items) {
		$result = array();
		foreach($items as $station) {
			$result[] = $station->getJSONArrayForApp();
		}
		return $result;
	}
	
	public function getJSONArrayForApp() {
		return array(
			'id'			=> $this->id,
			'station_name'	=> $this->station_name,
			'station_description' => $this->station_description,
			'station_abbrev'	=> $this->station_abbrev,
			'station_short'		=> $this->station_short,
			'station_frequency'	=> $this->station_frequency,
			'station_tagline'	=> $this->station_tagline,
			'station_twitterhandle'	=> $this->station_twitterhandle,
			'airshr_enabled'	=> $this->airshr_enabled == 1 ? '1' : '0',
			'stream_enabled'	=> $this->stream_enabled == 1 ? '1' : '0',
			'stream_url'	=> $this->stream_url,
			'station_homepage'	=> $this->station_homepage,
			'station_band'		=> $this->station_band
		);
	}
}
