var USPUploaders = new USPClassUploaders();

(function ($) {

    $(document).ready(function () {

        jQuery('body').on('drop', function (e) {
            return false;
        });
        jQuery(document.body).bind("drop", function (e) {
            e.preventDefault();
        });

        if (typeof USPUploaders !== 'undefined')
            USPUploaders.init();

    });

})(jQuery);

function USPClassUploaders() {

    this.uploaders = [];

    this.init = function () {

        this.uploaders.forEach(function (uploader, i) {
            uploader.init();
        });

    };

    this.add = function (props, sk) {

        this.uploaders.push(new USPUploader(props, sk));

    };

    this.get = function (uploader_id) {

        var k = false;

        this.uploaders.forEach(function (uploader, i) {

            if (uploader.uploader_id == uploader_id)
                k = i;
        });

        if (k !== false)
            return this.uploaders[k];

    };

    this.isset = function (uploader_id) {

        var k = false;

        this.uploaders.forEach(function (uploader, i) {

            if (uploader.uploader_id == uploader_id)
                k = i;
        });

        if (k !== false)
            return true;

        return false;

    };

}
;

function USPUploader(props, sk) {

    this.uploader_id = props.uploader_id;
    this.input = jQuery("#usp-uploader-input-" + this.uploader_id);
    this.button = this.input.parent(".usp-uploader-bttn");
    this.options = props;

    this.getFormData = function (uploader) {
        if (!uploader)
            uploader = this;

        var formData = {
            options: JSON.stringify(uploader.options),
            is_wp_admin_page: typeof adminpage ? 1 : 0,
            sk: sk
        };

        formData.action = 'usp_ajax_call';
        formData.call_action = uploader.options.action;
        formData._wpnonce = USP.nonce;

        return formData;

    };

    this.init = function () {

        if (this.options.dropzone)
            usp_init_dropzone(jQuery("#usp-dropzone-" + this.uploader_id));

        var uploader_id = this.options.uploader_id;
        var uploader = this;

        options = {
            dataType: 'json',
            type: 'POST',
            url: USP.wpurl + '/?rest_route=/userspace/usp_ajax_call/', /*USP.ajaxurl*/
            dropZone: this.options.dropzone ? jQuery("#usp-dropzone-" + this.uploader_id) : false,
            formData: this.getFormData(uploader),
            loadImageMaxFileSize: this.options.max_size * 1024,
            autoUpload: this.options.auto_upload,
            singleFileUploads: false,
            /*limitMultiFileUploads: this.options.max_files,*/
            maxNumberOfFiles: this.options.max_files,
            imageMinWidth: this.options.min_width,
            imageMinHeight: this.options.min_height,
            imageMaxWidth: 1920,
            imageMaxHeight: 1080,
            imageCrop: false,
            imageForceResize: false,
            previewCrop: false,
            previewThumbnail: true,
            previewCanvas: true,
            previewMaxWidth: 900,
            previewMaxHeight: 900,
            disableExifThumbnail: true,
            progressall: function (e, data) {
                USPUploaders.get(uploader_id).progressall(e, data);
            },
            processstart: function (e, data) {
                USPUploaders.get(uploader_id).processstart(e, data);
            },
            processdone: function (e, data) {
                USPUploaders.get(uploader_id).processdone(e, data);
            },
            processfail: function (e, data) {
                USPUploaders.get(uploader_id).processfail(e, data);
            },
            add: function (e, data) {
                USPUploaders.get(uploader_id).add(e, data);
            },
            submit: function (e, data) {
                USPUploaders.get(uploader_id).submit(e, data);
            },
            done: function (e, data) {
                USPUploaders.get(uploader_id).done(e, data);
            }
        };

        this.input.fileupload(options);

        /*this.initSortable();*/

        usp_do_action('usp_uploader_init', uploader_id);

    };

    this.initSortable = function () {
        jQuery("#usp-media-" + this.uploader_id).sortable({
            //connectWith: "#usp-media-" + this.uploader_id,
            containment: "parent",
            //handle: ".field-control .control-move",
            cursor: "move",
            placeholder: "ui-sortable-placeholder",
            distance: 5
        });
    };

    this.processstart = function (e, data) {
        console.log('processstart');
    };

    this.processdone = function (e, data) {
        console.log('processdone');
    };

    this.processfail = function (e, data) {
        console.log('processfail');
    };

    this.progressall = function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        jQuery('#usp-uploader-' + this.uploader_id + ' .usp-uploader-progress').html('<div class="usp-progress-bar usps usps__ai-center" style="width:' + progress + '%;">' + progress + '%</div>');
    };

    this.add = function (e, data) {

        var uploader = this;
        var options = uploader.options;

        var errors = [];

        var inGalleryNow = jQuery('#usp-media-' + uploader.uploader_id + ' .usp-media__item').length;

        jQuery.each(data.files, function (index, file) {

            inGalleryNow++;

            if (file.size > options.max_size * 1024) {
                errors.push(USP.errors.file_max_size + '. Max: ' + options.max_size + 'Kb');
            }

        });

        if (options.multiple && inGalleryNow > options.max_files) {
            errors.push(USP.errors.file_max_num + '. Max: ' + options.max_files);
        }

        errors = this.filterErrors(errors, data.files, uploader);

        if (errors.length) {
            errors.forEach(function (error, i) {
                usp_notice(error, 'error', 10000);
            });
            return false;
        }

        if (parseInt(options.crop) != 0 && parseInt(options.multiple) == 0 && typeof jQuery.Jcrop != 'undefined') {
            if (jQuery.inArray(data.files[0].type, [
                'image/png',
                'image/jpg',
                'image/jpeg',
                'image/gif'
            ]) >= 0) {
                return this.crop(e, data);
            }
        }

        data.process().done(function () {
            data.submit();
        });

    };

    this.filterErrors = function (errors, files, uploader) {
        return errors;
    };

    this.submit = function (e, data) {

        this.animateLoading(true);

        if (this.options.crop) {
            return this.submitCrop(e, data);
        }

    };

    this.done = function (e, data) {

        usp_preloader_hide();

        this.animateLoading(false);

        jQuery('#usp-uploader-' + this.uploader_id + ' .usp-uploader-progress').empty();

        if (data.result.error) {
            usp_notice(data.result.error, 'error', 10000);
            return false;
        }

        if (data.result.success) {
            usp_notice(data.result.success, 'success', 10000);
        }

        var uploader = this;

        if (this.options.multiple) {
            jQuery.each(data.result, function (index, file) {

                uploader.appendInGallery(file, uploader);

            });
        } else {

            jQuery('#usp-media-' + this.uploader_id).html('');

            uploader.appendInGallery(data.result, uploader);
        }

        this.afterDone(e, data);

        jQuery('#usp-preview').remove();

    };

    this.appendInGallery = function (file) {

        if (file.html) {
            jQuery('#usp-media-' + this.uploader_id).append(file.html);
            jQuery('#usp-gallery-' + this.uploader_id).append(file.html);
            jQuery('#usp-media-' + this.uploader_id + ' .usp-media__item').last().animateCss('flipInX');
        }
    };

    this.afterDone = function (e, data) {

    };

    this.crop = function (e, data) {

        var uploader = this;
        var crop = uploader.options.crop;
        var minWidthCrop = uploader.options.min_width;
        var minHeightCrop = uploader.options.min_height;

        jQuery.each(data.files, function (index, file) {

            jQuery('#usp-preview').remove();

            var maxSize = parseInt(uploader.options.max_size);

            if (file.size > maxSize * 1024) {
                usp_notice(USP.errors.file_max_size + '. Max:' + ' ' + maxSize + 'Kb', 'error', 10000);
                return false;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                var jcrop_api;
                var imgUrl = event.target.result;

                var maxWidth = window.innerWidth * 0.9;
                var maxHeight = window.innerHeight * 0.8;

                jQuery('body > div').last().after('<div id="usp-preview" class="usps usps__jc-center"><img style="max-width:' + maxWidth + 'px;max-height:' + maxHeight + 'px;" src="' + imgUrl + '"></div>');

                var image = jQuery('#usp-preview img');

                image.load(function () {

                    var img = jQuery(this);
                    var cf = 1;

                    if (img[0].naturalWidth > img.width()) {
                        cf = img.width() / img[0].naturalWidth;
                    }

                    minWidthCrop *= cf;
                    minHeightCrop *= cf;

                    var height = img.height();
                    var width = img.width();

                    if (height < minHeightCrop || width < minWidthCrop) {
                        usp_notice(USP.errors.file_min_size + '. Min:' + ' ' + minWidthCrop + '*' + minHeightCrop + ' px', 'error', 10000);
                        return false;
                    }

                    var jcrop_api;

                    img.Jcrop({
                            aspectRatio: (typeof crop.ratio != 'undefined') ? crop.ratio : 1,
                            minSize: [minWidthCrop, minHeightCrop],
                            onSelect: function (c) {
                                img.attr('data-width', width).attr('data-height', height).attr('data-x', c.x).attr('data-y', c.y).attr('data-w', c.w).attr('data-h', c.h);
                            }
                        },
                        function () {
                            jcrop_api = this;
                        });

                    ssi_modal.show({
                        sizeClass: 'auto',
                        title: USP.local.title_image_upload,
                        className: 'usp-hand-uploader ssi-no-padding',
                        buttons: [{
                            className: 'btn-cancel',
                            label: USP.local.cancel,
                            closeAfter: true,
                            method: function () {
                                jcrop_api.destroy();
                            }
                        }, {
                            className: 'btn-success',
                            label: USP.local.upload,
                            closeAfter: true,
                            method: function () {
                                data.submit();
                            }
                        }],
                        content: jQuery('#usp-preview'),
                        extendOriginalContent: true
                    });

                });

            };

            reader.readAsDataURL(file);

        });

    };

    this.submitCrop = function (e, data) {

        data.formData = this.getFormData();

        var image = jQuery('#usp-preview img');

        if (parseInt(image.data('w'))) {

            var width = image.data('width');
            var height = image.data('height');
            var x = image.data('x');
            var y = image.data('y');
            var w = image.data('w');
            var h = image.data('h');

            data.formData.crop_data = [x, y, w, h];
            data.formData.image_size = [width, height];

        }

    };

    this.animateLoading = function (status) {
        if (status)
            this.button.addClass('usp-bttn__loading');
        else
            this.button.removeClass('usp-bttn__loading');
    };

}

