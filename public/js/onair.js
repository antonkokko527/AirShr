var DEFAULT_RELOAD_INTERVAL = 90000;
var PREVIEWTAG_PRUNE_RANGE_COUNT = 10;		// check for next 10 tags
var PREVIEWTAG_PRUNE_RANGE_DURATION = 480 * 1000;		// check for next 8 mins

var OnAirForm = function(mode) {
	
	this.mode = mode ? mode : 'onair';
	
	this.prevTags = new Array();
	this.pastTags = new Array();
	this.currentTag = null;
	
	this.contentAssociation = new Array();
	
	this.fetchTimestamp = 0;
	this.fetching = false;
	
	this.progressScale = 100;
	
	this.initTable();
	this.loadAirData(true);
	
	this.initialLoading = true;
	
	this.selectedTag = null;
	this.lastSelectTagType = null;
	this.selectedRow = null;
	this.isCurrentTagSelected = false;

	this.filterMode = false;
	
	this.previewForm = new MobilePreviewForm('mobilepreview_slider_container');
	
	var that = this;
	this.webSocketConnector = new WebSocketConnector(WebSocketURL, function(data){
		that.processWebSocketMessage(data);
	}, function(){
		that.startWebsocketMsgCheckTimer(DEFAULT_RELOAD_INTERVAL);
	});
	
	if (this.isOnAirMode()) {
		$('#btn_competition_result').off('click').on('click', function(){
			
			that.loadCompetitionResultContent();
			
		});
	}

	$('#search_tags_button').off('click').on('click', function() {
		that._filterTags();
	});

	$('#search_tags_text').on('change', function(e) {
		// if(e.keyCode == 13) {
		that._filterTags();
		// }

		// $('#search_tags_text').next('.typeahead').on('click',function(e2) {
		// 	that._filterTags();
		// });
	});

	$('#clear_search').on('click', function() {
		$('#search_tags_text').val('');
		$('#filter_airshrd').prop('checked', false);
		$('#filter_airshrd_container').css({visibility:'hidden'});
		that._filterTags();
	});

	$('#search_tags_text').focus( function() {
		$('#filter_airshrd_container').css({visibility:'visible'});
	});
	// $('#search_tags_text').blur( function() {
	// 	$('#filter_airshrd_container').hide();
	// });
	// $('#filter_airshrd_container').click(function() {
	// 	$('#filter_airshrd_container').show();
	// })

	$('#filter_airshrd').on('click', function() {
		that._filterTags();
	})
}

