//
// scripts for USP_Customize_Color
//
// alpha-color-picker https://github.com/BraadMartin/components
// author BraadMartin http://braadmartin.com/
// license GPL
jQuery(document).ready(function ($) {
    $('.usp-color-control').each(function () {
        var $control, startingColor, paletteInput, defaultColor, palette, colorPickerOptions, $container;

        $control = $(this);

        // Get a clean starting value for the option.
        startingColor = $control.val().replace(/\s+/g, '');

        // Get some data off the control.
        paletteInput = $control.attr('data-palette');
        defaultColor = $control.attr('data-default-color');

        // Process the palette.
        if (paletteInput.indexOf('|') !== -1) {
            palette = paletteInput.split('|');
        } else if ('false' == paletteInput) {
            palette = false;
        } else {
            palette = true;
        }

        // Set up the options that we'll pass to wpColorPicker().
        colorPickerOptions = {
            change: function (event, ui) {
                var key, value;

                key = $control.attr('data-customize-setting-link');
                value = $control.wpColorPicker('color');

                // Send ajax request to wp.customize to trigger the Save action.
                wp.customize(key, function (obj) {
                    obj.set(value);
                });

            },
            palettes: palette
        };

        // Create the colorpicker.
        $control.wpColorPicker(colorPickerOptions);

        $container = $control.parents('.wp-picker-container:first');

        // Bind event handler for clicking on a palette color.
        $container.find('.iris-palette').on('click', function () {
            var color;

            color = $(this).css('background-color');
            $control.wpColorPicker('color', color);
        });

    });
});
