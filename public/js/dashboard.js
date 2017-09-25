/*!
 * remark (http://getbootstrapadmin.com/remark)
 * Copyright 2015 amazingsurge
 * Licensed under the Themeforest Standard Licenses
 */
// (function(document, window, $) {
//     'use strict';
//
//     var Site = window.Site;
//
//     $(document).ready(function($) {
//         Site.run();
//     });
//
//
//     // Example Morris Donut
//     // ---------------------
//     (function() {
//
//         getContentTypeDonut();
//
//     })();
//
// })(document, window, jQuery);

var contentTypesDonut = null;
var tzFormatted = GLOBAL.STATION_TIMEZONE.replace(new RegExp(' ', 'g'), '_');

//Sort of hacky way to make sure the total moments to calculate the percentage in the donut is dynamically updated
var moments = 0;
function getTotalMoments() {
    return moments;
}

var hourForPopularTagsEmail = 0;
var contentTypeForPopularTagsEmail = 0;

$(document).ready(function() {
    // if(dashboardInit) {
    //     return;
    // }
    // dashboardInit = true;

    $('#updated_time').html(moment().tz(tzFormatted).format('HH:mm on dddd, DD of MMMM YYYY'));

    //First Row
    getNewUsers();
    getUserClicks();

    //Second Row
    getMomentsCounts();
    activityChart();

    //Third Row
    $('#date_for_tod').datepicker('setDate', moment().format('YYYY-MM-DD'));
    $('#date_for_tod_text').html(moment().format('DD MMM YYYY') + '<span class="caret"></span>');
    $('#end_date_for_tod').datepicker('setDate', moment().format('YYYY-MM-DD'));
    $('#end_date_for_tod_text').html(moment().format('DD MMM YYYY') + '<span class="caret"></span>');
    timeOfDayChart();
    competitionAndVoteTable();

    //Fourth Row
    $('#all_time_button').addClass('active');
    getContentTypeDonut(0, moment().format('YYYY-MM-DD'));

    //Fifth Row
    getSourceDonut();
    getStreamTime();

    Site.run();
});

//Listeners

$('#date_for_tod').datepicker({
    autoclose: true,
    orientation: 'top auto',
    format:'yyyy-mm-dd'
});

$('#date_for_tod').datepicker().on('changeDate', function() {
    $('#date_for_tod_text').html(moment($('#date_for_tod').val()).format('DD MMM YYYY') + '<span class="caret"></span>');
    if(!$('#end_date_for_tod_wrapper').is(':visible')) {
        $('#end_date_for_tod').datepicker('setDate', $('#date_for_tod').val());
    }
    timeOfDayChart();
    competitionAndVoteTable();
});

$('#date_for_tod_text').click(function () {
    $('#date_for_tod').datepicker('show');
});

$('#end_date_for_tod').datepicker({
    autoclose: true,
    orientation: 'top auto',
    format:'yyyy-mm-dd'
});

$('#end_date_for_tod').datepicker().on('changeDate', function() {
    $('#end_date_for_tod_text').html(moment($('#end_date_for_tod').val()).format('DD MMM YYYY') + '<span class="caret"></span>');
    timeOfDayChart();
    competitionAndVoteTable();
});

$('#end_date_for_tod_text').click(function () {
    $('#end_date_for_tod').datepicker('show');
});

var isDrillDownDisabled = false;

$('#tod_range_button').click(function() {
    isDrillDownDisabled = true;
    disableDrillDown();
    $('#tod_range_button').hide();
    $('#end_date_for_tod_wrapper').show();
});

//Content Type Donut Buttons

$('#date_for_ctdonut').datepicker({
    autoclose: true,
    format:'yyyy-mm-dd'
});

$('#date_for_ctdonut').datepicker().on('changeDate', function() {
    $('#contentTypeButtons .btn').removeClass('active');
    $('#date_for_ctdonut_button').addClass('active');
    $('#date_for_ctdonut_button').html($('#date_for_ctdonut').val());
    getContentTypeDonut($('#date_for_ctdonut').val(), $('#date_for_ctdonut').val());
});

