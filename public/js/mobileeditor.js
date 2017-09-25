
//------------------------
//Editable stuff
$.fn.editable.defaults.mode='inline';

$(document).ready(function () {
    //Set up image dropzone editor
    if(contentMobileFormObj == null && contentMobileImageEditor == null) {
        contentMobileFormObj = new ContentMobileForm();
        contentMobileImageEditor = new ContentImageEditor('image-editor-cropper-div-mobile', 'image-editor-cropper-img-mobile', 'content_btn_img_confirm_mobile', 'content_btn_img_cancel_mobile', 'image_editor_preview_mobile', 'image_editor_preview_banner_mobile', '-mobile');
    } else {
        contentMobileFormObj.onAfterFormCreation();
    }
});

//------------------------
    //IMAGE EDIT STUFF

$('#edit-image-button-div').click(function() {
    //$('#imageModal').modal({backdrop:false});
    $('#imageModal').modalPopover('toggle')
});
$('#edit-image-button-div').hover(function() {
    $('#edit-image-button-div').show()
});
$('.mobilepreview_slider_container').hover(function() {
    $('#edit-image-button-div').show()
}, function() {
    $('#edit-image-button-div').hide()
});

$('#imageModal').hover(function() {$('#edit-image-button-div').show()});

//------------------------
    //ACTION BUTTON EDIT STUFF
$('#actionModal').modalPopover({modalPosition : 'body', placement: 'top', $parent:$('#edit-action-button-div'), backdrop:false});
$('.preview-action-button').hover(function(){$('#edit-action-button-div').show()}, function(){$('#edit-action-button-div').hide()});
$('#actionModal').hover(function() {$('#edit-action-button-div').show()});
$('#edit-action-button-div').hover(function(){$('#edit-action-button-div').show()});

//Client Info page
$('#edit_action_submit').click(function () {
    submitAction();
});

function submitAction() {
    var primaryKey = contentData.id;
    $('#action_loading').show();

    if (typeof page !== 'undefined' && page == 'clientInfo') {
        $.post("/content/saveClientInline", {
            pk: contentData.client_id,
            name: 'action_id',
            value: $('#action_id').val()
        }, function (resp) {
            if (resp.code == 0) {
                $.post("/content/saveClientInline", {
                    pk: contentData.client_id,
                    name: 'action_params',
                    value: $('#action_value').val()
                }, function (resp) {
                    if (resp.code == 0) {
                        //we want to display the updated action button
                        fillClientInfo(resp.data.client_id, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                            $('#actionModal').modalPopover('hide');
                            $('#action_loading').hide();
                        });
                    }
                });
            }
        });
    } else {

        $.post("/content/material/updateAd", {
            pk: primaryKey,
            name: 'action_id',
            value: $('#action_id').val()
        }, function (data) {
            if (data.code == 0) {
                $.post("/content/material/updateAd", {
                    pk: primaryKey,
                    name: 'action_params',
                    value: $('#action_value').val()
                }, function (data) {
                    if (data.code == 0) {
                        //We want to save all the data once a user saves any of the fields on the ad page
                        //we want to display the updated action button
                        //daily log page/material instruction page
                        if (typeof contentFormObj !== 'undefined') {
                            if(typeof page != undefined && page == 'dailyLog') {
                                contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, function () {
                                    $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                    $('#actionModal').modalPopover('hide');
                                    $('#action_loading').hide();

                                    updateDailyLogStats();
                                });
                            } else if(typeof page != undefined && page == 'materialInstruction') {
                                contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id, function () {
                                    $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                    $('#actionModal').modalPopover('hide');
                                    $('#action_loading').hide();
                                });
                            }
                        }
                        //on air page
                        else if (typeof OnAirFormObj !== 'undefined') {
                            OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId, function () {
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $('#actionModal').modalPopover('hide');
                                $('#action_loading').hide();
                            });
                        }
                        //scheduler/ad page
                        else if (typeof preview !== 'undefined') {
                            preview.renderPreviewInfo('content', contentData.id, function () {
                                if (typeof page !== 'undefined' && page == 'ad') {
                                    saveAd(function () {
                                        $('#actionModal').modalPopover('hide');
                                        $('#action_loading').hide();
                                    });
                                    return;
                                }
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $('#actionModal').modalPopover('hide');
                                $('#action_loading').hide();
                            });
                        }
                    }
                });
            }
        });
    }
}

