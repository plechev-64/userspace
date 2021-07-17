/* global ssi_modal, USP */

function usp_zoom_avatar(e) {
    ssi_modal.show({
        sizeClass: 'auto',
        className: 'usp-ava-zoom ssi-no-padding',
        content: '<img class="usps usps__img-reset usps__fit-cover" src="' + jQuery(e).data('zoom') + '">'
    });
}

function usp_get_user_info(e) {
    usp_preloader_show('#usp-avatar img');

    usp_ajax({
        data: {
            action: 'usp_return_user_details',
            user_id: jQuery(e).parents('#usp-office').data('account')
        },
        success: function (data) {
            if (data['content']) {
                ssi_modal.show({
                    title: USP.local.title_user_info,
                    sizeClass: 'auto',
                    className: 'usp-user-getails',
                    buttons: [{
                        label: USP.local.close,
                        closeAfter: true
                    }],
                    content: data['content']
                });

            }
        }
    });
}
