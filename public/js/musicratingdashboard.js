var vm = new Vue({
    el:'#dashboard',
    data: {
        musicRatings: [],
        filteredMusicRatings: [],

        songs: [],
        loadedSongs: [],
        allSongs: [],
        currentSongId: 0,
        currentSong: '',
        currentDepth:0,
        extraRowCount:0,
        firstPass:true,
        searchMode:false,

        artist: '',
        title: '',
        // startDate: moment().subtract(2, 'weeks').format('YYYY-MM-DD'),
        // endDate: moment().format('YYYY-MM-DD'),
        filterHasData:false,

        ratingsLoadedCount: 0,
        statisticsLoaded: false,
        loadingSongs: false,
        numberOfWeeks: 8,
        lastUpdated:0,
        
        orderKey: '',
        order: 1,

        mobileWidth: 1360
    },
    ready: function() {
        this.loadSongs();

        var offset = $('.navbar').height();

        $('#musicRatingsTable').stickyTableHeaders({fixedOffset:offset});

        Site.run();
    },
    methods: {
        loadSongs: function() {
            var that = this;

            if(this.loadingSongs) {
                return;
            }

            if(!this.firstPass && this.search == '') {
                this.resetSongs();
                return;
            } else if(!this.firstPass && this.search != '') {
                this.searchMode = true;
            }

            this.loadingSongs = true;

            this.loadedSongs = [];

            $.ajax({
                url: '/getSongs/',
                type: 'GET',
                data: {
                    search: that.search
                }
            }).done(function(resp) {

                that.songs = resp.songs;

                if(resp.songs.length == 0) {
                    that.loadingSongs = false;
                    return;
                }

                //=========== Loading statistics from scratch

                if(resp.needToUpdate) {
                    var time = new Date().getTime();
                    loadNext = function (i) {
                        var song = that.songs[i];
                        var p = that.loadStatistics(song);
                        p.then(
                            function () {
                                var t = new Date().getTime();
                                console.log((t - time) / 1000);
                                time = t;
                                i++;
                                if (i < that.songs.length) {
                                    loadNext(i);
                                }
                            }
                        ).catch(function () {
                            console.log("Error");
                        });
                    };

                    loadNext(0);
                    //==========
                }
                else {
                    //========== Using Cache
                    that.loadedSongs = resp.songs;
                    that.loadingSongs = false;

                    Vue.nextTick(function () {
                        $('audio').mediaelementplayer({
                            audioHeight: 64,
                            features: ['playpause']
                        });

                        $('.mejs-container .mejs-controls').css({'top': '10px'});
                        $('.mejs-container .mejs-controls').css({'left': '10px'});
                    });

                    that.songs.sort(function (songA, songB) {
                        return songB.data[songB.data.length - 1].score - songA.data[songA.data.length - 1].score;
                    });

                    that.loadingSongs = false;

                    for (var i = 0; i < that.songs.length; i++) {
                        that.songs[i].scoreIndex = i + 1;
                    }

                    that.orderKey = 'scoreIndex';

                    if (that.firstPass) {
                        that.firstPass = false;
                        that.allSongs = that.loadedSongs;
                    }

                    console.log(resp.lastUpdated);

                    that.lastUpdated = moment.unix(resp.lastUpdated).format("DD MMM h:mma");
                }
                //===========




            });
        },

        reloadSongs: function() {
            var that = this;

            this.loadedSongs = [];
            this.loadingSongs = true;

            var time = new Date().getTime();
            loadNext = function(i) {
                var song = that.songs[i];
                var p = that.loadStatistics(song);
                p.then(
                    function () {
                        var t = new Date().getTime();
                        console.log((t - time)/1000);
                        time = t;
                        i++;
                        if( i < that.songs.length ) {
                            loadNext(i);
                        }
                    }
                ).catch(function () {
                    console.log("Error");
                });
            };

            loadNext(0);
        },

        loadStatistics: function(song) {

            var that = this;

            var promise = new Promise(function(resolve, reject) {

                $.ajax(
                    {
                        url: '/getSongStatistics',
                        type: 'POST',
                        data: {
                            'artist': song.artist,
                            'title': song.title
                        }
                    }
                ).done(function(resp) {

                    console.log(song.title);
                    song.data = resp.data;
                    song.aggregateData = resp.aggregateData;

                    console.log(song.data);

                    song.musicRatingId = resp.musicRatingId;
                    song.watch = resp.watch;

                    that.loadedSongs.push(song);

                    Vue.nextTick(function() {
                        $('audio').mediaelementplayer({
                            audioHeight: 64,
                            features: ['playpause']
                        });

                        $('.mejs-container .mejs-controls').css({'top' : '10px'});
                        $('.mejs-container .mejs-controls').css({'left' : '10px'});
                    });

                    song.loaded = true;


                    // //Finished loading all songs
                    if(that.loadedSongs.length == that.songs.length) {

                        //Sort songs by score, higher score get a smaller index so appear higher up
                        that.songs.sort(function(songA, songB) {
                            return songB.data[songB.data.length - 1].score - songA.data[songA.data.length - 1].score;
                        });

                        that.loadingSongs = false;

                        for(var i = 0; i < that.songs.length; i++) {
                            that.songs[i].scoreIndex = i + 1;
                        }

                        that.orderKey = 'scoreIndex';

                        if(that.firstPass) {
                            that.firstPass = false;
                            that.allSongs = that.loadedSongs;
                        }

                        that.lastUpdated = moment().format("DD MMM h:mma");
                        Vue.nextTick(function() {
                        })
                    }

                    resolve(song);

                });
            });

            return promise;

        },

        resetSongs: function() {
            if(this.loadingSongs) return;
            this.loadedSongs = this.allSongs;
            this.searchMode = false;

            Vue.nextTick(function() {
                $('audio').mediaelementplayer({
                    audioHeight: 64,
                    features: ['playpause']
                });

                $('.mejs-container .mejs-controls').css({'top' : '10px', 'left' : '10px'});
            })
        },

        expandSong: function(song) {
            var that = this;

            if(song == this.currentSong) return;

            this.currentSong = song;
            this.currentDepth = Math.floor(this.numberOfWeeks / 2) - 1;
            
            Vue.nextTick(function() {

                that.extraRowCount = $('tr.extra-row').length;

                if($(window).width() > 768) {
                    $('.coverart').animate({'width': '50px'}, 200);// * ($(window).width() / 1920)

                    var coverartWidth = Math.max((25 * (that.extraRowCount + 1)), 50);
                }

                $('.coverart-expanded').animate({'width':coverartWidth+'px'});

                that.expandCoverart(song);

                $('tr.extra-row')
                    .find('td')
                    .wrapInner('<div style="display: none;" />')
                    .parent()
                    .find('td > div')
                    .fadeIn(400, function(){

                        var $set = $(this);
                        $set.replaceWith($set.contents());

                    });
            })
        },

        expandMore: function(song) {
            var that = this;

            if(this.currentDepth == this.numberOfWeeks - 1) return;

            var previousRowCount = this.currentDepth;

            this.currentDepth = this.numberOfWeeks - 1;
            
            Vue.nextTick(function() {

                that.expandCoverart(song);

                $('tr.extra-row:nth-child(n+'+previousRowCount+')')
                    .find('td')
                    .wrapInner('<div style="display: none;" />')
                    .parent()
                    .find('td > div')
                    .fadeIn(400, function(){

                        var $set = $(this);
                        $set.replaceWith($set.contents());

                    });
            })
        },

        minimizeSong: function() {
            var that = this;

            // $('.coverart-expanded').animate({'width':'50px'});
            this.currentSong = '';
            this.currentDepth = 0;
            Vue.nextTick(function() {

                $('.mejs-container .mejs-controls').css({'top' : '10px'});
                $('.mejs-container .mejs-controls').css({'left' : '10px'});

                $('.coverart').animate({'width':'50px'}, 200);

            })
        },

        watchSong: function(song) {
            $.ajax(
                {
                    url: '/watchMusicRating/' + song.musicRatingId
                }
            ).done(function(resp) {

                if(resp.code == 0) {

                    song.watch = resp.watch;

                    $('#song_' + song.musicRatingId+ ' .song-watch').addClass(song.watch ? 'active' : '').removeClass(song.watch ? '' : 'active');

                }

            })
        },

        expandCoverart: function(song) {
            this.extraRowCount = $('tr.extra-row').length;
            if($(window).width() > 768) {
                var coverartWidth = Math.max((25 * (this.extraRowCount + 1)), 50);

                $('.coverart-expanded').animate({'width': coverartWidth + 'px'});
            }

            $('.songrow:not(#song_'+song.musicRatingId+') .mejs-container .mejs-controls').animate({'top' : '10px', 'left' : '10px'});

            $('#song_'+song.musicRatingId + ' .mejs-container .mejs-controls').animate({'top' : (this.extraRowCount * 12.5) + 'px', 'left' : (this.extraRowCount * 12.5) + 'px'}, 200);
        },

        //Utility
        sort: function(orderKey, order) {
            if(order) {
                this.order = order
            }
            else if(this.orderKey == orderKey) {
                this.changeOrder();
            }

            console.log(orderKey);

            this.orderKey =  orderKey;
        },

        export: function() {
            $("#musicRatingsTableForExport").table2excel({
                exclude: ".noExl",
                exclude_img: false,
                name: "Excel Document Name",
                filename: "MusicRatings_" + moment().format(),
                fileext: '.xls'
            });
            
            // var images = [];
            //
            // $('.coverart, .coverart-expanded').each(function() {
            //     images.push($(this).attr('src'));
            // });

            this.post('/getSpreadsheet', {data: document.getElementById('spreadsheetLink').getAttribute('href')/*, images: images*/});
        },

        post: function(path, params, method) {
            method = method || "post"; // Set method to post by default if not specified.

            // The rest of this code assumes you are not using a library.
            // It can be made less wordy if you use one.
            var form = document.createElement("form");
            form.setAttribute("method", method);
            form.setAttribute("action", path);
            form.setAttribute("target", "_blank");

            for(var key in params) {
                if(params.hasOwnProperty(key)) {
                    var hiddenField = document.createElement("input");
                    hiddenField.setAttribute("type", "hidden");
                    hiddenField.setAttribute("name", key);
                    hiddenField.setAttribute("value", params[key]);

                    form.appendChild(hiddenField);
                }
            }

            document.body.appendChild(form);
            form.submit();
        },

        // setupDatePickers: function() {
        //     $('#startDate').datepicker({
        //         autoclose: true,
        //         format:'yyyy-mm-dd'
        //     });
        //     $('#endDate').datepicker({
        //         autoclose: true,
        //         format:'yyyy-mm-dd'
        //     });
        // },
        // email: function() {
        //     $('#email_sending_icon').show();
        //     var content = $('#song_' + this.currentSongId).find('.widget-content').html();
        //     var song = $('#song_' + this.currentSongId).find('.music-rating-song').html();
        //     var dateRange = $('#song_' + this.currentSongId).find('.start-date-for-music-rating').html()+
        //         ' - ' + $('#song_' + this.currentSongId).find('.end-date-for-music-rating').html();
        //
        //     $.ajax(
        //         {
        //             url: '/emailMusicRating',
        //             type: 'post',
        //             data: {
        //                 'content': content,
        //                 'email' : $('#email').val(),
        //                 'song' : song,
        //                 'dateRange' : dateRange
        //             }
        //         }
        //     ).done(function(resp) {
        //
        //         if(resp.code == 0) {
        //
        //             console.log('HTML inlined correctly');
        //
        //             var canvas = document.getElementById("canvas");
        //
        //             rasterizeHTML.drawHTML(resp.html, canvas)
        //                 .then(function success(returnResult) {
        //                     var dataURL = canvas.toDataURL("image/png");
        //
        //                     dataURL = dataURL.replace(/^data:image\/(png|jpg);base64,/, "");
        //
        //                     $.ajax(
        //                         {
        //                             url: '/emailMusicRating',
        //                             type: 'post',
        //                             data: {
        //                                 'content': content,
        //                                 'email' : $('#email').val(),
        //                                 'song' : song,
        //                                 'dateRange' : dateRange,
        //                                 'image' : dataURL
        //                             }
        //                         }
        //                     ).done(function(resp) {
        //
        //                         if(resp.code == 0) {
        //                             $('#email_sent_icon').fadeIn();
        //
        //                             setTimeout(function () {
        //                                 $('#emailModal').modal('hide');
        //                             }, 500);
        //                         }
        //                         else {
        //                             console.log(resp)
        //                         }
        //
        //                     }).fail(function(resp) {
        //
        //                     }).always(function() {
        //                         setTimeout(function() {
        //                             $('#email_sent_icon').fadeOut();
        //                             $('#email_failed_icon').fadeOut();
        //                         }, 3000);
        //                     });
        //             });
        //
        //             // alert(dataURL.replace(/^data:image\/(png|jpg);base64,/, ""));
        //
        //
        //
        //         }
        //         else {
        //
        //             $('#email_failed_icon').fadeIn();
        //
        //         }
        //
        //     }).fail(function(resp){
        //
        //         $('#email_failed_icon').fadeIn();
        //
        //     }).always(function() {
        //         $('#email_sending_icon').hide();
        //
        //         setTimeout(function() {
        //             $('#email_sent_icon').fadeOut();
        //             $('#email_failed_icon').fadeOut();
        //         }, 3000);
        //
        //     })
        // },
        numberWithCommas: function(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        changeOrder: function() {
            if(this.order == 1) this.order = -1;
            else this.order = 1;
        },
        setPropertyIfEmpty: function(obj, prop, value) {
            if(!obj[prop]) Vue.set(obj, prop, value);
            return true;
        }//https://laracasts.com/discuss/channels/vue/vuejs-update-dom-after-nested-array-updates
    },
    filters: {
        formatDate: function (date) {
            return moment(date).format('Do MMMM');
        }
    },
    watch: {
        // 'filterHasData' : function(oldValue, newValue) {
        //     this.filterMusicRatings();
        // }
    }
});
