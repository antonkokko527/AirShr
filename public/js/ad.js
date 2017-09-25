var preview = new MobilePreviewForm('mobilepreview_slider_container');
var productList;
var clientCompanyList;
var tradingNameList;

$(document).ready(function () {



	$('#content_content_type_id').on("change", function() {
        var contentType = $('#content_content_type_id').val();
        document.location = '/content?initialContentTypeID=' + encodeURIComponent(contentType) + '&initialFormMode=search';
    });
    
    $('#goBackLinkContainer .goBackLink').off('click').on('click', function() {
    	document.location = '/content?initialContentTypeID=' + encodeURIComponent(ContentTypeIDOfAd) + '&initialFormMode=search';
	});


   if(contentID == 0) {
       $('#content_btn_remove').hide();
       $('#content_btn_copy').hide();
       updateTradingNameEditable();
   } else {
       $('#content_btn_remove').show();
       $('#content_btn_copy').show();

       preview.renderPreviewInfo('content', contentID, function() {
           $('#content_subtype_id').val(contentData.content_subtype_id);
           $('#content_rec_type').val(contentData.content_rec_type);
           $('#content_ad_length').val(contentData.ad_length);
           $('#content_client').val(contentData.content_client_name);
           $('#content_product').val(contentData.content_product_name);
           $('#content_ad_key').val(contentData.ad_key);
           updateTradingNameEditable();
       }, 'ad');
   }

    $('#content_btn_save').click(function () {

        saveAd();

    });

    $('#content_btn_copy').click( function() {

        bootbox.confirm("Are you sure you want to copy the current content?", function(result){

            if (result) {

                showLoading();

                $.ajax (
                    {
                        url: "/content/copyContent",
                        type: "post",
                        data: {
                            "content_id" : contentID
                        },
                        dataType: "json",
                        success: function( resp ) {
                            if (resp.code == 0 && resp.data) {

                                $('.saveProgress').show().html('Copy Success. Successfully copied content').css('color', 'green');
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                                preview.renderPreviewInfo('content', resp.data.contentID, function() {
                                    $('#content_subtype_id').val(contentData.content_subtype_id);
                                    $('#content_rec_type').val(contentData.content_rec_type);
                                    $('#content_ad_length').val(contentData.ad_length);
                                    $('#content_client').val(contentData.content_client_name);
                                    $('#content_product').val(contentData.content_product_name);
                                    $('#content_ad_key').val(contentData.ad_key);
                                });

                            } else {

                                $('.saveProgress').show().html('Copy Failed. ' + resp.msg ? resp.msg : '').css('color', 'red');
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

    });

    $('#content_btn_remove').on("click", function() {
        if (contentID == 0 || contentID == null || contentID == undefined) return;

        bootbox.confirm("Are you sure to remove the current content?", function(result){

            if (result) {

                showLoading();

                $.ajax (
                    {
                        url: "/content/removeContent",
                        type: "post",
                        data: {
                            "pk" : contentID,
                            "content_type" : contentData.content_type_id
                        },
                        dataType: "json",
                        success: function( resp ) {
                            if (resp.code == 0) {
                                $('.saveProgress').show().html('Success. Content has been removed successfully.').css('color', 'green');
                                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                                preview._resetFormData();

                                $('#content_subtype_id').val(0);
                                $('#content_rec_type').val('');
                                $('#content_ad_length').val(0);
                                $('#content_client').val('');
                                $('#content_product').val('');
                                $('#content_ad_key').val('');

                                $('#content_btn_remove').hide();
                                $('#content_btn_copy').hide();

                            } else {
                                $('.saveProgress').show().html('Remove Failed. ' + resp.msg ? resp.msg : '').css('color', 'red');
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
    });

    updateAutoComplete();
});


$('#content_client').on("keydown", function(e) {
    if(e.keyCode == 13) {
        $('#content_client').next('.typeahead').one('hide', function () {
            prepopulateClientInfo('name', $('#content_client').val());
        })
    }
});

$('#content_client').one("change", function(e) {
    $('#content_client').next('.typeahead').on('click',function(e2) {
        prepopulateClientInfo();
    });
});

function setupAutoComplete(update) {

    if (update) {
        $( "#content_product" ).typeahead().data('typeahead').source = productList;
        $( "#content_client" ).typeahead().data('typeahead').source = clientCompanyList;
    } else {
        $( "#content_product" ).typeahead({
            source: clientProductList
        });
        $( "#content_client" ).typeahead({
            source: clientCompanyList
        });
    }
}

function saveAd(onComplete) {
    $.ajax(
        {
            url:'/content/save',
            type:'post',
            dataType:'json',
            data:{
                "content_id" : contentID ? contentID : '',
                "content_type_id" : getContentTypeIdOfAd(),
                "content_subtype_id" : $('#content_subtype_id').val(),
                "content_rec_type" : $('#content_rec_type').val(),
                "ad_length" : $('#content_ad_length').val(),
                "content_client" : $('#content_client').val(),
                "content_product" : $('#content_product').val(),
                "ad_key" : $('#content_ad_key').val(),
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
                preview.renderPreviewInfo('content', resp.data.content_id, onComplete, 'ad');

                $('#content_btn_remove').show();
                $('#content_btn_copy').show();

            } else {
                $('.saveProgress').show().html('Could not save. ' + resp.msg ? resp.msg : '').css('color', 'red');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

            }
        }).fail(function (resp) {
            console.log('complete failure');
            $('.saveProgress').show().html('Could not save. ' + resp.msg ? resp.msg : '').css('color', 'red');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
        });
}

function updateAutoComplete() {

    $.ajax(
        {
            url: "/content/productList",
            type: "get",
            dataType: "json",
            success: function (resp) {
                if (resp.code === 0 && resp.data) {
                    productList = resp.data;
                    setupAutoComplete(true);
                }
            }
        }
    ).fail(function () {


    }).always(function () {

        });

    $.ajax (
        {
            url: "/content/clientList",
            type: "get",
            dataType: "json",
            success: function( resp ) {
                if (resp.code === 0 && resp.data) {
                    clientCompanyList = resp.data;
                    setupAutoComplete(true);
                }
            }
        }
    ).fail ( function () {


    }).always( function () {

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
                    $('#mobilepreview_who_editlink').editable("option", "typeahead", {source:tradingNameList});
                    $('#mobilepreview_who_editlink').on('save', function(e, params) {
                        prepopulateClientInfo('trading', params.newValue);
                    });
                    setupAutoComplete(true);
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
            }
    }).done(function( resp ) {
        if (resp.code === 0 && resp.data) {
            var clientData = resp.data;
            $.ajax(
                {
                    url:'/content/copyClientToAd',
                    type:'post',
                    dataType:'json',
                    data : {
                        "client_id" : clientData.id,
                        "ad_id" : contentID ? contentID : ''
                    }
                }
            ).done(function (resp) {
                    if(resp.code == 0) {
                        contentID = resp.data.content.id;
                        console.log(contentID);
                        preview.renderPreviewInfo('content', contentID, function () {
                            $('#content_client').val(resp.data.content.client_name);
                            $('#content_product').val(resp.data.content.product_name);
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

    }).fail ( function () {
        $('.saveProgress').show().html('Error. Could not load client details').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
        
        $('#mobilepreview_who_editlink').editable("option", "typeahead", {source:tradingNameList});
    }).always( function () {

        });

}

//Hacky solution to trigger an event when an element is hidden
(function ($) {
    $.each(['show', 'hide'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            this.trigger(ev);
            return el.apply(this, arguments);
        };
    });
})(jQuery);