OnAirForm.prototype._startTalk = function(){

	var that = this;
	
	showGlobalLoading();

	$('.saveProgress').show().html('Loading...').css('color', 'green');
	
	$.ajax ( 
		{
			url: "/content/createManualTag",
			type: "post",
			data: {
				"type": "Talk",
				"original_who" : "AirShr Talk"
			},
			dataType: "json",
			success: function( resp ) {
				if (resp.code == 0) {
					
					that.setupStartStopTalkButtonMode(false);

					$('.saveProgress').show().html('Success. Talk segment has started.').css('color', 'green');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
					
				} else {
					$('.saveProgress').show().html('Error. ' + resp.msg ? resp.msg : '').css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Error. Network Error').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

	}).always( function () {
		
		hideGlobalLoading();
	});
	
}

OnAirForm.prototype._stopTalk = function(){
	
	var that = this;
	
	showGlobalLoading();

	$('.saveProgress').show().html('Loading...').css('color', 'green');

	$.ajax ( 
		{
			url: "/content/createManualTag",
			type: "post",
			data: {
				"type": "Sweeper",
				"original_who" : "AirShr Sweeper"
			},
			dataType: "json",
			success: function( resp ) {
				if (resp.code == 0) {
					
					that.setupStartStopTalkButtonMode(true);

					$('.saveProgress').show().html('Success. Talk segment has ended.').css('color', 'green');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
					
				} else {
					$('.saveProgress').show().html('Error. ' + resp.msg ? resp.msg : '').css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Error. Network Error').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		
	}).always( function () {
		
		hideGlobalLoading();
	});
}

OnAirForm.prototype.setupStartStopTalkButtonMode = function(start) {
	
	var that = this;
	
	if (start) {
		$('#btn_startstop_talk').addClass("btn-round-green");
		$('#btn_startstop_talk').removeClass("btn-round-red");
		$('#btn_startstop_talk').html('START TALK');
		$('#btn_startstop_talk').off('click').on('click', function(){
			that._startTalk();
		});
		
	} else {
		$('#btn_startstop_talk').removeClass("btn-round-green");
		$('#btn_startstop_talk').addClass("btn-round-red");
		$('#btn_startstop_talk').html('STOP TALK');
		$('#btn_startstop_talk').off('click').on('click', function(){
			that._stopTalk();
		});
	}
	
}


OnAirForm.prototype.loadCompetitionResultContent = function() {
	
	if (!$('#competitionresult_sidebar').hasClass('hidden')) {
		$('#competitionresult_sidebar').addClass('hidden');
		return;
	}
	
	if (!this.selectedTag) return;
	
	if (this.selectedTag.is_competition != 1) return;
	
	showLoading();
	
	var that = this;
	
	$.ajax ( 
		{
			url: "/content/getCompetitionResultContent?tag_id=" + this.selectedTag.id,
			type: "get",
			success: function( resp ) {
				$('#competitionresult_sidebar').html(resp);
			}
		}
	).fail ( function () {
		
		//alert("Initial data loading has failed. Please reload this page.");
		$('#competitionresult_sidebar').html('<p>Network error.</p>');
		
	}).always( function () {
		
		hideLoading();
		
		$('#competitionresult_sidebar').removeClass('hidden');
		
	});
}


OnAirForm.prototype.getContentAssociation = function() {
	return this.contentAssociation;
}

OnAirForm.prototype.resetContentAssociation = function(values) {
	
	this.contentAssociation = new Array();
	
	for (var index in this.prevTags) {
		var item = this.prevTags[index];
		this.removeContentAssociation('prev', item.id);
	}
	
	for (var index in this.pastTags) {
		var item = this.pastTags[index];
		this.removeContentAssociation('past', item.id);
	}
	
	if (this.currentTag) {
		this.removeContentAssociation('current', this.currentTag.id);
	}
	
	for (var index in values) {
		var item = values[index];
		this.addContentAssociation(item.assoc_type, item.assoc_id, item.assoc_timestamp);
	}
}

OnAirForm.prototype.isOnAirMode = function() {
	return this.mode == 'onair';
}

OnAirForm.prototype.isTalkAssignMode = function() {
	return this.mode == 'talk_assign';
}

OnAirForm.prototype.startRefreshTimer = function(duration) {
	var that = this;
	
	if (this.refreshTimer) {
		clearTimeout(this.refreshTimer);
		this.refreshTimer = null;
	}
	
	if (duration > 0) {
		this.refreshTimer = setTimeout(function(){
			console.log('refreshing data.');
			that.loadAirData();
		}, duration);
	}
}

OnAirForm.prototype.startWebsocketMsgCheckTimer = function(duration) {
	var that = this;
	
	if (this.websocketTimer) {
		clearTimeout(this.websocketTimer);
		this.websocketTimer = null;
	}
	
	if (duration > 0) {
		this.websocketTimer = setTimeout(function(){
			that.webSocketConnector.forceNewConnection();
		}, duration);
	}
}


OnAirForm.prototype.notReceivedWebSocketMessageForWhile = function() {
	
}

OnAirForm.prototype.processWebSocketMessage = function(data) {
	
	console.log(data);
	var data = JSON.parse(data);
	
	if (data.event == "NEWTAG") {		
		if (data.tag.station_id != GLOBAL.STATION_ID) return;   // process only tags for current station
		var newTag = TagModel.CreateFromJSON(data.tag, 'current');
		if (newTag.timestamp > this.fetchTimestamp) {
			this._addNewCurrentTag(newTag);
		}
		
		var nextExpectedMsgDuration = DEFAULT_RELOAD_INTERVAL;
		if (newTag.duration > 0) {
			nextExpectedMsgDuration = (newTag.duration + 5) * 1000;
		}
		this.startWebsocketMsgCheckTimer(nextExpectedMsgDuration);
				
	} else if (data.event == 'TAG_COUNT_UPDATE') {
		if (data.tag.station_id != GLOBAL.STATION_ID) return;   // process only tags for current station
		console.log('event count updated.');
		console.log(data.tag);
		this._updateTagEventCount(data.tag);
		
		var nextExpectedMsgDuration = DEFAULT_RELOAD_INTERVAL;
		this.startWebsocketMsgCheckTimer(nextExpectedMsgDuration);
		
	} else if (data.event == 'TAG_VOTE_COUNT_UPDATE') {
		if (data.tag.station_id != GLOBAL.STATION_ID) return;   // process only tags for current station
		console.log('tag vote count updated.');
		console.log(data.tag);
		this.previewForm.updateVoteOptionCounts(data.tag.id, data.tag.vote_option1_count, data.tag.vote_option2_count);
		var nextExpectedMsgDuration = DEFAULT_RELOAD_INTERVAL;
		this.startWebsocketMsgCheckTimer(nextExpectedMsgDuration);
	}
}

OnAirForm.prototype.loadAirData = function(displayLoadingBar) {
	
	if (this.fetching) return;
	
	this.fetching = true;
	
	if (displayLoadingBar) showGlobalLoading();
	
	var that = this;
	
	console.log('Loading on air data');
	
	$.ajax ( 
		{
			url: "/content/air/airData",
			type: "post",
			dataType: "json",
			data: {
				timestamp: that.fetchTimestamp,
				loadPreviewTags : that.fetchTimestamp == 0 ? 'true' : ''
			},
			success: function( resp ) {
				if (resp.code === 0 && resp.data != undefined) {
					that._populateTags(resp.data.prev_tags, resp.data.past_tags, resp.data.current_tag);
				} else {
					//alert("Initial data loading has failed. Please reload this page.");
					//console.log('air data loading has failed.');
				}
			}
		}
	).fail ( function () {
		
		//alert("Initial data loading has failed. Please reload this page.");
		
	}).always( function () {
		
		hideGlobalLoading();
		that.fetching = false;
	});
	
}

OnAirForm.prototype.setTagAsCompetition = function(id) {
	var tag = this.findTagById(id);
	if (!tag) return;
	tag.is_competition = 1;
}

OnAirForm.prototype.setTagAsVote = function(id) {
	var tag = this.findTagById(id);
	if (!tag) return;
	tag.is_vote = 1;
}

OnAirForm.prototype.findTagById = function(id) {
	
	if (this.currentTag) {
		if (this.currentTag.id == id) return this.currentTag;
	}
	
	for (var index in this.pastTags) {
		var item = this.pastTags[index];
		if (item.id == id) return item;
	}
	
	for (var index in this.prevTags) {
		var item = this.prevTags[index];
		if (item.id == id) return item;
	}
	
	return null;
}

OnAirForm.prototype.setupShare = function() {

	var that = this;

	$('#past-airtag-container tbody tr').hover( function() {
		$('.share_button').css({'visibility' : 'hidden'});
		$(this).find('.share_button').css({'visibility' : 'visible'});
	});

	$('#current-airtag-container').hover( function() {
		$('.share_button').css({'visibility' : 'hidden'});
		$(this).find('.share_button').css({'visibility' : 'visible'});
	});

	$('.share_button').off('click').on('click', function() {

		console.log('clicked');

		var button = $(this);

		if(button.hasClass('current_share')) {
			that.isCurrentTagSelected = true;
		}
		else {
			that.isCurrentTagSelected = false;
		}

		$('#shareModal').modalPopover({
			modalPosition: 'body',
			placement: 'bottom',
			$parent: button,
			backdrop: true
		});

		$.ajax(
			{
				url: '/getInternalShare/' + button.attr('data-tag-id')
			}
		).done(function(resp) {

			console.log(resp);
			if(resp.code == 0) {

				var tag = resp.data;

				$('#shareModal').modalPopover('show');


				// $('.pasttag_share_button').css({color: 'grey'});
				// button.css({color: 'white'});
                //

				that.selectedRow = button.closest('tr');

                var who = that.selectedRow.find('.who_cell_span');
                //


				$('<span class="expandedShare"><i class="mdi mdi-share"></i></span>' +
					'<span class="expandedWho">'+who.html()+'</span>').appendTo(document.body);

				that._positionShareOverlay();

				$('.modal-backdrop').on('click', function() {
					$('.expandedWho').remove();
					$('.expandedShare').remove();
					$('.who_cell_span').css({'visibility':'visible'});
				});

				who.css({'visibility':'hidden'});
				$('.share_button').css({'visibility' : 'hidden'});
                //
				// $('body').append('<span class="who_overlay">' + who.html() +'</span>');
				// $('.who_overlay').css({
				// 	color: 'white',
				// 	'font-weight': 'bold',
				// 	'font-size': '20px',
				// 	'position' : 'absolute',
				// 	'top' : who.offset().top - 5,
				// 	'left' : who.offset().left - 5,
				// 	'z-index': 9999
				// });
                //
				// who.hide();
				$('#share_title').html(tag.who ? ('(' + tag.who + ')') : '');
				var title;

				switch (parseInt(tag.content_type_id)) {
					case parseInt(getContentTypeIdOfMusic()) :
						title = 'Check out this song: ' + tag.what + ' by ' + tag.who;
						break;
					case parseInt(getContentTypeIdOfAd()) :
					case parseInt(getContentTypeIdOfPromotion()):
						title = 'Check out this deal: ' + tag.what + ' from ' + tag.who;
						break;
					case parseInt(getContentTypeIdOfNews()):
						title = 'Check out this news: ' + tag.what + ' from ' + tag.who;
						break;
					case parseInt(getContentTypeIdOfTalk()):
						title = 'Check out this segment: ' + tag.what + ' with ' + tag.who;
						break;
					default:
						title = tag.what + ' ' + tag.who;
						break;
				}


				// $('.addthis_sharing_toolbox')
				// 	.attr('data-url', 'http://airshrd.com/' + resp.hash)
				// 	.attr('data-title', title);


				// window.addthis_share = {
				// 	title: title,
				// 	url: resp.url
				// }

				var audioDownloadLink = '';

				if(parseInt(tag.content_type_id) != parseInt(getContentTypeIdOfMusic())) {

					audioDownloadLink = resp.audioUrl;

				} else {

					$('.download_audio').hide();

				}

				$('.download_audio').attr('href', audioDownloadLink);

				var share = $.extend(true, {}, window.addthis_share);
				var config = $.extend(true, {}, window.addthis_config);

				share.title = title;
				share.url = resp.url;

				console.log(tag);

				var audioDownloadInfo = '';

				$('.mail_button').on('click', function() {

					if(audioDownloadLink) {
						audioDownloadInfo = ' %0A%0AClick here to download the audio: ' + audioDownloadLink;
					}

					$(this).attr('href', 'mailto:?subject=' + title + '&body=Click here to view the moment: ' + resp.url + audioDownloadInfo)
				});

				console.log(share);

				addthis.toolbox(".page_sharing_toolbox", config, share);

				$('.at-copy-link-share-page-url').removeAttr('data-reactid').val(resp.url);
				$('.at-expanded-menu-page-url').html(resp.url);
				// addthis.update('share', 'description', "Hello, I am a description");



			}
		});

	});

	$('#shareModal').on('hidden.bs.modal', function() {
		// $('.who_overlay').remove();
		// $('.who_cell_span').css({color: 'black', 'font-weight' : 'normal', 'font-size' : '14px;'});
        //
		// $('.pasttag_share_button').css({color: 'grey'});

		// $('.expandedRow').remove();
	})

	$('.close').on('click', function() {
		$('.expandedWho').remove();
		$('.expandedShare').remove();

		$('.who_cell_span').css({'visibility':'visible'});
	})
}

OnAirForm.prototype._populateTags = function(prevTags, pastTags, currentTag) {
	
	if (this.initialLoading) {
		for (var index in pastTags) {
			this._addPastTag(TagModel.CreateFromJSON(pastTags[index], 'past'), false);
		}
		this.pastTagsTable.rows.add(this.pastTags).draw();
	} else {
		for (var index in pastTags) {
			var newTag = TagModel.CreateFromJSON(pastTags[index], 'current');
			this._addNewCurrentTag(newTag);
		}
	}
	
	this._scrollPastTagsToBottom();
	this._setCurrentTag(TagModel.CreateFromJSON(currentTag, 'current'));
	
	if (prevTags && prevTags.length > 0) {
		for (var index in prevTags) {
			this.prevTags.push(TagModel.CreateFromJSON(prevTags[index], 'prev'));
		}
		this.prevTagsTableObj.rows.add(this.prevTags).draw();
	}
	
	this._updateFetchTimestamp();
	this._prunePrevTags();

	this.setupShare();

	this.setupAutoCompleteForTags(false);

	this.initialLoading = false;

}

OnAirForm.prototype._filterTags = function() {

	var pastTagsFiltered = [];

	this.pastTagsTable.clear().draw();

	var query = $('#search_tags_text').val().toLowerCase();

	var filterAirshrd = $('#filter_airshrd').prop('checked');

	var totalHeight = 260 + 60 + parseInt($('#preview-airtag-container').height(), 10);

	if(query || filterAirshrd) {

		$('#clear_search').css({visibility:'visible'});

		for (var i = 0; i < this.pastTags.length; i++) {
			// console.log(this.pastTags[i]);
			var who = this.pastTags[i].who.toLowerCase();
			var what = this.pastTags[i].what.toLowerCase();

			if (who.indexOf(query) >= 0 || what.indexOf(query) >= 0) {
				// this._addPastTag(TagModel.CreateFromJSON(tags[i], 'past'), false);
				if(!filterAirshrd || this.pastTags[i].clicks > 0) {
					pastTagsFiltered.push(this.pastTags[i]);
				}
			}
		}

		// this.pastTagsTable.rows().remove();
		//
		// for (var index in tags) {
		// }

		$('.current-airtag-container').hide();
		$('.preview-airtag-container').hide();
		$('.past-airtag-container').find('.dataTables_scrollBody').css({height:totalHeight+'px'});

		this.pastTagsTable.rows.add(pastTagsFiltered).draw();

		this.filterMode = true;

	} else {
		this.pastTagsTable.rows.add(this.pastTags).draw();

		$('.past-airtag-container').find('.dataTables_scrollBody').css({height:'260px'});
		$('.current-airtag-container').show();
		$('.preview-airtag-container').show();


		$('#filter_airshrd_container').css({visibility:'hidden'});
		$('#clear_search').css({visibility:'hidden'});

		this.filterMode = false;

		this._scrollPastTagsToBottom();
	}

	this.setupShare();

	// else {
	// 	for (var index in pastTags) {
	// 		var newTag = TagModel.CreateFromJSON(pastTags[index], 'current');
	// 		this._addNewCurrentTag(newTag);
	// 	}
	// }
    //
	// this._scrollPastTagsToBottom();
	// this._setCurrentTag(TagModel.CreateFromJSON(currentTag, 'current'));
    //
	// if (prevTags && prevTags.length > 0) {
	// 	for (var index in prevTags) {
	// 		this.prevTags.push(TagModel.CreateFromJSON(prevTags[index], 'prev'));
	// 	}
	// 	this.prevTagsTableObj.rows.add(this.prevTags).draw();
	// }
    //
	// this._updateFetchTimestamp();
	// this._prunePrevTags();
    //
	// this.setupShare();
    //
	// this.initialLoading = false;
}

OnAirForm.prototype._prunePrevTags = function() {
	
	var removeCount = 0;
	var removeIndex = 0;
	
	for (var index in this.prevTags) {
		if (this.prevTags[index].timestamp <= this.fetchTimestamp) {
			
			var tagRow = this.getTableRow(this.prevTags[index].id, 'prev');
			if (tagRow) {
				this.prevTagsTableObj.row(tagRow).remove().draw();
			}
			if (removeCount == 0) removeIndex = index;
			removeCount++;
			
		} else {
			break;
		}
	}
	
	if (removeCount > 0) {
		this.prevTags.splice(removeIndex, removeCount);
	}
	
	this._prunePrevTagsByContent(this.currentTag);
	
}

OnAirForm.prototype._prunePrevTagsByContent = function(currentTag) {
	
	if (currentTag == null) return;
	
	var matchingFound = false;
	var checkIndex = 0;
	
	for (var index in this.prevTags) {
		var previewTag = this.prevTags[index];
		
		if (previewTag.isEqualWithTag(currentTag)) {
			matchingFound = true;
			break;
		}
		
		if (checkIndex >= PREVIEWTAG_PRUNE_RANGE_COUNT || previewTag.timestamp - currentTag.timestamp >= PREVIEWTAG_PRUNE_RANGE_DURATION) {
			break;
		}
		
		checkIndex++;
	}
	
	if (matchingFound) {
		for (var i = 0; i <= checkIndex; i++) {
			var previewTag = this.prevTags[0];
			var tagRow = this.getTableRow(previewTag.id, 'prev');
			if (tagRow) {
				this.prevTagsTableObj.row(tagRow).remove().draw();
			}
			this.prevTags.splice(0, 1);
		}
	}
}

OnAirForm.prototype._updateFetchTimestamp = function() {
	if (this.currentTag && this.currentTag.timestamp) {
		this.fetchTimestamp = this.currentTag.timestamp;
	}
}

OnAirForm.prototype._setCurrentTag = function(tag) {
	this.currentTag = tag;
	this._renderCurrentTag();
	
	var nextFetchDuration = DEFAULT_RELOAD_INTERVAL;
	if (this.currentTag.duration > 0) nextFetchDuration = (this.currentTag.duration + 5) * 1000;
	//this.startRefreshTimer(nextFetchDuration);   // Disable refreshing of on air page for a while - it is causing some unknown issue for updated tags
	
	if (!this.lastSelectTagType || this.lastSelectTagType == 'current') {
		this._setSelectedTag(tag);
	}
	
	if (this.currentTag && this.currentTag.is_manual && this.currentTag.content_type_id == ContentTypeIDOfTalk) {
		this.setupStartStopTalkButtonMode(false);
	} else {
		this.setupStartStopTalkButtonMode(true);
	}
}

OnAirForm.prototype._setSelectedTag = function(tag) {
	
	if (this.selectedTag) {
		var tagRow = this.getTableRow(this.selectedTag.id, this.selectedTag.tagType);
		if (tagRow) {
			tagRow.removeClass('tagSelected');
		}
		this.selectedTag.tagSelected = false;
	}
	
	this.selectedTag = tag;
	this.selectedTag.tagSelected = true;
	
	tagRow = this.getTableRow(this.selectedTag.id, this.selectedTag.tagType);
	if (tagRow){
		tagRow.addClass('tagSelected');
	}
	
	this.lastSelectTagType = this.selectedTag.tagType;
	
	if (this.isOnAirMode()) this._renderPreviewScreen();
}

OnAirForm.prototype._renderPreviewScreen = function() {
	if (!this.selectedTag) return;
	
	var tagType = '';
	if (this.selectedTag.tagType == 'current' || this.selectedTag.tagType == 'past') {
		tagType = 'live';
	} else if (this.selectedTag.tagType == 'prev') {
		tagType = 'preview';
	}
	
	this.previewForm.renderPreviewInfo(tagType, this.selectedTag.id, function (data) {
		if(!data) {
			return;
		}

		//if(contentFormObj == null && contentImageEditor == null) {
		//	contentFormObj = new ContentForm();
		//	contentImageEditor = new ContentImageEditor('image-editor-cropper-div', 'image-editor-cropper-img', 'content_btn_img_confirm', 'content_btn_img_cancel', 'image_editor_preview', 'image_editor_preview_banner');
		//} else {
		//	contentFormObj.onAfterFormCreation();
		//}

		var formActionElement = $('.mobilepreview_action_buttons_container .preview-form-button').first();//$('.bottom-nav-shape .preview-form-button').first();

		if (!formActionElement || formActionElement.length <= 0) return;

		formActionElement.off('click');

		if (!data) {
			formActionElement.hide();
		} else {

			data.hasConnectData = parseAsInt(data.hasConnectData);
			data.adkey = parseAsString(data.adkey);
			data.content_type_id = parseAsInt(data.content_type_id);

			if ((data.content_type_id == ContentTypeIDOfAd || data.content_type_id == ContentTypeIDOfPromotion) && !data.hasConnectData && data.adkey) {
				formActionElement.html('<i class="mdi mdi-plus"></i>');
				formActionElement.show();

				formActionElement.on('click', function(){

					bootbox.confirm("Are you sure you want to create new content for this tag?", function(result){

						if (result) {

							showLoading();

							$.ajax (
								{
									url: tagType == 'preview' ? "/content/createAdFromPreviewTag" : "/content/createAdFromTag",
									type: "post",
									data: {
										"tag_id" : data.id
									},
									dataType: "json",
									success: function( resp ) {
										if (resp.code == 0 && resp.data) {
											document.location = '/content?initialFormMode=edit&initialContentID=' + parseAsInt(resp.data.contentID) + '&prevPage=onair';
										} else {
											$('.saveProgress').show().html('Creation Failed. ' + resp.msg).css('color', 'red');
											setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
											hideLoading();
										}
									}
								}
							).fail ( function () {
								$('.saveProgress').show().html('Creation Failed. Network error.').css('color', 'red');
								setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
								hideLoading();
							}).always( function () {


							});

						}
					});
				});

			} else if ((data.content_type_id == ContentTypeIDOfAd || data.content_type_id == ContentTypeIDOfPromotion) && data.hasConnectData) {
				formActionElement.html('<i class="mdi mdi-credit-card"></i>');
				formActionElement.show();

				formActionElement.on('click', function(){

					document.location = '/content?initialFormMode=edit&initialContentID=' + parseAsInt(data.finalContentID) + '&prevPage=onair';
				});

			} else {
				formActionElement.hide();
			}
		}
	});

	/*if (this.selectedTag.is_competition == 1) {
		$('#btn_competition_result').show();
	} else {
		$('#btn_competition_result').hide();
	}*/

	$('#competitionresult_sidebar').addClass('hidden');
}


OnAirForm.prototype._updateTagEventCount = function(tag) {
	
	var foundItem = false;
	for (var index in this.pastTags) {
		if (this.pastTags[index].id == parseAsInt(tag.id)) {
			this.pastTags[index].clicks = parseAsInt(tag.count);
			foundItem = true;
			break;
		}
	}
	
	if (!foundItem) {
		if (this.currentTag && this.currentTag.id == parseAsInt(tag.id)) {
			this.currentTag.clicks = parseAsInt(tag.count);
		}
	}
	
	var progressContainer = $('div#progress-wrapper-' + tag.id).first();
	if (progressContainer) {
		progressContainer.find('.progress-bar').attr('data-transitiongoal', parseAsInt(tag.count)).progressbar();
		progressContainer.find('.progress-value').html(tag.count);
	}
	
}

OnAirForm.prototype._addNewCurrentTag = function(tag) {
	if (this.currentTag != null) {
		this.currentTag.tagType = 'past';
		this.currentTag.generateHTMLContents();
		this._addPastTag(this.currentTag, true);
		// this._scrollPastTagsToBottom();
	}
	if(this.isCurrentTagSelected) {
		this.selectedRow = $('#past-tags-list-table tr:last');
		this.isCurrentTagSelected = false;
	}
	this._setCurrentTag(tag);
	this._updateFetchTimestamp();

	this.setupShare();

	this.setupAutoCompleteForTags(true);

	this._positionShareOverlay();
	this._prunePrevTags();
}

OnAirForm.prototype._positionShareOverlay = function() {

	if(this.selectedRow) {
		var button = this.selectedRow.find('.share_button');
		var who = this.selectedRow.find('.who_cell_span');

		who.css({'visibility':'hidden'});

		// $('.expandedShare').css({
		// 	'top': parseInt(button.offset().top, 10) - 6 + 'px',
		// 	'left': '18px'
		// });
		// $('.expandedWho').css({
		// 	'top': parseInt(who.offset().top, 10) - 2 + 'px',
		// 	'left': parseInt(who.offset().left, 10) + 'px',
		// });

		$('.expandedShare').css({
			'z-index':9999,
			'position':'absolute',
			'font-size':'30px',
			'top' : parseInt(button.offset().top, 10) - 6 + 'px',
			'left' :  parseInt(button.offset().left, 10) + 'px',
			'font-weight' : 'bold',
			'color':'white'
		})
		$('.expandedWho').css({
			'z-index':9999,
			'position':'absolute',
			'font-size':'20px',
			'top' : parseInt(who.offset().top, 10) - 7 + 'px',
			'left' :  parseInt(who.offset().left, 10) + 'px',
			'font-weight' : 'bold',
			'color' : 'white'
		});

		$('#shareModal').css({ left:'40px', top: parseInt(button.offset().top, 10) - 40 + 'px'});

	}
}

OnAirForm.prototype._renderCurrentTag = function() {
	var rows = this.currentTagTable
    			.rows()
    			.remove()
    			.draw();
	
	this.currentTagTable.row.add(this.currentTag).draw();
} 

OnAirForm.prototype._addPastTag = function(tag, addToTable) {

	var that = this;

	this.pastTags.push(tag);
	
	if (addToTable && !this.filterMode) {
		this.pastTagsTable.row.add(tag).draw();

		this.setupShare();
	}

	// $('#shareModal').modalPopover({
	// 	modalPosition: 'body',
	// 	placement: 'right',
	// 	$parent: that.selectedTagForShare,
	// 	backdrop: true
	// });

	// var top = $('#shareModal').css('top');
	// $('#shareModal').css({top: (top - 32) + 'px'});

	// $('.expandedWho').css({'top':parseInt($('.expandedWho').css('top')) - 30 + 'px' });
	// $('.expandedShare').css({'top':parseInt($('.expandedShare').css('top')) - 30 + 'px' });

}

OnAirForm.prototype.setupAutoCompleteForTags = function(update) {

	var tagSearches = [];

	for(var index in this.pastTags) {
		if(tagSearches.indexOf(this.pastTags[index].what) < 0) {
			tagSearches.push(this.pastTags[index].what);
		}
		if(tagSearches.indexOf(this.pastTags[index].who) < 0) {
			tagSearches.push(this.pastTags[index].who);
		}
	}

	if (update) {
		$( "#search_tags_text" ).typeahead().data('typeahead').source = tagSearches;
	} else {
		var newRender = function(items) {
			var that = this

			items = $(items).map(function (i, item) {
				i = $(that.options.item).attr('data-value', item)
				i.find('a').html(that.highlighter(item))
				return i[0]
			})

			this.$menu.html(items)
			return this
		};

		$.fn.typeahead.Constructor.prototype.render = newRender;

		$.fn.typeahead.Constructor.prototype.select = function() {
			var val = this.$menu.find('.active').attr('data-value');
			if (val) {
				this.$element
					.val(this.updater(val))
					.change();
			}

			this.$element.change();

			return this.hide()
		};

		$( "#search_tags_text" ).typeahead({
			source: tagSearches
		});
	}
}


OnAirForm.prototype._scrollPastTagsToBottom = function() {
	
	if ($('#past-tags-list-table').height() < 260) {
	
		$('#past-tags-list-table').css({"position": "absolute", "bottom" : "0"});
		
	} else {
	
		$('#past-tags-list-table').css({"position": "inherit", "bottom" : "auto"});
		$("#past-airtag-container .dataTables_scrollBody").scrollTop(999999);
		
	}
	
	
}

OnAirForm.prototype.getTableRow = function(id, type) {
	var idSpanElement = $('span[data-tagtype="' + type + '"][data-tagid="' + id + '"]').first();
	if (idSpanElement == null || idSpanElement == undefined) return null;
	return idSpanElement.parent().parent();
}

OnAirForm.prototype.getTagFromRow = function(row) {
	var idSpanElement = row.find('span.tagIdSpan').first();
	
	if (!idSpanElement || idSpanElement.length == 0) {
		idSpanElement = row.find('span.check-box.check-mark').first();
	}
	
	if (!idSpanElement) {
		return null;
	}
	
	var tagType = idSpanElement.data('tagtype');
	var tagId = idSpanElement.data('tagid');
	
	if (!tagType || !tagId) return null;
	
	
	if (tagType == 'past') {
		
		for (var index in this.pastTags) {
			if (this.pastTags[index].id == tagId) {
				return this.pastTags[index];
			}
		}
		
	} else if (tagType == 'current') {
		
		if (this.currentTag && this.currentTag.id == tagId) return this.currentTag;
		
	} else if (tagType == 'prev') {
		
		for (var index in this.prevTags) {
			if (this.prevTags[index].id == tagId) {
				return this.prevTags[index];
			}
		}
	}
	
	return null;
}


OnAirForm.prototype.initTable = function() {
	
	var that = this;
	
	if (this.isOnAirMode()) {
		this.pastTagsTable = $('#past-tags-list-table').DataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: 260,
					ordering: false,
					columns :[
								{"data" : "share_html", "className" : 'dt-body-right', "width": "5%"},
					            {"data" : "timestamp_html", "className" : 'dt-body-right', "width": "15%"},
					            {"data" : "state", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who_html", "width": "40%"},
					            {"data" : "competition_html", "width": "50px"},
					            {"data" : "clicks_html", "width": "40%", "className" : "progressbar-cell"}
					],
					autoWidth: false
				}
		);
	} else if (this.isTalkAssignMode()) {
		this.pastTagsTable = $('#past-tags-list-table').DataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: 260,
					ordering: false,
					columns :[
								{"data" : "share_html", "className" : 'dt-body-right', "width": "5%"},
					          	{"data" : "selector_html", "className" : 'dt-body-center', "width": "30px"},
					            {"data" : "timestamp_html", "className" : 'dt-body-right', "width": "120px"},
					            {"data" : "content_type_html", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who", "className" : 'dt-body-left', "width": "300px"}
					],
					autoWidth: false
				}
		);
	}
	
	$('#past-tags-list-table').on('draw.dt', function() {
		that.onDrawPastTagListTable();
	});
	
	if (this.isOnAirMode()) {
		this.currentTagTable = $('#current-airtag-table').DataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: 60,
					ordering: false,
					columns :[
								{"data" : "share_html", "className" : 'dt-body-right', "width": "5%"},
					            {"data" : "timestamp_html", "className" : 'dt-body-center', "width": "15%"},
					            {"data" : "state", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who_html", "width": "40%"},
					            {"data" : "competition_html", "width": "50px"},
					            {"data" : "clicks_html", "width": "40%", "className" : "progressbar-cell"}
					],
					autoWidth: false
				}
		);
	} else if (this.isTalkAssignMode()) {
		this.currentTagTable = $('#current-airtag-table').DataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: 60,
					ordering: false,
					columns :[
								{"data" : "share_html", "className" : 'dt-body-right', "width": "5%"},
					          	{"data" : "selector_html", "className" : 'dt-body-center', "width": "30px"},
					            {"data" : "timestamp_html", "className" : 'dt-body-right', "width": "120px"},
					            {"data" : "content_type_html", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who", "className" : 'dt-body-left', "width": "300px"}
					],
					autoWidth: false
				}
		);
	}
	
	$('#current-airtag-table').on('draw.dt', function() {
		that.onDrawCurrentTagTable();
	});
	
	if (this.isOnAirMode()) {
		this.prevTagsTable = $('#preview-tags-list-table').dataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: $('#preview-airtag-container').height(),
					ordering: false,
					columns :[
					            {"data" : "timestamp_html", "className" : 'dt-body-right', "width": "20%"},
					            {"data" : "state", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who_html", "width": "40%"},
					            {"data" : "competition_html", "width": "50px"},
					            {"data" : "what_html", "width": "40%"}
					],
					autoWidth: false
				}
		);
	} else if (this.isTalkAssignMode()) { 
		this.prevTagsTable = $('#preview-tags-list-table').dataTable(
				{
					paging: false,
					searching: false,
					info: false,
					sScrollY: $('#preview-airtag-container').height(),
					ordering: false,
					columns :[
					          	{"data" : "selector_html", "className" : 'dt-body-center', "width": "30px"},
					            {"data" : "timestamp_html", "className" : 'dt-body-right', "width": "120px"},
					            {"data" : "content_type_html", "className" : 'dt-body-center', "width": "50px"},
					            {"data" : "who", "className" : 'dt-body-left', "width": "300px"}
					],
					autoWidth: false
				}
		);
	}
	
	this.prevTagsTableObj = $('#preview-tags-list-table').DataTable();

	this.prevTagsTableObj.on('draw.dt', function() {
		that.onDrawPreviewTagListTable();
	});
	
	
	$(window).resize(function () {
		adjustPreviewTagsTableHeight();
	 });  
	 
	 
	 var adjustPreviewTagsTableHeight = function() {
		 if (that.prevTagsTable == null) return;
		 
		 var oSettings = that.prevTagsTable.fnSettings();
		 var proposedHeight = $('#preview-airtag-container').height();
		 oSettings.oScroll.sY = proposedHeight;
		 that.prevTagsTable.fnDraw();
		 
		 $('#preview-airtag-container div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
	 };
	 
	 adjustPreviewTagsTableHeight();
}

