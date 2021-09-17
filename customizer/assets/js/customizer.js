/* global wp */
// real time preview
wp.customize('usp-customizer[usp_background]', function (value) {
    value.bind(function (to) {
        jQuery('.usp-bttn__type-primary').css('background-color', to);
        jQuery('.usp-bar-color').css({'background-color': to, 'opacity': '.85'});
    });
});

wp.customize('usp-customizer[usp_color]', function (value) {
    value.bind(function (to) {
        jQuery('.usp-bttn__type-primary').css('color', to);
    });
});

wp.customize('usp-customizer[usp_bttn_size]', function (value) {
    value.bind(function (to) {
        jQuery('.usp-bttn > *,.usp-bttn__size-standart > *').css('font-size', to + 'px');

        jQuery('.usp-bttn__size-small > *').css('font-size', 0.86 * to + 'px');
        jQuery('.usp-bttn__size-medium > *').css('font-size', 1.14 * to + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-medium > *,.usp-bttn__size-large:not(.usp-bttn__mod-only-icon) > *').css('font-size', 1.28 * to + 'px');
        jQuery('.usp-bttn__size-big > *').css('font-size', 1.5 * to + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-large > *').css('font-size', 1.8 * to + 'px');
        jQuery('.usp-bttn__type-clear.usp-bttn__mod-only-icon.usp-bttn__size-big > *').css('font-size', 2.1 * to + 'px');
    });
});
