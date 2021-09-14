$(function() {
    activateFormValidation();
    activateCKEditor();
    activateDeleteFiles();
    activateAutocomplete();
    activateDatePicker();
    activateDragField();
    activateNestedForms();
    activateSortable();
});

function activateFormValidation() {
    $('form').attr('novalidate', true).on('submit', function() {
        var isValid = true;
        $('input:visible,select:visible,textarea:visible', this).each(function() {
            isValid = isValid && this.reportValidity();
            return isValid;
        });
        return isValid;
    })
}

var ckEditors = [];

function activateCKEditor() {
    $('.textarea_ck_editor textarea').each(function(index, ele) {
        if (!$(ele).hasClass('ck_editor_textarea_check')) {
            $(ele).addClass('ck_editor_textarea_check')
            if (!$(ele).attr('id')) {
                $(ele).attr('id', Math.floor(Math.random() * 1000000));
            }
            ClassicEditor.create($(ele)[0]).then(editor => {
                ckEditors[$(ele).attr('id')] = editor;
            }).catch(error => {
                console.error(error);
            });
        }
    });
}

function activateDeleteFiles() {
    /**
     * DELETE an image from an object.
     **/
    $(document).on('click', '.form_fields_image_delete', function(event) {
        event.stopImmediatePropagation();
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            let eleContainer = $(this).parents('.form_fields_image').first();
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            $.ajax($(this).data('url')).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.status == 'OK') {
                    eleContainer.parents('.drag_field_wrapper').first().removeClass('drag_field_wrapper_has_image');
                    eleContainer.remove();
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
    /**
     * DELETE a file from an object.
     **/
    $(document).on('click', '.form_fields_file_delete', function(event) {
        event.stopImmediatePropagation();
        if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
            let eleContainer = $(this).parents('.form_fields_file').first();
            eleContainer.css({
                'opacity': '0.2',
                'pointer-events': 'none'
            });
            $.ajax($(this).data('url')).done(function(response) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                if (response && response.message_error) {
                    alert(response.message_error);
                }
                if (response.status && response.status == 'OK') {
                    eleContainer.remove();
                }
            }).fail(function(event) {
                eleContainer.css({
                    'opacity': '1',
                    'pointer-events': 'auto'
                });
                alert('Error');
            });
        }
    });
}

/**
 * AUTOCOMPLETE for certain elements in a form.
 */
function activateAutocomplete() {
    $('.autocomplete_item input').each(function(index, ele) {
        $(ele).autocomplete({
            minLength: 2,
            source: function(request, response) {
                $.getJSON($(ele).parents('.autocomplete_item').data('url'), {
                    term: split(request.term).pop()
                }, function(data) {
                    response((data && data.results) ? data.results : []);
                });
            },
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                let terms = split(this.value);
                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });
    });
}
/**
 * DATE PICKER for certain elements in a form.
 **/
function activateDatePicker() {
    $('.date_text input').each(function(index, ele) {
        var dateFormatView = 'yy-mm-dd';
        $(ele).datepicker({
            'firstDay': 1,
            'dateFormat': dateFormatView
        });
    });
}

/**
 * Function to activate the drag field.
 **/
