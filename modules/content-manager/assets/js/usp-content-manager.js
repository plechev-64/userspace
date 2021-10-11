function usp_table_manager_state(classname, state) {

}

function usp_content_manager_submit(e) {

    var isAjax = parseInt(jQuery(e).parents('form').find('#value-ajax').val());

    if (isAjax) {

        usp_load_content_manager(e);

        return false;

    } else {

        if (e && jQuery(e).parents('.usp-preloader-parent')) {
            usp_preloader_show(jQuery(e).parents('.usp-preloader-parent'));
        }

        usp_submit_form(e);

    }

}

function usp_init_on_change_select_templates_filter() {

    jQuery('body').on('change', '#usp-templates-manager select', function () {
        usp_content_manager_submit(this);
    });

}

function usp_table_manager_search_by_col(e, key, submit) {

    if (key != 'Enter')
        return;

    jQuery(e).parents('form').find('#value-pagenum').val(1);

    usp_content_manager_submit(e);

}

function usp_load_content_manager_page(dataval, postname, e) {

    jQuery(e).parents('form').find('#value-' + postname).val(jQuery(e).data(dataval));

    usp_content_manager_submit(e);

}

function usp_order_table_manager_page(e) {

    var order = jQuery(e).data('order');

    var nextorder = (order == 'desc') ? 'asc' : 'desc';

    jQuery(e).attr('data-order', nextorder);

    var form = jQuery(e).parents('form');

    form.find('#value-order').val(nextorder);
    form.find('#value-orderby').val(jQuery(e).data('col'));

    form.find('#value-pagenum').val(1);

    usp_content_manager_submit(e);

}

function usp_table_manager_prev(prevData, e) {

    var FormFactory = new USPForm(jQuery(e).parents('form'));

    usp_ajax({
        data: prevData,
        success: function (result) {
            usp_proccess_ajax_return(result);

            FormFactory.form.find('.usp-content-manager').replaceWith(result.content);
        }
    });

}

function usp_load_content_manager(e, props) {

    // getting the form data
    var FormFactory = new USPForm(jQuery(e).parents('form'));

    if (props != 'undefined' && props) {

        usp_ajax({
            data: {
                action: 'usp_load_content_manager',
                classname: props.classname,
                classargs: props.classargs,
                tail: props.tail,
                prevs: props.prevs
            },
            success: function (result) {
                usp_proccess_ajax_return(result);

                FormFactory.form.find('.usp-content-manager').replaceWith(result.content);
            }
        });

    } else {

        // correctness of filling
        if (!FormFactory.validate())
            return false;

        FormFactory.send('usp_load_content_manager', function (result) {

            usp_proccess_ajax_return(result);

            FormFactory.form.find('.usp-content-manager').replaceWith(result.content);

        }, true);

    }

}

function usp_load_content_manager_state(state, e) {

    // get form data
    var FormFactory = new USPForm(jQuery(e).parents('form'));

    usp_preloader_show(jQuery('form.usp-preloader-parent'));

    usp_ajax({
        rest: true,
        data: {
            action: 'usp_load_content_manager_state',
            state: state,
        },
        success: function (result) {
            usp_proccess_ajax_return(result);
            FormFactory.form.find('.usp-content-manager').replaceWith(result.content);
        }
    });


}

function usp_save_table_manager_cols(e) {

    var form = jQuery('#usp-cols-manager .active-cols input');

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

