var Ad_Rec_Type_List = new Array();

Ad_Rec_Type_List.push({value: 'rec', text: 'Rec'});
Ad_Rec_Type_List.push({value: 'live', text: 'Live'});
Ad_Rec_Type_List.push({value: 'sim_live', text: 'Sim Live'});


var DEFAULT_CONTENT_DATES_COUNT = 3;

var skipConfirm = false;
var overwriteExistingMIAd = true;

function getAdRecTypeTextByValue(val) {
	return getTextOfArrayByValue(Ad_Rec_Type_List, val);
}

function getDurationTextByValue(val) {
	return getTextOfArrayByValue(durationValuesList, val);
}

function getPercentTextByValue(val) {
	return getTextOfArrayByValue(adPercentValuesList, val);
}


$(document).ready(function(){
	setupAutoCompleteForCompany();
	setupAutoCompleteForProduct();
	setupDatePicker();
	
	contentFormObj = new ContentForm();
	
	contentImageEditor = new ContentImageEditor('image-editor-cropper-div', 'image-editor-cropper-img', 'content_btn_img_confirm', 'content_btn_img_cancel', 'image_editor_preview', 'image_editor_preview_banner');
});


function setupAutoCompleteForCompany(update) {
	
	if (update) {
		$( "#content_client" ).typeahead().data('typeahead').source = clientCompanyList;
		$( "#search_content_client" ).typeahead().data('typeahead').source = clientCompanyList;
		$( "#search_content_client2" ).typeahead().data('typeahead').source = clientCompanyList;
		$( "#search_content_client3" ).typeahead().data('typeahead').source = clientCompanyList;
	} else {
		$( "#content_client" ).typeahead({
		      source: clientCompanyList
		});
		
		$( "#search_content_client" ).typeahead({
		      source: clientCompanyList
		});
		
		$( "#search_content_client2" ).typeahead({
		      source: clientCompanyList
		});
		
		$( "#search_content_client3" ).typeahead({
		      source: clientCompanyList
		});
	}
}

function setupAutoCompleteForProduct(update) {
	
	if (update) {
		$( "#content_product" ).typeahead().data('typeahead').source = clientProductList;
		$( "#content_product2" ).typeahead().data('typeahead').source = clientProductList;
		$( "#content_client_product" ).typeahead().data('typeahead').source = clientProductList;
		$( "#search_content_product" ).typeahead().data('typeahead').source = clientProductList;
		$( "#search_content_product2" ).typeahead().data('typeahead').source = clientProductList;
	} else {
		$( "#content_product" ).typeahead({
		      source: clientProductList
		});
		
		$( "#content_product2" ).typeahead({
		      source: clientProductList
		});
		
		$( "#content_client_product" ).typeahead({
		      source: clientProductList
		});
		
		$( "#search_content_product" ).typeahead({
		      source: clientProductList
		});
		
		$( "#search_content_product2" ).typeahead({
		      source: clientProductList
		});
	}
}

function updateAutoCompleteCompanyAndProductList() {
	
	$.ajax ( 
		{
			url: "/content/clientList",
			type: "get",
			dataType: "json",
			success: function( resp ) {
				if (resp.code === 0 && resp.data) {
					clientCompanyList = resp.data;
					setupAutoCompleteForCompany(true);
				}
			}
		}
	).fail ( function () {
		
		
	}).always( function () {
		
	});
	
	
	$.ajax ( 
		{
			url: "/content/productList",
			type: "get",
			dataType: "json",
			success: function( resp ) {
				if (resp.code === 0 && resp.data) {
					clientProductList = resp.data;
					setupAutoCompleteForProduct(true);
				}
			}
		}
	).fail ( function () {
		
		
	}).always( function () {
		
	});
}

function setupDatePicker() {
	/*$('#content_atb_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});*/
	
	/*$('#content_start_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});
	
	$('#content_end_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});*/
	
	/*$('#search_atb_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});*/
	
	$('#content_talk_start_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});
	
	$('#content_talk_end_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});
	
	$('#content_talk_start_time').timepicker({
		showMeridian: false,
		defaultTime: false
	});
	
	$('#content_talk_end_time').timepicker({
		showMeridian: false,
		defaultTime: false
	});
	
	$('#search_start_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});
	
	$('#search_end_date').datepicker({
		autoclose:  true,
		format: 'dd-mm-yyyy'
	});
	
	
	$('#search_content_talk_start_time').timepicker({
		showMeridian: false,
		defaultTime: false
	});
	
	$('#search_content_talk_end_time').timepicker({
		showMeridian: false,
		defaultTime: false
	});
	
	$('#search_created_date').datepicker({
		autoclose: true,
		format: 'dd-mm-yyyy'
	});
	
	$('#dailylog_date').datepicker({
		autoclose: true,
		format: 'dd-mm-yyyy'
	});
	
	$('#dailylog_date').datepicker('setDate', new Date());
	$('#dailylog_date').datepicker('update', new Date());
}


var uploadURL = '/content/upload';
var materialAdInlineChangeURL = '/content/material/updateAd';

var contentFormObj = null;
var contentImageEditor = null;

var openImageEditor = function(image, callback, showBlur, presetOptions) {
	
	if (contentImageEditor == null) return;
	
	contentImageEditor.initImageEditor(image, callback, showBlur, presetOptions);
	
	$('#image_editor_overlay').show();
}

var hideImageEditor = function() {
	$('#image_editor_overlay').hide();
}

var ContentForm = function() {
		
	var that = this;
	
	// left side bar preview
	this.previewFormObj = new MobilePreviewForm('mobilepreview_slider_container');

	$('.mobilepreview_content_container .preview-close-button').off('click').on('click', function() {
		$('#mobilepreview_sidebar').addClass('hidden');
		that.prevTagTagID = null;
	});
	
	this.assignTalkContentID = null;
	
	// navigation
	this.navigationArray = new Array();
	
	$('#goBackLinkContainer .goBackLink').off('click').on('click', function() {
		that.popNavigation();
	});
	
	
	this.AttachmentImage1 = null;
	this.AttachmentImage2 = null;
	this.AttachmentImage3 = null;
	this.AttachmentLogo1 = null;
	this.AttachmentAudio1 = null;
	
	this.nextAction = '';
	this.nextParam = null;
	
	this.setupDropZone();
	
	this.setupDataTable();
	
	this.setFormToInitMode();
	
	
	
	// Set up event listener
	$('#content_action_id').on("change", function() {
		that.setupActionParamFields($(this).val());
	});
	
	
	$('#content_btn_save').on("click", function() {
		that.saveForm();
	});
	
	$('#content_btn_new').on("click", function() {
		that.newForm();
	});
	
	$('#content_btn_copy').on("click", function() {
		that.copyForm();
	});
	
	$('#content_btn_remove').on("click", function() {
		that.deleteForm();
	});
	
	$('#content_btn_print').on("click", function() {
		that.printForm();
	});
	
	$('#content_btn_search').on("click", function() {
		that.searchForm();
	});
	
	$('#content_btn_preview').on("click", function() {
		that.previewForm();
	});
	
	$('#content_content_type_id').on("change", function() {
		that.onContentTypeSelected();
	});
	
	
	$('#content_rec_type').on("change", function() {
		if ($(this).val() == 'live') {
			$('#content_audio_enabled').prop('checked', true);
		}
	});
	
	// Search condition event listeners
	
	$('#search_content_sub_type_id').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_sub_type_id2').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_sub_type_id3').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_rec_type').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_ad_length').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_client').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_atb_date').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_line_number').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_start_date').datepicker().on("changeDate", function(){
		that.loadSearchResult();
	});
	
	$('#search_end_date').datepicker().on("changeDate", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_session_name').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_talk_start_time').timepicker().on("changeTime.timepicker", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_talk_end_time').timepicker().on("changeTime.timepicker", function(){
		that.loadSearchResult();
	});
	
	$('#search_ad_key').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_product').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_manager_user_id').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_agency_id').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_created_date').datepicker().on("changeDate", function(){
		that.loadSearchResult();
	});
	
	$('#dailylog_date').datepicker().on("changeDate", function(){
		that.loadSearchResult();
	});
	
	$('#material_add_ad_btn').on("click", function() {
		that.addSubContent();
	});
	
	
	$('#search_content_client2').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_client3').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_product2').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_agency_id2').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_manager_user_id2').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_who').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#search_content_what').on("change", function(){
		that.loadSearchResult();
	});
	
	$('#content_version').on("change", function() {
		
		if (that.formMode == 'search') {
			that.loadSearchResult();
		} else if (that.formMode == 'edit') {
			that.createAnotherVersionOfContent();
		}
	});
	
	// Pre populate client info
	$('#content_client').on("change", function(){
		that.prepopulateClientInfo();
	});
	
	
	// talk
	$('#content_content_sub_type_id3').on("change", function(){
		that.updateTalkForm();
	});
	
	for (var i = 0; i < 7; i++){
		$('#search_content_talk_weekday_' + i).on("click", function() {
			that.loadSearchResult();
		});
	}
	
	
	$('#content_talk_assign_btn').on('click', function() {
		OnAirFormObj.resetContentAssociation(that.contentAssociation);
		that.showTalkAssignSideBar();
	});
	
	$('#talk_assign_yes_btn').on('click', function() {
		that.contentAssociation = OnAirFormObj.getContentAssociation();
		that.hideTalkAssignSideBar();
		that.saveForm(true);
	});
	
	$('#talk_assign_no_btn').on('click', function() {
		that.hideTalkAssignSideBar();
	});
	
	$('#content_create_demo_tag').on('click', function() {
		that.createTagFromContent();
	});
	
	
	this.onAfterFormCreation();
}

ContentForm.prototype.onAfterFormCreation = function() {
	
	var that = this;

	this.getCurrentPage();
	
	if (initialFormMode == 'edit' && initialContentID != 0) {
		
		that.loadContentDetails(initialContentID, function(){
			
			if (prevPage == 'onair') {
				that._saveOnAirNavigation();
			}
			
		});
		
	} else if (initialFormMode == 'search' && initialContentTypeID != 0) {
		
		this.searchForm();
		$('#content_content_type_id').val(initialContentTypeID);
		this.onContentTypeSelected();
		
	}
	
}

ContentForm.prototype.createTagFromContent = function() {
	
	if (!isStationPrivate()) return;
	
	var that = this;
	
	bootbox.confirm("Are you sure to create Tag from this content?<br/>Note. Please make sure you have saved all the data entered in this form before creating tag.", function(result){
		
		if (result) {
			
			showLoading();
			
			$.ajax ( 
				{
					url: "/content/createTagFromContent",
					type: "post",
					dataType: "json",
					data: {
						"content_id" : that.content_id
					},
					success: function( resp ) {
						if (resp.code === 0) {
							$('.saveProgress').show().html('Success. Tag has been created from content').css('color', 'green');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
							that.tag_id_list = resp.data.tag_id_list;
							that.renderTagIdList();
						} else {
							$('.saveProgress').show().html('Tag creation error. ' + resp.msg).css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
						}
					}
				}
			).fail ( function () {

				$('.saveProgress').show().html('Tag creation error. Network error').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				
			}).always( function () {
				
				hideLoading();
			});
			
		}
		
	});
	
}

ContentForm.prototype.showTalkAssignSideBar = function() {
	this.assignTalkContentID = this.content_id;
	$('#onair_sidebar').removeClass('hidden');
	OnAirFormObj._scrollPastTagsToBottom();
	$(window).resize();
}


ContentForm.prototype.hideTalkAssignSideBar = function() {
	this.assignTalkContentID = null;
	$('#onair_sidebar').addClass('hidden');
	$(window).resize();
}


ContentForm.prototype.createAnotherVersionOfContent = function() {
	
	var that = this;
	
	var newContentVersion = $('#content_version').val();
	
	if (newContentVersion == '') {
		$('#content_version').val(this.content_version);
		return;
	}
	
	if (newContentVersion != this.content_version) {
		
		bootbox.confirm("Are you sure you want to create another version of this Material Instruction?", function(result){
			
			if (result) {
			
				showLoading();
				
				$.ajax ( 
					{
						url: "/content/material/copyWithNewVersion",
						type: "post",
						data: {
							"content_id" : that.content_id,
							"content_version" : newContentVersion
						},
						dataType: "json",
						success: function( resp ) {
							if (resp.code == 0 && resp.data) {
								
								that.loadContentDetails(resp.data.contentID);
								
							} else {

								$('.saveProgress').show().html('Copy Failed. ' + resp.msg).css('color', 'red');
								setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
								
								hideLoading();
							}
						}
					}
				).fail ( function () {

					$('.saveProgress').show().html('Copy Failed. Network Error').css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
					
					hideLoading();
					
				}).always( function () {
					
					
				});
		
			} else {
				$('#content_version').val(that.content_version);
			}
		});
		
	}
}

ContentForm.prototype.pushNavigation = function(nav) {
	this.navigationArray.push(nav);
}

ContentForm.prototype.hasBack = function() {
	if (this.navigationArray.length > 0) return this.navigationArray[this.navigationArray.length - 1];
	return false;
}

ContentForm.prototype.popNavigation = function() {
	if (!this.hasBack()) return;
	var lastNavigationInfo = this.navigationArray.pop();
	this._processNavigation(lastNavigationInfo);
}

ContentForm.prototype._processNavigation = function(navInfo) {
	
	showLoading();
	
	this.setFormToInitMode();
	
	if (navInfo.formMode == 'search') {
		this.setFormToInitSearchMode();
	
		if (navInfo.content_content_type_id) {
			$('#content_content_type_id').val(navInfo.content_content_type_id);
			if (!navInfo.preserveForm) {
				this.onContentTypeSelected();
			} else {
				$('#mobilepreview_sidebar').addClass('hidden');
				this.hideTalkAssignSideBar();
				this.setFormToSearchMode();
				this.showSearchForm(navInfo.content_content_type_id, true);
				this.showSearchResultTable(navInfo.content_content_type_id);
				this.loadSearchResult();
			}
		}
	} else if (navInfo.formMode == 'edit') {
		
		this.loadContentDetails(navInfo.content_content_id);
		
	} else if (navInfo.formMode == 'url' && navInfo.url != null && navInfo.url != '') {
		
		document.location = navInfo.url;
		
	}

	hideLoading();
}

ContentForm.prototype.updateDailyLogStatistics = function() {
	var that = this;
	
	$.ajax(
		{
			url: "/content/previewlog",
			type: "post",
			data: {
				"dailylog_date": that.search_dailylog_date,
				"dailylog_content_type": that.search_dailylog_content_type,
				"dailylog_only_missing": that.search_dailylog_only_missing
			},
			dataType: "json",
			success: function (resp) {
				if (resp.data) {
					that.showDailyLogStatistics(resp.data.statistics, resp.data.statistics_unique);
				}
			}
		}
	);
}

