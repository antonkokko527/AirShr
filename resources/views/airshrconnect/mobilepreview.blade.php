
<?php
	$border = '';
	$height = '';
	if($mode == 'scheduler') {
		$border = 'border: 1px solid rgb(230,230,230);';
		$height = 'height:335px;';
	}
?>

	@if ($mode != 'image_editor_preview' && $mode != 'image_editor_preview_mobile')
	<div class="mobilepreview_action_buttons_container">
		@if($mode == 'ad')
		<div id="new_ad_text" style="color:red;font-family:'GothamRnd Book';text-align:center">
			Please enter client company before editing
		</div>
		@endif
		{{--<span class="station_name">{{ Auth::User()->station->station_abbrev }}</span>--}}
		@if ($displayFormOption == 'true')
			@if ($displayFormCloseOption == 'true')
				<a href="javascript:void(0)" class="mobileeditor-close-button btn-action" title="Close preview"><i class="mdi mdi-logout" style="font-size:28px"></i></a>
			@endif
			@if($mode != 'clientinfo' && $mode != 'scheduler' && $mode != 'talkbreak' && $mode != 'ad' && $mode != 'news' && $mode != 'musicrating')
				<a href="javascript:void(0)" class="preview-form-button btn-action" title="View more details" style="float:left; z-index: 1; margin-left:10px;"><i class="mdi mdi-information-outline"></i></a>
				<a href="javascript:void(0)" class="client-info-button" title="View more details" style="float:left; z-index: 1; margin-left:10px; display:none;"><i class="mdi mdi-information-outline"></i></a>
				<span class="itunes-buttons music-indicator" style="border-bottom:solid; display:none;">
					<a href="javascript:void(0)" class="itunes-button">
						<svg width="24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 170 170" version="1.1" height="24px">
							<g fill="#7ed321" id="itunes_ready_icon">
								<path d="m150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.197-2.12-9.973-3.17-14.34-3.17-4.58 0-9.492 1.05-14.746 3.17-5.262 2.13-9.501 3.24-12.742 3.35-4.929 0.21-9.842-1.96-14.746-6.52-3.13-2.73-7.045-7.41-11.735-14.04-5.032-7.08-9.169-15.29-12.41-24.65-3.471-10.11-5.211-19.9-5.211-29.378 0-10.857 2.346-20.221 7.045-28.068 3.693-6.303 8.606-11.275 14.755-14.925s12.793-5.51 19.948-5.629c3.915 0 9.049 1.211 15.429 3.591 6.362 2.388 10.447 3.599 12.238 3.599 1.339 0 5.877-1.416 13.57-4.239 7.275-2.618 13.415-3.702 18.445-3.275 13.63 1.1 23.87 6.473 30.68 16.153-12.19 7.386-18.22 17.731-18.1 31.002 0.11 10.337 3.86 18.939 11.23 25.769 3.34 3.17 7.07 5.62 11.22 7.36-0.9 2.61-1.85 5.11-2.86 7.51zm-31.26-123.01c0 8.1021-2.96 15.667-8.86 22.669-7.12 8.324-15.732 13.134-25.071 12.375-0.119-0.972-0.188-1.995-0.188-3.07 0-7.778 3.386-16.102 9.399-22.908 3.002-3.446 6.82-6.3113 11.45-8.597 4.62-2.2516 8.99-3.4968 13.1-3.71 0.12 1.0831 0.17 2.1663 0.17 3.2409z"/>
							</g>
						</svg>
					</a>
					<a href="javascript:void(0)" id="itunes_ready_check" class="success-green"><i class="mdi mdi-checkbox-marked-outline"></i></a>
				</span>
				<span class="google-buttons music-indicator" style="display:none;">
					<a href="javascript:void(0)" class="google-button">
						<svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-147 -70 294 345">
							<g fill="#7ed321" id="google_ready_icon">
								<use stroke-width="14.4" xlink:href="#b" stroke="#FFF"/>
								<use xlink:href="#a" transform="scale(-1,1)"/>
								<g id="a" stroke="#FFF" stroke-width="7.2">
									<rect rx="6.5" transform="rotate(29)" height="86" width="13" y="-86" x="14"/>
									<rect id="c" rx="24" height="133" width="48" y="41" x="-143"/>
									<use y="97" x="85" xlink:href="#c"/>
								</g>
								<g id="b">
									<ellipse cy="41" rx="91" ry="84"/>
									<rect rx="22" height="182" width="182" y="20" x="-91"/>
								</g>
							</g>
							<g stroke="#FFF" stroke-width="7.2" fill="#FFF">
								<path d="m-95 44.5h190"/><circle cx="-42" r="4"/><circle cx="42" r="4"/>
							</g>
						</svg>
					</a>
					<a href="javascript:void(0)" id="google_ready_check" data-toggle="tooltip" class="success-green"><i class="mdi mdi-checkbox-marked-outline"></i></a>
				</span>
			@endif
			<a href="javascript:void(0)" class="copy-ad-button btn-action" title="Show copy options" style="float:left; z-index: 1; display:none"><i class="mdi mdi-content-copy"></i></a>
			@if($mode == 'talkbreak')
				<a href="javascript:void(0)" onclick="setVote()" id="vote_button" data-toggle="tooltip" title="Set as vote">
					<svg width="24px" height="24px" style="float:left;margin-top:13px;margin-left:15px;" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Client-Info/Images" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round">
							<g id="vote_icon" transform="translate(-609.000000, -116.000000)" stroke-width="2" stroke="#9B9B9B">
								<g id="Page-1" transform="translate(610.000000, 117.000000)">
									<g id="Group-4">
										<path d="M20.6042623,12.160682 L21.8224918,12.160682 C22.9392787,12.160682 23.8527213,13.0663213 23.8527213,14.1734689 L23.8527213,14.1734689 C23.8527213,15.2806164 22.9392787,16.1867148 21.8224918,16.1867148 L19.7922623,16.1867148" id="Stroke-2"></path>
										<path d="M18.5738492,16.1866689 L20.807423,16.1866689 C21.8122098,16.1866689 22.6347672,17.001882 22.6347672,17.9984066 L22.6347672,17.9984066 C22.6347672,18.9949311 21.8122098,19.8101443 20.807423,19.8101443 L19.5891934,19.8101443" id="Stroke-4"></path>
										<path d="M18.5738492,12.160682 L20.6040787,12.160682 C21.7208656,12.160682 22.6347672,11.2550426 22.6347672,10.1478951 L22.6347672,10.1478951 C22.6347672,9.04074754 21.7208656,8.13464918 20.6040787,8.13464918 L14.9122754,8.13464918 L14.9122754,3.2323541 C14.9122754,1.50002623 13.4824393,0.0825836066 11.7349639,0.0825836066 L10.8513574,0.0825836066 L10.8513574,4.08520656 C10.8513574,7.05458361 8.40020984,9.48461639 5.40512787,9.48461639 L4.5743082,9.48461639 L4.5743082,19.1941902 C4.5743082,21.3134689 6.30663607,23.0311082 8.44381639,23.0315672 L19.8379803,23.0334033 C20.7059803,23.0338623 21.4165377,22.3297311 21.4165377,21.4690754 L21.4165377,21.428223 C21.4165377,20.534059 20.6844066,19.8092721 19.7907016,19.8097311 C19.1545049,19.8101902 18.5738492,19.8101902 18.5738492,19.8101902" id="Stroke-6"></path>
										<path d="M0.968642623,22.9203016 L4.57421639,22.9203016 L4.57421639,8.53518689 L0.968642623,8.53518689 C0.461429508,8.53518689 0.0506098361,8.94600656 0.0506098361,9.45321967 L0.0506098361,22.0022689 C0.0506098361,22.509482 0.461429508,22.9203016 0.968642623,22.9203016 L0.968642623,22.9203016 Z" id="Stroke-8"></path>
									</g>
								</g>
							</g>
						</g>
					</svg>
				</a>
				<a href = "javascript:void(0)" onclick="setCompetition()" id="competition_button" data-toggle="tooltip" title="Set as competition">
					<svg style="width:24px;height:24px;float:left;margin-top:13px;margin-left:15px;" viewBox="0 0 24 24"><path id="competition_icon" fill="#9B9B9B" d="M7,2V4H2V11C2,12 3,13 4,13H7.2C7.6,14.9 8.6,16.6 11,16.9V19C8,19.2 8,20.3 8,21.6V22H16V21.7C16,20.4 16,19.3 13,19.1V17C15.5,16.7 16.5,15 16.8,13.1H20C21,13.1 22,12.1 22,11.1V4H17V2H7M9,4H15V12A3,3 0 0,1 12,15C10,15 9,13.66 9,12V4M4,6H7V8L7,11H4V6M17,6H20V11H17V6Z" /></svg>
				</a>
			@endif
			@if ($mode == 'tag_preview')
				<a href="javascript:void(0)" id="vote_button" data-toggle="tooltip" title="Set as vote" style="display: none">
					<svg width="24px" height="24px" style="float:left;margin-top:13px;margin-left:15px;" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Client-Info/Images" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round">
							<g id="vote_icon" transform="translate(-609.000000, -116.000000)" stroke-width="2" stroke="#9B9B9B">
								<g id="Page-1" transform="translate(610.000000, 117.000000)">
									<g id="Group-4">
										<path d="M20.6042623,12.160682 L21.8224918,12.160682 C22.9392787,12.160682 23.8527213,13.0663213 23.8527213,14.1734689 L23.8527213,14.1734689 C23.8527213,15.2806164 22.9392787,16.1867148 21.8224918,16.1867148 L19.7922623,16.1867148" id="Stroke-2"></path>
										<path d="M18.5738492,16.1866689 L20.807423,16.1866689 C21.8122098,16.1866689 22.6347672,17.001882 22.6347672,17.9984066 L22.6347672,17.9984066 C22.6347672,18.9949311 21.8122098,19.8101443 20.807423,19.8101443 L19.5891934,19.8101443" id="Stroke-4"></path>
										<path d="M18.5738492,12.160682 L20.6040787,12.160682 C21.7208656,12.160682 22.6347672,11.2550426 22.6347672,10.1478951 L22.6347672,10.1478951 C22.6347672,9.04074754 21.7208656,8.13464918 20.6040787,8.13464918 L14.9122754,8.13464918 L14.9122754,3.2323541 C14.9122754,1.50002623 13.4824393,0.0825836066 11.7349639,0.0825836066 L10.8513574,0.0825836066 L10.8513574,4.08520656 C10.8513574,7.05458361 8.40020984,9.48461639 5.40512787,9.48461639 L4.5743082,9.48461639 L4.5743082,19.1941902 C4.5743082,21.3134689 6.30663607,23.0311082 8.44381639,23.0315672 L19.8379803,23.0334033 C20.7059803,23.0338623 21.4165377,22.3297311 21.4165377,21.4690754 L21.4165377,21.428223 C21.4165377,20.534059 20.6844066,19.8092721 19.7907016,19.8097311 C19.1545049,19.8101902 18.5738492,19.8101902 18.5738492,19.8101902" id="Stroke-6"></path>
										<path d="M0.968642623,22.9203016 L4.57421639,22.9203016 L4.57421639,8.53518689 L0.968642623,8.53518689 C0.461429508,8.53518689 0.0506098361,8.94600656 0.0506098361,9.45321967 L0.0506098361,22.0022689 C0.0506098361,22.509482 0.461429508,22.9203016 0.968642623,22.9203016 L0.968642623,22.9203016 Z" id="Stroke-8"></path>
									</g>
								</g>
							</g>
						</g>
					</svg>
]				</a>
				<a href = "javascript:void(0)" id="competition_button" data-toggle="tooltip" title="Set as competition" style="display: none">
					<svg style="width:24px;height:24px;float:left;margin-top:13px;margin-left:15px;" viewBox="0 0 24 24"><path id="competition_icon" fill="#9B9B9B" d="M7,2V4H2V11C2,12 3,13 4,13H7.2C7.6,14.9 8.6,16.6 11,16.9V19C8,19.2 8,20.3 8,21.6V22H16V21.7C16,20.4 16,19.3 13,19.1V17C15.5,16.7 16.5,15 16.8,13.1H20C21,13.1 22,12.1 22,11.1V4H17V2H7M9,4H15V12A3,3 0 0,1 12,15C10,15 9,13.66 9,12V4M4,6H7V8L7,11H4V6M17,6H20V11H17V6Z" /></svg>
				</a>
			@endif
			<a href="javascript:void(0)" id="ready_button" data-toggle="tooltip" title="Ready to AirShr" style="z-index: 1; color:red; display:none"><i class="mdi mdi-checkbox-blank-outline"></i></a>
		@endif
		{{--<div class="actions">--}}
		{{--<a href="javascript:void(0)" class="action-btn"><img src="/img/iconTimeMachine.png" /></a>--}}
		{{--<a href="javascript:void(0)" class="action-btn"><img src="/img/iconTrash.png" /></a>--}}
		{{--<a href="javascript:void(0)" class="action-btn"><img src="/img/iconShare.png" /></a>--}}
		{{--<a href="javascript:void(0)" class="action-btn"><img src="/img/iconFavorite.png" /></a>--}}
		{{--</div>--}}
	</div>
	@endif