$('#edit-action-button-div').click(function () {
    //$('#actionModal').modal({backdrop:false});

    var selectedActionID;
    if(contentData.action) {
        selectedActionID= actionTypesByLabel[contentData.action.action_label];
    }
    else if (contentData.action_id) {
        selectedActionID = contentData.action_id;
    }
    else {
        selectedActionID = '';
        toggleActionValue(false);
    }


    var actionDropdownHTML = '';

    actionDropdownHTML = ('<select id="action_id" class="form-control input-large" style="width:300px"><option selected disabled value="">Select an action</option>');
    $.each(actionTypesByID, function(index, value) {
        actionDropdownHTML += ('<option value="' + index + '" ' + (index == selectedActionID ? 'selected' : '') + '>' + value + '</option>')
    });
    actionDropdownHTML += ('</select>');
    $('#actionType').html(actionDropdownHTML);

    $('#action_id').on('change', function() {
        toggleActionValue(true);
    });

    var actionValue = '';
    if(contentData.action_params && contentData.action_params.website) {
        actionValue = contentData.action_params.website;
        toggleActionValue(true);
    } else if(contentData.action_params && contentData.action_params.phone) {
        actionValue =  contentData.action_params.phone;
        toggleActionValue(true);
    }

    $('#actionContent').html('<input id="action_value" type="text" class="form-control input-large" name="action_params" placeholder="Action" style="width:300px" value="' + actionValue + '"></input>');
    //$('#mobilepreview_actiontype_editlink').editable('show');
    //$('#mobilepreview_action_editlink').editable('show');

    $('#action_value').on('keydown', function (e) {
        if(e.keyCode == 13) {
            submitAction();
        }
    });

    $('#actionModal').modalPopover('toggle');

});

function toggleActionValue(show) {
    if(show) {
        $('#actionContent').show();
        $('#edit_action_submit').show();
        $('#edit_action_cancel').show();
    } else {
        $('#actionContent').hide();
        $('#edit_action_submit').hide();
        $('#edit_action_cancel').hide();
    }
}


