<?php
/**
 * grfx Uploader HTML and Javascript
 *
 * @package grfx
 * @subpackage grfx_Uploader
 */

class grfx_Uploader{
 
    public function __construct(){
		
		/*
		 * This is necessary in case the plugin has been upgraded, we must re-activate the library
		 */
		if(!is_executable (grfx_core_plugin . 'admin/includes/exiftool/exiftool'))
			exec('chmod a+x '.grfx_core_plugin.'admin/includes/exiftool/exiftool');
    }
    
	/**
	 * Prints out HTML for uploader. The javascript library itself populates the actual uploader, this 
	 * is just a foundation.
	 */
	public function render_html(){
        ?>
        <div id="wpbody">
            <div class="wrap grfx-uploader">
                <h2><?php _e( 'grfx Content Uploader', 'grfx' ) ?></h2>
                <br />
                <div id="uploader">
                    <div><?php _e('Your browser doesn\'t have Flash, Silverlight or HTML5 support.', 'grfx') ?></div>
                    <a id="pickfiles" href="javascript:;">[<?php _e('Select files', 'grfx') ?>]</a> 
                    <a id="uploadfiles" href="javascript:;">[<?php _e('Upload files', 'grfx') ?>]</a>
                </div>
				<?php 
				$this->show_upload_form();
				?>				
            </div>  
        </div>        
        <?php
    }
    
	
    /**
           * Prints out Javascript 
           */
    public function render_js(){
        
        $js = grfx_plugin_dir().'admin/includes/uploader/plupload/js/';
        
        $moxie_swf = $js.'Moxie.swf';
        $moxie_xap = $js.'Moxie.xap';
        $upload_php = grfx_plugin_dir().'admin/includes/uploader/plupload/upload.php';
        
        ?>
        
        <script type="text/javascript">

        (function ( $ ) {
                "use strict";
                // Initialize the widget when the DOM is ready
                $(function() {
					//$('#grfx-upload-row-template').hide();
                    // Setup html5 version
                    $("#uploader").pluploadQueue({
                        // General settings
                        runtimes : 'html5,flash,silverlight,html4',
                        url : '<?php echo $upload_php; ?>',

                        chunk_size : '1mb',
                        rename : true,
                        dragdrop: true,

                        filters : {
                            // Maximum file size
                            max_file_size : '100mb',
                            // Specify what files to browse for
                            mime_types: [
                                {title : "Image files", extensions : "jpg,jpeg,png,psd"},
                                {title : "Zip files", extensions : "zip,tar,gz,tar.gz,tar.bz2"},
								{title : "Vector files", extensions : "ai,eps,svg"},
                                /*{title : "Vector files", extensions : "eps,ai,svg"}*/
                            ]
                        }, 

                        // Flash settings
                        flash_swf_url : '<?php echo $moxie_swf; ?>',

                        // Silverlight settings
                        silverlight_xap_url : '<?php echo $moxie_xap; ?>'
                    });
                    
                    var uploader = $('#uploader').pluploadQueue();

					uploader.bind('FileUploaded', function(up, file, response) {
						
						var new_upload = jQuery.parseJSON(response.response); 			
												
						if(new_upload.complete == true){
							var info = new_upload.upload_object;
							
							var new_row = $('#grfx-upload-row-template').clone();
					
							//Remove ID to avoid bad HTML
							new_row.attr("id","grfx-upload-row-"+new_upload.upload_id);
																	
							//Set up info in table row
							new_row.find('.grfx-entry-original-name').html(info.original_name);
							new_row.find('.grfx-entry-file-type').html(info.file_mime);
							new_row.find('.grfx-entry-extension').html(info.extension);
							new_row.find('.grfx-entry-file-size').html('-');
							new_row.find('.grfx-entry-datetime').html('-');
							new_row.find('.grfx-entry-upload-id input').val(new_upload.upload_id);
							
							//append and fade in new row
							new_row.prependTo("tbody");
							new_row.show();							
							
						}
						

					});
					
                    uploader.bind('UploadComplete', function() {
                        if (uploader.files.length == (uploader.total.uploaded + uploader.total.failed)) {
                            //location.reload();
                        }
                    });              
                    
                });  
                
        }(jQuery));        
        
        </script>    
        
        <?php
    }
    
