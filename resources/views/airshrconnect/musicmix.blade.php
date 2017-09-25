@extends('layout.main')

@section('styles')
@parent
<link href="/css/fullcalendar.css" media="all" rel="stylesheet" type="text/css" />
<link href="/css/bootstrap-fullcalendar.css" media="all" rel="stylesheet" type="text/css" />
<link href="/css/jquery.timepicker.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
<link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/css/musicmix.css">
<link rel="stylesheet" href="/css/mobileeditor.css">
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
                        <option value="{{ $key }}"  <?php if ($key == $content_type_id_for_musicmix) echo "selected"; ?>>{{ $val }}</option>
                    @endforeach
                </select>
            </div>

        </form>
    </div>
    <div class="content-sub-header-form">
        <form id="content-sub-header-second-form" style="display: block;">
            <div class="form-group">
                <input class="form-control" type="text" id="goto_date" placeholder="Go to Date" style="visibility:hidden;position:absolute;width:0">
                <input class="form-control" type="text" id="current_week" placeholder="Go to Date">
            </div>
        </form>
    </div>
    <div class="content-sub-header-form">
        <form id="content-sub-header-third-form" style="display: block;">
            <div class="form-group">
                <div class="fc-button-group">
                    <button type="button" class="fc-prev-button fc-button btn btn-default"><span class="fc-icon fc-icon-left-single-arrow"></span></button>
                    <button type="button" class="fc-next-button fc-button btn btn-default"><span class="fc-icon fc-icon-right-single-arrow"></span></button>
                    <button type="button" class="fc-today-button fc-button btn btn-default">Today</button>
                </div>
            </div>
        </form>
    </div>
    <div class="content-sub-header-actions">
        <span class="saveProgress"></span>
        <a class="btn-action" title="Print" id="content_btn_print" style="display: inline-block;"><i class="mdi mdi-printer"></i></a>
    </div>
</div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-17">
                <div id='calendar'style="margin-top:80px;max-width:1500px"></div>
                <img src="/img/ajax-loader.gif" class="loader-img hide" id="calendar_loader">
            </div>
        </div>
    </div>


    <div class="content-modal-sidebar right-sidebar" id="mobilepreview_sidebar" style="right:1px">
        @include('airshrconnect.mobilepreview', ['mode' => 'scheduler', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])
    </div>
    <!-- edit event modal -->
    <div id="editEventModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit show</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" id="event_form">
                        <div class="form-group">
                            <label for="what" class="col-md-4 control-label">Mix</label>
                            <div class="col-md-20"><input type="text" name="what" id="what" class="form-control" autocomplete="off" placeholder="Mix"></div>
                        </div>
                        <div class="form-group">
                            <label for="who" class="col-md-4 control-label">Talent</label>
                            <div class="col-md-20"><input type="text" name="who" id="who" class="form-control" autocomplete="off" placeholder="Talent"></div>
                        </div>
                        <div class="form-group">
                            <label for="mix_title" class="col-md-4 control-label">Mix Title (Metadata)</label>
                            <div class="col-md-20"><input type="text" name="mix_title" id="mix_title" class="form-control" autocomplete="off" placeholder="e.g. THROWBACK not THROWBACK [FEB 19 SEG 2]"></div>
                        </div>
                        <div class="form-group">
                            <label for="time" class="col-md-4 control-label">Time</label>
                            <div class="col-md-10"><input type="text" name="start_time" id="start_time" class="form-control" placeholder="Start Time"></div>
                            <div class="col-md-10"><input type="text" name="end_time" id="end_time" class="form-control" placeholder="End Time"></div>
                        </div>
                        <div class="form-group">
                            <label for="date" class="col-md-4 control-label">Date</label>
                            <div class="col-md-10"><input type="text" name="start_date" id="start_date" class="form-control" placeholder="Start Date"></div>
                            <div class="col-md-10"><input type="text" name="end_date" id="end_date" class="form-control" placeholder="End Date"></div>
                        </div>
                        <div class="form-group">
                            <label for="repeat" class="col-md-4 control-label">Repeat</label>
                            <div class="col-md-20">
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="1">Mon</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="2">Tue</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="3">Wed</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="4">Thu</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="5">Fri</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="6">Sat</label>
                                <label class="checkbox-inline"><input type="checkbox" name="weekday" value="0">Sun</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="current_date" class="col-md-8 control-label">This single event date</label>
                            <div class="col-md-8"><input type="text" name="current_date" id="current_date" class="form-control" placeholder="Current Date"></div>
                            <div class="col-md-8"><button type="button" id="delete_single_event" class="btn btn-danger">Remove this recurrence</button></div>
                        </div>
                        <input type="hidden" name="content_id" id="content_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <img id="edit_event_loading" src="/img/ajax-loader.gif" style="display:none;">
                    <button type="button" id="update_talk_show" class="btn">Save</button>
                    <button type="button" id="update_single_talk_show" class="btn">Save for only this recurrence</button>
                    <button type="button" id="delete_talk_show" class="btn btn-danger">Remove all recurrences</button>
                    <br/><br/>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

    <!-- new event modal -->
    <div id="newEventModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">New show</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" id="new_event_form">
                        <div class="form-group">
                            <label for="new_what" class="col-md-4 control-label">Mix</label>
                            <div class="col-md-20"><input type="text" name="new_what" id="new_what" class="form-control" autocomplete="off" placeholder="Mix"></div>
                        </div>
                        <div class="form-group">
                            <label for="new_who" class="col-md-4 control-label">Talent</label>
                            <div class="col-md-20"><input type="text" name="new_who" id="new_who" class="form-control" autocomplete="off" placeholder="Talent"></div>
                        </div>
                        <div class="form-group">
                            <label for="new_mix_title" class="col-md-4 control-label">Mix Title (Metadata)</label>
                            <div class="col-md-20"><input type="text" name="new_mix_title" id="new_mix_title" class="form-control" autocomplete="off" placeholder="e.g. THROWBACK not THROWBACK [FEB 19 SEG 2]"></div>
                        </div>
                        <div class="form-group">
                            <label for="new_time" class="col-md-4 control-label">Time</label>
                            <div class="col-md-10"><input type="text" name="new_start_time" id="new_start_time" class="form-control" placeholder="Start Time"></div>
                            <div class="col-md-10"><input type="text" name="new_end_time" id="new_end_time" class="form-control" placeholder="End Time"></div>
                        </div>
                        <div class="form-group">
                            <label for="new_date" class="col-md-4 control-label">Date</label>
                            <div class="col-md-10"><input type="text" name="new_start_date" id="new_start_date" class="form-control" placeholder="Start Date"></div>
                            <div class="col-md-10"><input type="text" name="new_end_date" id="new_end_date" class="form-control" placeholder="End Date"></div>
                        </div>
                        <div class="form-group">
                            <label for="repeat" class="col-md-4 control-label">Repeat</label>
                            <div class="col-md-20">
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="1">Mon</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="2">Tue</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="3">Wed</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="4">Thu</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="5">Fri</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="6">Sat</label>
                                <label class="checkbox-inline"><input type="checkbox" name="new_weekday" value="0">Sun</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <img id="new_event_loading" src="/img/ajax-loader.gif" style="display:none;">
                    <button type="button" id="create_talk_show" class="btn">Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>


        </div>
    </div>



    <!-- update event modal -->
    <div id="updateEventModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Update</h4>
                </div>
                <div class="modal-body">
                    <p>Did you want to update all recurrences of this event or just this event?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" id="update_all" class="btn" data-dismiss="modal" onclick="updateEvent()">Update all</button>
                    <button type="button" id="update_single" class="btn" data-dismiss="modal" onclick="updateSingleEvent()">Update just this event</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="cancel_update">Cancel</button>
                </div>
            </div>


        </div>
    </div>

    @include('airshrconnect.mobileeditor')
