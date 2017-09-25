@extends('layout.main')

@section('styles')
    @parent
    <link href="/css/fullcalendar.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/css/jquery.timepicker.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/css/clientinfo.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
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
                            <option value="{{ $key }}"  <?php if ($key == $content_type_id_for_clientinfo) echo "selected"; ?>>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>
        
        <div class="content-sub-header-form" id="goBackLinkContainer">
			<a href="javascript:void(0)" class="goBackLink">Return to Search List</a>
		</div>
	
        <div class="content-sub-header-actions" style="float:right;">
            <span class="saveProgress"></span>
            <a class="btn-action" title="Copy" id="content_btn_copy" style="display: inline-block;"><i class="mdi mdi-content-copy"></i></a>
            <a class="btn-action" title="Save" id="content_btn_save" style="display: inline-block;"><i class="mdi mdi-content-save"></i></a>
            <a class="btn-action" title="Remove" id="content_btn_remove" style="display: inline-block;"><i class="mdi mdi-delete"></i></a>
        </div>
    </div>

    <form class="content-client-form" id="content_client_add_form">
        <div class="row">
            <div class="col-sm-7">

                <div class="form-group with-warning">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="content_client">Client Company Name (From Aquira) *</label>
                    <input type="text" class="form-control" id="content_client" value="{{$who}}" />
                </div>

                <div class="form-group with-warning">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="client_who">Trading / Brand Name *</label>
                    <input type="text" class="form-control" id="client_who" value="{{$who}}" />
                    <label for="client_who" style="float:right">(This is what listeners will see)</label>
                </div>

                <div class="form-group with-warning" style="padding-top:80px;">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="content_product">Industry / Category</label>
                    <input type="text" class="form-control" id="content_product" />
                </div>

                <div class="form-group with-warning">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="client_twitter">Brand @twitter Handle</label>
                    <div class="input-group">
                        <span class="input-group-addon" id="basic-addon1">@</span>
                        <input type="text" class="form-control" id="client_twitter"  maxlength="15"/>
                    </div>
                </div>

                <div class="form-group with-warning" style="padding-top:80px">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="content_executive">Client Executive</label>
                    <input type="text" class="form-control" id="client_executive" />
                </div>

                {{--<div class="form-group">--}}
                    {{--<input type="text" class="form-control" id="content_sales_executive" placeholder="Station Sales Executive" value="{{$client->client_contact_name}}" />--}}
                {{--</div>--}}

                <div class="form-group with-warning">
                    <label class="warning-label" style="float:right;display:none;color:red">Click save to save changes</label>
                    <label for="client_type">Direct / Agency</label>
                    <select class="form-control" id="client_type" name="client_type" >
                        <option value="0">Direct / Agency</option>
                        <option value="direct">Direct</option>
                        <option value="agency">Agency</option>
                    </select>
                </div>
                {{--<div id="direct_form" style="display:none">--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="client_contact_name">Client Contact Name</label>--}}
                        {{--<input type="text" class="form-control" id="client_contact_name" placeholder="Client Contact Name" />--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="client_contact_phone">Client Contact Phone</label>--}}
                        {{--<input type="text" class="form-control" id="client_contact_phone" placeholder="Client Contact Phone" />--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="client_contact_email">Client Contact Email</label>--}}
                        {{--<input type="text" class="form-control" id="client_contact_email" placeholder="Client Contact Email" />--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--<div id="agency_form" style="display:none">--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="agency_name">Agency Name</label>--}}
                        {{--<input type="text" class="form-control" id="agency_name" placeholder="Agency Name" />--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="agency_contact_name">Agency Contact Name</label>--}}
                        {{--<input type="text" class="form-control" id="agency_contact_name" placeholder="Agency Contact Name" />--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="agency_contact_phone">Agency Contact Phone</label>--}}
                        {{--<input type="text" class="form-control" id="agency_contact_phone" placeholder="Agency Contact Phone" />--}}
                    {{--</div>--}}
                    {{--<div class="form-group">--}}
                        {{--<label for="agency_contact_email">Agency Contact Email</label>--}}
                        {{--<input type="text" class="form-control" id="agency_contact_email" placeholder="Agency Contact Email" />--}}
                    {{--</div>--}}
                {{--</div>--}}


            </div>

            <div id="mobilepreview_centered">

                @include('airshrconnect.mobilepreview', ['mode' => 'clientinfo', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])

            </div>
        </div>

    </form>

    <div id="copyClientModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Copy Client</h4>
                </div>
                <div class="modal-body">
                    <form id="copy_client_form">
                        <div class="form-group">
                            <label for="new_client_name" class="control-label">New Client Company Name</label>
                            <input type="text" name="new_client_name" id="new_client_name" class="form-control" autocomplete="off" placeholder="New Client Company Name">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <img id="copy_loading" src="/img/ajax-loader.gif" style="display:none;">
                    <button type="button" id="copy_client" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>


        </div>
    </div>

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
    <script src="/js/nearest/jquery.nearest.min.js"></script>
    <script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

    <script>
        var page = 'clientInfo';
    </script>
    <script src="/js/clientinfo.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
            $('#client_type').on('change', function() {
                if($(this).val() == 'direct')  {
                    $('#direct_form').show();
                    $('#agency_form').hide();
                }
                else if($(this).val() == 'agency')  {
                    $('#direct_form').hide();
                    $('#agency_form').show();
                }
                else {
                    $('#direct_form').hide();
                    $('#agency_form').hide();
                }
            });
        });
        var clientID = {{$clientID}};
    </script>
@endsection