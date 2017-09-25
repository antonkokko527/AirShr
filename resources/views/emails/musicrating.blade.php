<body>
Here are the music rating results for  "{{ $song }}" between {{$dateRange}}:

<img src="<?php echo $message->embedData($image, $song.' '.$dateRange.'.png'); ?>">

Last updated at {{ $lastUpdated }}
</body>