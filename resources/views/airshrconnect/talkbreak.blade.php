@extends('layout.main')

@section('styles')
    @parent
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    <link rel="stylesheet" href="/css/talkbreak.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
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
                            <option value="{{ $key }}"  <?php if ($key == $content_type_id_for_talkbreak) echo "selected"; ?>>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>

        <div class="content-sub-header-form" id="goBackLinkContainer">
            <a href="javascript:void(0)" class="goBackLink">Return to Search List</a>
        </div>
        
        <div class="content-sub-header-form">
            <h1 class="content-sub-header-info" id="content_created_date_info" style="display: inline-block;"></h1>
        </div>
        
        <div class="content-sub-header-actions">
            <span class="saveProgress"></span>
            <a class="btn-action" title="Save" id="content_btn_save" style="display: inline-block;"><i class="mdi mdi-content-save"></i></a>
            <a class="btn-action" title="Copy" id="content_btn_copy" style="display: inline-block;"><i class="mdi mdi-content-copy"></i></a>
            <a class="btn-action" title="Remove" id="content_btn_remove" style="display: inline-block;"><i class="mdi mdi-delete"></i></a>
            <a class="btn-action" title="New" id="content_btn_new" style="display: inline-block;"><i class="mdi mdi-plus"></i></a>
        </div>
    </div>

    <div id="mobilepreview_centered">

        @include('airshrconnect.mobilepreview', ['mode' => 'talkbreak', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])

    </div>

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
        var page = 'talkBreak';
        var contentID = {{$content->id}};
        var createdDate = moment('{{$content->created_at}}');
        var isNew = {{$is_new}};
        var saved = isNew ? false : true;
        var deleted = false;
        $('#content_created_date_info').html('CREATED ' + createdDate.format('DD-MMM HH:mm'));
    </script>
    <script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src ="/js/talkbreak.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection