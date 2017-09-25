var ASPECT_RATIO				= 0.75; // 4:3

var REALIMAGE_MAX_WIDTH 		= 1080;
var REALIMAGE_MAX_HEIGHT		= 810;

var REALIMAGE_MIN_WIDTH			= 800;
var REALIMAGE_MIN_HEIGHT		= 600;

var CROPCONTAINER_MAX_WIDTH		= 540;
var CROPCONTAINER_MAX_HEIGHT	= 405;

var EDITOR_SCALE				= CROPCONTAINER_MAX_WIDTH / REALIMAGE_MAX_WIDTH;

var CROPPER_MIN_WIDTH			= REALIMAGE_MIN_WIDTH * EDITOR_SCALE;
var CROPPER_MIN_HEIGHT			= REALIMAGE_MIN_HEIGHT * EDITOR_SCALE;

var BANNER_PADDING				= 40;
var BANNER_RECT_WIDTH			= CROPPER_MIN_WIDTH - BANNER_PADDING;
var BANNER_RECT_HEIGHT			= CROPPER_MIN_HEIGHT - BANNER_PADDING;

var THRESHOLD_RANGE 			= 5;

var ContentImageEditor = function(cropperDivId, imageElementId, confirmButtonID, cancelButtonID, previewImgElementID, previewBannerDivElementID, mobile) {

	var that = this;

	if(typeof mobile == 'undefined') mobile = '';
	
	this.cropperDivID = cropperDivId;
	this.imageElementId = imageElementId;
	this.imageElement = $('#' + imageElementId);
	
	this.confirmButtonID = confirmButtonID;
	this.cancelButtonID = cancelButtonID;
	
	this.previewImgElementID = previewImgElementID;
	this.previewImgElement = $('#' + previewImgElementID);
	
	this.previewImageWidth = this.previewImgElement.width();
	this.previewImageHeight = this.previewImgElement.height();
	
	this.previewBannerDivElementID = previewBannerDivElementID;
	this.previewBannerDivElement = $('#' + previewBannerDivElementID);
	this.previewBannerImgElement = this.previewBannerDivElement.find('.image_editor_preview_banner_img').first();
	
	//this.previewBannerImgElement = this.previewBannerDivElement.find('img').first();
	
	this.cropHasSetup = false;
	
	this.imageEditorSlider = $('#image-editor-zoom-slider'+mobile).slider(
			{
				reversed: true,
				tooltip: 'always',
				formatter: function() {
					return this.value[0] + '%';
				}
			}
	);
	
	this.imageEditorSlider.on('slide', function(e){
		
		that.setImageZoom(e.value / 100);
		
	});
	
}


ContentImageEditor.prototype.setImageZoom = function(val) {
	
	if (!this.cropHasSetup) return;
	
	this.zoomScale = val;
	
	this.displayCropImage();
	this.showPreview(this.cropCoords);
} 

ContentImageEditor.prototype.setSliderValue = function(val) {
	
	this.imageEditorSlider.slider('setValue', val * 100);
	
}

ContentImageEditor.prototype.initParams = function() {
	
	this.imageElement.attr('src', '');
	this.imageElement.removeAttr('style');
	this.imageElement.hide();
	
	this.previewImgElement.attr('src', '');
	
	this.imageWidth = 0;
	this.imageHeight = 0;
	this.imagePosX = 0;
	this.imagePosY = 0;
	this.imageScaleFactor = 0;
	this.imageDisplayWidth = 0;
	this.imageDisplayHeight = 0;
	
	this.cropWidth = REALIMAGE_MIN_WIDTH;
	this.cropHeight = REALIMAGE_MIN_HEIGHT;
	this.cropPosX = 0;
	this.cropPosY = 0;
	
	this.zoomScale = 1;
}

