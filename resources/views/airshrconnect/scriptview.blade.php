@extends('layout.maintest')

@section('styles')
@parent
<link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/css/annotator.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/css/annotator.touch.css" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
<link rel="stylesheet" href="/css/scriptview.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
</head>
@endsection

@section('content')

<div class="content-sub-header">
    <h1 class="content-sub-header-title" id="content_title">Script #1 (CNNCTONSTRDS) Preview</h1>
    <div class="content-sub-header-actions">
        <span class="saveProgress"></span>
    </div>
</div>

<div id="script_preview">
    <div class="col-md-6"></div>
    <div class="col-md-12">
        <div id="script_content" class="container-fluid" style="background-color: white;">
            <h2 style="text-align: center;">Demo Script</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam sagittis metus dui, bibendum condimentum metus venenatis ac.</p>
        </div>
    </div>
    <div class="col-md-6"></div>
</div>
@endsection

@section('scripts')
@parent
<script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
<script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
<script src="/js/typeaheadjs.js"></script>

<script src="/js/jcrop/js/Jcrop.js"></script>
<script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
<script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>
<script src="/js/annotator/annotator-full.min.js"></script>
<script src="/js/annotator/annotator.touch.min.js"></script>
<script src="/js/scriptview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<script>
    //get id
</script>
@endsection