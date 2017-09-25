$(document).ready(function () {
	$('#player audio').mediaelementplayer({
		audioHeight: 64,
		features: ['playpause', 'progress']
	});


	var preview = new MobilePreview('cover');
	preview.renderPreviewInfo('', tagID, initialise);

	$(window).on('resize scroll', function () {
		if (jscd.os.indexOf('iOS') === -1) {
			recalibrateRail();
		}
	});

	$('#btnSendAppLink').click(function () {
		$('#btnSendAppLink').prop('disabled', 'disabled');

		$.ajax({
			url: 'http://connect.airshr.net/sendAppLink?phone_number=' + encodeURIComponent($('#txtPhoneNumber').val()),
			type: 'get',
			cache: false,
			async: false,
			contentType: "application/json",
			dataType: 'jsonp',
			jsonpCallback: 'sendAppCallback'
		}).always(function () {});
	});

	$(window).on('load', function() {
		carouselNormalization();
	});
});

$('#track-information .more .more-content-more').click(function () {
	$('#track-information .more .more-content').hide();
	$('#track-information .more .more-content-more').hide();
	$('#track-information .more .more-content-full').show();

	return false;
});

$('.share ul li a').hover(function () {
	$(this).find('.static').hide();
	$(this).find('.hover').show();
}, function () {
	$(this).find('.static').show();
	$(this).find('.hover').hide();
});

function initialise(data) {
	if (data.connectContent) {
		if (data.connectContent.action_params) {
			if (contentTypeList[contentTypeId] == 'Music') {
				if (jscd.os.indexOf('Mac') !== -1) {
					$('#player .call-to-action-button').attr('href', parseAsString(data.connectContent.action_params.website));
					$('#player .call-to-action-button').css('display', 'inline').html('<img class="iTunes-button" src="/img/getItOnItunes.png">');
				} else if (jscd.os.indexOf('iOS') !== -1) {
					$('#player .call-to-action-button').attr('href', parseAsString(data.connectContent.action_params.website));
					$('#track-information .lyrics .lyrics-button').attr('href', parseAsString('https://itunes.apple.com/au/app/airshr/id970256863'));
					$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://itunes.apple.com/au/app/airshr/id970256863'));
					$('#player .call-to-action-button').css('display', 'inline').html('<img class="iTunes-button" src="/img/getItOnItunes.png">');
				} else if (jscd.os.indexOf('Android') !== -1) {
					$('#player .call-to-action-button').attr('href', parseAsString(data.connectContent.action_params.website_google));
					$('#track-information .lyrics .lyrics-button').attr('href', parseAsString('https://play.google.com/store/apps/details?id=com.airshr.androidapp'));
					$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://play.google.com/store/apps/details?id=com.airshr.androidapp'));
					$('#player .call-to-action-button').css('display', 'inline').html('<img class="googlePlay-button" src="/img/getItOnGooglePlay.png">');
				}
			} else {
				if (jscd.os.indexOf('iOS') !== -1) {
					$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://itunes.apple.com/au/app/airshr/id970256863'));
				} else if (jscd.os.indexOf('Android') !== -1) {
					$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://play.google.com/store/apps/details?id=com.airshr.androidapp'));
				}

				if (parseAsString(data.connectContent.action_params.website).length <= 0 && parseAsString(data.connectContent.action_params.phone).length > 0) {
					if (jscd.os.indexOf('iOS') !== -1 || jscd.os.indexOf('Android') !== -1) {
						$('#player .call-to-action-button').attr('href', 'tel:' + parseAsString(data.connectContent.action_params.phone));
						$('#player .call-to-action-button').html('Call ' + data.who);
					} else {
						$('#player .call-to-action-button').hide();
					}
				} else {
					$('#player .call-to-action-button').attr('href', parseAsString(data.connectContent.action_params.website));
					$('#player .call-to-action-button').html('Visit ' + data.who);
				}
			}
		} else {
			if (jscd.os.indexOf('iOS') !== -1) {
				$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://itunes.apple.com/au/app/airshr/id970256863'));
			} else if (jscd.os.indexOf('Android') !== -1) {
				$('#get-the-app .get-the-app-button').attr('href', parseAsString('https://play.google.com/store/apps/details?id=com.airshr.androidapp'));
			}
		}
	}

	var track   = $('#track-information .track').html();
	var artist  = $('#track-information .artist').html();
	var station = $('.station').html();
}

function recalibrateRail() {
	var rail   = '#player .mejs-controls div.mejs-time-rail';
	var margin = 64;

	if ($(window).width() >= 768) {
		margin = 128;
	}

	$(rail).css('top', ($(window).height() - margin) + 'px');

	for (var i = 0; i < 100; i++) {
		setTimeout(function () {
			$(rail).css('top', ($(window).height() - margin) + 'px');
		}, 10);
	}
}

function carouselNormalization() {
	var items   = $('#cover .item');
	var heights = [];
	var tallest;

	if (items.length) {
		function normalizeHeights() {
			items.each(function() {
				var height = ($(this).height() / $(this).width()) * 300;

				heights.push(height);
			});

			tallest = Math.max.apply(null, heights);

			items.each(function() {
				$(this).css('min-height', tallest + 'px');
			});
		}

		normalizeHeights();

		$(window).on('resize orientationchange', function() {
			tallest = 0,
			heights.length = 0;

			items.each(function() {
				$(this).css('min-height', '0');
			});

			normalizeHeights();
		});
	}
}

function sendAppCallback(resp) {
	if (resp.code == 0) {
		$('#topMsg').html('Nicely done! Check your SMS.');

		setTimeout(function() {
			$('#getAppModalDlg').modal('hide');
		}, 3000);
	} else {
		$('#topMsg').html('Error. ' + resp.msg);
		$('#btnSendAppLink').removeAttr('disabled');
	}
}

function getApp() {
	$('#txtPhoneNumber').val('04');
	$('#topMsg').html("Enter your phone number, and we'll text you a <br/>download link!");
	$('#btnSendAppLink').removeAttr('disabled');
	$('#getAppModalDlg').modal('show');
	$('#txtPhoneNumber').focus();
}