OnAirForm.prototype.onDrawPastTagListTable = function() {
	
	var that = this;
	
	$('#past-tags-list-table_wrapper .progress-bar').progressbar();
	
	if (this.isOnAirMode()) {
		$('#past-tags-list-table_wrapper tbody tr').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this));
			
			if (tag != null) {
				that._setSelectedTag(tag);
			}
		});
	} else if (this.isTalkAssignMode()) {
		$('#past-tags-list-table_wrapper tbody tr span.check-box.check-mark').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this).parent().parent());
			if (tag == null) return;
			tag.tagSelectedForAssoc = !tag.tagSelectedForAssoc;
			if (tag.tagSelectedForAssoc) {
				that.addContentAssociation('past', tag.id, tag.timestamp);
			} else {
				that.removeContentAssociation('past', tag.id);
			}
		});
	}
	
}

OnAirForm.prototype.onDrawCurrentTagTable = function() {
	
	var that = this;
	
	$('#current-airtag-table_wrapper .progress-bar').progressbar();
	
	if (this.isOnAirMode()) {
		$('#current-airtag-table_wrapper tbody tr').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this));
			
			if (tag != null) {
				that._setSelectedTag(tag);
			}
		});
	} else if (this.isTalkAssignMode()) {
		$('#current-airtag-table_wrapper tbody tr span.check-box.check-mark').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this).parent().parent());
			if (tag == null) return;
			tag.tagSelectedForAssoc = !tag.tagSelectedForAssoc;
			if (tag.tagSelectedForAssoc) {
				that.addContentAssociation('current', tag.id, tag.timestamp);
			} else {
				that.removeContentAssociation('current', tag.id);
			}
		});
	}
}


