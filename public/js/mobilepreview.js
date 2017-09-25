$(document).ready(function(){
	$('.preview_audio_player').mediaelementplayer({
		audioWidth: 348,
	    audioHeight: 30
	});
});


var REALIMAGE_MIN_WIDTH			= 800;
var REALIMAGE_MIN_HEIGHT		= 600;

var BANNER_PADDING				= 40;

var PREVIEW_IMAGE_WIDTH 		= 348;
var PREVIEW_IMAGE_HEIGHT		= 260;

var MobilePreviewForm = function(previewSliderContainerID) {
	this.previewSliderContainerID = previewSliderContainerID;

	this.voteExpireCountTimer = null;

	this.previewData = {};

}

var mapScriptLoaded = false;

var contentData = {};
var tagId;

MobilePreviewForm.prototype.renderPreviewInfo = function(type, id, onComplete, page) {
	var that = this;

	this.currentFormDataType = type;
	this.currentFormDataId = id;
	this.currentFormOnComplete = onComplete;
	this.currentFormPage = page;

	this.currentWhoAutoCompleteID = 0;
	this.currentWhatAutoCompleteID = 0;

	var url;
	if(type == "content") {
		url = "/content/show/" + id;
	}
	else if(type == 'coverart' || type == 'google') {
		url = "/content/getSong/" + id + '/' + type;
	}
	else {
		url = "/tag/" + id + "?type=" + type;
	}

	if(type != 'google') {
		$('.itunes-buttons').css('border-bottom', 'solid');
		$('.google-buttons').css('border-bottom', 'none');
	}

	this.showPreviewLoading();
	$.ajax(
		{
			//When type == content it means we want to retrieve by content id rather than tag id.
			url: url,
			type: "get",
			dataType: "json"
		}
	).done(function (resp) {
		if (resp.code === 0 && resp.data != undefined) {
			console.log(resp.data);

			//This is usually when type is a tag or tag preview
			if(type != "content") {
				contentData = resp.data.connectContent;
				contentData.id = resp.data.connectContentId;
				contentData.is_ready = resp.data.is_ready;
				contentData.text_enabled = parseAsInt(resp.data.text_enabled);
				contentData.image_enabled = parseAsInt(resp.data.image_enabled);
				contentData.action_enabled = parseAsInt(resp.data.action_enabled);
				contentData.audio_enabled = parseAsInt(resp.data.audio_enabled);
				contentData.adkey = resp.data.adkey;
				contentData.is_client_found = resp.data.is_client_found;
				contentData.coverart_id = parseAsInt(resp.data.coverart_id);
				contentData.what = resp.data.what;
				contentData.who = resp.data.who;
				contentData.original_what = resp.data.original_what;
				contentData.original_who = resp.data.original_who;
				contentData.itunes_ready = resp.data.itunes_ready;
				contentData.google_ready = resp.data.google_ready;
				contentData.itunes_available = resp.data.itunes_available;
				contentData.google_available = resp.data.google_available;

				that.previewData = resp.data;

				tagId = resp.data.id;

				//Daily log
				updateDailyLogStats();

				that._updateWhoValueOfTable(contentData.id, resp.data.who);
				that._updateWhatValueOfTable(contentData.id, resp.data.what);
			}

			//When type == content it means we want to retrieve by content id rather than tag id.
			else if (type == "content") {
				contentData = resp.data;
				contentData.is_ready = resp.data.is_ready;
				contentData.map = {lat:Number(resp.data.map_address1_lat), lng: Number(resp.data.map_address1_lng), address: resp.data.map_address1};
			}

			that.renderPreviewInformation(resp.data, type, page);

			//Map initialisation - some pages have more than one mobile preview, so we must make sure we only load the google maps api once per page
			if (typeof initMapEdit == 'function' && contentData) {
				initMapEdit();
			}
			if (!mapScriptLoaded && contentData.map && contentData.map.lat && contentData.map.lng) {
				if (typeof getMapScript == 'function') {
					getMapScript();
				}
				$('#map').show();
				mapScriptLoaded = true;
			} else if (mapScriptLoaded && contentData.map && contentData.map.lat && contentData.map.lng) {
				if (typeof initMap == 'function') {
					initMap();
				}
				$('#map').show();
			} else {
				$('#map').hide();
			}

			//Perform callback
			if (onComplete) {
				onComplete(resp.data);
			}
		} else if(resp.code == 0 && resp.song != undefined) {
			if(type == 'coverart' || type == 'google') {
				that.renderCoverartPreviewInformation(resp.song, type);

				//Perform callback
				if (onComplete) {
					onComplete(resp.data);
				}
			}
		}

		else {
			if (onComplete) {
				onComplete(null);
			}
		}
	}).fail(function () {

		if (onComplete) {
			onComplete(null);
		}

	}).always(function () {

			that.hidePreviewLoading();

	});

}

MobilePreviewForm.prototype.reloadPreviewForm = function () {
	this.renderPreviewInfo(this.currentFormDataType, this.currentFormDataId/*, this.currentFormOnComplete, this.currentFormPage*/);
}

MobilePreviewForm.prototype.showPreviewLoading = function(){
	$('#mobilepreview_loader').removeClass('hide');
}


MobilePreviewForm.prototype.hidePreviewLoading = function(){
	$('#mobilepreview_loader').addClass('hide');
}

MobilePreviewForm.prototype._removeEditableLinks = function() {

	if ($('.mobilepreview_content_container .link-editable').editable) {
		$('.mobilepreview_content_container .link-editable').editable('hide');
	}
}

MobilePreviewForm.prototype._updateWhatValueOfTable = function(content_id, what) {
	if (!content_id) {
		if(this.currentFormDataType == 'preview') {
			$('#previewtag_what_' + this.currentFormDataId + '').html(what);
		} else {
			$('#pasttag_what_' + this.currentFormDataId + '').html(what);
		}
		return;
	}
	$('.what_cell_span[data-tag-content-id="' + content_id + '"]').css('color', what ? '' : 'red').html(what ? what : 'Missing Headline');
}

MobilePreviewForm.prototype._updateWhoValueOfTable = function(content_id, who) {
	if (!content_id) {
		if(this.currentFormDataType == 'preview') {
			$('#previewtag_who_' + this.currentFormDataId + '').html(who);
		} else {
			$('#pasttag_who_' + this.currentFormDataId).html(who ? who : contentData.what);
		}
		return;
	}
	if(this.previewData) {
		if ((this.previewData.content_type_id == ContentTypeIDOfPromotion || this.previewData.content_type_id == ContentTypeIDOfAd) && typeof OnAirFormObj === 'undefined') {
			$('.who_cell_span[data-tag-content-id="' + content_id + '"]').addClass(contentData.is_client_found ? '' : 'missing-client').html(who);
			if(contentData.is_client_found) {
				$('.who_cell_span[data-tag-content-id="' + content_id + '"]').removeClass('missing-client');
			}
		} else {
			$('.who_cell_span[data-tag-content-id="' + content_id + '"]').html(who);
		}
	}
}

MobilePreviewForm.prototype._getTalkBreakAutoCompleteSource = function(field, is_vote, is_competition) {

	if (typeof TalkBreakAutoCompleteList == 'undefined' || TalkBreakAutoCompleteList == null) return null;

	var itemList = this._getTalkBreakAutoCompleteArray(is_vote, is_competition);

	return function findMatches(q, cb) {
	    var matches, substringRegex;

	    matches = [];
	    substrRegex = new RegExp(q, 'i');
	    $.each(itemList, function(i, item) {
	    	if (field == 'who') {
	    		if (substrRegex.test(item.who)) matches.push(item);
	    	} else if (field == 'what') {
	    		if (substrRegex.test(item.what)) matches.push(item);
	    	} else if (field == 'vote_question') {
	    		if (substrRegex.test(item.vote_question)) matches.push(item);
	    	}
	    });

	    cb(matches);

	  };
}


MobilePreviewForm.prototype._getTalkBreakAutoCompleteArray = function(is_vote, is_competition) {

	if (TalkBreakAutoCompleteList == undefined || TalkBreakAutoCompleteList == null) return [];

	var resultArray = [];

	for (var i in TalkBreakAutoCompleteList) {
		var item = TalkBreakAutoCompleteList[i];

		if (is_vote) {
			if (item.is_vote) resultArray.push(item);
		} else if (is_competition) {
			if (item.is_competition) resultArray.push(item);
		} else {
			resultArray.push(item);
		}
	}

	return resultArray;
}


MobilePreviewForm.prototype.loadCompetitionResultContent = function() {

	if (!$('#competitionresult_sidebar').hasClass('hidden')) {
		$('#competitionresult_sidebar').addClass('hidden');
		return;
	}

	this.showPreviewLoading();

	var that = this;

	$.ajax (
		{
			url: "/content/getCompetitionResultContent?tag_id=" + that.currentFormDataId,
			type: "get",
			success: function( resp ) {
				$('#competitionresult_sidebar').html(resp);

				$('#competition_btn_print').off('click').on('click', function(){
					PrintElem('#competition_result_content_wrapper', "Competition Result");
				});

			}
		}
	).fail ( function () {

		//alert("Initial data loading has failed. Please reload this page.");
		$('#competitionresult_sidebar').html('<p>Network error.</p>');

	}).always( function () {

		that.hidePreviewLoading();

		$('#competitionresult_sidebar').removeClass('hidden');

	});
}

