@extends('layout.main')

@section('styles')
    @parent
    <link href="/js/bootstrap-editable/css/bootstrap-editable.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/jcrop/css/Jcrop.min.css" media="all" rel="stylesheet" type="text/css" />
    <link href="/js/bootstrap.slider/css/bootstrap-slider.min.css" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="/css/mobileeditor.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    <link rel="stylesheet" href="/css/musicrating.css?v={{ \Config::get('app.ConnectWebAppVersion') }}">
    </head>
@endsection

@section('content')



    <!--Main content-->
    <div id="musicRating">
        <div class="content-sub-header">
            <h1 class="content-sub-header-title" id="content_title">Music Rating</h1>
            <div class="content-sub-header-form">
            </div>

            <div class="content-sub-header-actions">
                <span class="saveProgress"></span>
                </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-4">
                    <a class="btn-action" title="Search" data-toggle="tooltip" id="content_btn_search" v-on:click="setSearchMode"><i class="mdi mdi-magnify"></i></a>
                    <a class="btn-action" title="New" id="content_btn_new" data-toggle="tooltip" v-on:click="new" style="display: inline-block;"><i class="mdi mdi-plus"></i></a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-8">
                    <div id="searchForm" v-if="searchMode">
                        <h5 style="margin-left:15px; margin-top:20px;">Search for Existing Music Ratings</h5>
                        <form class="music-search-form">
                            <div class="form-group">
                                <label>Artist</label>
                                <input type="text" class="form-control" id="artistSearch" v-model="searchArtist" v-on:keyup.enter="listMusicRatings"/>
                            </div>

                            <div class="form-group">
                                <label>Title/Track</label>
                                <input type="text" class="form-control" id="trackSearch" v-model="searchTrack" v-on:keyup.enter="listMusicRatings"/>
                            </div>

                            <button type="button" class="btn btn-primary" v-on:click="listMusicRatings">Search for Music Ratings</button>
                        </form>
                    </div>
                    <div id="editForm" v-if="editMode">
                        <h5 style="margin-left:15px; margin-top:20px;">
                            <span v-if="currentMusicRatingIndex !== ''">Edit Music Rating</span>
                            <span v-else>New Music Rating - Search for a Song</span>
                        </h5>
                        <form class="music-search-form">
                            <div class="form-group">
                                <label>Artist</label>
                                <input type="text" class="form-control" id="artist" v-model="artist" v-on:keyup.enter="searchSongs"/>
                            </div>

                            <div class="form-group">
                                <label>Title/Track</label>
                                <input type="text" class="form-control" id="track" v-model="track" v-on:keyup.enter="searchSongs"/>
                            </div>

                            <button type="button" class="btn btn-primary" v-on:click="searchSongs">Search for Song</button>
                        </form>
                    </div>
                </div>
                <div class="col-sm-8">
                    <h5 style="margin-left:15px; margin-top:20px;" v-if="editMode">Select Rating Period</h5>
                    <form class="date-form" v-if="editMode">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="text" class="form-control" v-model="startDate" id="startDate"/>
                        </div>

                        <div class="form-group">
                            <label>End Date</label>
                            <input type="text" class="form-control" v-model="endDate" id="endDate"/>
                        </div>

                        <button type="button" class="btn btn-primary" v-on:click="saveMusicRating" v-if="coverArtId != 0 && startDate && endDate">
                            <span v-if="currentMusicRatingIndex !== ''">Update</span><span v-else>Save Music Rating</span>
                        </button>
                    </form>
                </div>
                <div class='col-sm-8'></div>
            </div>
            <div class="row existing-music-ratings" v-if="editMode || searchMode">
                <div class="col-sm-16">
                    <h5 style="margin-top:20px; margin-left:15px;">Existing Music Ratings</h5>
                    <table class="table table-hover table-striped music-ratings-table" style="margin-left:24px;">
                        <thead>
                        <tr>
                            <th>Artist</th>
                            <th>Track</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="musicRating in musicRatings" id="musicRating_@{{ $index }}" class="musicRating" v-on:mouseover="mouseOver($index)" v-on:click="selectMusicRating($index)">
                            <td v-bind:class="{ 'ended' : musicRating.ended }">@{{ musicRating.artist }}</td>
                            <td v-bind:class="{ 'ended' : musicRating.ended }">@{{ musicRating.track }}</td>
                            <td v-bind:class="{ 'ended' : musicRating.ended }">@{{ musicRating.start_date }}</td>
                            <td v-bind:class="{ 'ended' : musicRating.ended }">@{{ musicRating.end_date }}</td>
                            <td>
                                <a href="javascript:void(0)" title="End" v-if="!musicRating.ended" v-on:click="endMusicRating($index)" class="delete-button" style="display:none; color:red;">
                                    END
                                </a>
                                <a href="/dashboard/musicRatings/@{{ musicRating.coverart_rating_id }}" title="View" class="view-button" style="display:none; color:green;">
                                    VIEW ANALYTICS
                                </a>
                            </td>
                        </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>


    <!--Mobile preview and image editor-->
    <div class="content-modal-overlay" id="image_editor_overlay" style="display: none">

        <div class="content-sub-header">
            <h1 class="content-sub-header-title">Image Editor</h1>
            <div class="content-sub-header-actions">
                <a class="btn-action" title="Confirm" id="content_btn_img_confirm"><i class="mdi mdi-check"></i></a>
                <a class="btn-action" title="Cancel" id="content_btn_img_cancel"><i class="mdi mdi-close"></i></a>
            </div>
        </div>

        <div class="container-fluid image-editor-content">
            <div class="image-editor-main-wrapper">
                <div class="image-editor-main-area">
                    <div id="image-editor-cropper-div">
                        <img id="image-editor-cropper-img" />
                    </div>
                    <div id="image-editor-zoom-container">
                        <input id="image-editor-zoom-slider" class="image-editor-slider" type="text" data-slider-min="10" data-slider-max="300" data-slider-step="10" data-slider-value="100" data-slider-orientation="vertical"/>
                    </div>
                </div>

                <p class="success-green image-status-icon"><i class="mdi mdi-checkbox-marked-circle"></i></p>
                <p class="image-status-description success-green">Image resolution is OK</p>
                <p>Uploaded image is <span class="image-information">900w * 700h (2:1)</span></p>
                <p>Minimum required is 800w * 600h (4:3)</p>

            </div>
        </div>

    </div>


    <div class="container-fluid">
        <div class="row">
            <div class="content-modal-sidebar right-sidebar" id="mobilepreview_sidebar">

                @include('airshrconnect.mobilepreview', ['mode' => 'musicrating', 'sliderContainerID' => 'mobilepreview_slider_container', 'displayFormOption' => 'true', 'displayFormCloseOption' => 'false'])

            </div>
        </div>
    </div>

    @include('airshrconnect.mobileeditor')
@endsection

@section('scripts')
    <script>
        var page = 'musicrating';
    </script>
    @parent
    <script src="/js/timepicker/jquery.timepicker.min.js" type="text/javascript"></script>
    <script src="/js/bootstrap-editable/js/bootstrap-editable.min.js"></script>
    <script src="/js/typeaheadjs.js"></script>

    <script src="/js/jcrop/js/Jcrop.js"></script>
    <script src="/js/bootstrap.slider/bootstrap-slider.min.js"></script>
    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>

    <script src="/js/image_editor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>
    <script src="/js/mobilepreview.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

    <script src="/js/bootstrap-modal-popover/bootstrap-modal-popover.js"></script>
    <script src="/js/mobileeditor.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

    <script src="/js/musicrating.js?v={{ \Config::get('app.ConnectWebAppVersion') }}"></script>

@endsection