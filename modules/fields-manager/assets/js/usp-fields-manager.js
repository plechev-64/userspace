/* global tinyMCE */

var USPManagerFields = {};
var startDefaultbox = 0;

jQuery(function ($) {
    jQuery('.usp-frame').on('change', 'select[name*="[type]"]', function () {
        usp_manager_get_custom_field_options(this);
    });
});

usp_box_default_fields_init();

jQuery(window).scroll(function () {

    usp_box_default_fields_init();

});

function usp_init_manager_fields(props) {

    USPManagerFields = props;

}

function usp_manager_field_switch(e) {
    jQuery(e).parents('.usp-frame__field-header').next('.usp-frame__field-settings').slideToggle();
}

function usp_switch_view_settings_manager_group(e) {
    jQuery(e).parents('.usp-frame__section-settings').next('.usp-frame__section-more-options').slideToggle();
}

function usp_init_manager_sortable() {

    jQuery(".usp-frame .fields-box").sortable({
        connectWith: ".usp-frame .fields-box",
        handle: ".usp-frame__field-control .usp-frame__field-bttn-move",
        cursor: "move",
        placeholder: "ui-sortable-placeholder",
        distance: 15,
        receive: function (ev, ui) {
            /*if ( jQuery( ev.target ).hasClass( "usp-frame__group-fields" ) )
             return true;
             if ( !ui.item.hasClass( "default-field" ) )
             ui.sender.sortable( "cancel" );*/

            if (jQuery(ev.target).hasClass("usp-frame__group-fields")) {

                if (ui.item.hasClass("template-field")) {
                    var now = new Date();
                    ui.item.clone().appendTo(".usp-template-fields");
                    ui.item.html(ui.item.html().replace(new RegExp(ui.item.data('id'), 'g'), 'id' + now.getTime()));
                }

                return true;
            } else if (ui.item.hasClass("template-field")) {
                ui.item.remove();
            }

            if (!jQuery(ev.target).hasClass("usp-frame__default") && ui.item.hasClass("default-field"))
                ui.sender.sortable("cancel");

            if (jQuery(ev.target).hasClass("usp-frame__default") && !ui.item.hasClass("default-field"))
                ui.sender.sortable("cancel");
        }
    });

    var parentGroup;
    jQuery(".usp-frame .usp-frame__section-content").sortable({
        connectWith: ".usp-frame .usp-frame__section-content",
        handle: ".usp-frame__control .usp-frame__group-bttn-move",
        cursor: "move",
        placeholder: "ui-sortable-area-placeholder",
        distance: 15,
        start: function (ev, ui) {
            parentGroup = ui.item.parents('.usp-frame__section');
        },
        stop: function (ev, ui) {
            usp_init_manager_group(ui.item.parents('.usp-frame__section'), true);
            usp_init_manager_group(parentGroup, true);
        }
    });

}

function usp_init_manager_areas_resizable() {

    jQuery(".usp-frame__section").each(function () {

        usp_init_manager_group(jQuery(this));

    });

}

function usp_init_manager_group(group, isDefault) {

    var container = group.find(".usp-frame__section-content");
    var areas = container.children('.usp-frame__group');

    if (isDefault) {
        var defaultPercent = 100 / areas.length;
        areas.css('width', defaultPercent + '%');
        areas.children('.area-width').val(defaultPercent);
    }

    //var minWidth = (container.innerWidth())/5;
    //var maxWidth = container.innerWidth() - minWidth * (areas.length - 1);

    var sibTotalWidth;
    areas.resizable({
        //handles: 'e',
        //minWidth: minWidth,
        //maxWidth: maxWidth,
        start: function (event, ui) {
            sibTotalWidth = ui.originalSize.width + ui.originalElement.next().outerWidth();
            var nextCell = ui.originalElement.next();
            ui.originalElement.addClass('resizable-area');
            nextCell.addClass('resizable-area');
        },
        stop: function (event, ui) {
            var cellPercentWidth = 100 * ui.originalElement.outerWidth(true) / container.innerWidth();
            ui.originalElement.css('width', cellPercentWidth + '%');
            ui.originalElement.children('.area-width').val(Math.round(cellPercentWidth));
            ui.originalElement.removeClass('resizable-area');

            var nextCell = ui.originalElement.next();
            var nextPercentWidth = 100 * nextCell.outerWidth(true) / container.innerWidth();
            nextCell.css('width', nextPercentWidth + '%');
            nextCell.children('.area-width').val(Math.round(nextPercentWidth));
            nextCell.removeClass('resizable-area');
        },
        resize: function (event, ui) {
            ui.originalElement.next().width(sibTotalWidth - ui.size.width);

            var cellPercentWidth = 100 * ui.originalElement.outerWidth(true) / container.innerWidth();
            ui.originalElement.children('.usp-frame__group-width').text(Math.round(cellPercentWidth) + '%');

            var nextCell = ui.originalElement.next();
            var nextPercentWidth = 100 * nextCell.outerWidth(true) / container.innerWidth();

            nextCell.children('.usp-frame__group-width').text(Math.round(nextPercentWidth) + '%');
        }
    });

}

