<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use File;
use abeautifulsite\SimpleImage;
use Symfony\Component\Process\Process;

class ConnectContentAttachment extends Model {

	use SoftDeletes;
	
	protected $dates = ['deleted_at'];
	
	protected $table = 'airshr_connect_content_attachments';
	
	protected $fillable = array('content_id', 'type', 'filename', 'saved_name', 'saved_path', 'width', 'height', 'extra', 'original_saved_name', 'original_saved_path', 'original_moreinfo', 'duration', 'candidate_adkey', 'station_id');
	
		
	public static function createAttachmentFromFile($station_id, $file, $attachmentType, $originalName, $fileExtension, $additionalImageInfoString, $videoUrl = '', $content_id = 0, $create_object = true) {
		
		if ($attachmentType == 'video') {
			
			$videoInfo = getVideoURLDetails($videoUrl);
			
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
			
			$objArray = [
					'content_id' => $content_id,
					'type' => 'video',
					'filename' => 'video',
					'saved_name' => 'video',
					'saved_path' => convertHttpToHttps($videoUrl),
					'width'		=> $width,
					'height'	=> $height,
					'extra'		=> json_encode($videoInfo),
					'station_id'	=> $station_id
			];
			
			if ($create_object) {
				return ConnectContentAttachment::create($objArray);
			} else {
				return $objArray;
			}
						
		} else {
			
			// read file content
			$fObj = $file->openFile("r");
			$contents = $fObj->fread($fObj->getSize());
			
			$newFileName = uniqid($attachmentType) . "." . $fileExtension;
			$relativePath = \Config::get('app.ContentUploadsS3DIR') . $attachmentType . "/";
			$relativeFileName = $relativePath . $newFileName;
			
			// store to s3
			if (!\Storage::disk('s3')->put($relativeFileName, $contents)) {
				throw new \Exception("Unable to upload file to S3.");
			}
			
			$fullURL = \Config::get('app.ContentS3BaseURL') . $relativeFileName;
			
			// create temp file on the disk
			$tmpFileName = uniqid('tmp_' . $attachmentType) . "." . $fileExtension;
			$tmpRelativePath = \Config::get('app.ContentUploadsDIR') . "tmp/";
			$tmpFullPath = public_path($tmpRelativePath);
			if (!\File::isDirectory($tmpFullPath)) \File::makeDirectory($tmpFullPath, 0777, true);
			file_put_contents($tmpFullPath . $tmpFileName, $contents);
			
			$width = 0;
			$height = 0;
			$original_saved_name = '';
			$original_saved_path = '';
			$original_moreinfo = '';
			
			if ($attachmentType == 'image' || $attachmentType == 'logo') {
				
				$sizeInfo = getimagesize($tmpFullPath . $tmpFileName);
				$width = !empty($sizeInfo[0]) ? $sizeInfo[0] : 0;
				$height = !empty($sizeInfo[1]) ? $sizeInfo[1] : 0;
			
				// crop and save crop information
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
			
					/*$cropAreaX = $cropAreaX * $zoomScale;
					 $cropAreaY = $cropAreaY * $zoomScale;
					$cropAreaWidth = $cropAreaWidth * $zoomScale;
					$cropAreaHeight = $cropAreaHeight * $zoomScale;*/
			
					$croppedImageFileName = uniqid('cropped') . "." . $fileExtension;
					$croppedImageRelativeFileName = $relativePath . $croppedImageFileName;
			
					$simpleImage = new SimpleImage($tmpFullPath . $tmpFileName);
			
					$simpleImage->resize($simpleImage->get_width() * $zoomScale * $imageScale, $simpleImage->get_height() * $zoomScale * $imageScale);
			
					if ($simpleImage->crop($cropAreaX, $cropAreaY, $cropAreaWidth + $cropAreaX, $cropAreaHeight + $cropAreaY)->save($tmpFullPath . $croppedImageFileName)) {
						
						if (\Storage::disk('s3')->put( $croppedImageRelativeFileName, file_get_contents($tmpFullPath . $croppedImageFileName))) {
		
							$original_saved_name = $newFileName;
							$original_saved_path = $fullURL;
							$original_moreinfo = $additionalImageInfoString;
							
							$newFileName = $croppedImageFileName;
							$fullURL = \Config::get('app.ContentS3BaseURL') . $croppedImageRelativeFileName;
				
							$width = $cropAreaWidth;
							$height = $cropAreaHeight;
							
						}
			
					}
				}
			
			} else if ($attachmentType == 'audio') {
			
				// needs converting?
				if ($fileExtension != 'mp3') {
			
					$convertedAudioFileName = uniqid('converted') . ".mp3";
					$convertedAudioRelativeFileName = $relativePath . $convertedAudioFileName;
			
					$audioConvertProcess = new Process("ffmpeg -i {$tmpFullPath}{$tmpFileName} -codec:a libmp3lame -qscale:a 2 {$tmpFullPath}{$convertedAudioFileName}");
					try{
						$audioConvertProcess->run();
						if (!$audioConvertProcess->isSuccessful()) {
							throw new \Exception('Convert process was not successful.');
						}
						
						if (\Storage::disk('s3')->put( $convertedAudioRelativeFileName, file_get_contents($tmpFullPath . $convertedAudioFileName))) {
			
							$original_saved_name = $newFileName;
							$original_saved_path = $fullURL;
				
							$newFileName = $convertedAudioFileName;
							$fullURL = \Config::get('app.ContentS3BaseURL') . $convertedAudioRelativeFileName;	
						}
			
					} catch (\Exception $exx) {
						\Log::error($exx);
						throw new \Exception('Audio file can not be converted to mp3 format.');
					}
				}
			
			}
			
			$objArray = [
					'content_id' => $content_id,
					'type' => $attachmentType,
					'filename' => $originalName,
					'saved_name' => $newFileName,
					'saved_path' => $fullURL,
					'width'		=> $width,
					'height'	=> $height,
					'original_saved_name' => $original_saved_name,
					'original_saved_path' => $original_saved_path,
					'original_moreinfo' => $original_moreinfo,
					'candidate_adkey' => getCandidateAdKeyFromFileName($originalName),
					'station_id'	=> $station_id
			];
			
			if ($create_object) {
				return ConnectContentAttachment::create($objArray);
			} else {
				return $objArray;
			}
		}
	}
	
	public function removeAttachment() {
		
		//@unlink(public_path($this->saved_path));
		
		$attachmentContent = $this->content;
		
		$this->content_id = 0;
		$this->save();
		
		// relink the audio for the content
		if ($this->type == 'audio' && $attachmentContent) {
			$attachmentContent->audio_enabled = 0;
			$attachmentContent->save();
			$attachmentContent->searchAudioFileAndLink();
		}
				
		return $this->delete();
		
	}
	
	public function content() {
		return $this->belongsTo('App\ConnectContent', 'content_id');
	}
	
	public function getJSONArrayForAttachment() {
		$resultArray = array();
		
		if ($this->type == 'video') {
			$resultArray = array('type' => 'video', 'url' => $this->saved_path, 'content_attachment_id' => $this->id);
		} else {
			//$resultArray = array('type' => $this->type, 'url' => \Config::get('app.AirShrConnectBaseURL') . $this->saved_path);
			$resultArray = array('type' => $this->type, 'url' => $this->saved_path, 'content_attachment_id' => $this->id);
		}
		
		if ($this->type != 'audio') {
			$resultArray['width'] = $this->width + 0;
			$resultArray['height'] = $this->height + 0;
		}
		
		if (!empty($this->extra)) {
			$extraValues = json_decode($this->extra, true);
			$resultArray = array_merge($resultArray, $extraValues);
		}
		
		// display information
		if ($this->type == 'image' || $this->type == 'logo') {
			if ($resultArray['width'] >= 800 - 5) {
				$resultArray['display'] = 'fill';
				if ($this->type == 'logo') {
					$resultArray['background'] = '#FFFFFF';
				} else {
					$resultArray['background'] = '#000000';
				}
			} else {
				$resultArray['display'] = 'natural';
				if ($this->type == 'logo') {
					$resultArray['background'] = '#FFFFFF';
				} else {
					$resultArray['background'] = 'blur';
				}
			}
		} else if ($this->type == 'video') {
			$resultArray['display'] = 'fill';
			$resultArray['background'] = '#000000';
		}
		
		return $resultArray;
	}
	
	public static function CopyAttachmentById($attachment_id, $content_id = 0) {
		
		try {

			$attachment = ConnectContentAttachment::findOrFail($attachment_id);
			
			return $attachment->copyAttachment($content_id);
			
			
		} catch (\Exception $ex) {
			return null;
		}
		
	}
	
	public function copyAttachment($content_id = 0) {
		
		try {
			
			if ($this->type == 'video') {
				
				$newObj = ConnectContentAttachment::create(
					[
						'content_id'		=> $content_id,
						'type'				=> 'video',
						'filename'			=> 'video',
						'saved_name'		=> 'video',
						'saved_path'		=> $this->saved_path,
						'width'				=> $this->width,
						'height'			=> $this->height,
						'extra'				=> $this->extra,
						'original_saved_name'	=> $this->original_saved_name,
						'original_saved_path'	=> $this->original_saved_path,
						'original_moreinfo'	=> $this->original_moreinfo,
						'duration'			=> $this->duration,
						'candidate_adkey'	=> $this->candidate_adkey,
						'station_id'		=> $this->station_id
					]);
				
			} else {
				
				$filename = $this->filename;
				$extension = substr($filename, strripos($filename, "."));
				$attachmentType = $this->type;
				
				$newFileName = uniqid($attachmentType) . $extension;
				
				//$relativePath = \Config::get('app.ContentUploadsDIR') . $attachmentType . "/";
				$relativePath = \Config::get('app.ContentUploadsS3DIR') . $attachmentType . "/";
				/*$fullPath = public_path($relativePath);
				if (!File::isDirectory($fullPath)) File::makeDirectory($fullPath, 0777, true);
				$relativeFileName = $relativePath . $newFileName; 
				
				if (!File::copy( public_path($this->saved_path), public_path($relativeFileName) )) {
					throw new \Exception("Unable to copy file");
				}*/
				
				if (!\Storage::disk('s3')->copy($relativePath . $this->saved_name, $relativePath . $newFileName)) {
					throw new \Exception("Unable to copy file");
				}
				
				$fullUrl = \Config::get('app.ContentS3BaseURL') . $relativePath . $newFileName;
				
				$newObj = ConnectContentAttachment::create(
						[
							'content_id'		=> $content_id,
							'type'				=> $this->type,
							'filename'			=> $this->filename,
							'saved_name'		=> $newFileName,
							'saved_path'		=> $fullUrl,
							'width'				=> $this->width,
							'height'			=> $this->height,
							'extra'				=> $this->extra,
							'original_saved_name'	=> $this->original_saved_name,
							'original_saved_path'	=> $this->original_saved_path,
							'original_moreinfo'	=> $this->original_moreinfo,
							'duration'			=> $this->duration,
							'candidate_adkey'	=> $this->candidate_adkey,
							'station_id'		=> $this->station_id
						]);
				
			}
			
			return $newObj;
			
		} catch (\Exception $ex) {
			\Log::error($ex);
			return null;
		}
		
	}
}
