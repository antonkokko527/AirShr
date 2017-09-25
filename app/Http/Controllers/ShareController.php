<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\ConnectUser;
use App\ContentType;
use App\ConnectContent;
use App\ConnectContentAction;
use App\ConnectContentAttachment;
use App\ConnectContentClient;
use App\ConnectContentProduct;
use App\ConnectContentBelongs;
use Symfony\Component\Process\Process;
use Carbon\Carbon;

use App\Tag;
use App\Station;
use App\WebSocketPub;
use App\User;

use Request;
use File;

use abeautifulsite\SimpleImage;
use App\PreviewTag;

use App\CoverArt;
use App\Remote;

use App\Competition;
use GuzzleHttp\json_decode;


class ShareController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the content that a user shared by tagID
     */
    public function showContent($hash) {
    	
    	$tagID = 0;

    	$tag = Tag::where('hash', '=', $hash)->first();

        $stationLogo = '';
        $stationURL  = '';
        $title = '';
        $image = '';
        $url = \Config::get('app.AirShrShareURLBase') . $hash;

    	if (!empty($tag)) {
    		$tagID = $tag->id;
    		//$tag->saveAudioRenderFile();
            $station = Station::find($tag->station_id);
            $stationLogo = $station->station_logo;
            $stationURL = $station->station_homepage;

            switch($tag->content_type_id) {
                case ContentType::GetMusicContentTypeID():
                    $title = "Check out this song: {$tag->what} by {$tag->who}";
                    if($tag->coverart) {
                        $image = $tag->coverart->coverart_url;
                    }
                    break;
                case ContentType::GetAdContentTypeID():
                    $title = "Check out this deal: {$tag->what} from {$tag->who}";
                    break;
                case ContentType::GetTalkContentTypeID():
                    $title = "Check out this talk segment: {$tag->what} with {$tag->who}";
                    break;
                case ContentType::GetNewsContentTypeID():
                    $title = "Check out this news: {$tag->what}";
                    break;
                default:
                    $title= "{$tag->who} - {$tag->what}";

            }

            if($tag->connectContent) {
                $attachments = $tag->connectContent->getExtraAttachments();
                if(count($attachments) > 0) $image = $attachments[0]->saved_path;
            }
    	}
    	
        return view('share.share')
            ->with('content_type_list', ContentType::$CONTENT_TYPES)
            ->with('stationLogo', $stationLogo)
            ->with('stationURL', $stationURL)
            ->with('title', $title)
            ->with('image', $image)
            ->with('url', $url)
            ->with('tagID', $tagID);
    }
    
    public function showContentDefault() {
    	return $this->showContent('');
    }

    public function audioDownload($hash) {
        try {

            $tag = Tag::where('hash', '=', $hash)->first();
            $station = Station::find($tag->station_id);

            $audioInfo = $tag->getAudioInfoForTag(false);
            $originalFile = $audioInfo['audioURL'];

            $date = Carbon::createFromTimestamp($tag->tag_timestamp / 1000)->format('YMd_H_m');
            $stationAbbrev = $station->station_abbrev;
            $who = $tag->who;
            $adKey = $tag->connectContent->ad_key ? "_{$tag->connectContent->ad_key}" : '';

            $newFilename = "{$date}_{$stationAbbrev}_{$who}{$adKey}.mp3";
            $newFilename = preg_replace('/(\s)/', '_', $newFilename);

            // headers to send your file
            header("Content-Type: audio/mp3");
//            header("Content-Length: " . filesize($originalFile));
            header('Content-Disposition: attachment; filename="' . $newFilename . '"');

            // upload the file to the user and quit
            readfile($originalFile);

        } catch(\Exception $ex) {
            \Log::error($ex);
            return response()->json(array('code' => -1, 'msg' => $ex->getMessage()));
        }
    }

    /**
     * Embedded link
     */
    public function showPlayer($tagID) {
        return view('share.showplayer')
            ->with('content_type_list', ContentType::$CONTENT_TYPES)
            ->with('tagID', $tagID);
    }
}