ContentForm.prototype.setupDataTable = function() {
	
	var that = this;
	
	$.fn.dataTable.moment( 'D-MMM' );
	
	$.fn.dataTable.ext.order['content-type'] = function  ( settings, col )
	{
	    return this.api().column( col, {order:'index'} ).nodes().map( function ( td, i ) {
	        return $('span', td).data('content-type');
	    } );
	}
	
	
	$.fn.dataTableExt.oApi.fnSortNeutral = function ( oSettings ){
	    /* Remove any current sorting */
	    oSettings.aaSorting = [];

	    /* Sort display arrays so we get them in numerical order */
	    oSettings.aiDisplay.sort( function (x,y) {
	        return x-y;
	    } );
	    oSettings.aiDisplayMaster.sort( function (x,y) {
	        return x-y;
	    } );

	    /* Redraw */
	    oSettings.oApi._fnReDraw( oSettings );
	};
	
	// Content List Table - Ad
	this.ContentListTable = $('#content-list-table').dataTable(
			{
				paging: false,
				searching: false,
				info: false,
				columns :[
				            {"data" : "start"},
				            {"data" : "end"},
				            {"data" : "type"},
				            {"data" : "content_rec_type"},
				            {"data" : "who"},
				            {"data" : "what"},
				            {"data" : "key"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "duration"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "audio_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "text_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "image_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "action_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "is_ready"},
				],
				sScrollY: $('#content-bottom-full-wrapper').height() - 52,
				ajax: function(data, callback, settings) {
				
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/list",
								type: "post",
								data: {
									"search_content_type_id" : that.search_content_type_id,
									"search_content_sub_type_id" : that.search_content_subtype_id,
									"search_content_rec_type" : that.search_content_rec_type,
									"search_ad_length" : that.search_ad_length,
									"search_content_client" : that.search_content_client,
									"search_atb_date" : that.search_atb_date,
									"search_line_number" : that.search_line_number,
									"search_start_date" : that.search_start_date,
									"search_end_date" : that.search_end_date,
									"search_ad_key" : that.search_ad_key,
									"search_content_product" : that.search_content_product,
									"search_manager_user_id" : that.search_manager_user_id,
									"search_agency_id" : that.search_agency_id,
									"search_created_date" : that.search_created_date
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.data)
										callback({aaData: resp.data});
									else
										callback({aaData: new Array()});
								}
							}
						).fail ( function () {
							
							callback({aaData: new Array()});
							
						}).always( function () {
							
							hideTableLoader();
						});
				}
			}
	);
	
	
	$('#content-list-table').on('draw.dt', function() {
		
		$('#content-list-table tbody tr').off('click').on('click', function(){

			var contentID = findContentIDOfRow($(this));
			
			if (contentID == null) return;
			
			document.location = '/content/ad/' + contentID;
			
			/*that.loadContentDetails(contentID, function() {
				that._saveOtherSearchNavigation();
			});*/
			
		});
		
	});
	
	var adjustContentTableHeight = function() {
		 if (that.ContentListTable == null) return;
		 
		 var oSettings = that.ContentListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.ContentListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
    };
	
    $(window).resize(function () {
		 adjustContentTableHeight();
	});  
    
    adjustContentTableHeight();
    
    
    // Content List Table - Talk
    this.ContentTalkListTable = $('#content-talk-list-table').dataTable(
			{
				paging: false,
				searching: false,
				info: false,
				columns :[
				          	{"data" : "type"},
				          	{"data" : "who"},
				            {"data" : "what"},
				            {"data" : "start"},
				            {"data" : "end"},
				            {"data" : "start_time"},
				            {"data" : "end_time"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "text_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "image_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "action_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "is_ready"},
				],
				sScrollY: $('#content-bottom-full-wrapper').height() - 52,
				ajax: function(data, callback, settings) {
				
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/list",
								type: "post",
								data: {
									"search_content_type_id" : ContentTypeIDOfTalk, //that.search_content_type_id,
									"search_content_sub_type_id" : that.search_content_subtype_id,
									"search_session_name" : that.search_session_name,
									"search_content_client" : that.search_content_client,
									"search_start_date" : that.search_start_date,
									"search_end_date" : that.search_end_date,
									"search_start_time" : that.search_start_time,
									"search_end_time" : that.search_end_time,
									"search_content_weekdays" : that.searchContentWeekDays,
									"search_content_who" : that.search_content_who,
									"search_content_what" : that.search_content_what
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.data)
										callback({aaData: resp.data});
									else
										callback({aaData: new Array()});
								}
							}
						).fail ( function () {
							
							callback({aaData: new Array()});
							
						}).always( function () {
							
							hideTableLoader();
						});
				}
			}
	);
	
	// Content List Table - News
    this.ContentNewsListTable = $('#content-news-list-table').dataTable(
			{
				paging: false,
				searching: false,
				info: false,
				columns :[
				          	{"data" : "who"},
				            {"data" : "what"},
				            {"data" : "start"},
				            {"data" : "end"},
				            {"data" : "start_time"},
				            {"data" : "end_time"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "text_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "image_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "action_enabled"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "is_ready"},
				],
				sScrollY: $('#content-bottom-full-wrapper').height() - 52,
				ajax: function(data, callback, settings) {
				
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/list",
								type: "post",
								data: {
									"search_content_type_id" : that.search_content_type_id,
									"search_session_name" : that.search_session_name,
									"search_content_client" : that.search_content_client,
									"search_start_date" : that.search_start_date,
									"search_end_date" : that.search_end_date,
									"search_start_time" : that.search_start_time,
									"search_end_time" : that.search_end_time,
									"search_content_weekdays" : that.searchContentWeekDays,
									"search_content_who" : that.search_content_who,
									"search_content_what" : that.search_content_what
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.data)
										callback({aaData: resp.data});
									else
										callback({aaData: new Array()});
								}
							}
						).fail ( function () {
							
							callback({aaData: new Array()});
							
						}).always( function () {
							
							hideTableLoader();
						});
				}
			}
	);

	$('#content-talk-list-table').on('draw.dt', function() {
		
		$('#content-talk-list-table tbody tr').off('click').on('click', function(){

			var contentID = findContentIDOfRow($(this));
			
			if (contentID == null) return;
			
			var subContentType = findContentSubTypeIDOfRow($(this));
			
			if (subContentType == 'Individual Segment') {
				document.location = '/content/talkBreak/' + contentID;
				return;
			}
			
			that.loadContentDetails(contentID, function() {
				that._saveOtherSearchNavigation();
			});
			
		});
		
	});

	$('#content-news-list-table').on('draw.dt', function() {
		
		$('#content-news-list-table tbody tr').off('click').on('click', function(){

			var contentID = findContentIDOfRow($(this));
			
			if (contentID == null) return;
			
			that.loadContentDetails(contentID, function() {
				that._saveOtherSearchNavigation();
			});
			
		});
		
	});
	
	var adjustContentTalkTableHeight = function() {
		 if (that.ContentTalkListTable == null) return;
		 
		 var oSettings = that.ContentTalkListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.ContentTalkListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
    };

    var adjustContentNewsTableHeight = function() {
		 if (that.ContentNewsListTable == null) return;
		 
		 var oSettings = that.ContentNewsListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.ContentNewsListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
    };
	
    $(window).resize(function () {
    	adjustContentTalkTableHeight();
	});  

	$(window).resize(function () {
    	adjustContentNewsTableHeight();
	});  
    
    adjustContentTalkTableHeight();
    
    adjustContentNewsTableHeight();

    // Material Search List Table
    this.MaterialContentListTable = $('#material-list-table').dataTable(
			{
				paging: false,
				searching: false,
				info: false,
				columns :[
				            {"data" : "client"},
				            {"data" : "product"},
				            {"data" : "atb_date"},
				            {"data" : "line_number"},
				            {"data" : "version"},
				            {"data" : "created"},
				            {"orderable" : false, "className" : 'dt-body-center', "data" : "is_ready"},
				],
				sScrollY: $('#content-bottom-full-wrapper').height() - 52,
				ajax: function(data, callback, settings) {
				
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/list",
								type: "post",
								data: {
									"search_content_type_id" : that.search_content_type_id,
									"search_content_sub_type_id" : that.search_content_subtype_id,
									"search_ad_length" : that.search_ad_length,
									"search_content_client" : that.search_content_client,
									"search_atb_date" : that.search_atb_date,
									"search_line_number" : that.search_line_number,
									"search_start_date" : that.search_start_date,
									"search_end_date" : that.search_end_date,
									"search_ad_key" : that.search_ad_key,
									"search_content_product" : that.search_content_product,
									"search_manager_user_id" : that.search_manager_user_id,
									"search_agency_id" : that.search_agency_id,
									"search_created_date" : that.search_created_date,
									"search_content_version" : that.search_content_version
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.data)
										callback({aaData: resp.data});
									else
										callback({aaData: new Array()});
								}
							}
						).fail ( function () {
							
							callback({aaData: new Array()});
							
						}).always( function () {
							
							hideTableLoader();
						});
				}
			}
	);
    
    $('#material-list-table').on('draw.dt', function() {
		
		$('#material-list-table tbody tr').off('click').on('click', function(){

			var contentID = findContentIDOfRow($(this));
			
			if (contentID == null) return;
			
			that.loadContentDetails(contentID, function(){
				that._saveOtherSearchNavigation();
			});
			
		});
		
	});
	
	var adjustMaterialContentTableHeight = function() {
		 if (that.MaterialContentListTable == null) return;
		 
		 var oSettings = that.MaterialContentListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.MaterialContentListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
    };
	
    $(window).resize(function () {
    	adjustMaterialContentTableHeight();
	});  
    
    adjustMaterialContentTableHeight();
    
        
    // Material Instruction Ad Table
    this.MaterialInstructionAdListTable = $('#material-ad-list-table').dataTable(
    	{
    		paging: false,
			searching: false,
			ordering: false,
			info: false,
			columns :[
			          	{"className" : 'dt-body-center', "data" : "number", "width" : "10px"},
			          	{"data" : "ad_rec_type_html", "width" : "50px" },
			            {"data" : "start_date_html", "width" : "60px"},
			            {"data" : "end_date_html", "width" : "60px"},
			            {"data" : "instructions_html", "width" : "35%"},
			            {"data" : "what_html", "width" : "35%"},
			            {"data" : "key_html", "width" : "30%"},
			            {"className" : 'dt-body-center', "data" : "preview_button_html", "width" : "1px"},
						{"className" : 'dt-body-center', "data" : "copy_ad_checkbox_html", "width" : "1px"},
			            {"data" : "duration_html", "width" : "20px"},
			            {"data" : "percent_html", "width" : "20px"},
			            {"className" : 'dt-body-center', "data" : "audio_enabled_html", "width" : "1px"},
			            {"className" : 'dt-body-center', "data" : "text_enabled_html", "width" : "1px"},
			            {"className" : 'dt-body-center', "data" : "is_ready_html", "width" : "1px"},
			            {"className" : 'dt-body-center', "data" : "action_html", "width" : "1px"},
			],
			sScrollY: $('#content-bottom-full-wrapper').height() - 52,
			autoWidth: false,
			bAutoWidth: false
    	}
    );
    
    $('#material-ad-list-table').on('draw.dt', function() {
    	that.onDrawMaterialAdTable();
    });

    this.MaterialInstructionAdListTableObj = $('#material-ad-list-table').DataTable();
    
    var adjustMaterialAdTableHeight = function() {
		 if (that.MaterialInstructionAdListTable == null) return;
		 
		 var oSettings = that.MaterialInstructionAdListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.MaterialInstructionAdListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
   };
	
   $(window).resize(function () {
		 adjustMaterialAdTableHeight();
	});  
   
   adjustMaterialAdTableHeight();
   
   
   // Audio Bulk Upload Table
   this.audioBulkUploadTable = $('#audio-upload-list-table').dataTable(
   	{
   			paging: false,
			searching: false,
			info: false,
			columns :[
			          	{"className" : 'dt-body-right', "data" : "play_html", "width" : "30px", "orderable": false},
			          	{"data" : "filename", "width" : "20%" },
			          	{"data" : "entered", "width" : "10%" },
			            {"data" : "who_html", "width" : "25%"},
			            {"data" : "what_html", "width" : "25%"},
			            {"data" : "adKey_html", "width" : "20%"},
			            {"className" : 'dt-body-center', "data" : "text_enabled_html", "width" : "1px", "orderable": false},
			            {"className" : 'dt-body-center', "data" : "image_enabled_html", "width" : "1px", "orderable": false},
			            {"className" : 'dt-body-center', "data" : "action_enabled_html", "width" : "1px", "orderable": false},
			            {"className" : 'dt-body-center', "data" : "is_ready_html", "width" : "1px", "orderable": false},
			            {"data" : "action_html", "width" : "50px", "orderable": false},
			],
			sScrollY: $('#content-bottom-full-wrapper').height() - 52,
			autoWidth: false,
			bAutoWidth: false
   	}
   );
   
   $('#audio-upload-list-table').on('draw.dt', function() {
   	that.onDrawAudioUploadListTable();
   });

   this.audioBulkUploadTableObj = $('#audio-upload-list-table').DataTable();
   
   var adjustAudioUploadListTableHeight = function() {
		 if (that.audioBulkUploadTable == null) return;
		 
		 var oSettings = that.audioBulkUploadTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.audioBulkUploadTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
  };
	
  $(window).resize(function () {
	  adjustAudioUploadListTableHeight();
	});  
  
  adjustAudioUploadListTableHeight();
   
   
  
  //Daily Log Meta List Table  
  this.dailyLogTagListTable = $('#dailylog-tag-list-table').dataTable(
  	{
  			paging: false,
			searching: false,
			ordering: true,
			info: false,
			columns :[
			          	{"className" : 'dt-body-center column-header-center', "data" : "time_html", "width" : "60px", "orderable" : true},
			          	{"className" : 'dt-body-center', "data" : "content_type_html", "width" : "1px", "orderable" : true, "orderDataType" : "content-type", "type" : 'string'},
			            {"data" : "who_html", "width" : "10%", "orderable" : true},
			            {"data" : "what_html", "width" : "10%", "orderable" : true},
			            /*{"data" : "cart_html", "width" : "100px", "orderable" : false},*/
			            {"data" : "zettaid_html", "width" : "100px", "orderable" : false},
			            {"data" : "adKey_html", "width" : "80px", "orderable" : false},
			            {"data" : "duration_html", "width" : "20px", "orderable" : false},
			            {"className" : 'dt-body-center', "data" : "audio_enabled_html", "width" : "1px", "orderable" : false},
			            {"className" : 'dt-body-center', "data" : "text_enabled_html", "width" : "1px", "orderable" : false},
			            {"className" : 'dt-body-center', "data" : "image_enabled_html", "width" : "1px", "orderable" : false},
			            {"className" : 'dt-body-center', "data" : "action_enabled_html", "width" : "1px", "orderable" : false},
			            {"className" : 'dt-body-center', "data" : "is_ready_html", "width" : "1px", "orderable" : false}
			],

			order: [],
			sScrollY: $('#content-bottom-full-wrapper').height() - 52,
			autoWidth: false,
			bAutoWidth: false,
			fnRowCallback: function( row, previewTag, index, iDisplayIndexFull ) {
				if( previewTag.content_type_id == 1 && previewTag.is_client_found == false ) {
					$('td span[class="who_cell_span"]', row).addClass('missing-client');
				}
			},
			ajax: function(data, callback, settings) {
				
				showTableLoader();

				$.ajax ( 
					{
						url: "/content/previewlog",
						type: "post",
						data: {
							"dailylog_date" : that.search_dailylog_date,
							"dailylog_content_type" : that.search_dailylog_content_type,
							"dailylog_only_missing" : that.search_dailylog_only_missing
						},
						dataType: "json",
						success: function( resp ) {
							if (resp.data) {
								that.showDailyLogStatistics(resp.data.statistics, resp.data.statistics_unique);
								var dailyLogMetaTags = new Array();
								for (var index in resp.data.preview_tags) {
									var item = resp.data.preview_tags[index];
									var newPreviewTag = new PreviewTagModel;
									newPreviewTag.loadDataFromJson(item);
									newPreviewTag.generateHTMLContentForRow();
									dailyLogMetaTags.push(newPreviewTag);
								}

								callback({aaData: dailyLogMetaTags});
							}
							else
								callback({aaData: new Array()});
						}
					}
				).fail ( function () {
					
					callback({aaData: new Array()});
					
				}).always( function () {

					//This is to display the button next to the who in the daily log when a client is missing and to display the modal which
					//displays similar existing clients
					$('#dailylog-tag-list-table tbody tr').hover(function(){
						var hoverRow = ($(this).index()+1);

						var tagID = findTagIDOfRow($(this));

						$('.open-client-modal-button').remove();
						if($('#dailylog-tag-list-table tr[role="row"]:nth-child(' + hoverRow + ') .who_cell_span').hasClass('missing-client')) {
							var who = $('#dailylog-tag-list-table tr[role="row"]:nth-child(' + hoverRow + ') .who_cell_span').html();
							$('#dailylog-tag-list-table tr[role="row"]:nth-child(' + hoverRow + ') .who_cell_span').prepend('<a href="javascript:void(0)" class="open-client-modal-button"><i class="mdi mdi-pencil"></i></a>');
							$('#dailylog-tag-list-table tr[role="row"]:nth-child(' + hoverRow + ') .who_cell_span .open-client-modal-button').off('click').on('click', function() {

								showTableLoader();

								$.ajax(
									{
										url: "/content/list",
										type: "post",
										data: {
											"search_content_type_id" : getContentTypeIdOfClientInfo(),
											"search_content_ad_who" : who
										}
									}
								).success(function(resp) {
									$('#existing_client_table').empty();

									var hasClients = false;
									$.each(resp.data, function (key, client) {
										hasClients = true;
										$('#existing_client_table').append('<tr onclick="openClientInfo(' + client.id + ')" style="cursor:pointer;">' +
											'<td>' + client.client_name + '</td>' +
											'<td>' + client.trading_name + '</td></tr>');
									});
									if(!hasClients) {
										$('#existing_client_table').append('<tr><td>No Existing Clients</td><td></td></tr>');
									}

									$('#existing_client_table').append(
										'<form id="new_client" method="post" action="/content/clientInfo" target="newClientWindow">' +
										'<input type="hidden" name="who" value="'+who.replace('"', '')+'" /></form>'); //the who.replace is just in case the who contains a '"'

									$('.create-client-button').off('click').on('click', function() {
										window.open('', 'newClientWindow');
										$('#new_client').submit();
									});

									$('#client_close_button').off('click').on('click', function() {
										$.ajax(
											{
												url: '/content/material/updateAd',
												type: 'post',
												data: {
													'name' : 'who',
													'value' : that.previewFormObj.previewData.who,
													'pk' : contentData.id,
													'check_client_details' : true
												}
											}
										).success(function(resp) {
											if(typeof contentFormObj === 'undefined') {
												return;
											}
											contentFormObj.previewFormObj.renderPreviewInfo('preview', tagID);
											$('#clientModal').modal('hide');
										}).fail(function(resp) {
											console.log(resp);
										});
									});

									$('#clientModal').modal();
								}).always(function() {
									hideTableLoader();
								});
							})
						}
					}, function() {
						$('.open-client-modal-button').remove();
					});

					hideTableLoader();
				});
			}
  	}
  );
  
  $('#dailylog-tag-list-table').on('draw.dt', function() {
  	that.onDrawPreviewTagListTable();
  });

  this.dailyLogTagListTableObj = $('#dailylog-tag-list-table').DataTable();
  
  var adjustPreviewTagListTableHeight = function() {
		 if (that.dailyLogTagListTable == null) return;
		 
		 var oSettings = that.dailyLogTagListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.dailyLogTagListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
 };

 $(window).resize(function () {
	 adjustPreviewTagListTableHeight();
 });  
 
 adjustPreviewTagListTableHeight();
 
 
 $('#dailylog-tag-list-table').on('draw.dt', function() {
		
		$('#dailylog-tag-list-table tbody tr').off('click').on('click', function(){

			var tagID = findTagIDOfRow($(this));
			
			if (tagID == null) return;
			
			that.previewTagRowClicked(tagID);
			
		});
		
		$('#dailylog-tag-list-table tbody tr td i.add-content-btn').off('click').on('click', function(e){
			var event =  e || window.event;
			if (event.stopPropagation) {
				event.stopPropagation();
			} else {
				event.cancelBubble = true;	
			}
			
			var tagID = findTagIDOfRow($(this).parent().parent());
			
			if (tagID == null) return;
			
			that.addMaterialInstructionFromAd(tagID);
			
		});
		
	});
 
 
  // daily log filtering
 
  	var categoryList = [ContentTypeIDOfAd, ContentTypeIDOfPromotion, ContentTypeIDOfTalk, ContentTypeIDOfNews, ContentTypeIDOfMusic, 100, 0];
	
    for (var index in categoryList) {
    	
		var category = categoryList[index];
		
		$('#preview_analytics_' + category + ' .content-type').off('click').on('click', function(){
			
			var selectedCategory = $(this).parent().data('content-type');
			
			that.search_dailylog_content_type = selectedCategory;
			that.search_dailylog_only_missing = 0;
			
			$('.preview_analytics_info').removeClass('selected');
			$('#preview_analytics_' + that.search_dailylog_content_type).addClass('selected');
			
			that.loadSearchResult();

		});
		
		$('#preview_analytics_' + category + ' .tag-count').off('click').on('click', function(){
			
			var selectedCategory = $(this).parent().data('content-type');
			
			that.search_dailylog_content_type = selectedCategory;
			that.search_dailylog_only_missing = 0;
			
			$('.preview_analytics_info').removeClass('selected');
			$('#preview_analytics_' + that.search_dailylog_content_type).addClass('selected');

			that.loadSearchResult();
		});

		$('#preview_analytics_' + category + ' .content-type-status').off('click').on('click', function(){
			
			var selectedCategory = $(this).parent().data('content-type');

			var hasException = $(this).children().hasClass('error-red');

			that.search_dailylog_content_type = selectedCategory;
			that.search_dailylog_only_missing = hasException ? 1 : 0;
			
			$('.preview_analytics_info').removeClass('selected');
			$('#preview_analytics_' + that.search_dailylog_content_type).addClass('selected');


			that.loadSearchResult();
		});
		
		$('#preview_analytics_' + category + ' .tag-missing-count').off('click').on('click', function(){
			
			var selectedCategory = $(this).parent().data('content-type');
			
			that.search_dailylog_content_type = selectedCategory;
			that.search_dailylog_only_missing = 1;
			
			$('.preview_analytics_info').removeClass('selected');
			$('#preview_analytics_' + that.search_dailylog_content_type).addClass('selected');

			that.loadSearchResult();
			
		});

		$('#preview_analytics_' + category + ' .tag-missing-count-unique').off('click').on('click', function(){

			var selectedCategory = $(this).parent().data('content-type');

			that.search_dailylog_content_type = selectedCategory;
			that.search_dailylog_only_missing = 1;

			$('.preview_analytics_info').removeClass('selected');
			$('#preview_analytics_' + that.search_dailylog_content_type).addClass('selected');

			that.loadSearchResult();

		});
	}
    
    
    // Client List Table
    this.ClientsListTable = $('#clients-list-table').dataTable(
			{
				paging: false,
				searching: false,
				ordering: false,
				info: false,
				columns :[
				            {"data" : "client_name_html", "width" : "20%"},
				            {"data" : "trading_name_html", "width" : "20%"},
							{"data" : "product_name_html", "width" : "20%"},
							{"data" : "client_executive_html", "width" : "20%"},
				            {"className" : 'dt-body-center', "data" : "text_enabled_html", "width" : "1px"},
				            {"className" : 'dt-body-center', "data" : "logo_enabled_html", "width" : "1px"},
				            {"className" : 'dt-body-center', "data" : "image_enabled_html", "width" : "1px"},
				            {"className" : 'dt-body-center', "data" : "is_ready_html", "width" : "1px"},
				],
				sScrollY: $('#content-bottom-full-wrapper').height() - 52,
				ajax: function(data, callback, settings) {
				
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/list",
								type: "post",
								data: {
									"search_content_type_id" : that.search_content_type_id,
									"search_content_client" : that.search_content_client,
									"search_content_product" : that.search_content_product,
									"search_manager_user_id" : that.search_manager_user_id,
									"search_agency_id" : that.search_agency_id
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.data){
										var clients = new Array();
										for (var index in resp.data) {
											var item = resp.data[index];
											var newClient = new ConnectClientModel;
											newClient.loadDataFromJson(item);
											newClient.generateHTMLContentForRow();
											clients.push(newClient);
										}
										callback({aaData: clients});
									}
									else
										callback({aaData: new Array()});
								}
							}
						).fail ( function () {
							
							callback({aaData: new Array()});
							
						}).always( function () {
							
							hideTableLoader();
						});
				}
			}
	);
	
	
	$('#clients-list-table').on('draw.dt', function() {
		
		$('#clients-list-table tbody tr').off('click').on('click', function(){

			var contentID = findClientIDOfRow($(this));
			
			if (contentID == null) return;

			that.clientRowClicked(contentID);
			//window.open('/content/clientInfo/'+contentID);
			/*that.loadContentDetails(contentID, function(){
				that._saveOtherSearchNavigation();
			}, ContentTypeIDOfClientInfo);*/
			
			//document.location = '/content/clientInfo/' + contentID;
			
		});
		
	});
	
	var adjustClientsListTableHeight = function() {
		 if (that.ClientsListTable == null) return;
		 
		 var oSettings = that.ClientsListTable.fnSettings();
		 var proposedHeight = $('#content-bottom-full-wrapper').height() - 52;
		 oSettings.oScroll.sY = proposedHeight;
		 that.ClientsListTable.fnDraw();
		 
		 $('#content-bottom-full-wrapper div.dataTables_scrollBody').css({'height' : proposedHeight + 'px'});
    };
	
    $(window).resize(function () {
    	adjustClientsListTableHeight();
	});  
    
    adjustClientsListTableHeight();
   
}

function openClientInfo(clientID, who) {
	if(clientID != 0) {
		window.open('/content/clientInfo/' + clientID);
	} else {
		window.open('/content/clientInfo/0/'+who);
	}
}

ContentForm.prototype.addMaterialInstructionFromAd = function(tagID) {
	
	var that = this;
	
	bootbox.confirm("Are you sure you want to create new content for this tag?", function(result){
		
		if (result) {
		
			showLoading();
			
			$.ajax ( 
				{
					url: "/content/createAdFromPreviewTag",
					type: "post",
					data: {
						"tag_id" : tagID
					},
					dataType: "json",
					success: function( resp ) {
						if (resp.code == 0 && resp.data) {
							
							that.loadContentDetails(resp.data.contentID, function(){
								
								that._saveDailyLogSearchNavigation();
								
							});
							
						} else {
							$('.saveProgress').show().html('Creation Failed. ' + resp.msg).css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
							
							hideLoading();
						}
					}
				}
			).fail ( function () {

				$('.saveProgress').show().html('Creation Failed. Network Error').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				
				hideLoading();
				
			}).always( function () {
				
				
			});
	
		}
	});
	
}

ContentForm.prototype.clientRowClicked = function(clientID) {

	var that = this;

	if (clientID == this.prevTagclientID) {
		$('#mobilepreview_sidebar').addClass('hidden');
		this.prevTagclientID = null;
		return;
	}

	this.prevTagclientID = clientID;

	$('#mobilepreview_sidebar').removeClass('hidden');
	fillClientInfo(clientID, function (resp) {

		var formActionElement = $('.mobilepreview_action_buttons_container .preview-form-button').first();//$('.bottom-nav-shape .preview-form-button').first();

		if (!formActionElement) return;

		formActionElement.off('click');
		formActionElement.on('click', function () {
			document.location = '/content/clientInfo/' + clientID;
		});
	})


}

ContentForm.prototype.previewTagRowClicked = function(tagID) {

	var that = this;
	
	if (tagID == this.prevTagTagID) {
		$('#mobilepreview_sidebar').addClass('hidden');
		this.prevTagTagID = null;
		return;
	}
	
	this.prevTagTagID = tagID;
	
	$('#mobilepreview_sidebar').removeClass('hidden');
	
	this.previewFormObj.renderPreviewInfo('preview', tagID, function(data) {
    
		console.log(contentData);
    
		var formActionElement = $('.mobilepreview_action_buttons_container .preview-form-button').first();//$('.bottom-nav-shape .preview-form-button').first();
	
		if (!formActionElement) return;
	
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
					that.addMaterialInstructionFromAd(data.id);
				});
	
			} else if ((data.content_type_id == ContentTypeIDOfAd || data.content_type_id == ContentTypeIDOfPromotion) && data.hasConnectData) {
				formActionElement.html('<i class="mdi mdi-information-outline"></i>');
				formActionElement.show();
    
				contentID = data.connectContentId;
				//updateTradingNameEditable();
	
				formActionElement.on('click', function(){
					that.loadContentDetails(parseAsInt(data.finalContentID), function(){
	
						that._saveDailyLogSearchNavigation();
	
					});
				});
	
			} else {
				formActionElement.hide();
			}
		}
	
	});
	
}

