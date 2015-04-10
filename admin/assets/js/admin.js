(function ($) {
    "use strict";

    $(function () {
        
        //get rid of annoying upgrade and pro notices
        if ( $( 'a[href^="admin.php?page=metaslider-go-pro"]' ).length ) {
            $('a[href^="admin.php?page=metaslider-go-pro"]').parent().remove();
        }
        
        
        $(".grfx-uploader-cron, .grfx-ftp").click(function () {
            $(this).select();
        });
        
        if ($('#license-accordion').length) {
            $('#license-accordion').accordion();
        }
        
        /**
                 * Watermark Uploader
                 */
        var custom_uploader;

        $('#grfx_upload_watermark_button').click(function(e) {

            e.preventDefault();

            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }

            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            //When a file is selected, grab the URL and set it as the text field's value
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                //alert(JSON.stringify(attachment));
                $('#grfx_watermark_location').val(attachment.url);
                $('#grfx_watermark_image_id').val(attachment.id);
                $('#grfx_watermark_image_preview img').attr("src", attachment.url);
            });

            //Open the uploader dialog
            custom_uploader.open();
            
          
            
        });  
    });
}(jQuery));