function usp_init_uploader(props, securityKey) {
    USPUploaders.add(props, securityKey);
}

function usp_init_dropzone(dropZone) {

    jQuery(document.body).bind("drop", function (e) {
        var node = e.target, found = false;

        if (dropZone[0]) {
            dropZone.removeClass('in-dropzone hover-dropzone');
            do {
                if (node === dropZone[0]) {
                    found = true;
                    break;
                }
                node = node.parentNode;
            } while (node != null);

            if (found) {
                e.preventDefault();
            } else {
                return false;
            }
        }
    });

    dropZone.bind('dragover', function (e) {
        var timeout = window.dropZoneTimeout;

        if (!timeout) {
            dropZone.addClass('in-dropzone');
        } else {
            clearTimeout(timeout);
        }

        var found = false, node = e.target;

        do {
            if (node === dropZone[0]) {
                found = true;
                break;
            }
            node = node.parentNode;
        } while (node != null);

        if (found) {
            dropZone.addClass('hover-dropzone');
        } else {
            dropZone.removeClass('hover-dropzone');
        }

        window.dropZoneTimeout = setTimeout(function () {
            window.dropZoneTimeout = null;
            dropZone.removeClass('in-dropzone hover-dropzone');
        }, 100);
    });
}

function usp_delete_attachment(attachment_id, post_id, e) {

    if (e)
        usp_preloader_show(jQuery(e).parents('.usp-media__item'));

    var objectData = {
        action: 'usp_ajax_delete_attachment',
        post_id: post_id,
        attach_id: attachment_id
    };

    usp_ajax({
        rest: true,
        data: objectData,
        success: function (data) {

            jQuery('.usp-media__item-' + attachment_id).animateCss('flipOutX', function (e) {
                jQuery(e).remove();
            });

        }
    });

    return false;
}

function usp_add_attachment_in_editor(attach_id, editor_name, e) {

    var image = jQuery(e).data('html');
    var src = jQuery(e).data('src');

    if (src)
        image = '<a href="' + src + '">' + image + '</a>';

    jQuery("textarea[name=" + editor_name + "]").insertAtCaret(image + "&nbsp;");

    if (typeof tinyMCE != 'undefined') {
        tinyMCE.editors.forEach(function (editor) {

            if (editor.targetElm.name.length === editor_name.length) {
                editor.execCommand('mceInsertContent', false, image);
            }
        });
    }

    return false;
}