ContentForm.prototype.loadContentDetails = function(contentID, onLoadComplete, contentType) {
	
	var that = this;
	
	showLoading();
	
	$.ajax ( 
		{
			url: "/content/show/" + contentID + (contentType ? '?content_type=' + contentType : ''),
			type: "get",
			dataType: "json",
			success: function( resp ) {
				if (resp.code == 0) {
					
					if (onLoadComplete) {
						onLoadComplete();
					}
					
					that.populateContentDetails(resp.data);
					
				} else {
					$('.saveProgress').show().html('Error. ' + resp.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Network Error. Unable to load content details').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		
	}).always( function () {
		
		hideLoading();
	});
	
}


ContentForm.prototype.populateContentDetails = function(data) {

	this.content_id = parseAsInt(data.id);
	
	this.content_type_id = parseAsInt(data.content_type_id);
	this.ad_key = parseAsString(data.ad_key);
	
	this.content_subtype_id = parseAsInt(data.content_subtype_id);
	this.content_rec_type = parseAsString(data.content_rec_type);
	this.ad_length = parseAsInt(data.ad_length);
	this.content_client = parseAsString(data.content_client_name);
	this.content_product = parseAsString(data.content_product_name);
	this.content_line_number = parseAsString(data.content_line_number);
	this.content_contact = parseAsString(data.content_contact);
	this.content_email = parseAsString(data.content_email);
	this.content_phone = parseAsString(data.content_phone);
	this.content_instructions = parseAsString(data.content_instructions);
	this.content_voices = parseAsString(data.content_voices);
	this.content_agency_id = parseAsInt(data.content_agency_id);
	this.description = parseAsString(data.description);
	this.content_manager_user_id = parseAsInt(data.content_manager_user_id);
	
	this.atb_date = parseAsString(data.atb_date);
	this.start_date = parseAsString(data.start_date);
	this.end_date = parseAsString(data.end_date);
	
	this.map_address1 = parseAsString(data.map_address1);
	
	if (this.map_address1 != '') this.map_included = true;
	else this.map_included = false;
	
	this.who = parseAsString(data.who);
	this.what = parseAsString(data.what);
	this.more = parseAsString(data.more);
	
	this.action_id = parseAsInt(data.action_id);
	
	this.action_param_phone_number = parseAsString(data.action_params.phone);
	this.action_param_website = parseAsString(data.action_params.website);
	
	this.content_version = parseAsString(data.content_version);
	this.content_creation_timestamp = parseAsInt(data.content_creation_timestamp);
	this.content_session_name = parseAsString(data.session_name);
	
	this.start_time = parseAsString(data.start_time);
	this.end_time = parseAsString(data.end_time);
	
	this.contentWeekDays = new Array();
	
	for (var i = 0; i < 7; i++) { this.contentWeekDays[i] = false; }
	if (data.content_weekdays) {
		for (var index in data.content_weekdays) {
			this.contentWeekDays[index] = parseAsBool(data.content_weekdays[index]);
		}
	}
	
	this.text_enabled = parseAsBool(data.text_enabled);
	this.audio_enabled = parseAsBool(data.audio_enabled);
	this.image_enabled = parseAsBool(data.image_enabled);
	this.action_enabled = parseAsBool(data.action_enabled);
	this.is_ready = parseAsBool(data.is_ready);
	this.is_competition = parseAsBool(data.is_competition);
	
	this.attachments = new Array();
	
	var imageCount = 0;
	
	if (data.attachments != undefined && data.attachments != null) {
		
		for (var index in data.attachments) {
			var attachmentInfo = data.attachments[index];
			
			if (attachmentInfo.type == 'image' || attachmentInfo.type == 'video') {
				this.attachments[imageCount] = attachmentInfo;
				imageCount++;
			} else if (attachmentInfo.type == 'logo') {
				this.attachments[3] = attachmentInfo;
			} else if (attachmentInfo.type == 'audio') {
				this.attachments[4] = attachmentInfo;
			}
		}
	}
	
	
	if (this.content_type_id == ContentTypeIDOfMaterialInstruction) {
		this.subContents = new Array();
		
		if (data.subContents) {
			for (var index in data.subContents) {
				var subContent = data.subContents[index];
				
				var newSubContent = new ContentModel(subContent.id);
				newSubContent.ad_rec_type = parseAsString(subContent.content_rec_type);
				newSubContent.start = parseAsString(subContent.start_date);
				newSubContent.end = parseAsString(subContent.end_date);
				newSubContent.instructions = parseAsString(subContent.content_instructions);
				newSubContent.what = parseAsString(subContent.what);
				newSubContent.key = parseAsString(subContent.ad_key);
				newSubContent.duration = parseAsInt(subContent.ad_length);
				newSubContent.percent = parseAsInt(subContent.content_percent);
				newSubContent.audio_enabled = parseAsInt(subContent.audio_enabled);
				newSubContent.text_enabled = parseAsInt(subContent.text_enabled);
				newSubContent.is_ready = parseAsInt(subContent.is_ready);
				newSubContent.content_sync = parseAsInt(subContent.content_sync);
				newSubContent.child_content_date_id = parseAsInt(subContent.child_content_date_id);
				
				this.subContents.push(newSubContent);
				 
				this.refreshMaterialInstructionTable();
			}
		}
		
	} else {
		this.subContents = new Array();
	}
	
	
	this.contentDates = new Array();
	
	if (data.content_dates != undefined && data.content_dates != null) {	
		for (var index in data.content_dates) {
			var contentDate = data.content_dates[index];
			this.contentDates.push(contentDate);
		}
	}

	if (this.contentDates.length < DEFAULT_CONTENT_DATES_COUNT) {
		var missingCount = DEFAULT_CONTENT_DATES_COUNT - this.contentDates.length;
		for (var i = 0; i < missingCount; i++) {
			this.contentDates.push({'start_date': '', 'end_date': '', 'date_id': 0});
		}
	}
	
	this.tag_id_list = parseAsString(data.tag_id_list);
	
	this.setFormToEditMode();
	this.renderDataToForm();
	
	this.onContentTypeSelected();
	
	if (this.content_type_id == ContentTypeIDOfAd) {
		//$('#content-list-table_wrapper').show();
	}
}

ContentForm.prototype.onDrawMaterialAdTable = function() {

	var that = this;
	
	$('.material_ad_rec_type').editable(
		{
			type: 'select',
			source: Ad_Rec_Type_List,
			url: materialAdInlineChangeURL,
			showbuttons: false,
			params: function(params){
				params.child_content_date_id = $(this).data('dateid');
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					that.updateSubContentValue(response.data.pk, response.data.date_id, 'ad_rec_type', newValue);
					if (newValue == 'live') {
						$(this).closest('tr').find('td:nth-child(11)').find('span').removeClass('disabled').addClass('enabled');
					}
				} else {
					return response.msg;
				}
			}
		}
	);
	
	$('.material_ad_start_date').editable(
			{
				type: 'date',
				mode: 'popup',
				format: 'dd-mm-yyyy',
				url: materialAdInlineChangeURL,
				showbuttons: false,
				params: function(params){
					params.child_content_date_id = $(this).data('dateid');
					params.parent_content_id = that.content_id;
					return params;
				},
				success: function(response, newValue) {
					if (response.code == 0) {
						
						var originalDateId = $(this).data('dateid');
						
						that.updateSubContentValue(response.data.pk, originalDateId, 'start', moment(newValue).format('DD-MM-YYYY'));
						that.updateSubContentValue(response.data.pk, originalDateId, 'child_content_date_id', response.data.date_id);
						
						that.updateSubContentRowDateID($(this).closest('td').parent(), response.data.date_id);
						
						skipConfirm = true;
						$(this).closest('td').next().find('.material_ad_end_date').editable('show');
						
						
					} else {
						return response.msg;
					}
				},
				display: function(value, response) {
					if (value == null) {
						$(this).html('Empty');
					} else {
						$(this).html(moment(new Date(value)).format('DD-MMM'));
					}
				}
			}
	);
	
	var updateAdRowDateCallback = function() {
		var dateId = $(this).data('dateid');
		var contentID = $(this).data('pk');
		
		if (dateId != '' && dateId != 0 && !skipConfirm) {
			
			bootbox.dialog({
				message: "Do you want to modify this date range or add a new date range?",
				title: "Confirmation",
				buttons: {
					success: {
						label: "Modify date range",
						className: "btn-success",
						callback: function() {
							
						}
					},
					main: {
						label: "Add another date range",
						className: "btn-primary",
						callback: function() {
							
							showLoading();
							
							$.ajax ( 
								{
									url: "/content/material/showAdDetails/" + contentID,
									type: "get",
									dataType: "json",
									success: function( resp ) {
										if (resp.code == 0) {
											that.addNewSubContentFromJSON(resp.data, true);
										} else {

											$('.saveProgress').show().html('Error. ' + resp.msg).css('color', 'red');
											setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
										}
									}
								}
							).fail ( function () {


								$('.saveProgress').show().html('Network Error. Unable to load content details').css('color', 'red');
								setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
								
							}).always( function () {
								
								hideLoading();
							});
							
						}
					}
				}
			});
			
		}
		
		skipConfirm = false;
	};
	
	$('.material_ad_start_date').each(function() {
		$(this).off('shown').on('shown', updateAdRowDateCallback);
	});
	
	$('.material_ad_end_date').editable(
			{
				type: 'date',
				mode: 'popup',
				format: 'dd-mm-yyyy',
				url: materialAdInlineChangeURL,
				showbuttons: false,
				params: function(params){
					params.child_content_date_id = $(this).data('dateid');
					params.parent_content_id = that.content_id;
					return params;
				},
				success: function(response, newValue) {
					if (response.code == 0) {
						that.updateSubContentValue(response.data.pk, $(this).data('dateid'), 'end', moment(newValue).format('DD-MM-YYYY'));
						that.updateSubContentValue(response.data.pk, $(this).data('dateid'), 'child_content_date_id', response.data.date_id);
						
						that.updateSubContentRowDateID($(this).closest('td').parent(), response.data.date_id);
						
						/*$(this).data('dateid', response.data.date_id);
						$(this).closest('td').prev().find('.material_ad_start_date').data('dateid', response.data.date_id);*/
						
					} else {
						return response.msg;
					}
				},
				display: function(value, response) {
					if (value == null) {
						$(this).html('Empty');
					} else {
						$(this).html(moment(new Date(value)).format('DD-MMM'));
					}
				}
			}
	);
	
	$('.material_ad_end_date').each(function() {
		$(this).off('shown').on('shown', updateAdRowDateCallback);
	});
	
	$('.material_ad_instructions').editable(
			{
				type: 'text',
				url: materialAdInlineChangeURL,
				showbuttons: false,
				params: function(params){
					params.child_content_date_id = $(this).data('dateid');
					return params;
				},
				success: function(response, newValue) {
					if (response.code == 0) {
						that.updateSubContentValue(response.data.pk, response.data.date_id, 'instructions', newValue);
					} else {
						return response.msg;
					}
				}
			}
	);
	
	$('.material_ad_what').editable(
			{
				type: 'text',
				url: materialAdInlineChangeURL,
				showbuttons: false,
				params: function(params){
					params.child_content_date_id = $(this).data('dateid');
					return params;
				},
				success: function(response, newValue) {
					if (response.code == 0) {
						that.updateSubContentValue(response.data.pk, response.data.date_id, 'what', newValue);
					} else {
						return response.msg;
					}
				}
			}
	);
	
	$('.material_ad_key').editable(
			{
				type: 'text',
				url: materialAdInlineChangeURL,
				showbuttons: false,
				params: function(params){
					params.child_content_date_id = $(this).data('dateid');
					params.overwrite_existing = overwriteExistingMIAd ? '1' : '0';
					return params;
				},
				success: function(response, newValue) {

					if (response.code == 0) {

						that.updateSubContentValue(response.data.pk, response.data.date_id, 'key', newValue);

					} else if (response.code == 100 && response.data.existing_id) { // duplicate key
						var childContentDateId = response.data.date_id;
						bootbox.confirm("Ad content with such key is already existing. Do you want to use the existing Ad for this Material Instruction?", function(result){
							
							if (result) {
								
								showLoading();
								
								var subContentItem = that.getSubContentObj(response.data.pk, response.data.date_id);
								
								if (subContentItem) {
								
									$.ajax ( 
										{
											url: "/content/material/showAdDetails/" + response.data.existing_id + "?start_date=" + subContentItem.start + "&end_date=" + subContentItem.end,
											type: "get",
											dataType: "json",
											success: function( resp ) {
												if (resp.code == 0) {
													
													//that.updateSubContentData(response.data.pk, childContentDateId, resp.data, false);
													that.updateSubContentData(response.data.pk, childContentDateId, resp.data, true);
													
												} else {

													$('.saveProgress').show().html('Error. ' + resp.msg).css('color', 'red');
													setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
												}
											}
										}
									).fail ( function () {


										$('.saveProgress').show().html('Network Error. Unable to load content details').css('color', 'red');
										setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
										
									}).always( function () {
										
										hideLoading();
									});
								
								} else {
									hideLoading();
								}
								
							}
							
						});
						
						return response.msg;
						
					} else if (response.code == 200 && response.data) {
						
						that.updateSubContentData(response.data.pk, response.data.date_id, response.data.newObj, true);
						//that.addNewSubContentFromJSON(response.data, false);
						
					} else {
						return response.msg;
					}
				}
			}
	);
	
	$('.material_ad_key').each(function(){
		$(this).off('shown').on('shown', function(){
			
			var val = $(this).editable('getValue', true);
			
			if (val != undefined && val != '') {
				bootbox.dialog({
					message: "Do you want to change the existing ad key number or create a new ad?",
					title: "Confirmation",
					buttons: {
						success: {
							label: "Change existing ad key number",
							className: "btn-success",
							callback: function() {
								overwriteExistingMIAd = true;
							}
						},
						main: {
							label: "New ad",
							className: "btn-primary",
							callback: function() {
								overwriteExistingMIAd = false; 
							}
						}
					}
				});
			} else {
				overwriteExistingMIAd = true;
			}
		});
	});
	
	
	$('.material_ad_duration').editable(
		{
			type: 'select',
			source: durationValuesList,
			url: materialAdInlineChangeURL,
			showbuttons: false,
			params: function(params){
				params.child_content_date_id = $(this).data('dateid');
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					that.updateSubContentValue(response.data.pk, response.data.date_id, 'duration', parseInt(newValue));
				} else {
					return response.msg;
				}
			}
		}
	);
	
	$('.material_ad_percent').editable(
		{
			type: 'select',
			source: adPercentValuesList,
			url: materialAdInlineChangeURL,
			showbuttons: false,
			params: function(params){
				params.child_content_date_id = $(this).data('dateid');
				return params;
			},
			success: function(response, newValue) {
				if (response.code == 0) {
					that.updateSubContentValue(response.data.pk, response.data.date_id, 'percent', parseInt(newValue));
				} else {
					return response.msg;
				}
			}
		}
	);
	
}

ContentForm.prototype.getSubContentObj = function(id, contentDateId) {
	for (var index in this.subContents) {
		var subContent = this.subContents[index];
		
		if (subContent.id == id && subContent.child_content_date_id == contentDateId) {
			return this.subContents[index];
		}
	}
	return null;
}

ContentForm.prototype.updateSubContentValue = function(id, date_id,  key, val) {

	for (var index in this.subContents) {
		var subContent = this.subContents[index];
		
		if (subContent.id == id && subContent.child_content_date_id == date_id) {
			subContent[key] = val;
			break;
		}
	}
	
	$(window).resize();
}


ContentForm.prototype.updateSubContentRow = function(id, date_id, row) {
	
	for (var index in this.subContents) {
		var subContent = this.subContents[index];
		
		if (subContent.id == id && subContent.child_content_date_id == date_id) {
			this.subContents[index].generateHTMLContents();
			this.MaterialInstructionAdListTableObj.row(row).data(this.subContents[index]).draw();
			break;
		}
	}
}

ContentForm.prototype.updateSubContentRowDateID = function(row, date_id) {
	
	row.find('.material_ad_rec_type').first().data('dateid', date_id);
	row.find('.material_ad_start_date').first().data('dateid', date_id);
	row.find('.material_ad_end_date').first().data('dateid', date_id);
	row.find('.material_ad_instructions').first().data('dateid', date_id);
	row.find('.material_ad_what').first().data('dateid', date_id);
	row.find('.material_ad_key').first().data('dateid', date_id);
	row.find('.material_sync_icon').first().data('dateid', date_id);
	row.find('.material_ad_duration').first().data('dateid', date_id);
	row.find('.material_ad_percent').first().data('dateid', date_id);
	row.find('.material_ad_action_remove').first().data('dateid', date_id);
	row.find('.material_ad_action_detail').first().data('dateid', date_id);
}

ContentForm.prototype.updateSubContentData = function(id, date_id, data, overwriteDate) {
	
	for (var index in this.subContents) {
		var subContent = this.subContents[index];
		
		if (subContent.id == id && subContent.child_content_date_id == date_id) {
			subContent.id = parseAsInt(data.id);
			subContent.ad_rec_type = parseAsString(data.content_rec_type);
			subContent.instructions = parseAsString(data.content_instructions);
			subContent.what = parseAsString(data.what);
			subContent.key = parseAsString(data.ad_key);
			subContent.duration = parseAsInt(data.ad_length);
			subContent.percent = parseAsInt(data.content_percent);
			subContent.audio_enabled = parseAsInt(data.audio_enabled);
			subContent.text_enabled = parseAsInt(data.text_enabled);
			subContent.is_ready = parseAsInt(data.is_ready);
			subContent.content_sync = parseAsInt(data.content_sync);
			
			if (overwriteDate) {
				subContent.start = parseAsString(data.start_date);
				subContent.end = parseAsString(data.end_date);
				subContent.child_content_date_id = parseAsInt(data.child_content_date_id);
			} else {
				subContent.child_content_date_id = 0;
			}
			
			break;
		}
	}
	
	this.refreshMaterialInstructionTable();
	
}

ContentForm.prototype.addNewSubContentFromJSON = function (data, create_new) {
	
	var newSubContent = new ContentModel(data.id);
	newSubContent.ad_rec_type = parseAsString(data.content_rec_type);
	newSubContent.start = parseAsString(data.start_date);
	newSubContent.end = parseAsString(data.end_date);
	newSubContent.instructions = parseAsString(data.content_instructions);
	newSubContent.what = parseAsString(data.what);
	newSubContent.key = parseAsString(data.ad_key);
	newSubContent.duration = parseAsInt(data.ad_length);
	newSubContent.percent = parseAsInt(data.content_percent);
	newSubContent.audio_enabled = parseAsInt(data.audio_enabled);
	newSubContent.text_enabled = parseAsInt(data.text_enabled);
	newSubContent.is_ready = parseAsInt(data.is_ready);
	newSubContent.content_sync = parseAsInt(data.content_sync);
	newSubContent.child_content_date_id = parseAsInt(data.child_content_date_id);
	
	if (create_new) {
		newSubContent.child_content_date_id = 0;
		newSubContent.start = '';
		newSubContent.end = '';
	}
	
	this.subContents.push(newSubContent);
	this.refreshMaterialInstructionTable();
}

ContentForm.prototype.saveForm = function(ignoreConfirm) {
	
	var that = this;
	
	this.updateDataFromForm();
	
	if (!this.validateForm()) {
		return;
	}
	
	var confirmMsg = "Are you sure to save the current form data?";
	
	if (this.content_id && this.content_type_id == ContentTypeIDOfMaterialInstruction) {
		confirmMsg = "Are you sure to save the current form data? <br/>Warning: Any changes made to the material instruction will overwrite any unique items in the Ads below.";
	}
	
	if (ignoreConfirm) {
		that._saveProcess();
	} else {
		bootbox.confirm(confirmMsg, function(result){
			
			if (result) {
				
				that._saveProcess();
				
			}
			
		});
	}
}

ContentForm.prototype._saveProcess = function() {
	
	var that = this;
	
	showLoading();
	
	$.ajax ( 
		{
			url: "/content/save",
			type: "post",
			dataType: "json",
			data: {
				"content_id" : that.content_id,
				"content_type_id" : that.content_type_id,
				"content_subtype_id" : that.content_subtype_id,
				"content_rec_type" : that.content_rec_type,
				"ad_length" : that.ad_length,
				"content_client" : that.content_client,
				"content_product" : that.content_product,
				"content_line_number" : that.content_line_number,
				"content_contact" : that.content_contact,
				"content_email" : that.content_email,
				"content_phone" : that.content_phone,
				"content_instructions" : that.content_instructions,
				"content_voices" : that.content_voices,
				"content_agency" : that.content_agency_id,
				"description" : that.description,
				"content_manager_user_id" : that.content_manager_user_id,
				"atb_date" : that.atb_date,
				"ad_key" : that.ad_key,
				"start_date" : that.start_date,
				"end_date" : that.end_date,
				"map_included" : that.map_included,
				"map_address1" : that.map_address1,
				"map_address2" : that.map_address2,
				"who" : that.who,
				"what" : that.what,
				"more" : that.more,
				"action_id" : that.action_id,
				"action_param" : that.action_param, 
				"text_enabled" : that.text_enabled,
				"audio_enabled" : that.audio_enabled,
				"image_enabled" : that.image_enabled,
				"action_enabled" : that.action_enabled,
				"is_ready" : that.is_ready,
				"attachments" : that.attachments,
				"subContents" : that.getSubContentsIdList(),
				"subContentsSync" : that.getSubContentSyncList(),
				"subContentsDate" : that.getSubContentDateIdList(),
				"contentDates" : that.getContentDatesFields(),
				"content_version" : that.content_version,
				"content_session_name" : that.content_session_name,
				"start_time" : that.start_time,
				"end_time" : that.end_time,
				"content_weekdays" : that.contentWeekDays,
				"is_competition" : that.is_competition,
				"content_association" : that.contentAssociation
			},
			success: function( resp ) {
				if (resp.code === 0) {
					// update auto complete for company and product
					updateAutoCompleteCompanyAndProductList();

					$('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
					
					if (that.nextAction == 'gotoEdit' && that.nextParam != null) {
						
						that.loadContentDetails(that.nextParam, function(){
							that._saveMaterialInstructionNavigation(that.content_id);
						});
						
						that.nextAction = '';
						that.nextParam = null;
						
					} else {
					
						if (that.content_type_id == ContentTypeIDOfMaterialInstruction) {
							that.loadContentDetails(resp.data.content_id);
						} else if (that.content_type_id == ContentTypeIDOfClientInfo) {
							that.loadContentDetails(resp.data.content_id, null, ContentTypeIDOfClientInfo);
						} else {
							/*that.content_id = resp.data.content_id;
							that.tag_id_list = resp.data.tag_id_list;
							that.renderTagIdList();
							that.setFormToEditMode();*/
							that.loadContentDetails(resp.data.content_id);
						}
					
					}
					
				} else if (resp.code === 100) {			// Duplicate AdKey
					
					var existingContentID = resp.data.contentID;
					
					bootbox.dialog({
						message: "The key number you entered already exists in another entry.<br/>Would you like to Edit this key number, open the existing Ad or Replace the existing Ad with this new one?",
						title: "Duplicate Key",
						buttons: {
							success: {
								label: "Edit Key Number",
								className: "btn-success",
								callback: function() {
									$('#content_ad_key').val('');
									$('#content_ad_key').focus();
								}
							},
							main: {
								label: "Open Existing Ad",
								className: "btn-primary",
								callback: function() {
									
									$('body').css({"padding-right": 0});
									
									bootbox.confirm("The information you have entered in this screen will be deleted.", function(result){
										if (result) {
											that.loadContentDetails(existingContentID);
										}
									});
									
									
								}
							},
							danger: {
								label: "Replace Existing Ad",
								className: "btn-danger",
								callback: function() {
									
									$('body').css({"padding-right": 0});
									
									bootbox.confirm("Click 'OK' to delete the existing Ad with this key number and replace it with the information you are entering into the current screen.", function(result){
										if (result) {
											
											showLoading();
											
											$.ajax ( 
												{
													url: "/content/removeContent",
													type: "post",
													data: {
														"pk" : existingContentID
													},
													dataType: "json",
													success: function( resp ) {
														if (resp.code == 0) {
															that.saveForm(true);																																		
														} else {
															$('.saveProgress').show().html('Removed Failed. ' + resp.msg).css('color', 'red');
															setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
														}
													}
												}
											).fail ( function () {

												$('.saveProgress').show().html('Remove Failed. Network Error').css('color', 'red');
												setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
												
											}).always( function () {
												
												hideLoading();
											});
											
										}
									});
								}
							}
						}
					});
					
					
				} else {
					$('.saveProgress').show().html('Save Error. ' + resp.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Save Error. Network Error').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		
	}).always( function () {
		
		hideLoading();
	});
}

ContentForm.prototype.newForm = function() {
	
	var that = this;
	
	if (this.formMode == 'init' || this.formMode == 'initSearch' || this.formMode == 'search') {
		this.setFormToInitNewMode();
		return;
	}
	
	
	bootbox.confirm("Are you sure to discard the current change and reset this form?", function(result){
		
		if (result) {
			
			that.setFormToNewMode();
			that.onContentTypeSelected();
		}
		
	});
	
}

ContentForm.prototype.copyForm = function() {
	
	var that = this;
	
	bootbox.confirm("Are you sure you want to copy the current content?", function(result){
		
		if (result) {
		
			showLoading();
			
			$.ajax ( 
				{
					url: "/content/copyContent",
					type: "post",
					data: {
						"content_id" : that.content_id
					},
					dataType: "json",
					success: function( resp ) {
						if (resp.code == 0 && resp.data) {
							
							that.loadContentDetails(resp.data.contentID);
							
						} else {

							$('.saveProgress').show().html('Copy Failed. ' + resp.msg).css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
							
							hideLoading();
						}
					}
				}
			).fail ( function () {

				$('.saveProgress').show().html('Copy Failed. Network Error').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				
				hideLoading();
				
			}).always( function () {
				
				
			});
	
		}
		
	});
	
}

ContentForm.prototype.printForm = function() {
	
	if (this.content_type_id == ContentTypeIDOfMaterialInstruction) {
		window.open('/content/print/' + this.content_id, "_self");
	}
	
}

ContentForm.prototype.copyClientToAdUsingMI = function (adId, onComplete) {

	var that = this;

	$.ajax (
		{
			url: "/content/copyClientToAdUsingMI",
			type: "post",
			dataType: "json",
			data: {
				"ad_id": adId,
				"material_id": that.content_id
			}
		}
	).done(function(resp) {
			if(resp.code == 0) {

				that.updateSubContentData(adId, 0, resp.data, true);

			}
			else {

				$('.saveProgress').show().html('Error. Failed to copy').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

				console.log(resp.msg);

			}

		}).fail(function() {

		$('.saveProgress').show().html('Copy Failure').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		});
}

ContentForm.prototype.addSubContent = function() {
	
	var that = this;
	
	showTableLoader();
	
	$.ajax ( 
		{
			url: "/content/material/newAd",
			type: "post",
			dataType: "json",
			success: function( resp ) {
				if (resp.code === 0 && resp.data && resp.data.id) {
					var newObj = new ContentModel(resp.data.id);
					newObj.ad_rec_type = resp.data.ad_rec_type;

					that.copyClientToAdUsingMI(resp.data.id);

					that.addNewSubContent(newObj);
				} else {
					$('.saveProgress').show().html('Creation Error. ' + resp.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Network Error. Unable to create new ad item.').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		
	}).always( function () {
		
		hideTableLoader();
	});
	
}


ContentForm.prototype.searchForm = function() {
	
	var that = this;
	
	if (this.formMode == 'init' || this.formMode == 'initNew') {
		this.setFormToInitSearchMode();
		return;
	}
	
	if (this.formMode == 'new' || this.formMode == 'edit') {
		
		bootbox.confirm("Are you sure to discard the current change in the form and switch to search form?", function(result){
			
			if (result) {
				
				that.setFormToInitSearchMode();
			}
			
		});
	}
	
}



ContentForm.prototype.deleteForm = function() {
	
	var that = this;
	
	if (this.content_id == 0 || this.content_id == null || this.content_id == undefined) return;
	
	bootbox.confirm("Are you sure to remove the current content?", function(result){
		
		if (result) {
			
			showLoading();
			
			$.ajax ( 
				{
					url: "/content/removeContent",
					type: "post",
					data: {
						"pk" : that.content_id,
						"content_type" : that.content_type_id
					},
					dataType: "json",
					success: function( resp ) {
						if (resp.code == 0) {
							
							that.setFormToNewMode();
							
							that.onContentTypeSelected();

							$('.saveProgress').show().html('Success. Content has been removed successfully.').css('color', 'green');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
							
						} else {
							$('.saveProgress').show().html('Remove Failed. ' + resp.msg).css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
						}
					}
				}
			).fail ( function () {

				$('.saveProgress').show().html('Remove Failed. Network error.').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				
			}).always( function () {
				
				hideLoading();
			});
			
		}
		
	});	
}

ContentForm.prototype.previewForm = function() {
	
}

ContentForm.prototype.getCurrentContentTypeSelected = function() {
	var contentType = $('#content_content_type_id').val();
	return contentType;
}

var page;

ContentForm.prototype.getCurrentPage = function() {
	switch(this.getCurrentContentTypeSelected()) {
		case ContentTypeIDOfClientInfo:
			page = 'clientInfo';
			break;
		case ContentTypeIDOfDailyLog:
			page = 'dailyLog';
			break;
		case ContentTypeIDOfMaterialInstruction:
			page = 'materialInstruction';
			break;
		default:
			page = null;
			break;
	}
}

ContentForm.prototype.onContentTypeSelected = function() {
	
	$('#mobilepreview_sidebar').addClass('hidden');
	this.hideTalkAssignSideBar();
	
	if ($('#content_content_type_id').val() == '0') return;
	var contentType = $('#content_content_type_id').val();
	
	if (contentType == ContentTypeIDOfTalkShow) {
		this.openTalkShowSchedulePage();
		return;
	}
	if (contentType == ContentTypeIDOfMusicMix) {
		this.openMusicMixPage();
		return;
	}

	this.getCurrentPage();
	
	if (this.formMode == 'initSearch') {
		this.setFormToSearchMode();
		this.showSearchForm(contentType);
		this.showSearchResultTable(contentType);
		this.loadSearchResult();
	} else if (this.formMode == 'search') {
		this.showSearchForm(contentType);
		this.showSearchResultTable(contentType);
		this.loadSearchResult();
	} else if (this.formMode == 'initNew') {
		this.setFormToNewMode();
		this.showNewOrEditForm(contentType);
	} else if (this.formMode == 'new') {
		this.showNewOrEditForm(contentType);
	} else if (this.formMode == 'edit') {
		this.showNewOrEditForm(contentType);
	}
		
}

ContentForm.prototype.openTalkShowSchedulePage = function() {
	document.location = '/content/scheduler';
}

ContentForm.prototype.openMusicMixPage = function() {
	document.location = '/content/musicMix';
}

ContentForm.prototype.setFormToSearchMode = function() {
	
	this.formMode = 'search';
	this.updateFormLayout();
}

ContentForm.prototype.setFormToInitMode = function() {
	
	this.formMode = 'init';
	this.updateFormLayout();
}

ContentForm.prototype.setFormToInitSearchMode = function() {
	
	this.formMode = 'initSearch';
	$('#content_content_type_id').val('0');
	this.updateFormLayout();
}

ContentForm.prototype.setFormToInitNewMode = function() {
	
	this.formMode = 'initNew';
	$('#content_content_type_id').val('0');
	this.updateFormLayout();
}


ContentForm.prototype.setFormToNewMode = function() {
	
	var contentType = $('#content_content_type_id').val();
	
	this.initFormData();
	this.renderDataToForm();
	
	$('#content_content_type_id').val(contentType);
	
	this.formMode = 'new';
	this.updateFormLayout();
}


ContentForm.prototype.setFormToEditMode = function() {
		
	this.formMode = 'edit';
	
	this.updateFormLayout();
	
	this.onContentTypeSelected();
}

ContentForm.prototype.updateFormLayout = function() {
	
	var backNavigation = this.hasBack();
	
	if (backNavigation) {
		$('#goBackLinkContainer .goBackLink').html('Return to ' + backNavigation.title);
	} else {
		$('#goBackLinkContainer .goBackLink').html('');
	}
	
	$('.content-sub-header .content-sub-header-actions').removeClass('pull-left');
	
	this.hideAllSubHeaderActions();
	
	// Hide all bottom tables
	$('#material-ad-list-table_wrapper').hide();
	$('#material-list-table_wrapper').hide();
	$('#audio-upload-list-table_wrapper').hide();
	$('#clients-list-table_wrapper').hide();
	
	$('#content_content_type_id').show();
	$('#content-sub-header-second-form').hide();
	$('#content-sub-header-version-form').hide();
	$('#content_created_date_info').hide();
	
	$('#mobilepreview_sidebar').addClass('hidden');
	this.hideTalkAssignSideBar()
	
	if (this.formMode == 'init') {
		
		$('#content_title').html('');
		$('#content-sub-header-form').hide();
		
		$('.content-sub-header .content-sub-header-actions').addClass('pull-left');
		
		$('#content_btn_search').show();
		$('#content_btn_new').show();
		
		$('#content_add_form').hide();
		$('#content_client_add_form').hide();
		$('#content_search_form').hide();
		$('#content_audio_upload_form').hide();
		
		$('#content-list-table_wrapper').hide();
		
		$('#dailylog-tag-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$('#content_content_type_id').hide();
				
	} else if (this.formMode == 'initSearch') {
		$('#content_title').html('Search');
		$('#content-sub-header-form').show();
		
		$('#content_btn_new').show();
		
		$('#content_add_form').hide();
		$('#content_client_add_form').hide();
		$('#content_search_form').hide();
		$('#content_audio_upload_form').hide();
		
		$('#content-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		
		$('#dailylog-tag-list-table_wrapper').hide();
		
		addOptionToSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog, "Daily Log");
		
	} else if (this.formMode == 'search') {
		
		$('#content_title').html('Search');
		$('#content_btn_new').show();
		$('#content_add_form').hide();
		$('#content_client_add_form').hide();
		$('#content_audio_upload_form').hide();
		
		$('#content-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		
		addOptionToSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog, "Daily Log");
		
	} else if (this.formMode == 'initNew') {
		$('#content_title').html('New');
		$('#content-sub-header-form').show();
		
		$('#content_search_form').hide();
		
		$('#content_btn_search').show();
		
		$('#content-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		
		removeOptionFromSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog);
		
	} else if (this.formMode == 'new') {
		
		$('#content_title').html('New');
		//this.hideAllSubHeaderActions(true);
		
		$('#content_btn_search').show();
		
		$('#content_btn_new').show();
		$('#content_btn_save').show();
		
		
		$('#content_search_form').hide();
		
		$('#content-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		
		removeOptionFromSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog);
		
	} else if (this.formMode == 'edit') {
		
		$('#content_title').html('Edit' + ' ' + getContentTypeString(this.content_type_id));
		$('#content_content_type_id').hide();
		
		//this.hideAllSubHeaderActions(true);
		
		$('#content_btn_search').show();
		$('#content_btn_new').show();
		$('#content_btn_print').show();
		$('#content_btn_copy').show();
		$('#content_btn_save').show();
		$('#content_btn_remove').show();
		$('#content_btn_preview').show();
		
		$('#content_search_form').hide();
		
		$('#content-list-table_wrapper').hide();
		
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		
		removeOptionFromSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog);
	}
	
}

ContentForm.prototype.updateTalkForm = function(){
	
	var talkSubType = $('#content_content_sub_type_id3').val();
	
	if (talkSubType == ContentSubTypeIDOfTalkShow) { // talk show
		$('#content_session_name_wrapper').show();
		$('#content_talk_time_range_wrapper').show();
		$('#content_talk_weekdays_wrapper').show();
		$('#content_client_wrapper').hide();
		$('#content_talk_date_range_wrapper').show();
		$('#add_form_middle_section_wrapper').show();
		$('#add_form_right_section_wrapper').show();
		$('#content_talk_segment_assign_wrapper').hide();
	} else if (talkSubType == ContentSubTypeIDOfSegment) {  // individual segment
		$('#content_session_name_wrapper').hide();
		$('#content_talk_time_range_wrapper').hide();
		$('#content_talk_weekdays_wrapper').hide();
		$('#content_client_wrapper').show();
		$('#content_talk_date_range_wrapper').show();
		$('#add_form_middle_section_wrapper').show();
		$('#add_form_right_section_wrapper').show();
		$('#content_talk_segment_assign_wrapper').show();
	} else {
		$('#content_session_name_wrapper').hide();
		$('#content_talk_time_range_wrapper').hide();
		$('#content_talk_weekdays_wrapper').hide();
		$('#content_client_wrapper').hide();
		$('#content_talk_date_range_wrapper').hide();
		$('#add_form_middle_section_wrapper').hide();
		$('#add_form_right_section_wrapper').hide();
		$('#content_talk_segment_assign_wrapper').hide();
	}
	
	$(window).resize();
}

ContentForm.prototype.showNewOrEditForm = function(contentType){

		
	//this.initFormData();
	//this.renderDataToForm();
	$('#content-sub-header-version-form').hide();
	
	if (contentType == ContentTypeIDOfAd || contentType == ContentTypeIDOfMaterialInstruction || contentType == ContentTypeIDOfTalk || contentType == ContentTypeIDOfNews) {
		$('#content_add_form').show();
		$('#content_audio_upload_form').hide();
		$('#content_client_add_form').hide();
		
		$('#add_form_middle_section_wrapper').show();
		$('#add_form_right_section_wrapper').show();

		if (contentType == ContentTypeIDOfAd) {
			
			$('#content_add_form').hide();
			document.location = '/content/ad' + ((this.content_id > 0 && this.content_id != undefined && this.content_id != '') ? "/" + this.content_id : '');
			return;
			
			$('#content_manager_user_id2').hide();
			$('#content_manager_user_id').show();
			
			$('#content_sub_type_ad_length_wrapper').show();
			
			$('#content_agency_id').show();
			
			$('#content_ad_key_wrapper').show();
			$('#content_date_range_wrapper').show();
			
			$('#content_ready_to_print_wrapper').hide();
			$('#content_audio_enabled_wrapper').show();
			
			$('#content_product_only_wrapper').show();
			$('#content_product_and_type_wrapper').hide();
			
			$('#attachment_audio').show();
			
			$('#material-list-table_wrapper').hide();
			
			// talk related
			$('#content_sub_type_talk_wrapper').hide();
			$('#content_session_name_wrapper').hide();
			$('#content_notfortalk_elements_wrapper1').show();
			$('#content_talk_date_range_wrapper').hide();
			$('#content_talk_time_range_wrapper').hide();
			$('#content_talk_weekdays_wrapper').hide();
			$('#content_talk_segment_assign_wrapper').hide();
			

			$('#content_client_wrapper').show();
			
		} else if (contentType == ContentTypeIDOfMaterialInstruction) {
			
			$('#content_manager_user_id').hide();
			$('#content_manager_user_id2').show();
			
			$('#content_sub_type_ad_length_wrapper').hide();
			
			$('#content_agency_id').hide();
			
			$('#content_ad_key_wrapper').hide();
			$('#content_date_range_wrapper').hide();
			
			$('#content_ready_to_print_wrapper').show();
			$('#content_audio_enabled_wrapper').hide();
			
			$('#content_product_only_wrapper').hide();
			$('#content_product_and_type_wrapper').show();
			
			$('#attachment_audio').hide();
			
			$('#content-list-table_wrapper').hide();
			
			$('#content-talk-list-table_wrapper').hide();
			$('#content-news-list-table_wrapper').hide();
			$('#content-sub-header-version-form').show();
			
			if (this.formMode == 'edit') {
				$('#content_created_date_info').show();
			} else {
				$('#content_created_date_info').hide();
			}

			// talk related
			$('#content_sub_type_talk_wrapper').hide();
			$('#content_session_name_wrapper').hide();
			$('#content_notfortalk_elements_wrapper1').show();
			$('#content_talk_date_range_wrapper').hide();
			$('#content_talk_time_range_wrapper').hide();
			$('#content_talk_weekdays_wrapper').hide();
			$('#content_talk_segment_assign_wrapper').hide();

			$('#add_form_middle_section_wrapper').hide();
			$('#add_form_right_section_wrapper').hide();
			
			$('#content_client_wrapper').show();
			
		} else if (contentType == ContentTypeIDOfTalk) {
		
			$('#content_manager_user_id2').hide();
			$('#content_manager_user_id').hide();
			
			$('#content_sub_type_ad_length_wrapper').hide();
			
			$('#content_agency_id').hide();
			
			$('#content_ad_key_wrapper').hide();
			$('#content_date_range_wrapper').hide();
			
			$('#content_ready_to_print_wrapper').hide();
			$('#content_audio_enabled_wrapper').hide();
			
			$('#content_product_only_wrapper').hide();
			$('#content_product_and_type_wrapper').hide();
			
			if (isStationPrivate()) {
				$('#attachment_audio').show();
			} else {
				$('#attachment_audio').hide();
			}
			
			$('#material-list-table_wrapper').hide();
			$('#content-news-list-table_wrapper').hide();

			// talk related
			$('#content_sub_type_talk_wrapper').show();
			$('#content_session_name_wrapper').show();
			$('#content_notfortalk_elements_wrapper1').hide();
			$('#content_talk_date_range_wrapper').show();
			$('#content_talk_time_range_wrapper').show();
			$('#content_talk_weekdays_wrapper').show();
			$('#content_talk_segment_assign_wrapper').hide();
			
			this.updateTalkForm();
		} else if (contentType == ContentTypeIDOfNews) {
			
			$('#content_add_form').hide();
			document.location = '/content/news';
			return;
			
			$('#content_manager_user_id2').hide();
			$('#content_manager_user_id').hide();
			
			$('#content_sub_type_ad_length_wrapper').hide();
			
			$('#content_agency_id').hide();
			
			$('#content_ad_key_wrapper').hide();
			$('#content_date_range_wrapper').hide();
			
			$('#content_ready_to_print_wrapper').hide();
			$('#content_audio_enabled_wrapper').hide();
			
			$('#content_product_only_wrapper').hide();
			$('#content_product_and_type_wrapper').hide();
			
			if (isStationPrivate()) {
				$('#attachment_audio').show();
			} else {
				$('#attachment_audio').hide();
			}
			
			$('#material-list-table_wrapper').hide();
			$('#content-news-list-table_wrapper').hide();

			// talk related
			$('#content_sub_type_talk_wrapper').hide();
			$('#content_session_name_wrapper').hide();
			$('#content_notfortalk_elements_wrapper1').hide();
			$('#content_talk_date_range_wrapper').show();
			$('#content_talk_time_range_wrapper').show();
			$('#content_talk_weekdays_wrapper').show();
			$('#content_talk_segment_assign_wrapper').hide();
			$('#content_client_wrapper').hide();

			//this.updateTalkForm();
		}
		
		if (this.formMode == 'edit' && isStationPrivate()) {
			$('#content_create_demo_tag_wrapper').show();
		} else {
			$('#content_create_demo_tag_wrapper').hide();
		}
		
	} else if (contentType == ContentTypeIDOfAudio){
		$('#content_add_form').hide();
		$('#content_client_add_form').hide();
		$('#content_audio_upload_form').show();
		
	} else if (contentType == ContentTypeIDOfClientInfo) {
		
		document.location = '/content/clientInfo';
		$('#content_add_form').hide();
		$('#content_audio_upload_form').hide();
		$('#content_client_add_form').hide();
		
	} else if (contentType == ContentTypeIDOfTalkBreak) {
		
		document.location = '/content/talkBreak';
		return;
		
	} else {
		$('#content_add_form').hide();
		$('#content_audio_upload_form').hide();
		$('#content_client_add_form').hide();
	}
	
	var contentType = this.getCurrentContentTypeSelected();
	
	if (contentType != ContentTypeIDOfMaterialInstruction) {
		$('#material-ad-list-table_wrapper').hide();
	} else {
		$('#material-ad-list-table_wrapper').show();
	}
	
	if (contentType == ContentTypeIDOfAudio) {
		$('#audio-upload-list-table_wrapper').show();
		$('#content_btn_new').hide();
		$('#content_btn_save').hide();
	} else {
		$('#audio-upload-list-table_wrapper').hide();
		$('#content_btn_new').show();
		$('#content_btn_save').show();
	}
	
	$(window).resize();
}

ContentForm.prototype.showSearchForm = function(contentType, preserveSearchVal) {
	
	$('#content-sub-header-second-form').hide();
	$('#content-sub-header-version-form').hide();
	$('#content_created_date_info').hide();
	
	if (contentType == ContentTypeIDOfAd || contentType == ContentTypeIDOfMaterialInstruction || contentType == ContentTypeIDOfAudio || contentType == ContentTypeIDOfDailyLog || contentType == ContentTypeIDOfClientInfo || contentType == ContentTypeIDOfTalk || contentType == ContentTypeIDOfNews || contentType == ContentTypeIDOfTalkBreak) {
		$('#content_search_form').show();
		
		if (contentType == ContentTypeIDOfAd) {
			$('#search_adtype_length_wrapper').show();
			$('#search_start_end_date_wrapper').show();
			$('#search_content_type_created_date').hide();
			$('#search_ad_key_wrapper').show();
			$('#search_agency_id').show();
			
			$('#search_content_sub_type_talk_wrapper').hide();
			$('#search_content_client_wrapper').show();
			$('#search_content_atb_line_wrapper').show();
			$('#search_content_session_name_wrapper').hide();
			$('#search_content_talk_time_range_wrapper').hide();
			$('#search_content_talk_weekdays_wrapper').hide();
			$('#search_content_client_wrapper2').hide();
			$('#search_content_product_wrapper').show();
			$('#search_manager_user_id').show();
			$('#search_content_who_what_wrapper').hide();
		} else if (contentType == ContentTypeIDOfMaterialInstruction) {
			$('#search_adtype_length_wrapper').hide();
			$('#search_start_end_date_wrapper').hide();
			$('#search_content_type_created_date').show();
			$('#search_ad_key_wrapper').hide();
			$('#search_agency_id').hide();
			$('#content-sub-header-version-form').show();
			
			$('#search_content_sub_type_talk_wrapper').hide();
			$('#search_content_client_wrapper').show();
			$('#search_content_atb_line_wrapper').show();
			$('#search_content_session_name_wrapper').hide();
			$('#search_content_talk_time_range_wrapper').hide();
			$('#search_content_talk_weekdays_wrapper').hide();
			$('#search_content_client_wrapper2').hide();
			$('#search_content_product_wrapper').show();
			$('#search_manager_user_id').show();
			$('#search_content_who_what_wrapper').hide();
		} else if (contentType == ContentTypeIDOfAudio) {
			$('#search_adtype_length_wrapper').show();
			$('#search_start_end_date_wrapper').show();
			$('#search_content_type_created_date').hide();
			$('#search_ad_key_wrapper').show();
			$('#search_agency_id').show();
			
			$('#search_content_sub_type_talk_wrapper').hide();
			$('#search_content_client_wrapper').show();
			$('#search_content_atb_line_wrapper').show();
			$('#search_content_session_name_wrapper').hide();
			$('#search_content_talk_time_range_wrapper').hide();
			$('#search_content_talk_weekdays_wrapper').hide();
			$('#search_content_client_wrapper2').hide();
			$('#search_content_product_wrapper').show();
			$('#search_manager_user_id').show();
			$('#search_content_who_what_wrapper').hide();
		} else if (contentType == ContentTypeIDOfTalk || contentType == ContentTypeIDOfTalkBreak) {
			$('#search_adtype_length_wrapper').hide();
			$('#search_start_end_date_wrapper').show();
			$('#search_content_type_created_date').hide();
			$('#search_ad_key_wrapper').hide();
			$('#search_agency_id').hide();
			
			$('#search_content_sub_type_talk_wrapper').show();
			$('#search_content_client_wrapper').hide();
			$('#search_content_atb_line_wrapper').hide();
			$('#search_content_session_name_wrapper').show();
			$('#search_content_talk_time_range_wrapper').show();
			$('#search_content_talk_weekdays_wrapper').show();
			$('#search_content_client_wrapper2').show();
			$('#search_content_product_wrapper').hide();
			$('#search_manager_user_id').hide();
			$('#search_content_who_what_wrapper').show();
		} else if (contentType == ContentTypeIDOfNews) {
			/*$('#search_adtype_length_wrapper').hide();
			$('#search_start_end_date_wrapper').show();
			$('#search_content_type_created_date').hide();
			$('#search_ad_key_wrapper').hide();
			$('#search_agency_id').hide();
			
			$('#search_content_sub_type_talk_wrapper').hide();
			$('#search_content_client_wrapper').hide();
			$('#search_content_atb_line_wrapper').hide();
			$('#search_content_session_name_wrapper').hide();
			$('#search_content_talk_time_range_wrapper').show();
			$('#search_content_talk_weekdays_wrapper').show();
			$('#search_content_client_wrapper2').hide();
			$('#search_content_product_wrapper').hide();
			$('#search_manager_user_id').hide();
			$('#search_content_who_what_wrapper').show();*/
			
			$('#content_search_form').hide();
			document.location = '/content/news';
			return;
			
		} else if (contentType == ContentTypeIDOfDailyLog) {
			$('#content-sub-header-second-form').show();
		}
		
		if (contentType == ContentTypeIDOfDailyLog) {
			$('#content_search_content_form').hide();
			$('#dailylog_statistics_container').show();
			$('#client_search_form').hide();
		} else if (contentType == ContentTypeIDOfClientInfo) {
			$('#content_search_content_form').hide();
			$('#dailylog_statistics_container').hide();
			$('#client_search_form').show();
		} else {
			$('#content_search_content_form').show();
			$('#dailylog_statistics_container').hide();
			$('#client_search_form').hide();
		}
		
		if (!preserveSearchVal) {
			this.initSearchFormData();
			this.renderDataToSearchForm();
		}
		
	} else {
		$('#content_search_form').hide();
	}
}

ContentForm.prototype.showSearchResultTable = function(contentType) {
	if (contentType == ContentTypeIDOfAd) {
		$('#content-list-table_wrapper').show();
		$('#material-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} else if (contentType == ContentTypeIDOfMaterialInstruction) {
		$('#material-list-table_wrapper').show();
		$('#content-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} else if (contentType == ContentTypeIDOfTalk || contentType == ContentTypeIDOfTalkBreak) {
		$('#content-list-table_wrapper').hide();
		$('#material-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').show();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} /*else if (contentType == ContentTypeIDOfNews) {
		$('#content-list-table_wrapper').hide();
		$('#material-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').show();
		$(window).resize();
	}*/ else if (contentType == ContentTypeIDOfAudio) {
		$('#material-list-table_wrapper').hide();
		$('#content-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').show();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} else if (contentType == ContentTypeIDOfDailyLog) {
		$('#material-list-table_wrapper').hide();
		$('#content-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').show();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} else if (contentType == ContentTypeIDOfClientInfo) {
		$('#material-list-table_wrapper').hide();
		$('#content-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').show();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
		$(window).resize();
	} else {
		$('#content-list-table_wrapper').hide();
		$('#material-list-table_wrapper').hide();
		$('#audio-upload-list-table_wrapper').hide();
		$('#dailylog-tag-list-table_wrapper').hide();
		$('#clients-list-table_wrapper').hide();
		$('#content-talk-list-table_wrapper').hide();
		$('#content-news-list-table_wrapper').hide();
	}
}

ContentForm.prototype.hideAllSubHeaderActions = function(show) {
	
	var contentType = this.getCurrentContentTypeSelected();
	
	if( show ) {
		$('#content_btn_print').show();
		$('#content_btn_search').show();
		$('#content_btn_new').show();
		
		if (contentType != ContentTypeIDOfMaterialInstruction)
			$('#content_btn_copy').show();
		
		$('#content_btn_save').show();
		$('#content_btn_remove').show();
		
		if (contentType != ContentTypeIDOfMaterialInstruction)
			$('#content_btn_preview').show();
	} else {
		$('#content_btn_print').hide();
		$('#content_btn_search').hide();
		$('#content_btn_new').hide();
		$('#content_btn_copy').hide();
		$('#content_btn_save').hide();
		$('#content_btn_remove').hide();
		$('#content_btn_preview').hide();
	}
}

ContentForm.prototype.loadSearchResult = function() {
	this.updateSearchDataFromForm();
	
	var contentType = this.getCurrentContentTypeSelected();
	
	if (contentType == ContentTypeIDOfAd) {	
		$('#content-list-table').DataTable().ajax.reload();
	} else if (contentType == ContentTypeIDOfMaterialInstruction) {
		$('#material-list-table').DataTable().ajax.reload();
	} else if (contentType == ContentTypeIDOfTalk || contentType == ContentTypeIDOfTalkBreak) {
		$('#content-talk-list-table').DataTable().ajax.reload();
	} else if (contentType == ContentTypeIDOfNews) {
		//$('#content-news-list-table').DataTable().ajax.reload();
	} else if (contentType == ContentTypeIDOfAudio) {
		this.loadAudioFileSearchResult();
	} else if (contentType == ContentTypeIDOfDailyLog) {
		//$('#dailylog-tag-list-table').DataTable().order([]).draw();
		//console.log($('#dailylog-tag-list-table').DataTable().order());
		this.dailyLogTagListTable.fnSortNeutral();
		$('#dailylog-tag-list-table').DataTable().ajax.reload();
	} else if (contentType == ContentTypeIDOfClientInfo) {
		$('#clients-list-table').DataTable().ajax.reload();
	}
}

ContentForm.prototype.loadAudioFileSearchResult = function() {
	
	var that = this;
	
	this.audioFiles = new Array();
	
	showTableLoader();
	
	$.ajax ( 
		{
			url: "/content/listAudio",
			type: "post",
			data: {
				"search_content_type_id" : ContentTypeIDOfAudio,
				"search_content_sub_type_id" : that.search_content_subtype_id,
				"search_ad_length" : that.search_ad_length,
				"search_content_client" : that.search_content_client,
				"search_atb_date" : that.search_atb_date,
				"search_line_number" : that.search_line_number,
				"search_start_date" : that.search_start_date,
				"search_end_date" : that.search_end_date,
				"search_ad_key" : that.search_ad_key,
				"search_content_product" : that.search_content_product,
				"search_manager_user_id" : that.search_manager_user_id,
				"search_agency_id" : that.search_agency_id
			},
			dataType: "json",
			success: function( resp ) {
				if (resp.data) {
					for (var index in resp.data) {
						var newAudioFile = new ContentAudioModel(resp.data[index].attachment_id);
						newAudioFile.loadDataFromJson(resp.data[index]);
						that.audioFiles.push(newAudioFile);
					}
				}
			}
		}
	).fail ( function () {
		
		
	}).always( function () {
		
		that.refreshAudioBulkUploadTable();
		
		hideTableLoader();
		
	});
	
}

ContentForm.prototype.initSearchFormData = function() {
	
	this.search_content_subtype_id = 0;
	this.search_content_rec_type = '';
	this.search_ad_length = 0;
	this.search_content_client = "";
	this.search_atb_date = "";
	this.search_start_date = "";
	this.search_end_date = "";
	this.search_line_number = "";
	this.search_ad_key = "";
	this.search_content_product = "";
	this.search_agency_id = 0;
	this.search_manager_user_id = 0;
	
	this.search_content_version = "";
	this.search_created_date = "";
	
	this.search_dailylog_date = '';
	this.search_dailylog_content_type = 0;
	this.search_dailylog_only_missing = 0;
	
	this.search_start_time = "";
	this.search_end_time = "";
	this.search_session_name = "";
	this.search_content_who = "";
	this.search_content_what = "";
	
	this.searchContentWeekDays = new Array();
	for (var i = 0; i < 7; i++) { this.searchContentWeekDays[i] = false; }
}

ContentForm.prototype.renderDataToSearchForm = function() {

	$('#search_content_sub_type_id').val(this.search_content_subtype_id);
	$('#search_content_sub_type_id2').val(this.search_content_subtype_id);
	$('#search_content_sub_type_id3').val(this.search_content_subtype_id);
	
	$('#search_content_rec_type').val(this.search_content_rec_type);
	$('#search_ad_length').val(this.search_ad_length);
	$('#search_content_client').val(this.search_content_client);
		
	$('#search_atb_date').val(this.search_atb_date);
	
	$('#search_line_number').val(this.search_line_number);
	
	var dateParsed = parseStringToDate(this.search_start_date);
	if (dateParsed == null) {
		$('#search_start_date').datepicker('update', new Date());
		$('#search_start_date').val('');
	} else {
		$('#search_start_date').datepicker('setDate', dateParsed);
		$('#search_start_date').datepicker('update', dateParsed);
	}
	
	dateParsed = parseStringToDate(this.search_end_date);
	if (dateParsed == null) {
		$('#search_end_date').datepicker('update', new Date());
		$('#search_end_date').val('');
	}else {
		$('#search_end_date').datepicker('setDate', dateParsed);
		$('#search_end_date').datepicker('update', dateParsed);
	}
	
	$('#search_ad_key').val(this.search_ad_key);
	$('#search_content_product').val(this.search_content_product);
	$('#search_manager_user_id').val(this.search_manager_user_id);
	$('#search_agency_id').val(this.search_agency_id);
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		$('#search_content_client2').val(this.search_content_client);
		$('#search_content_product2').val(this.search_content_product);
		$('#search_manager_user_id2').val(this.search_manager_user_id);
		$('#search_agency_id2').val(this.search_agency_id);
	}
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		$('#search_content_client3').val(this.search_content_client);
	}
	
	dateParsed = parseStringToDate(this.search_created_date);
	if (dateParsed == null) {
		$('#search_created_date').datepicker('update', new Date());
		$('#search_created_date').val('');
	} else {
		$('#search_created_date').datepicker('setDate', dateParsed);
		$('#search_created_date').datepicker('update', dateParsed);
	}
	
	$('#content_version').val(this.search_content_version);
	
	$('.preview_analytics_info').removeClass('selected');
	$('#preview_analytics_' + this.search_dailylog_content_type).addClass('selected');
	
	$('#search_content_session_name').val(this.search_session_name);
	if (this.search_start_time == "" || !this.search_start_time) {
		$('#search_content_talk_start_time').val('');
	} else {
		$('#search_content_talk_start_time').timepicker('setTime', this.search_start_time);
	}
	if (this.search_end_time == "" || !this.search_end_time) {
		$('#search_content_talk_end_time').val('');
	} else {
		$('#search_content_talk_end_time').timepicker('setTime', this.search_end_time);
	}
	$('#search_content_who').val(this.search_content_who);
	$('#search_content_what').val(this.search_content_what);
	
	for (var index in this.searchContentWeekDays) {
		checkCircleBox($('#search_content_talk_weekday_' + index), this.searchContentWeekDays[index]);
	}
}

ContentForm.prototype.updateSearchDataFromForm = function() {
	
	this.search_content_type_id = $('#content_content_type_id').val();
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfMaterialInstruction) {
		this.search_content_subtype_id = $('#search_content_sub_type_id2').val();
	} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		this.search_content_subtype_id = $('#search_content_sub_type_id3').val();
	} else {
		this.search_content_subtype_id = $('#search_content_sub_type_id').val();
	}
	
	this.search_content_rec_type = $('#search_content_rec_type').val();
	this.search_ad_length = $('#search_ad_length').val();
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		this.search_content_client = $('#search_content_client2').val();
		this.search_content_product = $('#search_content_product2').val();
		this.search_manager_user_id = $('#search_manager_user_id2').val();
		this.search_agency_id = $('#search_agency_id2').val();
	} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		this.search_content_client = $('#search_content_client3').val();
	} else {
		this.search_content_client = $('#search_content_client').val();
		this.search_content_product = $('#search_content_product').val();
		this.search_manager_user_id = $('#search_manager_user_id').val();
		this.search_agency_id = $('#search_agency_id').val();
	}
	
	this.search_atb_date = $('#search_atb_date').val();
	this.search_line_number = $('#search_line_number').val();
	this.search_start_date = $('#search_start_date').val();
	this.search_end_date = $('#search_end_date').val();
	
	this.search_ad_key = $('#search_ad_key').val();
	
	this.search_content_version = $('#content_version').val();
	
	this.search_created_date = $('#search_created_date').val();
	
	this.search_dailylog_date = $('#dailylog_date').val();
	
	
	for (var i = 0; i < 7; i++) {
		this.searchContentWeekDays[i] = getCircleBoxCheck($('#search_content_talk_weekday_' + i));
	}
	this.search_session_name = $('#search_content_session_name').val();
	this.search_start_time = $('#search_content_talk_start_time').val();
	this.search_end_time = $('#search_content_talk_end_time').val();
	this.search_content_who = $('#search_content_who').val();
	this.search_content_what = $('#search_content_what').val();
}


ContentForm.prototype.initFormData = function() {

	this.content_id = 0;
	
	// form data
	this.content_type_id = 0;
	this.content_subtype_id = 0;
	this.content_rec_type = '';
	this.who = "";
	this.what = "";
	this.more = "";
	this.description = "";
	this.ad_length = 0;
	this.content_client = "";
	this.content_product = "";
	this.content_line_number = "";
	this.content_contact = "";
	this.content_email = "";
	this.content_phone = "";
	this.content_instructions = "";
	this.content_voices = "";
	this.content_agency_id = 0;
	
	this.content_manager_user_id = 0;
	this.atb_date = "";
	this.start_date = "";
	this.end_date = "";
	this.ad_key = "";
	this.map_included = 0;
	this.map_address1 = "";
	this.map_address2 = "";
	this.action_id = 0;
	this.action_param_phone_number = "";
	this.action_param_website = "";
	this.action_param = {};
	
	this.content_version = "";
	this.content_creation_timestamp = 0;
	
	this.content_session_name = "";
	
	this.text_enabled = 0;
	this.audio_enabled = 0;
	this.image_enabled = 0;
	this.action_enabled = 0;
	this.is_ready = 0;
	
	this.attachments = new Array();
	
	this.subContents = new Array();
	
	this.audioFiles = new Array();
	
	this.contentDates = new Array();
	for (var i = 0; i < DEFAULT_CONTENT_DATES_COUNT; i++) {
		this.contentDates.push({'start_date': '', 'end_date': '', 'date_id': 0});
	}
	
	
	this.contentWeekDays = new Array();
	for (var i = 0; i < 7; i++) { this.contentWeekDays[i] = false; }
	
	this.start_time = "";
	this.end_time = "";
	this.is_competition = 0;
	
	this.contentAssociation = new Array();
	
	this.tag_id_list = "";
}

ContentForm.prototype.renderDataToForm = function() {
	
	$('#content_content_type_id').val(this.content_type_id);
	$('#content_rec_type').val(this.content_rec_type);
	$('#content_ad_length').val(this.ad_length);
	$('#content_client').val(this.content_client);
	
	
	$('#content_line_number').val(this.content_line_number);
	$('#content_contact').val(this.content_contact);
	$('#content_email').val(this.content_email);
	$('#content_phone').val(this.content_phone);
	$('#content_instructions').val(this.content_instructions);
	$('#content_voices').val(this.content_voices);
	$('#content_agency_id').val(this.content_agency_id);
	
	$('#content_description').val(this.description);
	
	//if (this.getCurrentContentTypeSelected() == ContentTypeIDOfAd) {
		$('#content_manager_user_id').val(this.content_manager_user_id);
		$('#content_product').val(this.content_product);
		$('#content_content_sub_type_id').val(this.content_subtype_id);
	//} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfMaterialInstruction) {
		$('#content_manager_user_id2').val(this.content_manager_user_id);
		$('#content_product2').val(this.content_product);
		$('#content_content_sub_type_id2').val(this.content_subtype_id);
	//} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		$('#content_content_sub_type_id3').val(this.content_subtype_id);
	//}
	
	//if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		$('#content_client_name').val(this.content_client);
		$('#content_client_product').val(this.content_product);
		$('#content_client_contact').val(this.content_contact);
		$('#content_client_email').val(this.content_email);
		$('#content_client_phone').val(this.content_phone);
		$('#content_client_who').val(this.who);
		$('#content_client_map_address').val(this.map_address1);
		$('#content_client_manager_user_id').val(this.content_manager_user_id);
		$('#content_client_agency_id').val(this.content_agency_id);
		
		$('#content_client_enabled').prop('checked', this.is_ready);
	//}
		
	
	$('#content_atb_date').val(this.atb_date);
	
	$('#content_ad_key').val(this.ad_key);
	
	//if (this.content_type_id == ContentTypeIDOfTalk) {
		var dateParsed = parseStringToDate(this.start_date);
		if (dateParsed == null) {
			$('#content_talk_start_date').datepicker('update', new Date());
			$('#content_talk_start_date').val('');
		} else {
			$('#content_talk_start_date').datepicker('setDate', dateParsed);
			$('#content_talk_start_date').datepicker('update', dateParsed);
		}
		
		dateParsed = parseStringToDate(this.end_date);
		if (dateParsed == null){
			$('#content_talk_end_date').datepicker('update', new Date());
			$('#content_talk_end_date').val('');
		} else {
			$('#content_talk_end_date').datepicker('setDate', dateParsed);
			$('#content_talk_end_date').datepicker('update', dateParsed);
		}
	//}
	
	//$('#content_map_address1').val(this.map_address1);
	//$('#content_map_address2').val(this.map_address2);
	$('#content_map_address').val(this.map_address1);
	
	if (this.map_address1 != undefined && this.map_address1 != '') {
		this.map_included = true;
	} else {
		this.map_included = false;
	}
	$('#content_map_included').prop('checked', this.map_included);
		
	$('#content_who').val(this.who);
	$('#content_what').val(this.what);
	$('#content_more').val(this.more);
	
	$('#content_action_id').val(this.action_id);
	$('#content_action_param_phone_number').val(this.action_param_phone_number);
	$('#content_action_param_website').val(this.action_param_website);
	
	this.setupActionParamFields(this.action_id);
	
	$('#content_version').val(this.content_version);
	if (this.content_creation_timestamp > 0) {
		$('#content_created_date_info').html('CREATED ' + moment(new Date(this.content_creation_timestamp * 1000)).format('D-MMM HH:mm'));
	} else {
		$('#content_created_date_info').html('');
	}
	
	$('#content_session_name').val(this.content_session_name);
	
	$('#content_text_enabled').prop('checked', this.text_enabled);
	$('#content_audio_enabled').prop('checked', this.audio_enabled);
	$('#content_image_enabled').prop('checked', this.image_enabled);
	$('#content_action_enabled').prop('checked', this.action_enabled);
	$('#content_is_ready').prop('checked', this.is_ready);
	$('#content_is_competition').prop('checked', this.is_competition);
	
	if (this.start_time == "" || !this.start_time) {
		$('#content_talk_start_time').val('');
	} else {
		$('#content_talk_start_time').timepicker('setTime', this.start_time);
	}
	
	if (this.end_time == "" || !this.end_time) {
		$('#content_talk_end_time').val('');
	} else {
		$('#content_talk_end_time').timepicker('setTime', this.end_time);
	}
	
	this.renderTagIdList();
		
	this.renderContentWeekDays();
	
	this.renderAttachmentFields();
	
	this.refreshMaterialInstructionTable();
	
	this.refreshAudioBulkUploadTable();
	
	this.renderContentDatesForm();
}

ContentForm.prototype.renderTagIdList = function() {
	$('#content_demo_tag_ids').val(this.tag_id_list);
}

ContentForm.prototype.renderContentWeekDays = function() {
	
	for (var index in this.contentWeekDays) {
		checkCircleBox($('#content_talk_weekday_' + index), this.contentWeekDays[index]);
	}
}

ContentForm.prototype.updateDataFromForm = function() {
	
	this.content_type_id = $('#content_content_type_id').val();
	
	this.content_rec_type = $('#content_rec_type').val();
	this.ad_length = $('#content_ad_length').val();
	this.content_client = $('#content_client').val();
		
	this.content_line_number = $('#content_line_number').val();
	this.content_contact = $('#content_contact').val();
	this.content_email = $('#content_email').val();
	this.content_phone = $('#content_phone').val();
	this.content_instructions = $('#content_instructions').val();
	this.content_voices = $('#content_voices').val();
	this.content_agency_id = $('#content_agency_id').val();
	
	this.description = $('#content_description').val();
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfAd) {
		this.content_manager_user_id = $('#content_manager_user_id').val();
		this.content_product = $('#content_product').val();
		this.content_subtype_id = $('#content_content_sub_type_id').val();
	} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfMaterialInstruction) {
		this.content_manager_user_id = $('#content_manager_user_id2').val();
		this.content_product = $('#content_product2').val();
		this.content_subtype_id = $('#content_content_sub_type_id2').val();
	} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		this.content_subtype_id = $('#content_content_sub_type_id3').val();
	} else if (this.getCurrentContentTypeSelected() == ContentTypeIDOfNews) {
		this.content_subtype_id = $('#content_content_sub_type_id3').val();
	}
	
	this.atb_date = $('#content_atb_date').val();
	this.ad_key = $('#content_ad_key').val();
	/*this.start_date = $('#content_start_date').val();
	this.end_date = $('#content_end_date').val();*/
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfTalk) {
		this.start_date = $('#content_talk_start_date').val();
		this.end_date = $('#content_talk_end_date').val();
	} else {
		this.start_date = "";
		this.end_date = "";
	}
	
	this.start_time = $('#content_talk_start_time').val();
	this.end_time = $('#content_talk_end_time').val();
	
	this.map_included = $('#content_map_included').is(':checked');
	//this.map_address1 = $('#content_map_address1').val();
	//this.map_address2 = $('#content_map_address2').val();
	this.map_address1 = $('#content_map_address').val();
	this.who = $('#content_who').val();
	this.what = $('#content_what').val();
	this.more = $('#content_more').val();
	this.action_id = $('#content_action_id').val();
	
	this.action_param_phone_number = $('#content_action_param_phone_number').val();
	this.action_param_website = $('#content_action_param_website').val();
	
	this.getActionParamFields(this.action_id);
	
	this.content_version = $('#content_version').val();
	
	this.content_session_name = $('#content_session_name').val();
	
	this.text_enabled =  $('#content_text_enabled').is(':checked');
	this.audio_enabled =  $('#content_audio_enabled').is(':checked');
	this.image_enabled =  $('#content_image_enabled').is(':checked');
	this.action_enabled =  $('#content_action_enabled').is(':checked');
	this.is_ready =  $('#content_is_ready').is(':checked');
	this.is_competition = $('#content_is_competition').is(':checked');
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		
		this.content_client = $('#content_client_name').val();
		this.content_product = $('#content_client_product').val();
		this.content_contact = $('#content_client_contact').val();
		this.content_email = $('#content_client_email').val();
		this.content_phone = $('#content_client_phone').val();
		this.who = $('#content_client_who').val();
		this.map_address1 = $('#content_client_map_address').val();
		this.content_manager_user_id = $('#content_client_manager_user_id').val();
		this.content_agency_id = $('#content_client_agency_id').val();
		
		this.is_ready = $('#content_client_enabled').is(':checked');
	}
	
	this.getContentWeekdaysFields();
	
	this.getAttachmentFields();
	
	this.getContentDatesFields();
}

