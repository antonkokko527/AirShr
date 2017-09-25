<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>AirShr</title>

    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <!-- share css -->
    <link rel="stylesheet" type="text/css" href="/css/pushmenu/normalize.css" />
    <link rel="stylesheet" type="text/css" href="/css/pushmenu/demo.css" />
    <link rel="stylesheet" type="text/css" href="/css/pushmenu/icons.css" />
    <link rel="stylesheet" type="text/css" href="/css/pushmenu/component.css" />

    <link href="/css/materialdesignicons.min.css" media="all" rel="stylesheet" type="text/css" />
    
    <link href="/js/mediaelement/mediaelementplayer.min.css" media="all" rel="stylesheet" type="text/css" />

    <link href="http://netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet">

    <!-- Main Css -->
    <link href="/css/airshrconnect.css?v={{ \Config::get('app.ConnectWebAppVersion') }}" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="/css/share.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div class="main-content">
    <!-- The Mobile preview -->
    <div class="mobilepreview_container">
        <div class="mobilepreview_slider_container" id="mobilepreview_slider_container">
        </div>

        <div class="mobilepreview_audio_player_container">
            <audio id="preview_audio_player" src="#" type="audio/mp3" controls="controls" class="preview_audio_player">
            </audio>
        </div>

        <div class="mobilepreview_action_buttons_container">
            <span class="station_name"></span>
        </div>

        <div class="mobilepreview_content_container">
            <div class="text-content">
                <h1 id="mobilepreview_what"></h1>
                <h2 id="mobilepreview_who"></h2>
                <p id="mobilepreview_more"></p>
            </div>

            <div class="bottom-action-button">
                <a href="javascript:void(0)" class="preview-action-button" id="preview-action-button" target="_blank"></a>
            </div>
            <div class="bottom-nav-shape">
            </div>

        </div>
        <div class="loading hide" id="mobilepreview_loader">
            <img src="/img/ajax-loader.gif" class="loader-img">
        </div>
    </div>

</div>

<div class="hide loading">
    <img src="/img/ajax-loader.gif" class="loader-img">
</div>



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/js/bootstrap.min.js"></script>
<script src="/js/bootstrap3-typeahead.min.js"></script>
<script src="/js/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/js/bootbox/bootbox.min.js"></script>
<script src="/js/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>

<!-- share scripts -->
<script>
    <!--
    var contentTypeList = new Array();
    @if (isset($content_type_list))
    @foreach ($content_type_list as $key => $val)
        contentTypeList[{{$key}}] = '{{$val}}';
    @endforeach
    @endif
-->
</script>

<script>
    <!--
    var tagID = {{ $tagID }};
    -->
</script>

<script src="/js/pushmenu/modernizr.custom.js"></script>
<script src="/js/pushmenu/classie.js"></script>

<script src="/js/moment/moment.js"></script>

<script src="/js/mediaelement/mediaelement-and-player.min.js"></script>

<script src="/js/app.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<script type="text/javascript" src="/js/bootstrap.progressbar/bootstrap-progressbar.min.js"></script>

<script src="/js/sharemobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>


</body>
</html>