$('#date_for_ctdonut_button').click(function () {
    $('#date_for_ctdonut').datepicker('show');
});

$('#all_time_button').click(function () {
    $('#contentTypeButtons .btn').removeClass('active');
    $('#all_time_button').addClass('active');
    $('#date_for_ctdonut_button').html('Select Date');
    getContentTypeDonut(0, moment().format('YYYY-MM-DD'));
});

$('#this_week_button').click(function () {
    $('#contentTypeButtons .btn').removeClass('active');
    $('#this_week_button').addClass('active');
    $('#date_for_ctdonut_button').html('Select Date');
    var start = moment().startOf('isoweek');
    var end = start.clone().endOf('isoweek');
    getContentTypeDonut(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
});

$('#last_week_button').click(function () {
    $('#contentTypeButtons .btn').removeClass('active');
    $('#last_week_button').addClass('active');
    $('#date_for_ctdonut_button').html('Select Date');
    var start = moment().subtract(1, 'week').startOf('isoweek');
    var end = start.clone().endOf('isoweek');
    getContentTypeDonut(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
});

//First Row

function getNewUsers() {

    //Downloads
    //Hard coded data for now
    //Counters
    $.ajax(
        {
            url: '/getNumberOfUsers',
            type: 'get'
        }
    ).done(function(resp) {
        var today = resp.total_users;
        var yesterday = resp.users_yesterday;

        $('#totalUsers').html(numberWithCommas(today));
        var diff = today - yesterday;
        $('#totalUsersDiff').html(numberWithCommas(Math.abs(diff)));
        $('#usersExtra .counter-number-related').html(diff < 0 ? 'less than yesterday' : 'more than yesterday');

        //Progress Bar
        var percentDiff = diff / yesterday * 100;
        $('#totalUsersProgress').attr('aria-valuenow', percentDiff);
        if (diff < 0) {
            $('#usersExtra .counter-icon').removeClass('blue-600');
            $('#usersExtra .counter-icon').addClass('red-600');
            $('#usersExtra .counter-icon').html('<i class="wb-graph-down"></i>');
        }

    }).fail(function(resp) {
        console.log(resp);
    });
}

function getUserClicks() {
    //chart-bar-stacked
    $.ajax (
        {
            url: '/getUsersByClicks',
            type: 'get'
        }
    ).done(function(resp) {
        if(resp.code == 0) {
            var labels = resp.labels;

            var stacked_bar = new Chartist.Bar('#clicksPerUserWidget .ct-chart', {
                labels: labels,
                series: [
                    resp.more_clicks,
                    resp.double_clicks,
                    resp.single_clicks
                ]
            }, {
                stackBars: true,
                height: 360,
                fullWidth: true,
                seriesBarDistance: 0,
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                chartPadding: {
                    top: 20,
                    right: 20,
                    bottom: 0,
                    left: 20
                },
                axisX: {
                    showLabel: true,
                    showGrid: false,
                    offset: 30
                },
                axisY: {
                    showLabel: true,
                    showGrid: true,
                    offset: 30
                }
            });

        } else {
            console.log('fail');
        }
    }).fail(function(resp) {
        console.log(resp);
    });

}

//Second Row

function getMomentsCounts() {
    $.ajax(
        {
            url:'/getMomentsTodayAndMonth',
            type: 'get'
        }
    ).done(function(resp) {
        $('#moments_today_count').html(numberWithCommas(resp.moments_today));
        $('#moments_month_count').html(numberWithCommas(resp.moments_month));
    }).fail(function(resp) {

    })
}

function activityChart() {

    $.ajax(
        {
            url:'/getMomentsByMonth',
            type: 'get'
        }
    ). done(function(resp) {

        $('#moments_month').html(resp.moments_this_month);
        $('#total_moments').html(numberWithCommas(resp.total_moments));

        var momentData = resp.moments;
        var moments = [];

        var months = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var labels = [];

        if(momentData.length > 8) {
            var prevMonth = 0;
            $.each(momentData, function(k, v) {
                if(k == 0) {
                    labels.push(months[v.month - 1] + ' ' + v.year);
                    prevMonth = v.month;
                }
                if(v.count != 0) {
                    moments.push(v.count);
                    if(v.month != prevMonth) {
                        labels.push(months[v.month - 1] + ' ' + v.year);
                        prevMonth = v.month;
                    }
                    else {
                        labels.push('')
                    }
                }
            });
        }
        else {
            $.each(momentData, function (k, v) {
                moments.push(v.count);
                var weekStart = moment().isoWeek(v.week).year(v.year).startOf('isoWeek');
                var weekEnd = weekStart.clone().add(6, 'days');
                labels.push(weekStart.format('D MMM') + ' - ' + weekEnd.format('D MMM'));
            });
        }

        // options for style
        var options = {
            showArea: true,
            low: 0,
            height: 275,
            fullWidth: true,
            axisX: {
                offset: 30
            },
            axisY: {
                offset: 30,
                labelInterpolationFnc: function(value) {
                    if (value == 0) {
                        return null;
                    }
                    return value >= 1000 ? (value / 1000 + 'k') : value;
                },
                scaleMinSpace: 50
            },
            chartPadding: {
                bottom: 12,
                left: 30,
                right:30
            },
            plugins: [
                Chartist.plugins.tooltip()
            ],
            lineSmooth: Chartist.Interpolation.simple({
                divisor: 4
            })
        };
        var momentsList = {
            name: 'Activity',
            data: moments
        };

        var newScoreLineChart = function(chartId, labels, series1List, options) {

            var lineChart = new Chartist.Line(chartId, {
                labels: labels,
                series: [momentsList]
            }, options);

            //start create
            lineChart.on('draw', function(data) {
                var elem, parent;
                if (data.type === 'point') {
                    elem = data.element;
                    parent = new Chartist.Svg(elem._node.parentNode);

                    parent.elem('line', {
                        x1: data.x,
                        y1: data.y,
                        x2: data.x + 0.01,
                        y2: data.y,
                        "class": 'ct-point-content'
                    });
                }
            });
            //end create
        }

        newScoreLineChart("#activityWidget .ct-chart", labels,
            momentsList, options);
    }).fail(function(resp) {

    })

}

//Third Row
function timeOfDayChart() {

    $.ajax(
        {
            url:'/getMomentsByDate/' + $('#date_for_tod').val() + '/' + $('#end_date_for_tod').val(),
            type: 'get'
        }
    ). done(function(resp) {

        var total = 0;
        $.each(resp.music, function(k,v) {
            total += v;
        });
        $.each(resp.talk, function(k,v) {
            total += v;
        });
        $.each(resp.ad, function(k,v) {
            total += v;
        });
        $.each(resp.news, function(k,v) {
            total += v;
        });
        $.each(resp.traffic, function(k,v) {
            total += v;
        });
        $.each(resp.promo, function(k,v) {
            total += v;
        });

        $('#total_unprompted_moments').html(numberWithCommas(total));
        var labels = [ '6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm'];

        var stacked_bar = new Chartist.Bar('#timeOfDayWidget .ct-chart', {
            labels: labels,
            series: [
                resp.music,
                resp.talk,
                resp.ad,
                resp.news,
                resp.traffic,
                resp.promo
            ]
        }, {
            stackBars: true,
            height: 360,
            fullWidth: true,
            seriesBarDistance: 0,
            plugins: [
                Chartist.plugins.tooltip()
            ],
            chartPadding: {
                top: 20,
                right: 5,
                bottom: 0,
                left: 45
            },
            axisX: {
                showLabel: true,
                showGrid: false,
                offset: 30
            },
            axisY: {
                showLabel: true,
                showGrid: true,
                onlyInteger: true,
                offset: 30
            }
        });

        stacked_bar.on('draw', function() {
            if(isDrillDownDisabled) {
                disableDrillDown();
                return;
            }
            $('#timeOfDayWidget .ct-bar').off('click').on('click', function() {
                var hour = $(this).index() + 6;

                var type = '';
                var color = '#000000';
                if($(this).parents('.ct-series-a').length) {
                    type = getContentTypeIdOfMusic();
                    color = '#DD218B';
                } else if($(this).parents('.ct-series-b').length) {
                    type = getContentTypeIdOfTalk();
                    color = '#60C3EC';
                } else if($(this).parents('.ct-series-c').length) {
                    type = getContentTypeIdOfAd();
                    color = '#50E3C2';
                } else if($(this).parents('.ct-series-d').length) {
                    type = getContentTypeIdOfNews();
                    color = '#F5A623';
                } else if($(this).parents('.ct-series-e').length) {
                    type = getContentTypeIdOfTraffic();
                    color = '#3583ca';
                } else if($(this).parents('.ct-series-f').length) {
                    type = getContentTypeIdOfPromotion();
                    color = '#000000';
                }
                
                contentTypeForPopularTagsEmail = type;
                hourForPopularTagsEmail = hour;

                $.ajax(
                    {
                        url: '/getPopularTags/'+$('#date_for_tod').val()+'/'+hour+'/'+type,
                        type: 'get'
                    }
                ).done(function(resp) {
                    var contentTypeName = getContentTypeString(type);
                    $('#contentTypeDot').css({color : color});
                    $('#contentTypeTagInfo').html('<i class="mdi mdi-checkbox-blank-circle" style="color:'+color+'"></i>' + contentTypeName);
                    $('#dateTagInfo').html(moment($('#date_for_tod').val()).format('DD MMM YYYY'));
                    $('#hourTagInfo').html(moment(hour, "H").format('hh:mma') + ' - ' + moment(hour+1, "H").format('hh:mma'));

                    $('#popularTagsTable').empty();
                    $.each(resp.results, function(key,val) {
                        var time = moment.unix(Number(val.tag_timestamp) / 1000).tz(tzFormatted).format('hh:mma');
                        
                        $('#popularTagsTable').append('<tr>'+
                            '<td class="who">'+(val.who ? val.who : '')+'</td>'+
                            '<td class="what">'+(val.what ? val.what : '')+'</td>'+
                            '<td class="time-played">'+time+'</td>'+
                            '<td class="event-count">'+val.event_count+'</td>'+
                            '</tr>');
                    });

                    $('#popularTags').modal();
                });

            })
        })


    }).fail(function(resp) {

    });
}

function competitionAndVoteTable() {
    $.ajax(
        {
            url:'/getCompetitionAndVoteMoments/' + $('#date_for_tod').val() + '/' + $('#end_date_for_tod').val(),
            type: 'get'
        }
    ). done(function(resp) {

        var total = 0;
        for(var i = 0; i <= 16; i++) {
            total += resp.competition[i] + resp.vote[i];
            $('#comp-' + i).html(resp.competition[i] ? numberWithCommas(Number(resp.competition[i])) : '-');
            $('#vote-' + i).html(resp.vote[i] ? numberWithCommas(Number(resp.vote[i])) : '-');
        }

        $('#total_comp_and_vote_moments').html(numberWithCommas(total));

    }).fail(function(resp) {

    })
}

//Fourth Row
function getContentTypeDonut(start, end) {

    $.ajax (
        {
            url: '/getContentTypePercentages/' + start + '/' + end,
            type: 'get'
        }
    ).done(function(resp) {
        if(resp.code == 0) {
            var data = resp.data;
            $('#music_value').html(numberWithCommas(data.music));
            $('#talk_value').html(numberWithCommas(data.talk));
            $('#ad_value').html(numberWithCommas(data.ad));
            $('#news_value').html(numberWithCommas(data.news));
            $('#traffic_value').html(numberWithCommas(data.traffic));
            $('#promo_value').html(numberWithCommas(data.promo));

            $('#music_text').css({ color : data.music_color});
            $('#talk_text').css({ color : data.talk_color});
            $('#advertisment_text').css({ color : data.ad_color});
            $('#news_text').css({ color : data.news_color});
            $('#traffic_text').css({ color : data.traffic_color});
            $('#promo_text').css({ color : data.promo_color});

            $('#music_percent').html((data.moments ? Math.round(data.music/data.moments * 100) : 0) + "%");
            $('#talk_percent').html((data.moments ? Math.round(data.talk/data.moments * 100) : 0)  + "%");
            $('#ad_percent').html((data.moments ? Math.round(data.ad/data.moments * 100) : 0)  + "%");
            $('#news_percent').html((data.moments ? Math.round(data.news/data.moments * 100) : 0)  + "%");
            $('#traffic_percent').html((data.moments ? Math.round(data.traffic/data.moments * 100) : 0)  + "%");
            $('#promo_percent').html((data.moments ? Math.round(data.promo/data.moments * 100) : 0)  + "%");

            $('#total_value').html(numberWithCommas(data.moments));

            moments = data.moments;

            if(data.moments != 0) {
                var dataDonut = [];
                var donutColors = [];
                if(Math.round(data.music/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'Music',
                        value: data.music
                    });
                    donutColors.push(data.music_color);
                }
                if(Math.round(data.talk/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'Talk',
                        value: data.talk
                    });
                    donutColors.push(data.talk_color);
                }
                if(Math.round(data.ad/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'Ad',
                        value: data.ad
                    });
                    donutColors.push(data.ad_color);
                }
                if(Math.round(data.news/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'News',
                        value: data.news
                    });
                    donutColors.push(data.news_color);
                }
                if(Math.round(data.traffic/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'Traffic',
                        value: data.traffic
                    });
                    donutColors.push(data.traffic_color);
                }
                if(Math.round(data.promo/data.moments * 100) != 0) {
                    dataDonut.push({
                        label: 'Promo',
                        value: data.promo
                    });
                    donutColors.push(data.promo_color);
                }

                if (contentTypesDonut == null) {
                    $('#contentTypesDonut').css('visibility', 'visible');
                    contentTypesDonut = Morris.Donut({
                        resize: true,
                        element: 'contentTypesDonut',
                        formatter: function (value, d) {
                            return numberWithCommas(value) + ' = ' + (getTotalMoments() ? Math.round(value / getTotalMoments() * 100) : 0) + "%"
                        },
                        data: dataDonut,
                        colors: donutColors
                        //valueColors: ['#37474f', '#f96868', '#76838f']
                    }).on('click', function (i, row) {
                        console.log(i, row);
                    });
                } else {
                    $('#contentTypesDonut').empty();
                    $('#contentTypesDonut').css('visibility', 'visible');
                    contentTypesDonut = Morris.Donut({
                        resize: true,
                        element: 'contentTypesDonut',
                        formatter: function (value, d) {
                            return numberWithCommas(value) + ' = ' + (getTotalMoments() ? Math.round(value / getTotalMoments() * 100) : 0) + "%"
                        },
                        data: dataDonut,
                        colors: donutColors
                        //valueColors: ['#37474f', '#f96868', '#76838f']
                    }).on('click', function (i, row) {
                        console.log(i, row);
                    });
                }
            } else {
                $('#contentTypesDonut').css('visibility','hidden');
            }
        }
        else {
            console.log(resp);
        }
    }).fail(function(resp) {
        console.log(resp);
    });
}

