/* global wp */
// real time preview
wp.customize('usp_customizer[usp_background]', function (setting) {
    setting.bind(function (value) {
        jQuery('.usp-bar-primary').css({'background-color': value, 'opacity': '.85'});

        jQuery('body').get(0).style.setProperty('--uspHex', value);
    });
});

wp.customize('usp_customizer[usp_color]', function (setting) {
    setting.bind(function (value) {
        jQuery('body').get(0).style.setProperty('--uspText', value);
    });
});

// todo standart - fix typo standard
wp.customize('usp_customizer[usp_bttn_size]', function (setting) {
    setting.bind(function (value) {
        jQuery('body').get(0).style.setProperty('--uspSize', value);

        jQuery('.usp-bttn__size-small > *').css('font-size', 0.86 * value + 'px');
        jQuery('.usp-bttn__size-medium > *').css('font-size', 1.14 * value + 'px');
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

        // del inline customizer background-color
        uspBar.attr('style', '');

        uspBar.removeClass('usp-bar-primary usp-bar-white usp-bar-dark');

        if (value === 'dark') {
            uspBar.addClass('usp-bar-dark');
        } else if (value === 'white') {
            uspBar.addClass('usp-bar-white');
        } else {
            uspBar.addClass('usp-bar-primary');
        }
    });
});

wp.customize('usp_customizer[usp_bar_width]', function (setting) {
    setting.bind(function (value) {
        if (value > 0) {
            jQuery('.usp-bar-wrap').css('max-width', value + 'px');
        } else {
            jQuery('.usp-bar-wrap').css('max-width', 'calc(100% - 24px)');
        }
    });
});

// example of getting an option by id
/*wp.customize('usp_customizer[usp_background]', function (setting) {
    console.log(setting._value);
});*/