	/**
	   * Displays uploads by user.
	   */
	public function show_upload_form(){
		
		$user_uploads = new grfx_Upload_Tracker();
		$user_uploads->clean_uploads_dir();
		$uploads = $user_uploads->get_user_uploads();	
		?>
		<form id="grfx-upload-manager" method="post" action="">
			<table class="wp-list-table widefat">
				<thead>
				<tr>
					<th id="cb" class="manage-column column-cb check-column" style="" scope="col">
						<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
						<input id="cb-select-all-2" class="" type="checkbox" name="grfx_upload_id[]" value="" />
					</th>
					<th>
						<?php _e('File Name', 'grfx') ?>
					</th>
					<th>
						<?php _e('File Type', 'grfx') ?>
					</th>
					<th>
						<?php _e('Extension', 'grfx') ?>
					</th>
					<th>
						<?php _e('Size', 'grfx') ?>
					</th>
					<th>
						<?php _e('Uploaded', 'grfx') ?>
					</th>						
				</tr>
				</thead>
				<tbody>
					<tr id="grfx-upload-row-template" class="grfx-new-upload">
						<td class="grfx-entry-upload-id">
							<label class="screen-reader-text"><?php _e('Process Item', 'grfx') ?></label>
							<input type="checkbox" name="grfx_upload_id[]" checked="checked" value="" />
						</td>
						<td class="grfx-entry-original-name">#</td>
						<td class="grfx-entry-file-type">#</td>
						<td class="grfx-entry-extension">#</td>
						<td class="grfx-entry-file-size">#</td>
						<td class="grfx-entry-datetime">#</td>							
					</tr>						
					<?php
					if($uploads):
						$i = 0;
						$total_size = 0;
						foreach($uploads as $u):
							
							if($u->enqueued == 1){
								$class_enqueued = __('grfx-upload-complete', 'grfx');								
								if($u->to_draft == 1){
									$message = '<span class="grfx-success">' . __( 'Enqueued (to draft)', 'grfx' ) . '</span>';
								} else {
									$message = '<span class="grfx-success">' . __( 'Enqueued (to publish)', 'grfx' ) . '</span>';
								}
								
							} else {
								$class_enqueued = '';
								$message = '';
							}
							
						?>								
					<tr id="grfx-upload-row-<?php echo $u->upload_id ?>"  class="<?php echo ($i % 2 == 0)?'alternate':'' ?> <?php echo $class_enqueued; ?>">
						<td>
							<label class="screen-reader-text" for="cb-select-<?php echo $i ?>"><?php _e('Process Item', 'grfx') ?></label>
							<input id="cb-select-<?php echo $i ?>" type="checkbox" name="grfx_upload_id[]" value="<?php echo $u->upload_id ?>" />
						</td>
						<td class="grfx-entry-original-name">
							<?php echo $u->original_name ?> <?php echo $message ?>
						</td>
						<td class="grfx-entry-file-type">
							<?php echo $u->file_type ?>
						</td>
						<td class="grfx-entry-extension">
							<?php echo $u->extension ?>
						</td>
						<td class="grfx-entry-file-size">
							<?php
								$total_size += $u->file_size;
								echo grfx_format_size($u->file_size) 
							?>
						</td>
						<td class="grfx-entry-datetime">
							<?php
							echo date("h:i:s a m/d/Y",strtotime($u->datetime));	
							?>							
						</td>							
					</tr>
						
						<?php
						$i++;
						endforeach;
					endif;
					?>
				</tbody>
				<tfoot>
					<tr>
					<th id="cb" class="manage-column column-cb check-column" style="" scope="col">
						<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
						<input id="cb-select-all-2" type="checkbox">
					</th>
					<th>
						<?php _e('File Name', 'grfx') ?>
					</th>
					<th>
						<?php _e('File Type', 'grfx') ?>
					</th>
					<th>
						<?php _e('Extension', 'grfx') ?>
					</th>
					<th>
						<?php _e('Size', 'grfx') ?>
					</th>
					<th>
						<?php _e('Uploaded', 'grfx') ?>
					</th>					
					</tr>
				</tfoot>
			</table>
			
			<?php if($uploads): ?>
			<p><?php echo $i . ' <span id="grfx-total-files">' . __('Files', 'grfx') . '</span><span id="grfx-total-files-size">, ' . grfx_format_size($total_size) . '</span>' ?></p>
			<?php endif;?>
			
			<?php if(!is_multisite()): ?>	
			<br />
			<label for="grfx-process-now">
				<input id="grfx-process-now" type="checkbox" name="grfx_process_now" value="" />
				<em><?php _e('Process Now...', 'grfx') ?></em>
			</label>
			<br /><br />
			<?php endif; ?>			
			
			<select id="grfx-upload-process-option" name="grfx_upload_process_option">
				<option value="1"><?php _e('Process to Draft', 'grfx') ?></option>
				<option value="2"><?php _e('Process to Publish', 'grfx') ?></option>
				<option value="3"><?php _e('Delete Selected', 'grfx') ?></option>
				<option value="4"><?php _e('Delete All', 'grfx') ?></option>
			</select>

			<?php
			submit_button( __('Apply', 'grfx'), 'submit', 'grfx_process_images', false) 
			?>
		</form>
		<br /><br /><hr /><br />
		
		<?php
		//get the loading gif
		echo grfx_loading_img();
		
		//if(!is_multisite()):
		//$phpPath = grfx_get_php_binary();
	
		//endif;
		
	}
	
	
}
