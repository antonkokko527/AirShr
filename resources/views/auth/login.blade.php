@extends('layout.basic')

@section('layout')


<div class="login-container">

	<img class="login-back-image" src="/img/Background.png" />
	
	<div class="middle-login">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-24 logo-container">
					<img class="img img-responsive img-logo" src="/img/Logo AirShr.png" />
				</div>
			</div>
			<div class="row">
				<div class="col-sm-24">
					<form class="login-form" method="post">
					  <div class="form-group">
					    <input type="text" class="form-control" id="login_username" name="email" placeholder="Email" autocomplete="off" value="{{ old('username') }}" spellcheck="false">
					  </div>
					  <div class="form-group">
					    <input type="password" class="form-control" id="login_password" name="password" placeholder="Password" autocomplete="off">
					    <!--  To Prevent Autocomplete, I have added dummy text field item. -->
					    <input type="text" style="display:none">
						<input type="password" style="display:none">
					  </div>
					  @if ($errors->has('login_error'))
					  <div class="form-group">
					  	 <p class="error">{{ $errors->first('login_error') }} </p>
					  </div>
					  @endif
					  <div class="form-group text-center login-submit-container">
					  	<input type="image" src="/img/LoginGo.png"></input>
					  </div>
					</form>
				</div>
			</div>
		</div>
	</div>

</div>


@endsection
