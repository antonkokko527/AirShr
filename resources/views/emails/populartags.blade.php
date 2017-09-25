<h4 class="modal-title"><span id="contentTypeDot" style="color:{{$content_type_color}};">&#9679;</span>  Popular <span id="contentTypeTagInfo">{{$content_type_name}}</span></h4>
<h4 class="modal-title"><span id="dateTagInfo">{{$date}}</span></h4>
<table class="table">
    <thead>
    <tr>
        <th style="border-bottom:1px solid #E4EAEC; padding:8px; text-align: left;">Who</th>
        <th style="border-bottom:1px solid #E4EAEC; padding:8px; text-align: left;">What</th>
        <th style="border-bottom:1px solid #E4EAEC; padding:8px; text-align: left;">Time Played</th>
        <th style="border-bottom:1px solid #E4EAEC; padding:8px; text-align: left;">Popularity</th>
    </tr>
    </thead>
    <tbody id="popularTagsTable">
        @foreach($tags as $tag)
            <tr>
                <td class="who" style="border-bottom:1px solid #E4EAEC; padding:8px;">{{$tag->who}}</td>
                <td class="what" style="border-bottom:1px solid #E4EAEC; padding:8px;">{{$tag->what}}</td>
                <td class="time-played" style="border-bottom:1px solid #E4EAEC; padding:8px;">{{\Carbon\Carbon::createFromTimestamp(getSecondsFromMili($tag->tag_timestamp), $timezone)->format('h:ia')}}</td>
                <td class="event-count" style="border-bottom:1px solid #E4EAEC; padding:8px;">{{$tag->event_count}}</td>
            </tr>
        @endforeach
    </tbody>
</table>