ContentForm.prototype.getContentWeekdaysFields = function() {
	for (var i = 0; i < 7; i++) {
		this.contentWeekDays[i] = getCircleBoxCheck($('#content_talk_weekday_' + i));
	}
}


ContentForm.prototype.validateForm = function() {
	
	if (!this.content_type_id || this.content_type_id == '0') {
		$('.saveProgress').show().html('Validation error. Please select content type.').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		return false;
	}
	
	if (this.content_type_id == ContentTypeIDOfAd) {  // Ad-specific validation
	
		/*if (!this.start_date || this.start_date == '') {
			showGritterMsg('Validation error.', 'Please select start date.');
			return false;
		}
		
		if (!this.end_date || this.end_date == '') {
			showGritterMsg('Validation error.', 'Please select end date.');
			return false;
		}*/
		
		if (!this.ad_key || this.ad_key == '') {
			$('.saveProgress').show().html('Validation error. Please enter ad key.').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			return false;
		}
		
		var dateRangeSelected = false;
		
		for (var index in this.contentDates) {
			var contentDate = this.contentDates[index];
			if (contentDate.start_date && contentDate.start_date != '' && contentDate.end_date && contentDate.end_date != '') {
				dateRangeSelected = true;
				break;
			}
		}
		
		if (!dateRangeSelected) {
			$('.saveProgress').show().html('Validation error. Please enter at least one date range.').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			return false;
		}
		
	} else if (this.content_type_id == ContentTypeIDOfMaterialInstruction) {  // Material instruction specific validation
		
		if (this.subContents.length <= 0) {
			$('.saveProgress').show().html('Validation error. Please add at least one Ad content to the list.').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			return false;
		}
		
	} else if (this.content_type_id == ContentTypeIDOfClientInfo) {		// Client info specific validation
		
		if (!this.content_client || this.content_client == '') {
			$('.saveProgress').show().html('Validation error. Please enter client company name.').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			return false;
		}
		
	} else if (this.content_type_id == ContentTypeIDOfTalk) {
		
		if (this.content_subtype_id == 0) {
			$('.saveProgress').show().html('Validation error. Please select talk type.').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			return false;
		}
		
		if (this.content_subtype_id == ContentSubTypeIDOfTalkShow) {
			
			if (!this.content_session_name || this.content_session_name == '') {
				$('.saveProgress').show().html('Validation error. Please enter session name.').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				return false;
			}
			
		} else if (this.content_subtype_id == ContentSubTypeIDOfSegment) {
			
		}
	}
	
	return true;
	
}

