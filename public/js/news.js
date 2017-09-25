var content_type_id;
var preview = new MobilePreviewForm('mobilepreview_slider_container');

$(document).ready(function () {
    $('#content_content_type_id').on("change", function() {
        var contentType = $('#content_content_type_id').val();
        document.location = '/content?initialContentTypeID=' + encodeURIComponent(contentType) + '&initialFormMode=search';
    });

    content_type_id = ContentTypeIDOfNews;
    fillNewsData();
});

$('#content_type_id').on('change', function() {
    content_type_id = $('#content_type_id').val();
    console.log(content_type_id);
    fillNewsData();
});

function fillNewsData() {

    $.ajax(
        {
            url:'/content/getNews/' + content_type_id,
            type:'get',
            dataType:'json'
        }
    ).done(function(resp) {
        var contentID = resp.data.content_id;
        preview.renderPreviewInfo('content', contentID);
    }).fail(function(resp) {
        $('.saveProgress').show().html('Error.').css('color', 'red');
        setTimeout(function() { $('.saveProgress').fadeOut(); }, 6000);
    });
}