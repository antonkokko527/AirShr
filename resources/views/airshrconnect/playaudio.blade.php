@if (isset($error)) 
	<p>{{ $error }}</p>
@else
	<audio controls preload="none" style="width:480px;">
	 	<source src="{{ $audio }}" type="audio/mp3" />
	 	<p>Your browser does not support HTML5 audio.</p>
	 </audio>	
@endif