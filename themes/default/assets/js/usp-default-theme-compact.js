usp_add_action('usp_upload_tab', 'usp_office_add_class');

function usp_office_add_class() {
    jQuery('#usp-office').addClass('usp-office-small');
}

function usp_office_shift() {
    jQuery('#usp-office').toggleClass('usp-office-small');
}