//------------------------
//MAP STUFF
function getMapScript() {
    console.log('getting map script');
    $.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyBUsctJ35AdCaivOqRjz3jfF6ehVlKbm1A&callback=initMap');
}
function setupMapEditableLink() {
    updateInlineURL = (typeof page !== 'undefined' && page == 'clientInfo' ? '/content/saveClientInline' : '/content/material/updateAd');
    $('#mobilepreview_address_editlink').editable(
        {
            type: 'text',
            url: updateInlineURL,
            showbuttons: 'bottom',
            placeholder: 'Address',
            onblur: 'ignore',
            inputclass: 'input-large',
            tpl: "<input type='text' style='width: 320px'>",
            success: function(response, newValue) {
                if (response.code == 0) {
                    //update the map on success
                    //Content page

                    //client info page
                    if (typeof page !== 'undefined' && page == 'clientInfo') {
                        fillClientInfo(response.data.client_id, function() {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });
                    }

                    //daily log/material instruction
                    else if(typeof contentFormObj !== 'undefined') {
                        if(typeof page !== 'undefined' && page == 'dailyLog') {
                            contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, function () {
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $("#mapModal").modalPopover('hide');
                                updateDailyLogStats();
                            });
                        }

                        else if(typeof page !== 'undefined' && page == 'materialInstruction') {
                            contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id, function () {
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');      
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $("#mapModal").modalPopover('hide');
                            });
                        }
                    }
                    //On air page
                    else if(typeof OnAirFormObj !== 'undefined') {
                        OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');             
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });
                    }
                    //Content/Talk show page
                    else if(typeof preview !== 'undefined') {
                        preview.renderPreviewInfo('content', contentData.id, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');     
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });
                    }
                } else {
                    return response.msg;
                }
            }
        }
    );

    $('#mobilepreview_locationsurl_editlink').editable(
        {
            type: 'text',
            url:  updateInlineURL ,
            showbuttons: 'bottom',
            placeholder: '"Find a store" on client\'s website',
            onblur: 'ignore',
            inputclass: 'input-large',
            tpl: "<input type='text' style='width: 320px'>",
            success: function(response, newValue) {
                if (response.code == 0) {
                    //update the map on success
                    //Content page

                    //Client info page
                    if (typeof page !== 'undefined' && page == 'clientInfo') {
                        fillClientInfo(response.data.client_id, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');            
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });

                    }
                    else if(typeof contentFormObj !== 'undefined') {

                        if(typeof page !== 'undefined' && page == 'dailyLog') {
                            contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, function () {
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');  
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $("#mapModal").modalPopover('hide');

                                updateDailyLogStats();
                            });
                        }
                        else if(typeof page !== 'undefined' && page == 'materialInstruction') {
                            contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id, function () {
                                $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');      
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                                $("#mapModal").modalPopover('hide');
                            });
                        }
                    }
                    //On air page
                    else if(typeof OnAirFormObj !== 'undefined') {
                        OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');           
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });
                    }
                    //Content/Talk show page
                    else if(typeof preview !== 'undefined') {
                        preview.renderPreviewInfo('content', contentData.id, function () {
                            $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');  
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);;
                            $("#mapModal").modalPopover('hide');
                        });
                    }
                } else {
                    return response.msg;
                }
            }
        }
    );

    $('#mobilepreview_address_editlink').editable().on('hidden', function () {
        $('#mapModal').modalPopover('hide');
    });
    $('#mobilepreview_locationsurl_editlink').editable().on('hidden', function () {
        $('#mapModal').modalPopover('hide');
    });
}
var map = null;
var marker = null;
function initMap() {
    if (contentData.map && contentData.map.lat) {
        var latLng = {lat: Number(contentData.map.lat), lng: Number(contentData.map.lng)};

        if(map == null) {
            map = new google.maps.Map(document.getElementById('map'));
        }
        if(marker != null) {
            marker.setMap(null);
        }
        marker = new google.maps.Marker({
            position: latLng,
            map: map
        });

        map.setCenter(latLng);
        map.setZoom(13);

        map.panBy(-100,-10);

        google.maps.event.clearListeners(map, 'click');

        map.addListener('click', function() {
            window.open('https://www.google.com.au/maps/place/'+contentData.map.address+'/@'+contentData.map.lat+','+contentData.map.lng+'z/')
        });

    } else {
        $('#map').hide();
    }
}
function initMapEdit() {
    $('#map').hover(function(){$('#edit-address-button-div').show();}, function(){$('#edit-address-button-div').hide();});
    $('.bottom-nav-shape').hover(function(){$('#edit-address-button-div').show();}, function(){$('#edit-address-button-div').hide();});
    $('#edit-address-button-div').hover(function(){$('#edit-address-button-div').show();});
    $('#mapModal').hover(function() {$('#edit-address-button-div').show()});
    $('#mapModal').modalPopover({modalPosition : 'body', placement: 'top', $parent:$('.edit-address-button'), backdrop:false});

    $('.edit-address-button').click(function () {
        if(typeof page !== 'undefined' && page == 'clientInfo') {
            $('#mapAddress').html('<a id="mobilepreview_address_editlink" class="link-editable" data-type="text" data-name="map_address1" data-pk="' + contentData.client_id + '">' + (contentData.map ? parseAsString(contentData.map.address) : 'Empty') + '</a>');
            $('#locationsUrl').html('<a id="mobilepreview_locationsurl_editlink" class="link-editable" data-type="text" data-name="locations_url" data-pk="' + contentData.client_id + '">' + /*(contentData.map ? parseAsString(contentData.map.address) : 'Empty') +*/ '</a>');
        }
        else {
            $('#mapAddress').html('<a id="mobilepreview_address_editlink" class="link-editable" data-type="text" data-name="map_address1" data-pk="' + contentData.id + '">' + (contentData.map ? parseAsString(contentData.map.address) : 'Empty') + '</a>');
            $('#locationsUrl').html('<a id="mobilepreview_locationsurl_editlink" class="link-editable" data-type="text" data-name="locations_url" data-pk="' + contentData.id + '">' + /*(contentData.map ? parseAsString(contentData.map.address) : 'Empty') +*/ '</a>');
        }
        setupMapEditableLink();
        $('#mobilepreview_address_editlink').editable('toggle');
        $('#mobilepreview_locationsurl_editlink').editable('toggle');
        $('#mapModal').modalPopover('show');
    });
}

//$('#location-btns').click( function() {
//    console.log($(this).val());
//    if($('#single-loc').hasClass('active')) {
//        $('#mapAddress').show();
//        $('#locationUrl').hide();
//    } else if ($('#multiple-loc').hasClass('active')) {
//        $('#mapAddress').hide();
//        $('#locationUrl').show();
//    }
//});
$(document).ready( function() {
    $('#mapAddress').show();
    $('#locationUrl').hide();
    $('#single-loc').addClass('active');
})

$('#single-loc').click( function() {
    $('#mapAddress').show();
    $('#locationsUrl').hide();
});

