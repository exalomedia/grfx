<?php

/**
 * grfx Woocommerce Settings Integration
 *
 * @package grfx
 * @subpackage grfx_Ajax
 */
add_action( 'wp_ajax_grfx_ajax', 'grfx_ajax_callback' );
add_action( 'wp_ajax_nopriv_grfx_ajax', 'grfx_ajax_callback' );


/**
 * The simple Ajax functionality of grfx. This function is the main route through which 
 * ajax interactions pass.
 * 
 * @global object $wpdb Wordpress database object
 */
function grfx_ajax_callback() {
	global $wpdb; // this is how you get access to the database
	define('grfx_AJAX', true);
	
	if(isset($_POST['grfx-get-license'])){		
		
		if(  !is_numeric( $_POST['grfx-get-license'] ))
			return 'error';
		
		$license_title = get_option( 'grfx_license_name_'.$_POST['grfx-get-license'] );
		$license_text  = get_option( 'grfx_license_text_'.$_POST['grfx-get-license'], __('No license found!', 'grfx') );
		
		$license = array(
			'title' => stripslashes($license_title),
			'text'  => nl2br(stripslashes($license_text)),
		);
		
		echo json_encode($license);
		
	}	
	
	if(isset($_POST['grfx-process-action'])){		
		require_once('includes/uploader/class-upload-tracker.php');
		$upload_tracker = new grfx_Upload_Tracker();			
		$upload_tracker->process_uploads();
	}
	
	wp_die(); // this is required to terminate immediately and return a proper response
}