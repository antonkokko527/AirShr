var vm = new Vue({
    el: "#musicRating",
    data: {
        artist: '',
        track: '',
        searchArtist: '',
        searchTrack: '',
        coverArtId: '',
        mobilePreview: [],
        musicRatings: [],
        currentMusicRatingIndex: '',
        startDate: '',
        endDate: '',
        searchMode: false,
        editMode: false
    },
    ready: function() {
        this.mobilePreview = new MobilePreviewForm('mobilepreview_slider_container');
        $('[data-toggle="tooltip"]').tooltip();
        this.listMusicRatings();
    },
    methods: {
        searchSongs: function() {
            var that = this;
            this.mobilePreview.showPreviewLoading();
            if(!this.artist && !this.track) {
                showMessage('No artist or title specified', 'red');
                this.mobilePreview.hidePreviewLoading();
            }

            $.ajax({
                url: '/content/searchMusic',
                type: 'get',
                data: {
                    'artist' : that.artist,
                    'track' : that.track
                }
            }).done(function(resp) {

                console.log(resp);

                if(resp.code == 0) {

                    that.mobilePreview.hidePreviewLoading();
                    that.mobilePreview.renderPreviewInfo('coverart', resp.song.id);

                    that.artist = resp.song.artist;
                    that.track = resp.song.track;
                    that.coverArtId = resp.song.id;

                    //Only set up the date pickers once the date fields are visible
                    Vue.nextTick(function() {
                        that.setupDatePickers();
                        $('#startDate').datepicker('setDate', moment().format('YYYY-MM-DD'));
                        $('#endDate').datepicker('setDate', moment().add(3, 'weeks').format('YYYY-MM-DD'));
                    });
                }
                else {
                    showMessage(resp.msg, 'red');
                    that.mobilePreview.hidePreviewLoading();
                }

            }).fail(function(resp) {
                console.log(resp);
                that.mobilePreview.hidePreviewLoading();
            })
        },
        saveMusicRating: function() {
            var that = this;
            var musicRatingId = 0;

            var message = 'Are you sure you want to create a new music rating for "' + this.artist + ' - ' + this.track + '" between ' + this.startDate + ' and ' + this.endDate + '?';

            if(this.currentMusicRatingIndex >= 0 && this.musicRatings[this.currentMusicRatingIndex] != undefined) {
                musicRatingId = this.musicRatings[this.currentMusicRatingIndex].coverart_rating_id;
                message = 'This will replace the current music rating. Are you sure you want to update the music rating for "' + this.artist + ' - ' + this.track + '" between ' + this.startDate + ' and ' + this.endDate + '?'
            }

            if(!this.startDate) {
                showMessage('Please enter a start date', 'orange');
                return;
            }
            if(!this.endDate) {
                showMessage('Please enter an end date', 'orange');
                return;
            }
            if(!this.coverArtId) {
                showMessage('No song selected', 'red');
                return;
            }

            bootbox.confirm(message, function(result) {
                if(result) {
                    $.ajax({
                        url: '/content/saveMusicRating',
                        type: 'post',
                        data: {
                            'music_rating_id' : musicRatingId,
                            'coverart_id' : that.coverArtId,
                            'start_date' : that.startDate,
                            'end_date' : that.endDate
                        }
                    }).done(function(resp) {

                        console.log(resp);

                        if(resp.code == 0) {

                            showMessage('Music rating has successfully been saved', 'green');

                            that.currentMusicRating = resp.musicRating;

                            that.listMusicRatings();
                        }


                    }).fail(function(resp) {
                        console.log(resp);
                    });
                }
            })
        },
        endMusicRating: function(musicRatingIndex) {
            console.log(musicRatingIndex);
            var that = this;
            
            var musicRatingId = this.musicRatings[musicRatingIndex].coverart_rating_id;

            bootbox.confirm('Are you sure you want to end this music rating?', function(result) {
                if(result) {
                    $.ajax({
                        url: '/content/endMusicRating',
                        type: 'post',
                        data: {
                            'music_rating_id' : musicRatingId
                        }
                    }).done(function(resp) {

                        console.log(resp);

                        if(resp.code == 0) {

                            showMessage('Music rating has successfully been deleted', 'green');

                            that.clearMusicRating();

                            that.listMusicRatings();
                        }


                    }).fail(function(resp) {
                        console.log(resp);
                    });
                }
            })
        },
        listMusicRatings: function() {
            var that = this;
            
            $.ajax({
                url: '/content/listMusicRatings',
                type: 'get'
            }).done(function(resp) {

                console.log(resp);

                that.musicRatings = resp.musicRatings;

                //Filter music ratings
                var filteredMusicRatings = [];

                for(var i = 0; i < that.musicRatings.length; i++) {

                    var musicRating = that.musicRatings[i];

                    musicRating.ended = moment(musicRating.end_date).isSameOrBefore(moment()); //This probably should be in the backend

                    if (musicRating.artist.toLowerCase().indexOf(that.searchArtist.toLowerCase()) >= 0 && musicRating.track.toLowerCase().indexOf(that.searchTrack.toLowerCase()) >= 0) {
                        filteredMusicRatings.push(musicRating);
                    }
                }

                that.musicRatings = filteredMusicRatings;

            }).fail(function(resp) {
                console.log(resp);
            });
        },
        selectMusicRating: function(musicRatingIndex) {
            this.setEditMode();
            
            this.artist = this.musicRatings[musicRatingIndex].artist;
            this.track = this.musicRatings[musicRatingIndex].track;
            this.coverArtId = this.musicRatings[musicRatingIndex].coverart_id;
            this.startDate = this.musicRatings[musicRatingIndex].start_date;
            this.endDate = this.musicRatings[musicRatingIndex].end_date;
            this.currentMusicRatingIndex = musicRatingIndex;

            var that = this;
            $('.musicRating').removeClass('selected');
            $('#musicRating_'+musicRatingIndex).addClass('selected');

            Vue.nextTick(function() {
                that.setupDatePickers();
            });

            this.mobilePreview.renderPreviewInfo('coverart', this.coverArtId);
        },
        clearMusicRating: function() {
            this.artist = '';
            this.track = '';
            this.coverArtId = '';
            this.startDate = '';
            this.endDate = '';
            this.currentMusicRatingIndex = '';

            this.mobilePreview._resetFormData();

            $('.musicRating').removeClass('selected');
        },
        setSearchMode: function() {
            this.searchMode = true;
            this.editMode = false;
        },
        setEditMode: function() {
            this.searchTrack = '';
            this.searchArtist = '';
            this.searchMode = false;
            this.editMode = true;
        },
        new: function() {
            var that = this;

            this.clearMusicRating();
            this.setEditMode();

            Vue.nextTick(function() {
                that.setupDatePickers();
            });

        },
        mouseOver: function(index) {
            $('.edit-pencil').hide();
            $('#musicRating_'+index+' .edit-pencil').show();

            $('.delete-button').hide();
            $('#musicRating_'+index+' .delete-button').show();

            $('.view-button').hide();
            $('#musicRating_'+index+' .view-button').show();
        },
        setupDatePickers: function() {
            
            $('#startDate').datepicker({
                autoclose: true,
                format:'yyyy-mm-dd'
            });
            $('#endDate').datepicker({
                autoclose: true,
                format:'yyyy-mm-dd'
            });
            
        }
    }
});

function showMessage(message, color) {
    $('.saveProgress').show().html(message).css('color', color);
    setTimeout(function() { $('.saveProgress').fadeOut(); }, 5000);
}