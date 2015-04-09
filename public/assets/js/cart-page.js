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

    });

}(jQuery));