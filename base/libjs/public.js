$(function() {
    activateBanners();
    activateMenu();
    activateSortable();
    activateConfirm();
    activateModal();
    activateUrlReplace();
});
$(window).on('load', function() {
    removeLoader();
});
$(window).on('resize', function() {});
$(window).on('scroll', function() {});

function removeLoader() {
    $('.body_content_loader').remove();
}

function activateBanners() {
    if ($('.banners').length > 0) {
        $('.banner').hide();
        $('.banner').first().show();
        if ($('.banner').length <= 1) {
            $('.banners_controls').hide();
        }
        var changeBanner = function(index) {
            $('.banner').fadeOut();
            $('.banner:eq(' + index + ')').fadeIn();
        }
        var changeBannerAutomatic = function() {
            var bannerIndex = $('.banner:visible').first().index();
            bannerIndex = (bannerIndex >= $('.banner').length - 1) ? 0 : bannerIndex + 1;
            changeBanner(bannerIndex);
        }
        var bannerLoop = setInterval(changeBannerAutomatic, 10000);
        $(document).on('click tap', '.banners_control_left', function(evt) {
            clearInterval(bannerLoop);
            var bannerIndex = $('.banner:visible').first().index();
            bannerIndex = (bannerIndex <= 0) ? $('.banner').length - 1 : bannerIndex - 1;
            changeBanner(bannerIndex);
        });
        $(document).on('click tap', '.banners_control_right', function(evt) {
            clearInterval(bannerLoop);
            var bannerIndex = $('.banner:visible').first().index();
            bannerIndex = (bannerIndex >= $('.banner').length - 1) ? 0 : bannerIndex + 1;
            changeBanner(bannerIndex);
        });
    }
}

function activateMenu() {
    $(document).on('click tap', '.menu_trigger', function(evt) {
        $('.menu_all').toggleClass('menu_all_open');
    });
}

function activateConfirm() {
    $(document).on('click tap', '.confirm', function(evt) {
        return window.confirm($(this).data('confirm'));
    });
}

function activateSortable() {
    $('.sortable_list').each(function(index, ele) {
        var url = $(this).data('url');
        $(ele).sortable({
            handle: '.sortable_handle',
            update: function() {
                console.log($(ele).children().toArray().map(item => $(item).data('id')));
                $.post(url, {
                    'new_order[]': $(ele).children().toArray().map(item => $(item).data('id'))
                });
            }
        });
    });
}

function activateModal() {
    $(document).on('click', '#modal_background', function(event) {
        $('#modal').remove();
    });
    $(document).on('click', '[data-modal]', function(event) {
        event.stopImmediatePropagation();
        $.ajax($(this).data('modal')).done(function(response) {
            if (response.status && response.status == 'OK') {
                var modal = '<div id="modal"><div id="modal_background"></div><div id="modal_inside">' + response.html + '</div></div>';
                $('#modal').remove();
                $(modal).appendTo(document.body);
            }
            if (response.status && response.status == 'NOTCONNECTED' && response.url) {
                window.location.href = response.url;
            }
        });
    });
}

function activateUrlReplace() {
    $(document).on('click', '[data-urlreplace]', function(event) {
        event.stopImmediatePropagation();
        var eleContainer = $(this);
        eleContainer.css({
            'opacity': '0.2',
            'pointer-events': 'none'
        });
        $.ajax($(this).data('urlreplace')).done(function(response) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            if (response && response.html) {
                eleContainer.replaceWith(response.html)
            }
        }).fail(function(event) {
            eleContainer.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
            alert('Error');
        });
    });
}
