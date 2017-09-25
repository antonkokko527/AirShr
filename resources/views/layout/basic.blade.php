<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    
    <title>AirShr Connect</title>

    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/js/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="/js/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet">
    
    @yield('styles')
    
    <!-- Main Css -->
    <link href="/css/airshrconnect.css?v={{ \Config::get('app.ConnectWebAppVersion') }}" rel="stylesheet">
    
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body @yield('bodyClass')>
    
    @yield('layout')
    
    <div class="hide loading" id="globalLoadingIcon">
		<img src="/img/ajax-loader.gif" class="loader-img">
	</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Vue.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.24/vue.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/bootstrap3-typeahead.min.js"></script>
    <!--  <script src="/js/typeahead.bundle.js"></script> -->
   	<script src="/js/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
   	<script src="/js/bootbox/bootbox.min.js"></script>
    <script src="/js/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
    
    @yield('scripts')
    
  </body>
</html>