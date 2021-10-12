/* global wp */
// real time preview
// noinspection JSUnresolvedFunction,JSUnresolvedVariable

wp.customize('usp_customizer[usp_background]', function (setting) {
    setting.bind(function (value) {
        if (usp_bar_get_type() === 'primary') {
            jQuery('#usp-bar').css({'background-color': value, 'opacity': usp_bar_get_opacity()});
            jQuery('#usp-bar .usp-bttn > *').css({'color': usp_get_text_color()});
        }

        jQuery('body').get(0).style.setProperty('--uspHex', value);
    });
});

wp.customize('usp_customizer[usp_color]', function (setting) {
    setting.bind(function (value) {
        jQuery('body').get(0).style.setProperty('--uspText', value);

        if (usp_bar_get_type() === 'primary') {
            jQuery('#usp-bar .usp-bttn > *').css({'color': value});
        }
    });
});

wp.customize('usp_customizer[usp_bttn_size]', function (setting) {
    setting.bind(function (value) {
        jQuery('body').get(0).style.setProperty('--uspSize', value + 'px');

        jQuery('.usp-bttn__size-standard > *').css('font-size', value + 'px');
        jQuery('.usp-bttn__size-small > *').css('font-size', 0.86 * value + 'px');
        jQuery('.usp-bttn__size-medium.usp-bttn__mod-only-icon:not(.usp-bttn__type-clear) > *').css('font-size', 1.28 * value + 'px');
        jQuery('.usp-bttn__size-medium:not(.usp-bttn__type-clear):not(.usp-bttn__mod-only-icon) > *').css('font-size', 1.14 * value + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium > *,.usp-bttn__size-large:not(.usp-bttn__mod-only-icon) > *').css('font-size', 1.28 * value + 'px');
        jQuery('.usp-bttn__size-big > *').css('font-size', 1.5 * value + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large > *').css('font-size', 1.8 * value + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big > *').css('font-size', 2.1 * value + 'px');
    });
});

wp.customize('usp_customizer[usp_bar_show]', function (setting) {
    setting.bind(function (value) {
        if (true === value) {
            jQuery('#usp-bar').show();

            jQuery('html').attr('style', 'margin-top: 40px !important');
        } else {
            jQuery('#usp-bar').hide();

            jQuery('html').attr('style', 'margin-top: 0 !important');
        }
    });
});

wp.customize('usp_customizer[usp_bar_color]', function (setting) {
    setting.bind(function (value) {
        let uspBar = jQuery('#usp-bar');

        if (value === 'primary') {
            uspBar.css({'background-color': usp_bar_get_background_color()});
        } else {
            uspBar.css({'background-color': value});

            let barBttn = jQuery('#usp-bar .usp-bttn > *');

            if (value === 'black') {
                barBttn.css({'color': '#fff'});
            } else {
                barBttn.css({'color': '#374151'});
            }
        }
        uspBar.css({'opacity': usp_bar_get_opacity()});
    });
});

function usp_bar_get_background_color() {
    return wp.customize('usp_customizer[usp_background]')._value;
}

function usp_get_text_color() {
    return wp.customize('usp_customizer[usp_color]')._value;
}

function usp_bar_get_type() {
    return wp.customize('usp_customizer[usp_bar_color]')._value;
}

function usp_bar_get_opacity() {
    return wp.customize('usp_customizer[usp_bar_opacity]')._value;
}

wp.customize('usp_customizer[usp_bar_opacity]', function (setting) {
    setting.bind(function (value) {
        let bar = jQuery('#usp-bar');

        bar.css({'opacity': value});

        if (usp_bar_get_type() === 'primary') {
            bar.css({'background': usp_bar_get_background_color()});
        }
    });
});

wp.customize('usp_customizer[usp_bar_width]', function (setting) {
    setting.bind(function (value) {
        let barWrap = jQuery('.usp-bar-wrap');

        if (value > 0) {
            barWrap.css('max-width', value + 'px');
        } else {
            barWrap.css('max-width', 'calc(100% - 24px)');
        }
    });
});

// example of getting an option by id
/*wp.customize('usp_customizer[usp_background]', function (setting) {
    console.log(setting._value);
});*/
