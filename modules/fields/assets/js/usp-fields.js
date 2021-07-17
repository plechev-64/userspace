//usp_add_action( 'usp_init', 'usp_init_update_requared_checkbox' );
function usp_init_update_requared_checkbox() {

    jQuery('body form').find('.required-checkbox').each(function () {
        usp_update_require_checkbox(this);
    });

    jQuery('body form').on('click', '.required-checkbox', function () {
        usp_update_require_checkbox(this);
    });

}

function usp_add_dynamic_field(e) {
    var parent = jQuery(e).parents('.dynamic-value');
    var box = parent.parent('.dynamic-values');
    var html = parent.html();
    box.append('<span class="dynamic-value">' + html + '</span>');
    jQuery(e).attr('onclick', 'usp_remove_dynamic_field(this);return false;').children('i').toggleClass("fa-plus fa-minus");
    box.children('span').last().children('input').val('').focus();
}

function usp_remove_dynamic_field(e) {
    jQuery(e).parents('.dynamic-value').remove();
}

function usp_update_require_checkbox(e) {
    var name = jQuery(e).attr('name');
    var chekval = jQuery('form input[name="' + name + '"]:checked').val();
    if (chekval)
        jQuery('form input[name="' + name + '"]').attr('required', false);
    else
        jQuery('form input[name="' + name + '"]').attr('required', true);
}

function usp_setup_datepicker_options() {

//	jQuery.datepicker.setDefaults( jQuery.extend( jQuery.datepicker.regional["ru"] ) );

    var options = {
//		monthNames: [],
//		dayNamesMin: [],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        yearRange: "1950:c+3",
        changeYear: true
    };

    options = usp_apply_filters('usp_datepicker_options', options);

    return options;

}

function usp_show_datepicker(e) {
    jQuery(e).datepicker(usp_setup_datepicker_options());
    jQuery(e).datepicker("show");
    usp_add_action('usp_upload_tab', 'usp_remove_datepicker_box');
}

function usp_remove_datepicker_box() {
    jQuery('#ui-datepicker-div').remove();
}

function usp_init_field_file(field_id) {
    jQuery("#" + field_id).parents('form').attr("enctype", "multipart/form-data");
}

function usp_init_runner(props) {

    var box = jQuery('#usp-runner-' + props.id);

    box.children('.usp-runner-box').slider({
        value: parseInt(props.value),
        min: parseInt(props.min),
        max: parseInt(props.max),
        step: parseInt(props.step),
        create: function (event, ui) {
            var value = box.children('.usp-runner-box').slider('value');
            box.children('.usp-runner-value').text(value);
            box.children('.usp-runner-field').val(value);
        },
        slide: function (event, ui) {
            box.find('.usp-runner-value').text(ui.value);
            box.find('.usp-runner-field').val(ui.value);
        }
    });
}

function usp_init_range(props) {

    var box = jQuery('#usp-range-' + props.id);

    box.children('.usp-range-box').slider({
        range: true,
        values: [parseInt(props.values[0]), parseInt(props.values[1])],
        min: parseInt(props.min),
        max: parseInt(props.max),
        step: parseInt(props.step),
        create: function (event, ui) {
            var values = box.children('.usp-range-box').slider('values');
            box.children('.usp-range-value').text(values[0] + ' - ' + values[1]);
            box.children('.usp-range-min').val(values[0]);
            box.children('.usp-range-max').val(values[1]);
        },
        slide: function (event, ui) {
            box.children('.usp-range-value').text(ui.values[0] + ' - ' + ui.values[1]);
            box.find('.usp-range-min').val(ui.values[0]);
            box.find('.usp-range-max').val(ui.values[1]);
        }
    });
}

function usp_init_color(id, props) {
    jQuery("#" + id).wpColorPicker(props);
}

function usp_init_field_maxlength(fieldID) {

    var field = jQuery('#' + fieldID);
    var maxlength = field.attr('maxlength');

    if (!field.parent().find('.usp-maxlength').length) {

        if (field.val()) {
            maxlength = maxlength - field.val().length;
        }

        field.after('<span class="usp-maxlength usps usps__ai-center usps__jc-center usps__no-select">' + maxlength + '</span>');
    }

    field.on('keyup', function () {
        var maxlength = jQuery(this).attr('maxlength');
        if (!maxlength)
            return false;
        var word = jQuery(this);
        var count = maxlength - word.val().length;
        jQuery(this).next().text(count);
        if (word.val().length > maxlength)
            word.val(word.val().substr(0, maxlength));
    });
}

function usp_init_ajax_editor(id, options) {

    if (typeof QTags === 'undefined')
        return false;

    usp_do_action('usp_pre_init_ajax_editor', {
        id: id,
        options: options
    });

    var qt_options = {
        id: id,
        buttons: (options.qt_buttons) ? options.qt_buttons : "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
    };

    QTags(qt_options);

    QTags._buttonsInit();

    if (options.tinymce && typeof tinyMCEPreInit != 'undefined') {

        tinyMCEPreInit.qtInit[id] = qt_options;

        tinyMCEPreInit.mceInit[id] = {
            body_class: id,
            selector: '#' + id,
            menubar: false,
            skin: "lightgray",
            theme: 'modern',
            toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv",
            toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
            wpautop: true
        };

        tinymce.init(tinyMCEPreInit.mceInit[id]);
        tinyMCE.execCommand('mceAddEditor', true, id);

        switchEditors.go(id, 'html');
    }

}

function usp_setup_quicktags(newTags) {

    if (typeof QTags === 'undefined')
        return false;

    newTags.forEach(function (tagArray, i, newTags) {

        QTags.addButton(
            tagArray[0],
            tagArray[1],
            tagArray[2],
            tagArray[3],
            tagArray[4],
            tagArray[5],
            tagArray[6]
        );

    });

}

usp_add_action('usp_pre_init_ajax_editor', 'usp_add_ajax_quicktags');

function usp_add_ajax_quicktags(editor) {

    if (typeof USP === 'undefined' || !USP.QTags)
        return false;

    usp_setup_quicktags(USP.QTags);

}

usp_add_action('usp_footer', 'usp_add_quicktags');

function usp_add_quicktags() {

    if (typeof USP === 'undefined' || !USP.QTags)
        return false;

    usp_setup_quicktags(USP.QTags);

}

function usp_init_iconpicker() {
    jQuery('.usp-iconpicker').iconpicker();
}