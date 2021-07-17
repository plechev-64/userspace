/* global tinyMCE */

var USPOptionsControl = {
    getId: function (e) {
        return jQuery(e).attr('type') == 'radio' && jQuery(e).is(":checked") ? jQuery(e).data('slug') : jQuery(e).attr('id');
    },
    showChildrens: function (parentId, parentValue) {

        var childrenBox = jQuery('[data-parent="' + parentId + '"][data-parent-value="' + parentValue + '"]');

        if (!childrenBox.length)
            return false;

        childrenBox.show();

        if (childrenBox.hasClass('usp-parent-field')) {

            childrenBox.find("input, select").each(function () {

                USPOptionsControl.showChildrens(USPOptionsControl.getId(this), jQuery(this).val());

            });
        }

    },
    hideChildrens: function (parentId) {

        var childrenBox = jQuery('[data-parent="' + parentId + '"]');

        childrenBox.hide();

        if (childrenBox.hasClass('usp-parent-field')) {

            childrenBox.find("input, select").each(function () {

                USPOptionsControl.hideChildrens(USPOptionsControl.getId(this));

            });
        }
    }

};

jQuery(function ($) {
    /* show children fields */
    $('.usp-parent-field:not(.usp-children-field)').find('input, select').each(function () {
        USPOptionsControl.showChildrens(USPOptionsControl.getId(this), $(this).val());
    });

    $('.usp-parent-field select, .usp-parent-field input').change(function () {
        var el = $(this);

        if (jQuery(this).hasClass('switch-field')) {
            el = jQuery(this).siblings('.switch-field-hidden');
        }

        USPOptionsControl.hideChildrens(USPOptionsControl.getId(el));
        USPOptionsControl.showChildrens(USPOptionsControl.getId(el), $(el).val());
    });
});

/* show/hide extend settings */
function usp_enable_extend_options(e) {
    var options = jQuery('.usp-options .extend-options');

    if (jQuery(e).hasClass('usp-toggle-extend-show')) {
        options.hide();
        jQuery(e).removeClass('usp-toggle-extend-show');

        jQuery.cookie('usp_extends', 0);
    } else {
        options.show();
        jQuery(e).addClass('usp-toggle-extend-show');

        jQuery.cookie('usp_extends', 1);
    }
}

function usp_update_options() {

    usp_preloader_show(jQuery('.usp-options'));

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    usp_ajax({
        /*rest: {action: 'usp_update_options'},*/
        data: 'action=usp_update_options&' + jQuery('.usp-options').serialize()
    });

    return false;
}

function usp_get_option_help(elem) {

    var help = jQuery(elem).children('.help-content');
    var title_dialog = jQuery(elem).parents('.usp-option').children('usp-field-title').text();

    var content = help.html();
    help.dialog({
        modal: true,
        dialogClass: 'usp-help-dialog',
        resizable: false,
        minWidth: 400,
        title: title_dialog,
        open: function (e, data) {
            jQuery('.usp-help-dialog .help-content').css({
                'display': 'block',
                'min-height': 'initial'
            });
        },
        close: function (e, data) {
            jQuery(elem).append('<span class="help-content">' + content + '</span>');
        }
    });
}

function usp_onclick_options_label(e) {
    var label = jQuery(e);

    var viewBox = label.data('options');

    if (jQuery('#' + viewBox + '-options-box').hasClass('active'))
        return false;

    jQuery('.usp-options .options-box').removeClass('active');
    jQuery('.usp-options-bttn').removeClass('usp-bttn__active');

    jQuery('#' + viewBox + '-options-box').addClass('active');
    jQuery(e).addClass('usp-bttn__active');

    usp_update_history_url(label.attr('href'));

    jQuery('.usp-options .active-menu-item .usp-bttn__text').text(label.children('span.usp-bttn__text').text());
    jQuery('.usp-options-tabs').removeClass('active-menu');

}

function usp_show_options_menu(e) {
    jQuery('.usp-options-tabs').addClass('active-menu');
}