ContentImageEditor.prototype.initImageEditor = function(image, callback, showBlur, presetOptions) {
	this.initParams();
	
	this.showBlur = showBlur;
	
	this.callback = callback;
	this.imageElement.attr('src', image);
	this.imageElement.show();
	
	this.previewImgElement.attr('src', image);
	this.previewBannerImgElement.attr('src', image);
	
	this.presetOptions = presetOptions;
	
	if (this.presetOptions && this.presetOptions.zoomScale) {
		this.zoomScale = this.presetOptions.zoomScale;
	}
	
	var that = this;
	
	var imageObj = new Image();
	
	imageObj.onload =  function() {
		that.imageWidth = imageObj.width;
		that.imageHeight = imageObj.height;
		
		that.adjustImageSizeAndPosition();
		that.setupCropArea();
		that.showImageInformation();
		
		that.setSliderValue(that.zoomScale);
	};
	
	imageObj.src = image;
	
	var that = this;
	
	$('#' + this.confirmButtonID).off('click').on('click', function(){
		that.callback({
			'imageWidth' : that.imageWidth,
			'imageHeight' : that.imageHeight,
			'imagePosX' : that.imagePosX,
			'imagePosY' : that.imagePosY,
			'imageScaleFactor' : that.imageScaleFactor,
			'imageDisplayWidth' : that.imageDisplayWidth,
			'imageDisplayHeight' : that.imageDisplayHeight,
			'cropWidth' : that.cropWidth,
			'cropHeight' : that.cropHeight,
			'cropPosX' : that.cropPosX,
			'cropPosY' : that.cropPosY,
			'cropDisplayWidth' : that.cropDisplayWidth,
			'cropDisplayHeight' : that.cropDisplayHeight,
			'cropAreaX' : that.cropAreaX,
			'cropAreaY' : that.cropAreaY,
			'cropAreaWidth' : that.cropAreaWidth,
			'cropAreaHeight' : that.cropAreaHeight,
			'editorScale' : EDITOR_SCALE,
			'zoomScale' : that.zoomScale
		});
	});
	
	$('#' + this.cancelButtonID).off('click').on('click', function(){
		that.callback(false);
	});
}

ContentImageEditor.prototype.showImageInformation = function() {

		
	if (this.imageWidth >= REALIMAGE_MIN_WIDTH && this.imageHeight >= REALIMAGE_MIN_HEIGHT) {
		$('p.image-status-icon').addClass('success-green');
		$('p.image-status-icon').removeClass('error-red');
		
		$('p.image-status-icon i').addClass('mdi-checkbox-marked-circle');
		$('p.image-status-icon i').removeClass('mdi-information-outline');
		
		$('p.image-status-description').addClass('success-green');
		$('p.image-status-description').removeClass('error-red');
		
		$('p.image-status-description').html('Image resolution is OK');
		
	} else {
		$('p.image-status-icon').removeClass('success-green');
		$('p.image-status-icon').addClass('error-red');
		
		$('p.image-status-icon i').removeClass('mdi-checkbox-marked-circle');
		$('p.image-status-icon i').addClass('mdi-information-outline');
		
		$('p.image-status-description').removeClass('success-green');
		$('p.image-status-description').addClass('error-red');
		
		$('p.image-status-description').html('Image resolution is too low to fill the frame');
	}
	

	var aspectRatioString = "";
	
	if (this.imageWidth > 0 && this.imageHeight > 0){
	
		var gcdValue = GCD(this.imageWidth, this.imageHeight);
	
		aspectRatioString = (this.imageWidth / gcdValue) + ":" + (this.imageHeight / gcdValue); 
	}
	
	$('span.image-information').html(this.imageWidth + "w * " + this.imageHeight + "h (" + aspectRatioString + ")");

	
}


ContentImageEditor.prototype.displayCropImage = function() {
	
	if (this.imageWidth == 0 || this.imageHeight == 0) return;
	
	if (this.imageWidth > REALIMAGE_MAX_WIDTH || this.imageHeight > REALIMAGE_MAX_HEIGHT) {
		this.imageScaleFactor = Math.min(REALIMAGE_MAX_WIDTH / this.imageWidth, REALIMAGE_MAX_HEIGHT / this.imageHeight);
	} else {
		this.imageScaleFactor = 1;
	}
	
	this.imageDisplayWidth = this.imageScaleFactor * this.imageWidth * EDITOR_SCALE * this.zoomScale;
	this.imageDisplayHeight = this.imageScaleFactor * this.imageHeight * EDITOR_SCALE * this.zoomScale;
	
	this.imageElement.css({width: this.imageDisplayWidth + 'px', height: this.imageDisplayHeight + 'px'});
	
	this.imagePosX = (CROPCONTAINER_MAX_WIDTH - this.imageDisplayWidth) / 2;
	this.imagePosY = (CROPCONTAINER_MAX_HEIGHT - this.imageDisplayHeight) / 2;
	
	this.imageElement.css({left: this.imagePosX + 'px', top: this.imagePosY + 'px'});
	
} 


