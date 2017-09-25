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



$(document).ready(function() {

    map = new google.maps.Map(document.getElementById('map'));
    $('#start_date_for_map').datepicker('setDate', moment().format('YYYY-MM-DD'));
    $('#start_date_for_map_text').html(moment().format('DD MMM YYYY') + '<span class="caret"></span>');
    $('#end_date_for_map').datepicker('setDate', moment().format('YYYY-MM-DD'));
    $('#end_date_for_map_text').html(moment().format('DD MMM YYYY') + '<span class="caret"></span>');
    getEventLocations();
    
    Site.run();
});

//Map buttons and datepicker

$('#map_range_button').click(function() {
    $('#map_range_button').hide();
    $('#end_date_for_map_wrapper').show();
});

$('#start_date_for_map').datepicker({
    autoclose: true,
    orientation: 'top auto',
    format:'yyyy-mm-dd'
});

$('#start_date_for_map').datepicker().on('changeDate', function() {
    $('#start_date_for_map_text').html(moment($('#start_date_for_map').val()).format('DD MMM YYYY') + '<span class="caret"></span>');
    //If we aren't in range mode, set the end date to be the same as start date
    if(!$('#end_date_for_map_wrapper').is(':visible')) {
        $('#end_date_for_map').datepicker('setDate', $('#start_date_for_map').val());
        return;
    }
    $('#start_time_map').val('0');
    $('#end_time_map').val('24');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#all_day_button').addClass('active');
    getEventLocations();
});

$('#start_date_for_map_text').click(function () {
    $('#start_date_for_map').datepicker('show');
});


$('#end_date_for_map').datepicker({
    autoclose: true,
    orientation: 'top auto',
    format:'yyyy-mm-dd'
});

$('#end_date_for_map').datepicker().on('changeDate', function() {
    $('#end_date_for_map_text').html(moment($('#end_date_for_map').val()).format('DD MMM YYYY') + '<span class="caret"></span>');
    $('#start_time_map').val('0');
    $('#end_time_map').val('24');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#all_day_button').addClass('active');
    getEventLocations();
});

$('#end_date_for_map_text').click(function () {
    $('#end_date_for_map').datepicker('show');
});

var heatmapEnabled = false;

$('#all_day_button').click(function() {
    $('#start_time_map').val('0');
    $('#end_time_map').val('24');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#all_day_button').addClass('active');
    getEventLocations();
});
$('#breakfast_button').click(function() {
    $('#start_time_map').val('6');
    $('#end_time_map').val('9');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#breakfast_button').addClass('active');
    getEventLocations();
});
$('#morning_button').click(function() {
    $('#start_time_map').val('9');
    $('#end_time_map').val('12');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#morning_button').addClass('active');
    getEventLocations();
});
$('#afternoon_button').click(function() {
    $('#start_time_map').val('12');
    $('#end_time_map').val('16');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#afternoon_button').addClass('active');
    getEventLocations();
});
$('#drive_button').click(function() {
    $('#start_time_map').val('16');
    $('#end_time_map').val('18');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#drive_button').addClass('active');
    getEventLocations();
});
$('#evening_button').click(function() {
    $('#start_time_map').val('18');
    $('#end_time_map').val('22');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#evening_button').addClass('active');
    getEventLocations();
});
$('#late_button').click(function() {
    $('#start_time_map').val('22');
    $('#end_time_map').val('24');
    $('#mapWidgetButtons .btn').removeClass('active');
    $('#late_button').addClass('active');
    getEventLocations();
});

$('#heatmap_button').click(function() {
    if(heatmapEnabled) {
        $('#heatmap_button').removeClass('enabled');
        heatmapEnabled = false;
        getEventLocations();
    } else {
        $('#heatmap_button').addClass('enabled');
        heatmapEnabled = true;
        getEventLocations();
    }
});

var map = null;
var markers = [];
var heatmap = null;
var centerAndZoomSet = false;
function getEventLocations () {

    $.ajax (
        {
            url: '/getEventLocationsByDate/' + $('#start_date_for_map').val() + '/'
            + $('#end_date_for_map').val() + '/'
            + $('#start_time_map').val() + '/'
            + $('#end_time_map').val(),
            type: 'get'
        }
    ).done(function(resp) {
        if(resp.code == 0) {
            if(!centerAndZoomSet) {
                map.setCenter({lat: Number(resp.center_lat), lng: Number(resp.center_lng)});
                map.setZoom(10);
                centerAndZoomSet = true;
                // setRegions();
            }
            
            $.each(markers, function(key,val) {
                val.setMap(null);
            });
            markers = [];
            dataPoints = [];
            var data = resp.data;

            if(heatmap != null) {
                heatmap.setMap(null);
            }

            if(heatmapEnabled) {
                // http://stackoverflow.com/questions/1253499/simple-calculations-for-working-with-lat-lon-km-distance
                var latDiff = 150 / 110.574; //150km, should be using resp.radius, but 150km is fine for now
                var lngDiff = 150 / (111.320 * Math.cos(latDiff));
                $.each(data, function (key, val) {
                    if(Math.abs(Number(val.lat) - Number(resp.center_lat)) < latDiff
                        && Math.abs(Number(val.lng) - Number(resp.center_lng)) < lngDiff ) {
                        dataPoints.push(new google.maps.LatLng(Number(val.lat), Number(val.lng)));
                    }
                });

                heatmap = new google.maps.visualization.HeatmapLayer({
                    data: dataPoints,
                    // dissipating: false,
                    opacity: 0.9,
                    map: map
                });

                heatmap.set('radius', 20);
            } else {
                $.each(data, function (key, val) {
                    var infowindow = new google.maps.InfoWindow({
                        content: 'Recorded at ' + moment.unix(Number(val.timestamp)).tz(tzFormatted).format("hh:mma DD MMM YYYY")
                    });
                    var marker = new google.maps.Marker({
                        title: 'Recorded at ' + moment.unix(Number(val.timestamp)).tz(tzFormatted).format("hh:mma DD MMM YYYY"),
                        position: {lat: Number(val.lat), lng: Number(val.lng)},
                        map: map,
                        icon: '/img/mapDot2.png'
                    });
                    markers.push(marker);
                    marker.addListener('click', function() {
                        infowindow.open(map, marker);
                    });
                });
            }

        } else {
            console.log(resp);
        }
    }).fail(function(resp) {
        console.log(resp);
    });
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