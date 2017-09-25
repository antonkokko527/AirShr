$(document).ready(function(){

	$(window).resize(function(){
		
		adjustSameHeightElements();
		adjustContentBottomFullHeight();
		adjustAspectFitElements();
	});
	
	
	var adjustSameHeightElements = function() {
		
		$('.same-height').each(function(){
			var cw = $(this).width();
			cw = cw * 0.75 + 30;
			$(this).css({'height':cw+'px'});
		});
		
	};
	
	
	var adjustContentBottomFullHeight = function() {
		
		$('div.content-bottom.full').each(function() {
			
			var parentContainer = $(this).parent();
			var parentContainerHeight = parentContainer.height();
			
			//console.log('parent height: ' + parentContainerHeight);
		
			var siblingsHeight = 0;
			
			$(this).siblings().each(function() {
				if (!$(this).hasClass('content-modal-overlay') && !$(this).hasClass('content-modal-sidebar')) {
					siblingsHeight += $(this).outerHeight();
				}
			});
			
			//console.log('siblings height: ' + siblingsHeight);
			
			var proposedHeight = parentContainerHeight - siblingsHeight;
			
			if (proposedHeight < 200) proposedHeight = 200;
			
			$(this).css({'height':proposedHeight+'px'});
			
		});
		
	};
	
	
	var adjustAspectFitElements = function() {
		
		$('.aspect-fit').each(function(){
			
			var parentContainer = $(this).parent();

			if (!parentContainer.is(':visible')) {
				parentContainer = parentContainer.parent().siblings().first();
			}
			
			var parentWidth = parentContainer.outerWidth();
			var parentHeight = parentContainer.outerHeight();
			
			var selfWidth = $(this).width();
			var selfHeight = $(this).height();
			
			var aspectSizeInfo = getAspectFitSize(parentWidth, parentHeight, selfWidth, selfHeight);
			
			$(this).attr('style', '');
			
			$(this).css({
				position: 'absolute',
				left: aspectSizeInfo.left + 'px',
				top: aspectSizeInfo.top + 'px',
				width: aspectSizeInfo.width + 'px',
				height: aspectSizeInfo.height + 'px',
			});
			
		});
		
	};
	
	adjustSameHeightElements();
	adjustContentBottomFullHeight();
	adjustAspectFitElements();
	
	
	
	$('span.check-mark.check-box').each(function(){
		
		$(this).off('click').on('click', function(){
			
			var checkData = getCircleBoxCheck($(this));
			
			if (!checkData) {
				checkCircleBox($(this), true);
			} else {
				checkCircleBox($(this), false);
			}
			
		});
		
	});
	
});


function checkCircleBox(element, checked) {
	
	if (element == null || element == undefined) return;
	
	if (checked) {
		element.removeClass('deactive');
		element.addClass('enabled-black');
		element.data('check', '1');
	} else {
		element.addClass('deactive');
		element.removeClass('enabled-black');
		element.data('check', '0');
	}
}

function getCircleBoxCheck(element) {
	var checkData = element.data('check');
	if (!checkData || checkData == 0) {
		return false;
	} else {
		return true;
	}
}

function showLoading() {
	$('.loading').removeClass('hide');
}

function hideLoading() {
	$('.loading').addClass('hide');
}

function showGlobalLoading() {
	$('#globalLoadingIcon').removeClass('hide');
}

function hideGlobalLoading() {
	$('#globalLoadingIcon').addClass('hide');
}


function dateFromTimestamp(timestamp) {
	return new Date(timestamp);
}

function parseStringToDate(string, format)
{
	if (string == undefined || string == null || string == "") return null;
	
	if (format == undefined) format = 'DD-MM-YYYY';
	
	return moment(string, format).toDate();
}

function getTextOfArrayByValue(valList, val) {
	for (var index in valList) {
		if (valList[index].value == val) 
			return valList[index].text;
	}
	return "";
}

function parseAsString(val) {
	if (val == null || val == undefined) return "";
	return val + "";
}

function parseAsInt(val) {
	if (val == null || val == undefined) return 0;
	var parsed = parseInt(val);
	if (isNaN(parsed)) return 0;
	return parsed;
}

function parseAsBool(val) {
	if (val == null || val == undefined) return false;
	if (val == '1' || val == 1) return true;
	return false;
}

function nlToBr(str) {
	return str.replace(new RegExp('\r?\n','g'), '<br />');
}

function GCD(a, b) {
    if (!b) {
        return a;
    }
    return GCD(b, a % b);
}

function getDurationString(seconds)
{
	var minutes = Math.floor(seconds / 60);
	seconds = seconds - minutes * 60;
	if (seconds < 10) seconds = "0" + seconds;
	return minutes +  ":" + seconds;
}

function getCurrentUnixTimestamp() {
	return Math.round(new Date().getTime() / 1000);
}

