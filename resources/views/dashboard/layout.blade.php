<!DOCTYPE html>
<html class="no-js css-menubar" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
    <meta name="description" content="bootstrap admin template">
    <meta name="author" content="">
    <title>AirShr Analytics</title>
    <link rel="apple-touch-icon" href="/dashboard-assets/base/assets/images/apple-touch-icon.png">
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/dashboard-assets/global/css/bootstrap.min.css">
    <link rel="stylesheet" href="/dashboard-assets/global/css/bootstrap-extend.min.css">
    <link rel="stylesheet" href="/dashboard-assets/base/assets/css/site.min.css">
    <!-- Plugins -->
    <link href="/js/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/animsition/animsition.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/asscrollable/asScrollable.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/switchery/switchery.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/intro-js/introjs.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/slidepanel/slidePanel.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/flag-icon-css/flag-icon.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/morris-js/morris.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/chartist-js/chartist.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.css">
    <link rel="stylesheet" href="/dashboard-assets/global/vendor/aspieprogress/asPieProgress.min.css">

    <link rel="stylesheet" href="/css/dashboard.css">
    <!-- Fonts -->
    <link rel="stylesheet" href="/dashboard-assets/global/fonts/web-icons/web-icons.min.css">
    <link rel="stylesheet" href="/dashboard-assets/global/fonts/brand-icons/brand-icons.min.css">
    <link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,300italic'>

    @yield('styles')
    <!--[if lt IE 9]>
    <script src="/dashboard-assets/global/vendor/html5shiv/html5shiv.min.js"></script>
    <![endif]-->
    <!--[if lt IE 10]>
    <script src="/dashboard-assets/global/vendor/media-match/media.match.min.js"></script>
    <script src="/dashboard-assets/global/vendor/respond/respond.min.js"></script>
    <![endif]-->
    <!-- Scripts -->
    <script src="/dashboard-assets/global/vendor/modernizr/modernizr.js"></script>
    <script src="/dashboard-assets/global/vendor/breakpoints/breakpoints.js"></script>
    <script>
        Breakpoints();
    </script>
</head>
<body class="dashboard">
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<nav class="site-navbar navbar navbar-default navbar-fixed-top navbar-mega" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle hamburger hamburger-close navbar-toggle-left hided"
                data-toggle="menubar">
            <span class="sr-only">Toggle navigation</span>
            <span class="hamburger-bar"></span>
        </button>
        <div class="navbar-brand navbar-brand-center">
            <img class="navbar-brand-logo" src="/img/LogoGreen.png" title="Remark">
            <span class="navbar-brand-text"> Analytics</span>
        </div>
        <button type="button" class="navbar-toggle collapsed" data-target="#site-navbar-search"
                data-toggle="collapse">
            <span class="sr-only">Toggle Search</span>
            <i class="icon wb-search" aria-hidden="true"></i>
        </button>
    </div>
    <div class="navbar-container container-fluid">
        <!-- Navbar Collapse -->
        <div class="collapse navbar-collapse navbar-collapse-toolbar" id="site-navbar-collapse">
            <!-- Navbar Toolbar -->
            <ul class="nav navbar-toolbar">
                <li class="hidden-float" id="toggleMenubar">
                    <a data-toggle="menubar" href="#" role="button">
                        <i class="icon hamburger hamburger-arrow-left">
                            <span class="sr-only">Toggle menubar</span>
                            <span class="hamburger-bar"></span>
                        </i>
                    </a>
                </li>
                <li class="hidden-xs" id="toggleFullscreen">
                    <a class="icon icon-fullscreen" data-toggle="fullscreen" href="#" role="button">
                        <span class="sr-only">Toggle fullscreen</span>
                    </a>
                </li>
            </ul>
            <!-- End Navbar Toolbar -->
            <!-- Navbar Toolbar Right -->
            <ul class="nav navbar-toolbar navbar-right navbar-toolbar-right" style="display:none">
                <li id="toggleChat">
                    <a data-toggle="site-sidebar" href="javascript:void(0)" title="Chat" data-url="../site-sidebar.tpl">
                        <i class="icon wb-chat" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
            <!-- End Navbar Toolbar Right -->
        </div>
        <!-- End Navbar Collapse -->
    </div>
