$(document).ready(function() {

    $('.button_api_recipes').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        button.text('Cargando...');

        var url = $(this).data('url');
        var data = {
            short_description: $('textarea[name="short_description"]').val()
        };
        $.post(url, data, function(response) {
            if (response.description) {
                $('input[name="title_page"]').val(response.title_page);
                $('textarea[name="meta_description"]').val(response.meta_description);
                $('textarea[name="short_description"]').val(response.short_description);
                var textareaId = $('textarea[name="description"]').attr('id');
                CKEDITOR.instances[textareaId].setData(response.description);
            }
        }).always(function() {
            button.text(oldLabel);
        });
    });

    $('.button_api_posts').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        button.text('Cargando...');

        var url = $(this).data('url');
        var data = {
            short_description: $('textarea[name="short_description"]').val(),
            description: $('textarea[name="description"]').val()
        };
        $.post(url, data, function(response) {
            if (response.title_page) {
                $('input[name="title_page"]').val(response.title_page);
                $('textarea[name="meta_description"]').val(response.meta_description);
                $('textarea[name="short_description"]').val(response.short_description);
            }
        }).always(function() {
            button.text(oldLabel);
        });
    });

});