function formatPercent(percent) {
	return percent < 10 ? "0" + percent : percent;
}

function getAspectFitSize(containerWidth, containerHeight, imageWidth, imageHeight) {
	
	var scaleFactor = 1;
	if (imageWidth > containerWidth || imageHeight > containerHeight) {
		scaleFactor = Math.min(containerWidth / imageWidth,  containerHeight / imageHeight);
	}
	
	imageWidth *= scaleFactor;
	imageHeight *= scaleFactor;
	
	if (imageWidth == 0) imageWidth = containerWidth;
	if (imageHeight == 0) imageHeight = containerHeight;
	
	var left = ( containerWidth - imageWidth ) / 2;
	var top = ( containerHeight - imageHeight ) / 2;
	
	return {
		left: left,
		top: top,
		width: imageWidth,
		height: imageHeight
	};
}



// Global Functions & Variables

function removeOptionFromSelect(select, val) {
	if (!val || val == '') return;
	select.find("option[value='" + val + "']").remove();
}


function addOptionToSelect(select, val, text) {
	//removeOptionFromSelect(select, val);
	var existing = select.find("option[value='" + val + "']");
	if (existing && existing.length && existing.length > 0) {
		console.log('existing');
		return;
	}
	select.append('<option value="' + val + '">' + text + '</option>');
}

function getContentTypeString(id) {
	for (var index in contentTypeList) {
		if (index == id) return contentTypeList[index];
	}
	return "";
}

function getContentTypeIdWithVal(val) {
	for (var index in contentTypeList) {
		if (contentTypeList[index] == val) return index;
	}
	return 0;
}

function getContentTypeIdOfAd(){
	return getContentTypeIdWithVal('Ad');
}

var ContentTypeIDOfAd = getContentTypeIdOfAd();

function getContentTypeIdOfMaterialInstruction(){
	return getContentTypeIdWithVal('Material Instruction');
}

var ContentTypeIDOfMaterialInstruction = getContentTypeIdOfMaterialInstruction();

function getContentTypeIdOfAudio(){
	return getContentTypeIdWithVal('Audio');
}

var ContentTypeIDOfAudio = getContentTypeIdOfAudio();


function getContentTypeIdOfMusic(){
	return getContentTypeIdWithVal('Music');
}

var ContentTypeIDOfMusic = getContentTypeIdOfMusic();

function getContentTypeIdOfPromotion(){
	return getContentTypeIdWithVal('Promotion');
}

var ContentTypeIDOfPromotion = getContentTypeIdOfPromotion();

function getContentTypeIdOfDailyLog(){
	return getContentTypeIdWithVal('Daily Log');
}

var ContentTypeIDOfDailyLog = getContentTypeIdOfDailyLog();

function getContentTypeIdOfTalkShow(){
	return getContentTypeIdWithVal('Talk Show');
}

var ContentTypeIDOfTalkShow = getContentTypeIdOfTalkShow();

function getContentTypeIdOfTalkBreak(){
	return getContentTypeIdWithVal('Talk Break');
}

var ContentTypeIDOfTalkBreak = getContentTypeIdOfTalkBreak();


function getContentTypeIdOfTalk(){
	return getContentTypeIdWithVal('Talk');
}

var ContentTypeIDOfTalk = getContentTypeIdOfTalk();
var ContentSubTypeIDOfTalkShow = 1;
var ContentSubTypeIDOfSegment = 2;

function getContentTypeIdOfNews(){
	return getContentTypeIdWithVal('News');
}

var ContentTypeIDOfNews = getContentTypeIdOfNews();

function getContentTypeIdOfWeather(){
	return getContentTypeIdWithVal('Weather');
}

var ContentTypeIDOfWeather = getContentTypeIdOfWeather();

function getContentTypeIdOfTraffic(){
	return getContentTypeIdWithVal('Traffic');
}

var ContentTypeIDOfTraffic = getContentTypeIdOfTraffic();

function getContentTypeIdOfSport(){
	return getContentTypeIdWithVal('Sport');
}

var ContentTypeIDOfSport = getContentTypeIdOfSport();


function getContentTypeIdOfClientInfo(){
	return getContentTypeIdWithVal('Client Info');
}

var ContentTypeIDOfClientInfo = getContentTypeIdOfClientInfo();


function getContentTypeIdOfMusicMix(){
	return getContentTypeIdWithVal('Music Mix');
}

var ContentTypeIDOfMusicMix = getContentTypeIdOfMusicMix();

// print 

function PrintElem(elem, title)
{
	PopupPrint($(elem).html(), title);
}

function PopupPrint(data, title) 
{
    var mywindow = window.open('', '_blank', 'height=600,width=800,left=100,top=100');
    mywindow.document.write('<html><head><title>' + title + '</title>');
    mywindow.document.write('</head><body >');
    mywindow.document.write(data);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10

    mywindow.print();
    mywindow.close();

    return true;
}