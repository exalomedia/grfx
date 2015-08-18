(function ($) {
    "use strict";

    $(function () {
        /*
         * Initially go through these rather redundant steps to ensure the 
         * product options show. Different themes effect this differently, so
         * we proceed to reset visuals with every page load.
         */
        var last_opt = $('#grfx-product-option').val();
             
        
        $('#grfx-product-option').on('change', function () {
            
            $('.grfx-options-descriptions').hide();                        
            $('.grfx-options-descriptions').removeClass('show'); 

            $('#grfx-option-description-' + last_opt).removeClass('show'); 
            $('#grfx-option-description-' + last_opt).hide();     
            var opt = $(this).find(':selected').data('summary');         
            $('#grfx-option-description-' + opt).fadeIn('slow');
            $('#grfx-option-description-' + opt).addClass('show');
            last_opt = opt;
        });
        
    });
}(jQuery));