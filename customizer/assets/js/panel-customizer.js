/* global wp */
// load customizer panel
;(function () {
    // Run function when customizer is ready.
    wp.customize.bind('ready', function () {
        // usp_bar_show depend
        wp.customize.control('usp_customizer[usp_bar_show]', function (control) {
            let value = control.setting._value;

            usp_customizer_toggle_child_control('#customize-control-usp_customizer-usp_bar_color', value);
            usp_customizer_toggle_child_control('#customize-control-usp_customizer-usp_bar_width', value);
        });
    });
})();

// change on actions
wp.customize('usp_customizer[usp_bar_show]', function (setting) {
    setting.bind(function (value) {
        usp_customizer_toggle_child_control('#customize-control-usp_customizer-usp_bar_color', value);
        usp_customizer_toggle_child_control('#customize-control-usp_customizer-usp_bar_width', value);
    });
});

// toggle children settings
function usp_customizer_toggle_child_control(id, value) {
    if (true === value) {
        jQuery(id).css({'display': 'block'});
    } else {
        jQuery(id).css({'display': 'none'});
    }
}
