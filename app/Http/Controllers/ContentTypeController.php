<?php namespace App\Http\Controllers;

use Request;
use App\ContentType;

class ContentTypeController extends JSONController {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function index() {
	
		if (env("APP_DEBUG")) {
			\Log::info("Content Type List Request : " . json_encode(Request::all()));
		}
	
		$contentTypes = ContentType::all();
		
		// prepare for output data
		$this->setJSONOutputInfo("data", $contentTypes);
	
		$this->setErrorCode("SUCCESS");
		return $this->sendJSONOutput();
	}
}
