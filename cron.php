<?php
/**
 * Main grfx cron job file
 *
 * @package grfx
 * @subpackage grfx_Cron
 */

$CRON_TEST = true;

if($CRON_TEST == true)
	error_reporting( E_ALL );

/**
 * FIRST, Verify there are uploads before loading any sort of process. ----   ----   ----   ----   ----   
 */

$uploads = array();

$files = scandir( dirname( __FILE__ ) . '/../../uploads/grfx_uploads/protected/' );

if ( $files ) {
    
    $signature = false;
    $filesum   = false;
    
    if(file_exists(trailingslashit(grfx_tmp_dir()).'filesum'))
        $filesum = file_get_contents (trailingslashit(grfx_tmp_dir()).'filesum');
    
	foreach ( $files as $file ) {
		if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == '.ftpquota' )
			continue;
		array_push( $uploads, $file );
	}
    
  /**
     * This little operation writes a unique string to see if our file collection has changed at all
     * If it hasn't changed, that means they are sitting there but not queued.  We will do a comparison 
     * to determine whether or not to proceed.
     */
    if(!empty($files)){
        $signature = md5(implode('', $files));        
    }
    
   if($signature && $filesum){
     
       if($signature == $filesum)
           die(':(');
   } else {
       file_put_contents(trailingslashit(grfx_tmp_dir()).'filesum', $signature);
   }
    
}

if ( empty( $uploads ) )
	die(':)');










/**
 * SECOND if files, start to process ----   ----   ----   ----   ----   ----   ----   ----   ----   ----   
 */


/**
 * Initial Cron Job Setup
 */
function grfx_start_cron(){
	$cron = new grfx_Cron();	
}

grfx_start_cron();

if($CRON_TEST == true)
	grfx_get_memory_use();