ContentForm.prototype.getAttachmentFields = function() {

	this.attachments = new Array();
	
	if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		this.attachments[0] = {
				"type" : "logo",
				"content_attachment_id" : this.ClientAttachmentLogo1.selectedAttachmentId
		};
	} else {
		this.attachments[0] = {
			"type" : this.AttachmentImage1.videoChecked ? "video" : "image",
			"video_url" : this.AttachmentImage1.videoTextBox.val(),
			"content_attachment_id" : this.AttachmentImage1.selectedAttachmentId
		};
		
		this.attachments[1] = {
				"type" : this.AttachmentImage2.videoChecked ? "video" : "image",
				"video_url" : this.AttachmentImage2.videoTextBox.val(),
				"content_attachment_id" : this.AttachmentImage2.selectedAttachmentId
		};
		
		this.attachments[2] = {
				"type" : this.AttachmentImage3.videoChecked ? "video" : "image",
				"video_url" : this.AttachmentImage3.videoTextBox.val(),
				"content_attachment_id" : this.AttachmentImage3.selectedAttachmentId
		};
		
		this.attachments[3] = {
				"type" : "logo",
				"content_attachment_id" : this.AttachmentLogo1.selectedAttachmentId
		};
		
		this.attachments[4] = {
				"type" : "audio",
				"content_attachment_id" : this.AttachmentAudio1.selectedAttachmentId
		};
	}
	
}

