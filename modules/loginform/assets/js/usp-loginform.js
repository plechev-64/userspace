/* global USP, usp_url_params */

jQuery(window).load(function () {
    jQuery('body').on('click', '.usp-register', function (e) {
        e.preventDefault();
        USP.loginform.call('register');
    });

    jQuery('body').on('click', '.usp-login', function (e) {
        e.preventDefault();
        USP.loginform.call('login');
    });

    if (usp_url_params['usp-form']) {
        if (usp_url_params['type-form'] == 'float') {
            USP.loginform.call(usp_url_params['usp-form'], usp_url_params['formaction']);
        } else {
            USP.loginform.tabShow(usp_url_params['usp-form']);
        }
    }

    jQuery('.usp-entry-bttn').click(function () {
        usp_preloader_show(jQuery(this), 30);
    });

});

USP.loginform = {
    animating: false,
    tabShow: function (tabId, e) {
        var form = jQuery('.usp-entry-form');
        form.find('.usp-entry-tab').removeClass('usp-bttn__active');
        form.find('.usp-entry-tab__' + tabId).addClass('usp-bttn__active');

        form.find('.usp-entry-box').removeClass('usp-entry-box__active');
        form.find('.usp-entry-box__' + tabId).addClass('usp-entry-box__active');
        if (e)
            jQuery(e).addClass('usp-entry-box__active');
        else
            form.find('.usp-entry-box__' + tabId).addClass('usp-entry-box__active');

    },
    send: function (tabId, e) {
        var form = jQuery(e).parents('form');
        if (!usp_check_form(form))
            return false;

        usp_preloader_show(jQuery('.usp-entry-form'));

        usp_ajax({
            data: form.serialize() + '&tab_id=' + tabId + '&action=usp_send_loginform',
            afterSuccess: function (result) {
                jQuery('.usp-entry-box__' + tabId).html(result.content);
            }
        });

    },
    call: function (form, action) {

        var typeform = form ? form : 'login';
        var formaction = action ? action : '';

        usp_ajax({
            data: {
                form: typeform,
                formaction: formaction,
                action: 'usp_call_loginform'
            }
        });

    }
};

function passwordStrength(password) {
    var desc = [
        USP.local.pass0,
        USP.local.pass1,
        USP.local.pass2,
        USP.local.pass3,
        USP.local.pass4,
        USP.local.pass5
    ];

    var score = 0;
    if (password.length > 6)
        score++;
    if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)))
        score++;
    if (password.match(/\d+/))
        score++;
    if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
        score++;
    if (password.length > 12)
        score++;
    document.getElementById("passwordDescription").innerHTML = desc[score];
    document.getElementById("passwordStrength").className = "strength" + score;
}