@if ($displayFormOption == 'true')
	<div id="edit-image-button-div" style="display:none;"></div>
@endif
<div class="mobilepreview_slider_container slider-border" id="{{$sliderContainerID}}">


	@if ($mode == 'image_editor_preview')
	<img id="image_editor_preview" border="0" class="image_editor_preview"/>
	<div id="image_editor_preview_banner" class="image_editor_preview_banner">
		<img border="0" class="image_editor_preview_banner_img"/>
	</div>
	@elseif ($mode == 'image_editor_preview_mobile')
	<img id="image_editor_preview_mobile" border="0" class="image_editor_preview"/>
	<div id="image_editor_preview_banner_mobile" class="image_editor_preview_banner">
		<img border="0" class="image_editor_preview_banner_img"/>
	</div>
	@elseif ($mode == 'tag_preview')


	@endif
</div>
@if($mode != 'scheduler' && $mode != 'clientinfo' && $mode != 'talkbreak'&& $mode != 'news')
<div class="mobilepreview_audio_player_container">
	@if ($mode == 'image_editor_preview' || $mode == 'image_mobile_editor_preview')
	<audio id="audio_player" src="#" type="audio/mp3" controls="controls" class="preview_audio_player">
	</audio>	
	@elseif ($mode == 'tag_preview' || $mode == 'ad' || $mode == 'musicrating')
	<audio id="preview_audio_player" src="#" type="audio/mp3" controls="controls" class="preview_audio_player">
	</audio>	
	@endif
