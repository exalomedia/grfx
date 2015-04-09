(function ($) {
    "use strict";

    $(function () {

        var last_opt = 1;
                
        $('#grfx-product-option').on('change', function () {
            $('#grfx-option-description-' + last_opt).removeClass('show'); 
            $('#grfx-option-description-' + last_opt).hide();     
            var opt = $(this).find(':selected').data('summary');         
            $('#grfx-option-description-' + opt).fadeIn('slow');
            $('#grfx-option-description-' + opt).addClass('show');
            last_opt = opt;
        });


    });



}(jQuery));