OnAirForm.prototype.onDrawPreviewTagListTable = function() {
	
	var that = this;
	
	if (this.isTalkAssignMode()) {
	
		$('#preview-tags-list-table_wrapper tbody tr span.check-box.check-mark').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this).parent().parent());
			if (tag == null) return;
			tag.tagSelectedForAssoc = !tag.tagSelectedForAssoc;
			if (tag.tagSelectedForAssoc) {
				that.addContentAssociation('prev', tag.id, tag.timestamp);
			} else {
				that.removeContentAssociation('prev', tag.id);
			}
							
		});
		
	} else if (this.isOnAirMode()) {
		
		$('#preview-tags-list-table_wrapper tbody tr').off('click').on('click', function(e){
			
			var tag = that.getTagFromRow($(this));
			
			if (tag != null) {
				that._setSelectedTag(tag);
			}
		});
	}
	
}

OnAirForm.prototype.getContentAssociationCheckBox = function(type, id) {
	var span = $('span.check-box.check-mark[data-tagid="' + id + '"][data-tagtype="' + type + '"]').first();
	if (!span || span.length == 0) return null;
	return span;
}

OnAirForm.prototype.addContentAssociation = function(type, id, timestamp) {
	var newAssoc = new ConnectContent2Preview();
	newAssoc.assoc_type = type;
	newAssoc.assoc_id = id;
	newAssoc.assoc_timestamp = timestamp;
	this.contentAssociation.push(newAssoc);
	
	var spanElement = this.getContentAssociationCheckBox(type, id);
	checkCircleBox(spanElement, true);
}