ContentForm.prototype.renderAttachmentFields = function() {

	//if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
		var logoAttachment = this.attachments[0];
		
		if (!logoAttachment || logoAttachment.type == 'video' || logoAttachment.type == 'image') logoAttachment = this.attachments[3];
		
		this.ClientAttachmentLogo1.resetWithObjectValue((logoAttachment == null || logoAttachment == undefined) ? {"type" : "logo"} : logoAttachment);
		
	//} else {
		this.AttachmentImage1.resetWithObjectValue((this.attachments[0] == null || this.attachments[0] == undefined) ? {"type" : "image"} : this.attachments[0]);
		this.AttachmentImage2.resetWithObjectValue((this.attachments[1] == null || this.attachments[1] == undefined) ? {"type" : "image"} : this.attachments[1]);
		this.AttachmentImage3.resetWithObjectValue((this.attachments[2] == null || this.attachments[2] == undefined) ? {"type" : "image"} : this.attachments[2]);
		
		this.AttachmentLogo1.resetWithObjectValue((this.attachments[3] == null || this.attachments[3] == undefined) ? {"type" : "logo"} : this.attachments[3]);
		this.AttachmentAudio1.resetWithObjectValue((this.attachments[4] == null || this.attachments[4] == undefined) ? {"type" : "audio"} : this.attachments[4]);
	//}
}

ContentForm.prototype.renderContentDatesForm = function() {
	
	$('#content_date_range_wrapper').html('');
	
	var contentDatesHTML = '';
	
	var i = 0;
	
	for (var index in this.contentDates) {
		var contentDate = this.contentDates[index];
		contentDatesHTML += '<div class="form-group"><div class="row"><div class="col-sm-12"><input type="text" class="form-control" id="content_start_date_' + i + '" placeholder="Start Date ' + (i+1) + '" /></div><div class="col-sm-12"><input type="text" class="form-control" id="content_end_date_' + i + '" placeholder="End Date ' + (i+1) + '" /><input type="hidden" id="content_date_id_' + i + '" value="' + contentDate.date_id + '" /></div></div></div>';
		i++;
	}
	
	$('#content_date_range_wrapper').html(contentDatesHTML);
	
	i = 0;
	for (var index in this.contentDates) {
		var contentDate = this.contentDates[index];
		
		$('#content_start_date_' + i).datepicker({
			autoclose:  true,
			format: 'dd-mm-yyyy'
		});
		
		$('#content_end_date_' + i).datepicker({
			autoclose:  true,
			format: 'dd-mm-yyyy'
		});
				
		var dateParsed = parseStringToDate(contentDate.start_date);
		if (dateParsed == null) {
			$('#content_start_date_' + i).datepicker('update', new Date());
			$('#content_start_date_' + i).val('');
		} else {
			$('#content_start_date_' + i).datepicker('setDate', dateParsed);
			$('#content_start_date_' + i).datepicker('update', dateParsed);
		}
		
		dateParsed = parseStringToDate(contentDate.end_date);
		if (dateParsed == null) {
			$('#content_end_date_' + i).datepicker('update', new Date());
			$('#content_end_date_' + i).val('');
		} else {
			$('#content_end_date_' + i).datepicker('setDate', dateParsed);
			$('#content_end_date_' + i).datepicker('update', dateParsed);
		}
		
		i++;
	}
}

ContentForm.prototype.getContentDatesFields = function() {
	var i = 0;
	for (var index in this.contentDates) {
		this.contentDates[index].start_date = $('#content_start_date_' + i).val();
		this.contentDates[index].end_date = $('#content_end_date_' + i).val();
		i++;
	}
	return this.contentDates;
}


ContentForm.prototype.getSubContentsIdList = function() {
	
	var subContentIdList = new Array();
	
	for(var index in this.subContents) {
		subContentIdList.push(this.subContents[index].id);
	}
	
	return subContentIdList;
}

ContentForm.prototype.getSubContentSyncList = function() {
	
	var subContentSyncList = new Array();
	
	for(var index in this.subContents) {
		var subContent = this.subContents[index];
		subContentSyncList.push(subContent.content_sync);
	}
	
	return subContentSyncList;
}

ContentForm.prototype.getSubContentDateIdList = function() {
	
	var subContentDateIdList = new Array();
	
	for(var index in this.subContents) {
		var subContent = this.subContents[index];
		subContentDateIdList.push(subContent.child_content_date_id);
	}
	
	return subContentDateIdList;
	
}

ContentForm.prototype.getActionParamFields = function(action_id) {
	
	this.action_param = {};
	
	switch (action_id) {
	case "1":				// Book
		this.action_param.website = this.action_param_website;
		break;
	case "2":				// Phone
		this.action_param.phone = this.action_param_phone_number;
		break;
	case "3":				// Claim
		break;
	case "4":				// Contact Me
		this.action_param.phone = this.action_param_phone_number;
		break;
	case "5":				// Get
		this.action_param.website = this.action_param_website;
		break;
	case "6":				// Website
		this.action_param.website = this.action_param_website;
		break;
	case "7":				// Call Me
		this.action_param.website = this.action_param_website;
		break;
	case "8":				// SMS
		this.action_param.phone = this.action_param_phone_number;
		break;
	case "9":				// Update
		this.action_param.website = this.action_param_website;
		break;
	}
}

ContentForm.prototype.setupActionParamFields = function(action_id) {
	
	$('#content_action_param_phone_number').hide();
	$('#content_action_param_website').hide();
			
	action_id = parseAsString(action_id);
	
	switch (action_id) {
	case "1":				// Book
		$('#content_action_param_website').show();
		break;
	case "2":				// Phone
		$('#content_action_param_phone_number').show();
		$('#content_action_param_phone_number').attr('placeholder', 'Phone number to call');
		break;
	case "3":				// Claim
		break;
	case "4":				// Contact Me
		$('#content_action_param_phone_number').show();
		$('#content_action_param_phone_number').attr('placeholder', 'Phone number to call');
		break;
	case "5":				// Get
		$('#content_action_param_website').show();
		break;
	case "6":				// Website
		$('#content_action_param_website').show();
		break;
	case "7":				// Call Me
		$('#content_action_param_website').show();
		break;
	case "8":				// SMS
		$('#content_action_param_phone_number').show();
		$('#content_action_param_phone_number').attr('placeholder', 'Phone number to SMS');
		break;
	case "9":				// Update
		$('#content_action_param_website').show();
		break;
	}
}


