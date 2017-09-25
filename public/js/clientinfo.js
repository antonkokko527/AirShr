var preview = new MobilePreviewForm('mobilepreview_slider_container');
var contentData;
var clientProductList;
var agencyList;
var executiveList;

$(document).ready(function() {
    $('#content_content_type_id').on("change", function() {
        var contentType = $('#content_content_type_id').val();
        document.location = '/content?initialContentTypeID=' + encodeURIComponent(contentType) + '&initialFormMode=search';
    });
    
    $('#goBackLinkContainer .goBackLink').off('click').on('click', function() {
    	document.location = '/content?initialContentTypeID=' + encodeURIComponent(ContentTypeIDOfClientInfo) + '&initialFormMode=search';
	});

    $("#agency_name").on('change', function() {
        $.ajax(
            {
                url: '/content/getAgencyDetails',
                type: 'post',
                dataType: 'json',
                data: {
                    "agency_name": $("#agency_name").val()
                }
            }).done(function (resp) {
                console.log(resp);
                if(resp.code == 0 && resp.data) {
                    $("#agency_contact_name").val(resp.data.agency_contact_name);
                    $("#agency_contact_phone").val(resp.data.agency_contact_phone);
                    $("#agency_contact_email").val(resp.data.agency_contact_email);
                }
            })
    });

    $('#client_who').keyup(function(e) {
       $('#mobilepreview_who_editlink').html($('#client_who').val());
    });

    $('.with-warning').keyup(function(e) {
        $(this).addClass('has-error');
        $(this).children('.warning-label').show();
    });

    $('.with-warning').on('change', function(e) {
        $(this).addClass('has-error');
        $(this).children('.warning-label').show();
    });

    $('#content_btn_copy').click(function () {
        $('#copy_client').prop("disabled", false);
        $('#copyClientModal').modal('show');
    });

    $('#copy_client').click(copyClient);

    $('#new_client_name').keydown(function(e) {
        if(e.keyCode == 13) {
            e.preventDefault();
            copyClient();
        }
    })
    
    $('#content_btn_save').click(function () {

        $('.saveProgress').show().html('Saving...').css('color', 'green');

        $.ajax(
            {
                url:'/content/saveClientInfo',
                type:'post',
                dataType:'json',
                data:{
                    "client_id" : clientID ? clientID : '',
                    "content_client" : $('#content_client').val(),
                    "content_product" : $('#content_product').val(),
                    "client_type" : $('#client_type').val(),
                    "who" : $('#client_who').val(),
                    "client_executive" : $("#client_executive").val(),
                    // "client_contact_name" : $("#client_contact_name").val(),
                    // "client_contact_email": $("#client_contact_email").val(),
                    // "client_contact_phone": $("#client_contact_phone").val(),
                    // "agency_name" : $("#agency_name").val(),
                    // "agency_contact_name" : $("#agency_contact_name").val(),
                    // "agency_contact_phone" : $("#agency_contact_phone").val(),
                    // "agency_contact_email" :$("#agency_contact_email").val(),
                    "client_twitter" : $("#client_twitter").val()
                }
            }
        ).done(function (resp) {
            if (resp.code === 0) {
                $('.saveProgress').show().html('Saved').css('color', 'green');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                $('.with-warning').removeClass('has-error');

                $('.warning-label').hide();

                clientID = resp.data.client_id;

                

                //Update form with new values resp.client
                fillClientInfo(resp.data.client_id);
            } else {
                $('.saveProgress').show().html('Could not save. '+ (resp.msg ? resp.msg : 'Please Try Again.')).css('color', 'red');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
            }
        }).fail(function (resp) {
            console.log('complete failure');
            $('.saveProgress').show().html('Could not save. ' + (resp.msg ? resp.msg : 'Please Try Again.')).css('color', 'red');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
        });
    });

    $('#content_btn_remove').click(function () {bootbox.dialog({
        message: "Are you sure you want to delete this client information?",
        title: "Custom title",
        buttons: {
            danger: {
                label: "Yes, I want to delete this client information",
                className: "btn-danger",
                callback: function() {
                    $.ajax (
                    {
                        url: "/content/removeClient",
                        type: "post",
                        data: {
                            "pk" : clientID,
                        },
                        dataType: "json",
                        success: function( resp ) {
                            if (resp.code == 0) {
                                clearClientInfo();

                                $('.saveProgress').show().html('Client information has been removed.').css('color', 'green');
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

    // updateAutoCompleteProductListAndAgencyList();
    updateAutoCompleteProductListAndExecutiveList();

    if(clientID != 0) {
        fillClientInfo(clientID);
    } else {
        $('#mobilepreview_what').html('<div style="color:red">Please click the save button before making changes here</div>');
        $('#content_btn_copy').hide();
    }
});

function copyClient() {
    $('#copy_loading').show();
    $('#copy_client').prop("disabled", true);
    $('.saveProgress').show().html('Copying...').css('color', 'green');

    $.ajax(
        {
            url:'/content/copyClient',
            type:'post',
            dataType:'json',
            data:{
                "client_id" : clientID,
                "new_client_name" : $('#new_client_name').val()
            }
        }
    ).done(function (resp) {
        if (resp.code === 0) {
            $('#copy_loading').hide();
            $('#copyClientModal').modal('hide');

            $('.saveProgress').html('Copied Successfully').css('color', 'green');
            document.location = '/content/clientInfo/' + resp.data.new_client.id;
        } else {
            $('#copy_loading').hide();
            $('#copyClientModal').modal('hide');
            $('.saveProgress').show().html('Copy Error. ' + resp.msg).css('color', 'red');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
        }
    }).fail(function (resp) {
        console.log('complete failure');
        $('.saveProgress').show().html('Copy Error.').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
    });

}

function clearClientInfo () {
    $('#content_client').val('');
    $('#content_product').val('');
    $('#client_executive').val('');
    $('#client_type').val(0);
    $('#direct_form').hide();
    $('#agency_form').hide();
    $('#client_contact_name').val('');
    $('#client_contact_phone').val('');
    $('#client_contact_email').val('');
    $('#agency_name').val('');
    $('#agency_contact_name').val('');
    $('#agency_contact_phone').val('');
    $('#agency_contact_email').val('');
    contentData = null;
    preview._resetFormData();
    clientID = '';
}
function fillClientInfo (clientID, onComplete) {
    $.ajax(
        {
            url:'/content/getClientInfo/' + clientID,
            type:'get',
            dataType:'json'
        }
    ).done(function (resp) {
        if (resp.code === 0) {
            $('#content_btn_copy').show();

            //Update form with new values resp.client
            var client = resp.data;
            $('#content_client').val(client.client_name);
            $('#client_who').val(client.who);
            $('#content_product').val(client.product_name);
            $('#client_executive').val(client.client_executive);
            $('#client_type').val(client.client_type ? client.client_type : 0);
            // if(client.client_type == 'direct')  {
            //     $('#direct_form').show();
            //     $('#agency_form').hide();
            // }
            // else if(client.client_type == 'agency')  {
            //     $('#direct_form').hide();
            //     $('#agency_form').show();
            // }
            // $('#client_contact_name').val(client.client_contact_name);
            // $('#client_contact_phone').val(client.client_contact_phone);
            // $('#client_contact_email').val(client.client_contact_email);
            // $('#agency_name').val(client.agency_name);
            // $('#agency_contact_name').val(client.agency_contact_name);
            // $('#agency_contact_phone').val(client.agency_contact_phone);
            // $('#agency_contact_email').val(client.agency_contact_email);
            if(client.client_twitter.indexOf('@') == 0) {
                $('#client_twitter').val(client.client_twitter.substring(1));
            }
            else {
                $('#client_twitter').val(client.client_twitter);
            }
            contentData = client;
            preview.renderPreviewClientInformation(client, onComplete);

        } else {
            $('.saveProgress').show().html('Can\'t Load Client Info. ' + resp.msg).css('color', 'red');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

        }
    }).fail(function (resp) {
        console.log('complete failure');
        $('.saveProgress').show().html('Can\'t Load Client Info. ' + resp.msg).css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
    });
}

function setupAutoCompleteForProductAndExecutive(update) {

    if (update) {
        $( "#content_product" ).typeahead().data('typeahead').source = clientProductList;
        $( "#client_executive" ).typeahead().data('typeahead').source = executiveList;
    } else {
        $( "#content_product" ).typeahead({
            source: clientProductList,
        });

        $( "#client_executive" ).typeahead({
            source: executiveList
        });
    }

    $( "#content_product" ).on('keyup', function() {
        $(this).parent().addClass('has-error');
        $(this).parent().children('.warning-label').show();
    });
    $( "#client_executive" ).on('keyup', function() {
        $(this).parent().addClass('has-error');
        $(this).parent().children('.warning-label').show();
    });
}
function updateAutoCompleteProductListAndExecutiveList() {

    $.ajax(
        {
            url: "/content/productList",
            type: "get",
            dataType: "json",
            success: function (resp) {
                if (resp.code === 0 && resp.data) {
                    clientProductList = resp.data;
                    setupAutoCompleteForProductAndExecutive(true);
                }
            }
        }
    ).fail(function () {


    }).always(function () {

    });

    $.ajax (
        {
            url: "/content/getClientExecutiveList",
            type: "get",
            dataType: "json",
            success: function( resp ) {
                if (resp.code === 0 && resp.data) {
                    executiveList = resp.data;
                    setupAutoCompleteForProductAndExecutive(true);
                }
            }
        }
    ).fail ( function () {


    }).always( function () {

        });
}

//
// function setupAutoCompleteForProductAndAgency(update) {
//
//     if (update) {
//         $( "#content_product" ).typeahead().data('typeahead').source = clientProductList;
//         $( "#agency_name" ).typeahead().data('typeahead').source = agencyList;
//         $( "#client_executive" ).typeahead().data('typeahead').source = executiveList;
//     } else {
//         $( "#content_product" ).typeahead({
//             source: clientProductList
//         });
//         $( "#agency_name" ).typeahead({
//             source: agencyList
//         });
//         $( "#client_executive" ).typeahead({
//             source: executiveList
//         });
//     }
// }
// function updateAutoCompleteProductListAndAgencyList() {
//
//     $.ajax (
//         {
//             url: "/content/productList",
//             type: "get",
//             dataType: "json",
//             success: function( resp ) {
//                 if (resp.code === 0 && resp.data) {
//                     clientProductList = resp.data;
//                     setupAutoCompleteForProductAndAgency(true);
//                 }
//             }
//         }
//     ).fail ( function () {
//
//
//     }).always( function () {
//
//     });
//
//     $.ajax (
//         {
//             url: "/content/agencyList",
//             type: "get",
//             dataType: "json",
//             success: function( resp ) {
//                 if (resp.code === 0 && resp.data) {
//                     agencyList = resp.data;
//                     setupAutoCompleteForProductAndAgency(true);
//                 }
//             }
//         }
//     ).fail ( function () {
//
//
//     }).always( function () {
//
//     });
//
//     $.ajax (
//         {
//             url: "/content/getClientExecutiveList",
//             type: "get",
//             dataType: "json",
//             success: function( resp ) {
//                 if (resp.code === 0 && resp.data) {
//                     executiveList = resp.data;
//                     setupAutoCompleteForProductAndAgency(true);
//                 }
//             }
//         }
//     ).fail ( function () {
//
//
//     }).always( function () {
//
//         });
// }