OnAirForm.prototype.removeContentAssociation = function(type, id) {
	for (var index in this.contentAssociation) {
		var item = this.contentAssociation[index];
		if (item.assoc_type == type && item.assoc_id == id) {
			this.contentAssociation.splice(index, 1);
			break;
		}
	}
	var spanElement = this.getContentAssociationCheckBox(type, id);
	checkCircleBox(spanElement, false);
}


var TagModel = function(id, type) {
	this.id = id;
	
	this.timestamp = 0;
	this.state = "";
	this.who = "";
	this.what = "";
	this.clicks = 0;
	this.content_type_id = 0;
	this.content_type_color = '';
	
	this.tagType = type;
	this.what_html = '';
	this.who_html = '';
	this.clicks_html = '';
	
	this.duration = 0;
	this.connect_content_id = 0;
	this.coverart_id = 0;

	this.hasConnectData = 0;
	
	this.tagSelected = false;
	
	this.tagSelectedForAssoc = false;
	
	this.is_competition = 0;
	this.is_vote = 0;
	this.vote_question = '';
	
	this.is_manual = 0;
}

TagModel.prototype.loadDataFromJSON = function(jsonData) {
	
	this.id = parseAsInt(jsonData.id);
	this.timestamp = parseAsInt(jsonData.tag_timestamp);
	this.who = parseAsString(jsonData.who);
	this.what = parseAsString(jsonData.what);
	this.content_type_id = parseAsInt(jsonData.content_type_id);
	this.content_type_color = parseAsString(jsonData.content_type_color);
	this.clicks = parseAsInt(jsonData.event_count);
	
	this.duration = parseAsInt(jsonData.tag_duration);
	this.connect_content_id = parseAsInt(jsonData.connect_content_id);
	this.coverart_id = parseAsInt(jsonData.coverart_id);
	
	this.hasConnectData = parseAsInt(jsonData.hasConnectData);
	this.is_competition = parseAsInt(jsonData.is_competition);
	this.is_vote = parseAsInt(jsonData.is_vote);
	this.vote_question = parseAsString(jsonData.vote_question);
	
	this.is_manual = parseAsInt(jsonData.is_manual);
	
	//this.is_competition = 1;
}