@endsection

@section('scripts')
@parent
<script src="/js/fullcalendar/fullcalendar.js" type="text/javascript"></script>
<script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
<script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
<script src="/js/typeaheadjs.js"></script>

<script src="/js/jcrop/js/Jcrop.js"></script>
<script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>

<script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
<script src="/js/nearest/jquery.nearest.min.js"></script>
<script src="/js/mobilepreview.js"></script>
<script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>
<script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

<script src="/js/musicmix.js?v={{ \Config::get('app.ConnectWebAppVersion') }}" type="text/javascript"></script>
<!-- Ready Button -->
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<!--Click events-->
<script>
    function editEvent() {
        $('#editEventModal').modal({backdrop:false});
    }
    $('#create_talk_show').on('click', function () {
        createMix();
    });
    $('#update_talk_show').on('click', function () {
        if(confirm('This will change all recurrences of this talk show from ' + $('#start_date').val() + ' to ' + $('#end_date').val() +'. Are you sure you want to save?')) {
            updateEvent();
        }
    });
    $('#update_single_talk_show').on('click', function () {
        if(confirm('This will change just this recurrence at ' + $('#current_date').val() + '. Are you sure you want to save?')) {
            updateSingleEvent();
        }
    });
    $('#delete_talk_show').on('click', function () {
        if(confirm('This will delete all recurrences of this talk show from ' + $('#start_date').val() + ' to ' + $('#end_date').val() +' . If you want to delete only a single recurrence, please fill out the exceptions form. Are you sure you want to delete all recurrences?')) {
            deleteEvent();
        }
    });
    $('#delete_single_event').on('click', function () {
        if(confirm('This will delete just this recurrence at ' + $('#current_date').val() + '. Are you sure you want to delete?')) {
            deleteSingleEvent();
        }
    });
</script>
<!-- Datepickers -->
<script>
    $('#start_date').datepicker({
        autoclose:  true,
        format: 'yyyy-mm-dd'
    });
    $('#end_date').datepicker({
        autoclose:  true,
        format: 'yyyy-mm-dd'
    });
    $('#start_time').timepicker();
    $('#end_time').timepicker();

    $('#new_start_date').datepicker({
        autoclose:  true,
        format: 'yyyy-mm-dd'
    });
    $('#new_end_date').datepicker({
        autoclose:  true,
        format: 'yyyy-mm-dd'
    });
    $('#new_start_time').timepicker();
    $('#new_end_time').timepicker();
</script>

<script>
    //Override image_dropzone loading overlay
    function showLoading() {
    }

    function hideLoading() {
    }
</script>
@endsection