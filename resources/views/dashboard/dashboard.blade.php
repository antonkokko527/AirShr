@extends('dashboard.layout')

@section('menubar')
    <li id="dashboardMenu" class="site-menu-item active">
        <a href="/dashboard">
            <i class="site-menu-icon wb-dashboard" aria-hidden="true"></i>
            <span class="site-menu-title">Dashboard</span>
        </a>
    </li>
    <li id="mapMenu" class="site-menu-item">
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
    <div id="dashboardHeader" class="page-header">
        <h1 class="page-title">{{\Auth::User()->station->station_abbrev}}'s AirShr Dashboard</h1><p>Last updated at <span id="updated_time"></span></p>
    </div>
    <!-- Page Content -->
    <div class="page-content container-fluid">
        <div id = "dashboardWrapper">
        <!-- First Row -->
                <!-- Counter Widgets -->
            <div class="row" data-plugin="matchHeight">
                <div class="col-xlg-3 col-lg-3">
                    <div id="firstRowCountsContainer" data-mh="firstRowDashboard">
                        <div class="widget">
                            <div class="widget-content padding-30 bg-white">
                                <div class="counter counter-md text-left">
                                    <div class="counter-label margin-bottom-5 font-size-16">
                                        Total Users <br />(6.5% drop off from download to registration as at 13 April 16)
                                    </div>
                                    <div class="counter-number-group margin-bottom-10">
                                        <span id="totalUsers" class="counter-number">No Data</span>
                                    </div>
                                    <div class="counter-label">
                                        <div class="counter counter-sm text-left">
                                            <div id="usersExtra" class="counter-number-group">
                                                <span class="counter-icon blue-600 margin-right-5"><i class="wb-graph-up"></i></span>
                                                <span id="totalUsersDiff" class="counter-number">No Data</span>
                                                <span class="counter-number-related">more than yesterday</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="widget widget-shadow widget-completed-options">
                            <div class="widget-content padding-30">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="counter text-left">
                                            <div class="counter-label font-size-16 margin-top-10">
                                                Total number of social shares
                                            </div>
                                            <div class="counter-number font-size-40 margin-top-10" id="social_shares">
                                                15
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 col-xlg-9">
                    <div id="clicksPerUserWidget" class="widget widget-shadow example-responsive" data-mh="firstRowDashboard">
                        <div class="widget-header">
                            <span class="font-size-16 margin-bottom-0" style="color:#000000">
                                Listener Engagement (Unique Listeners)
                                <a class="help-tooltip" href="#" data-toggle="tooltip" data-placement="bottom"
                                   title=" Unique listeners using AirShr once per week, twice per week or three or more times per week.">
                                    <i class="wb-help-circle blue-400"></i>
                                </a>
                            </span>
                            <ul class="list-inline pull-right" style="padding-right:10px;">
                                <li>
                                    <span class="icon wb-medium-point blue-600 margin-right-5"></span>3+ Moments</li>
                                <li>
                                    <span class="icon wb-medium-point purple-600 margin-right-5"></span>2 Moments</li>
                                <li>
                                    <span class="icon wb-medium-point blue-grey-300 margin-right-5"></span>1 Moment</li>
                            </ul>
                        </div>
                        <div class="widget-content">
                            <div class="ct-chart" style="height:380px; background-color:white;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- End First Row -->
            <!-- Second Row -->
                <div class="row" data-plugin="matchHeight">
                    <div class="col-xlg-3 col-lg-3">
                        <div id="momentCountsContainer" data-mh="secondRowDashboard">
                            <div class="widget">
                                <div class="widget-content padding-30 bg-white">
                                    <div class="counter counter-md text-left">
                                        <div class="counter-label margin-bottom-5 font-size-16">
                                            Moments Today
                                        </div>
                                        <div class="counter-number-group margin-bottom-10">
                                            <span id="moments_today_count" class="counter-number font-size-40">No Data</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="widget widget-shadow widget-completed-options">
                                <div class="widget-content padding-30">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div class="counter text-left">
                                                <div class="counter-label font-size-16 margin-top-10">
                                                    Moments this Month to Date
                                                </div>
                                                <div class="counter-number font-size-40 margin-top-10" id="moments_month_count">
                                                    0
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{--<div class="widget widget-shadow widget-completed-options">--}}
                                {{--<div class="widget-content padding-30">--}}
                                    {{--<div class="row">--}}
                                        {{--<div class="col-xs-6">--}}
                                            {{--<div class="counter text-left">--}}
                                                {{--<div class="counter-label margin-top-10">Moments Today--}}
                                                {{--</div>--}}
                                                {{--<div class="counter-number font-size-40 margin-top-10" id="moments_today_count">--}}
                                                    {{--No Data--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                            {{--<div class="widget widget-shadow widget-completed-options">--}}
                                {{--<div class="widget-content padding-30">--}}
                                    {{--<div class="row">--}}
                                        {{--<div class="col-xs-6">--}}
                                            {{--<div class="counter text-left">--}}
                                                {{--<div class="counter-label margin-top-10">Moments this Month to Date--}}
                                                {{--</div>--}}
                                                {{--<div class="counter-number font-size-40 margin-top-10" id="moments_month_count">--}}
                                                    {{--No Data--}}
                                                {{--</div>--}}
                                            {{--</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                            {{--</div>--}}
                         </div>
                    </div>
                    <!-- End Counter Widgets -->
                    <!-- Graph Widget -->
                    <div class="col-lg-9 col-xlg-9">
                        <div id="activityWidget" class="widget widget-shadow example-responsive" data-mh="secondRowDashboard">
                            <div class="widget-header">
                                <p class="font-size-16 margin-bottom-0">Total Moments Saved (Year to Date) = <span id="total_moments"></span>
                                    <a class="help-tooltip" href="#" data-toggle="tooltip"
                                       data-placement="bottom" title="The total number of times someone pressed the AirShr button in the app to save a radio moment between 6am and 10pm each day. Outside of these times moments are not included due to AirShr conducting testing and maintenance">
                                        <i class="wb-help-circle blue-400"></i>
                                    </a>
                                </p>
                            </div>
                            <div class="widget-content">
                                <div class="ct-chart"></div>
                            </div>
                        </div>
                    </div>
                    {{--<div class="col-lg-9 col-xlg-9">--}}
                        {{--<div id="activityWidget" class="widget widget-shadow example-responsive" data-mh="secondRowDashboard">--}}
                            {{--<div class="widget-content padding-20 padding-bottom-25">--}}
                                {{--<div class="row padding-bottom-40">--}}
                                    {{--<div class="col-md-6 col-sm-12">--}}
                                        {{--<div class="counter text-left padding-left-10">--}}
                                            {{--<div class="counter-label">Total Moments Saved (Year to Date)</div>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}
                                {{--<div class="ct-chart"></div>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                </div>
                <!-- End Graph Widget -->
                <!-- End Second Row -->
                <!-- Third Row -->
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div id="timeOfDayWidget" class="widget widget-shadow example-responsive">
                            <div class="widget-header">
                                <span class="font-size-16 margin-bottom-0" style="color:#000000">Moments Saved by Time of Day -
                                    <span id="date_for_tod_text" style="font-weight:bold"></span>
                                    <input class="form-control" type="text" id="date_for_tod" style="visibility:hidden;position:absolute;width:0;top:10px;left:300px;">
                                    <button class="btn btn-default" id="tod_range_button">Select Range</button>
                                    <span id="end_date_for_tod_wrapper" style="display:none">
                                        <span> - </span>
                                        End: <span id="end_date_for_tod_text" class="font-size-16 margin-bottom-0" style="font-weight:bold;">01 Mar 2016</span>
                                        <input class="form-control" type="text" id="end_date_for_tod" style="visibility:hidden;position:absolute;width:0;left:350px;top:20px;">
                                    </span>
                                </span>
                                <ul class="list-inline pull-right" style="padding-right:10px;">
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#DD218B;"></span>Music</li>
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#60C3EC;"></span>Talk</li>
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#50E3C2;"></span>Ad</li>
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#F5A623;"></span>News</li>
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#3583ca;"></span>Traffic</li>
                                    <li>
                                        <span class="icon wb-medium-point margin-right-5" style="color:#000000;"></span>Promo</li>
                                </ul>
                                <div>Unprompted Moments (<span id="total_unprompted_moments"></span>)</div>
                            </div>
                            <div class="widget-content">
                                <div class="ct-chart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div id="competitionAndVoteWidget" class="widget widget-shadow example-responsive">
                            <div class="widget-header">
                                <span class="margin-bottom-0" style="color:#000000">Competition and Vote Moments (<span id="total_comp_and_vote_moments"></span>)</span>
                            </div>
                            <div class="widget-content">
                                <table id="competition_and_vote_table" class="table">
                                    <tr>
                                        <th>Type</th>
                                        <th>6am</th>
                                        <th>7am</th>
                                        <th>8am</th>
                                        <th>9am</th>
                                        <th>10am</th>
                                        <th>11am</th>
                                        <th>12pm</th>
                                        <th>1pm</th>
                                        <th>2pm</th>
                                        <th>3pm</th>
                                        <th>4pm</th>
                                        <th>5pm</th>
                                        <th>6pm</th>
                                        <th>7pm</th>
                                        <th>8pm</th>
                                        <th>9pm</th>
                                        <th>10pm</th>
                                    </tr>
                                    <tr>
                                        <td>Comp</td>
                                        <td id="comp-0"></td>
                                        <td id="comp-1"></td>
                                        <td id="comp-2"></td>
                                        <td id="comp-3"></td>
                                        <td id="comp-4"></td>
                                        <td id="comp-5"></td>
                                        <td id="comp-6"></td>
                                        <td id="comp-7"></td>
                                        <td id="comp-8"></td>
                                        <td id="comp-9"></td>
                                        <td id="comp-10"></td>
                                        <td id="comp-11"></td>
                                        <td id="comp-12"></td>
                                        <td id="comp-13"></td>
                                        <td id="comp-14"></td>
                                        <td id="comp-15"></td>
                                        <td id="comp-16"></td>
                                    </tr>
                                    <tr>
                                        <td>Vote</td>
                                        <td id="vote-0"></td>
                                        <td id="vote-1"></td>
                                        <td id="vote-2"></td>
                                        <td id="vote-3"></td>
                                        <td id="vote-4"></td>
                                        <td id="vote-5"></td>
                                        <td id="vote-6"></td>
                                        <td id="vote-7"></td>
                                        <td id="vote-8"></td>
                                        <td id="vote-9"></td>
                                        <td id="vote-10"></td>
                                        <td id="vote-11"></td>
                                        <td id="vote-12"></td>
                                        <td id="vote-13"></td>
                                        <td id="vote-14"></td>
                                        <td id="vote-15"></td>
                                        <td id="vote-16"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            <!-- End Third Row -->
            <!-- Fourth Row -->
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <!-- Content Types Widget -->
                        <div id="contentTypesWidget" class="widget widget-shadow">
                            <div class="widget-header">
                                <p class="font-size-16 margin-bottom-0">Breakdown of Activity by Content Type</p>
                            </div>
                            <div class="widget-content">
                                <div class="col-sm-5 col-xs-12">
                                    <!-- Content Types Table -->
                                    <table class="table table-analytics margin-bottom-0">
                                        <thead>
                                        <tr>
                                            <th>Content Type</th>
                                            <th>Total</th>
                                            <th>Percentage</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td id = "music_text" style="font-weight: bold">
                                                Music
                                            </td>
                                            <td id = "music_value">
                                                0
                                            </td>
                                            <td id = "music_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "talk_text" style="font-weight: bold">
                                                Talk
                                            </td>
                                            <td id = "talk_value">
                                                0
                                            </td>
                                            <td id = "talk_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "advertisment_text" style="font-weight: bold">
                                                Ad
                                            </td>
                                            <td id = "ad_value">
                                                0
                                            </td>
                                            <td id = "ad_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "news_text" style="font-weight: bold">
                                                News
                                            </td>
                                            <td id = "news_value">
                                                0
                                            </td>
                                            <td id = "news_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "traffic_text" style="font-weight: bold">
                                                Traffic
                                            </td>
                                            <td id = "traffic_value">
                                                0
                                            </td>
                                            <td id = "traffic_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "promo_text" style="font-weight: bold">
                                                Promotion
                                            </td>
                                            <td id = "promo_value">
                                                0
                                            </td>
                                            <td id = "promo_percent">
                                                0%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id = "total_text" style="font-weight:bold">
                                                TOTAL
                                            </td>
                                            <td id = "total_value" style="font-weight:bold">
                                                0
                                            </td>
                                            <td></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <!-- End Content Types Table -->
                                </div>
                                <div class="col-sm-5 col-xs-12 padding-horizontal-0">
                                    <!-- MorrisDonut -->
                                    <div id="contentTypesDonut"></div>
                                    <!-- End MorrisDonut -->
                                </div>
                                <div class="col-sm-2 col-xs-12 padding-horizontal-0">
                                    <div id="contentTypeButtons">
                                        <button id="all_time_button" class="btn btn-default btn-block">All Time</button> <br />
                                        <button id="last_week_button" class="btn btn-default btn-block">Last Week</button> <br />
                                        <button id="this_week_button" class="btn btn-default btn-block">This Week</button> <br />
                                        <input class="form-control" type="text" id="date_for_ctdonut" style="visibility:hidden;position:absolute;width:0;">
                                        <button id="date_for_ctdonut_button" class="btn btn-default btn-block">Select Date</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Content Types Widget -->
                    </div>
                </div>
            <!-- End Fourth Row -->
            <!-- Fifth Row -->
            <div class="row" data-plugin="matchHeight">
                <div class="col-xlg-3 col-lg-3">
                    <div id="sourceWidgetsContainer" data-mh="fifthRowDashboard">
                        <div id="sourceDonutWidget" class="widget">
                            <div class="widget-header">
                                <p class="font-size-16 margin-bottom-0">Source of AirShr'd Moments
                                    <a class="help-tooltip" href="#" data-toggle="tooltip"
                                       data-placement="bottom" title=" AirShr detects if the listener saved a moment via FM or a digital source like a website radio player, radio app or while streaming Nova1069 from within the AirShr app">
                                        <i class="wb-help-circle blue-400"></i>
                                    </a>
                                </p>
                            </div>
                            <div class="widget-content padding-15 bg-white">
                                <div id="sourceDonut"></div>
                            </div>
                        </div>
                        <div class="widget">
                            <div class="widget-content padding-30 bg-white">
                                <div class="counter counter-md text-left">
                                    <div class="counter-label margin-bottom-5 font-size-16">Listeners Using AirShr to Stream
                                        <a class="help-tooltip" href="#" data-toggle="tooltip"
                                           data-placement="bottom" title="Number of people who used the AirShr app to stream Nova1069">
                                            <i class="wb-help-circle blue-400"></i>
                                        </a>
                                    </div>
                                    <div class="counter-number-group margin-bottom-10">
                                        <span class="counter-number" id="stream_count">No Data</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xlg-9 col-lg-9">
                    <div id="timeStreamWidget" class="widget" data-mh="fifthRowDashboard">
                        <div class="widget-header">
                            <p class="font-size-16 margin-bottom-0">Time Listeners Using AirShr to Stream NOVA
                                <a class="help-tooltip" href="#" data-toggle="tooltip"
                                   data-placement="bottom" title="Times when people pressed ‘Play’ to stream Nova1069 throughout the day">
                                    <i class="wb-help-circle blue-400"></i>
                                </a>
                            </p>
                        </div>
                        <div class="widget-content">
                            <div class="ct-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- End Fifth row -->
        </div>
    </div>

<!-- Modal -->
<div id="popularTags" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <button type="button" data-toggle="modal" data-target="#emailModal" style="float:right; margin-right:10px;"><i class="icon wb-envelope"></i></button>
                <h4 class="modal-title"><span id="contentTypeDot">&#9679;</span>  Popular <span id="contentTypeTagInfo">Music</span></h4>
                <h4 class="modal-title"><span id="dateTagInfo"></span> @ <span id="hourTagInfo"></span></h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Who</th>
                        <th>What</th>
                        <th>Time Played</th>
                        <th>Popularity</th>
                    </tr>
                    </thead>
                    <tbody id="popularTagsTable">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<div id="emailModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <img id="email_sending_icon" src="/img/ajax-loader.gif" style="float:right; margin-right:10px; display:none;">
                <i class="icon wb-check" id="email_sent_icon" style="color:green; float:right; margin-right:10px; display:none;"></i>
                <i class="icon wb-close" id="email_failed_icon" style="color:red; float:right; margin-right:10px; display:none;"></i>
                <h4 class="modal-title">Send via email</h4>
            </div>
            <div class="modal-body">
                <div id="email_form_group" class="form-group">
                    <label class="control-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter email address">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="sendEmail()">Send Email</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>
    <!-- End Page Content -->
<!-- End Page -->
<!-- Footer -->
<!--
<footer class="site-footer">
    <div class="site-footer-legal">© 2015 <a href="http://themeforest.net/item/remark-responsive-bootstrap-admin-template/11989202">Remark</a></div>
    <div class="site-footer-right">
        Crafted with <i class="red-600 wb wb-heart"></i> by <a href="http://themeforest.net/user/amazingSurge">amazingSurge</a>
    </div>
</footer>
-->
@endsection
@section('scripts')
    <script src="/js/dashboard.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection