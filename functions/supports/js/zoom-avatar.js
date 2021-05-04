/* global ssi_modal */

function usp_zoom_user_avatar( e ) {
    ssi_modal.show( {
        sizeClass: 'auto',
        className: 'usp-ava-zoom ssi-no-padding',
        content: '<img class="usps usps__img-reset" src="' + jQuery( e ).data( 'zoom' ) + '" style="max-width: 90vh;">'
    } );
}