$('#multiple-loc').click( function() {
    $('#mapAddress').hide();
    $('#locationsUrl').show();
});
//------------------------
//Ready button stuff
$('#ready_button').click(function () {
    if(contentData.is_ready == 1) {
        bootbox.confirm("Are you sure you want to stop this item from being AirShr'd?", function(result) {
            if(result && typeof page !== 'undefined' && page == 'clientInfo') {
                readyClient();
            } else if(result) {
                readyContent();
            }
        });
    } else {
        if (typeof page !== 'undefined' && page == 'clientInfo') {
            readyClient();
        }
        else {
            readyContent();
        }
    }
});
function readyClient() {
    $.ajax (
        {
            url: "/content/readyClientInfo",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.client_id
            }

        }
    ).done(function(resp) {
        if (resp.data.content.is_ready == 1) {
            contentData.is_ready = 1;
            $('.saveProgress').show().html('Success. The content is now ready to AirShr!').css('color', 'green');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
            $('#ready_button').html('<i class="mdi mdi-checkbox-marked"></i>').css('color', 'green');
            
            updateReadyToAirShrStatusIcon(contentData.client_id, 1);
        }
        else if (resp.data.content.is_ready == 0) {
            contentData.is_ready = 0;
            $('.saveProgress').show().html('The content has stopped being ready to AirShr').css('color', 'green');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
            $('#ready_button').html('<i class="mdi mdi-checkbox-blank-outline"></i>').css('color', 'red');
            
            updateReadyToAirShrStatusIcon(contentData.client_id, 0);
        }
    })
}


function updateReadyToAirShrStatusIcon(content_id, ready_to_airshr) {
	if (!content_id) return;
		
	if (ready_to_airshr) {
		$('.ready_cell_i[data-tag-content-id="' + content_id + '"]').addClass('success-green');
		$('.ready_cell_i[data-tag-content-id="' + content_id + '"]').removeClass('error-red');
		
		$('.ready_cell_span[data-tag-content-id="' + content_id + '"]').addClass('enabled');
		$('.ready_cell_span[data-tag-content-id="' + content_id + '"]').removeClass('disabled');
		
	} else {
		$('.ready_cell_i[data-tag-content-id="' + content_id + '"]').removeClass('success-green');
		$('.ready_cell_i[data-tag-content-id="' + content_id + '"]').addClass('error-red');
		
		$('.ready_cell_span[data-tag-content-id="' + content_id + '"]').addClass('disabled');
		$('.ready_cell_span[data-tag-content-id="' + content_id + '"]').removeClass('enabled');
	}
	
}

function readyContent () {
	showMobilePreviewLoading();
	
    $.ajax (
        {
            url: "/content/readyContent",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.id
            }

        }
    ).done( function( resp ) {
        if (resp.code === 0) {
            var content = resp.data.content;
            if(content.is_ready == 1) {
                contentData.is_ready = 1;
                //This is for the talk roster screen to update the calendar when we ready a talk show
                if($('#calendar').length) {
                    var events = $("#calendar").fullCalendar( 'clientEvents', contentData.id );
                    $.each(events, function(index, value) {
                        value.is_ready = 1;
                        value.className = "fc-state-highlight";
                    });
                    $('#calendar').fullCalendar( 'rerenderEvents' );
                }
                $('.saveProgress').show().html('Success. The content is now ready to AirShr!').css('color', 'green');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                $('#ready_button').html('<i class="mdi mdi-checkbox-marked"></i>').css('color', 'green');

                updateDailyLogStats();

                updateReadyToAirShrStatusIcon(contentData.id, 1);
            }
            else {
                contentData.is_ready = 0;
                //This is for the talk roster screen to update the calendar when we ready a talk show
                if($('#calendar').length) {
                    var events = $("#calendar").fullCalendar( 'clientEvents', contentData.id );
                    $.each(events, function(index, value) {
                        value.is_ready = 0;
                        value.className= "not-ready fc-state-highlight";

                    });
                    $('#calendar').fullCalendar( 'rerenderEvents' );
                }
                $('.saveProgress').show().html('The content has stopped being ready to AirShr').css('color', 'green');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                $('#ready_button').html('<i class="mdi mdi-checkbox-blank-outline"></i>').css('color', 'red');

                updateDailyLogStats();

                updateReadyToAirShrStatusIcon(contentData.id, 0);
            }
        } else {
            console.log('error');
            console.log(resp);
        }
        hideMobilePreviewLoading();
    }).fail(function(resp) {

        console.log('error');
        $('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
        console.log(resp);

        hideMobilePreviewLoading();

    });
}

