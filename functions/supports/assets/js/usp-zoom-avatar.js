/* global ssi_modal */

function usp_zoom_user_avatar(e) {
    ssi_modal.show({
        sizeClass: 'auto',
        className: 'usp-ava-zoom ssi-no-padding',
        content: '<img alt="" class="usps usps__img-reset usps__fit-cover" src="' + jQuery(e).data('zoom') + '" style="max-height: 90vh;">'
    });
}
