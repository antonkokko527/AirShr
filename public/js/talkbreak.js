var preview = null;
$(document).ready(function () {
	
	$('#content_content_type_id').on("change", function() {
        var contentType = $('#content_content_type_id').val();
        document.location = '/content?initialContentTypeID=' + encodeURIComponent(contentType) + '&initialFormMode=search';
    });
    
    $('#goBackLinkContainer .goBackLink').off('click').on('click', function() {
    	document.location = '/content?initialContentTypeID=' + encodeURIComponent(ContentTypeIDOfTalk) + '&initialFormMode=search';
	});
	
    preview = new MobilePreviewForm('mobilepreview_slider_container');
    preview.renderPreviewInfo('content', contentID);
});

$(window).bind('beforeunload', function (e) {

    var isEmpty = true;
    if(contentData.who !== null || contentData.what !== null || contentData.more !== null ||
        contentData.attachments.length > 0 || contentData.action_params.length > 0 || contentData.action_id != 0 ||
        contentData.map.lat != 0 || contentData.map.lng != 0 || contentData.map.address !== null || contentData.is_competition || contentData.is_vote) {
        isEmpty = false;
    }

    if(!contentData.is_ready && !isEmpty) {
        return 'The content is not ready to AirShr. Are you sure you wish to leave the page?';
    }

    if(isEmpty) {
        $.ajax(
            {
                url : '/content/removeContent',
                type : 'post',
                async : false,
                data : {
                    "pk" : contentID,
                    "content_type" : 'content'
                }
            }

        ).done(function () {
                console.log('deleted');
                $('.saveProgress').show().html('Success. The content has been deleted successfully').css('color', 'green');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
            });
    }
});

$('#content_btn_save').click(function () {
    //Hack. Don't let the user click this button if modals are open
    if($('body').hasClass('modal-open')) {
        $('.saveProgress').show().html('Please save and close any open edit windows before saving').css('color', 'yellow');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
        return false;
    }
    saved = true;
    preview.renderPreviewInfo('content', contentID);
    $('.editableform').editable().submit();
    $('.saveProgress').show().html('Success. The content has been saved successfully').css('color', 'green');
    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
});

$('#content_btn_remove').click(function () {
    if(!deleted) {
        bootbox.confirm('Are you sure you want to remove this content?', function (result) {
            if (result) {

                $.ajax(
                    {
                        url: '/content/removeContent',
                        type : 'post',
                        data: {
                            "pk": contentID,
                            "content_type": 'content'
                        }
                    }
                ).done(function () {
                        $('.saveProgress').show().html('Success. The content has been deleted successfully').css('color', 'green');
                        setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                        deleted = true;
                        preview._resetFormData();
                    });

            }
        });
    } else {
        $('.saveProgress').show().html('The content has already been deleted').css('color', 'yellow');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
    }
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

                            $('.saveProgress').show().html('Success. The content has been copied successfully').css('color', 'green');
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                            preview.renderPreviewInfo('content', resp.data.contentID);

                        } else {

                            $('.saveProgress').show().html('Copy Failed. ' + resp.msg).css('color', 'red');
                            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);

                            hideLoading();
                        }
                    }
                }
            ).fail ( function () {
                
                $('.saveProgress').show().html('Copy Failed. Network error').css('color', 'red');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);


                hideLoading();

            }).always( function () {


                });

        }

    });

});


//---- Voting and competitiion
function setCompetition () {
    if(contentData.is_vote) {
        //Unset vote
        setVote();
    }
    $.ajax (
        {
            url: "/content/setCompetition",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.id
            }

        }
    ).done( function( resp ) {
            if (resp.code === 0) {
                var content = resp.data.content;
                if(content.is_competition == 1) {
                    contentData.is_competition = 1;
                    $('.saveProgress').show().html('Success. The content is now set as a competition').css('color', 'green');
                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                    $('#competition_icon').attr('fill', '#008800');
                    $('#vote_button').hide('slow');
                }
                else {
                    contentData.is_competition = 0;
                    $('.saveProgress').show().html('Success. The content is no longer a competition').css('color', 'green');
                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);

                    $('#competition_icon').attr('fill', '#9B9B9B');
                    $('#vote_button').show('slow');
                }
            } else {
                console.log('error');
                console.log(resp);
            }
        }).fail(function(resp) {
            console.log('error');
            $('.saveProgress').show().html('Error. An error has occurred while trying to make changes. Please try again').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
            console.log(resp);
        });
}

function setVote () {
    if(contentData.is_competition) {
        //Unset competition
        setCompetition();
    }
    $.ajax (
        {
            url: "/content/setVote",
            type: "post",
            dataType: "json",
            data: {
                "id" : contentData.id
            }

        }
    ).done( function( resp ) {
            if (resp.code === 0) {
                var content = resp.data.content;
                if(content.is_vote == 1) {
                    contentData.is_vote = 1
                    ;$('.saveProgress').show().html('Success. The content is now set as a vote').css('color', 'green');
                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                    $('#vote_icon').attr('stroke', '#543DED');
                    preview.renderVote(content.id);
                    $('#competition_button').hide('slow');
                }
                else {
                    contentData.is_vote = 0;
                    $('.saveProgress').show().html('Success. The content is no longer a vote').css('color', 'green');
                    setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
                    $('#vote_icon').attr('stroke', '#9B9B9B');
                    preview.renderPreviewInfo('content', content.id);
                    $('#competition_button').show('slow');
                }
            } else {
                console.log('error');
                console.log(resp);
            }
        }).fail(function(resp) {
            console.log('error');
            $('.saveProgress').show().html('Error. An error has occurred while trying to make changes. Please try again').css('color', 'red');
            setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
            console.log(resp);
        });
}