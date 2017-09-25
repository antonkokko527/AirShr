@extends('layout.main')

@section('bodyClass')
class="onair"
@endsection

@section('styles')
@parent 
<link href="/js/datatables-1.10.7/css/jquery.dataTables.min.css" media="all" rel="stylesheet" type="text/css" />
<!-- <link href="/js/datatable-bootstrap/dataTables.bootstrap.css" media="all" rel="stylesheet" type="text/css" /> -->
<link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
<link rel="stylesheet" href="/css/talkbreak.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">

<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#async=1&pubid=ra-57b13be44184a211"></script>

<style>
	/* Float */
	.hvr-float {
		display: inline-block;
		vertical-align: middle;
		-webkit-transform: translateZ(0);
		transform: translateZ(0);
		box-shadow: 0 0 1px rgba(0, 0, 0, 0);
		-webkit-backface-visibility: hidden;
		backface-visibility: hidden;
		-moz-osx-font-smoothing: grayscale;
		-webkit-transition-duration: 0.3s;
		transition-duration: 0.3s;
		-webkit-transition-property: transform;
		transition-property: transform;
		-webkit-transition-timing-function: ease-out;
		transition-timing-function: ease-out;
	}
	.hvr-float:hover, .hvr-float:focus, .hvr-float:active {
		-webkit-transform: translateY(-8px);
		transform: translateY(-8px);
	}
    body {
        padding-right: 0 !important; /*Hack*/
    }
    .what_missing_who {
        color:lightgrey;
    }

    #clear_search {
        color:#fa9898;
        margin-left:10px;
    }
    #clear_search:hover {
        text-decoration: none;
    }
    #clear_search:active {
         text-decoration: none;
    }

    .custom_share_button {
        font-size:32px;
        height:32px;
        width:32px;
    }
</style>

@endsection

@section('content')

<div class="content-sub-header" style="text-align: center">
	<h1 class="content-sub-header-title" id="content_title">On Air</h1>
	<div class="content-sub-header-actions">
		<a class="btn-action" title="Competition Result" id="btn_competition_result" style="display: none"><svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="#000000" d="M7,2V4H2V11C2,12 3,13 4,13H7.2C7.6,14.9 8.6,16.6 11,16.9V19C8,19.2 8,20.3 8,21.6V22H16V21.7C16,20.4 16,19.3 13,19.1V17C15.5,16.7 16.5,15 16.8,13.1H20C21,13.1 22,12.1 22,11.1V4H17V2H7M9,4H15V12A3,3 0 0,1 12,15C10,15 9,13.66 9,12V4M4,6H7V8L7,11H4V6M17,6H20V11H17V6Z" /></svg></a>
    </div>
    <div class="content-sub-header-center-box">
        <span class="form-inline">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Search" id="search_tags_text" typeahead-focus-first="false">
                <button type="button" class="btn-round btn-round-green" id="search_tags_button">Search</button>
            </div>
            <div class="form-group" id="filter_airshrd_container" style="visibility:hidden; margin-left:20px;">
                <label class="checkbox-inline">
                    <input type="checkbox" id="filter_airshrd">Only Show AirShr'd Moments
                </label>
            </div>
            <a href="javascript:void(0)" id="clear_search" style="visibility: hidden;">Clear Search</a>
        </span>
    </div>
	<div class="content-sub-header-right-box">
		<a class="btn-round btn-round-green" id="btn_startstop_talk">START TALK</a>
	</div>

	<span class="saveProgress" style="float:right;"></span>
</div>

<div class="content-air-wrapper">

	<div class="content-air-tag-container">
	
		@include('airshrconnect.onairtags', ['mode' => 'onair'])
	
	</div>
	
	<div class="content-air-preview">
		
		@include('airshrconnect.mobilepreview', ['mode' => 'tag_preview', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'] )
	
	</div>
	
	<div class="content-modal-sidebar right-sidebar hidden" id="competitionresult_sidebar">

		
		
	</div>

</div>

@include('airshrconnect.mobileeditor')

<div id="shareModal" class="popover fade">
	{{--<div class="arrow"></div>--}}

	<h3 class = "popover-title">Share <button type="button" class="close" data-dismiss="modal">&times;</button></h3>
	<div class="popover-content">
		<!-- Go to www.addthis.com/dashboard to customize your tools -->
		{{--<div class="addthis_sharing_toolbox"></div>--}}

		<div class="page_sharing_toolbox addthis_toolbox addthis_32x32_style" style="text-align:center;">
			<div class="share-icons" id="share-icons">
				<span class="hvr-float" style="height:50px;"><a href="javascript:void(0)" class="mail_button custom_share_button" title="Email" style="background-color:grey;"><i class="mdi mdi-email"></i></a></span>
				<span class="hvr-float"><a class="addthis_button_facebook left" title="Facebook"></a></span>
				<span class="hvr-float"><a class="addthis_button_twitter left" title="Twitter"></a></span>
                <span class="hvr-float"><a class="addthis_button_link left last" title="Copy URL"></a></span>
                <span class="hvr-float" style="height:50px;">
                    <a href="javascript:void(0)" class="download_audio custom_share_button" title="Download Audio" style="margin-left:20px; background-color:forestgreen;">
                        <i class="mdi mdi-download"></i>
                    </a>
                </span>
			</div>
		</div>

	</div>
</div>

@endsection


@section('scripts')

@parent

<script>
<!--
	var WebSocketURL = '{{ $WebSocketURL }}';
-->
</script>
<script src="/js/datatables-1.10.7/js/jquery.dataTables.min.js"></script>
<!-- <script src="/js/datatable-bootstrap/dataTables.bootstrap.min.js"></script> -->
<script src="//cdn.datatables.net/plug-ins/1.10.7/sorting/datetime-moment.js"></script>
<script type="text/javascript" src="/js/bootstrap.progressbar/bootstrap-progressbar.min.js"></script>
<script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
<script src="/js/typeaheadjs.js"></script>
<script src="/js/jcrop/js/Jcrop.js"></script>
<script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
<script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

<script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<script src="/js/websocket.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/onair.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/typeaheadjs.js"></script>
<script>
<!--
	var OnAirFormObj = null;
	$(document).ready(function() {
		OnAirFormObj = new OnAirForm('onair');
	});


	var TalkBreakAutoCompleteList = [];
	@if (isset($talkbreak_autocomplete_list))
		TalkBreakAutoCompleteList = <?php echo json_encode($talkbreak_autocomplete_list);?>;
	@endif

	
-->
</script>
<script>
	<!--
	var previewType = 'live';
	-->
</script>

<script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>


@endsection


