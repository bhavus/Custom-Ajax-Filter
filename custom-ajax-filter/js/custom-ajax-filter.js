jQuery(function($) {
    $('#category-filter').change(function() {
        var category = $(this).val();

        $.ajax({
            url: customAjaxFilter.ajaxurl,
            type: 'POST',
            data: {
                action: 'custom_ajax_filter',
                category: category,
            },
            beforeSend: function() {
                // Show loading spinner or message
            },
            success: function(response) {
                $('#filtered-posts-container').html(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });
});