TagModel.prototype.isEqualWithTag = function(compareTag) {
	if (this.content_type_id != compareTag.content_type_id) return false;
	if (this.who.trim().toLowerCase() != compareTag.who.trim().toLowerCase()) return false;
	if (this.what.trim().toLowerCase() != compareTag.what.trim().toLowerCase()) return false;
	return true;
}

TagModel.prototype.generateHTMLContents = function() {

	if (this.tagType == 'current') {
		this.share_html='<span class="share_button current_share" style="visibility:hidden; font-size:30px; color:grey;" data-tag-id="'+this.id+'"><i class="mdi mdi-share"></i></span>';
		this.timestamp_html = 'Now playing';
		this.who_html = '<span id="pasttag_who_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="who_cell_span ' + (this.who ? '' : 'what_missing_who') + '">' + (this.who ? this.who : this.what) + '</span>';
	} else if (this.tagType == 'past') {
		this.share_html='<span class="share_button" style="visibility:hidden; font-size:30px; color:grey;" data-tag-id="'+this.id+'"><i class="mdi mdi-share"></i></span>';
		this.timestamp_html = '' + moment(dateFromTimestamp(this.timestamp)).tz(GLOBAL.STATION_TIMEZONE).format('HH:mm:ss');
		this.who_html = '<span id="pasttag_who_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="who_cell_span ' + (this.who ? '' : 'what_missing_who') + '">' + (this.who ? this.who : this.what) + '</span>';
	} else if (this.tagType == 'prev') {
		this.timestamp_html = moment(dateFromTimestamp(this.timestamp)).tz(GLOBAL.STATION_TIMEZONE).format('HH:mm:ss');
		this.what_html = '<span class="check-mark" style="background-color: ' + this.content_type_color + '"></span>&nbsp;&nbsp;&nbsp;&nbsp;' + '<span id="previewtag_what_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="what_cell_span">' + this.what + '</span>';
		this.who_html = '<span id="previewtag_who_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="who_cell_span">' + this.who + '</span>';
	}
			
	if (this.tagType == 'current' || this.tagType == 'past') {
		this.clicks_html = '<div class="row" id="progress-wrapper-' + this.id + '"><div class="col-sm-20"><div class="progress"><div data-tag-id="' + this.id + '" class="tag-clicks-progress-bar progress-bar color-content-' + this.content_type_id +  (this.is_vote == 1 ? ' color-content-vote ' : '') + '" role="progressbar" data-transitiongoal="' + this.clicks + '" aria-valuemin="0" aria-valuemax="100"></div></div></div><div class="col-sm-4"><div class="progress-value">' + this.clicks + '</div></div></div>';
	}
	
	this.state = '<span data-tagid="' + this.id + '" class="tagIdSpan" data-tagtype="' + this.tagType + '"></span>';
	
	if (this.tagType == 'current') {
		if (this.duration > 0) {
			this.state += '<span class="content-type-circle" style="margin-top: 10px; border-color: ' + this.content_type_color + '; background-color: ' + this.content_type_color + ';"><div class="pie-wrapper"><div class="pie spinner" style="animation :rota ' + this.duration + 's linear 1; -webkit-animation: rota ' + this.duration + 's linear 1;"></div><div class="pie filler" style="animation: fill ' + this.duration + 's steps(1,end) 1;-webkit-animation: fill ' + this.duration + 's steps(1,end) 1;"></div><div class="mask" style="background-color: ' + this.content_type_color + '; animation: mask ' + this.duration + 's steps(1,end) 1; -webkit-animation: mask ' + this.duration + 's steps(1,end) 1;"></div></div></span>';
		} else {
			this.state += '<span class="content-type-circle" style="margin-top: 10px; border-color: ' + this.content_type_color + '; background-color: ' + this.content_type_color + ';"></span>';
		}
	} else {
		if (this.content_type_id == ContentTypeIDOfAd || this.content_type_id == ContentTypeIDOfPromotion || this.content_type_id == ContentTypeIDOfMusic) {
			if (this.hasConnectData == 1) {
				if (this.content_type_id != ContentTypeIDOfMusic) {
					this.state += '<span class="content-type-circle" style="border-color: ' + this.content_type_color + ';"></span>';
				}
			} else {
				this.state += '<i class="mdi mdi-information-outline error-red"></i>';
			}
		}
		
	}
	
	if (this.content_type_id == ContentTypeIDOfTalk) { 
		if (this.tagSelectedForAssoc) {
			this.selector_html = '<span class="check-mark big-size enabled-black check-box" data-tagid="' + this.id + '" data-tagtype="' + this.tagType + '" data-check="1"></span>';
		} else {
			this.selector_html = '<span class="check-mark big-size deactive check-box" data-tagid="' + this.id + '" data-tagtype="' + this.tagType + '"></span>';
		}
	} else {
		this.selector_html = '';
	}
	 
	this.content_type_html = '<span class="content-type-circle small" style="margin-top: 8px; border-color: ' + this.content_type_color + '; background-color: ' + this.content_type_color + ';"></span>';
	
	if (this.is_competition == 1) {
		this.competition_html = '<span class="talkbreak_type_cell_span" data-tag-id="' + this.id + '"><svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="#000000" d="M7,2V4H2V11C2,12 3,13 4,13H7.2C7.6,14.9 8.6,16.6 11,16.9V19C8,19.2 8,20.3 8,21.6V22H16V21.7C16,20.4 16,19.3 13,19.1V17C15.5,16.7 16.5,15 16.8,13.1H20C21,13.1 22,12.1 22,11.1V4H17V2H7M9,4H15V12A3,3 0 0,1 12,15C10,15 9,13.66 9,12V4M4,6H7V8L7,11H4V6M17,6H20V11H17V6Z" /></svg></span>';
	} else if (this.is_vote == 1) { 
		this.competition_html = '<span class="talkbreak_type_cell_span" data-tag-id="' + this.id + '"><svg width="24px" height="24px" style="float:left;" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Client-Info/Images" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round"><g id="vote_icon" transform="translate(-609.000000, -116.000000)" stroke-width="2" stroke="#9B9B9B"><g id="Page-1" transform="translate(610.000000, 117.000000)"><g id="Group-4"><path d="M20.6042623,12.160682 L21.8224918,12.160682 C22.9392787,12.160682 23.8527213,13.0663213 23.8527213,14.1734689 L23.8527213,14.1734689 C23.8527213,15.2806164 22.9392787,16.1867148 21.8224918,16.1867148 L19.7922623,16.1867148" id="Stroke-2"></path><path d="M18.5738492,16.1866689 L20.807423,16.1866689 C21.8122098,16.1866689 22.6347672,17.001882 22.6347672,17.9984066 L22.6347672,17.9984066 C22.6347672,18.9949311 21.8122098,19.8101443 20.807423,19.8101443 L19.5891934,19.8101443" id="Stroke-4"></path><path d="M18.5738492,12.160682 L20.6040787,12.160682 C21.7208656,12.160682 22.6347672,11.2550426 22.6347672,10.1478951 L22.6347672,10.1478951 C22.6347672,9.04074754 21.7208656,8.13464918 20.6040787,8.13464918 L14.9122754,8.13464918 L14.9122754,3.2323541 C14.9122754,1.50002623 13.4824393,0.0825836066 11.7349639,0.0825836066 L10.8513574,0.0825836066 L10.8513574,4.08520656 C10.8513574,7.05458361 8.40020984,9.48461639 5.40512787,9.48461639 L4.5743082,9.48461639 L4.5743082,19.1941902 C4.5743082,21.3134689 6.30663607,23.0311082 8.44381639,23.0315672 L19.8379803,23.0334033 C20.7059803,23.0338623 21.4165377,22.3297311 21.4165377,21.4690754 L21.4165377,21.428223 C21.4165377,20.534059 20.6844066,19.8092721 19.7907016,19.8097311 C19.1545049,19.8101902 18.5738492,19.8101902 18.5738492,19.8101902" id="Stroke-6"></path><path d="M0.968642623,22.9203016 L4.57421639,22.9203016 L4.57421639,8.53518689 L0.968642623,8.53518689 C0.461429508,8.53518689 0.0506098361,8.94600656 0.0506098361,9.45321967 L0.0506098361,22.0022689 C0.0506098361,22.509482 0.461429508,22.9203016 0.968642623,22.9203016 L0.968642623,22.9203016 Z" id="Stroke-8"></path></g></g></g></g></svg></span>';
		this.who_html = '<span id="pasttag_who_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="who_cell_span">' + this.vote_question + '</span>';
	} else {
		this.competition_html = '<span class="talkbreak_type_cell_span" data-tag-id="' + this.id + '"></span>';
	}
	
}


TagModel.CreateFromJSON = function(jsonData, type) {
	var obj = new TagModel(0, type);
	if (jsonData != null && jsonData != undefined) obj.loadDataFromJSON(jsonData);
	obj.generateHTMLContents();
	return obj;
}


var ConnectContent2Preview = function() {

	this.assoc_type = '';
	this.assoc_id = 0;
	this.assoc_timestamp = 0;
}