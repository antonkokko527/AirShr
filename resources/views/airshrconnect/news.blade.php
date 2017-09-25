@extends('layout.main')

@section('styles')
    @parent
    <link href="/css/fullcalendar.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/css/jquery.timepicker.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    </head>
@endsection

@section('content')

    <div class="content-sub-header">
        <h1 class="content-sub-header-title" id="content_title">Search</h1>
        <div class="content-sub-header-form">
            <form id="content-sub-header-form">
                <div class="form-group">
                    <select class="form-control" id="content_content_type_id">
                        @foreach ($content_type_list_for_connect as $key => $val)
                            <option value="{{ $key }}"  <?php if ($key == $content_type_id_for_news) echo "selected"; ?>>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>

        <div class="content-sub-header-actions">
            <span class="saveProgress"></span>
            <a class="btn-action" title="Search" id="content_btn_search" style="display: inline-block;"><i class="mdi mdi-magnify"></i></a>
            <a class="btn-action" title="Save" id="content_btn_save" style="display: inline-block;"><i class="mdi mdi-content-save"></i></a>
            <a class="btn-action" title="Remove" id="content_btn_remove" style="display: inline-block;"><i class="mdi mdi-delete"></i></a>
        </div>
    </div>

    <form class="content-form" id="content_ad_add_form">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <select class="form-control" id="content_type_id">
                        <option value="3" id="news">News</option>
                        <option value="13" id="weather">Weather</option>
                        <option value="15" id="traffic">Traffic</option>
                        <option value="16" id="sport">Sport</option>
                    </select>
                </div>
            </div>

            <div id="mobilepreview_centered">

                @include('airshrconnect.mobilepreview', ['mode' => 'news', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])

            </div>
        </div>

    </form>

    @include('airshrconnect.mobileeditor')
@endsection

@section('scripts')
    @parent
    <script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <script src="/js/typeaheadjs.js"></script>

    <script src="/js/jcrop/js/Jcrop.js"></script>
    <script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>

    <script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

    <script>
        var page = 'news';
        $('#news').val(ContentTypeIDOfNews);
        $('#weather').val(ContentTypeIDOfWeather);
        $('#traffic').val(ContentTypeIDOfTraffic);
        $('#sport').val(ContentTypeIDOfSport);
    </script>
    <script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/news.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection