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

/*
 * Do not allow to be triggered remotely
 */
if ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']){
  $this->output->set_status_header(400, 'No Remote Access Allowed');
  exit; //just for good measure
}




/**
 * FIRST, Verify there are uploads before loading any sort of process. ----   ----   ----   ----   ----   
 */




$uploads = array();

$files = scandir( dirname( __FILE__ ) . '/../../uploads/grfx_uploads/protected/' );

if ( $files ) {
	foreach ( $files as $file ) {
		if ( $file == '.' || $file == '..' || $file == '.htaccess' )
			continue;
		array_push( $uploads, $file );
	}
}

if ( empty( $uploads ) )
	die(':)');










/**
 * SECOND if files, start to process ----   ----   ----   ----   ----   ----   ----   ----   ----   ----   
 */




define('grfx_DOING_CRON', true);

//define('SHORTINIT', true);
require_once("../../../wp-load.php");
require_once(ABSPATH.'wp-includes/formatting.php');

if (!defined('grfx_core_plugin'))
	define('grfx_core_plugin', trailingslashit(plugin_dir_path(__FILE__)));


require_once('includes/globals.php');
require_once('includes/functions.php');
require_once('includes/class-cron.php');

/**
 * Initial Cron Job Setup
 */
function grfx_start_cron(){
	$cron = new grfx_Cron();	
}

grfx_start_cron();

if($CRON_TEST == true)
	grfx_get_memory_use();