//Fifth Row
var sourceDonut;
function getSourceDonut(start, end) {

    $.ajax (
        {
            url: '/getSourceOfListeners',
            type: 'get'
        }
    ).done(function(resp) {
        if(resp.code == 0) {
            $('#stream_count').html(numberWithCommas(resp.streaming_users[0].count));

            if(sourceDonut != null) {
                $('#sourceDonut').empty();
            }
            sourceDonut = Morris.Donut({
                resize: true,
                element: 'sourceDonut',
                formatter: function (value, d) {
                    return value + "%"
                },
                data: [{
                    label: 'Terrestrial',
                    value: Math.round(resp.terrestrial / (resp.terrestrial + resp.stream) * 100)
                }, {
                    label: 'Streamed',
                    value: Math.round(resp.stream / (resp.terrestrial + resp.stream) * 100)
                }],
                colors: ['#9e9e9e', '#3583ca']
                //valueColors: ['#37474f', '#f96868', '#76838f']
            }).on('click', function (i, row) {
                console.log(i, row);
            });
        }
        else {
            console.log(resp);
        }
    }).fail(function(resp) {
        console.log(resp);
    });
}


function getStreamTime() {

    $.ajax(
        {
            url: '/getStreamTime',
            type: 'get'
        }
    ).done(function (resp) {
        var data = resp.data;
        var labels = [];
        var series = [];
        $.each(data, function (k, v) {
            labels.push(v.hour);
            series.push(v.count);
        });

        // options for style
        var options = {
            showArea: true,
            low: 0,
            height: 340,
            fullWidth: true,
            axisX: {
                offset: 30
            },
            axisY: {
                offset: 30,
                labelInterpolationFnc: function (value) {
                    if (value == 0) {
                        return null;
                    }
                    return value;
                },
                scaleMinSpace: 50
            },
            chartPadding: {
                bottom: 12,
                left: 30,
                right: 30
            },
            plugins: [
                Chartist.plugins.tooltip()
            ],
            lineSmooth: Chartist.Interpolation.simple({
                divisor: 4
            })
        };

        var labelList = labels;//[ '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22'];
        var series1List = {
            name: 'Activity',
            data: series
        };

        var newScoreLineChart = function (chartId, labelList, series1List, options) {

            var lineChart = new Chartist.Line(chartId, {
                labels: labelList,
                series: [series1List]
            }, options);

            //start create
            lineChart.on('draw', function (data) {
                var elem, parent;
                if (data.type === 'point') {
                    elem = data.element;
                    parent = new Chartist.Svg(elem._node.parentNode);

                    parent.elem('line', {
                        x1: data.x,
                        y1: data.y,
                        x2: data.x + 0.01,
                        y2: data.y,
                        "class": 'ct-point-content'
                    });
                }
            });
            //end create
        }

        newScoreLineChart("#timeStreamWidget .ct-chart", labelList,
            series1List, options);

        //Set the widget heights to be the same only after the graph has rendered in the time of day widget
        // $('#clicksPerUserWidget').height($('#timeOfDayWidget').height());

    }).fail(function (resp) {

    })
}

