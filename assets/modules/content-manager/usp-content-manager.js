function usp_content_manager_submit(managerId) {

    const $managerForm = jQuery('#' + managerId);
    const $pageInput = $managerForm.find('input[name="pagenum"]');

    if ($pageInput.length) {
        $pageInput.val(1);
    }

    usp_load_content_manager($managerForm);
}

function usp_table_manager_search_by_col(e, key, submit) {

    if (key !== 'Enter') {
        return;
    }

    const $managerForm = jQuery(e).closest('form');

    $managerForm.find('input[name="pagenum"]').val(1);

    usp_load_content_manager($managerForm);

}

function usp_load_content_manager_page(managerId, e) {

    const $managerForm = jQuery('#' + managerId);

    $managerForm.find('input[name="pagenum"]').val(jQuery(e).data('page'));

    usp_load_content_manager($managerForm);

}

function usp_order_table_manager_page(e) {

    const order = jQuery(e).data('order');

    const nextorder = (order === 'desc') ? 'asc' : 'desc';

    jQuery(e).attr('data-order', nextorder);

    const form = jQuery(e).closest('form');

    form.find('input[name="order"]').val(nextorder);
    form.find('input[name="orderby"]').val(jQuery(e).data('col'));

    form.find('input[name="pagenum"]').val(1);

    usp_load_content_manager(form);

}

function usp_load_content_manager(managerForm) {

    // getting the form data
    const FormFactory = new USPForm(jQuery(managerForm));

    // correctness of filling
    if (!FormFactory.validate()) {
        return false;
    }

    FormFactory.send('usp_load_content_manager', function (result) {

        usp_proccess_ajax_return(result);

        FormFactory.form.find('.usp-content-manager__body').replaceWith(result.content);

    }, true);


}

function usp_save_table_manager_cols(e) {

    const form = jQuery('#usp-cols-manager .active-cols input');

    usp_ajax({
        rest: {
            action: 'usp_save_table_manager_cols'
        },
        data: form.serialize() + '&action=usp_save_table_manager_cols'
    });
}

function usp_get_table_manager_cols(managerId, cols, active_cols, disabled_cols, e) {

    usp_ajax({
        rest: true,
        data: {
            action: 'usp_get_table_manager_cols',
            manager_id: managerId,
            cols: cols,
            active_cols: active_cols,
            disabled_cols: disabled_cols
        }
    });

}

