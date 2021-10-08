/* global ssi_modal, USP */

function usp_zoom_avatar(e) {
    ssi_modal.show({
        sizeClass: 'auto',
        className: 'usp-ava-zoom ssi-no-padding',
        content: '<img alt="" class="usps usps__img-reset usps__fit-cover" src="' + jQuery(e).data('zoom') + '">'
    });
}

function usp_get_user_info(e, user_id = false, className = false) {
    usp_preloader_show(jQuery(e), 42);

    user_id = (user_id) ? user_id : jQuery(e).parents('#usp-office').data('account');

    if (!user_id) {
        return;
    }

    usp_ajax({
        data: {
            action: 'usp_return_user_details',
            user_id: user_id
        },
        success: function (data) {
            if (data['content']) {
                ssi_modal.show({
                    title: data['name'],
                    sizeClass: 'auto',
                    className: 'usp-user-modal ssi-no-padding ' + className,
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