</div>
@endif


<div class="mobilepreview_content_container" style="{{$border}} {{$height}}">
	@if($mode == 'talkbreak')
	<div class="vote-separator" style="display:none"></div>
	@endif
	<div class="text-content">
		@if ($mode == 'tag_preview' || $mode == 'scheduler' || $mode == 'clientinfo' || $mode == 'talkbreak' || $mode == 'ad' || $mode == 'news' || $mode == 'musicrating')
		<h1 id="mobilepreview_what"></h1>
		<h2 id="mobilepreview_who"></h2>
		<p id="mobilepreview_more"></p>
		@endif
		@if($mode == 'talkbreak' || $mode == 'tag_preview')
			<div class="row" id="vote_options">
				<div class="col-md-12" id="vote_option_1"></div>
				<div class="col-md-12" id="vote_option_2"></div>
			</div>
			<div class="row" id="vote_options_percent">
				<div class="col-md-12 text-center" id="vote_option_1_percent">00%</div>
				<div class="col-md-12 text-center" id="vote_option_2_percent">00%</div>
			</div>
		@endif
	</div>
	
	<div class="bottom-action-button">
		@if ($displayFormOption == 'true')
		<div id="edit-action-button-div" style="display:none"></div>
		@endif
		@if ($mode == 'tag_preview' || $mode == 'scheduler' || $mode == 'clientinfo' || $mode == 'talkbreak' || $mode == 'ad' || $mode == 'news' || $mode == 'musicrating')
		<a href="javascript:void(0)" class="preview-action-button" id="preview-action-button" target="_blank"></a>
		@elseif ($mode == 'image_editor_preview')
		<a href="javascript:void(0)" class="preview-action-button" target="_blank"></a>
		@endif
	</div>
	<div class="bottom-nav-shape">
		@if ($displayFormOption == 'true')
		@if ($displayFormCloseOption == 'true')
		{{--<a href="javascript:void(0)" class="preview-close-button" style="z-index: 1"><i class="mdi mdi-close"></i></a>--}}
		@endif
		{{--<a href="javascript:void(0)" class="preview-form-button" style="z-index: 1"><i class="mdi mdi-credit-card"></i></a>--}}
		<div id="edit-address-button-div" style="display:none"></div>
		<a href="javascript:void(0)" id="openGoogleModalButton" style="color:white; margin:25px 10px; position:absolute; display:none; float:right; font-weight:500;">Wrong Song?</a>
		<a href="javascript:void(0)" id="openITunesModalButton" style="color:white; margin:25px 10px; position:absolute; display:none; float:right; font-weight:500;">Wrong Song?</a>
		<div id="map" style="width:348px;height:72px;"></div>
		@endif
	</div>

</div>

@if ($mode == 'tag_preview' || $mode == 'scheduler' || $mode == 'talkbreak' || $mode == 'ad' || $mode == 'musicrating')
<div class="loading hide" id="mobilepreview_loader">
	<img src="/img/ajax-loader.gif" class="loader-img">
</div>
@endif