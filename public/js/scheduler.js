var talkShowList;
var talentList;
var preview = new MobilePreviewForm('mobilepreview_slider_container');

function setupModalClickListeners() {
	$('#create_talk_show').off('click').on('click', function () {
		createTalkShow();
	});
	$('#update_talk_show').off('click').on('click', function () {
		if (confirm('This will change all recurrences of this talk show from ' + $('#start_date').val() + ' to ' + $('#end_date').val() + '. Are you sure you want to save?')) {
			updateEvent();
		}
	});
	$('#update_single_talk_show').off('click').on('click', function () {
		if (confirm('This will change just this recurrence at ' + $('#current_date').val() + '. Are you sure you want to save?')) {
			updateSingleEvent();
		}
	});
	$('#delete_talk_show').off('click').on('click', function () {
		if (confirm('This will delete all recurrences of this talk show from ' + $('#start_date').val() + ' to ' + $('#end_date').val() + ' . If you want to delete only a single recurrence, please fill out the exceptions form. Are you sure you want to delete all recurrences?')) {
			deleteEvent();
		}
	});
	$('#delete_single_event').off('click').on('click', function () {
		if (confirm('This will delete just this recurrence at ' + $('#current_date').val() + '. Are you sure you want to delete?')) {
			deleteSingleEvent();
		}
	});
}
function unsetModalClickListeners() {
	$('#create_talk_show').off('click');
	$('#update_talk_show').off('click');
	$('#update_single_talk_show').off('click');
	$('#delete_talk_show').off('click');
	$('#delete_single_event').off('click');
}

//Auto complete stuff
function setupAutoCompleteForTalkShow(update) {

	if (update) {
		$( "#what" ).typeahead().data('typeahead').source = talkShowList;
		$( "#new_what" ).typeahead().data('typeahead').source = talkShowList;
		$( "#who" ).typeahead().data('typeahead').source = talentList;
		$( "#new_who" ).typeahead().data('typeahead').source = talentList;
	} else {
		$( "#what" ).typeahead({
			source: talkShowList
		});
		$( "#new_what" ).typeahead({
			source: talkShowList
		});
		$( "#who" ).typeahead({
			source: talentList
		});
		$( "#new_who" ).typeahead({
			source: talentList
		});
	}
}
function updateAutoCompleteForTalkShow() {

	$.ajax(
		{
			url: "/content/talkShowList",
			type: "get",
			dataType: "json",
			success: function (resp) {
				if (resp.code === 0 && resp.data) {
					talkShowList = resp.data;
					setupAutoCompleteForTalkShow(true);
				}
			}
		}
	).fail(function () {


	}).always(function () {

	});

	$.ajax(
		{
			url: "/content/talentList",
			type: "get",
			dataType: "json",
			success: function (resp) {
				if (resp.code === 0 && resp.data) {
					talentList = resp.data;
					setupAutoCompleteForTalkShow(true);
				}
			}
		}
	).fail(function () {


		}).always(function () {

		});
}

