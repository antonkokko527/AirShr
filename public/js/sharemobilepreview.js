var MobilePreview = function (previewSliderContainerID) {
	this.previewSliderContainerID = previewSliderContainerID;
}

MobilePreview.prototype.renderPreviewInfo = function (type, id, onComplete) {
	var that = this;

	this.showPreviewLoading();

	$.ajax({
		url: "/tag/" + id,
		type: "get",
		dataType: "json",
		success: function (resp) {
			if (resp.code === 0 && resp.data != undefined) {
				var tagDateRaw = moment.unix(resp.data.tag_timestamp >= 1000000000000 ? resp.data.tag_timestamp / 1000 : resp.data.tag_timestamp);
				var yesterday  = moment().startOf('day').subtract(1, 'days');
				var tomorrow   = moment().startOf('day').add(1, 'days');
				var today      = moment().startOf('day');
				var tagDate    = tagDateRaw.format('D MMM YYYY');

				if (tagDateRaw.isBetween(today, tomorrow)) {
					tagDate = 'Today';
				} else if(tagDateRaw.isBetween(yesterday, today)) {
					tagDate = 'Yesterday';
				}

				var tagTime = tagDateRaw.format('h:mma');

				// All the data needed for display on the share page
				shareContent = {};
				shareContent.tagDate = tagDate;
				shareContent.tagTime = tagTime;
				shareContent.contentTypeName = getContentTypeString(resp.data.content_type_id);

				if (shareContent.contentTypeName == 'Ad') {
					shareContent.contentTypeName = 'Offer';
				}

				resp.data.contentTypeName = shareContent.contentTypeName;

				shareContent.contentColor = parseAsString(resp.data.content_color);
				shareContent.what         = resp.data.what;
				shareContent.who          = resp.data.who;
				shareContent.more         = resp.data.more;
				shareContent.stationName  = parseAsString(resp.data.station_abbrev);

				if (resp.data.content_type_id == getContentTypeIdOfMusic()) {
					shareContent.googlePlayURL = resp.data.connectContent.action_params.website_google;
					shareContent.itunesURL     = resp.data.connectContent.action_params.website;
				} else if (resp.data.connectContent.action_params && resp.data.connectContent.action_params.website) {
					shareContent.websiteURL    = resp.data.connectContent.action_params.website;
				}

				//shareContent.audioURL    = parseAsString(resp.data.stream_url);
				shareContent.audioURL    = parseAsString(resp.data.render_url);
				if (!shareContent.audioURL || shareContent.audioURL == '') {
					shareContent.audioURL    = parseAsString(resp.data.stream_url);
				}
				
				shareContent.attachments = resp.data.connectContent.attachments;

				// console.log(shareContent);

				// This will become obselete
				that.renderPreviewInformation(resp.data);

				if (onComplete) {
					onComplete(resp.data);
				}
			} else {
				if (onComplete) {
					onComplete(null);
				}
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

MobilePreview.prototype.showPreviewLoading = function() {
	// $('#loader').removeClass('hide');
}

MobilePreview.prototype.hidePreviewLoading = function() {
	// $('#loader').addClass('hide');
}

MobilePreview.prototype._resetFormData = function() {
	$('#track-information .track').html('');
	$('#track-information .artist').html('');
	$('#track-information .more .more-content').html('');
	$('#player .call-to-action-button').attr('href', 'javascript:void(0)');
	$('#player .call-to-action-button').html('Go');
	$('#' + this.previewSliderContainerID + ' .carousel-inner').html('');
	$('#player audio').attr('src', '#');
}

MobilePreview.prototype.renderPreviewInformation = function (data) {
	this._resetFormData();

	$('body').addClass(parseAsString(data.contentTypeName.toLowerCase()));
	$('.content-type').html(data.contentTypeName);
	$('.track').html(parseAsString(data.what));

	contentTypeId   = data.content_type_id;

	if (contentTypeList[contentTypeId] == 'Music'
		|| contentTypeList[contentTypeId] == 'Ad'
		|| contentTypeList[contentTypeId] == 'Promotion') {
		$('.artist').html(parseAsString(data.who));
	}

	$('.station').html(data.station_abbrev);
	$('.date').html(shareContent.tagDate + ', ' + shareContent.tagTime);

	var charactersLimit = 150;
	var more = nlToBr(parseAsString(data.more).substring(0, charactersLimit));

	if (nlToBr(parseAsString(data.more)).length > charactersLimit) {
		$('#track-information .more .more-content-more').show();
		more = more + '...';
	}

	$('#track-information .lyrics .lyrics-content').html(more);
	// $('#track-information .more .more-content').html(more);
	$('#track-information .more .more-content').html(nlToBr(parseAsString(data.more)));

	if (parseAsString(data.more).length > 0) {
		$('#track-information .more .more-title').show();
	} else {
		$('#track-information .more .more-title').hide();
	}

	if (data.connectContent) {
		if (data.connectContent.action) {
			// $('#player .call-to-action-button').html(parseAsString(data.connectContent.action.action_label));
		}

		if (data.connectContent.attachments) {
			this._renderSliderInformation(data.connectContent.attachments);
		}
	}

	tagDateForShare = shareContent.tagDate + ' ' + shareContent.tagTime;
	
	if (data.render_url) {
		$('#player audio').attr('src', parseAsString(data.render_url));
	} else if (data.stream_url) {
		$('#player audio').attr('src', parseAsString(data.stream_url));
	}

	$('.secondary-playpause-button').on('click', function() {
		$('.mejs-playpause-button button').trigger('click');
	});

	$('.mejs-playpause-button button').on('click', function() {
		var secondaryButton = $('.secondary-playpause-button');
		secondaryButton.attr('title', (secondaryButton.attr('title') == 'Play' ? 'Pause' : 'Play')) ;
	})
}

MobilePreview.prototype._generateAttachmentPreviewHTML = function (attachment) {
	var html = '';

	console.log(attachment);

	if (attachment.type == 'image' || attachment.type == 'logo') {

		var actualHeight = 300 / attachment.width * attachment.height;

		html = '<div class="secondary-playpause" style="top:'+(Math.floor(actualHeight/2)-50)+'px;" > <button class="secondary-playpause-button"type="button" aria-controls="mep_0" title="Play" aria-label="Play"></button> </div><img src="' + attachment.url + '">';

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

MobilePreview.prototype._renderSliderInformation = function (attachments) {
	if (attachments == undefined || attachments == null) {
		$('#' + this.previewSliderContainerID).hide();
		return;
	}

	if (attachments.length == undefined || attachments.length <= 0) {
		$('#' + this.previewSliderContainerID).hide();
		$('#' + this.previewSliderContainerID + ' .carousel-inner').append('<div class="item active"></div>');
		return;
	}

	var i = 0;

	for (var index in attachments) {
		var attachment  = attachments[index];
		var html        = '<div class="item' + ((i == 0) ? ' active' : '') + '">' + this._generateAttachmentPreviewHTML(attachment) + '</div>';

		$('#' + this.previewSliderContainerID + ' .carousel-inner').append(html);

		i++;
	}
}
