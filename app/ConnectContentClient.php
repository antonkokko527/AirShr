<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ConnectContentClient extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_clients';
	
	protected $guarded = array();

	
	public static function createOrFindClient($station_id, $client_name) {
		
		try {
			
			$existing = null;
			
			try {
				$existing = ConnectContentClient::where('station_id', '=', $station_id)->where('client_name', '=', $client_name)->firstOrFail();
				
			} catch (\Exception $ex2) {}
			
	
			if (!$existing) {
				$existing = ConnectContentClient::create([
							'station_id' => $station_id,
							'client_name' => $client_name
						]); 
			}
			
			return $existing->id;			
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
	
	
	public static function clientExists($station_id, $client_name) {
		
		try {
				
			$existing = null;
				
			try {
				$existing = ConnectContentClient::where('station_id', '=', $station_id)->where('client_name', '=', $client_name)->firstOrFail();
			} catch (\Exception $ex2) {}
				
		
			if ($existing) {
				return $existing;
			}
				
			return false;
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}
		
	}

	public static function clientTradingNameExists($station_id, $trading_name) {

		try {

			$existing = null;

			try {
				$existing = ConnectContentClient::where('station_id', '=', $station_id)->where('who', '=', $trading_name)->firstOrFail();
			} catch (\Exception $ex2) {}


			if ($existing) {
				return $existing;
			}

			return false;

		} catch (\Exception $ex) {
			\Log::error($ex);
			return false;
		}

	}
	
	public function clientProduct() {
		return $this->hasOne('App\ConnectContentProduct', 'id', 'product_id');
	}
	
	public function clientLogo() {
		return $this->hasOne('App\ConnectContentAttachment', 'id', 'logo_attachment_id');
	}
	
	public static function getArrayListForClientsTable($items) {
		$result = array();
		foreach($items as $client) {
			$result[] = $client->getJSONArrayListForClients();
		}
		return $result;
	}
	
	public function getJSONArrayListForClients() {
		$client_executive = \App\ConnectContentExecutive::find($this->content_manager_user_id);

		return  array(
				'id'					=> $this->id,
				'client_name'			=> $this->client_name,
				'client_executive'		=> $client_executive ? $client_executive->executive_name : '',
				'trading_name'			=> $this->who,
				'product_name'			=> $this->clientProduct ? $this->clientProduct->product_name : '',
				'text_enabled'			=> empty($this->who) ? '0' : '1',
				'logo_enabled'			=> empty($this->logo_attachment_id) ? '0' : '1',
				'image_enabled'			=> empty($this->image_attachment1_id) && empty($this->image_attachment2_id) && empty($this->image_attachment3_id) ? '0' : '1',
				'is_ready'				=> $this->is_ready
		);
		
	}
	
	public function getJSONArrayListForClientDetail() {
		
		$clientLogo = $this->copyClientLogo();
		
		return array(
				'id'					=> $this->id,
				'who'					=> $this->who,
				'client_name'			=> $this->client_name,
				'product_name'			=> $this->clientProduct ? $this->clientProduct->product_name : '',
				'content_contact'		=> $this->content_contact,
				'content_email'			=> $this->content_email,
				'content_phone'			=> $this->content_phone,
				'map_address1'			=> $this->map_address1,
				'content_agency_id'		=> $this->content_agency_id,
				'content_manager_user_id'	=> $this->content_manager_user_id,
				'logo_attachment'		=> !$clientLogo ? null : array(
											'url'	=>  $clientLogo->saved_path,
											'filename' => $clientLogo->filename,
											'type'		=> $clientLogo->type,
											'content_attachment_id' => $clientLogo->id
											)
		);
	}
	
	public function copyClientLogo() {
		
		$clientLogo = $this->clientLogo;
		
		if (!$clientLogo) return null;
		
		return $clientLogo->copyAttachment();
		
	}
	
	public function removeConnectClient() {
		$logo_attachment = $this->clientLogo;
		if ($logo_attachment) {
			$logo_attachment->removeAttachment();
		}
		return $this->delete();
	}
	
	public static function GetConnectContentByTradingName($who, $station_id) {
	
		try {
				
			$client = ConnectContentClient::where('station_id', '=', $station_id)
											->where('who', '=', $who)
											->firstOrFail();
				
			return $client;
				
		} catch (\Exception $ex) {
			return null;
		}
	}
	
	public static function GetConnectContentByWho($who, $station_id) {
		
		try {
			
			$client = ConnectContentClient::where('station_id', '=', $station_id)
										->where('client_name', '=', $who)
										->firstOrFail();
			
			return $client;
			
		} catch (\Exception $ex) {
			return null;
		}
	}
}