//-----------------------
// Daily log stuff
function updateDailyLogStats() {
    if(typeof contentFormObj !== 'undefined') {
        if (typeof page !== 'undefined' && page == 'dailyLog') {

            contentFormObj.updateDailyLogStatistics();

            var tagRow = $('#previewtag_who_'+tagId).parent().parent();

            if(contentData.audio_enabled) {
                tagRow.find('#audio_check').removeClass('disabled').addClass('enabled');
            } else {
                tagRow.find('#audio_check').removeClass('enabled').addClass('disabled');
            }
            if(contentData.action_enabled) {
                tagRow.find('#action_check').removeClass('disabled').addClass('enabled');
            } else {
                tagRow.find('#action_check').removeClass('enabled').addClass('disabled');
            }
            if(contentData.image_enabled) {
                tagRow.find('#image_check').removeClass('disabled').addClass('enabled');
            } else {
                tagRow.find('#image_check').removeClass('enabled').addClass('disabled');
            }
            if(contentData.text_enabled) {
                tagRow.find('#text_check').removeClass('disabled').addClass('enabled');
            } else {
                tagRow.find('#text_check').removeClass('enabled').addClass('disabled');
            }

            tagRow.find('.ready_cell_i').removeClass('mdi-checkbox-marked-circle').removeClass('success-green').removeClass('mdi-alert-box').removeClass('warning-orange').removeClass('mdi-alert-circle').removeClass('error-red');

            if(contentData.is_ready == 1) {
                tagRow.find('.ready_cell_i').addClass('mdi-checkbox-marked-circle').addClass('success-green');
            }
            else if(contentData.is_ready == 2) {
                tagRow.find('.ready_cell_i').addClass('mdi-alert-box').addClass('warning-orange');
            }
            else {
                tagRow.find('.ready_cell_i').addClass('mdi-alert-circle').addClass('error-red');
            }
        }
    }
}

//------------------------
// Image Dropzone Stuff

var uploadURL = '/content/upload';

var contentMobileFormObj = null;
var contentMobileImageEditor = null;

var ContentMobileForm = function() {

    var that = this;

    // left side bar preview
    //this.previewMobileFormObj = new MobilePreviewForm('mobilepreview_slider_container');

    $('.mobileeditor-close-button').off('click').on('click', function() {
        $('#mobilepreview_sidebar').addClass('hidden');

        $('.copy_ad_checkbox').hide(); //Material instruction page for hiding copy ad checkboxes
        $('.copy_ad_checkbox').removeClass('checked');
        $('.copy_ad_checkbox').closest('tr').removeClass('selected');

        that.prevTagTagID = null;
    });

    // navigation
    this.navigationArray = new Array();


    this.AttachmentImage1 = null;
    this.AttachmentImage2 = null;
    this.AttachmentImage3 = null;
    this.AttachmentLogo1 = null;
    this.AttachmentAudio1 = null;

    this.nextAction = '';
    this.nextParam = null;

    $('#content_btn_save_mobile').on("click", function() {
        that.saveForm();
    });

    this.setupDropZone();

    this.onAfterFormCreation();
}

function showGritterMsg(title, text, type) {

    $.gritter.add({
        title: title,
        text: text
    });

}

function showMobilePreviewLoading() {
	$('#mobilepreview_loader').removeClass('hide');
}

function hideMobilePreviewLoading() {
	$('#mobilepreview_loader').addClass('hide');
}

function showLoading() {
    //$('.loading').removeClass('hide');
}

function hideLoading() {
    //$('.loading').addClass('hide');
}

var openImageMobileEditor = function(image, callback, showBlur, presetOptions) {

    if (contentMobileImageEditor == null) return;

    contentMobileImageEditor.initImageEditor(image, callback, showBlur, presetOptions);

    $('#image_editor_overlay_mobile').show();
}

var hideImageMobileEditor = function() {
    $('#image_editor_overlay_mobile').hide();
}