</nav>
<div class="site-menubar">
    <div class="site-menubar-body">
        <div>
            <div>
                <ul class="site-menu">
                    <li class="site-menu-category">Menu</li>
                    @yield('menubar')
                    <!--<li id="healthMenu" class="site-menu-item">
                        <a href="javascript:void(0)" onclick="showHealth()">
                            <i class="site-menu-icon wb-pluse" aria-hidden="true"></i>
                            <span class="site-menu-title">AirShr Current Status</span>
                        </a>
                    </li>-->
                    <li class="site-menu-item has-sub">
                        <a href="/content">
                            <i class="site-menu-icon wb-layout" aria-hidden="true"></i>
                            <span class="site-menu-title">Back to AirShr Connect</span>
                        </a>
                    </li>
                    <li class="site-menu-item has-sub" style="display:none">
                        <a href="javascript:void(0)">
                            <i class="site-menu-icon wb-plugin" aria-hidden="true"></i>
                            <span class="site-menu-title">Menu</span>
                            <span class="site-menu-arrow"></span>
                        </a>
                        <ul class="site-menu-sub">
                            <li class="site-menu-item">
                                <a class="animsition-link" href="javascript:void(0)">
                                    <span class="site-menu-title">Sub Menu</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="site-menubar-section" style="display:none">
                    <h5>
                        Milestone
                        <span class="pull-right">30%</span>
                    </h5>
                    <div class="progress progress-xs">
                        <div class="progress-bar active" style="width: 30%;" role="progressbar"></div>
                    </div>
                    <h5>
                        Release
                        <span class="pull-right">60%</span>
                    </h5>
                    <div class="progress progress-xs">
                        <div class="progress-bar progress-bar-warning" style="width: 60%;" role="progressbar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="site-menubar-footer">
        <a href="/auth/logout" data-placement="top" data-toggle="tooltip" data-original-title="Logout">
            <span class="icon wb-power" aria-hidden="true"></span>
        </a>
    </div>
</div>
<!-- Page -->
<div class="page animsition" id="dashboard">
    @yield('content')
</div>
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

<script>
    var contentTypeList = new Array();
    @if (isset($content_type_list))
            @foreach ($content_type_list as $key => $val)
            contentTypeList[{{$key}}] = '{{$val}}';
            @endforeach
            @endif

    var GLOBAL = {
                STATION_ID: '{{\Auth::User()->station->id}}',
                STATION_TIMEZONE: '{{\Auth::User()->station->getStationTimezone()}}',
                CLIENT_LIST: [],
                CLIENT_TRADING_NAME_LIST: []
            };

</script>


<!-- Core  -->
<script src="/dashboard-assets/global/vendor/jquery/jquery.js"></script>
<script src="/dashboard-assets/global/vendor/bootstrap/bootstrap.js"></script>
<script src="/dashboard-assets/global/vendor/animsition/animsition.js"></script>
<script src="/dashboard-assets/global/vendor/asscroll/jquery-asScroll.js"></script>
<script src="/dashboard-assets/global/vendor/mousewheel/jquery.mousewheel.js"></script>
<script src="/dashboard-assets/global/vendor/asscrollable/jquery.asScrollable.all.js"></script>
<script src="/dashboard-assets/global/vendor/ashoverscroll/jquery-asHoverScroll.js"></script>
<!-- Plugins -->
<script src="/js/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="/js/moment/moment.js"></script>
<script src="/js/moment/moment-timezone-with-data.min.js"></script>
<script src="/dashboard-assets/global/vendor/switchery/switchery.min.js"></script>
<script src="/dashboard-assets/global/vendor/intro-js/intro.js"></script>
<script src="/dashboard-assets/global/vendor/screenfull/screenfull.js"></script>
<script src="/dashboard-assets/global/vendor/slidepanel/jquery-slidePanel.js"></script>
<script src="/dashboard-assets/global/vendor/chartist-js/chartist.js"></script>
<script src="/dashboard-assets/global/vendor/raphael/raphael-min.js"></script>
<script src="/dashboard-assets/global/vendor/morris-js/morris.min.js"></script>
<script src="/dashboard-assets/global/vendor/matchheight/jquery.matchHeight-min.js"></script>
<script src="/dashboard-assets/global/vendor/chartist-plugin-tooltip/chartist-plugin-tooltip.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBUsctJ35AdCaivOqRjz3jfF6ehVlKbm1A&libraries=visualization"></script>
<script src="/dashboard-assets/global/vendor/aspieprogress/jquery-asPieProgress.min.js"></script>
<!-- Scripts -->
<script src="/dashboard-assets/global/js/core.js"></script>
<script src="/dashboard-assets/base/assets/js/site.js"></script>
<script src="/dashboard-assets/base/assets/js/sections/menu.js"></script>
<script src="/dashboard-assets/base/assets/js/sections/menubar.js"></script>
<script src="/dashboard-assets/base/assets/js/sections/gridmenu.js"></script>
<script src="/dashboard-assets/base/assets/js/sections/sidebar.js"></script>
<script src="/dashboard-assets/global/js/configs/config-colors.js"></script>
<script src="/dashboard-assets/base/assets/js/configs/config-tour.js"></script>
<script src="/dashboard-assets/global/js/components/asscrollable.js"></script>
<script src="/dashboard-assets/global/js/components/animsition.js"></script>
<script src="/dashboard-assets/global/js/components/slidepanel.js"></script>
<script src="/dashboard-assets/global/js/components/switchery.js"></script>
<script src="/dashboard-assets/global/js/components/matchheight.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.24/vue.min.js"></script>
<script src="/js/app.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

@yield('scripts')

</body>
</html>