function activateDragField() {
    $(document).on('change', '.drag_field_file input[type=file]', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var container = $(this).parents('.form_field').first();
        var file = $(this)[0]['files'][0];
        loadFileField(file, container);
    });
    $(document).on('click', '.drag_field_drag', function(event) {
        event.stopImmediatePropagation();
        $(this).parents('.form_field').first().find('input[type=file]').trigger('click');
    });
    $(document).on('dragleave', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).removeClass('drag_field_drag_selected');
    });
    $(document).on('dragover', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).addClass('drag_field_drag_selected');
    });
    $(document).on('drop', '.drag_field_drag', function(event) {
        event.preventDefault();
        $(this).removeClass('drag_field_drag_selected');
        var file = event.originalEvent.dataTransfer.files[0];
        var container = $(this).parents('.form_field').first();
        loadFileField(file, container);
    });
}
function resizeBase64Img(base64, maxWidth, maxHeight) {
    return new Promise((resolve, reject) => {
        let img = document.createElement("img");
        img.src = base64;
        img.onload = function() {
            var mode = (img.width > img.height) ? 'horizontal' : 'vertical';
            var ratio = (img.width > img.height) ? img.height / img.width : img.width / img.height;
            if (img.width > img.height) {
                var newWidth = Math.ceil((img.width > maxWidth) ? maxWidth : img.width);
                var newHeight = Math.ceil(newWidth * img.height / img.width);
            } else {
                var newHeight = Math.ceil((img.height > maxHeight) ? maxHeight : img.height);
                var newWidth = Math.ceil(newHeight * img.width / img.height);
            }
            var canvas = document.createElement("canvas");
            canvas.width = newWidth;
            canvas.height = newHeight;
            let context = canvas.getContext("2d");
            context.drawImage(img, 0, 0, newWidth, newHeight);
            resolve(canvas.toDataURL());
        }
    });
}
 function loadFileField(file, container) {
    var reader = new FileReader();
    reader.onloadend = function() {
        if (reader.result != '') {
            var containerData = container.find('.drag_field_wrapper');
            if (containerData.data('maxdimensions')) {
                resizeBase64Img(reader.result, containerData.data('maxwidth'), containerData.data('maxheight')).then((result)=>{
                    processFileField(result, file, container);
                });
            } else {
                processFileField(reader.result, file, container);
            }
        }
    };
    reader.readAsDataURL(file);
}
function processFileField(baseString, file, container) {
    var fileInput = container.find('input.filevalue').first();
    var fileInputName = container.find('input.filename').first();
    var fileInputUploaded = container.find('input.filename_uploaded').first();
    var fileInputFile = container.find('input.filename_input').first();
    var imageContainer = container.find('img').first();
    var fileContainer = container.find('.drag_field_file_name').first();
    var loader = container.find('.drag_field_loader').first();
    var loaderBar = container.find('.drag_field_loader_bar').first();
    var loaderMessage = container.find('.drag_field_loader_message').first();
    fileInput.val(baseString);
    fileInputName.val(file.name);
    fileInputFile.val(null);
    if (imageContainer) {
        imageContainer.attr('src', baseString);
        imageContainer.parents('.drag_field_image').show();
    }
    if (fileContainer) {
        fileContainer.find('em').html(file.name);
        fileContainer.show();
    }
    // Start uploading the image
    loaderBar.removeClass('drag_field_loader_bar_loaded');
    loaderMessage.html(loaderMessage.data('messageloading'));
    $.post({
        url: container.data('urluploadtemp'),
        data: {
            "file": baseString,
            "filename": fileInputName.val()
        },
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentage = Math.ceil((evt.loaded / evt.total) * 100);
                    loaderBar.css('width', percentage + '%');
                    loaderMessage.html(loaderMessage.data('messageloading') + ' (' + percentage + ' %)');
                    if (percentage == 100) {
                        loaderMessage.html(loaderMessage.data('messagesaving'));
                        loaderBar.addClass('drag_field_loader_bar_loaded');
                    }
                }
            }, false);
            return xhr;
        }
    }).done(function(response) {
        loaderBar.removeClass('drag_field_loader_bar_loaded');
        if (response.status == 'OK') {
            fileInput.val('');
            fileInputUploaded.val(response.file);
            loaderMessage.html(loaderMessage.data('messagesavedas') + ' : ' + response.filename);
        } else {
            loaderMessage.html(response.message_error || 'Error');
        }
    }).fail(function(event) {
        loaderMessage.html('');
        loaderBar.removeClass('drag_field_loader_bar_loaded');
    });
}

/**
 * NESTED elements in a form.
 */
