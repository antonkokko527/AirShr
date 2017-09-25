@extends('layout.main')

@section('styles')
    @parent
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
                            <option value="{{ $key }}"  <?php if ($key == $content_type_id_for_ad) echo "selected"; ?>>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>

        <div class="content-sub-header-form" id="goBackLinkContainer">
            <a href="javascript:void(0)" class="goBackLink">Return to Search List</a>
        </div>

        <div class="content-sub-header-actions">
            <span class="saveProgress"></span>
            <a class="btn-action" title="Search" id="content_btn_search" style="display: inline-block;"><i class="mdi mdi-magnify"></i></a>
            <a class="btn-action" title="New" id="content_btn_new" style="display: inline-block;"><i class="mdi mdi-plus"></i></a>
            <a class="btn-action" title="Copy" id="content_btn_copy" style="display: inline-block;"><i class="mdi mdi-content-copy"></i></a>
            <a class="btn-action" title="Save" id="content_btn_save" style="display: inline-block;"><i class="mdi mdi-content-save"></i></a>
            <a class="btn-action" title="Remove" id="content_btn_remove" style="display: inline-block;"><i class="mdi mdi-delete"></i></a>
        </div>
    </div>

    <form class="content-form" id="content_ad_add_form">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group" id="content_sub_type_ad_length_wrapper">
                    <div class="row">
                        <div class="col-sm-8">
                            <select class="form-control" id="content_subtype_id">
                                <option value="0">Type</option>
                                <option value="1">Direct</option>
                                <option value="2">Agency</option>
                                <option value="3">Promo</option>
                                <option value="4">Generic</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <select class="form-control" id="content_rec_type">
                                <option value="">Rec/Live</option>
                                <option value="rec">Rec</option>
                                <option value="live">Live</option>
                                <option value="sim_live">Sim Live</option>
                            </select>
                        </div>
                        <div class="col-sm-8">
                            <select class="form-control" id="content_ad_length">
                                <option value="0">Dur.</option>
                                <option value="10">10s</option>
                                <option value="15">15s</option>
                                <option value="20">20s</option>
                                <option value="30">30s</option>
                                <option value="45">45s</option>
                                <option value="60">60s</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" id="content_client" placeholder="Client Company" autocomplete="off"  />
                </div>

                <div class="form-group">
                    <input type="text" class="form-control" id="content_product" placeholder="Product" autocomplete="off" />
                </div>

                <br />

                <div class="form-group">
                    <input type="text" class="form-control" id="content_ad_key" placeholder="Key #">
                </div>
            </div>

            <div id="mobilepreview_centered">

                @include('airshrconnect.mobilepreview', ['mode' => 'ad', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])

            </div>
        </div>

    </form>

    @include('airshrconnect.mobileeditor')
@endsection

@section('scripts')
    @parent
    <script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <script src="/js/typeaheadjs.js"></script>

    <script src="/js/jcrop/js/Jcrop.js"></script>
    <script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>

    <script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

    <script>
        var page = 'ad';
        var contentID = {{$contentID}};
    </script>
    <script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/ad.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection