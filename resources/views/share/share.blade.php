<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta property="og:title" content="{{$title}}">
	<meta property="og:type" content="website">
	<meta property="og:image" content="{{$image}}">

	<title>AirShr</title>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
	<link href="/js/mediaelement/mediaelementplayer.min.css" rel="stylesheet">
	<link href="http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet">
	<link href="/css/airshrconnect.css?v={{ \Config::get('app.ConnectWebAppVersion') }}" rel="stylesheet">
	<link href="/css/share.css?v={{ \Config::get('app.ConnectWebAppVersion') }}" rel="stylesheet">
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<style>
		@if(!empty($stationLogo))
		.brand {
			background-image:  url({{$stationLogo}}) !important;
			height: 100px !important;
			width: 200px !important;
		}
		@endif
	</style>
</head>
<body>
	<header>
		<div class="container">
			@if(!empty($stationURL)) <!--Should probably not hardcode the station id-->
				<a class="brand" href="{{$stationURL}}"></a>
			@else
				<a class="brand" href="http://www.airshr.com.au"></a>
			@endif
			<div class="share-information">
				<p class="date"></p>
			</div>
		</div>
	</header>
	<section id="track-information">
		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-lg-6 col-sm-offset-2 col-lg-offset-3">
					<h1 class="track"></h1>
					<h2 class="artist"></h2>
				</div>
			</div>
			<div id="cover" class="carousel slide" data-ride="carousel">

				<div class="carousel-inner" role="listbox"></div>
			</div>
			<div class="lyrics">
				<h3 class="lyrics-title">Lyrics</h3>
				<p class="lyrics-content"></p>
				<a class="lyrics-button" href="javascript:getApp()">
					<span>Get the lyrics</span> on the AirShr App
				</a>
			</div>
			<div class="more">
				<p class="more-content"></p>
				<p class="more-content-full" style="display: none;"></p>
			</div>
			<div class="share addthis_toolbox hidden-md hidden-lg col-md-4">
				<ul class="custom_images">
					<li>
						<a class="addthis_button_facebook">
							<img class="static" src="/img/iconShareFacebook.png">
							<img class="hover" style="display: none;" src="/img/iconShareFacebookHover.png">
						</a>
					</li>

					<li>
						<a class="addthis_button_twitter">
							<img class="static" src="/img/iconShareTwitter.png">
							<img class="hover" style="display: none;" src="/img/iconShareTwitterHover.png">
						</a>
					</li>

					<li>
						<a class="addthis_button_whatsapp">
							<img class="static" src="/img/iconShareWhatsapp.png">
							<img class="hover" style="display: none;" src="/img/iconShareWhatsappHover.png">
						</a>
					</li>

					<li>
						<a class="addthis_button_email">
							<img class="static" src="/img/iconShareEmail.png">
							<img class="hover" style="display: none;" src="/img/iconShareEmailHover.png">
						</a>
					</li>
				</ul>
			</div>
		</div>
	</section>
	<section id="get-the-app">
		<div class="container">
			<div class="row">
				<div class="col-md-6">
					<a class="brand" href="http://www.airshr.com.au">
						<img src="/img/logoAirShrGrey.png">
					</a>
					<h3>Liked something  on radio? Save it. Keep it. Shr it.</h3>
					<p class="visible-md visible-lg">Save great radio interviews, talk shows, comedy, music, ads, news  – anything. AirShr lets you enjoy every thing you hear on radio, long after the moment has passed.</p>
					<a class="get-the-app-button" href="javascript:getApp()">Get the app</a>
				</div>
				<div class="col-md-6">
					<div class="img-frame">
						<img src="/img/AirShr_Samsung.png">
						<img src="/img/AirShr_iPhone.png">
					</div>
				</div>
			</div>
		</div>
	</section>
	<div id="player">
		<div class="container">
			<div class="row">
				<div class="col-xs-4">
					<audio type="audio/mp3" controls="controls" src="#" style="width: 100% !important;"></audio>
				</div>
				<div class="col-md-4 hidden-xs hidden-sm">
					<a class="call-to-action-button" href="javascript:void(0)" target="_blank"></a>
				</div>
				<div class="share addthis_toolbox hidden-xs hidden-sm col-md-4">
					<ul class="custom_images">
						<li>
							<a class="addthis_button_facebook">
								<img class="static" src="/img/iconShareFacebook.png">
								<img class="hover" style="display: none;" src="/img/iconShareFacebookHover.png">
							</a>
						</li>
						<li>
							<a class="addthis_button_twitter">
								<img class="static" src="/img/iconShareTwitter.png">
								<img class="hover" style="display: none;" src="/img/iconShareTwitterHover.png">
							</a>
						</li>
						<li>
							<a class="addthis_button_whatsapp">
								<img class="static" src="/img/iconShareWhatsapp.png">
								<img class="hover" style="display: none;" src="/img/iconShareWhatsappHover.png">
							</a>
						</li>
						<li>
							<a class="addthis_button_mailto">
								<img class="static" src="/img/iconShareEmail.png">
								<img class="hover" style="display: none;" src="/img/iconShareEmailHover.png">
							</a>
						</li>
					</ul>
				</div>

				<div class="col-xs-8 hidden-md hidden-lg">
					<a class="call-to-action-button" href="javascript:void(0)" target="_blank"></a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="getAppModalDlg" style="top: 30px">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="border-bottom: none;">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body text-center">
					<h4 id="topMsg"></h4>
					<div class="input-group">
						<input type="text" class="form-control" id="txtPhoneNumber">
						<span class="input-group-btn">
							<button class="btn btn-primary" type="button" id="btnSendAppLink">TEXT ME THE LINK</button>
						</span>
					</div>
					<p style="margin-top: 30px">Available on iOS and Android</p>
				</div>
			</div>
		</div>
	</div>
	{{--<script type="text/javascript">--}}
		{{--var addthis_config = addthis_config||{};--}}
		{{--addthis_config.data_track_clickback = false;--}}
	{{--</script>--}}
	<script>
		var contentTypeList = new Array();
		var tagID           = {{ $tagID }};

		@if (isset($content_type_list))
			@foreach ($content_type_list as $key => $val)
				contentTypeList[{{ $key }}] = '{{ $val }}';
			@endforeach
		@endif
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/mediaelement/2.20.1/mediaelement-and-player.min.js"></script>
	<script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-56d570d944862dc0"></script>
	<script src="/js/moment/moment.js"></script>
	<script src="/js/clientdetection.js"></script>
	<script src="/js/app.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
	<script src="/js/sharemobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
	<script src="/js/share.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
</body>
</html>