ContentImageEditor.prototype.adjustImageSizeAndPosition = function() {
	
	this.displayCropImage();
	
	/*if (this.imageWidth >= REALIMAGE_MIN_WIDTH && this.imageHeight >= REALIMAGE_MIN_HEIGHT) {
		var cropperScale = Math.max(this.imageWidth / REALIMAGE_MIN_WIDTH, this.imageHeight / REALIMAGE_MIN_HEIGHT);
		this.cropWidth = cropperScale * REALIMAGE_MIN_WIDTH;
		this.cropHeight = cropperScale * REALIMAGE_MIN_HEIGHT;
	}*/
	
	this.cropDisplayWidth = this.cropWidth * EDITOR_SCALE;
	this.cropDisplayHeight = this.cropHeight * EDITOR_SCALE;
	
	this.cropPosX = (CROPCONTAINER_MAX_WIDTH - this.cropDisplayWidth) / 2;
	this.cropPosY = (CROPCONTAINER_MAX_HEIGHT - this.cropDisplayHeight) / 2;
	
	
	if (this.presetOptions && this.presetOptions.cropDisplayWidth) {
		this.cropDisplayWidth = this.presetOptions.cropDisplayWidth;
	}
	
	if (this.presetOptions && this.presetOptions.cropDisplayHeight) {
		this.cropDisplayHeight = this.presetOptions.cropDisplayHeight;
	}
	
	if (this.presetOptions && this.presetOptions.cropPosX != undefined) {
		this.cropPosX = this.presetOptions.cropPosX;
	}
	
	if (this.presetOptions && this.presetOptions.cropPosY != undefined) {
		this.cropPosY = this.presetOptions.cropPosY;
	}
}

ContentImageEditor.prototype.calculateImageCropArea = function(coords) {
	
	this.cropCoords = coords;
	
	this.cropPosX = coords.x;
	this.cropPosY = coords.y;
	this.cropDisplayWidth = coords.w;
	this.cropDisplayHeight = coords.h;
		
	this.cropAreaX = coords.x - this.imagePosX;
	this.cropAreaWidth = coords.w;
	
	if (this.cropAreaX < 0) {
		this.cropAreaWidth += this.cropAreaX;
		this.cropAreaX = 0;
	}
	
	this.cropAreaY = coords.y - this.imagePosY;
	this.cropAreaHeight = coords.h;
	
	if (this.cropAreaY < 0) {
		this.cropAreaHeight += this.cropAreaY;
		this.cropAreaY = 0;
	}
	
	if (this.cropAreaX + this.cropAreaWidth > this.imageDisplayWidth) {
		this.cropAreaWidth -= (this.cropAreaX + this.cropAreaWidth - this.imageDisplayWidth);
	}
	
	if (this.cropAreaY + this.cropAreaHeight > this.imageDisplayHeight) {
		this.cropAreaHeight -= (this.cropAreaY + this.cropAreaHeight - this.imageDisplayHeight);
	}
	
	//if (this.cropAreaWidth / this.imageScaleFactor < CROPPER_MIN_WIDTH - THRESHOLD_RANGE || this.cropAreaHeight / this.imageScaleFactor < CROPPER_MIN_HEIGHT - THRESHOLD_RANGE) {
	if (this.cropAreaWidth < CROPPER_MIN_WIDTH - THRESHOLD_RANGE || this.cropAreaHeight < CROPPER_MIN_HEIGHT - THRESHOLD_RANGE) {
		this.bannerMode = true;
		this.bannerScale = Math.min(this.cropAreaWidth / CROPPER_MIN_WIDTH, this.cropAreaHeight / CROPPER_MIN_HEIGHT);
		/*this.bannerScale = Math.min(BANNER_RECT_WIDTH / this.cropAreaWidth, BANNER_RECT_HEIGHT / this.cropAreaHeight );
		if (this.bannerScale > 1) this.bannerScale = 1;*/
	} else {
		this.bannerMode = false;
		this.bannerScale = 1;
	}
}