var ContentMobileAttachment = function(type, wrapper_id, dropzone_id, uploadURL, previewElementID) {
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
        this.videoSubmit = $('#' + wrapper_id).find('.attachment-desc').find('button.video-submit').first();

        this.videoCheckBox.on("click", function(){
            that.checkVideoBox($(this).is(':checked'));
        });

        this.videoTextBox.bind('keydown', function(e) {
           if(e.keyCode == 13) {
               if(typeof contentMobileFormObj !== 'undefined' && contentMobileFormObj !== null) {
                   that.checkVideoBox(that.videoCheckBox.is(':checked'));
                   contentMobileFormObj.saveForm(true);
               }
           }
        });

        this.videoSubmit.on('click', function (){
            contentMobileFormObj.saveForm(true);
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

ContentMobileForm.prototype.onAfterFormCreation = function() {

    var that = this;

    if (contentData.id != 0) {

        that.loadContentDetails(contentData.id, function(){

        });

    } else if ( contentData.coverart_id != 0 ) {

    	that.loadContentDetails(contentData.coverart_id, function(){

        });

    }

}

ContentMobileForm.prototype.loadContentDetails = function(contentID, onLoadComplete, contentType) {

    var that = this;

    if (onLoadComplete) {
        onLoadComplete();
    }

    that.populateContentDetails(contentData);

}

ContentMobileForm.prototype.populateContentDetails = function(data) {

    this.content_id = parseAsInt(data.id);
    this.coverart_id = parseAsInt(data.coverart_id);
    
    this.attachments = new Array();

    var imageCount = 0;

    if (data.attachments != undefined && data.attachments != null) {

        for (var index in data.attachments) {
            var attachmentInfo = data.attachments[index];

            if (attachmentInfo.type == 'image' || attachmentInfo.type == 'video') {
                
                if (attachmentInfo.not_editable == 1) {
                	attachmentInfo.type = 'logo';
                	this.attachments[3] = attachmentInfo;
                } else {
                	this.attachments[imageCount] = attachmentInfo;
                    imageCount++;
                }
                
            } else if (attachmentInfo.type == 'logo') {
                this.attachments[3] = attachmentInfo;
            } else if (attachmentInfo.type == 'audio') {
                this.attachments[4] = attachmentInfo;
            }
        }
    }

    this.renderAttachmentFields();

}

ContentMobileForm.prototype.saveForm = function(ignoreConfirm) {

    var that = this;

    this.updateDataFromForm();

    var confirmMsg = "Are you sure to save the current images?";

    //if (this.content_id && this.content_type_id == ContentTypeIDOfMaterialInstruction) {
    //    confirmMsg = "Are you sure to save the current form data? <br/>Warning: Any changes made to the material instruction will overwrite any unique items in the Ads below.";
    //}

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

ContentMobileForm.prototype.updateDataFromForm = function() {

    this.getAttachmentFields();

}

ContentMobileForm.prototype.getAttachmentFields = function() {

    this.attachments = new Array();

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

}

ContentMobileForm.prototype._saveProcess = function() {

    var that = this;

    showLoading();
    console.log(that.coverart_id);
    $.ajax(
        {
            url: "/content/saveImages",
            type: "post",
            dataType: "json",
            data: {
                "content_id": that.content_id,
                "attachments": that.attachments,
                "is_client" : (typeof page !== 'undefined'&& page == 'clientInfo' ? 1 : 0),
                "client_id" : contentData.client_id,
                "coverart_id" : that.coverart_id
            },
            success: function (resp) {
                if (resp.code === 0) {
                    console.log('save success');

                    $('.saveProgress').show().html('Success. The images have been saved successfully!').css('color', 'green');
                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                    //We want to save all the data once a user saves any of the fields on the ad page
                    if(typeof page !== 'undefined' && page == 'ad') {
                        $('.saveProgress').show().html('Remember to click the save button to save all content').css('color', 'yellow');
                        setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                    }

                    if(typeof page !== 'undefined'&& page == 'musicrating' && typeof vm !== 'undefined') {
                        vm.mobilePreview.renderPreviewInfo('coverart', contentData.coverart_id);
                    }
                    //-- Update the mobile previews
                    //Client info page
                    if(typeof page !== 'undefined'&& page == 'clientInfo') {
                        fillClientInfo(contentData.client_id);
                    }
                    //Daily log/Material Instruction page
                    else if(typeof contentFormObj !== 'undefined') {
                        if(typeof page !== 'undefined'&& page == 'dailyLog') {
                            contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, updateDailyLogStats);
                        }

                        else if(typeof page !== 'undefined'&& page == 'materialInstruction') {
                            contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id);
                        }
                    }
                    //On air page
                    else if(typeof OnAirFormObj !== 'undefined') {
                        OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId);
                    }
                    //Content/Talk show page
                    else if(typeof preview !== 'undefined') {
                        preview.renderPreviewInfo('content', contentData.id);
                    }

                } else {
                    console.log('save failed. ' + resp.msg);
                    $('.saveProgress').show().html('Save Error. '+ resp.msg).css('color', 'red');
                }
            }
        }
    ).fail(function (data) {

        $('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
        console.log(data);
    }).always(function () {

            hideLoading();
        });
}

ContentMobileAttachment.prototype.resetWithObjectValue = function(attachment) {

    if (!attachment || attachment == null) return;

    this.type = attachment.type;
    this.selectedAttachmentId = attachment.content_attachment_id == undefined ? 0 : attachment.content_attachment_id;
    
    var notEditable = attachment.not_editable == 1 ? true : false;
    
    if (this.selectedAttachmentId) {
        this.fileSelected = true;
    } else {
        this.fileSelected = false;
    }
    
    if (notEditable) {
    	this.fileSelected = true;
    	$('#' + this.previewElementId).find('a.attachment-remove-link').hide();
    } else {
    	$('#' + this.previewElementId).find('a.attachment-remove-link').show();
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


    } else if(this.fileSelected && this.type == 'video') {
        $('#' + this.previewElementId).show();
        //this.loadVideoPreview(attachment.url);
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
		
		this.loadVideoPreview(video_embed_url);
        
    } else {
        $('#' + this.previewElementId).hide();
    }
}

ContentMobileForm.prototype.renderAttachmentFields = function() {

    //if (this.getCurrentContentTypeSelected() == ContentTypeIDOfClientInfo) {
    //var logoAttachment = this.attachments[0];

    //if (!logoAttachment || logoAttachment.type == 'video' || logoAttachment.type == 'image') logoAttachment = this.attachments[3];

    //} else {
    this.AttachmentImage1.resetWithObjectValue((this.attachments[0] == null || this.attachments[0] == undefined) ? {"type" : "image"} : this.attachments[0]);
    this.AttachmentImage2.resetWithObjectValue((this.attachments[1] == null || this.attachments[1] == undefined) ? {"type" : "image"} : this.attachments[1]);
    this.AttachmentImage3.resetWithObjectValue((this.attachments[2] == null || this.attachments[2] == undefined) ? {"type" : "image"} : this.attachments[2]);

    this.AttachmentLogo1.resetWithObjectValue((this.attachments[3] == null || this.attachments[3] == undefined) ? {"type" : "logo"} : this.attachments[3]);
    //}
}

ContentMobileForm.prototype.setupDropZone = function() {

    this.AttachmentImage1 = new ContentMobileAttachment('image', 'attachment_image1_mobile', 'attachment_image1_drop_mobile', uploadURL, 'attachment_image1_preview_mobile');
    this.AttachmentImage2 = new ContentMobileAttachment('image', 'attachment_image2_mobile', 'attachment_image2_drop_mobile', uploadURL, 'attachment_image2_preview_mobile');
    this.AttachmentImage3 = new ContentMobileAttachment('image', 'attachment_image3_mobile', 'attachment_image3_drop_mobile', uploadURL, 'attachment_image3_preview_mobile');
    this.AttachmentLogo1 = new ContentMobileAttachment('logo', 'attachment_logo_mobile', 'attachment_logo_drop_mobile', uploadURL, 'attachment_logo_preview_mobile');

}

ContentMobileAttachment.prototype.showPreview = function(show, data) {

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

ContentMobileAttachment.prototype.alterCurrentImage = function() {

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

                    openImageMobileEditor(resp.data.url, function(imageEditorResult) {

                        hideImageMobileEditor();

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

                                            //We want to save all the data once a user saves any of the fields on the ad page
                                            if(typeof page !== 'undefined' && page == 'ad') {
                                                $('.saveProgress').show().html('Remember to click the save button to save all content').css('color', 'yellow');
                                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                                            }

                                            //-- Update the mobile previews
                                            //Client info page
                                            if(typeof page !== 'undefined'&& page == 'clientInfo') {
                                                fillClientInfo(contentData.client_id);
                                            }
                                            //Daily log/Material Instruction page
                                            else if(typeof contentFormObj !== 'undefined') {
                                                if(typeof page !== 'undefined'&& page == 'dailyLog') {
                                                    contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, updateDailyLogStats);
                                                }

                                                else if(typeof page !== 'undefined'&& page == 'materialInstruction') {
                                                    contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id);
                                                }
                                            }
                                            //On air page
                                            else if(typeof OnAirFormObj !== 'undefined') {
                                                OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId);
                                            }
                                            //Content/Talk show page
                                            else if(typeof preview !== 'undefined') {
                                                preview.renderPreviewInfo('content', contentData.id);
                                            }

                                        } else {
                                            $('.saveProgress').show().html('Error. An error has occured while trying to make changes. Please try again').css('color', 'red');
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
        $('.saveProgress').show().html('Network Error. Unable to load attachment details.').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

    }).always( function () {

            hideLoading();
        });

}
ContentMobileAttachment.prototype.loadVideoPreview = function(url) {

    var videoElement = $('#' + this.previewElementId).find('iframe.attachment-preview-video');

    var video_embed_url = url;

    //Use YouTube embed url instead of direct link
    /*if(url.indexOf('youtube') >= 0) {
        var start_of_id = url.indexOf('?v=');
        var youtube_id = '';
        video_embed_url = 'https://www.youtube.com/embed/';
        if(start_of_id > 0) {
            start_of_id = start_of_id + 3;
            youtube_id = url.substring(start_of_id);
            video_embed_url = video_embed_url + youtube_id
        } else {
            video_embed_url = url;
        }
    }

    //Use Vimeo embed url instead of direct link
    else if(url.indexOf('vimeo') >= 0 && url.indexOf('player') < 0) {
        var start_of_id = url.indexOf('vimeo.com/');
        console.log(start_of_id);
        video_embed_url = 'https://player.vimeo.com/video/';
        var vimeo_id = '';
        if(start_of_id >= 0) {
            start_of_id = start_of_id + 10;
            vimeo_id = url.substring(start_of_id);
            video_embed_url = video_embed_url + vimeo_id;
        } else {
            video_embed_url = url;
        }
    }*/

    videoElement.attr('src', video_embed_url);
}

