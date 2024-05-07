$(document).ready(function() {

    $('.button_api_recipes').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        button.text('Loading...');

        var url = $(this).data('url');
        var data = {
            short_description: $('textarea[name="short_description"]').val()
        };
        $.post(url, data, function(response) {
            if (response.description) {
            $('textarea[name="meta_description"]').val(response.meta_description);
            $('textarea[name="short_description"]').val(response.short_description);
            var textareaId = $('textarea[name="description"]').attr('id');
            CKEDITOR.instances[textareaId].setData(response.description);
            }
        }).always(function() {
            button.text(oldLabel);
        });
    });

});
