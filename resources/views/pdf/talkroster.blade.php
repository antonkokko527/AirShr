<!doctype html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Material Instruction</title>
<style>
	table td{
		padding: 20px;
	}
</style>
</head>
<body style="position: relative; padding: 0; width: 27cm; height: 20cm; margin: 0 auto; color: black; background: white;  font-family: Arial, sans-serif;  font-size: 12pt;  font-family: Arial;">
	
		<table style="width: 27cm; padding: 0; margin: 0;">
			<tr>
				<td style="text-align: left; padding: 0" width="50%">
					<span style="font-size: 24pt;">{{$content->station ? $content->station->station_short : ''}}</span>
					<span style="margin-left: 2cm">Account Exec: {{$content->executive ? $content->executive->executive_name : ''}}</span>
				</td>
				<td style="text-align: right; padding: 0" width="50%">
					<span>Created: {{date('d-M H:i', strtotime($content->created_at))}}</span>				
				</td>
			</tr>
		</table>
	
	
	<p>
		<span style="font-size: 20pt; font-weight: bold;">MATERIAL INSTRUCTIONS</span>
		<span style="margin-left: 2cm; font-size: 16pt;">{{\App\ConnectContent::GetContentVersionString($content->content_version)}}</span>
	</p>
	<p>
		<span style="font-weight: bold">Order Number/ATB</span>
		<span style="margin-left: 0.5cm">{{$content->atb_date}}</span>
		<span style="margin-left: 1cm">Line number</span>
		<span style="margin-left: 0.5cm">{{$content->content_line_number ? '(' . $content->content_line_number . ')' : ''}}</span>
	</p>
	<p>
		<span>Other details</span>
		<span style="margin-left: 0.5cm"></span>
	</p>

		<table style="width: 27cm; padding: 0; margin: 0; border-color: black; border-collapse: collapse; table-layout: fixed; word-break: break-all; word-wrap: break-word;" border="1px"> 
			<tr>
				<td style="text-align: right; padding: 3px 10px; font-weight: bold;" width="15%">Client:</td>
				<td style="text-align: left; padding: 3px 10px; font-weight: bold; font-size: 16pt; " width="45%">{{ $content->contentClient ? $content->contentClient->client_name : ''}}</td>
				<td style="text-align: right; padding: 3px 10px;" width="10%">Product:</td>
				<td style="text-align: left; padding: 3px 10px;" width="30%">{{ $content->contentProduct ? $content->contentProduct->product_name : ''}}</td>
			</tr>
			<tr>
				<td style="text-align: right; padding: 3px 10px;" width="15%">Contact:</td>
				<td style="text-align: left; padding: 3px 10px;" width="45%">{{ $content->content_contact}}</td>
				<td style="text-align: right; padding: 3px 10px;" width="10%">Mobile:</td>
				<td style="text-align: left; padding: 3px 10px;" width="30%"></td>
			</tr>
			<tr>
				<td style="text-align: right; padding: 3px 10px; font-weight: bold;" width="15%" rowspan="2">Email:</td>
				<td style="text-align: left; padding: 3px 10px;" width="45%" rowspan="2">{{ $content->content_email }}</td>
				<td style="text-align: right; padding: 3px 10px; font-weight: bold;" width="10%">Phone:</td>
				<td style="text-align: left; padding: 3px 10px;" width="30%">{{ $content->content_phone }}</td>
			</tr>
			<tr>
				<td style="text-align: right; padding: 3px 10px; font-weight: bold;" width="10%">Fax:</td>
				<td style="text-align: left; padding: 3px 10px;" width="30%"></td>
			</tr>
			<tr>
				<td style="text-align: right; padding: 3px 10px;" width="15%">Instructions:</td>
				<td style="text-align: left; padding: 3px 10px;" width="85%" colspan="3">{{ $content->content_instructions}}</td>
			</tr>
			<tr>
				<td style="text-align: right; padding: 3px 10px;" width="15%">Voices:</td>
				<td style="text-align: left; padding: 3px 10px;" width="85%" colspan="3">{{ $content->content_voices}}</td>
			</tr>
		</table>
		<br/>
		<br/>
		<table style="width: 27cm; padding: 0; margin: 0; border-color: black; border-collapse: collapse; table-layout: fixed; word-break: break-all; word-wrap: break-word;" border="1px">
			<tr>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="10%" nowrap>Rec or<br/>Live</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="12%" nowrap>Start</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="12%" nowrap>Finish</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="16%" nowrap>Key No.</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="5%" nowrap>Dur</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="10%" nowrap>Rotate</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="25%" nowrap>Instructions</td>
				<td style="text-align: center; padding: 3px 10px; font-weight: bold; background-color: #eeeeee" width="10%" nowrap>Cart No.</td>
			</tr>
			@foreach ($content->getSubContents() as $ad)
			<tr>
				<td style="text-align: center; padding: 3px 10px;" width="10%" nowrap>{{ \App\ConnectContent::GetRecTypeString($ad->content_rec_type) }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="12%" nowrap>{{ formatDateByParse("d-m-Y", $ad->start_date) }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="12%" nowrap>{{ formatDateByParse("d-m-Y", $ad->end_date) }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="16%">{{ $ad->ad_key }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="5%">{{ $ad->ad_length + 0 }}s</td>
				<td style="text-align: center; padding: 3px 10px;" width="10%" nowrap>{{ \App\ConnectContent::GetPercentString($ad->content_percent) }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="25%">{{ $ad->content_instructions }}</td>
				<td style="text-align: center; padding: 3px 10px;" width="10%">&nbsp;</td>
			</tr>
			@endforeach
		</table>

</body>
</html>