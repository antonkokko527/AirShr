<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentType extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_content_types';
	
	protected $fillable = array();

	public static $CONTENT_TYPES = array(
		-1	=> 'Comment',
		1 	=> 'Ad',
		2	=> 'Music',
		3 	=> 'News',
		4	=> 'Talk',
		5   => 'Sweeper',
		6	=> 'Promotion',
		7	=> 'Special',
		8	=> 'Material Instruction',
		9	=> 'Audio',
		10	=> 'Daily Log',
		11  => 'Client Info',
		12  => 'Talk Show',
		13  => 'Talk Break',
		14  => 'Weather',
		15  => 'Traffic',
		16  => 'Sport',
		17  => 'Music Mix'
	);
	
	public static $MUSIC_CONTENT_TYPE_ID = 2;
	
	public static $CONTENT_TYPES_FOR_CONNECT = array(
			1 	=> 'Ad',
			3 	=> 'News',
			4	=> 'Talk',
			13  => 'Talk Break',
			12  => 'Talk Show',
			8	=> 'Material Instruction',
			9	=> 'Audio',
			10	=> 'Daily Log',
			11	=> 'Client Info',
			17  => 'Music Mix'
	);
	
	public static $CONTENT_TYPE_COLORS = array(
		0		=> '#000000',
		1		=> '#50E3C2',
		2		=> '#DD218B',
		3		=> '#F5A623',
		4		=> '#60C3EC',
		5		=> '#000000',
		6		=> '#50E3C2',
		7		=> '#000000',
		14		=> '#F5A623',
		15		=> '#F5A623',
		16		=> '#F5A623',
		17		=> '#DD218B'
	);
	
	public static $CONTENT_TYPE_SPECIAL_COLORS = array(
		'VOTE'			=> '#543DED'	
	);
		
	public static $CONTENT_SUB_TYPES = array(
		1	=> array(1 => 'Direct', 2 => 'Agency', 3 => 'Promo', 4 => 'Generic'),
		8	=> array(1 => 'Direct', 2 => 'Agency', 3 => 'Promo'),
		4	=> array(1 => 'Talk Show', 2 => 'Individual Segment')
	);
	
	public static function getContentTypeColor($content_type_id) {
		if (empty($content_type_id)) return ContentType::$CONTENT_TYPE_COLORS[0]; 
		if (isset(ContentType::$CONTENT_TYPE_COLORS[$content_type_id])) {
			return ContentType::$CONTENT_TYPE_COLORS[$content_type_id];
		}	
		return ContentType::$CONTENT_TYPE_COLORS[0];
	}
	
	public static function getContentSpecialColor($type) {
		if (empty($type)) return ContentType::$CONTENT_TYPE_COLORS[0];
		if (isset(ContentType::$CONTENT_TYPE_SPECIAL_COLORS[$type])) {
			return ContentType::$CONTENT_TYPE_SPECIAL_COLORS[$type];
		}
		return ContentType::$CONTENT_TYPE_COLORS[0];
	}
	
	public static function getContentTypeText($content_type_id) {
		if (empty($content_type_id)) return '';
		if (isset(ContentType::$CONTENT_TYPES[$content_type_id])) {
			return ContentType::$CONTENT_TYPES[$content_type_id];
		}	
		return '';
	}
	
	public static function findContentTypeIDByName($name) {
		
		$id = 0;
		
		foreach (ContentType::$CONTENT_TYPES as $key=>$val) {
			if ($name == $val) {
				$id = $key;
				break;
			}
		}
		
		return $id;
	}
	
	public static function findContentTypeById($id) {
		foreach (ContentType::$CONTENT_TYPES as $key=>$val) {
			if ($key == $id) {
				return $val;
			}
		}
		return "";
	}
	
	public static function findSubContentTypeIDByName($parentID, $name) {
	
		$id = 0;
	
		if (!isset(ContentType::$CONTENT_SUB_TYPES[$parentID])) return 0;
		
		foreach (ContentType::$CONTENT_SUB_TYPES[$parentID] as $key=>$val) {
			if ($name == $val) {
				$id = $key;
				break;
			}
		}
	
		return $id;
	}
	
	
	public static function GetSweeperContentTypeID() {
		return ContentType::findContentTypeIDByName('Sweeper');
	}
	
	public static function GetAdContentTypeID() {
		return ContentType::findContentTypeIDByName('Ad');
	}
	
	public static function GetMusicContentTypeID() {
		return ContentType::findContentTypeIDByName('Music');
	}
	
	public static function GetPromoContentTypeID() {
		return ContentType::findContentTypeIDByName('Promotion');
	}
	
	public static function GetTalkContentTypeID() {
		return ContentType::findContentTypeIDByName('Talk');
	}

	
	public static function GetTalkSubContentTalkShowTypeID() {
		return ContentType::findSubContentTypeIDByName(ContentType::GetTalkContentTypeID(), "Talk Show");
	}

	public static function GetNewsSubContentTalkShowTypeID() {
		return ContentType::findSubContentTypeIDByName(ContentType::GetNewsContentTypeID(), "News");
	}
	
	public static function GetTalkSubContentIndividualSegmentTypeID() {
		return ContentType::findSubContentTypeIDByName(ContentType::GetTalkContentTypeID(), "Individual Segment");
	}
	
	public static function GetNewsContentTypeID() {
		return ContentType::findContentTypeIDByName('News');
	}

	public static function GetWeatherContentTypeID() {
		return ContentType::findContentTypeIDByName('Weather');
	}

	public static function GetTrafficContentTypeID() {
		return ContentType::findContentTypeIDByName('Traffic');
	}

	public static function GetSportContentTypeID() {
		return ContentType::findContentTypeIDByName('Sport');
	}
	
	public static function GetClientInfoContentTypeID() { 
		return ContentType::findContentTypeIDByName('Client Info');
	}
	
	public static function GetMaterialInstructionContentTypeID() {
		return ContentType::findContentTypeIDByName('Material Instruction');
	}
	
	public static function GetTalkShowContentTypeID() {
		return ContentType::findContentTypeIDByName('Talk Show');
	}
	
	public static function GetTalkBreakContentTypeID() {
		return ContentType::findContentTypeIDByName('Talk Break');
	}

	public static function GetMusicMixContentTypeID() {
		return ContentType::findContentTypeIDByName('Music Mix');
	}

	public static function getFromEntryType($entryType) {
		$contentType = null;

		if ($entryType == 'Song') {
			$contentType = 'Music';
		}
		else
		if ($entryType == 'Link' || $entryType == 'SpecificLink') {
			$contentType = 'Sweeper';
		}
		else
		if ($entryType == 'Spot') {
			$contentType = 'Ad';
		}
		else
		if ($entryType == 'Break') {
			$contentType = 'Talk';
		}

		return $contentType;
	}

	public static function getFromWhat($what, $originalContentType = null) {
		$contentType = $originalContentType;

		$description = strtoupper($what);
		
		// promo starts with PROMO or PRM
		if (strpos($description, "PROMO") === 0 || strpos($description, "PRM") === 0) {
			$contentType = 'Promotion';
		}
		else
		// Starts with cre or credit - Ad
		if (/*strpos($description, "CRE") === 0 || strpos($description, "CREDIT") === 0 || */strpos($description, "SF-COLOUR") === 0 || strpos($description, "VH-COLOUR") === 0) {
			$contentType = 'Ad';
		}
		else
		// Starts with BED - talk
		if (strpos($description, "BED") === 0 || strpos($description, "SEG") === 0 || strpos($description, "INT") === 0 || strpos($description, "VT") === 0 || strpos($description, "OOB") === 0 || strpos($description, "TOH") === 0 || strpos($description, "ELM") === 0 || strpos($description, "KTM") === 0 || strpos($description, "AKL") === 0 || strpos($description, "TWS") === 0) {
			$contentType = 'Talk';
		}
		else 
		// Starts with news, traffic, sports - news
		if (strpos($description, "NEWS") === 0 ||  strpos($description, "BRIS NOVA NEWS") === 0) {
			$contentType = 'News';
		}
		else 
		if (strpos($description, "TRAFFIC") === 0 || strpos($description, "TRAF") === 0) {
			$contentType = 'Traffic';
		} 
		else
		if (strpos($description, "SPORTS") === 0) {
			$contentType = 'Sport';
		}

		return $contentType;
	}	
}