//Utility
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function getWeekRange(date) {
    var currentDate= moment(date);
    var start = moment(currentDate).startOf('isoweek');
    var end = moment(currentDate).endOf('isoweek');
    return start.format('MMM D') + ' - ' + end.format('MMM D') + ' ' + currentDate.format('YYYY');
}

//Other
function sendEmail() {
    if($('#email').val() === '') {
        $('#email_form_group').addClass('has-error');
        return;
    }
    
    $('#email_sending_icon').show();
    $.ajax(
        {
            url: '/emailPopularTags',
            type: 'post',
            data: {
                "date" : $('#date_for_tod').val(),
                "hour" : hourForPopularTagsEmail,
                "content_type_id" : contentTypeForPopularTagsEmail,
                "email" : $('#email').val()

            }
        }
    ).success(function(resp) {

        if(resp.code == 0) {
            $('#email_failed_icon').hide();
            $('#email_sent_icon').fadeIn();
            $('#email_form_group').removeClass('has-error');
        }
        else if(resp.code == -2) {
            $('#email_form_group').addClass('has-error');
            $('#email_failed_icon').fadeIn();
        }
        else {
            console.log(resp);
            $('#email_sent_icon').hide();
            $('#email_failed_icon').fadeIn();
        }

    }).fail(function(resp) {
        
        console.log(resp);
        $('#email_sent_icon').hide();
        $('#email_failed_icon').fadeIn();

    }).always(function() {

        $('#email_sending_icon').hide();

        setTimeout(function() {
            $('#email_sent_icon').fadeOut();
            $('#email_failed_icon').fadeOut();
        }, 3000);

    })
}

function disableDrillDown() {
    $('#timeOfDayWidget .ct-bar').off('click');
}