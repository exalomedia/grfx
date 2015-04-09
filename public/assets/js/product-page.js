(function ($) {
    "use strict";

    $(function () {

        $('.grfx-modal').easyModal({
            top: 50,
            overlay: 0.2
        });

        $('.grfx-modal-open').click(function (e) {
            var target = $(this).attr('href');
            $(target).trigger('openModal');
            e.preventDefault();
        });

        $('.grfx-modal-close').click(function (e) {
            $('.grfx-modal').trigger('closeModal');
        });

        /*
         * We dynamically get license data on deman so as not to harm page SEO
         */
        $('.grfx-modal-open').live('click', function () {

            $('.grfx-loader-gif').show();
            
            var license = $(this).data('license');

            var ajax_data = {
                'action': 'grfx_ajax',
                'grfx-get-license': license,
                async: false
            };

            $.ajax({
                type: 'POST',
                url: wc_add_to_cart_params.ajax_url,
                data: ajax_data,
                success: function (response) {
       
                    var results = JSON.parse(response);
                    var title   = results.title;
                    var text    = results.text;
                    
                    $('#grfx-license-title').html(title);
                    $('#grfx-license-text').html(text);
                        
                    $('.grfx-loader-gif').hide();
                    
                    return true;
                }
            });
        });


    });

}(jQuery));