<style>
#competition_btn_print {
	color: #9b9b9b;
    font-size: 26px;
    line-height: 50px;
    display: inline-block;
    padding: 0px 10px;
    cursor: pointer;
    -webkit-transition: color 0.5s;
    -moz-transition: color 0.5s;
    transition: color 0.5s;
	position: absolute;
	right: 10px;
	top: 10px;
}

#competition_btn_print:hover{
	color: #4A4A4A;
}

</style>

<div id="competition_result_content_wrapper">
@if ($error)
	<p>{{ $error }}</p>
@else
<p>
Competition Result <br/>
{{ $competitionDateTime }}
</p>

<p>
Total applicants: {{$total_applicants}}
</p>

{{$pick_count}} Random Participants <br/>
(Confidential)
<p>
<?php echo $user_list; ?>
</p>
@endif
</div>


<a class="btn-action" title="Print" id="competition_btn_print"><i class="mdi mdi-printer"></i></a>



