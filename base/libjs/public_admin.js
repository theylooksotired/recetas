$(document).ready(function() {

    $('.button_api_recipes').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        button.text('Cargando...');

        $.post($(this).data('url'), [], function(response) {
            if (response.description) {
                $('input[name="title_page"]').val(response.title_page);
                $('textarea[name="meta_description"]').val(response.meta_description);
                $('textarea[name="short_description"]').val(response.short_description);
                var textareaId = $('textarea[name="description"]').attr('id');
                CKEDITOR.instances[textareaId].setData(response.description);
            }
        }).always(function() {
            resizableTextareas();
            button.text(oldLabel);
        });
    });

    $('.button_api_content').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        var postUrl = $(this).data('url');
        button.text('Cargando...');
        var content = $('textarea[name="recipe_content"]').val();

        $.post(postUrl, { content: content }, function(response) {
            if (response.titulo) {
                $('input[name="title"]').val(response.titulo);
                $('input[name="title_page"]').val(response.tituloPagina);
                $('textarea[name="meta_description"]').val(response.metaDescripcion);
                $('textarea[name="short_description"]').val(response.descripcion);
                var textareaId = $('textarea[name="description"]').attr('id');
                CKEDITOR.instances[textareaId].setData(response.descripcionHtml);
                $('textarea[name="ingredients_raw"]').val(response.ingredientes.join('\n'));
                $('textarea[name="preparation_raw"]').val(response.pasos.join('\n'));
            }
        }).always(function() {
            resizableTextareas();
            button.text(oldLabel);
        });
    });

    $('.button_api_steps').click(function() {
        var button = $(this);
        var oldLabel = button.text();
        button.text('Cargando...');

        $.post($(this).data('url'), [], function(response) {
            if (response.steps) {
                $('textarea[name="preparation_raw"]').val(response.steps);
                $('.nested_form_field_multiple_items_preparation .nested_form_field_ins .nested_form_field_object .nested_form_field_delete').each(function(index, ele) {
                    $.ajax($(ele).data('url'));
                });
                $('.nested_form_field_multiple_items_preparation .nested_form_field_ins .nested_form_field_object').remove();
            }
        }).always(function() {
            resizableTextareas();
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