ContentImageEditor.prototype.showPreview = function(coords) {
	
	var that = this;
	
	this.calculateImageCropArea(coords);
	
	var rx = that.previewImageWidth / coords.w;
	var ry = that.previewImageHeight / coords.h;

	this.previewImgElement.css({
		width: Math.round(rx * that.imageDisplayWidth) + 'px',
		height: Math.round(ry * that.imageDisplayHeight) + 'px',
		marginLeft: (Math.round(rx * (coords.x - that.imagePosX)) * (-1)) + 'px',
		marginTop: (Math.round(ry * (coords.y - that.imagePosY)) * (-1)) + 'px'
	});
	
	// banner preview
	if (that.bannerMode) {
		
		if (this.showBlur) {
			that.previewImgElement.show();
		} else {
			that.previewImgElement.hide();
		}
		
		that.previewBannerDivElement.show();
		
		var previewImageWidth = Math.round(rx * that.imageDisplayWidth);
		var previewImageHeight = Math.round(ry * that.imageDisplayHeight);
		
		var previewImageScale = Math.max(that.previewImageWidth / previewImageWidth, that.previewImageHeight / previewImageHeight);
		
		if (previewImageScale <= 0) previewImageScale = 1;
				
		/*this.previewImgElement.css({
			width: previewImageScale * previewImageWidth  + 'px',
			height: previewImageScale * previewImageHeight + 'px',
			marginLeft: ((that.previewImageWidth - previewImageScale * previewImageWidth - Math.round((coords.x - that.imagePosX) / previewImageScale)) / 4) + 'px',
			marginTop: ((that.previewImageHeight - previewImageScale * previewImageHeight - Math.round((coords.y - that.imagePosY) / previewImageScale)) / 2) + 'px'
		});*/
		
		
		var previewBannerWidth = that.bannerScale * that.previewImageWidth;
		var previewBannerHeight = that.bannerScale * that.previewImageHeight;
		
		var previewBannerWidth = that.cropAreaWidth * that.previewImageWidth / CROPPER_MIN_WIDTH;
		var previewBannerHeight = that.cropAreaHeight * that.previewImageHeight / CROPPER_MIN_HEIGHT;
		
		//var bannerRectScale = Math.min(BANNER_RECT_WIDTH / that.cropAreaWidth, BANNER_RECT_HEIGHT / that.cropAreaHeight);
		//var previewBannerWidth = (that.previewImageWidth - BANNER_PADDING) * (that.cropAreaWidth / CROPPER_MIN_WIDTH) * bannerRectScale;
		//var previewBannerHeight = (that.previewImageHeight - BANNER_PADDING) * (that.cropAreaHeight / CROPPER_MIN_HEIGHT) * bannerRectScale;
					
		var rx2 = previewBannerWidth / that.cropAreaWidth;
		var ry2 = previewBannerHeight / that.cropAreaHeight;
		
		that.previewBannerDivElement.css({
			width: previewBannerWidth + 'px',
			height: previewBannerHeight + 'px',
			/*width: Math.round(rx2 * that.imageDisplayWidth) + 'px',
			height: Math.round(rx2 * that.imageDisplayHeight) + 'px',*/
			left: (that.previewImageWidth - previewBannerWidth) / 2 + 'px',
			top: (that.previewImageHeight - previewBannerHeight) / 2 + 'px',
		});
		
		that.previewBannerImgElement.css({
			width: Math.round(rx2 * that.imageDisplayWidth) + 'px',
			height: Math.round(rx2 * that.imageDisplayHeight) + 'px',
			marginLeft: (Math.round(rx2 * that.cropAreaX) * (-1)) + 'px',
			marginTop: (Math.round(rx2 * that.cropAreaY) * (-1)) + 'px'
			/*left: (that.previewImageWidth - previewBannerWidth) / 2 + 'px',
			top: (that.previewImageHeight - previewBannerHeight) / 2 + 'px'*/
		});
		
		that.previewImgElement.addClass('blur');
		
	} else {
		that.previewBannerDivElement.hide();
		that.previewImgElement.removeClass('blur');
		
		that.previewImgElement.show();
	}
}

ContentImageEditor.prototype.setupCropArea = function() {
	
	var that = this;
	
	$('#' + this.cropperDivID).Jcrop({
		aspectRatio: 1 / ASPECT_RATIO,
		minSize: [CROPPER_MIN_WIDTH, CROPPER_MIN_HEIGHT],
		canResize: false,
		bgOpacity: 0.8,
		bgColor: 'gray',
		setSelect: [this.cropPosX, this.cropPosY, this.cropDisplayWidth, this.cropDisplayHeight],
		onChange: function(coords){
			that.showPreview(coords);
		},
		onSelect: function(coords){
			that.showPreview(coords);
		},
	}, function(){
		that.jcropObj = this;
		that.cropHasSetup = true;
	});
	
}
