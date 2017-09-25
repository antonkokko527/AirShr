/*!
 * remark (http://getbootstrapadmin.com/remark)
 * Copyright 2015 amazingsurge
 * Licensed under the Themeforest Standard Licenses
 */
(function(document, window, $) {
  'use strict';

  var Site = window.Site;

  $(document).ready(function($) {
    Site.run();
  });

  // Top Line Chart With Tooltips
  // ------------------------------
  (function() {

    // common options for common style
    var options = {
      showArea: true,
      low: 0,
      high: 8000,
      height: 240,
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
          return value / 1000 + 'k';
        },
        scaleMinSpace: 40
      },
      plugins: [
        Chartist.plugins.tooltip()
      ]
    };

    //day data
    var dayLabelList = ['AUG 8', 'SEP 15', 'OCT 22', 'NOV 29', 'DEC 8', 'JAN 15', 'FEB 22', ''];
    var daySeries1List = {
      name: 'series-1',
      data: [0, 7300, 6200, 6833, 7568, 4620, 4856, 2998]
    };
    var daySeries2List = {
      name: 'series-2',
      data: [0, 3100, 7200, 5264, 5866, 2200, 3850, 1032]
    };

    //week data
    var weekLabelList = ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', ''];
    var weekSeries1List = {
      name: 'series-1',
      data: [0, 2400, 6200, 7833, 5568, 3620, 4856, 2998]
    };
    var weekSeries2List = {
      name: 'series-2',
      data: [0, 4100, 6800, 5264, 5866, 3200, 2850, 1032]
    };

    //month data
    var monthLabelList = ['AUG', 'SEP', 'OCT', 'NOV', 'DEC', 'JAN', 'FEB', ''];
    var monthSeries1List = {
      name: 'series-1',
      data: [0, 6400, 5200, 7833, 5568, 3620, 5856, 0]
    };
    var monthSeries2List = {
      name: 'series-2',
      data: [0, 3100, 4800, 5264, 6866, 3200, 2850, 1032]
    };

    var newScoreLineChart = function(chartId, labelList, series1List, series2List, options) {

      var lineChart = new Chartist.Line(chartId, {
        labels: labelList,
        series: [series1List, series2List]
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

    //finally new a chart according to the state
    var createKindChart = function(clickli) {
      var clickli = clickli || $("#productOverviewWidget .product-filters").children(".active");
      var chartId = clickli.children("a").attr("href");
      switch (chartId) {
        case "#scoreLineToDay":
          newScoreLineChart(chartId, dayLabelList,
            daySeries1List, daySeries2List, options);
          break;
        case "#scoreLineToWeek":
          newScoreLineChart(chartId, weekLabelList,
            weekSeries1List, weekSeries2List, options);
          break;
        case "#scoreLineToMonth":
          newScoreLineChart(chartId, monthLabelList,
            monthSeries1List, monthSeries2List, options);
          break;
      }
    }

    //default create chart whithout click
    createKindChart();

    //create for click
    $(".product-filters li").on("click", function() {
      createKindChart($(this));
    });

  })();

  //// Overlapping Bars One ~ Four
  // ------------------------------
  (function() {
    //Four Overlapping Bars Data
    var overlappingBarsDataOne = {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [
        [3, 4, 6, 10, 8, 6, 3, 4],
        [2, 3, 5, 8, 6, 5, 4, 3]
      ]
    };
    var overlappingBarsDataTwo = {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [
        [2, 4, 5, 10, 6, 8, 3, 5],
        [3, 5, 6, 5, 4, 6, 3, 3]
      ]
    };
    var overlappingBarsDataThree = {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [
        [5, 2, 6, 7, 10, 8, 6, 5],
        [4, 3, 5, 6, 8, 6, 4, 3]
      ]
    };
    var overlappingBarsDataFour = {
      labels: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
      series: [
        [2, 1, 5, 6, 7, 10, 8, 5],
        [4, 3, 4, 5, 5, 8, 6, 3]
      ]
    };

    //define an array contains four bar's data
    var barsData = [overlappingBarsDataOne, overlappingBarsDataTwo, overlappingBarsDataThree, overlappingBarsDataThree];

    //Common OverlappingBarsOptions 
    var overlappingBarsOptions = {
      low: 0,
      high: 10,
      seriesBarDistance: 6,
      fullWidth: true,
      axisX: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      axisY: {
        showLabel: false,
        showGrid: false,
        offset: 0
      },
      chartPadding: {
        //   top: 20,
        //   right: 115,
        //   bottom: 55,
        left: 30
      },
    };

    var responsiveOptions = [
      ['screen and (max-width: 640px)', {
        seriesBarDistance: 6,
        axisX: {
          labelInterpolationFnc: function(value) {
            return value[0];
          }
        }
      }]
    ];

    // create Four Bars
    var createBar = function(chartId, data, options, responsiveOptions) {
      new Chartist.Bar(chartId, data, options, responsiveOptions);
    }

    $("#productOptionsData .ct-chart").each(function(index) {
      createBar(this, barsData[index], overlappingBarsOptions, responsiveOptions);
    });

  })();

  //// Stacked Week Bar Chart
  // ------------------------------
  (function() {
    new Chartist.Bar('#weekStackedBarChart', {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        series: [
          [4, 4.5, 5, 6, 7, 7.5, 7],
          [6, 5.5, 5, 4, 3, 2.5, 3],
        ]
      }, {
        stackBars: true,
        axisY: {
          offset: 0
        },
        axisX: {
          offset: 60
        }
      }

    ).on('draw', function(data) {
      if (data.type === 'bar') {
        data.element.attr({
          style: 'stroke-width: 20px'
        });
      }
    });
  })();


  // Example Morris Donut
  // ---------------------
  (function() {

    getContentTypePercentages();

  })();

})(document, window, jQuery);

var donut = null;

//Sort of hacky way to make sure the total moments to calculate the percentage in the donut is dynamically updated
var moments = 0;
function getTotalMoments() {
  return moments;
}

$('#date1').datepicker({
  autoclose: true,
  format:'yyyy-mm-dd'
});

$('#date1').on('change', function() {
  getContentTypePercentages();
  $('#week_range1').val(getWeekRange());
});

$('#week_range1').click(function () {
  $('#date1').datepicker('show');
});

$('#all_time_button').click(function () {
  $('#week_range1').val("All Time");
  $('#date1').val(0);
  getContentTypePercentages();
});

$('#this_week_button').click(function () {
  $('#date1').datepicker('setDate', moment().startOf('isoweek').format('YYYY-MM-DD'));
  $('#week_range1').val("This Week");
  getContentTypePercentages();
});

$('#last_week_button').click(function () {
  $('#date1').datepicker('setDate',moment().subtract(1, 'week').startOf('isoweek').format('YYYY-MM-DD'));
  $('#week_range1').val("Last Week");
  getContentTypePercentages();
});


function getContentTypePercentages() {

  $.ajax (
      {
        url: '/getContentTypePercentages/' + $('#date1').val(),
        type: 'get'
      }
  ).done(function(resp) {
    if(resp.code == 0) {
      var data = resp.data;
      $('#music_value').html(data.music);
      $('#talk_value').html(data.talk);
      $('#ad_value').html(data.ad);
      $('#news_value').html(data.news);

      $('#music_text').css({ color : data.music_color});
      $('#talk_text').css({ color : data.talk_color});
      $('#advertisment_text').css({ color : data.ad_color});
      $('#news_text').css({ color : data.news_color});

      $('#music_percent').html((data.moments ? Math.round(data.music/data.moments * 100) : 0) + "%");
      $('#talk_percent').html((data.moments ? Math.round(data.talk/data.moments * 100) : 0)  + "%");
      $('#ad_percent').html((data.moments ? Math.round(data.ad/data.moments * 100) : 0)  + "%");
      $('#news_percent').html((data.moments ? Math.round(data.news/data.moments * 100) : 0)  + "%");

      $('#total_value').html(data.moments);

      moments = data.moments;


      if(donut == null) {
        $('#contentTypesDonut').css('visibility','visible');
        donut = Morris.Donut({
          resize: true,
          element: 'contentTypesDonut',
          formatter: function (value, d) {
            return value + ', ' + (getTotalMoments() ? Math.round(value / getTotalMoments() * 100) : 0) + "%"
          },
          data: [{
            label: 'Music',
            value: data.music
          }, {
            label: 'Talk',
            value: data.talk
          }, {
            label: 'Ad',
            value: data.ad
          }, {
            label: 'News',
            value: data.news
          }],
          colors: [data.music_color, data.talk_color, data.ad_color, data.news_color]
          //valueColors: ['#37474f', '#f96868', '#76838f']
        }).on('click', function (i, row) {
          console.log(i, row);
        });
      } else {
        if(data.moments != 0) {
          $('#contentTypesDonut').css('visibility','visible');
          donut.setData([{
            label: 'Music',
            value: data.music
          }, {
            label: 'Talk',
            value: data.talk
          }, {
            label: 'Ad',
            value: data.ad
          }, {
            label: 'News',
            value: data.news
          }]);
        }
        else {
          $('#contentTypesDonut').css('visibility','hidden');
        }
      }
    }
  }).fail(function(resp) {

  });
};


function getWeekRange() {
  var currentDate= moment($('#date1').val());
  var start = moment(currentDate).startOf('isoweek');
  var end = moment(currentDate).endOf('isoweek');
  return start.format('MMM D') + ' - ' + end.format('MMM D') + ' ' + currentDate.format('YYYY');
}