MobilePreviewForm.prototype._setupEditableLinksForMusicTag = function(type) {

	var that = this;

	$('#mobilepreview_what_editlink').editable(
		{
			type: 'text',
			url: '/content/updateMusicTag',
			showbuttons: 'bottom',
			onblur: 'ignore',
			emptytext: "Empty (Title)",
			savenochange: true,
			inputclass: 'input-large',
			tpl: "<textarea style='width: 280px; height:70px;'>",
			params: function(params){
				params.tagId = that.currentFormDataId;
				params.type = type;
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					contentData.what = newValue;
					that.showSavedMessage();

					// if filled with other talk break, reload page
					// if (response.data && response.data.require_reload) {
					that._updateWhatValueOfTable(0, newValue);
					that.reloadPreviewForm();
					// }
				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		}
	);

	$('#mobilepreview_who_editlink').editable(
		{
			type: 'text',
			url: '/content/updateMusicTag',
			showbuttons: 'bottom',
			onblur: 'ignore',
			emptytext: "Empty (Artist)",
			savenochange: true,
			inputclass: 'input-large',
			tpl: "<textarea style='width: 280px;'>",
			params: function(params){
				params.tagId = that.currentFormDataId;
				params.type = type;
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					contentData.what = newValue;
					that.showSavedMessage();

					// if filled with other talk break, reload page
					// if (response.data && response.data.require_reload) {
					that._updateWhoValueOfTable(0, newValue);
					that.reloadPreviewForm();
					// }
				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		}
	);

}

MobilePreviewForm.prototype._setupEditableLinks = function(type, id, contentTypeID, whatSource, whoSource) {

	var that = this;

	var connectContentInlineChangeURL = '/content/material/updateAd';

	var emptyWhat = 'Empty';
	var emptyWho = 'Empty';
	var emptyMoreInfo = 'Empty (More Info)';

	if(contentTypeID == ContentTypeIDOfTalk) {
		emptyWhat = 'Empty (Show)';
		emptyWho = 'Empty (Talent)';
	} else if (contentTypeID == ContentTypeIDOfAd) {
		emptyWhat = 'Empty (Headline)';
		emptyWho = 'Empty (Brand)';
	} else if(contentTypeID == ContentTypeIDOfNews  || contentTypeID == ContentTypeIDOfWeather || contentTypeID == ContentTypeIDOfTraffic || contentTypeID == ContentTypeIDOfSport || contentTypeID == ContentTypeIDOfMusicMix) {
		emptyWhat = 'Empty (Show)';
		emptyWho = 'Empty (Talent)';
	} else if(contentTypeID == ContentTypeIDOfMusic) {
		connectContentInlineChangeURL = '/content/updateMusic';
		emptyMoreInfo = 'Empty (Lyrics)';
	}

	if (contentTypeID == ContentTypeIDOfTalk && that.currentFormDataType != 'content') {

		$('#mobilepreview_what_editlink').editable(
				{
					type: 'typeaheadjs',
					typeahead: {
						source: that._getTalkBreakAutoCompleteSource('what', contentData.is_vote, contentData.is_competition),
						displayText: function(item) {
							return item.what;
						},
						matcher: function(item) {
							return true;
						},
						afterSelect: function(item) {
							that.currentWhatAutoCompleteID = item.id;
						},
						autoSelect: false,
						items: 'all'
					},
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: emptyWhat,
					inputclass: 'input-large',
					tpl: "<textarea style='width: 280px; height:70px;'>",
					params: function(params){
						params.autoSuggestContentId = that.currentWhatAutoCompleteID;
						params.check_talkbreak_suggestion = '1';
						params.tagId = that.currentFormDataId;
						return params;
					},
					success: function(response, newValue) {
						if (response.code == 0) {
							contentData.what = newValue;
							that.showSavedMessage();

							// if filled with other talk break, reload page
							if (response.data && response.data.require_reload) {
								that.reloadPreviewForm();
							}
						} else {
							that.showSaveErrorMessage(response);
							return response.msg;
						}
					}
				}
		);


	} else {

		$('#mobilepreview_what_editlink').editable(
				{
					type: 'typeaheadjs',
					typeahead: {
						source: whatSource
					},
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					savenochange: true,
					onblur: 'ignore',
					emptytext: emptyWhat,
					inputclass: 'input-large',
					tpl: "<textarea style='width: 280px; height:70px;'>",
					success: function(response, newValue) {
						if (response.code == 0) {
							if(response.data.content) {
								contentData = response.data.content;
							}
							contentData.what = newValue;
							that.showSavedMessage();
							that._updateWhatValueOfTable(that.currentConnectContentId, newValue);

							/*if (type == 'preview') {
								$('#previewtag_what_' + id).html(newValue);
							} else {

							}*/

							if(type == 'google') {
								that.showGoogleModal(response.data.coverart.id);
							}
							if (response.data && response.data.require_reload) {
								that.reloadPreviewForm();
							}

							updateDailyLogStats();

						} else {
							that.showSaveErrorMessage(response);
							return response.msg;
						}
					}
				}
		);

	}


	if (contentTypeID == ContentTypeIDOfTalk && that.currentFormDataType != 'content') {

		$('#mobilepreview_who_editlink').editable(
				{
					type: 'typeaheadjs',
					typeahead: {
						source: that._getTalkBreakAutoCompleteSource('who', contentData.is_vote, contentData.is_competition),
						displayText: function(item) {
							return item.who;
						},
						matcher: function(item) {
							return true;
						},
						afterSelect: function(item) {
							that.currentWhoAutoCompleteID = item.id;
						},
						autoSelect: false,
						items: 'all'
					},
					params: function(params){
						params.autoSuggestContentId = that.currentWhoAutoCompleteID;
						params.check_talkbreak_suggestion = '1';
						params.tagId = that.currentFormDataId;
						return params;
					},
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: emptyWho,
					inputclass: 'input-large',
					tpl: "<input type='text' style='width: 280px'>",
					success: function(response, newValue) {
						if (response.code == 0) {
							contentData.who = newValue;
							that.showSavedMessage();

							// if filled with other talk break, reload page
							if (response.data && response.data.require_reload) {
								that.reloadPreviewForm();
							} else {
								$('#pasttag_who_' + that.currentFormDataId).html(newValue);
							}
						} else {
							that.showSaveErrorMessage(response);
							return response.msg;
						}
					}
				}
		);

	} else {

		$('#mobilepreview_who_editlink').editable(
				{
					type: 'typeaheadjs',
					typeahead: {
						source: whoSource
					},
					params: function(params){
						if (contentTypeID == ContentTypeIDOfAd) {
							params.check_client_details = '1';
						}
						return params;
					},
					url: connectContentInlineChangeURL,
					savenochange: true,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: emptyWho,
					inputclass: 'input-large',
					tpl: "<input type='text' style='width: 280px'>",
					success: function(response, newValue) {
						if (response.code == 0) {
							if(response.data.content) {
								contentData = response.data.content;
							}
							console.log(response);
							contentData.who = newValue;
							that.showSavedMessage();
							/*if (type == 'preview') {
								$('#previewtag_who_' + id).html(newValue);
							} else {
								$('#pasttag_who_' + id).html(newValue);
							}*/
							that._updateWhoValueOfTable(that.currentConnectContentId, newValue);

							updateDailyLogStats();

							// if filled with client info, reload page
							if (response.data && response.data.require_reload) {
								that.reloadPreviewForm();
							}

						} else {
							that.showSaveErrorMessage(response);
							return response.msg;
						}
					}
				}
		);

	}

	$('#mobilepreview_more_editlink').editable(
			{
				type: 'textarea',
				url: connectContentInlineChangeURL,
				showbuttons: 'bottom',
				savenochange: true,
				onblur: 'ignore',
				emptytext: emptyMoreInfo,
				tpl: "<textarea style='width: 280px;height:300px'>",
				success: function(response, newValue) {
					if (response.code == 0) {
						if(response.data.content) {
							contentData = response.data.content;
						}
						contentData.more = newValue;
						that.showSavedMessage();

						updateDailyLogStats();
					} else {
						that.showSaveErrorMessage(response);
						return response.msg;
					}
				}
			}
	);
	$('#mobilepreview_more_editlink').editable().on('hidden', function () {
		$("#mobilepreview_more").parent().animate({
			scrollTop:0
		},"slow");
	});

}

MobilePreviewForm.prototype.showGoogleModal = function(coverartID) {

	var that = this;

	var songs = [];

	$('#song_table').empty();

	$('#song_modal_title').html('Google Play Search');

	$('#songModal').modal();

	$('#song_search_artist').val(contentData.original_who);
	$('#song_search_title').val(contentData.original_what);

	$('#song_modal_button').off('click').on('click', function() {

		$('#song_modal_loading').show();
		$.ajax(
			{
				url: '/listGooglePlay',
				method: 'POST',
				data: {
					who: $('#song_search_artist').val(),
					what: $('#song_search_title').val()
				}
			}
		).done(function(resp) {

			if(resp.code == 0) {

				$('#song_table').empty();

				songs = resp.songs;

				var hasSongs = false;
				for (var i = 0; i < songs.length; i++) {
					hasSongs = true;
					$('#song_table').append('<tr class="song" style="cursor:pointer;">' +
						'<td><img width="50px" src="' + songs[i].googlePlayCoverArtUrl + '"></td>' +
						'<td>' + songs[i].googlePlayArtist + '</td>' +
						'<td>' + songs[i].googlePlayTitle + '</td></tr>');
				}
				if (!hasSongs) {
					$('#song_table').html('<tr><td>No Songs Found</td><td></td></tr>');
				}

				$('#song_table .song').on('click', function () {
					var index = $(this).index();
					console.log(index);
					console.log(songs[index]);

					$('#song_modal_loading').show();

					$.ajax(
						{
							url: '/content/updateMusicData',
							method: 'POST',
							data: {
								song: songs[index],
								coverartID: coverartID,
								type: 'google'
							}
						}
					).done(function (resp) {
						if (resp.code == 0) {

							$('#songModal').modal('hide');

							that.reloadPreviewForm();
						}
					}).always(function() {
						$('#song_modal_loading').hide();
					});
				});
			}

		}).always(function() {
			$('#song_modal_loading').hide();
		});
	});

	$('#song_mark_wrong').off('click').on('click', function() {

		$('#song_modal_loading').show();

		$.ajax(
			{
				url: '/content/updateMusic',
				method: 'POST',
				data: {
					pk: coverartID,
					name: 'google_available',
					value: 0
				}
			}
		).done(function(resp) {

			if(resp.code == 0) {

				that.reloadPreviewForm();

				$('#songModal').modal('hide');

			}

		}).always(function() {

			$('#song_modal_loading').hide();

		})
	});

	$('#song_modal_button').trigger('click');

}

MobilePreviewForm.prototype.showITunesModal = function(coverartID) {

	var that = this;

	var songs = [];

	$('#song_table').empty();

	$('#song_modal_title').html('iTunes Search');

	$('#songModal').modal();

	$('#song_search_artist').val(contentData.original_who);
	$('#song_search_title').val(contentData.original_what);

	$('#song_modal_button').off('click').on('click', function() {

		$('#song_modal_loading').show();

		$.ajax(
			{
				url: '/listITunes',
				method: 'POST',
				data: {
					who: $('#song_search_artist').val(),
					what: $('#song_search_title').val()
				}
			}
		).done(function(resp) {

			if(resp.code == 0) {

				$('#song_table').empty();

				songs = resp.songs;

				var hasSongs = false;
				for (var i = 0; i < songs.length; i++) {
					hasSongs = true;
					$('#song_table').append('<tr class="song" style="cursor:pointer;">' +
						'<td><img width="50px" src="' + songs[i].iTunesCoverArtUrl + '"></td>' +
						'<td>' + songs[i].iTunesArtist + '</td>' +
						'<td>' + songs[i].iTunesTitle + '</td></tr>');
				}
				if (!hasSongs) {
					$('#song_table').html('<tr><td>No Songs Found</td><td></td></tr>');
				}

				$('#song_table .song').on('click', function () {
					var index = $(this).index();
					console.log(index);
					console.log(songs[index]);

					$('#song_modal_loading').show();

					$.ajax(
						{
							url: '/content/updateMusicData',
							method: 'POST',
							data: {
								song: songs[index],
								coverartID: coverartID,
								type: 'itunes'
							}
						}
					).done(function (resp) {
						if (resp.code == 0) {

							$('#songModal').modal('hide');

							that.reloadPreviewForm();
						}
					}).always(function() {
						$('#song_modal_loading').hide();
					});
				});
			}

		}).always(function() {
			$('#song_modal_loading').hide();
		});
	});



	$('#song_mark_wrong').off('click').on('click', function() {

		$('#song_modal_loading').show();

		$.ajax(
			{
				url: '/content/updateMusic',
				method: 'POST',
				data: {
					pk: coverartID,
					name: 'itunes_available',
					value: 0
				}
			}
		).done(function(resp) {

			if(resp.code == 0) {

				that.reloadPreviewForm();

				$('#songModal').modal('hide');

			}

		}).always(function() {

			$('#song_modal_loading').hide();

		})
	});

	$('#song_modal_button').trigger('click');
}

MobilePreviewForm.prototype._resetFormData = function() {

	this._removeEditableLinks();

	$('#mobilepreview_what').html('');
	$('#mobilepreview_who').html('');
	$('#mobilepreview_more').html('');

	$('#preview-action-button').attr('href', 'javascript:void(0)');
	$('#preview-action-button').removeAttr('style');
	$('#preview-action-button').html('');

	$('#' + this.previewSliderContainerID).removeClass('slider-no-image');
	$('#' + this.previewSliderContainerID).addClass('slider-border');
	$('#vote_options').hide();
	$('#vote_options_percent').hide();

	$('#' + this.previewSliderContainerID).css({"background-color":"white"});
	$('.vote-separator').hide();

	$('#edit-image-button-div').html('');
	$('#edit-action-button-div').html('');
	$('#edit-address-button-div').html('');

	$('#ready_button').hide();

	$('#' + this.previewSliderContainerID).html('');

	$('#new_ad_text').hide();

	$('#preview_audio_player').attr('src', '#');

	if (this.voteExpireCountTimer) {
		clearTimeout(this.voteExpireCountTimer);
		this.voteExpireCountTimer = null;
	}

	$('.google-buttons').hide();
	$('.itunes-buttons').hide();

	$('#openGoogleModalButton').hide();
	$('#openITunesModalButton').hide();
}

MobilePreviewForm.prototype._updateWhoValueOfClientTable = function(client_id, who) {
	if (!client_id) return;
	$('.who_cell_span[data-tag-content-id="' + client_id + '"]').html(who);

	if(who) {
		$('.text_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('disabled').addClass('enabled');
	} else {
		$('.text_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('enabled').addClass('disabled');
	}

}

MobilePreviewForm.prototype._setupEditableClientLinks = function() {
	var clientInlineURL = '/content/saveClientInline';
	var that = this;

	$('#mobilepreview_who_editlink').editable(
		{
			type: 'text',
			url: clientInlineURL,
			showbuttons: 'bottom',
			onblur: 'ignore',
			emptytext: 'Empty (Trading Name)',
			inputclass: 'input-large',
			tpl: "<input type='text' style='width: 280px'>",
			success: function(response, newValue) {
				if (response.code == 0) {
					that.showSavedMessage();

					$('#client_who').val(newValue);
					contentData.who = newValue;

					that._updateWhoValueOfClientTable(contentData.client_id, newValue);
				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		}
	);
	$('#mobilepreview_what_editlink').editable(
		{
			type: 'text',
			url: clientInlineURL,
			showbuttons: 'bottom',
			onblur: 'ignore',
			emptytext: 'Empty (Headline)',
			inputclass: 'input-large',
			tpl: "<input type='text' style='width: 280px'>",
			success: function(response, newValue) {
				if (response.code == 0) {
					that.showSavedMessage();

					contentData.what = newValue;
				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		}
	);
	$('#mobilepreview_more_editlink').editable(
		{
			type: 'textarea',
			url: clientInlineURL,
			showbuttons: 'bottom',
			onblur: 'ignore',
			emptytext: 'Empty (More Info)',
			inputclass: 'input-large',
			tpl: "<textarea style='width: 280px;height:300px'>",
			success: function(response, newValue) {
				if (response.code == 0) {
					that.showSavedMessage();

					contentData.more = newValue;
				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		}
	);

	$('#mobilepreview_more_editlink').editable().on('hidden', function () {
		$("#mobilepreview_more").parent().animate({
			scrollTop:0
		},"slow");
	});

	$('#mobilepreview_who_editlink').on("shown", function() {
		$(this).data('editable').input.$input.on('keyup', function(e) {
			$('#client_who').val($(this).val())
		});
	});

	$('#mobilepreview_who_editlink').on("hidden", function() {
		$('#client_who').val($('#mobilepreview_who_editlink').html());
	});
}

MobilePreviewForm.prototype.renderPreviewClientInformation = function (data, onComplete) {

	this._resetFormData();

	if(data.attachments) {
		$('#' + this.previewSliderContainerID).removeClass('slider-no-image');
		this._renderSliderInformation(data.attachments);
	} else {
		$('#' + this.previewSliderContainerID).removeClass('slider-border');
		$('#' + this.previewSliderContainerID).addClass('slider-no-image');
	}

	//Set up image dropzone editor
	if(contentMobileFormObj == null && contentMobileImageEditor == null) {
		contentMobileFormObj = new ContentMobileForm();
		contentMobileImageEditor = new ContentImageEditor('image-editor-cropper-div-mobile', 'image-editor-cropper-img-mobile', 'content_btn_img_confirm_mobile', 'content_btn_img_cancel_mobile', 'image_editor_preview_mobile', 'image_editor_preview_banner_mobile', '-mobile');
	} else {
		contentMobileFormObj.onAfterFormCreation();
	}

	$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable" data-type="text" data-name="what" data-pk="' + data.client_id + '">' + (data.what === null ? '' : data.what) + '</a>');
	$('#mobilepreview_more').html('<a id="mobilepreview_more_editlink" class="link-editable" data-type="text" data-name="more" data-pk="' + data.client_id + '">' + (data.more === null ? '' : data.more) + '</a>');
	$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="text" data-name="who" data-pk="' + data.client_id + '">' + (data.who === null ? '' : data.who) + '</a>');
	this._setupEditableClientLinks();
	$('#edit-image-button-div').html('<a href="javascript:void(0)" class="edit-image-button" data-toggle="tooltip" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
	$('#edit-action-button-div').html('<a href="javascript:void(0)" class="edit-action-button" data-toggle="tooltip" title="Edit action"><i class="mdi mdi-pencil"></i></a>');
	$('#edit-address-button-div').html('<a href="javascript:void(0)" class="edit-address-button" data-toggle="tooltip" title="Edit address"><i class="mdi mdi-pencil"></i></a>');

	//This is here so that we can get the parent element of the modal (i.e. edit-image-button) after it has been created
	$('#imageModal').modalPopover({modalPosition : 'body', placement: 'bottom', $parent:$('.edit-image-button'), backdrop: false});

	$('#mobilepreview_more_editlink').click(function () {
		$("#mobilepreview_more").parent().animate({
			scrollTop: $("#mobilepreview_more").parent().prop("scrollHeight")
		}, "slow");
	});

	$('#ready_button').show();
	if(data.is_ready) {
		$('#ready_button').html('<i class="mdi mdi-checkbox-marked"></i>').css('color', 'green');
	}
	else {
		$('#ready_button').html('<i class="mdi mdi-checkbox-blank-outline"></i>').css('color', 'red');
	}

	$('#preview-action-button').css({"background-color" : parseAsString(data.content_color)});
	if (data.action_params && data.action_params.website) {
		$('#preview-action-button').html(actionTypesByID[data.action_id]);//parseAsString(data.action.action_label));
		$('#preview-action-button').attr('href', parseAsString(data.action_params.website));
	}
	else if (data.action_params && data.action_params.phone) {
		$('#preview-action-button').html(actionTypesByID[data.action_id]);//parseAsString(data.action.action_label));
	}
	else {
		$('#preview-action-button').css({"background-color": "red"});
		$('#preview-action-button').html('Empty');//parseAsString(data.action.action_label));
	}

	if(onComplete) {
		onComplete(data);
	}

	//Set up map
	if (typeof initMapEdit == 'function' && data) {
		initMapEdit();
	}

	if (!mapScriptLoaded && data.map && data.map.lat && data.map.lng) {
		if (typeof getMapScript == 'function') {
			getMapScript();
		}
		$('#map').show();
		mapScriptLoaded = true;
	} else if (mapScriptLoaded &&  data.map && data.map.lat && data.map.lng) {
		if (typeof initMap == 'function') {
			initMap();
		}
		$('#map').show();
	} else {
		$('#map').hide();
	}

}

//This is mainly for displaying google play music information
MobilePreviewForm.prototype.renderCoverartPreviewInformation = function(song, type) {
	var that = this;

	this._resetFormData();

	var coverartID = song.id;

	var songAvailable = true;
	if(type == 'coverart') {
		if(!song.itunes_available) {
			$('#mobilepreview_what').html('Not available on iTunes');

			$('#mobilepreview_what').html('<p id="mobilepreview_original_what" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_what) + '</p>');
			$('#mobilepreview_who').html('<p id="mobilepreview_original_who" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_who) + '</p>');
			songAvailable = false;
		} else {
			$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="text" data-name="track" data-pk="' + coverartID + '">' + parseAsString(song.track) + '</a>');
			$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="text" data-name="artist" data-pk="' + coverartID + '">' + parseAsString(song.artist) + '</a>');
		}
	} else if(type == 'google') {
		if(!song.google_available) {
			$('#mobilepreview_what').html('Not available on Google Play');

			$('#mobilepreview_what').html('<p id="mobilepreview_original_what" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_what) + '</p>');
			$('#mobilepreview_who').html('<p id="mobilepreview_original_who" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_who) + '</p>');
			songAvailable = false;
		}
		else {
			$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="text" data-name="google_title" data-pk="' + coverartID + '">' + parseAsString(song.google_title ? song.google_title : song.track) + '</a>');
			$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="text" data-name="google_artist" data-pk="' + coverartID + '">' + parseAsString(song.google_artist ? song.google_artist : song.artist) + '</a>');
		}
	}

	if(songAvailable) {
		$('#mobilepreview_what').append('<p id="mobilepreview_original_what" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_what) + '</p>');
		$('#mobilepreview_who').append('<p id="mobilepreview_original_who" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(contentData.original_who) + '</p>');

		$('#mobilepreview_more').html('<a id="mobilepreview_more_editlink" class="link-editable required" data-type="textarea" data-name="lyrics" data-pk="' + coverartID + '">' + parseAsString(song.lyrics) + '</a>');
		this._setupEditableLinks(type, song.id, ContentTypeIDOfMusic);

		$('#edit-image-button-div').html('<a href="javascript:void(0)" class="edit-image-button" data-toggle="tooltip" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
		$('#imageModal').modalPopover({
			modalPosition: 'body',
			placement: 'bottom',
			$parent: $('.edit-image-button'),
			backdrop: false
		});
		$('[data-toggle="tooltip"]').tooltip();
	}

	//We want the tag's original who and what (metadata) and not the coverart's original who and what
	var originalWho = contentData.original_who;
	var originalWhat = contentData.original_what;

	contentData = song;
	contentData.coverart_id = coverartID;

	contentData.original_who = originalWho;
	contentData.original_what = originalWhat;

	var songReadyInfo = {'coverart_id' : coverartID, 'google_ready' : song.google_ready, 'itunes_ready' : song.itunes_ready, 'google_available' : song.google_available, 'itunes_available' : song.itunes_available};
	this.showGoogleITunesLogos(songReadyInfo);

	$('.google-button').off('click').on('click', function() {
		that.renderPreviewInfo('google', coverartID);
	});

	if(!songAvailable) return;

	//Set up image dropzone editor
	if(contentMobileFormObj == null && contentMobileImageEditor == null) {
		contentMobileFormObj = new ContentMobileForm();
		contentMobileImageEditor = new ContentImageEditor('image-editor-cropper-div-mobile', 'image-editor-cropper-img-mobile', 'content_btn_img_confirm_mobile', 'content_btn_img_cancel_mobile', 'image_editor_preview_mobile', 'image_editor_preview_banner_mobile', '-mobile');	} else {
		contentMobileFormObj.onAfterFormCreation();
	}


	// render attachments
	var attachments = song.attachments;
	var audio_attachment = null;
	if (attachments) {
		for(var i in attachments) {
			if(attachments[i].type == 'audio') {
				audio_attachment = attachments[i];
				attachments.splice(i, 1);
			}
		}

		if (attachments.length <= 0) {
			$('#' + this.previewSliderContainerID).removeClass('slider-border');
			$('#' + this.previewSliderContainerID).addClass('slider-no-image');
		}
		this._renderSliderInformation(attachments);
	}

	if (song.stream_url) {
		$('#preview_audio_player').attr('src', parseAsString(song.stream_url));
	}

	if(type == 'coverart' && song.itunes_url) {
		$('#preview-action-button').css({"background-color": '#DD218B'});
		$('#preview-action-button').html('Get');
		$('#preview-action-button').attr('href', song.itunes_url);
	}
	else if(type == 'google' && song.google_music_url) {
		$('#preview-action-button').css({"background-color": '#DD218B'});
		$('#preview-action-button').html('Get');
		$('#preview-action-button').attr('href', song.google_music_url);
	}
	else {
		$('#preview-action-button').css({"background-color": 'red'});
		$('#preview-action-button').html('Empty');
		$('#preview-action-button').attr('href', 'javascript:void(0)');
	}

	$('#edit-action-button-div').html('');
	$('#edit-address-button-div').html('');
	$('#ready_button').hide();

	// $('#google_ready_icon').attr('fill', song.google_ready ? '#7ed321' : '#ff0322');
	// $('#itunes_ready_icon').attr('fill', song.itunes_ready ? '#7ed321' : '#ff0322');
	// $('.google-buttons').show();
	// $('.itunes-buttons').show();

	// $('.itunes-button').off('click').on('click', function() {
	// 	that.renderPreviewInfo(type, data.id);
	// });
}

MobilePreviewForm.prototype.showGoogleITunesLogos = function (data) {

	var that = this;

	$('#google_ready_check').off('click').on('click', function() {
		$.ajax(
			{
				url: '/content/updateMusic',
				method: 'POST',
				data: {
					'pk' : data.coverart_id,
					'name' : 'google_ready',
					'value' : parseAsInt(data.google_ready) ? 0 : 1
				}
			}
		).done(function(resp) {
			if(resp.code == 0) {
				that.showSavedMessage();

				var itunes_ready = parseAsInt(resp.data.coverart.itunes_ready);
				var google_ready = parseAsInt(resp.data.coverart.google_ready);

				that.showGoogleITunesLogos({'coverart_id' : resp.data.coverart.id, 'itunes_ready' : itunes_ready, 'google_ready' : google_ready, 'itunes_available' : resp.data.coverart.itunes_available, 'google_available' : resp.data.coverart.google_available});


				if(google_ready && itunes_ready) {
					contentData.is_ready = 1;
				}
				else if (google_ready || itunes_ready) {
					contentData.is_ready = 2;
				}
				else {
					contentData.is_ready = 0;
				}

				updateDailyLogStats();
			}
		})
	});

	$('#itunes_ready_check').off('click').on('click', function() {
		$.ajax(
			{
				url: '/content/updateMusic',
				method: 'POST',
				data: {
					'pk': data.coverart_id,
					'name': 'itunes_ready',
					'value': parseAsInt(data.itunes_ready) ? 0 : 1
				}
			}
		).done(function(resp) {
			if(resp.code == 0) {
				that.showSavedMessage();

				var itunes_ready = parseAsInt(resp.data.coverart.itunes_ready);
				var google_ready = parseAsInt(resp.data.coverart.google_ready);

				that.showGoogleITunesLogos({'coverart_id' : resp.data.coverart.id, 'itunes_ready' : itunes_ready, 'google_ready' : google_ready, 'itunes_available' : resp.data.coverart.itunes_available, 'google_available' : resp.data.coverart.google_available});

				if(google_ready && itunes_ready) {
					contentData.is_ready = 1;
				}
				else if (google_ready || itunes_ready) {
					contentData.is_ready = 2;
				}
				else {
					contentData.is_ready = 0;
				}

				updateDailyLogStats();
			}
		})
	});

	var google_ready = parseAsInt(data.google_ready);
	var itunes_ready = parseAsInt(data.itunes_ready);

	var google_available = parseAsInt(data.google_available);
	var itunes_available = parseAsInt(data.itunes_available);

	$('#google_ready_check').removeClass('error-red').removeClass('warning-orange').removeClass('success-green');
	$('#google_ready_check').hide();
	$('#itunes_ready_check').hide();

	if(google_ready == 1 && google_available == 1) {
		$('#google_ready_check').html('<i class="mdi mdi-checkbox-marked-outline"></i>');
		$('#google_ready_check').addClass('success-green');
		$('#google_ready_icon').attr('fill', '#7ed321');
	} else if(google_ready == 0 && google_available == 1) {
		$('#google_ready_check').html('<i class="mdi mdi-checkbox-blank-outline"></i>');
		$('#google_ready_check').addClass('warning-orange').show();
		$('#google_ready_icon').attr('fill', 'orange');
	} else {
		$('#google_ready_check').html('<i class="mdi mdi-checkbox-blank-outline"></i>');
		$('#google_ready_check').addClass('error-red');
		$('#google_ready_icon').attr('fill', '#ff0322');
	}

	if(itunes_ready == 1 && itunes_available == 1) {
		$('#itunes_ready_check').html('<i class="mdi mdi-checkbox-marked-outline"></i>');
		$('#itunes_ready_check').addClass('success-green');
		$('#itunes_ready_icon').attr('fill', '#7ed321');
	} else if(itunes_ready == 0 && itunes_available == 1) {
		$('#itunes_ready_check').html('<i class="mdi mdi-checkbox-blank-outline"></i>');
		$('#itunes_ready_check').addClass('warning-orange').show();
		$('#itunes_ready_icon').attr('fill', 'orange');
	} else {
		$('#itunes_ready_check').html('<i class="mdi mdi-checkbox-blank-outline"></i>');
		$('#itunes_ready_check').addClass('error-red');
		$('#itunes_ready_icon').attr('fill', '#ff0322');
	}

	if(google_ready == 1 && google_available == 1 && itunes_ready == 1 && itunes_available == 1) {
		$('#mobilepreview_original_who').hide();
		$('#mobilepreview_original_what').hide();
	} else {
		$('#mobilepreview_original_who').show();
		$('#mobilepreview_original_what').show();
	}

	if(this.currentFormDataType == 'google') {
		if(!data.google_available) {
			$('#openGoogleModalButton').html('Search for Song');
		} else {
			$('#openGoogleModalButton').html('Wrong Song?');
		}
		$('#openGoogleModalButton').show().off('click').on('click', function() {
			that.showGoogleModal(data.coverart_id);
		});
		$('#openITunesModalButton').hide();
	}
	else {
		if(!data.itunes_available) {
			$('#openITunesModalButton').html('Search for Song');
		} else {
			$('#openITunesModalButton').html('Wrong Song?');
		}
		$('#openGoogleModalButton').hide();
		$('#openITunesModalButton').show().off('click').on('click', function() {
			that.showITunesModal(data.coverart_id);
		});;
	}

	$('.google-buttons').show();
	$('.itunes-buttons').show();
}

MobilePreviewForm.prototype.renderPreviewInformation = function(data, type, page){

	var that = this;

	this._resetFormData();

	//Set up image dropzone editor
	if(contentMobileFormObj == null && contentMobileImageEditor == null) {
		contentMobileFormObj = new ContentMobileForm();
		contentMobileImageEditor = new ContentImageEditor('image-editor-cropper-div-mobile', 'image-editor-cropper-img-mobile', 'content_btn_img_confirm_mobile', 'content_btn_img_cancel_mobile', 'image_editor_preview_mobile', 'image_editor_preview_banner_mobile', '-mobile');	} else {
		contentMobileFormObj.onAfterFormCreation();
	}

	if(type != 'content') {
		var connectContentId = parseAsInt(data.connectContentId);
		var contentTypeID = parseAsInt(data.content_type_id);
		var coverartID = parseAsInt(data.coverart_id);

		this.currentConnectContentId = connectContentId;

		if (connectContentId > 0 && (contentTypeID == ContentTypeIDOfTalk || contentTypeID == ContentTypeIDOfPromotion || contentTypeID == ContentTypeIDOfAd
			|| contentTypeID == ContentTypeIDOfNews || contentTypeID == ContentTypeIDOfWeather || contentTypeID == ContentTypeIDOfTraffic || contentTypeID == ContentTypeIDOfSport)) {
			var what = parseAsString(data.what);
			var who = parseAsString(data.who);

			$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="typeaheadjs" data-name="what" data-pk="' + connectContentId + '">' + what + '</a>');
			$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="typeaheadjs" data-name="who" data-pk="' + connectContentId + '">' + who + '</a>');
			$('#mobilepreview_more').html('<a id="mobilepreview_more_editlink" class="link-editable required" data-type="textarea" data-name="more" data-pk="' + connectContentId + '">' + parseAsString(data.more) + '</a>');
			$('#edit-image-button-div').html('<a href="javascript:void(0)" class="edit-image-button" data-toggle="tooltip" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
			//$('#edit-image-button-div').html('<a href="#imageModal" class="edit-image-button" data-toggle="modal-popover" data-placement="bottom" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
			$('#edit-action-button-div').html('<a href="javascript:void(0)" class="edit-action-button" data-toggle="tooltip" title="Edit action"><i class="mdi mdi-pencil"></i></a>');
			$('#edit-address-button-div').html('<a href="javascript:void(0)" class="edit-address-button" data-toggle="tooltip" title="Edit address"><i class="mdi mdi-pencil"></i></a>');

			//This is here so that we can get the parent element of the modal (i.e. edit-image-button) after it has been created
			$('#imageModal').modalPopover({modalPosition : 'body', placement: 'bottom', $parent:$('.edit-image-button'), backdrop: false});

			$('#mobilepreview_more_editlink').click(function () {
				$("#mobilepreview_more").parent().animate({
					scrollTop: $("#mobilepreview_more").parent().prop("scrollHeight")
				}, "slow");
			});
			$('[data-toggle="tooltip"]').tooltip();
			if (contentTypeID == ContentTypeIDOfAd) {
				this._setupEditableLinks(type, data.id, contentTypeID, null, GLOBAL.CLIENT_TRADING_NAME_LIST);
			} else {
				this._setupEditableLinks(type, data.id, contentTypeID);
			}
			$('#ready_button').show();

		} else if (contentTypeID == ContentTypeIDOfMusic && coverartID > 0) {

			var songAvaliable = true;
			if(!contentData.itunes_available) {
				$('#mobilepreview_what').html('Not available on iTunes');
				$('#mobilepreview_what').html('<p id="mobilepreview_original_what" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(data.original_what) + '</p>');
				$('#mobilepreview_who').html('<p id="mobilepreview_original_who" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(data.original_who) + '</p>');

				songAvaliable = false;
			}
			else {
				$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="text" data-name="track" data-pk="' + coverartID + '">' + parseAsString(data.itunes_title ? data.itunes_title : data.what) + '</a>');
				$('#mobilepreview_what').append('<p id="mobilepreview_original_what" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(data.original_what) + '</p>');

				$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="text" data-name="artist" data-pk="' + coverartID + '">' + parseAsString(data.itunes_artist ? data.itunes_artist : data.who) + '</a>');
				$('#mobilepreview_who').append('<p id="mobilepreview_original_who" style="font-size:14px; color:darkgrey;">Original Metadata: ' + parseAsString(data.original_who) + '</p>');

				$('#mobilepreview_more').html('<a id="mobilepreview_more_editlink" class="link-editable required" data-type="textarea" data-name="lyrics" data-pk="' + coverartID + '">' + parseAsString(data.more) + '</a>');
				this._setupEditableLinks(type, data.id, contentTypeID);

				$('#edit-image-button-div').html('<a href="javascript:void(0)" class="edit-image-button" data-toggle="tooltip" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
				$('#imageModal').modalPopover({
					modalPosition: 'body',
					placement: 'bottom',
					$parent: $('.edit-image-button'),
					backdrop: false
				});
				$('[data-toggle="tooltip"]').tooltip();
			}

			$('#edit-action-button-div').html('');
			$('#edit-address-button-div').html('');

			var songReadyInfo = {'coverart_id' : coverartID, 'google_ready' : data.google_ready, 'itunes_ready' : data.itunes_ready, 'google_available' : data.google_available, 'itunes_available' : data.itunes_available};
			this.showGoogleITunesLogos(songReadyInfo);

			$('.google-button').off('click').on('click', function() {
				$('.google-buttons').css('border-bottom', 'solid');
				$('.itunes-buttons').css('border-bottom', 'none');
				that.renderPreviewInfo('google', coverartID);
			});
			$('.itunes-button').off('click').on('click', function() {
				$('.itunes-buttons').css('border-bottom', 'solid');
				$('.google-buttons').css('border-bottom', 'none');
				that.renderPreviewInfo(type, data.id);
			});

			$('#ready_button').hide();

			if(!songAvaliable) return;

		} else if(contentTypeID == ContentTypeIDOfMusic && tagId > 0) {
			$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="text" data-name="what" data-pk="'+tagId+'">' + parseAsString(data.what) + '</a>');

			$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="text" data-name="who" data-pk="'+tagId+'">' + parseAsString(data.who) + '</a>');

			this._setupEditableLinksForMusicTag(type);

		} else {
			$('#mobilepreview_what').html(parseAsString(data.what));
			$('#mobilepreview_who').html(parseAsString(data.who));
			$('#mobilepreview_more').html(nlToBr(parseAsString(data.more)));
			$('#edit-image-button-div').html('');
			$('#edit-action-button-div').html('');
			$('#edit-address-button-div').html('');
			$('#ready_button').hide();
		}

		$('#vote_icon').attr('stroke', '#9B9B9B');
		$('#competition_icon').attr('fill', '#9B9B9B');

		$('#vote_button').attr('title', 'Set as vote');
		$('#competition_button').attr('title', 'Set as competition');

		// Talk Break Related setup
		if (contentTypeID == ContentTypeIDOfTalk && type == 'live') {

			if (contentData && contentData.is_competition) { // already competition?
				$('#competition_button').show();
				$('#competition_button').off('click').on('click', function(){
					that.loadCompetitionResultContent();
				});
				$('#vote_button').hide();
				$('#competition_icon').attr('fill', '#008800');
				$('#competition_button').attr('title', 'View competition result');
			} else if (contentData && contentData.is_vote) {
				$('#vote_button').show();
				$('#vote_button').off('click').on('click', function(){
					that.showCurrentConnectContentVoteResult();
				});
				$('#competition_button').hide();
				$('#vote_icon').attr('stroke', '#543DED');
				$('#vote_button').attr('title', 'View vote result');

			} else {
				$('#vote_button').show();
				$('#competition_button').show();

				$('#competition_button').off('click').on('click', function(){
					that.setCurrentConnectContentAsCompetition();
				});
				$('#vote_button').off('click').on('click', function(){
					that.setCurrentConnectContentAsVote();
				});
			}

			$('#vote_button').tooltip("destroy");
			$('#vote_button').tooltip();
			$('#competition_button').tooltip("destroy");
			$('#competition_button').tooltip();

		} else {
			$('#vote_button').hide();
			$('#vote_button').off('click');
			$('#competition_button').hide();
			$('#competition_button').off('click');
		}
	}
	else if(type == 'content') {
		if(!data) {
			return;
		}

		var connectContentId = parseAsInt(data.id);
		var contentTypeID = parseAsInt(data.content_type_id);

		this.currentConnectContentId = connectContentId;

		if (connectContentId > 0 && (contentTypeID == ContentTypeIDOfTalk || contentTypeID == ContentTypeIDOfPromotion || contentTypeID == ContentTypeIDOfAd || contentTypeID == ContentTypeIDOfMusicMix
			|| contentTypeID == ContentTypeIDOfNews || contentTypeID == ContentTypeIDOfWeather || contentTypeID == ContentTypeIDOfTraffic || contentTypeID == ContentTypeIDOfSport)) {
			$('#mobilepreview_what').html('<a id="mobilepreview_what_editlink" class="link-editable required" data-type="typeaheadjs" data-name="what" data-pk="' + connectContentId + '">' + parseAsString(data.what) + '</a>');
			$('#mobilepreview_who').html('<a id="mobilepreview_who_editlink" class="link-editable required" data-type="typeaheadjs" data-name="who" data-pk="' + connectContentId + '">' + parseAsString(data.who) + '</a>');
			//$('#mobilepreview_what').html('<a href="javascript:editEvent()" style="color:black">' + (data.what ? parseAsString(data.what): '<span style="color:red">Empty</span>') + '</a>');
			//$('#mobilepreview_who').html('<a href="javascript:editEvent()" style="color:black">' + (data.who ? parseAsString(data.who) : '<span style="color:red">Empty</span>') + '</a>');
			$('#mobilepreview_more').html('<a id="mobilepreview_more_editlink" class="link-editable required" data-type="textarea" data-name="more" data-pk="' + connectContentId + '">' + parseAsString(data.more) + '</a>');

			$('#edit-image-button-div').html('<a href="javascript:void(0)" class="edit-image-button" data-toggle="tooltip" title="Edit images"><i class="mdi mdi-pencil"></i></a>');
			//$('#edit-image-button-div').html('<a href="#imageModal" class="edit-image-button" data-toggle="modal-popover" data-placement="bottom" title="Edit images" style="top:60px"><i class="mdi mdi-pencil"></i></a>');
			$('#edit-action-button-div').html('<a href="javascript:void(0)" class="edit-action-button" data-toggle="tooltip" title="Edit action"><i class="mdi mdi-pencil"></i></a>');
			$('#edit-address-button-div').html('<a href="javascript:void(0)" class="edit-address-button" data-toggle="tooltip" title="Edit address"><i class="mdi mdi-pencil"></i></a>');

			//This is here so that we can get the parent element of the modal (i.e. edit-image-button) after it has been created
			$('#imageModal').modalPopover({modalPosition : 'body', placement: 'bottom', $parent:$('.edit-image-button'), backdrop:false});

			$('#mobilepreview_more_editlink').click(function () {
				$("#mobilepreview_more").parent().animate({
					scrollTop: $("#mobilepreview_more").parent().prop("scrollHeight")
				}, "slow");
			});
			$('[data-toggle="tooltip"]').tooltip();

			if(page == 'scheduler') {
				this._setupEditableLinks(type, data.id, contentTypeID, talkShowList, talentList);
			}
			else if (page == 'dailyLog' || page == 'ad') {
				this._setupEditableLinks(type, data.id, contentTypeID);
			}
			else {
				this._setupEditableLinks(type, data.id, contentTypeID);
			}
			$('#ready_button').show();

		} else {
			$('#mobilepreview_what').html(parseAsString(data.what));
			$('#mobilepreview_who').html(parseAsString(data.who));
			$('#mobilepreview_more').html(nlToBr(parseAsString(data.more)));
			$('#edit-image-button-div').html('');
			$('#edit-action-button-div').html('');
			$('#edit-address-button-div').html('');
			$('#ready_button').hide();
		}
	}

	if (contentData) {
		$('#preview-action-button').css({"background-color" : parseAsString(data.content_color)});
		if(type != 'content') {
			if (contentData.action) {
				$('#preview-action-button').html(parseAsString(contentData.action.action_label));
			}
			else {
				$('#preview-action-button').css({"background-color": "red"});
				$('#preview-action-button').html('Empty');//parseAsString(data.action.action_label));
			}
			if (!$.isEmptyObject(contentData.action_params)) {
				if(contentData.action_params.website) {
					var url =  parseAsString(contentData.action_params.website);
					if(url.indexOf('//') < 0) url = '//'+url;
					$('#preview-action-button').attr('href', parseAsString(url));
				}
				else {
					$('#preview-action-button').attr('href', 'javascript:void(0)');
				}
			}
			else {
				$('#preview-action-button').css({"background-color": "red"});
				$('#preview-action-button').html('Empty');//parseAsString(data.action.action_label));
			}

		}
		else if (type == 'content') {
			if (contentData.action_params && contentData.action_params.website) {
				$('#preview-action-button').html(actionTypesByID[contentData.action_id]);//parseAsString(data.action.action_label));
				var url =  parseAsString(contentData.action_params.website);
				if(url.indexOf('//') < 0) url = '//'+url;
				$('#preview-action-button').attr('href', parseAsString(url));
			}
			else if (contentData.action_params && contentData.action_params.phone) {
				$('#preview-action-button').html(actionTypesByID[contentData.action_id]);//parseAsString(data.action.action_label));
			}
			else if (contentData.action_params.length <= 0) {
				$('#preview-action-button').css({"background-color" : "red"});
				$('#preview-action-button').html('Empty');//parseAsString(data.action.action_label));
			}
		}

		// render attachments

		var attachments = contentData.attachments;
		var audio_attachment = null;
		if (attachments) {
			for(var i in attachments) {
				if(attachments[i].type == 'audio') {
					audio_attachment = attachments[i];
					attachments.splice(i, 1);
				}
			}

			if (attachments.length <= 0) {
				$('#' + this.previewSliderContainerID).removeClass('slider-border');
				$('#' + this.previewSliderContainerID).addClass('slider-no-image');
			}
			this._renderSliderInformation(attachments);
		}
		if(data.is_ready) {
			$('#ready_button').html('<i class="mdi mdi-checkbox-marked"></i>').css('color', 'green');
		}
		else {
			$('#ready_button').html('<i class="mdi mdi-checkbox-blank-outline"></i>').css('color', 'red');
		}
	} else {
		$('#' + this.previewSliderContainerID).addClass('slider-no-image');
		$('#preview-action-button').css({"background-color" : "red"});
		$('#preview-action-button').html('Empty');//parseAsString(data.action.action_label));
		$('#ready_button').hide();
	}

	if(contentData.is_vote) {
		$('#vote_icon').attr('stroke', '#543DED');
		$('#competition_button').hide();
		$('span.talkbreak_type_cell_span[data-tag-id="' + data.id + '"]').html('<svg width="24px" height="24px" style="float:left;" viewBox="0 0 26 26" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Client-Info/Images" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round"><g id="vote_icon" transform="translate(-609.000000, -116.000000)" stroke-width="2" stroke="#9B9B9B"><g id="Page-1" transform="translate(610.000000, 117.000000)"><g id="Group-4"><path d="M20.6042623,12.160682 L21.8224918,12.160682 C22.9392787,12.160682 23.8527213,13.0663213 23.8527213,14.1734689 L23.8527213,14.1734689 C23.8527213,15.2806164 22.9392787,16.1867148 21.8224918,16.1867148 L19.7922623,16.1867148" id="Stroke-2"></path><path d="M18.5738492,16.1866689 L20.807423,16.1866689 C21.8122098,16.1866689 22.6347672,17.001882 22.6347672,17.9984066 L22.6347672,17.9984066 C22.6347672,18.9949311 21.8122098,19.8101443 20.807423,19.8101443 L19.5891934,19.8101443" id="Stroke-4"></path><path d="M18.5738492,12.160682 L20.6040787,12.160682 C21.7208656,12.160682 22.6347672,11.2550426 22.6347672,10.1478951 L22.6347672,10.1478951 C22.6347672,9.04074754 21.7208656,8.13464918 20.6040787,8.13464918 L14.9122754,8.13464918 L14.9122754,3.2323541 C14.9122754,1.50002623 13.4824393,0.0825836066 11.7349639,0.0825836066 L10.8513574,0.0825836066 L10.8513574,4.08520656 C10.8513574,7.05458361 8.40020984,9.48461639 5.40512787,9.48461639 L4.5743082,9.48461639 L4.5743082,19.1941902 C4.5743082,21.3134689 6.30663607,23.0311082 8.44381639,23.0315672 L19.8379803,23.0334033 C20.7059803,23.0338623 21.4165377,22.3297311 21.4165377,21.4690754 L21.4165377,21.428223 C21.4165377,20.534059 20.6844066,19.8092721 19.7907016,19.8097311 C19.1545049,19.8101902 18.5738492,19.8101902 18.5738492,19.8101902" id="Stroke-6"></path><path d="M0.968642623,22.9203016 L4.57421639,22.9203016 L4.57421639,8.53518689 L0.968642623,8.53518689 C0.461429508,8.53518689 0.0506098361,8.94600656 0.0506098361,9.45321967 L0.0506098361,22.0022689 C0.0506098361,22.509482 0.461429508,22.9203016 0.968642623,22.9203016 L0.968642623,22.9203016 Z" id="Stroke-8"></path></g></g></g></g></svg>');

		try {
			if (OnAirFormObj && OnAirFormObj.isOnAirMode()) {
				OnAirFormObj.setTagAsVote(data.id);
			}
		}catch(err) {}

		this.renderVote();

	} else {
		$('#vote_icon').attr('stroke', '#9B9B9B');

		if (!contentData.is_competition) $('span.talkbreak_type_cell_span[data-tag-id="' + data.id + '"]').html('');
	}

	if(contentData.is_competition) {
		$('#competition_icon').attr('fill', '#008800');
		$('#vote_button').hide();
		$('span.talkbreak_type_cell_span[data-tag-id="' + data.id + '"]').html('<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="#000000" d="M7,2V4H2V11C2,12 3,13 4,13H7.2C7.6,14.9 8.6,16.6 11,16.9V19C8,19.2 8,20.3 8,21.6V22H16V21.7C16,20.4 16,19.3 13,19.1V17C15.5,16.7 16.5,15 16.8,13.1H20C21,13.1 22,12.1 22,11.1V4H17V2H7M9,4H15V12A3,3 0 0,1 12,15C10,15 9,13.66 9,12V4M4,6H7V8L7,11H4V6M17,6H20V11H17V6Z" /></svg>');

		try {
			if (OnAirFormObj && OnAirFormObj.isOnAirMode()) {
				OnAirFormObj.setTagAsCompetition(data.id);
			}
		}catch(err) {}

	} else {
		$('#competition_icon').attr('fill', '#9B9B9B');

		if (!contentData.is_vote) $('span.talkbreak_type_cell_span[data-tag-id="' + data.id + '"]').html('');
	}

	// stream url
	if (data.stream_url) {
		$('#preview_audio_player').attr('src', parseAsString(data.stream_url));
	}
	if(audio_attachment) {
		$('#preview_audio_player').attr('src', parseAsString(audio_attachment.url));
	}

	// update list view item
	$('#pasttag_who_' + this.currentFormDataId).html(parseAsString(data.who ? data.who : data.what));

	if(contentData.is_vote) {
		$('#pasttag_who_' + this.currentFormDataId).html(parseAsString(contentData.vote_question));
		$('div.tag-clicks-progress-bar[data-tag-id="' + data.id + '"]').addClass('color-content-vote');
	} else {
		$('div.tag-clicks-progress-bar[data-tag-id="' + data.id + '"]').removeClass('color-content-vote');
	}
}

MobilePreviewForm.prototype.setCurrentConnectContentAsCompetition = function() {

	var that = this;

	this.showPreviewLoading();

    $.ajax (
        {
            url: "/content/setCompetition",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.id,
                "tagId" : this.currentFormDataId
            }
        }
    ).done( function( resp ) {
        if (resp.code === 0) {
        	that._updateWhoValueOfTable(contentData.id, resp.data.content.who);
        	that.reloadPreviewForm();
        } else {
			that.showSaveErrorMessage(resp);
        	that.hidePreviewLoading();
        }
    }).fail(function(resp) {
		that.showSaveErrorMessage(resp);
        that.hidePreviewLoading();
    });
}


MobilePreviewForm.prototype.setCurrentConnectContentAsVote = function() {

	var that = this;

	this.showPreviewLoading();

    $.ajax (
        {
            url: "/content/setVote",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.id,
                "tagId" : this.currentFormDataId
            }
        }
    ).done( function( resp ) {
        if (resp.code === 0) {
        	that._updateWhoValueOfTable(contentData.id, resp.data.content.who);
        	that.reloadPreviewForm();
        } else {
			that.showSaveErrorMessage(resp);
        	that.hidePreviewLoading();
        }
    }).fail(function(resp) {
		that.showSaveErrorMessage(resp);
		that.hidePreviewLoading();
    });

}

MobilePreviewForm.prototype.showCurrentConnectContentVoteResult = function() {

}

//--- Vote stuff
MobilePreviewForm.prototype._setupEditableVoteLinks = function(questionSource) {

	var connectContentInlineChangeURL = '/content/material/updateAd';

	var that = this;

	if (that.currentFormDataType != 'content') {

		$('#vote_question_editlink').editable(
			{
				type: 'typeaheadjs',
				typeahead: {
					source: that._getTalkBreakAutoCompleteSource('vote_question', true, false),
					displayText: function(item) {
						//return item.vote_question + "(" + parseAsString(item.vote_option_1) + "/" + parseAsString(item.vote_option_2) + ")";
						return item.vote_question;
					},
					matcher: function(item) {
						return true;
					},
					afterSelect: function(item) {
						that.currentWhatAutoCompleteID = item.id;
					},
					autoSelect: false,
					items: 'all'
				},
				url: connectContentInlineChangeURL,
				showbuttons: 'bottom',
				onblur: 'ignore',
				emptytext: 'Enter a question',
				inputclass: 'input-large',
				tpl: "<input type='text' style='width: 340px;' maxlength=60>",
				params: function(params){
					params.autoSuggestContentId = that.currentWhatAutoCompleteID;
					params.check_talkbreak_suggestion = '1';
					params.tagId = that.currentFormDataId;
					return params;
				},
				success: function(response, newValue) {
					if (response.code == 0) {
						contentData.vote_question = newValue;
						that.showSavedMessage();

						// if filled with other talk break, reload page
						if (response.data && response.data.require_reload) {
							that.reloadPreviewForm();
						} else {
							that._updateWhoValueOfTable(contentData.id, newValue);
						}

					} else {
						that.showSaveErrorMessage(response);
						return response.msg;
					}
				}
			}
		);

		$('#duration_editlink').editable({
			type: 'number',
			url: connectContentInlineChangeURL,
			emptytext: 'Enter Duration',
			params: function(params){
				params.tagId = that.currentFormDataId;
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					contentData.vote_duration_minutes = newValue;
					that.showSavedMessage();

					that.reloadPreviewForm();

				} else {
					that.showSaveErrorMessage(response);
					return response.msg;
				}
			}
		});


		$('#vote_option_1_editlink').editable(
			{
				type: 'text',
				url: connectContentInlineChangeURL,
				showbuttons: 'bottom',
				onblur: 'ignore',
				emptytext: 'Enter Option 1',
				inputclass: 'input-large',
				tpl: "<textarea style='width: 140px; height:120px;' maxlength=48>",
				success: function(response, newValue) {
					if (response.code == 0) {
						contentData.vote_option_1 = newValue;
						that.showSavedMessage();

					} else {
						return response.msg;
					}
				}
			}
		);


		$('#vote_option_2_editlink').editable(
			{
				type: 'text',
				url: connectContentInlineChangeURL,
				showbuttons: 'bottom',
				onblur: 'ignore',
				emptytext: 'Enter Option 2',
				inputclass: 'input-large',
				tpl: "<textarea style='width: 140px; height:120px;' maxlength=48>",
				success: function(response, newValue) {
					if (response.code == 0) {
						contentData.vote_option_2 = newValue;
						that.showSavedMessage();
					} else {
						return response.msg;
					}
				}
			}
		);

	} else {

			$('#vote_question_editlink').editable(
				{
					type: 'typeaheadjs',
					typeahead: {
						source: questionSource
					},
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: 'Enter a question',
					inputclass: 'input-large',
					tpl: "<input type='text' style='width: 340px;' maxlength=60>",
					success: function(response, newValue) {
						if (response.code == 0) {
							contentData.vote_question = newValue;
							that.showSavedMessage();

						} else {
							that.showSaveErrorMessage(response);
							return response.msg;
						}
					}
				}
			);

			$('#duration_editlink').editable({
				type: 'number',
				url: connectContentInlineChangeURL,
				emptytext: 'Enter Duration',
				success: function(response, newValue) {
					if (response.code == 0) {
						contentData.vote_duration_minutes = newValue;
						that.showSavedMessage();

					} else {
						that.showSaveErrorMessage(response);
						return response.msg;
					}
				}
			});


			$('#vote_option_1_editlink').editable(
				{
					type: 'text',
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: 'Enter Option 1',
					inputclass: 'input-large',
					tpl: "<textarea style='width: 140px; height:120px;' maxlength=48>",
					success: function(response, newValue) {
						if (response.code == 0) {
							contentData.vote_option_1 = newValue;
							that.showSavedMessage();

						} else {
							return response.msg;
						}
					}
				}
			);


			$('#vote_option_2_editlink').editable(
				{
					type: 'text',
					url: connectContentInlineChangeURL,
					showbuttons: 'bottom',
					onblur: 'ignore',
					emptytext: 'Enter Option 2',
					inputclass: 'input-large',
					tpl: "<textarea style='width: 140px; height:120px;' maxlength=48>",
					success: function(response, newValue) {
						if (response.code == 0) {
							contentData.vote_option_2 = newValue;
							that.showSavedMessage();
						} else {
							return response.msg;
						}
					}
				}
			);
	}
}

MobilePreviewForm.prototype.startVoteExpiryTimer = function() {

	if (this.voteExpireCountTimer) {
		clearTimeout(this.voteExpireCountTimer);
		this.voteExpireCountTimer = null;
	}

	this.displayExpiryDuration();
}

MobilePreviewForm.prototype.setVoteAsExpired = function() {

	$('#duration').html('Expired');
	$('#duration_minutes').html('');
	$('#vote_expiry_timer_display').html('');

	$('#vote_question_editlink').editable('destroy');
	$('#vote_option_1_editlink').editable('destroy');
	$('#vote_option_2_editlink').editable('destroy');
}

MobilePreviewForm.prototype.displayExpiryDuration = function() {

	var that = this;

	var currentTimestamp = getCurrentUnixTimestamp();

	if (!this.previewData || !this.previewData.connectContent || !this.previewData.connectContent.is_vote) return;

	if (this.previewData.vote_expired == 1) {
		this.setVoteAsExpired();
		return;
	}

	if (!this.previewData.vote_expiry_timestamp) return;

	var duration = this.previewData.vote_expiry_timestamp - currentTimestamp;

	if (duration <= 0) {
		this.setVoteAsExpired();
		return;
	}

	$('#vote_expiry_timer_display').html('Expiring in ' + getDurationString(duration));

	this.voteExpireCountTimer = setTimeout(function(){
		that.displayExpiryDuration();
	}, 1000);

}

MobilePreviewForm.prototype.displayVotePercentage = function() {

	if (!this.previewData) return;

	var vote_option1_count = parseAsInt(this.previewData.vote_option1_count);
	var vote_option2_count = parseAsInt(this.previewData.vote_option2_count);

	var vote_total_count = vote_option1_count + vote_option2_count;

	var vote_option1_percent = 0;
	var vote_option2_percent = 0;

	if (vote_total_count > 0) {
		vote_option1_percent = Math.floor(vote_option1_count / vote_total_count * 100);
		vote_option2_percent = 100 - vote_option1_percent;
	}

	$('#vote_option_1_percent').html(formatPercent(vote_option1_percent) + '%');
	$('#vote_option_2_percent').html(formatPercent(vote_option2_percent) + '%');
}

MobilePreviewForm.prototype.updateVoteOptionCounts = function(tag_id, option1_count, option2_count) {

	if (!this.previewData) return;

	if (this.previewData.id != tag_id) return;

	this.previewData.vote_option1_count = option1_count;
	this.previewData.vote_option2_count = option2_count;

	this.displayVotePercentage();
}

MobilePreviewForm.prototype.renderVote = function() {
	this._resetFormData();

	console.log(contentData);
	$('#' +  this.previewSliderContainerID).css({"background-color" :"#543DED"});
	$('#' +  this.previewSliderContainerID).html('<div id="question_title">Question</div><div id="vote_question"><a id="vote_question_editlink" style="color:white" class="link-editable" data-type="typeaheadjs" data-name="vote_question" data-pk="' + contentData.id + '">' + parseAsString(contentData.vote_question) + '</a></div>');
	$('#' +  this.previewSliderContainerID).append('<div id="duration" style="color:white"><a id="duration_editlink" style="color:white" class="link-editable" data-type="number" data-name="vote_duration_minutes" data-pk="' + contentData.id + '">' + parseAsString(contentData.vote_duration_minutes) + '</a></div><div id="duration_minutes">minutes</div>');
	$('#' +  this.previewSliderContainerID).append('<div id="vote_expiry_timer_display" class="text-center"></div>');

	$('#vote_option_1').html('<div id="vote_option_1_title">Option 1</div><div id="vote_option_1"><a id="vote_option_1_editlink" class="link-editable" data-type="text" data-name="vote_option_1" data-pk="' + contentData.id + '">' + parseAsString(contentData.vote_option_1) + '</a></div>');
	$('#vote_option_2').html('<div id="vote_option_1_title">Option 2</div><div id="vote_option_2"><a id="vote_option_2_editlink" class="link-editable" data-type="text" data-name="vote_option_2" data-pk="' + contentData.id + '">' + parseAsString(contentData.vote_option_2) + '</a></div>');
	$('#vote_options').show();
	$('#vote_options_percent').show();
	$('.vote-separator').show();

	$('#vote_option_1_editlink').click(function () {
		$("#vote_options").parent().animate({
			scrollTop: $("#vote_options").parent().prop("scrollHeight")
	}, "slow");
	});

	$('#vote_option_2_editlink').click(function () {
		$("#vote_options").parent().animate({
			scrollTop: $("#vote_options").parent().prop("scrollHeight")
		}, "slow");
	});

	$('#ready_button').show();
	if(contentData.is_ready) {
		$('#ready_button').html('<i class="mdi mdi-checkbox-marked"></i>').css('color', 'green');
	}
	else {
		$('#ready_button').html('<i class="mdi mdi-checkbox-blank-outline"></i>').css('color', 'red');
	}

	this._setupEditableVoteLinks('');
	$('#preview-action-button').hide();

	this.startVoteExpiryTimer();
	this.displayVotePercentage();
}

MobilePreviewForm.prototype._generateAttachmentPreviewHTML = function(attachment) {

	var html = '';
	var width = parseAsInt(attachment.width);
	var height = parseAsInt(attachment.height);

	if (attachment.type == 'image' || attachment.type == 'logo') {

		var bannerMode = false;
		if (width < REALIMAGE_MIN_WIDTH || height < REALIMAGE_MIN_HEIGHT) {
			bannerMode = true;
		}

		if(attachment.type != 'logo') {
			html = '<img border="0" class="image_editor_preview ' + (bannerMode ? 'blur' : '') + '" src="' + attachment.url + '"/>';
		}

		else if(attachment.type == 'logo' && !bannerMode) {
			html = '<img border="0" class="image_editor_preview ' + '" src="' + attachment.url + '"/>';
		}

		if (bannerMode) {

			var bannerImageScale = Math.min((PREVIEW_IMAGE_WIDTH - BANNER_PADDING) / width, (PREVIEW_IMAGE_HEIGHT - BANNER_PADDING) / height);

			var bannerDisplayWidth = width * bannerImageScale;
			var bannerDisplayHeight = height * bannerImageScale;

			html += '<div class="image_editor_preview_banner" style="width: ' + bannerDisplayWidth + 'px; height: ' + bannerDisplayHeight + 'px; left: ' +  (PREVIEW_IMAGE_WIDTH - bannerDisplayWidth) / 2 + 'px; top: ' + (PREVIEW_IMAGE_HEIGHT - bannerDisplayHeight) / 2 + 'px;">';
			html += '<img border="0" src="' + (this.currentFormDataType == 'google' ? attachment.google_url : attachment.url) + '" width="100%" height="100%"/>';
			html += '</div>';
		}


	} else if (attachment.type == 'video') {
		var video_embed_url = attachment.url;

		if (attachment.vtype == 'youtube') {

			if (attachment.vid && attachment.vid != '') {
				video_embed_url = 'https://www.youtube.com/embed/' + attachment.vid;
			}

		} else if (attachment.vtype == 'vimeo') {

			if (attachment.vid && attachment.vid != '') {
				video_embed_url = 'https://player.vimeo.com/video/' + attachment.vid;
			}
		}

		html = '<iframe width="100%" height="100%" src="' + video_embed_url + '" allowfullscreen></iframe>';
	}

	return html;
}

MobilePreviewForm.prototype._renderSliderInformation = function(attachments) {
	if (attachments == undefined || attachments == null) return;

	if (attachments.length == undefined || attachments.length <= 0) return;

	var innerHTML = '';
	var carousel = false;

	if (attachments.length == 1) {

		innerHTML = this._generateAttachmentPreviewHTML(attachments[0]);

	} else {

		carousel = true;

		innerHTML = '<div id="mobilepreview_slider_carousel" class="carousel slide" data-ride="carousel">';

		var indicatorHTML =  '<ol class="carousel-indicators">';
		var wrapperHTML = '<div class="carousel-inner" role="listbox">';

		var i = 0;

		for (var index in attachments) {

			var attachment = attachments[index];

			indicatorHTML += '<li data-target="#mobilepreview_slider_carousel" style="background-color:grey;" data-slide-to="' + i + '" class="' + ((i == 0) ? 'active' : '') + '"></li>';

			wrapperHTML += '<div class="item ' + ((i == 0) ? 'active' : '') + '">';
			wrapperHTML += this._generateAttachmentPreviewHTML(attachment);
			wrapperHTML += '</div>';

			i++;
		}

		indicatorHTML += '</ol>';
		wrapperHTML += '</div>';

		innerHTML += indicatorHTML;
		innerHTML += wrapperHTML;

		innerHTML += '</div>';

	}

	$('#' + this.previewSliderContainerID).html(innerHTML);

	if (carousel) {
		$('#' + this.previewSliderContainerID + ' #mobilepreview_slider_carousel').carousel();
	}


}

MobilePreviewForm.prototype.showSavedMessage = function () {
	$('.saveProgress').show().html('Saved successfully').css('color', 'green');
	setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
}

MobilePreviewForm.prototype.showSaveErrorMessage = function (resp) {
	$('.saveProgress').show().html('Error. ' + resp ? resp.msg : '').css('color', 'red');
	setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
}