function usp_box_default_fields_init() {

    var manager = jQuery('.usp-frame');
    var box = manager.children('.usp-frame__box-default');

    if (!box.length)
        return false;

    var structureEdit = manager.hasClass('usp-frame-edit') ? true : false;

    var scroll = jQuery(window).scrollTop();

    if (!startDefaultbox) {

        var indent = structureEdit ? -30 : 20;

        if (scroll > box.offset().top + indent) {
            startDefaultbox = box.offset().top + indent;
            if (structureEdit)
                box.next().attr('style', 'margin-top:' + box.outerHeight(true) + 'px');

            box.addClass("fixed");
        }

    } else {

        if (scroll < startDefaultbox) {
            startDefaultbox = 0;
            if (structureEdit)
                box.next().attr('style', 'margin-top:' + 0 + 'px');
            box.removeClass("fixed");
        }

    }

}

function usp_remove_manager_group(textConfirm, e) {

    if (!confirm(textConfirm))
        return false;

    var areasBox = jQuery(e).parents('.usp-frame__section');

    usp_preloader_show(areasBox);

    areasBox.remove();

    return false;

}

function usp_remove_manager_area(textConfirm, e) {

    if (!confirm(textConfirm))
        return false;

    var areaBox = jQuery(e).parents('.usp-frame__group');

    var areasBox = jQuery(e).parents('.usp-frame__section');

    usp_preloader_show(areaBox);

    areaBox.remove();

    var countAreas = areasBox.find('.usp-frame__group').length;

    areasBox.find('.usp-frame__group .usp-frame__control').hide();

    usp_init_manager_group(areasBox, true);

    return false;

}

function usp_manager_get_new_area(e) {

    var areasBox = jQuery(e).parents('.usp-frame__section');

    usp_preloader_show(areasBox);

    usp_ajax({
        data: {
            action: 'usp_manager_get_new_area',
            props: USPManagerFields
        },
        success: function (data) {

            areasBox.children('.usp-frame__section-content').append(data.content);

            usp_init_manager_sortable();

            usp_init_manager_group(areasBox, true);

        }
    });

    return false;
}

function usp_manager_get_new_group(e) {

    var groupsBox = jQuery('.usp-frame__panel');

    usp_preloader_show(groupsBox);

    usp_ajax({
        data: {
            action: 'usp_manager_get_new_group',
            props: USPManagerFields
        },
        success: function (data) {

            groupsBox.append(data.content);

            usp_init_manager_sortable();

        }
    });

    return false;
}

function usp_manager_field_edit(e) {

    var field = jQuery(e).parents('.usp-frame__field');

    field.toggleClass('settings-edit');

    /*ssi_modal.show({
     content: field,
     bodyElement: true,
     title: 'ssi-modal',
     extendOriginalContent: true,
     beforeShow: function(modal){
     field.remove();
     },
     });*/

}

function usp_manager_field_delete(field_id, meta_delete, e) {

    var field = jQuery(e).parents('.usp-frame__field');

    if (meta_delete) {

        if (confirm(jQuery('#usp-manager-confirm-delete').text())) {
            jQuery('.usp-frame__form .submit-box').append('<input type="hidden" name="deleted_fields[]" value="' + field_id + '">');
        }

    }

    field.remove();

    return false;
}

function usp_manager_get_custom_field_options(e) {

    var typeField = jQuery(e).val();
    var boxField = jQuery(e).parents('.usp-frame__field');
    var oldType = boxField.attr('data-type');

    var multiVals = ['multiselect', 'checkbox'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var multiVals = ['radio', 'select'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var singleVals = ['date', 'time', 'email', 'number', 'url', 'dynamic', 'tel'
    ];

    if (jQuery.inArray(typeField, singleVals) >= 0 && jQuery.inArray(oldType, singleVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var sliderVals = ['runner', 'range'];

    if (jQuery.inArray(typeField, sliderVals) >= 0 && jQuery.inArray(oldType, sliderVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    usp_preloader_show(boxField);

    usp_ajax({
        /*rest: true,*/
        data: {
            action: 'usp_manager_get_custom_field_options',
            newType: typeField,
            oldType: oldType,
            manager: USPManagerFields,
            fieldId: boxField.data('id')
        },
        success: function (data) {

            if (data['content']) {

                boxField.find('.field-secondary-options').replaceWith(data['content']);

                boxField.attr('data-type', typeField);

                usp_init_iconpicker();

            }

        }
    });

    return false;

}

function usp_manager_get_new_field(e) {

    var area = jQuery(e).parents('.usp-frame__group');

    usp_preloader_show(area);

    usp_ajax({
        /*rest: true,*/
        data: {
            action: 'usp_manager_get_new_field',
            props: USPManagerFields
        },
        success: function (data) {

            if (data['content']) {
                area.find('.fields-box').append(data['content']);
                area.find('.fields-box').last().find('.usp-field-core input').focus();
                usp_init_iconpicker();
            }

        }
    });

    return false;

}

function usp_manager_update_fields(newManagerId) {

    var newManagerId = newManagerId ? newManagerId : 0;

    usp_preloader_show(jQuery('.usp-frame'));

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    usp_ajax({
        /*rest: {action: 'usp_update_fields'},*/
        data: 'action=usp_manager_update_fields_by_ajax&copy=' + newManagerId + '&' + jQuery('.usp-frame__form').serialize()
    });

    return false;
}

function usp_manager_copy_fields(newManagerId) {

    usp_manager_update_fields(newManagerId);

    return false;
}