ContentMobileAttachment.prototype.loadImagePreview = function(url) {

    var imageElement = $('#' + this.previewElementId).find('img.attachment-preview-image');

    if (!imageElement) return;

    imageElement.attr('style', '');

    imageElement.attr('src', url);

    var imageObj = new Image();

    imageObj.onload = function(){

        var imageWidth = imageObj.width;
        var imageHeight = imageObj.height;

        var parentElement = imageElement.parent();

        //var aspectSizeInfo = getAspectFitSize(parentElement.width(), parentElement.height(), imageWidth, imageHeight);
        var containerWidth = 277;
        var containerHeight = 184;

        var scaleFactor = 1;
        if (imageWidth > containerWidth || imageHeight > containerHeight) {
            scaleFactor = Math.min(containerWidth / imageWidth,  containerHeight / imageHeight);
        }

        imageWidth *= scaleFactor;
        imageHeight *= scaleFactor;

        var left = ( containerWidth - imageWidth ) / 2;
        var top = ( containerHeight - imageHeight ) / 2;


        imageElement.css({
            position: 'absolute',
            left: left + 'px',//aspectSizeInfo.left + 'px',
            top: top + 'px',//aspectSizeInfo.top + 'px',
            width: imageWidth + 'px',
            height: imageHeight + 'px',
        });

    };

    imageObj.src = url;

}