ContentForm.prototype.setupDropZone = function() {

	this.AttachmentImage1 = new ContentAttachment('image', 'attachment_image1', 'attachment_image1_drop', uploadURL, 'attachment_image1_preview');
	this.AttachmentImage2 = new ContentAttachment('image', 'attachment_image2', 'attachment_image2_drop', uploadURL, 'attachment_image2_preview');
	this.AttachmentImage3 = new ContentAttachment('image', 'attachment_image3', 'attachment_image3_drop', uploadURL, 'attachment_image3_preview');
	this.AttachmentLogo1 = new ContentAttachment('logo', 'attachment_logo', 'attachment_logo_drop', uploadURL, 'attachment_logo_preview');
	this.AttachmentAudio1 = new ContentAttachment('audio', 'attachment_audio', 'attachment_audio_drop', uploadURL, 'attachment_audio_preview');
	
	this.ClientAttachmentLogo1 = new ContentAttachment('logo', 'client_attachment_logo', 'client_attachment_logo_drop', uploadURL, 'client_attachment_logo_preview');
	
	
	var that = this;
	
	// Bulk Audio upload dropzone
	
	this.bulkAudioUploadDrop = new Dropzone("div#audio_builk_dropzone", {
		url: "/content/audioUpload",
		method: 'post',
		paramName: 'file',
		createImageThumbnails: false,
		addRemoveLinks: true,
		maxFiles: 1000,
		uploadMultiple: false,
		init: function() {
			var self = this;
			
			this.on("complete", function(file) {
				
				self.removeFile(file);
				
				if (file.xhr && file.xhr.response && file.xhr.response != '')
					eval('var responseObj=' + file.xhr.response + ';');
				else
					responseObj = {};
				
				if (responseObj.code === 0 && responseObj.data) {
					
					var newAudioFile = new ContentAudioModel(responseObj.data.attachment_id);
					
					newAudioFile.loadDataFromJson(responseObj.data);
					
					that.addAudioFileRow(newAudioFile);
					
				} else if (responseObj.code === -1) {
					$('.saveProgress').show().html('Upload Error. ' + responseObj.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			});
			
			this.on("sending", function(file, xhr, formData) {
				//formData.append('attachment_type', that.type);
			});
			
			this.on("maxfilesexceeded", function(){
				$('.saveProgress').show().html('Upload Error. You are allowed to upload maximum 1000 files at a time.').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			});
			
			this.on("uploadprogress", function(file, percent) {
				console.log('percent: ' + percent);
			});
		}
	});
	
}


ContentForm.prototype.addNewSubContent = function(subcontent) {

	this.subContents.push(subcontent);
	this.refreshMaterialInstructionTable();
}


ContentForm.prototype.removeSubContent = function(subcontentId, contentDateId) {
	for (var index in this.subContents) {
		if (this.subContents[index].id == subcontentId && this.subContents[index].child_content_date_id == contentDateId) {
			this.subContents.splice(index, 1);
			break;
		}
	}
	this.refreshMaterialInstructionTable();
}


ContentForm.prototype.refreshMaterialInstructionTable = function() {
	
	var i = 0;
	
	for (var index in this.subContents) {
		i++;
		this.subContents[index].number = i;
		this.subContents[index].generateHTMLContents();
	}
	
	this.MaterialInstructionAdListTableObj.clear().draw();
	this.MaterialInstructionAdListTableObj.rows.add(this.subContents);
	this.MaterialInstructionAdListTableObj.draw();
	
	$(window).resize();
	
	this.attachEventListenersForTableRows();
	
}

var currentContentID=0;

ContentForm.prototype.attachEventListenersForTableRows = function() {
	
	var that = this;
	
	$('.material_ad_action_remove').each(function(){
		
		$(this).off('click').on('click', function(){
		
			var subcontentId = $(this).data('pk');
			var contentDateId = $(this).data('dateid');
			
			bootbox.confirm("Are you sure you want to remove this Ad from the Material Instruction?<br/>Note: This Ad will still remain in the Database. To delete it, please search for it and press the trash bin icon.", function(result){
				
				if (result) {
				
					showTableLoader();
					
					$.ajax ( 
						{
							url: "/content/removeContentFromParent",
							type: "post",
							data: {
								"pk" : subcontentId,
								"parent_id" : that.content_id,
								"child_content_date_id": contentDateId
							},
							dataType: "json",
							success: function( resp ) {
								if (resp.code == 0) {
									
									that.removeSubContent(subcontentId, contentDateId);
									
								} else {
									$('.saveProgress').show().html('Remove Failed' + resp.msg).css('color', 'red');
									setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
								}
							}
						}
					).fail ( function () {

						$('.saveProgress').show().html('Remove Failed. Network error.').css('color', 'red');
						setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
						
					}).always( function () {
						
						hideTableLoader();
					});
			
				}
			});
			
			
		});
		
	});

	$('.copy_ad_checkbox').off('click');
	$('.copy_ad_checkbox:not([data-pk='+currentContentID+'])').on('click', function() {
		var destID = $(this).data('pk');
		var checkbox = $(this);
		$.ajax(
			{
				url:'/content/copyAdToAd',
				type: 'post',
				data: {
					source_id: currentContentID,
					dest_id: destID
				}
			}
		).done(function(resp) {
			$('.saveProgress').show().html('Success! Successfully copied ad information').css('color', 'green');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

			checkbox.html('<i class="mdi mdi-checkbox-marked" style="color:green;"></i>');
			checkbox.addClass('checked');
			var ids = [];
			$('.copy_ad_checkbox.checked').each(function(i) {
				ids.push($(this).data('pk'));
			})
			that.updateSubContentValue(destID, checkbox.data('dateid'), "what", resp.data.what);

			that.refreshMaterialInstructionTable();
			$('.copy_ad_checkbox:not([data-pk='+currentContentID+'])').show();
			$('.copy_ad_checkbox[data-pk='+currentContentID+']').hide();
			$.each(ids, function(index, value) {
				$('.copy_ad_checkbox[data-pk='+value+']').html('<i class="mdi mdi-checkbox-marked" style="color:green;"></i>');
				$('.copy_ad_checkbox[data-pk='+value+']').addClass('checked');
			});
			console.log(resp.data);

		}).fail(function() {
			$('.saveProgress').show().html('Failed. Copying of ad failed').css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		});
	});

	$('.material_ad_show_preview').each(function() {

		$(this).off('click').on('click', function () {
			var contentID = $(this).data('pk');
			currentContentID = contentID;

			$('#mobilepreview_sidebar').removeClass('hidden');

			that.previewFormObj.renderPreviewInfo('content', contentID , function(data) {

				var formActionElement = $('.mobilepreview_action_buttons_container .preview-form-button').first();//$('.bottom-nav-shape .preview-form-button').first();

				var copyAdElement = $('.mobilepreview_action_buttons_container .copy-ad-button').first();

				$('.copy_ad_checkbox').hide();

				if (!formActionElement) return;

				formActionElement.off('click');

				if (!data) {
					formActionElement.hide();
				} else {

					copyAdElement.show();
					copyAdElement.on('click', function() {
						$('.copy_ad_checkbox:not([data-pk='+contentID+'])').show();
						$('.copy_ad_checkbox[data-pk='+contentID+']').hide();
					});

					$('.copy_ad_checkbox').html('<i class="mdi mdi-checkbox-blank"></i>');
					$('.copy_ad_checkbox').removeClass('checked');

					$('.copy_ad_checkbox:not([data-pk='+contentID+'])').closest('tr').removeClass('selected')
					$('.copy_ad_checkbox[data-pk='+contentID+']').closest('tr').addClass('selected');

					formActionElement.html('<i class="mdi mdi-information-outline"></i>');
					formActionElement.show();

					formActionElement.off('click');
					formActionElement.on('click', function(){
						bootbox.dialog({

							message: "Do you want to save any changes to the Material Instruction and Ads before exiting this screen?<br/>Note: If you do not save, you will lose any changes.",
							title: "Confirmation",
							buttons: {
								success: {
									label: "Yes",
									className: "btn-success",
									callback: function() {

										setTimeout(function(){
											that.nextAction = 'gotoEdit';
											that.nextParam = contentID;
											that.saveForm();
										}, 500);

									}
								},
								danger: {
									label: "No",
									className: "btn-danger",
									callback: function() {
										that.loadContentDetails(contentID, function(){
											that._saveMaterialInstructionNavigation(that.content_id);
										});
									}
								},
								main: {
									label: "Cancel",
									className: "btn-default",
									callback: function() {

									}
								}
							}
						});
					});


				}

			});

		});
	});

	$('.material_ad_action_detail').each(function() {
		
		$(this).off('click').on('click', function(){

			var contentID = $(this).data('pk');
			
			if (contentID == null) return;
			
			bootbox.dialog({
				
				  message: "Do you want to save any changes to the Material Instruction and Ads before exiting this screen?<br/>Note: If you do not save, you will lose any changes.",
				  title: "Confirmation",
				  buttons: {
				    success: {
				      label: "Yes",
				      className: "btn-success",
				      callback: function() {
				    	  
				    	 setTimeout(function(){
				    		 that.nextAction = 'gotoEdit';
				    		 that.nextParam = contentID;
				    		 that.saveForm();
				    	 }, 500);
				    	 
				      }
				    },
				    danger: {
				      label: "No",
				      className: "btn-danger",
				      callback: function() {
				    	  that.loadContentDetails(contentID, function(){
				    		  that._saveMaterialInstructionNavigation(that.content_id);
				    	  });
				      }
				    }
				  }
			});
		
		});
	});
	
	
	$('.material_sync_icon').each(function(){
		
		$(this).off('click').on('click', function(){
			
			var contentID = $(this).data('pk');
			var contentDateID = $(this).data('dateid');
			
			var selfElement = $(this).find('span').first();
			
			if (contentID == null) return;
			
			var subContentObj = that.getSubContentObj(contentID, contentDateID);
			
			if (!subContentObj) return;
			
			$.ajax ( 
				{
					url: "/content/syncSubContent",
					type: "post",
					data: {
						"content_id" : contentID,
						"parent_id"	: that.content_id,
						"content_sync" : subContentObj.content_sync ? "0" : "1",
						"content_date_id" : contentDateID
					},
					dataType: "json",
					success: function( resp ) {
						if (resp.code == 0 && resp.data) {
							var newSyncMode = parseAsInt(resp.data.content_sync);
							that.updateSubContentValue(contentID, contentDateID, "content_sync", newSyncMode);
							if (newSyncMode) {
								selfElement.removeClass('deactive');
								selfElement.addClass('enabled-black');
							} else {
								selfElement.addClass('deactive');
								selfElement.removeClass('enabled-black');
							}
						}
					}
				}
			).fail ( function () {
				
								
			}).always( function () {
				
			});
			
		});
		
	});
	
	
}



ContentForm.prototype.onDrawAudioUploadListTable = function() {
	
	var that = this;
	$('#audio-upload-list-table tr').off('click').on('click', function(){

		var contentID = findAudioAdContentID($(this));
		
		if (contentID == null || contentID == 0 || contentID == '0') return;
		
		that.loadContentDetails(contentID, function(){
			that._saveOtherSearchNavigation();
		});
		
	});
	
}

ContentForm.prototype.onDrawPreviewTagListTable = function() {

}

ContentForm.prototype.refreshAudioBulkUploadTable = function() {
	
	if (this.audioFiles == undefined) {
		this.audioFiles = new Array();
	}
	
	for (var index in this.audioFiles) {
		this.audioFiles[index].generateHTMLContents();
	}
	
	this.audioBulkUploadTableObj.clear().draw();
	this.audioBulkUploadTableObj.rows.add(this.audioFiles);
	this.audioBulkUploadTableObj.draw();
	
	$(window).resize();
	
	this.attachEventListenersForAudioFilesTableRows();
}

ContentForm.prototype.addAudioFileRow = function(val) {
	
	this.audioFiles.push(val);
	val.generateHTMLContents();
	
	if (this.audioBulkUploadTableObj) {
		this.audioBulkUploadTableObj.row.add(val).draw();
	}
	
	$(window).resize();
	
	this.attachEventListenersForAudioFilesTableRows();
	
}

ContentForm.prototype.updateAudioFileRow = function(val, id, row) {
	for (var index in this.audioFiles) {
		if (this.audioFiles[index].id == id) {
			this.audioFiles[index] = val;
			this.audioFiles[index].generateHTMLContents();
			this.audioBulkUploadTableObj.row(row).data(this.audioFiles[index]).draw();
			break;
		}
	}
}

ContentForm.prototype.removeAudioFileRow = function(row, id) {
	
	for (var index in this.audioFiles) {
		if (this.audioFiles[index].id == id) {
			this.audioFiles.splice(index, 1);
			break;
		}
	}
	
	this.audioBulkUploadTableObj.row(row).remove().draw();
	
}

ContentForm.prototype.attachEventListenersForAudioFilesTableRows = function() {
	
	var that = this;
	
	$('#audio-upload-list-table_wrapper .audio-file-row-play').each(function(){
		
		$(this).off('click').on('click', function(e){
			
				var event =  e || window.event;
	
				if (event.stopPropagation) {
					event.stopPropagation();
				} else {
					event.cancelBubble = true;	
				}	
		
				var subcontentId = $(this).data('pk');
			
				var newWindow = window.open('/content/playAttachment/' + subcontentId, "audioplay", "height=100,width=500");
				
				if (window.focus) {newWindow.focus()}
			
			});
			
	});
	
	
	$('#audio-upload-list-table_wrapper .audio-file-row-remove').each(function(){
		
		$(this).off('click').on('click', function(e){
			
				var event =  e || window.event;
	
				if (event.stopPropagation) {
					event.stopPropagation();
				} else {
					event.cancelBubble = true;	
				}	
		
				var attachmentID = $(this).data('pk');
			
				var row = $(this).parent().parent().parent();
				
				bootbox.confirm("Are you sure you want to delete this Audio file?", function(result){
					
					if (result) {
					
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/removeAttachment",
								type: "post",
								data: {
									"attachment_id" : attachmentID
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.code == 0) {
										
										that.removeAudioFileRow(row, attachmentID);
																				
									} else {
										$('.saveProgress').show().html('Remove Failed. ' + resp.msg).css('color', 'red');
										setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
									}
								}
							}
						).fail ( function () {

							$('.saveProgress').show().html('Remove Failed. Network error.').css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
							
						}).always( function () {
							
							hideTableLoader();
						});
				
					}
				});
			
			});
			
	});
	
	
	$('#audio-upload-list-table_wrapper .audio-file-row-create-ad').each(function(){
		
		$(this).off('click').on('click', function(e){
			
				var event =  e || window.event;
	
				if (event.stopPropagation) {
					event.stopPropagation();
				} else {
					event.cancelBubble = true;	
				}	
		
				var attachmentID = $(this).data('pk');
			
				var row = $(this).parent().parent().parent();
				
				bootbox.confirm("Are you sure you want to create new Ad from this audio file?", function(result){
					
					if (result) {
					
						showTableLoader();
						
						$.ajax ( 
							{
								url: "/content/createAdFromAudio",
								type: "post",
								data: {
									"attachment_id" : attachmentID
								},
								dataType: "json",
								success: function( resp ) {
									if (resp.code == 0 && resp.data) {
										
										var newAudioFile = new ContentAudioModel(resp.data.attachment_id);
										
										newAudioFile.loadDataFromJson(resp.data);
										
										that.updateAudioFileRow(newAudioFile, attachmentID, row);
																														
									} else {
										$('.saveProgress').show().html('Creation Failed. ' + resp.msg).css('color', 'red');
										setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
									}
								}
							}
						).fail ( function () {

							$('.saveProgress').show().html('Creation Failed. Network error.').css('color', 'red');
							setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
							
						}).always( function () {
							
							hideTableLoader();
						});
				
					}
				});
			
			});
			
	});
	
}


ContentForm.prototype.showDailyLogStatistics = function(statistics, statistics_unique) {
	
	var categoryList = [ContentTypeIDOfAd, ContentTypeIDOfPromotion, ContentTypeIDOfTalk, ContentTypeIDOfNews, ContentTypeIDOfMusic, 100, 0];
	
	for (var index in categoryList) {
		var category = categoryList[index];
		
		$('#preview_analytics_' + category + ' .tag-count').html('');
		$('#preview_analytics_' + category + ' .content-type-status').html('');
		$('#preview_analytics_' + category + ' .tag-missing-count').html('');
		

		if (statistics[category]) {
			
			var completeCount = parseAsInt(statistics[category].complete);
			var incompleteCount = parseAsInt(statistics[category].incomplete);
			var totalCount = completeCount + incompleteCount;
			
			$('#preview_analytics_' + category + ' .tag-count').html(totalCount);
			
			if (incompleteCount > 0) {
				$('#preview_analytics_' + category + ' .content-type-status').html('<i class="mdi mdi-alert-circle error-red"></i>');
				if (category != 0) {
					$('#preview_analytics_' + category + ' .tag-missing-count').html(incompleteCount + ' spots');
				} else {
					var percent = (totalCount == 0 ? 100 : Math.floor(completeCount * 100 / totalCount));
					$('#preview_analytics_' + category + ' .tag-missing-count').html(percent + '% complete');
				}
			} else {
				$('#preview_analytics_' + category + ' .content-type-status').html('<i class="mdi mdi-checkbox-marked-circle success-green"></i>');
				$('#preview_analytics_' + category + ' .tag-missing-count').html('');
			}

			//Unique counts, e.g. ads with distinct zetta ids, songs with distinct titles, promos with distinct adkeys
			if (statistics_unique[category]) {

				incompleteCount = parseAsInt(statistics_unique[category].incomplete);

				if (incompleteCount > 0) {
					$('#preview_analytics_' + category + ' .tag-missing-count-unique').html(incompleteCount + ' unique items');
				} else {
					$('#preview_analytics_' + category + ' .tag-missing-count-unique').html('');
				}
			}
		}
		
	}	
	
	
}


ContentForm.prototype._saveDailyLogSearchNavigation = function() {
	var nav = new ContentNavigationInfo('Daily Log');
	
	nav.formMode = 'search';
	nav.content_content_type_id = $('#content_content_type_id').val();
	
	this.pushNavigation(nav);
}

ContentForm.prototype._saveMaterialInstructionNavigation = function(id) {
	
	var nav = new ContentNavigationInfo('Material Instruction');
	
	nav.formMode = 'edit';
	nav.content_content_id = id;
	
	this.pushNavigation(nav);
	
}


ContentForm.prototype._saveOtherSearchNavigation = function() {
	var nav = new ContentNavigationInfo('Search');
	
	nav.formMode = 'search';
	nav.content_content_type_id = $('#content_content_type_id').val();
	nav.preserveForm = true;
	
	this.pushNavigation(nav);
}

ContentForm.prototype._saveOnAirNavigation = function() {
	
	var nav = new ContentNavigationInfo('OnAir');
	
	nav.formMode = 'url';
	nav.url = '/content/air';
	
	this.pushNavigation(nav);
	
}

ContentForm.prototype.prepopulateClientInfo = function() {
	
	var that = this;
	
	$.ajax ( 
		{
			url: "/content/client/byname",
			type: "post",
			dataType: "json",
			data: {
				"client_name" : $('#content_client').val()
			},
			success: function( resp ) {
				if (resp.code === 0 && resp.data) {
					var clientData = resp.data;
					
					$('#content_who').val(parseAsString(clientData.who));
					
					$('#content_product').val(parseAsString(clientData.product_name));
					$('#content_product2').val(parseAsString(clientData.product_name));
					
					
					$('#content_contact').val(parseAsString(clientData.content_contact));
					$('#content_email').val(parseAsString(clientData.content_email));
					$('#content_phone').val(parseAsString(clientData.content_phone));
										
					$('#content_map_address').val(parseAsString(clientData.map_address1));
					
					$('#content_agency_id').val(parseAsInt(clientData.content_agency_id));
					$('#content_manager_user_id').val(parseAsInt(clientData.content_manager_user_id));
					$('#content_manager_user_id2').val(parseAsInt(clientData.content_manager_user_id));
										
					if (clientData.logo_attachment) {
						that.AttachmentLogo1.resetWithObjectValue( clientData.logo_attachment);
					} else {
						that.AttachmentLogo1.resetWithObjectValue( {"type" : "logo"} );
					}
				}
			}
		}
	).fail ( function () {
		
	}).always( function () {
		
	});
		
}


var ContentAttachment = function(type, wrapper_id, dropzone_id, uploadURL, previewElementID) {
	this.type = type;
	this.wrapper_id = wrapper_id;
	this.wrapper = document.getElementById(wrapper_id);
	this.uploadURL = uploadURL;
	this.dropZoneElementID = dropzone_id;
	this.previewElementId = previewElementID;
	
	this.dropZone = null; 
	
	this.fileSelected = false;
	this.selectedAttachmentId = 0;
	
	this.videoChecked = false;
	
	this.additionalPostInfo = false;
	
	var that = this;
	
	if (type == 'image') {
		this.videoCheckBox = $('#' + wrapper_id).find('.attachment-desc').find('input.video-checkbox').first();
		this.videoTextBox = $('#' + wrapper_id).find('.attachment-desc').find('input.video-textbox').first();
		
		this.videoCheckBox.on("click", function(){
			that.checkVideoBox($(this).is(':checked'));
		});
		
	} else {
		this.videoCheckBox = null;
		this.videoTextBox = null;
	}
	
	$('#' + this.previewElementId).find('a.attachment-remove-link').first().on('click', function() {
		that.removeCurrentFile();
	});
	
	if (type == 'image' || type == 'logo') {
		
		$('#' + this.previewElementId).find('img.attachment-preview-image').first().off('click').on('click', function() {
			that.alterCurrentImage();
		});
		
	}
	
	
	if (type == 'audio') {
		
		$('#' + this.previewElementId).find('span.attachment-audio-preview').first().on('click', function() {
			
			window.open('/content/playAttachment/' + that.selectedAttachmentId, "_blank");
			
		});
		
	}
	
	this.checkVideoBox(false);
	
	this.setupDropZone();
}