//Creating and modifying talk shows
function createTalkShow () {
	$('#new_event_loading').show();
	$('#newEventModal').modal('hide');
	unsetModalClickListeners();

	var weekdays = [];
	$('input[name=new_weekday]:checked').each(function() {
		weekdays.push($(this).val());
	});

	$.ajax (
		{
			url: "/content/createTalkShow",
			type: "post",
			dataType: "json",
			data: {
				"start_date" : $('#new_start_date').val(),
				"end_date" : $('#new_end_date').val(),
				"start_time":$('#new_start_time').val(),
				"end_time" : $('#new_end_time').val(),
				"what" : $('#new_what').val(),
				"who":$('#new_who').val(),
				"weekdays" : weekdays
			}
		}
	).done( function( resp ) {
		if (resp.code === 0) {
			console.log('save success');
			$('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
			var content=resp.data.content;
			$('#newEventModal').modal('hide');
			$('#new_event_loading').hide();

			$('#calendar').fullCalendar( 'refetchEvents' );

			//Update form
			$('#start_date').val(content.start_date);
			$('#end_date').val(content.end_date);
			$('#start_time').val(content.start_time);
			$('#end_time').val(content.end_time);
			$('#what').val(content.what);
			$('#who').val(content.who);
			$('#content_id').val(content.id);

			//Update preview
			preview.renderPreviewInfo('content', content.id, null, 'scheduler');

			console.log(resp.data.contentID);
			console.log(content);

			setupModalClickListeners();
		} else {
			console.log('error');
			$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			console.log(resp);
		}
	}).fail(function(resp) {
		console.log('error');
		$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		console.log(resp);
	});
}

function updateEvent () {

	$('#edit_event_loading').show();
	unsetModalClickListeners();
	$('#editEventModal').modal('hide');

	var weekdays = [];
	$('input[name=weekday]:checked').each(function() {
		weekdays.push($(this).val());
	});

	$.ajax (
		{
			url: "/content/updateEvent",
			type: "post",
			dataType: "json",
			data: {
				"id" : $('#content_id').val(),
				"start_date" : $('#start_date').val(),
				"end_date" : $('#end_date').val(),
				"start_time":$('#start_time').val(),
				"end_time" : $('#end_time').val(),
				"what" : $('#what').val(),
				"who":$('#who').val(),
				"weekdays" : weekdays
			}
		}
	).done(function(resp) {
			if (resp.code === 0) {
				console.log('save success');
				$('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
				var content = resp.data.content;
				$('#editEventModal').modal('hide');
				$('#edit_event_loading').hide();
				$('#calendar').fullCalendar( 'refetchEvents' );

				//Highlight current event
				var events = $("#calendar").fullCalendar( 'clientEvents', resp.data.content.id );
				$.each(events, function(index, value) {
					if(value.is_complete == 0) {
						value.className= "not-ready fc-state-highlight";
					}
					else {
						value.className = "fc-state-highlight";
					}
				});
				$('#calendar').fullCalendar( 'rerenderEvents' );

				//Update form
				$('#start_date').val(content.start_date);
				$('#end_date').val(content.end_date);
				$('#start_time').val(content.start_time);
				$('#end_time').val(content.end_time);
				$('#what').val(content.what);
				$('#who').val(content.who);
				$('#content_id').val(content.id);

				//Update preview
				preview.renderPreviewInfo('content', content.id, null, 'scheduler');

				setupModalClickListeners();
			} else {
				console.log('error');

				$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

				console.log(resp);
				//Hack. Trigger the cancel update button to revert the calendar event.
				$('#cancel_update').trigger('click');
			}
		}). fail(function(resp) {
			console.log('error');
			$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			console.log(resp);
			//Hack. Trigger the cancel update button to revert the calendar event.
			$('#cancel_update').trigger('click');
		});
}

function deleteEvent () {
	$('#edit_event_loading').show();
	unsetModalClickListeners();
	$('#editEventModal').modal('hide');

	$.ajax (
		{
			url: "/content/removeEvent",
			type: "post",
			dataType: "json",
			data: {
				"id" : $('#content_id').val()
			}
		}
	).done(function(resp) {
		if (resp.code === 0) {
			console.log('delete success');   
			$('.saveProgress').show().html('Success. The content has been deleted successfully').css('color', 'green');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
			$('#editEventModal').modal('hide');
			$('#edit_event_loading').hide();

			//Remove the old event
			//$("#calendar").fullCalendar( 'removeEvents', resp.data.id );
			$('#calendar').fullCalendar( 'refetchEvents' );
			//Update preview
			preview._resetFormData();

			setupModalClickListeners();
		} else {
			console.log('error');
			$('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			console.log(resp);
		}
	}).fail(function(resp) {
		console.log('error');
		$('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		console.log(resp);
	});
}

function deleteSingleEvent () {
	$('#edit_event_loading').show();
	unsetModalClickListeners();
	$('#editEventModal').modal('hide');

	$.ajax (
		{
			url: "/content/removeSingleEvent",
			type: "post",
			dataType: "json",
			data: {
				"id" : $('#content_id').val(),
				"beforeEndDate" : moment($('#current_date').val()).subtract(1, 'days').format("YYYY-MM-DD"),
				"afterStartDate" : moment($('#current_date').val()).add(1, 'days').format("YYYY-MM-DD")
			}
		}
	).done(function( resp ) {
		if (resp.code === 0) {
			console.log('delete success');   
			$('.saveProgress').show().html('Success. The content has been deleted successfully').css('color', 'green');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
			console.log(resp);
			$('#editEventModal').modal('hide');
			$('#edit_event_loading').hide();

			$('#calendar').fullCalendar( 'refetchEvents' );
			//clear the preview
			preview._resetFormData();

			setupModalClickListeners();
		} else {
			console.log('error');
			$('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			console.log(resp);
		}
	}).fail(function(resp) {
		console.log('error');
		$('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		console.log(resp);
	});
}

function updateSingleEvent () {
	$('#edit_event_loading').show();
	unsetModalClickListeners();
	$('#editEventModal').modal('hide');

	$.ajax (
		{
			url: "/content/updateSingleEvent",
			type: "post",
			dataType: "json",
			data: {
				"id" : $('#content_id').val(),
				"start_date" : $('#start_date').val(),
				"end_date" : $('#end_date').val(),
				"start_time":$('#start_time').val(),
				"end_time" : $('#end_time').val(),
				"what" : $('#what').val(),
				"who":$('#who').val(),
				"beforeEndDate" : moment($('#current_date').val()).subtract(1, 'days').format("YYYY-MM-DD"),
				"afterStartDate" : moment($('#current_date').val()).add(1, 'days').format("YYYY-MM-DD"),
				"currentDate" : $('#current_date').val()
			}
		}
	).done(function( resp ) {
		if (resp.code === 0) {
			console.log('save success');
			$('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
			$('#editEventModal').modal('hide');
			$('#edit_event_loading').hide();
			var event = resp.data.current;

			$('#calendar').fullCalendar( 'refetchEvents' );

			//Highlight current event
			var events = $("#calendar").fullCalendar( 'clientEvents', resp.data.current.id );
			$.each(events, function(index, value) {
				if(event.is_complete == false) {
					value.className= "not-ready fc-state-highlight";
				}
				else {
					value.className = "fc-state-highlight";
				}
			});
			$('#calendar').fullCalendar( 'rerenderEvents' );

			setupModalClickListeners();
		} else {
			console.log('error');
			$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			console.log(resp);
			//Hack. Trigger the cancel update button to revert the calendar event.
			$('#cancel_update').trigger('click');
		}
	}).fail(function(resp) {
		console.log('error');
		$('.saveProgress').show().html('Error. An error has occured while trying to make changes').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		console.log(resp);
		//Hack. Trigger the cancel update button to revert the calendar event.
		$('#cancel_update').trigger('click');
	});
}

$(document).ready(function() {
	updateAutoCompleteForTalkShow();
	setupModalClickListeners();
	var calendar = $('#calendar').fullCalendar({
		header: false,
		lazyFetching: false,
		//defaultDate: '2015-07-08',
		defaultView: 'agendaWeek',
		slotDuration: '00:60:00',
		snapDuration: '00:30:00',
		allDaySlot: false,
		height:'auto',
		firstDay: 1,
		editable: true,
		columnFormat:'ddd D/M',
		eventLimit: true, // allow "more" link when too many events
		events: {
			url: '/content/getTalkShows/',
			type: 'get',
			cache: true,
			error: function() {
				$('.saveProgress').show().html('Error. Could not load events. Please try again.').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			}
		},
		eventRender: function(event){
			return (event.ranges.filter(function(range){ // test event against all the ranges
					var startDate = moment(range.start);//.subtract(1,'days');
					var endDate = moment(range.end).add(1,'days');
					if(event.start === null || event.end === null) {
						return false;
					}
					return (event.start.isSameOrBefore(endDate) &&
					event.end.isSameOrAfter(startDate));
				}).length)>0; //if it isn't in one of the ranges, don't render it (by returning false)
		},
		eventResize: function(event, delta, revertFunc, jsEvent) {
			//Render preview screen
			preview.renderPreviewInfo('content', event.id, null, 'scheduler');

			//Hack to get current date
			var clickedDate = $.nearest({x: jsEvent.pageX, y: jsEvent.pageY}, '.fc-day').attr('data-date');

			//Un-highlight all events
			var allEvents = $("#calendar").fullCalendar( 'clientEvents');
			$.each(allEvents, function(index, value) {
				if(value.is_complete == false) {
					value.className = "not-ready";
				} else {
					value.className = "";
				}
			});

			//Highlight current event
			var events = $("#calendar").fullCalendar( 'clientEvents', event.id );
			$.each(events, function(index, value) {
				if(value.is_complete == false) {
					value.className= "not-ready fc-state-highlight";
				}
				else {
					value.className = "fc-state-highlight";
				}
			});
			$('#calendar').fullCalendar( 'rerenderEvents' );

			//Update modal data so that updateTalkShow has the latest information
			$('#start_date').val(event.ranges[0].start);
			$('#end_date').val(event.ranges[0].end);
			$('#start_date').datepicker('setDate',event.ranges[0].start);
			$('#end_date').datepicker('setDate',event.ranges[0].end);
			$('#start_time').val(getTimeFormat(event.start));
			$('#end_time').val(getTimeFormat(event.end));
			$('#what').val(event.title);
			$('#who').val(event.who);
			$('#current_date').val(clickedDate);
			$('#content_id').val(event.id);
			for(var i = 0; i < 7; i++) {
				$("input[name='weekday'][value="+i+"]").prop('checked', false);
			}
			for(var i = 0; i < event.dow.length; i++) {
				$("input[name='weekday'][value="+event.dow[i]+"]").prop('checked', true);
			}

			$('#updateEventModal').modal({backdrop:false});
			$('#cancel_update').click( function() {
				revertFunc();
			})
		},
		eventDrop: function(event, delta, revertFunc, jsEvent) {
			//Render preview screen
			preview.renderPreviewInfo('content', event.id, null, 'scheduler');

			//Hack to get current date
			var clickedDate = $.nearest({x: jsEvent.pageX, y: jsEvent.pageY}, '.fc-day').attr('data-date');

			//Un-highlight all events
			var allEvents = $("#calendar").fullCalendar( 'clientEvents');
			$.each(allEvents, function(index, value) {
				if(value.is_complete == false) {
					value.className = "not-ready";
				} else {
					value.className = "";
				}
			});

			//Highlight current event
			var events = $("#calendar").fullCalendar( 'clientEvents', event.id );
			$.each(events, function(index, value) {
				if(value.is_complete == false) {
					value.className= "not-ready fc-state-highlight";
				}
				else {
					value.className = "fc-state-highlight";
				}
			});

			$('#calendar').fullCalendar( 'rerenderEvents' );

			//Update modal data so that updateTalkShow has the latest information
			$('#start_date').val(event.ranges[0].start);
			$('#end_date').val(event.ranges[0].end);
			$('#start_date').datepicker('setDate',event.ranges[0].start);
			$('#end_date').datepicker('setDate',event.ranges[0].end);
			$('#start_time').val(getTimeFormat(event.start));
			$('#end_time').val(getTimeFormat(event.end));
			$('#what').val(event.title);
			$('#who').val(event.who);
			$('#current_date').val(clickedDate);
			$('#content_id').val(event.id);
			for(var i = 0; i < 7; i++) {
				$("input[name='weekday'][value="+i+"]").prop('checked', false);
			}
			for(var i = 0; i < event.dow.length; i++) {
				var day = event.dow[i] + delta._data.days;
				if(day < 0) {
					day = day + 7;
				}
				$("input[name='weekday'][value="+day+"]").prop('checked', true);
			}
			$('#updateEventModal').modal({backdrop:false});
			$('#cancel_update').click( function() {
				revertFunc();
			})

		},
		eventMouseover: function(event, jsEvent, view) {
			$(this).children('.fc-content').prepend('<span class="edit-event-button calendar-pencil" onclick="editEvent()">' +
				'<i class="mdi mdi-pencil" style="color: black; z-index:1; font-size:15px"></i></span>');
//                $(this).css('background-color', 'blue');
		},
		eventMouseout: function(event, jsEvent, view) {
			$('.calendar-pencil').remove();
		},
		eventClick: function(event, jsEvent, view) {
			//Clear the screen
			preview._resetFormData();

			//Render preview screen
			preview.renderPreviewInfo('content', event.id, function () {
				//Un-highlight all events
				var allEvents = $("#calendar").fullCalendar( 'clientEvents');
				$.each(allEvents, function(index, value) {
					if(value.is_complete == false) {
						value.className = "not-ready";
					} else {
						value.className = "";
					}
				});

				//Highlight current event
				var events = $("#calendar").fullCalendar( 'clientEvents', event.id );
				$.each(events, function(index, value) {
					if(value.is_complete == false) {
						value.className= "not-ready fc-state-highlight";
					}
					else {
						value.className = "fc-state-highlight";
					}
				});
				$('#calendar').fullCalendar( 'rerenderEvents' );
			}, 'scheduler');

			//Hack to get current date
			var clickedDate = $.nearest({x: jsEvent.pageX, y: jsEvent.pageY}, '.fc-day').attr('data-date');


			//Setup data for modal
			$('#start_date').val(event.ranges[0].start);
			$('#end_date').val(event.ranges[0].end);
			$('#start_date').datepicker('setDate',event.ranges[0].start);
			$('#end_date').datepicker('setDate',event.ranges[0].end);
			$('#start_time').val(getTimeFormat(event.start));
			$('#end_time').val(getTimeFormat(event.end));
			$('#what').val(event.title);
			$('#who').val(event.who);
			$('#current_date').val(clickedDate);
			$('#content_id').val(event.id);
			for(var i = 0; i < 7; i++) {
				$("input[name='weekday'][value="+i+"]").prop('checked', false);
			}
			for(var i = 0; i < event.dow.length; i++) {
				$("input[name='weekday'][value="+event.dow[i]+"]").prop('checked', true);
			}
		},
		dayClick: function(date, jsEvent, view) {
			preview._resetFormData();

			contentData = null;
			//Un-highlight all events
			var allEvents = $("#calendar").fullCalendar( 'clientEvents');
			$.each(allEvents, function(index, value) {
				if(value.is_complete == false) {
					value.className = "not-ready";
				} else {
					value.className = "";
				}
			});

			$('#calendar').fullCalendar( 'rerenderEvents' );
//                $('#calendar').fullCalendar('select', date.start, date.end);
			//Clear previous entries
			for(var i = 0; i < 7; i++) {
				$("input[name='new_weekday'][value="+i+"]").prop('checked', false);
			}

			$('#new_what').val('');
			$('#new_who').val('');

			var formatted_date = date.format();
			if(formatted_date.indexOf('T') > 0) {
				var formatted_date_split = formatted_date.split('T');
				var start_date = moment(formatted_date_split[0]).startOf('isoweek');
				$('#new_start_date').datepicker('setDate',start_date.format('YYYY-MM-DD'));
				$('#new_end_date').datepicker('setDate',start_date.add(6, 'days').format('YYYY-MM-DD'));
				$('#new_start_time').val(getTimeFormat(moment(formatted_date)));
				$('#new_end_time').val(getTimeFormat(moment(formatted_date).add(1, 'hours')));

				var temp_date = new Date(formatted_date_split[0]);
				var day = temp_date.getDay();
				$("input[name='new_weekday'][value="+day+"]").prop('checked', true);
			}

			else {
				$('#new_start_date').val(formatted_date);

				var temp_date = new Date(formatted_date);
				var day = temp_date.getDay();
				$("input[name='new_weekday'][value="+day+"]").prop('checked', true);
			}

			$('#newEventModal').modal({backdrop:false});


		},
		viewRender: function(view, element) {
			//clear the preview
			preview._resetFormData();
			contentData = null;
		},
		loading: function(isLoading, view) {
			if(isLoading) {
				$('#calendar_loader').removeClass('hide');
			} else {
				$('#calendar_loader').addClass('hide');
			}
		},
		//Hacky...Make sure current event is highlighted after each calendar refetch.
		eventAfterAllRender: function (view) {
			//Highlight current event
			if(!contentData || !contentData.id) {
				return;
			}
			var events = $("#calendar").fullCalendar( 'clientEvents', contentData.id );
			$.each(events, function(index, value) {
				if(value.className.indexOf('fc-state-highlight') >= 0) {
					return;
				}
				if(value.is_complete == false) {
					value.className= "not-ready fc-state-highlight";
				}
				else {
					value.className = "fc-state-highlight";
				}
				$("#calendar").fullCalendar('updateEvent', value);
			});
		}

	});

	//$('.fc-left').append('<input type="text" id="goto_date" class="form-control" style="height:10px; width:30px; margin-top:8px;">');
	$('#goto_date').datepicker({
		autoclose:  true,
		format: 'yyyy-mm-dd'
	});
	$('#goto_date').datepicker('setDate',moment().format('YYYY-MM-DD'));

	$('#current_week').click(function () {
		$('#goto_date').datepicker('show');
	});

	$('#current_week').val(getWeekRange());

	$('#goto_date').datepicker().on('changeDate', function() {
		$('#goto_date').blur();
		if($('#goto_date').val().match(/^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$/)) {
			$('#calendar').fullCalendar('gotoDate', $('#goto_date').val());

			$('#current_week').val(getWeekRange());
		}
	});

	$('.fc-prev-button').click(function () {
		$('#calendar').fullCalendar('prev');

		$('#goto_date').datepicker('setDate',moment($('#calendar').fullCalendar('getDate')).format('YYYY-MM-DD'));
		$('#current_week').val(getWeekRange());
	});

	$('.fc-next-button').click(function () {
		$('#calendar').fullCalendar('next');

		$('#goto_date').datepicker('setDate',moment($('#calendar').fullCalendar('getDate')).format('YYYY-MM-DD'));
		$('#current_week').val(getWeekRange());
	});

	$('.fc-today-button').click(function () {
		$('#calendar').fullCalendar('today');

		$('#goto_date').datepicker('setDate',moment($('#calendar').fullCalendar('getDate')).format('YYYY-MM-DD'));
		$('#current_week').val(getWeekRange());
	});
	
	$('#content_content_type_id').on("change", function() {
		var contentType = $('#content_content_type_id').val();
		if (contentType == 0 || contentType == ContentTypeIDOfTalkShow) return;
		document.location = '/content?initialContentTypeID=' + encodeURIComponent(contentType) + '&initialFormMode=search';
	});
});

function getTimeFormat(time) {
	var result = moment(time).format("h:mma");
	return result;
}

function getWeekRange() {
	var currentDate= $('#calendar').fullCalendar('getDate');
	var start = moment(currentDate).startOf('week');
	var end = moment(currentDate).endOf('week');
	return start.format('MMM D') + ' - ' + end.format('MMM D') + ' ' + currentDate.format('YYYY');
}

////----- custom view:
//var FC = $.fullCalendar; // a reference to FullCalendar's root namespace
//var View = FC.AgendaView;      // the class that all views must inherit from
//var CustomView = View.extend({
//
//})
//
//FC.views.custom = CustomView; // register our class with the view system