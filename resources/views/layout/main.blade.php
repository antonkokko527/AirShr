@extends('layout.basic')


@section('styles')

<link rel="stylesheet" type="text/css" href="/css/pushmenu/normalize.css" />
<link rel="stylesheet" type="text/css" href="/css/pushmenu/demo.css" />
<link rel="stylesheet" type="text/css" href="/css/pushmenu/icons.css" />
<link rel="stylesheet" type="text/css" href="/css/pushmenu/component.css" />

<link href="/css/materialdesignicons.min.css" media="all" rel="stylesheet" type="text/css" />

<link rel="stylesheet" type="text/css" href="/js/dropzone/dropzone.css" />

<link rel="stylesheet" type="text/css" href="/js/gritter/css/jquery.gritter.css" />

<link href="/js/mediaelement/mediaelementplayer.min.css" media="all" rel="stylesheet" type="text/css" />

@endsection



@section('layout')

<div class="content-wrapper">
	
	<!-- Push Wrapper -->
	<div class="mp-pusher" id="mp-pusher">
	
		<!-- mp-menu -->
		<nav id="mp-menu" class="mp-menu">
			<div class="mp-level">
			
				<div class="menu-logo-container">
					<img class="img img-responsive img-logo" src="/img/Logo AirShr.png" width="40px"/>
				</div>
			
				<ul class="mp-side-menu">
					<li>
						<a class="icon icon-megaphone" href="/content/air">On Air</a>
					</li>
					<li>
						<a class="icon icon-news" href="/content">Content</a>
					</li>
					<!-- <li>
						<a class="icon icon-user" href="#">People</a>
					</li>
					<li>
						<a class="icon icon-settings" href="#">Settings</a>
					</li>
					<li>
						<a class="icon icon-note" href="#">Help</a>
					</li> -->
					
					@if (Auth::User()->isAdminUser())
					<li>
						<a class="icon icon-data" href="/dashboard">Analytics</a>
					</li>
					@endif

					<!--<li>
						<a class="icon icon-music" href="/content/musicRating"><span style="font-size:16px;">Music Ratings</span></a>
					</li>-->

					<li>
						<a class="icon icon-wallet" href="/auth/logout">Logout</a>
					</li>
					
				</ul>
			</div>
		</nav>
		<!-- /mp-menu -->
		
		<div class="main-content">
	
			<div class="main-top-header">
				
				<div class="menu-toggle-container">
					<span class="glyphicon glyphicon-menu-hamburger" id="trigger"></span>
				</div>
				
				<div class="top-station-region-info">
					{{ Auth::User()->station->getStationFirstRegionName() }} <br/>
					<span id="station_region_time"></span>
				</div>
				
				<div class="top-station-name">
					{{ Auth::User()->station->station_abbrev }}
				</div>
				
			
			</div>
			
			<div class="content-wrapper" id="main-content-wrapper">
			
				@yield('content')
				
			</div>
			
		
		</div>
		
	</div><!-- /pusher -->
	
	

</div><!--  /container -->

@endsection




@section('scripts')

<script>
<!--
	var contentTypeList = new Array();
	@if (isset($content_type_list)) 
	@foreach ($content_type_list as $key => $val)
		contentTypeList[{{$key}}] = '{{$val}}';
	@endforeach
	@endif

	var GLOBAL = {
			STATION_ID: '{{\Auth::User()->station->id}}',
			STATION_TIMEZONE: '{{\Auth::User()->station->getStationTimezone()}}',
			STATION_ABBREV: '{{ Auth::User()->station->station_abbrev }}',
			CLIENT_LIST: [],
			CLIENT_TRADING_NAME_LIST: []
	};

	@if (isset($client_list_array))
	@foreach ($client_list_array as $client_name)
		GLOBAL.CLIENT_LIST.push("<?php echo addslashes($client_name);?>");
	@endforeach
	@endif

	@if (isset($client_trading_name_list))
	@foreach ($client_trading_name_list as $client_name)
		GLOBAL.CLIENT_TRADING_NAME_LIST.push("<?php echo addslashes($client_name);?>");
	@endforeach
	@endif
		
	$(document).ready(function(){

		function showStationRegionTime() {
			$('#station_region_time').html(moment().tz(GLOBAL.STATION_TIMEZONE).format('HH:mm:ss'));

			setTimeout(function() {
				showStationRegionTime();
			}, 1000);
		}


		showStationRegionTime();
		
	});

	
-->
</script>

<script src="/js/pushmenu/modernizr.custom.js"></script>
<script src="/js/pushmenu/classie.js"></script>
<script src="/js/pushmenu/mlpushmenu.js"></script>

<script src="/js/dropzone/dropzone.js"></script>

<script src="/js/moment/moment.js"></script>
<script src="/js/moment/moment-timezone-with-data.min.js"></script>

<script src="/js/mediaelement/mediaelement-and-player.min.js"></script>

<script>
	new mlPushMenu( document.getElementById( 'mp-menu' ), document.getElementById( 'trigger' ) );

	Dropzone.autoDiscover = false;
	
</script>

<script src="/js/gritter/js/jquery.gritter.min.js"></script>

<script src="/js/app.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<!--  UserVoice Integration -->

@if (\Auth::User()->station->connect_uservoice_enabled)

<script>
// Include the UserVoice JavaScript SDK (only needed once on a page)
UserVoice=window.UserVoice||[];(function(){var uv=document.createElement('script');uv.type='text/javascript';uv.async=true;uv.src='//widget.uservoice.com/osmR8Ve36OFXkj4zjLO7eQ.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(uv,s)})();

//
// UserVoice Javascript SDK developer documentation:
// https://www.uservoice.com/o/javascript-sdk
//

// Set colors
UserVoice.push(['set', {
  accent_color: '#448dd6',
  trigger_color: 'white',
  trigger_background_color: 'rgba(68, 141, 214, 0.8)',
  forum_id: '350829'
}]);

// Identify the user and pass traits
// To enable, replace sample data with actual user traits and uncomment the line
UserVoice.push(['identify', {
  email:      '{{ \Auth::User()->email }}', // User's email address
  name:       '{{ \Auth::User()->first_name }} {{ \Auth::User()->last_name }}', // User's real name
  //created_at: 1364406966, // Unix timestamp for the date the user signed up
  //id:         123, // Optional: Unique id of the user (if set, this should not change)
  //type:       'Owner', // Optional: segment your users by type
  //account: {
  //  id:           123, // Optional: associate multiple users with a single account
  //  name:         'Acme, Co.', // Account name
  //  created_at:   1364406966, // Unix timestamp for the date the account was created
  //  monthly_rate: 9.99, // Decimal; monthly rate of the account
  //  ltv:          1495.00, // Decimal; lifetime value of the account
  //  plan:         'Enhanced' // Plan name for the account
  //}
}]);

// Add default trigger to the bottom-right corner of the window:
UserVoice.push(['addTrigger', {mode: 'contact', trigger_position: 'top-right' }]);

// Or, use your own custom trigger:
//UserVoice.push(['addTrigger', '#id', { mode: 'contact' }]);

// Autoprompt for Satisfaction and SmartVote (only displayed under certain conditions)
UserVoice.push(['autoprompt', {}]);
</script>

@endif

@endsection