ContentAttachment.prototype.alterCurrentImage = function() {
	
	if (this.selectedAttachmentId == 0) return;
	
	var that = this;
	
	showLoading();
	
	$.ajax ( 
		{
			url: "/content/attachment/imageInfo/" + that.selectedAttachmentId,
			type: "get",
			dataType: "json",
			success: function( resp ) {
				if (resp.code == 0 && resp.data) {
					
					var showBlur = that.type == 'image' ? true : false;
					
					openImageEditor(resp.data.url, function(imageEditorResult) {
						console.log(imageEditorResult);
						hideImageEditor();
						
						if (!imageEditorResult) {
							
						} else {
							
							showLoading();
							
							$.ajax ( 
									{
										url: "/content/attachment/updateImageInfo",
										type: "post",
										data:{
											attachmentId: that.selectedAttachmentId,
											additionalImageInfo: JSON.stringify(imageEditorResult) 
										},
										dataType: "json",
										success: function( resp ) {
											if (resp.code == 0 && resp.data) {
												that.loadImagePreview(resp.data.url);
											} else {
												$('.saveProgress').show().html('Error. ' + resp.msg).css('color', 'red');

												setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
											}
										}
									}
								).fail ( function () {
									$('.saveProgress').show().html('Network Error. Unable to update image.').css('color', 'red');

									setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
									
								}).always( function () {
									
									hideLoading();
								});
							
						}
						
					}, showBlur, resp.data.meta);
					
				} else {
					$('.saveProgress').show().html('Error. ' + resp.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Network Error. Unable to load content details').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
		
	}).always( function () {
		
		hideLoading();
	});
	
}

ContentAttachment.prototype.resetWithObjectValue = function(attachment) {
	
	if (!attachment || attachment == null) return;
	
	this.type = attachment.type;
	this.selectedAttachmentId = attachment.content_attachment_id == undefined ? 0 : attachment.content_attachment_id;
	
	if (this.selectedAttachmentId) {
		this.fileSelected = true;
	} else {
		this.fileSelected = false;
	}
	
	if (this.type == 'image') {
		this.checkVideoBox(false);
		this.videoCheckBox.prop('checked', false);
	} else if (this.type == 'video') {
		this.checkVideoBox(true);
		this.videoTextBox.val(attachment.url);
		this.videoCheckBox.prop('checked', true);
	}
	
	
	if (this.fileSelected && this.type != 'video') {
		
		$('#' + this.previewElementId).show();
		if (this.type == 'image' || this.type == 'logo') {
			//$('#' + this.previewElementId).find('img.attachment-preview-image').attr('src', attachment.url);
			this.loadImagePreview(attachment.url);
		}
		$('#' + this.previewElementId).find('span.attachment-filename').html(attachment.filename);
		
		
	} else {
		$('#' + this.previewElementId).hide();
	}
}

ContentAttachment.prototype.checkVideoBox = function(checked) {
	
	if (checked) {
		if (this.videoTextBox) this.videoTextBox.show();
		if (this.fileSelected) {
			$('#' + this.previewElementId).hide();
		}
	} else {
		if (this.videoTextBox) this.videoTextBox.hide();
		/*if (this.fileSelected) {
			$('#' + this.previewElementId).show();
		}*/
	}
	
	this.videoChecked = checked;
}

ContentAttachment.prototype.removeCurrentFile = function() {
	
	var that = this;
	
	bootbox.confirm("Are you sure to remove this file? <br/>Warning: This action can not be undone.", function(result){
		
		if (result) {
			
			var attachmentID = that.selectedAttachmentId;
			
			that.selectedAttachmentId = 0;
			that.fileSelected = false;
			
			$.ajax ( 
				{
					url: "/content/removeAttachment",
					type: "post",
					dataType: "json",
					data: {
						"attachment_id" : attachmentID
					},
					success: function( resp ) {
						if (resp.code === 0) {
							console.log('remove success');
						} else {
							console.log('remove failed. ' + resp.msg);
						}
					}
				}
			).fail ( function () {
				
			}).always( function () {
				
			});
			
			that.showPreview(false, null);
			
		}
		
	});
	
}

ContentAttachment.prototype.showPreview = function(show, data) {
	
	if (show) {
		$('#' + this.previewElementId).show();
		if (this.type == 'image' || this.type == 'logo') {
			this.loadImagePreview(data.url);
			//$('#' + this.previewElementId).find('img.attachment-preview-image').attr('src', data.url);
		}
		
		this.selectedAttachmentId = data.attachment_id;
		this.fileSelected = true;
		
		$('#' + this.previewElementId).find('span.attachment-filename').html(data.filename);
		
	} else {
		$('#' + this.previewElementId).hide();
	}
	
}

ContentAttachment.prototype.loadImagePreview = function(url) {
	
	var imageElement = $('#' + this.previewElementId).find('img.attachment-preview-image');
	
	if (!imageElement) return;
	
	imageElement.attr('style', '');
	
	imageElement.attr('src', url);
	
	var imageObj = new Image();
	
	imageObj.onload = function(){
		
		var imageWidth = imageObj.width;
		var imageHeight = imageObj.height;
		
		var parentElement = imageElement.parent();
		
		var aspectSizeInfo = getAspectFitSize(parentElement.width(), parentElement.height(), imageWidth, imageHeight);
		
		imageElement.css({
			position: 'absolute',
			left: aspectSizeInfo.left + 'px',
			top: aspectSizeInfo.top + 'px',
			width: aspectSizeInfo.width + 'px',
			height: aspectSizeInfo.height + 'px',
		});
		
	};
	
	imageObj.src = url;
	
}

ContentAttachment.prototype.setupDropZone = function() {
	
	var that = this;
	
	this.dropZone = new Dropzone("div#" + this.dropZoneElementID, {
		url: this.uploadURL,
		method: 'post',
		paramName: 'file',
		createImageThumbnails: false,
		uploadMultiple: false,
		maxFiles: 1,
		addRemoveLinks: true,
		autoProcessQueue: (this.type == 'image' || this.type == 'logo') ? false : true,
		init: function() {
			var self = this;
			
			this.on("complete", function(file) {
				
				self.removeFile(file);
				
				if (file.xhr && file.xhr.response && file.xhr.response != '')
					eval('var responseObj=' + file.xhr.response + ';');
				else
					responseObj = {};
				
				if (responseObj.code === 0) {
					that.showPreview(true, responseObj.data);
					that.checkVideoBox(false);
					if (that.videoCheckBox) that.videoCheckBox.prop('checked', false);
					
					if (that.type == 'audio') {
						$('#content_audio_enabled').prop('checked', true);
					}
					
				} else if (responseObj.code === -1) {
					$('.saveProgress').show().html('Upload Error.' + responseObj.msg).css('color', 'red');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
				}
			});
			
			this.on("sending", function(file, xhr, formData) {
				formData.append('attachment_type', that.type);
				
				if (that.additionalPostInfo) {
					formData.append('additionalImageInfo', JSON.stringify(that.additionalPostInfo));
				}
			});
			
			this.on("maxfilesexceeded", function(){
				$('.saveProgress').show().html('Upload Error. You are allowed to upload only 1 file at a time.').css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			});
			
			this.on("uploadprogress", function(file, percent) {
				console.log('percent: ' + percent);
			});
			
			this.on('addedfile', function(file) {
				
				if (that.type != 'image' && that.type != 'logo') {
					that.additionalPostInfo = false;
					return;
				}
				
				if (FileReader == undefined || FileReader == null) {
					alert('HTML5 is not supported on this browser. Please try another browser.');
					self.removeFile(file);
					return;
				}
								
				var reader = new FileReader();
				reader.onloadend = function() {
					
					var showBlur = that.type == 'image' ? true : false;
					
					openImageEditor(reader.result, function(imageEditorResult) {
						console.log(imageEditorResult);
						hideImageEditor();
						if (!imageEditorResult) {
							self.removeFile(file);
							that.additionalPostInfo = false;
						} else {
							that.additionalPostInfo = imageEditorResult;
							self.processQueue();
						}
					}, showBlur);
				};
				reader.readAsDataURL(file);
				
			});
		}
	});
}




var ContentModel = function(id) {
	
	this.id = id;
	
	this.ad_rec_type = '';
	this.start = '';
	this.end = '';
	this.instructions = '';
	this.what = '';
	this.key = '';
	this.duration = 0;
	this.percent = 0;
	this.audio_enabled = 0;
	this.text_enabled = 0;
	this.is_ready = 0;
	
	this.content_sync = 0;
	this.child_content_date_id = 0;
	
} 

ContentModel.prototype.generateHTMLContents = function() {
	
	this.ad_rec_type_html = '<a href="#" class="material_ad_rec_type" data-type="select" data-name="content_rec_type" data-pk="' + this.id + '" data-value="' + this.ad_rec_type + '" data-dateid="' + this.child_content_date_id + '">' + getAdRecTypeTextByValue(this.ad_rec_type) + '</a>';
	this.start_date_html = '<a href="#" class="material_ad_start_date" data-type="date" data-name="start_date" data-pk="' + this.id + '" data-value="' + this.start + '" data-dateid="' + this.child_content_date_id + '">' + this.start + '</a>';
	this.end_date_html = '<a href="#" class="material_ad_end_date" data-type="date" data-name="end_date" data-pk="' + this.id + '" data-value="' + this.end + '" data-dateid="' + this.child_content_date_id + '">' + this.end + '</a>';
	this.instructions_html = '<a href="#" class="material_ad_instructions twoline-ellipse" data-type="text" data-name="content_instructions" data-pk="' + this.id + '" data-value="' + this.instructions + '" data-dateid="' + this.child_content_date_id + '">' + this.instructions + '</a>';
	this.what_html = '<a href="#" class="material_ad_what twoline-ellipse" data-type="text" data-name="what" data-pk="' + this.id + '" data-value="' + this.what + '" data-dateid="' + this.child_content_date_id + '">' + this.what + '</a>';
	this.key_html = '<a href="#" class="material_ad_key" data-type="text" data-name="ad_key" data-pk="' + this.id + '" data-value="' + this.key + '" data-dateid="' + this.child_content_date_id + '">' + this.key + '</a>';
	this.duration_html = '<a href="#" class="material_ad_duration" data-name="ad_length" data-type="select" data-pk="' + this.id + '" data-value="' + this.duration + '" data-dateid="' + this.child_content_date_id + '">' + getDurationTextByValue(this.duration) + '</a>';
	this.percent_html = '<a href="#" class="material_ad_percent" data-name="content_percent" data-type="select" data-pk="' + this.id + '" data-value="' + this.percent + '" data-dateid="' + this.child_content_date_id + '">' + getPercentTextByValue(this.percent) + '</a>';
	
	if (this.audio_enabled == 0) {
		this.audio_enabled_html = '<span class="check-mark disabled"></span>';
	} else {
		this.audio_enabled_html = '<span class="check-mark enabled"></span>';
	}
	
	if (this.text_enabled == 0) {
		this.text_enabled_html = '<span class="check-mark disabled"></span>';
	} else {
		this.text_enabled_html = '<span class="check-mark enabled"></span>';
	}
	
	if (this.is_ready == 0) {
		this.is_ready_html = '<i class="mdi mdi-information disabled"></span>';
	} else {
		this.is_ready_html = '<i class="mdi mdi-checkbox-marked-circle enabled"></span>';
	}

	//if (this.content_sync) {
	//	this.content_sync_html = '<a href="javascript:void(0)" class="material_sync_icon" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"><span class="check-mark enabled-black"></span></a>';
	//} else {
	//	this.content_sync_html = '<a href="javascript:void(0)" class="material_sync_icon" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"><span class="check-mark deactive"></span></a>';
	//}

	this.preview_button_html = '<a href="javascript:void(0)" class="material_ad_show_preview" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"><i class="mdi mdi-eye"></i></a>';
	this.copy_ad_checkbox_html = '<a href="javascript:void(0)" style="display:none;" class="copy_ad_checkbox" data-toggle="tooltip" title="Copy currently open ad into this ad" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"><i class="mdi mdi-checkbox-blank"></i></a>';
	$('[data-toggle="tooltip"]').tooltip();

	this.action_html = '<div class="material_ad_action_container"><a href="javascript:void(0)" class="material_ad_action_remove" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"><i class="mdi mdi-close" data-pk="' + this.id + '" data-dateid="' + this.child_content_date_id + '"></i></a>';
} 

var ContentAudioModel = function(id) {
	this.id = id;

	this.content_id = 0;
	this.who = "";
	this.what = "";
	this.adKey = "";
	this.filename = "";
	this.entered = "";
}


ContentAudioModel.prototype.loadDataFromJson = function(data) {
	
	this.filename = data.filename;
	
	if (data.content && data.content != null) {
		this.content_id = data.content.id;
		this.who = data.content.who;
		this.what = data.content.what;
		this.adKey = data.content.ad_key;
		
		this.text_enabled = parseAsInt(data.content.text_enabled);
		this.image_enabled = parseAsInt(data.content.image_enabled);
		this.action_enabled = parseAsInt(data.content.action_enabled);
		this.is_ready = parseAsInt(data.content.is_ready);
		
		
	} else {
		this.content_id = 0;
		this.who = "";
		this.what = "";
		this.adKey = "";
		
	}
	
	this.entered = parseAsString(data.uploaded);
	
} 

ContentAudioModel.prototype.generateHTMLContents = function() {
	
	if (this.id != 0 && this.id != null) {
		this.play_html = '<i class="mdi mdi-play-circle audio-file-row-play" data-pk="' + this.id + '" data-contentid="' + this.content_id + '"></i>';
	} else {
		this.play_html = "";
	}
	

	if (this.content_id != 0 && this.content_id != undefined && this.content_id != null) {
		this.who_html = this.who;
		this.what_html = this.what;
		this.adKey_html = this.adKey;
		
		if (this.text_enabled == 0) {
			this.text_enabled_html = '<span class="check-mark disabled"></span>';
		} else {
			this.text_enabled_html = '<span class="check-mark enabled"></span>';
		}
		
		if (this.image_enabled == 0) {
			this.image_enabled_html = '<span class="check-mark disabled"></span>';
		} else {
			this.image_enabled_html = '<span class="check-mark enabled"></span>';
		}
		
		if (this.action_enabled == 0) {
			this.action_enabled_html = '<span class="check-mark disabled"></span>';
		} else {
			this.action_enabled_html = '<span class="check-mark enabled"></span>';
		}
		
		if (this.is_ready == 0) {
			this.is_ready_html = '<i class="mdi mdi-information disabled"></span>';
		} else {
			this.is_ready_html = '<i class="mdi mdi-checkbox-marked-circle enabled"></span>';
		}
	
		
		this.action_html = '<div class="audio_file_action_container"><a href="javascript:void(0)" class="audio-file-row-remove" data-pk="' + this.id + '"><i class="mdi mdi-close" data-pk="' + this.id + '"></i></a></div>';
		
	} else {
		this.who_html = '<span class="error">No entry found</span>';
		this.what_html = '<span class="error">No entry found</span>';
		this.adKey_html = '<span class="error">No entry found</span>';
		
		this.text_enabled_html = '<span class="check-mark deactive"></span>';
		this.image_enabled_html = '<span class="check-mark deactive"></span>';
		this.action_enabled_html = '<span class="check-mark deactive"></span>';
		this.is_ready_html = '<i class="mdi mdi-information deactive"></span>';
		
		this.action_html = '<div class="audio_file_action_container"><a href="javascript:void(0)" class="audio-file-row-remove" data-pk="' + this.id + '"><i class="mdi mdi-close" data-pk="' + this.id + '"></i></a>&nbsp;&nbsp;<a href="javascript:void(0)" class="audio-file-row-create-ad" data-pk="' + this.id + '"><i class="mdi mdi-plus"></i></a></div>';
	}
	
}


var PreviewTagModel = function(id) {
	this.id = id;
	
	this.tag_timestamp = 0;
	
	this.who = '';
	this.what = '';
	this.adkey = '';
	this.tag_duration = 0;
	this.hasConnectData = 0;
	this.audio_enabled = 0;
	this.text_enabled = 0;
	this.image_enabled = 0;
	this.action_enabled = 0;
	this.is_ready = 0;
	this.cart = '';
	this.zettaid = '';
	
	this.content_type_id = 0;
	this.content_color = '';
	
	this.connect_content_id = 0;
	this.is_client_found = true;
	
	this.filename = '';
} 

PreviewTagModel.prototype.loadDataFromJson = function(data) {
	this.id = parseAsInt(data.id);
	this.tag_timestamp = parseAsInt(data.tag_timestamp);
	this.adkey = parseAsString(data.adkey);
	this.who = parseAsString(data.who);
	this.what = parseAsString(data.what);
	this.tag_duration = parseAsInt(data.tag_duration);
	this.hasConnectData = parseAsInt(data.hasConnectData);
	this.audio_enabled = parseAsInt(data.audio_enabled);
	this.text_enabled = parseAsInt(data.text_enabled);
	this.image_enabled = parseAsInt(data.image_enabled);
	this.action_enabled = parseAsInt(data.action_enabled);
	this.is_ready = parseAsInt(data.is_ready);
	this.cart = parseAsString(data.cart);
	this.zettaid = parseAsString(data.zettaid);
	
	this.content_color = parseAsString(data.content_color);
	this.content_type_id = parseAsInt(data.content_type_id);
	
	this.connect_content_id = parseAsInt(data.connect_content_id);

	if (data.is_client_found!==null) {
		this.is_client_found = parseAsBool(data.is_client_found);
	}
	
	this.filename = parseAsString(data.filename);
}

PreviewTagModel.prototype.generateHTMLContentForRow = function() {	
	
	this.time_html = moment(new Date(this.tag_timestamp)).tz(GLOBAL.STATION_TIMEZONE).format('HH:mm:ss');
	this.content_type_html = '<span data-content-type="' + this.content_type_id + '" class="check-mark" data-tagid="' + this.id + '" style="background-color: ' + this.content_color + '"></span>';
	this.who_html = '<span id="previewtag_who_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="who_cell_span">' + this.who + '</span>';
	this.what_html = '<span id="previewtag_what_' + this.id + '" data-tag-content-id="' + this.connect_content_id + '" class="what_cell_span" '+ (this.what ? '>' : 'style="color:red;">') + (this.what ? this.what : 'Missing Headline') + '</span>';
	this.adKey_html = this.hasConnectData ? this.adkey : '<span class="error-red">' + this.adkey + '</span>';
	this.duration_html = getDurationString(this.tag_duration);
	
	if (this.hasConnectData) {
		if (this.audio_enabled) {
			this.audio_enabled_html = '<span id="audio_check" class="check-mark enabled"></span>';
		} else {
			this.audio_enabled_html = '<span id="audio_check" class="check-mark disabled"></span>';
		}
	} else if (this.filename && this.filename != '' ) {
		this.audio_enabled_html = '<span id="audio_check" class="check-mark enabled"></span>';
	} else {
		this.audio_enabled_html = '<span id="audio_check" class="check-mark deactive"></span>';
	}
	
	//this.audio_enabled_html = this.hasConnectData ? (this.audio_enabled ? '<span class="check-mark enabled"></span>' : '<span class="check-mark disabled"></span>') : '<span class="check-mark deactive"></span>'; 
	this.text_enabled_html = this.hasConnectData ? (this.text_enabled ? '<span id="text_check" class="check-mark enabled"></span>' : '<span id="text_check" class="check-mark disabled"></span>') : '<span class="check-mark deactive"></span>';
	this.image_enabled_html = this.hasConnectData ? (this.image_enabled ? '<span id="image_check" class="check-mark enabled"></span>' : '<span id="image_check" class="check-mark disabled"></span>') : '<span class="check-mark deactive"></span>';
	this.action_enabled_html = this.hasConnectData ? (this.action_enabled ? '<span id="action_check" class="check-mark enabled"></span>' : '<span id="action_check" class="check-mark disabled"></span>') : '<span class="check-mark deactive"></span>';
	
	if(this.hasConnectData) {
		if(this.is_ready == 1) {
			this.is_ready_html = '<i class="mdi mdi-checkbox-marked-circle success-green ready_cell_i" data-tag-content-id="' + this.connect_content_id + '"></i>';
		}
		else if(this.is_ready == 2) {
			this.is_ready_html = '<i class="mdi mdi-alert-box warning-orange ready_cell_i" data-tag-content-id="' + this.connect_content_id + '"></i>';
		}
		else {
			this.is_ready_html = '<i class="mdi mdi-alert-circle error-red ready_cell_i" data-tag-content-id="' + this.connect_content_id + '"></i>';
		}
	} else {
		this.is_ready_html = '<span class="check-mark deactive"></span>';
	}
	
	this.cart_html = this.cart;
	this.zettaid_html = this.zettaid;
	
	// this.add_action_html = '';
	//
	// if ((this.content_type_id == ContentTypeIDOfAd || this.content_type_id == ContentTypeIDOfPromotion) && !this.hasConnectData && this.adkey) {
	// 	this.add_action_html = '<i class="mdi mdi-plus add-content-btn" style="font-size: 20px"></i>';
	// }
}





var ConnectClientModel = function(id) {
	this.id = id;
	
	this.client_name = '';
	this.trading_name = '';
	this.product_name = '';
	this.client_executive = '';
	
	this.text_enabled = 0;
	this.image_enabled = 0;
	this.logo_enabled = 0;
	this.is_ready = 0;
} 

ConnectClientModel.prototype.loadDataFromJson = function(data) {

	this.id = parseAsInt(data.id);

	this.client_name = parseAsString(data.client_name);
	this.trading_name = parseAsString(data.trading_name);
	this.product_name = parseAsString(data.product_name);
	this.client_executive =  parseAsString(data.client_executive);
	
	this.text_enabled = parseAsInt(data.text_enabled);
	this.image_enabled = parseAsInt(data.image_enabled);
	this.logo_enabled = parseAsInt(data.logo_enabled);
	this.is_ready = parseAsInt(data.is_ready);
}

ConnectClientModel.prototype.generateHTMLContentForRow = function() {	
	
	this.client_name_html = this.client_name + '<i data-pk="' + this.id + '"></i>';
	this.trading_name_html = '<span data-tag-content-id = ' + this.id + ' class = "who_cell_span">' + this.trading_name + '</span>';
	this.product_name_html = this.product_name;
	this.client_executive_html = this.client_executive;
	
	this.logo_enabled_html = this.logo_enabled ? '<span class="check-mark enabled logo_cell_span" data-tag-content-id="' + this.id + '"></span>' : '<span class="check-mark disabled logo_cell_span" data-tag-content-id="' + this.id + '"></span>'; 
	this.text_enabled_html = this.text_enabled ? '<span class="check-mark enabled text_cell_span" data-tag-content-id="' + this.id + '"></span>' : '<span class="check-mark disabled text_cell_span" data-tag-content-id="' + this.id + '"></span>';
	this.image_enabled_html = this.image_enabled ? '<span class="check-mark enabled image_cell_span" data-tag-content-id="' + this.id + '"></span>' : '<span class="check-mark disabled image_cell_span" data-tag-content-id="' + this.id + '"></span>';
	this.is_ready_html = this.is_ready ? '<span class="check-mark enabled ready_cell_span" data-tag-content-id="' + this.id + '"></span>' : '<span class="check-mark disabled ready_cell_span" data-tag-content-id="' + this.id + '"></span>';
	
}



var ContentNavigationInfo = function(title) {
	this.title = title;
	this.formMode = '';
}


function showGritterMsg(title, text, type) {
	
	$.gritter.add({
		title: title,
		text: text
	});
	
}

function showTableLoader() {
	$('#content_table_loader').removeClass('hide');
}

function hideTableLoader() {
	$('#content_table_loader').addClass('hide');
}

function findTagIDOfRow(row) {
	var iElement = row.find('td:nth-child(2) span').first();
	if (iElement == undefined || iElement == null) return null;
	return iElement.data('tagid');
}

function findContentIDOfRow(row) {
	var iElement = row.find('td').last().find('i').first();
	if (iElement == undefined || iElement == null) return null;
	return iElement.data('pk');
}

function findContentSubTypeIDOfRow(row) {
	var iElement = row.find('td').first();
	if (iElement == undefined || iElement == null) return null;
	return iElement.html();
}

function findAudioAdContentID(row) {
	var iElement = row.find('td').first().find('i').first();
	if (iElement == undefined || iElement == null) return null;
	return iElement.data('contentid');
}


function findClientIDOfRow(row) {
	var iElement = row.find('td').first().find('i').first();
	if (iElement == undefined || iElement == null) return null;
	return iElement.data('pk');
}

function isStationPrivate() {
	if (!StationInfo) return false;
	var stationPrivate = parseAsInt(StationInfo.is_private);
	if (stationPrivate == 0) return false;
	return true;
}

//Mimics the functionality of the fillClientInfo on the clientInfo page, except for filling out the form fields
function fillClientInfo(clientID, onComplete) {

	$.ajax(
		{
			url:'/content/getClientInfo/' + clientID,
			type:'get',
			dataType:'json'
		}
	).done(function (resp) {

			if (resp.code === 0) {
				var client = resp.data;
				contentData = client;

				contentFormObj.previewFormObj.renderPreviewClientInformation(client, function (resp) {

					console.log(contentData);
					//Update table row check marks
					if(contentData.logo_enabled) {
						$('.logo_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('disabled').addClass('enabled');
					} else {
						$('.logo_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('enabled').addClass('disabled');
					}

					if(contentData.text_enabled) {
						$('.text_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('disabled').addClass('enabled');
					} else {
						$('.text_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('enabled').addClass('disabled');
					}

					if(contentData.image_enabled) {
						$('.image_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('disabled').addClass('enabled');
					} else {
						$('.image_cell_span[data-tag-content-id=' + contentData.client_id + ']').removeClass('enabled').addClass('disabled');
					}

					if(onComplete) {
						onComplete(resp);
					}

				});
			} else {
				$('.saveProgress').show().html('Can\'t Load Client Info. ' + resp.msg).css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			}
		});
}
//Hacky solution to trigger an event when an element is hidden
//(function ($) {
//	$.each(['show', 'hide'], function (i, ev) {
//		var el = $.fn[ev];
//		$.fn[ev] = function () {
//			this.trigger(ev);
//			return el.apply(this, arguments);
//		};
//	});
//})(jQuery);
//
//$(document).ready(function() {
//	if($('#clients-list-table_wrapper').is(':visible')) {
//		page = 'clientInfo';
//	} else {
//		page = null;
//	}
//
//	$('#clients-list-table_wrapper').on('hide', function() {
//		page = null;
//	});
//
//	$('#clients-list-table_wrapper').on('show', function() {
//		page = 'clientInfo';
//	});
//})
var contentID;

function saveAd(onComplete) {
	console.log("saveAd");
	console.log(contentData);
	$.ajax(
		{
			url:'/content/save',
			type:'post',
			dataType:'json',
			data:{
				"content_id" : contentID ? contentID : '',
				"content_type_id" : getContentTypeIdOfAd(),
				//"content_subtype_id" : $('#content_subtype_id').val(),
				//"content_rec_type" : $('#content_rec_type').val(),
				"ad_length" : contentData.ad_length,
				"content_client" : contentData.client_name,
				"content_product" : contentData.product_name,
				"ad_key" : contentData.adkey,
				"action_id" : contentData.action_id ? contentData.action_id : 0,
				"action_param" : contentData.action_params,
				"who" : contentData.who,
				"what" : contentData.what,
				"more" : contentData.more,
				"attachments" : contentData.attachments,
				"map_address1" : contentData.map ? contentData.map.address : ''

			}
		}
	).done(function (resp) {
			if (resp.code === 0) {
				console.log('save success');

				$('.saveProgress').show().html('Success. All content has been saved successfully!').css('color', 'green');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

				contentID = resp.data.content_id;

				//Update form with new values resp.client
				contentFormObj.previewFormObj.renderPreviewInfo('content', resp.data.content_id, onComplete, 'ad');

			} else {
				$('.saveProgress').show().html('Save Error. ' + resp.msg).css('color', 'red');
				setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
			}
		}).fail(function (resp) {
			console.log('complete failure');
			$('.saveProgress').show().html('Save Error. ' + resp.msg).css('color', 'red');
			setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

		});
}

function updateTradingNameEditable() {
	$.ajax (
		{
			url: "/content/tradingNameList",
			type: "get",
			dataType: "json",
			success: function( resp ) {
				if (resp.code === 0 && resp.data) {
					tradingNameList = resp.data;
					//console.log(tradingNameList);
					$('#mobilepreview_who_editlink').editable("option", "typeahead", {source:tradingNameList});
					$('#mobilepreview_who_editlink').on('save', function(e, params) {
						prepopulateClientInfo('trading', params.newValue);
					});
				}
			}
		}
	).fail ( function () {


	}).always( function () {

		});
}

function prepopulateClientInfo (by, name) {

	if(!name) return;

	var that = this;
	var url = "/content/client/byname";

	if(by == 'trading') {
		url = "/content/client/bytradingname";
	}

	$.ajax (
		{
			url: url,
			type: "post",
			dataType: "json",
			data: {
				"client_name" : name,
				"who" : name
			},
			success: function( resp ) {
				if (resp.code === 0 && resp.data) {
					var clientData = resp.data;
					$.ajax(
						{
							url:'/content/copyClientToAd',
							type:'post',
							dataType:'json',
							data : {
								"client_id" : clientData.id,
								"ad_id" : contentData.id ? contentData.id : ''
							}
						}
					).done(function (resp) {
							if(resp.code == 0) {
								contentID = resp.data.content.id;
								preview.renderPreviewInfo('content', contentID, function () {
									updateTradingNameEditable();
									saveAd();
								}, 'ad');
							} else {

								$('.saveProgress').show().html('Error. Couldn\'t update ad information').css('color', 'red');
								setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
							}
						});
				} else {
					$('.saveProgress').show().html('This client is not in the AirShr Client list. We recommend you add their details on the Client Info Page').css('color', 'yellow');
					setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
					saveAd();
				}
			}
		}
	).fail ( function () {

		$('.saveProgress').show().html('Error. Could not load client details').css('color', 'red');
		setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

		$('#mobilepreview_who_editlink').editable("option", "typeahead", {source:tradingNameList});
	}).always( function () {

		});

}