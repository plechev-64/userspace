/* global USP */

var usp_actions = typeof usp_actions === 'undefined' ? [] : usp_actions;
var usp_filters = typeof usp_filters === 'undefined' ? [] : usp_filters;
var usp_beats = [];
var usp_beats_delay = 0;
var usp_url_params = usp_get_value_url_params();

jQuery(document).ready(function ($) {

    $.fn.extend({
        insertAtCaret: function (myValue) {
            return this.each(function (i) {
                if (document.selection) {
                    // For Internet Explorer
                    this.focus();
                    var sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart == '0') {
                    // For Firefox & Webkit
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            })
        },
        animateCss: function (animationNameStart, functionEnd) {
            //var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            let animationEnd = 'animationend';
            this.addClass('animated ' + animationNameStart).one(animationEnd, function (l) {
                jQuery(this).removeClass('animated ' + animationNameStart);

                if (functionEnd) {
                    if (typeof functionEnd == 'function') {
                        functionEnd(this);
                    } else {
                        jQuery(this).animateCss(functionEnd);
                    }
                }
            });
            return this;
        }
    });

});

function usp_do_action(action_name) {

    var callbacks_action = usp_actions[action_name];

    if (!callbacks_action)
        return false;

    var args = [].slice.call(arguments, 1);

    callbacks_action.forEach(function (callback, i, callbacks_action) {
        if (window[callback])
            window[callback].apply(this, args);
        if (typeof callback === 'function')
            callback.apply(this, args);
    });
}

function usp_add_action(action_name, callback) {
    if (!usp_actions[action_name]) {
        usp_actions[action_name] = [callback];
    } else {
        var i = usp_actions[action_name].length;
        usp_actions[action_name][i] = callback;
    }
}

function usp_apply_filters(filter_name) {

    var args = [].slice.call(arguments, 1);

    var callbacks_filter = usp_filters[filter_name];

    if (!callbacks_filter)
        return args[0];

    callbacks_filter.forEach(function (callback, i, callbacks_filter) {
        args[0] = window[callback].apply(this, args);
    });

    return args[0];
}

function usp_add_filter(filter_name, callback) {
    if (!usp_filters[filter_name]) {
        usp_filters[filter_name] = [callback];
    } else {
        var i = usp_filters[filter_name].length;
        usp_filters[filter_name][i] = callback;
    }
}

function usp_get_value_url_params() {
    var tmp_1 = new Array();
    var tmp_2 = new Array();
    var usp_url_params = new Array();
    var get = location.search;
    if (get !== '') {
        tmp_1 = (get.substr(1)).split('&');
        for (var i = 0; i < tmp_1.length; i++) {
            tmp_2 = tmp_1[i].split('=');
            usp_url_params[tmp_2[0]] = tmp_2[1];
        }
    }

    return usp_url_params;
}

function usp_is_valid_url(url) {
    var objRE = /http(s?):\/\/[-\w\.]{3,}\.[A-Za-z]{2,3}/;
    return objRE.test(url);
}

function setAttr_usp(prmName, val) {
    var res = '';
    var d = location.href.split("#")[0].split("?");
    var base = d[0];
    var query = d[1];
    if (query) {
        var params = query.split("&");
        for (var i = 0; i < params.length; i++) {
            var keyval = params[i].split("=");
            if (keyval[0] !== prmName) {
                res += params[i] + '&';
            }
        }
    }
    res += prmName + '=' + val;
    return base + '?' + res;
}

function usp_update_history_url(url) {

    if (url != window.location) {
        if (history.pushState) {
            window.history.pushState(null, null, url);
        }
    }

}

function usp_init_cookie() {

    jQuery.cookie = function (name, value, options) {
        if (typeof value !== 'undefined') {
            options = options || {};
            if (value === null) {
                value = '';
                options.expires = -1;
            }
            var expires = '';
            if (options.expires && (typeof options.expires === 'number' || options.expires.toUTCString)) {
                var date;
                if (typeof options.expires === 'number') {
                    date = new Date();
                    date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                } else {
                    date = options.expires;
                }
                expires = '; expires=' + date.toUTCString();
            }
            var path = options.path ? '; path=' + (options.path) : '';
            var domain = options.domain ? '; domain=' + (options.domain) : '';
            var secure = options.secure ? '; secure' : '';
            document.cookie = [name, '=', encodeURIComponent(value),
                expires, path,
                domain, secure].join('');
        } else {
            var cookieValue = null;
            if (document.cookie && document.cookie !== '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = jQuery.trim(cookies[i]);
                    if (cookie.substring(0, name.length + 1) === (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }
    };

}

function usp_rand(min, max) {
    if (max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    } else {
        return Math.floor(Math.random() * (min + 1));
    }
}

function usp_notice(text, type, time_close) {

    let options = {
        text: '',
        type: '',
        time_close: 0,
        timeout: 1,
        posX: 'left',
        posY: 'top',
        closeAnim: 'auto',
        openAnim: 'auto'
    };

    if (typeof text === 'object') {
        options = {...options, ...text};
    } else {
        options = {...options, ...{text, type, time_close}};
    }

    options = usp_apply_filters('usp_notice_options', options);

    if (options.closeAnim === 'auto') {
        options.closeAnim = {
            left: 'fadeOutLeft',
            right: 'fadeOutRight',
            center: 'flipOutX'
        }[options.posX];
    }

    if (options.openAnim === 'auto') {
        options.openAnim = {
            left: 'slideInLeft',
            right: 'slideInRight',
            center: 'flipInX'
        }[options.posX];
    }

    const $ = jQuery;
    let closeTimeout = null;

    let noticeWrapper = $('#usp-wrap-notices[data-x="' + options.posX + '"][data-y="' + options.posY + '"]');

    if (!noticeWrapper.length) {
        noticeWrapper = $(`<div id="usp-wrap-notices" data-x="${options.posX}" data-y="${options.posY}"></div>`);
        $('body > *').last().after(noticeWrapper);
    }

    let $noticeBody = $(`<div id="notice" class="usp-notice usps__relative usps__line-normal usp-notice__type-${options.type}"></div>`);
    let $noticeCloser = $(`<i class="uspi fa-times usp-notice__close" aria-hidden="true"></i>`);
    let $noticeContent = $(`<div class="usp-notice__text">${options.text}</div>`);

    $noticeBody.append($noticeCloser, $noticeContent);

    if (options.time_close && options.timeout) {
        $noticeBody.append('<div class="usp-notice__timeout"></div>');
        $noticeBody.css('--usp-notice-timeout', options.time_close);
    }

    const closeNotice = () => {
        $noticeBody.animateCss(options.closeAnim, function () {
            $noticeBody.remove();
        });
        clearTimeout(closeTimeout);
    }

    $noticeCloser.on('click', closeNotice);

    $noticeBody.animateCss(options.openAnim, function () {
        if (options.time_close) {
            closeTimeout = setTimeout(closeNotice, options.time_close);
        }
    });

    $(noticeWrapper).append($noticeBody);

    return {
        close: closeNotice
    };
}

function usp_close_notice(e) {
    var timeCook = jQuery(e).data('notice_time');

    if (timeCook) {
        var idCook = jQuery(e).data('notice_id');

        jQuery.cookie(idCook, '1', {
            expires: timeCook,
            path: '/'
        });
    }

    var block = jQuery(e).parent();

    jQuery(block).animateCss('flipOutX', function () {
        jQuery(block).remove();
    });

    return false;
}

function usp_preloader_show(e, size) {

    var font_size = (size) ? size : 80;
    var margin = font_size / 2;

    var options = {
        size: font_size,
        margin: margin,
        icon: 'fa-spinner',
        class: 'usp_preloader'
    };

    options = usp_apply_filters('usp_preloader_options', options);

    var style = 'style="font-size:' + options.size + 'px;margin: -' + options.margin + 'px 0 0 -' + options.margin + 'px;"';

    var html = '<div class="' + options.class + '"><i class="uspi ' + options.icon + ' fa-spin" ' + style + '></i></div>';

    if (typeof (e) === 'string')
        jQuery(e).after(html);
    else
        e.append(html);
}

function usp_preloader_hide() {
    jQuery('.usp_preloader').remove();
}

function usp_proccess_ajax_return(result) {

    var methods = {
        redirect: function (url) {

            var urlData = url.split('#');

            if (window.location.origin + window.location.pathname === urlData[0]) {
                location.reload();
            } else {
                location.replace(url);
            }

        },
        reload: function () {
            location.reload();
        },
        current_url: function (url) {
            usp_update_history_url(url);
        },
        dialog: function (dialog) {

            if (dialog.content) {

                if (jQuery('#ssi-modalContent').length)
                    ssi_modal.close();

                var ssiOptions = {
                    className: 'usp-dialog-tab ' + (dialog.class ? ' ' + dialog.class : ''),
                    sizeClass: dialog.size ? dialog.size : 'auto',
                    content: dialog.content,
                    buttons: []
                };

                if (dialog.buttons) {
                    ssiOptions.buttons = dialog.buttons;
                }

                var buttonClose = true;

                if ('buttonClose' in dialog) {
                    buttonClose = dialog.buttonClose;
                }

                if (buttonClose) {

                    ssiOptions.buttons.push({
                        label: USP.local.close,
                        closeAfter: true
                    });

                }

                if ('onClose' in dialog) {
                    ssiOptions.onClose = function (m) {
                        window[dialog.onClose[0]].apply(this, dialog.onClose[1]);
                    };
                }

                if (dialog.title)
                    ssiOptions.title = dialog.title;

                ssi_modal.show(ssiOptions);

            }

            if (dialog.close) {
                ssi_modal.close();
            }

        }
    };

    for (var method in result) {
        if (methods[method]) {
            methods[method](result[method]);
        }
    }

}

function usp_ajax(prop) {

    if (prop.data.ask) {
        if (!confirm(prop.data.ask)) {
            usp_preloader_hide();
            return false;
        }
    }

    if (typeof USP != 'undefined') {
        if (typeof prop.data === 'string') {
            prop.data += '&_wpnonce=' + USP.nonce;
        } else if (typeof prop.data === 'object') {
            prop.data._wpnonce = USP.nonce;
        }
    }

    var action = 'usp_ajax_call';
    var callback = false;
    if (typeof prop.data === 'string') {

        var propData = prop.data.split('&');
        var newRequestArray = [];

        for (var key in propData) {
            if (propData[key].split("=")[0] == 'action') {
                callback = propData[key].split("=")[1];
                newRequestArray.push('call_action=' + propData[key].split("=")[1]);
            } else {
                newRequestArray.push(propData[key]);
            }
        }

        prop.data = newRequestArray.join('&');

        USP.used_modules.forEach(function (module_id) {
            prop.data += '&used_modules[]=' + module_id;
        });

        prop.data += '&action=usp_ajax_call';

    } else if (typeof prop.data === 'object') {
        callback = prop.data.action;
        prop.data.used_modules = USP.used_modules;
        prop.data.action = action;
        prop.data.call_action = callback;
    }

    prop.rest = {
        action: action
    };

    var url;

    if (prop.rest) {

        var restAction = action;
        var restRoute = restAction;
        var restSpace = 'userspace';

        if (typeof prop.rest === 'object') {

            if (prop.rest.action)
                restAction = prop.rest.action;
            if (prop.rest.space)
                restSpace = prop.rest.space;
            if (prop.rest.route)
                restRoute = prop.rest.route;
            else
                restRoute = restAction;

        }

        if (USP.permalink)
            url = USP.wpurl + '/wp-json/' + restSpace + '/' + restRoute + '/';
        else
            url = USP.wpurl + '/?rest_route=/' + restSpace + '/' + restRoute + '/';

    } else {

        url = (typeof ajax_url !== 'undefined') ? ajax_url : USP.ajaxurl;

    }

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    jQuery.ajax({
        type: 'POST',
        data: prop.data,
        dataType: 'json',
        url: url,
        success: function (result, post) {

            var noticeTime = result.notice_time ? result.notice_time : 5000;

            if (!result) {
                usp_notice(USP.local.error, 'error', noticeTime);
                return false;
            }

            if (result.error || result.errors) {

                usp_preloader_hide();

                if (result.errors) {
                    jQuery.each(result.errors, function (index, error) {
                        usp_notice(error, 'error', noticeTime);
                    });
                } else {
                    usp_notice(result.error, 'error', noticeTime);
                }

                if (prop.error)
                    prop.error(result);

                return false;

            }

            if (!result.preloader_live) {
                usp_preloader_hide();
            }

            if (result.success) {
                usp_notice(result.success, 'success', noticeTime);
            }

            if (result.warning) {
                usp_notice(result.warning, 'warning', noticeTime);
            }

            usp_do_action('usp_ajax_success', result);

            if (prop.success) {

                prop.success(result);

            } else {

                usp_proccess_ajax_return(result);

            }

            if (prop.afterSuccess) {

                prop.afterSuccess(result);

            }

            usp_do_action(callback, result);

            if (result.used_modules) {
                USP.used_modules = result.used_modules;
            }

        }
    });

}

function usp_add_beat(beat_name, delay, data) {

    delay = (delay < 10) ? 10 : delay;

    var data = (data) ? data : false;

    var i = usp_beats.length;

    usp_beats[i] = {
        beat_name: beat_name,
        delay: delay,
        data: data
    };

}

function usp_remove_beat(beat_name) {

    if (!usp_beats)
        return false;

    var remove = false;
    var all_beats = usp_beats;

    all_beats.forEach(function (beat, index, all_beats) {
        if (beat.beat_name != beat_name)
            return;
        delete usp_beats[index];
        remove = true;
    });

    return remove;

}

function usp_exist_beat(beat_name) {

    if (!usp_beats)
        return false;

    var exist = false;

    usp_beats.forEach(function (beat, index, usp_beats) {
        if (beat.beat_name != beat_name)
            return;
        exist = true;
    });

    return exist;

}

/** new uploader scripts **/

/** new uploader scripts end **/