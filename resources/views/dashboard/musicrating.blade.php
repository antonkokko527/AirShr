@extends('dashboard.layout')

@section('styles')
    <link rel="stylesheet" href="/css/musicratingdashboard.css">
    <link href="/js/mediaelement/mediaelementplayer.min.css" rel="stylesheet">
@endsection

@section('menubar')
    <li id="dashboardMenu" class="site-menu-item">
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
    <li id="musicRatingMenu" class="site-menu-item active">
        <a href="/dashboard/musicRatings">
            <i class="site-menu-icon wb-musical" aria-hidden="true"></i>
            <span class="site-menu-title">Music Rating</span>
        </a>
    </li>
@endsection

@section('content')
    <!-- Page Content -->
    <div class="page-content container-fluid">
        <div id="musicRating">
            <div class="row">
                <div class="col-sm-12">
                    <div class="widget widget-shadow">
                        <div class="widget-header">
                            <div class="row">
                            <div class="font-size-20 margin-bottom-0 black col-sm-8">
                                The Ingham-Myers Charts
                            </div>
                            <div class="font-size-16 margin-bottom-0 black col-sm-8">
                                Music Ratings (Last 7 Days)
                            </div>
                            <span class="font-size-14 margin-bottom-0 black col-sm-4 pull-right">
                            <button class="btn btn-default mail-button" v-on:click="export" style="float:right;"><img src="/img/Microsoft_Excel_2013_logo.svg" style="width:30px;"></button>
                            </span>
                        </div>
                        </div>
                        <div class="widget-content" style="min-height:1000px;">
                            <div class="white padding-15">
                                <form v-on:submit.prevent="loadSongs">
                                    <div class="form-group col-sm-4">
                                        <label for="search" class="black">Artist or Title</label>
                                        <input id="search" type="text" class="form-control" placeholder="Artist - Title" v-model="search" v-on:keyup.enter="loadSongs">
                                    </div>

                                    <div class="form-group col-sm-4">
                                        <br />
                                        <button role="button" type="button" class="btn btn-primary" v-on:click="loadSongs">Search</button>

                                        <a href="javascript:void(0)" v-if="searchMode && !loadingSongs" v-on:click="resetSongs" style="margin-left:20px;">Return to Top 50 Songs</a>
                                    </div>

                                    <div class="col-sm-4">
                                        <br />
                                        <span class="black pull-right">
                                        Last Updated: <span>@{{ lastUpdated }}</span>
                                        <a href="javascript:void(0)" class="grey-600" v-on:click="reloadSongs" title="Refresh Statistics"><i class="wb-icon wb-reload"></i></a>
                                        </span>
                                    </div>
                                </form>

                            </div>


                            <div class="white padding-30 musicrating-top-content bg-white">
                                <div class="font-size-20 clearfix grey-600">

                                </div>
                                <div class="row">
                                    <div class="col-sm-12 music-ratings-table-wrapper">
                                        <div class="table-responsive">
                                            <div class="loading" v-if="loadingSongs">
                                                <span class="loading-info">
                                                    <h4 id="loadingSongs" v-if="loadingSongs" class="green-500">Loading<i class="ellipsis"><i>.</i><i>.</i><i>.</i></i></h4>

                                                    <div v-if="loadingSongs" class="progress progress-xs margin-bottom-10">
                                                        <div class="progress-bar progress-bar-info bg-green-600" style="width: @{{ loadedSongs.length / songs.length * 100 }}%" role="progressbar">
                                                        </div>
                                                    </div>
                                                </span>
                                            </div>
                                            <table id="musicRatingsTable" class="table table-condensed">
                                                <thead>
                                                    <tr>
                                                        <th style="width:10px;" v-on:click="sort('scoreIndex')">
                                                            <span v-if="orderKey == 'scoreIndex' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'scoreIndex' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                            <span v-if="orderKey != 'scoreIndex'">#</span>
                                                        </th>
                                                        <th>
                                                            <span style="margin-left:76px;" v-on:click="sort('title')">
                                                                Title
                                                                <span v-if="orderKey == 'title' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                                <span v-if="orderKey == 'title' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                            </span>
                                                            <span style="margin-left:20px;" v-on:click="sort('artist')">
                                                                Artist
                                                                <span v-if="orderKey == 'artist' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                                <span v-if="orderKey == 'artist' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                            </span>
                                                        </th>
                                                        <th>
                                                            <span v-if="currentDepth > 1">
                                                                Week Commencing
                                                            </span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].lovePercent')">
                                                            Love it <br />%
                                                            <span v-if="orderKey == 'data[data.length-1].lovePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].lovePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].likePercent')">
                                                            Like it  <br />%
                                                            <span v-if="orderKey == 'data[data.length-1].likePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].likePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].hatePercent')">
                                                            Over it  <br />%
                                                            <span v-if="orderKey == 'data[data.length-1].hatePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].hatePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].posAcc')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                               title="Positive Acceptance. This includes the percentage of raters who Love it and Like it." style="line-height: 0;">Positive Acceptance
                                                            </span>
                                                            <span v-if="orderKey == 'data[data.length-1].posAcc' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].posAcc' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].airshrs')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                  title="Number of listeners who AirShr'd this song during this period.">AirShr'd<br />#
                                                            </span>
                                                            <span v-if="orderKey == 'data[data.length-1].airshrs' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].airshrs' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].rates')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                  title="The number of listeners who rated this song during this period.">Rated<br />#
                                                            </span>
                                                            <span v-if="orderKey == 'data[data.length-1].rates' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].rates' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('aggregateData.weeksPlayed')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                  title="The number of weeks that this song has been playing.">Total Weeks
                                                            </span>
                                                            <span v-if="orderKey == 'aggregateData.weeksPlayed' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'aggregateData.weeksPlayed' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('data[data.length-1].playCount')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                  title="The number of times this song has been played during this week.">Weekly Plays
                                                            </span>
                                                            <span v-if="orderKey == 'data[data.length-1].playCount' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'data[data.length-1].playCount' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th class="stat-heading" v-on:click="sort('aggregateData.playCount')">
                                                            <span data-toggle="tooltip" data-placement="top"
                                                                  title="Total number of times this song has been played for all time.">Total Plays
                                                            </span>
                                                            <span v-if="orderKey == 'aggregateData.playCount' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                                                            <span v-if="orderKey == 'aggregateData.playCount' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                                                        </th>
                                                        <th style="width:10px; text-align:center; border:none;" v-on:click="sort('watch', -1)">
                                                            Watch List
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody v-for="song in loadedSongs | orderBy orderKey order" id="song_@{{song.musicRatingId}}" transition="songrow" class="songrow" v-bind:class="{'not-loaded' : !song.loaded}">

                                                    <tr v-on:click="expandSong(song)">
                                                        <td v-if="song == currentSong">
                                                           <i class="wb wb-minus" v-on:click.stop="minimizeSong"></i>
                                                        </td>
                                                        <td v-else>
                                                            <span v-if="setPropertyIfEmpty(song, 'scoreIndex', [])">@{{ song.scoreIndex }}</span>
                                                        </td>
                                                        <td class="song-info" rowspan="@{{ currentDepth + 1 }}">
                                                            <audio type="audio/mp3" controls="controls" src="@{{ song.preview }}" style="width: 100% !important;"></audio>
                                                            <img v-bind:class="song == currentSong ? 'coverart-expanded' : 'coverart'" :src="song.coverart_url" style="width:50px; float:left;">


                                                            <div style="float:left; margin-left:25px;">
                                                                <div class="song-title black">@{{song.title}}</div>
                                                                <span class="song-artist black" style="font-weight:bold;">@{{song.artist}}</span>

                                                                <div class="see-more-button" style="color:cornflowerblue;" v-on:click="expandMore(song)" v-if="song == currentSong && currentDepth < numberOfWeeks - 1 && currentDepth < song.aggregateData.weeksPlayed - 1">See even more...</div>
                                                            </div>
                                                        </td>
                                                        <td class="song-week-label">
                                                            <span v-if="song == currentSong">
                                                                @{{ song.data[song.data.length - 1].date | formatDate }}
                                                            </span>
                                                        </td>
                                                        <td class="song-loves">
                                                            @{{ song.data[song.data.length - 1].lovePercent }}
                                                        </td>
                                                        <td class="song-likes">
                                                            @{{ song.data[song.data.length - 1].likePercent }}
                                                        </td>
                                                        <td class="song-overs" v-bind:class="{'bg-red-400' : song.data[song.data.length - 1].hatePercent > 20}">
                                                            @{{ song.data[song.data.length - 1].hatePercent }}
                                                        </td>
                                                        <td class="song-pos-acc" v-bind:class="{'bg-green-400' : song.data[song.data.length - 1].posAcc > 40}">
                                                            @{{ song.data[song.data.length - 1].posAcc }}
                                                        </td>
                                                        <td class="song-airshrs">
                                                            @{{ song.data[song.data.length - 1].airshrs }}
                                                        </td>
                                                        <td class="song-rated">
                                                            @{{ song.data[song.data.length - 1].rates }}
                                                        </td>
                                                        <td class="song-weeks">
                                                            @{{ song.aggregateData.weeksPlayed }}
                                                        </td>
                                                        <td class="song-weekly-plays">
                                                            @{{ song.data[song.data.length - 1].playCount }}
                                                        </td>
                                                        <td class="song-total-plays">
                                                            @{{ song.aggregateData.playCount }}
                                                        </td>
                                                        <td v-if="setPropertyIfEmpty(song, 'watch', [])" class="song-watch" v-on:click.stop="watchSong(song)" v-bind:class="{'active' : song.watch == 1}">
                                                            <i class="wb wb-eye watch-icon"></i>
                                                        </td>
                                                    </tr>

                                                    <tr v-if="song == currentSong && (song.aggregateData.weeksPlayed - weekIndex - 1) > 0" v-for="weekIndex in currentDepth" class="extra-row">
                                                        <td></td>
                                                        <td class="song-week-label">
                                                            @{{ song.data[song.data.length - weekIndex - 2].date | formatDate }}
                                                        </td>
                                                        <td class="song-loves">
                                                            @{{ song.data[song.data.length - weekIndex - 2].lovePercent }}
                                                        </td>
                                                        <td class="song-likes">
                                                            @{{ song.data[song.data.length - weekIndex - 2].likePercent }}
                                                        </td>
                                                        <td class="song-overs" v-bind:class="{'bg-red-400' : song.data[song.data.length - weekIndex - 2].hatePercent > 20}">
                                                            @{{ song.data[song.data.length - weekIndex - 2].hatePercent }}
                                                        </td>
                                                        <td class="song-pos-acc" v-bind:class="{'bg-green-400' : song.data[song.data.length - weekIndex - 2].posAcc > 40}">
                                                            @{{ song.data[song.data.length - weekIndex - 2].posAcc }}
                                                         </td>
                                                        <td class="song-airshrs">
                                                            @{{ song.data[song.data.length - weekIndex - 2].airshrs }}
                                                        </td>
                                                        <td class="song-rated">
                                                            @{{ song.data[song.data.length - weekIndex - 2].rates }}
                                                        </td>
                                                         <td class="song-weeks">
                                                             @{{ song.aggregateData.weeksPlayed - weekIndex - 1 }}
                                                        </td>
                                                        <td class="song-weekly-plays">
                                                            @{{ song.data[song.data.length - weekIndex - 2].playCount }}
                                                        </td>
                                                        <td class="song-total-plays">
                                                        </td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <canvas id="canvas" style="border:2px solid black;display:none;" width="1200" height="700">
    </canvas>

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
                        <label class="control-label">Email Address(es)</label>
                        <input type="email" class="form-control" id="email" placeholder="To send to multiple addresses, use: user1@example.com;user2@example.com">
                        <input type="hidden" id="musicRatingForEmail">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" v-on:click="email()">Send Email</button>
                </div>
            </div>

        </div>
    </div>

    <!-- Hidden copy of the music ratings table for exporting -->
    <table id="musicRatingsTableForExport" class="table" style="display:none;">
        <thead>
        <tr>
            <th>
                <span v-if="orderKey == 'scoreIndex' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'scoreIndex' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                <span v-if="orderKey != 'scoreIndex'">#</span>
            </th>
            <!--<th></th>-->
            <th>
                <span>
                    Title
                    <span v-if="orderKey == 'title' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                    <span v-if="orderKey == 'title' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                </span>
            </th>
            <th>
                <span>
                    Artist
                    <span v-if="orderKey == 'artist' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                    <span v-if="orderKey == 'artist' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
                </span>
            </th>
            <th v-if="currentDepth > 1">Week Commencing</th>
            <th class="stat-heading">
                Love it %
                <span v-if="orderKey == 'data[data.length-1].lovePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].lovePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                Like it %
                <span v-if="orderKey == 'data[data.length-1].likePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].likePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                Over it %
                <span v-if="orderKey == 'data[data.length-1].hatePercent' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].hatePercent' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="Positive Acceptance. This includes the percentage of raters who Love it and Like it.">Positive Acceptance
                                                        </span>
                <span v-if="orderKey == 'data[data.length-1].posAcc' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].posAcc' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="Number of listeners who AirShr'd this song during this period.">AirShr'd
                                                        </span>
                <span v-if="orderKey == 'data[data.length-1].airshrs' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].airshrs' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="The number of listeners who rated this song during this period."># Rated
                                                        </span>
                <span v-if="orderKey == 'data[data.length-1].rates' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].rates' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="The number of weeks that this song has been playing.">Total Weeks
                                                        </span>
                <span v-if="orderKey == 'aggregateData.weeksPlayed' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'aggregateData.weeksPlayed' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="The number of times this song has been played during this week.">Weekly Plays
                                                        </span>
                <span v-if="orderKey == 'data[data.length-1].playCount' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'data[data.length-1].playCount' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
            <th class="stat-heading">
                                                        <span data-toggle="tooltip" data-placement="bottom"
                                                              title="Total number of times this song has been played for all time.">Total Plays
                                                        </span>
                <span v-if="orderKey == 'aggregateData.playCount' && order == 1"><i class="wb-icon wb-chevron-up"></i></span>
                <span v-if="orderKey == 'aggregateData.playCount' && order == -1"><i class="wb-icon wb-chevron-down"></i></span>
            </th>
        </tr>
        </thead>
        <tbody v-for="song in loadedSongs | orderBy orderKey order" id="song_@{{song.musicRatingId}}" class="songrow">

        <tr>
            <td style="border:1px solid black;">
                <span class="black" v-if="setPropertyIfEmpty(song, 'scoreIndex', [])">@{{ song.scoreIndex }}</span>
            </td>
            <!--<td class="song-info" style="border:1px solid black;">
                <img :src="song.coverart_url" style="width:50px; float:left;">
            </td>-->
            <td style="border:1px solid black;">
                <span class="song-artist black font-size-16" style="font-weight:bold;">@{{song.artist}}</span>
            </td>
            <td style="border:1px solid black;">
                <div class="black font-size-16">@{{song.title}}</div>
            </td>
            <td v-if="currentDepth > 1" class="song-week-label" style="border:1px solid black;">
                <span v-if="song == currentSong">
                    @{{ song.data[song.data.length - 1].date | formatDate }}
                </span>
            </td>
            <td class="song-loves" style="border:1px solid black;">
                @{{ song.data[song.data.length - 1].lovePercent }}
            </td>
            <td class="song-likes" style="border:1px solid black;">
                @{{ song.data[song.data.length - 1].likePercent }}
            </td>
            <td class="song-overs" style="border:1px solid black; background-color: @{{ song.data[song.data.length - 1].hatePercent > 20 ? '#fa9898' : '' }};">
                @{{ song.data[song.data.length - 1].hatePercent }}
            </td>
            <td class="song-pos-acc" style="border:1px solid black; background-color: @{{ song.data[song.data.length - 1].posAcc > 20 ? '#7dd3ae' : '' }};">
                @{{ song.data[song.data.length - 1].posAcc }}
            </td>
            <td class="song-airshrs" style="border:1px solid black;">
                @{{ song.data[song.data.length - 1].airshrs }}
            </td>
            <td class="song-rated" style="border:1px solid black;">
                @{{ song.data[song.data.length - 1].rates }}
            </td>
            <td class="song-weeks" style="border:1px solid black;">
                @{{ song.aggregateData.weeksPlayed }}
            </td>
            <td class="song-weekly-plays" style="border:1px solid black;">
                @{{ song.data[song.data.length - 1].playCount }}
            </td>
            <td class="song-total-plays" style="border:1px solid black;">
                @{{ song.aggregateData.playCount }}
            </td>
        </tr>

        <tr v-if="song == currentSong && (song.aggregateData.weeksPlayed - weekIndex - 1) > 0" v-for="weekIndex in currentDepth" class="extra-row">
            <td  style="border:1px solid black;"></td>
            <!--<td  style="border:1px solid black;"></td>-->
            <td  style="border:1px solid black;"></td>
            <td  style="border:1px solid black;"></td>
            <td class="song-week-label" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].date | formatDate }}
            </td>
            <td class="song-loves" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].lovePercent }}
            </td>
            <td class="song-likes" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].likePercent }}
            </td>
            <td class="song-overs" style="border:1px solid black; background-color: @{{ song.data[song.data.length - weekIndex - 2].hatePercent ? '#fa9898' : '' }};">
                @{{ song.data[song.data.length - weekIndex - 2].hatePercent }}
            </td>
            <td class="song-pos-acc" style="border:1px solid black; background-color: @{{ song.data[song.data.length - weekIndex - 2].posAcc > 20 ? '#7dd3ae' : '' }}">
                @{{ song.data[song.data.length - weekIndex - 2].posAcc }}
            </td>
            <td class="song-airshrs" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].airshrs }}
            </td>
            <td class="song-rated" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].rates }}
            </td>
            <td class="song-weeks" style="border:1px solid black;">
                @{{ song.aggregateData.weeksPlayed - weekIndex - 1 }}
            </td>
            <td class="song-weekly-plays" style="border:1px solid black;">
                @{{ song.data[song.data.length - weekIndex - 2].playCount }}
            </td>
            <td class="song-total-plays" style="border:1px solid black;">
            </td>
        </tr>

        </tbody>
    </table>
@endsection

@section('scripts')
    <script src="/js/rasterizehtml/rasterizeHTML.js"></script>
    <script src="/js/vue-animated-list/vue-animated-list.js"></script>
    <script src="/js/stickytableheaders/jquery.stickytableheaders.js"></script>
    <script src="/js/table2excel/jquery.table2excel.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mediaelement/2.20.1/mediaelement-and-player.min.js"></script>
    <script src="/js/musicratingdashboard.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
@endsection