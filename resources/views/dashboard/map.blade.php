@extends('dashboard.layout')

@section('menubar')
    <li id="dashboardMenu" class="site-menu-item">
        <a href="/dashboard">
            <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
            <span class="site-menu-title">Dashboard</span>
        </a>
    </li>
    <li id="mapMenu" class="site-menu-item active">
        <a href="/dashboard/map">
            <i class="site-menu-icon wb-map" aria-hidden="true"></i>
            <span class="site-menu-title">Map</span>
        </a>
    </li>
    <li id="musicRatingMenu" class="site-menu-item">
        <a href="/dashboard/musicRatings">
            <i class="site-menu-icon wb-musical" aria-hidden="true"></i>
            <span class="site-menu-title">Music Rating</span>
        </a>
    </li>
@endsection

@section('content')
    <!-- Page Content -->
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <!-- Map Widget -->
                <div id="mapWidget" class="widget">

                    <div class="widget-header">
                                    <span class="font-size-16 margin-bottom-0">Click Locations -
                                        Start: <span id="start_date_for_map_text" class="font-size-14 margin-bottom-0" style="font-weight:bold;">01 Mar 2016</span>
                                        <input class="form-control" type="text" id="start_date_for_map" style="visibility:hidden;position:absolute;width:0;left:250px;top:20px">
                                        <button class="btn btn-default" id="map_range_button">Select Range</button>
                                        <span id="end_date_for_map_wrapper" style="display:none">
                                            <span> - </span>
                                            End: <span id="end_date_for_map_text" class="font-size-14 margin-bottom-0" style="font-weight:bold;">01 Mar 2016</span>
                                            <input class="form-control" type="text" id="end_date_for_map" style="visibility:hidden;position:absolute;width:0;left:350px;top:20px;">
                                        </span>
                                        <a class="help-tooltip" href="#" data-toggle="tooltip"
                                           data-placement="bottom" title="Each red dot is a saved moment">
                                            <i class="wb-help-circle blue-400"></i>
                                        </a>
                                    </span>

                                    <span id="mapWidgetButtons" class="btn-group inline" style="float:right;">
                                        <button id="all_day_button" class="btn btn-default">All Day</button>
                                        <button id="breakfast_button" class="btn btn-default">Breakfast </button>
                                        <button id="morning_button" class="btn btn-default">Morning</button>
                                        <button id="afternoon_button" class="btn btn-default">Afternoon</button>
                                        <button id="drive_button" class="btn btn-default">Drive</button>
                                        <button id="evening_button" class="btn btn-default">Evening</button>
                                        <button id="late_button" class="btn btn-default">Late</button>
                                        <button id="heatmap_button" class="btn btn-primary">Heat Map</button>
                                    </span>
                    </div>
                    <div class="widget-content">
                        <br />
                        <div class="col-sm-12 col-xs-12">
                            <div id="map" class="map" style="height:700px;"></div>
                            <input id="start_time_map" type="hidden" value="0">
                            <input id="end_time_map" type="hidden" value="24">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/map.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection