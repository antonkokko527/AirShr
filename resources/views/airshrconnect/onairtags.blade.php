<div class="past-airtag-container" id="past-airtag-container">
		
	<table @if ($mode == 'onair') width="100%" @else width="98%" @endif id="past-tags-list-table" class="content-table display">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				@if ($mode == 'onair')
				<th></th>
				@endif
				<th></th>
			</tr>
		</thead>
	</table>

</div>

<div class="current-airtag-container" id="current-airtag-container">

	<table @if ($mode == 'onair') width="100%" @else width="98%" @endif id="current-airtag-table" class="content-table display">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				@if ($mode == 'onair')
				<th></th>
				@endif
				<th></th>
			</tr>
		</thead>
	</table>
	
</div>

<div class="preview-airtag-container" id="preview-airtag-container">

	<table @if ($mode == 'onair') width="100%" @else width="98%" @endif id="preview-tags-list-table" class="content-table display">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th></th>
				@if ($mode == 'onair')
				<th></th>
				@endif
				<th></th>
			</tr>
		</thead>
	</table>

</div>