function activateNestedForms() {
    // Disable multiple forms
    $('.nested_form_field_empty :input').attr('disabled', true);
    // Action to add an element to the form.
    var addFormField = function(container) {
        var newForm = container.find('.nested_form_field_empty');
        var formsContainer = container.find('.nested_form_field_ins');
        var newFormClone = newForm.clone();
        newFormClone.removeClass('nested_form_field_empty');
        newFormClone.addClass('nested_form_field_object');
        newFormClone.find(':input').attr('disabled', false);
        newFormClone.html(newFormClone.html().replace(/\#ID_MULTIPLE#/g, randomString()));
        newFormClone.appendTo(formsContainer);
        $('.field_ord').each(function(index, ele) {
            $(ele).val(index + 1);
        });
        return newFormClone;
    }
    $(document).on('click', '.nested_form_field_add', function(event) {
        event.stopImmediatePropagation();
        var container = $(this).parents('.nested_form_field');
        addFormField(container);
    });
    // Action to add multiple images to the form.
    $(document).on('click', '.nested_form_field_add_multiple', function(event) {
        event.stopImmediatePropagation();
        event.preventDefault();
        var self = $(this);
        var fileInput = self.parents('.nested_form_field_add_multiple_wrapper').first().find('input[type=file]').first();
        fileInput.trigger('click');
    });
    $(document).on('change', '.nested_form_field_add_multiple_wrapper input[type=file]', function(event) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var container = $(this).parents('.nested_form_field');
        var field = $(this).parents('.nested_form_field_add_multiple_wrapper').first().data('field');
        if ($(this)[0]['files']) {
            for (var i=0; i < $(this)[0]['files'].length; i++) {
                var newFormClone = addFormField(container);
                var containerInside = newFormClone.find('.form_field_' + field).first();
                loadFileField($(this)[0]['files'][i], containerInside);
            }
        }
    });
    // Action to delete an element of the form.
    $(document).on('click', '.nested_form_field_delete', function(event) {
        event.stopImmediatePropagation();
        var self = $(this);
        var container = $(this).parents('.nested_form_field_object');
        var actionDelete = $(this).data('url');
        if (!actionDelete) {
            container.remove();
        } else {
            if (!$(this).data('confirm') || window.confirm($(this).data('confirm'))) {
                $.ajax(actionDelete).done(function(response) {
                    container.remove();
                });
            }
        }
    });
}
/**
 * SORT a list of elements.
 */
function activateSortable() {
    // Regular list
    $('.sortable_list .list_content').each(function(index, ele) {
        $(ele).sortable({
            handle: '.icon_handle',
            update: function() {
                var eleContainer = $(ele);
                eleContainer.css({
                    'opacity': '0.2',
                    'pointer-events': 'none'
                });
                var url = $(this).parents('.sortable_list').data('urlsort');
                $.post(url, {
                    'new_order[]': $(ele).find('.line_admin').toArray().map(item => $(item).data('id'))
                }).done(function(response) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    if (response && response.message_error) {
                        alert(response.message_error);
                    }
                    if (!response.status || response.status != 'OK') {
                        $(ele).sortable('cancel');
                    }
                }).fail(function(event) {
                    eleContainer.css({
                        'opacity': '1',
                        'pointer-events': 'auto'
                    });
                    alert('Error');
                });
            }
        });
    });
    // Nested list
    $('.nested_form_field_sortable').each(function(index, ele) {
        var eleContainer = $(ele).parents('.nested_form_field').first();
        $(ele).sortable({
            handle: '.nested_form_field_order',
            update: function() {
                eleContainer.find('.field_ord').each(function(index, ele) {
                    $(ele).val(index + 1);
                });
            }
        });
    });
}

function updateCKEditors() {
    for (var key in ckEditors) {
        ckEditors[key].updateSourceElement();
    }
}

function randomString() {
    return Math.random().toString(36).substring(7);
}

function baseName(string) {
    var base = new String(string).substring(string.lastIndexOf('/') + 1);
    if (base.lastIndexOf(".") != -1) {
        return base.substring(0, base.lastIndexOf("."));
    }
    return base;
}
