
/*
 * jQuery.ajaxQueue - A queue for ajax requests
 * 
 * (c) 2011 Corey Frang
 * Dual licensed under the MIT and GPL licenses.
 *
 * Requires jQuery 1.5+
 */
(function ($) {

// jQuery on an empty object, we are going to use this as our Queue
    var ajaxQueue = $({});

    $.ajaxQueue = function (ajaxOpts) {
        var jqXHR,
                dfd = $.Deferred(),
                promise = dfd.promise();

        // queue our ajax request
        ajaxQueue.queue(doRequest);

        // add the abort method
        promise.abort = function (statusText) {

            // proxy abort to the jqXHR if it is active
            if (jqXHR) {
                return jqXHR.abort(statusText);
            }

            // if there wasn't already a jqXHR we need to remove from queue
            var queue = ajaxQueue.queue(),
                    index = $.inArray(doRequest, queue);

            if (index > -1) {
                queue.splice(index, 1);
            }

            // and then reject the deferred
            dfd.rejectWith(ajaxOpts.context || ajaxOpts,
                    [promise, statusText, ""]);

            return promise;
        };

        // run the actual query
        function doRequest(next) {
            jqXHR = $.ajax(ajaxOpts)
                    .done(dfd.resolve)
                    .fail(dfd.reject)
                    .then(next, next);
        }

        return promise;
    };

})(jQuery);

/**
 * grfx functions
 * 
 * @param {type} $
 * @returns {undefined}
 */
(function ($) {
    "use strict";

    $(function () {

        var loading_img = $('.grfx-loader-gif');

        //uncheck all checkboxes on page load (in case they are checked from previous interaction)
        $('#grfx-upload-manager tbody tr').find('td:first :checkbox').each(function () {
            $(this).prop('checked', false);
        });

        /*
                * Upload Manager Extras
                */
                    

        $('#grfx-upload-manager #cb-select-all-1, #grfx-upload-manager #cb-select-all-2').on('click', function () {
            var checkedStatus = this.checked;
            $('#grfx-upload-manager tbody tr').find('td:first :checkbox').each(function () {
                $(this).prop('checked', checkedStatus);
            });
        });

        //are we processing now?
        var process_now = 0;        

        if ($('#grfx-process-now').length) {
            $('#grfx-process-now').change(
                    function () {
                        if ($(this).is(':checked')) {
                            process_now = 1;
                        } else {
                            process_now = 0
                        }
                    });
        }
        
        /*
                * Uploader wordpress-Ajax Functionality
                */


        /**
                * Function for processing various ajax operations
                * @param {type} selected
                * @returns {undefined}
                */
        function do_ajax(selected, process_action, iteration) {

            var ajax_data = {
                'action': 'grfx_ajax',
                'whatever': ajax_object.we_value,
                'grfx-upload-ids': selected,
                'grfx-process-action': process_action,
                'grfx-process-now': process_now,
                async: false
            };

            $.ajaxQueue({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: ajax_data,
                success: function (response) {
                    //alert('Got this from the server: ' + response);

                    //if deleting uploads
                    if ($('#grfx-upload-process-option').val() == 3 || $('#grfx-upload-process-option').val() == 4) {
                        location.reload();
                    }

                    //if processing uploads
                    if ($('#grfx-upload-process-option').val() == 1 || $('#grfx-upload-process-option').val() == 2) {


                        if (!response) {
                            return false;
                        }

                        var results = JSON.parse(response);
                        var message = results.message;
                        var processed_id = results.processed_id;

                        $(message).appendTo('#grfx-upload-row-' + processed_id + ' .grfx-entry-original-name');

                        $('#grfx-upload-row-' + processed_id).addClass('grfx-upload-complete');
                        //grfx-upload-processing
                        $('#grfx-upload-row-' + processed_id).removeClass('grfx-upload-processing');

                        /**
                                                setTimeout(function () {
                                                    $('#grfx-upload-row-' + processed_id).fadeOut(3000);
                                                }, 5000);
                                                */
                       
                        //console.log(iteration);
                    }
                    return true;
                }
            });
        }

        $("#grfx-upload-manager").submit(function (e) {
            e.preventDefault();

            //get selected items
            var selected = [];
            $('#grfx-upload-manager tbody input:checked').each(function () {
                selected.push($(this).attr('value'));
            });

            //which process are we creating?
            var process_action = $('#grfx-upload-process-option').val();

            //make decisions
            if (process_action == 3 || process_action == 4) {
                do_ajax(selected, process_action);
            }

            if (process_action == 1 || process_action == 2) {

                var iteration = 0;

                //loading_img.show();


                $.each(selected, function (key, value) {

                    $('#grfx-upload-row-' + value + ' .grfx-entry-original-name').append('<span class="grfx-processing">...</span>');
                    $('#grfx-upload-row-' + value).addClass('grfx-upload-processing');

                    do_ajax(value, process_action, iteration);
                    iteration = iteration + 1;
                });
            }
        });
    });
}(jQuery));
