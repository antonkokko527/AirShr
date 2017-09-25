<?php namespace App\Http\Controllers;

use Request;
use App\Firmware;

class MainController extends JSONController {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getLatestAppVersionInfo() {
		
		if (env("APP_DEBUG")) {
			\Log::info("App Version Request : " . json_encode(Request::all()));
		}
		
		$device_type = Request::input('device_type');
		if (empty($device_type)) $device_type = 'iOS'; 
			
		try {
				
			$appVersionInfo = \DB::table('airshr_app_versions')->where('app_device_type', '=', $device_type)->orderBy('app_version_num', 'desc')->first();
			
			if (!$appVersionInfo) {
				$this->setErrorCode("UNKNOWN_ERROR");
				return $this->sendJSONOutput();
			}
			
			$versionInfo = array(
				'app_version'		=> $appVersionInfo->app_version,
				'description'		=> $appVersionInfo->description,
				'update_link'		=> $appVersionInfo->update_link	
			);
				
			// prepare for output data
			$this->setJSONOutputInfo("data", $versionInfo);
				
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
				
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("UNKNOWN_ERROR");
			return $this->sendJSONOutput();
		}
		
	}
	
	
	public function getLatestFirmware() {
		
		if (env("APP_DEBUG")) {
			\Log::info("Firmware Version Request : " . json_encode(Request::all()));
		}
		
		$appVersion = Request::input('appVersion');
		
		
		try {
			
			$firmwareInfoArray = Firmware::orderBy('firmware_version_num', 'desc')->get();
			
			$foundFirmware = false;
			
			foreach ($firmwareInfoArray as $firmware) {
				
				if (empty($appVersion)) {
					$foundFirmware = $firmware;
					break;
				} else 	if (!empty($appVersion) && version_compare($appVersion, $firmware->min_app_version) >= 0) {
					$foundFirmware = $firmware;
					break;
				}
			}
			
			if (!$foundFirmware) throw new \Exception("Not found");
			
			
			$foundFirmware->firmware_file_ios = \Config::get('app.AirShrConnectBaseURL') . \Config::get('app.FirmwareUploadsDIR') . $foundFirmware->firmware_file_ios;
			$foundFirmware->firmware_file_android = \Config::get('app.AirShrConnectBaseURL') . \Config::get('app.FirmwareUploadsDIR') . $foundFirmware->firmware_file_android;
			
			// prepare for output data
			$this->setJSONOutputInfo("data", $foundFirmware);
			
			$this->setErrorCode("SUCCESS");
			return $this->sendJSONOutput();
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			$this->setErrorCode("FIRMWARE_INFO_NOT_FOUND");
			return $this->sendJSONOutput();
		}
	}
	
	
	public function getApp() {
		
		$detect = new \Mobile_Detect;
		
		$url = \Config::get('app.AirShrAppStoreURL');
		
		if ($detect->isiOS()) {
			$url = \Config::get('app.AirShrAppStoreURL');
		} else if ($detect->isAndroidOS()) {
			$url = \Config::get('app.AirShrGooglePlayStoreURL');
		}
		
		header("Location: " . $url);
		exit();
	}
}