ContentMobileAttachment.prototype.removeCurrentFile = function() {

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
                        "attachment_id" : attachmentID,
                        "client_id" : contentData.client_id
                    },
                    success: function( resp ) {
                        if (resp.code === 0) {
                            console.log('remove success');


                            $('.saveProgress').show().html('Success. The image has been deleted successfully').css('color', 'green');
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                            //-- Update the mobile previews
                            //Client Info search page

                            //Client info page
                            if(typeof page !== 'undefined'&& page == 'clientInfo') {
                                fillClientInfo(contentData.client_id);
                            }
                            //Content Page (Daily Log)/Material Instruction
                            else if(typeof contentFormObj !== 'undefined') {
                                if(typeof page !== 'undefined'&& page == 'dailyLog') {
                                    contentFormObj.previewFormObj.renderPreviewInfo(previewType, tagId, updateDailyLogStats);
                                }
                                else if(typeof page !== 'undefined'&& page == 'materialInstruction') {
                                    contentFormObj.previewFormObj.renderPreviewInfo('content', contentData.id);
                                }
                            }
                            //On air page
                            else if(typeof OnAirFormObj !== 'undefined') {
                                OnAirFormObj.previewForm.renderPreviewInfo(previewType, tagId)
                            }
                            //Content/Talk show page
                            else if(typeof preview !== 'undefined') {
                                preview.renderPreviewInfo('content', contentData.id);
                            }

                            that.videoTextBox.val('');
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

ContentMobileAttachment.prototype.setupDropZone = function() {

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
                    console.log(responseObj);
                    if(typeof contentMobileFormObj !== 'undefined' && contentMobileFormObj !== null) {
                        contentMobileFormObj.saveForm(true);
                        console.log('saving images');
                    }

                } else if (responseObj.code === -1) {
                    $('.saveProgress').show().html('Upload Error. ' + responseObj.msg).css('color', 'red');
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

                    openImageMobileEditor(reader.result, function(imageEditorResult) {
                        hideImageMobileEditor();
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


ContentMobileAttachment.prototype.checkVideoBox = function(checked) {

    if (checked) {
        if (this.videoTextBox) this.videoTextBox.show()
        if(this.videoSubmit) this.videoSubmit.show();
        //if (this.fileSelected) {
        //    $('#' + this.previewElementId).hide();
        //}
    } else {
        if (this.videoTextBox) this.videoTextBox.hide();
        if(this.videoSubmit) this.videoSubmit.hide();
        if (this.fileSelected) {
            $('#' + this.previewElementId).show();
        }
    }

    this.videoChecked = checked;
}