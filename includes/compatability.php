<?php


define('grfx_version', '1.1.81');

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * If functionality is limited on given server, suggest different option
 * @return string
 */
function grfx_proper_hosting(){
    return ' <small><a href="//www.bluehost.com/track/grfx/" target="_blank"> Bluehost</a> '.__(' is recommended for hosting grfx as it allows the perfect environment for <strong>grfx</strong> functionality.', 'grfx').'</small>';
}


/**
 * Ensures that the user tries to fix the problems instead of abandoning the 
 * plugin.
 * 
 * @package grfx
 * @subpackage Compatability
 * @return boolean|string
 */
function grfx_encourage_fix(){
    return '<br />'. __('Please resolve these problems and <strong>grfx</strong> should work great for you. If you cannot resolve them on your current host, ', 'grfx').
        grfx_proper_hosting();
}


/**
 * Checks that the version is correct;
 * 
 * @package grfx
 * @subpackage Compatability
 * @return boolean|string
 */
function grfx_version_check($wp = '4.2', $php = '5.4'){
        
    global $wp_version;
    
    if ( version_compare( PHP_VERSION, $php, '<' ) )
        $flag = 'PHP';
    
        elseif
            ( version_compare( $wp_version, $wp, '<' ) )
            $flag = 'WordPress';
        else
            return;
        
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );        
        deactivate_plugins( basename( __FILE__ ) );
        
        $wp_message = __('grfx requires at least Wordpress version ', 'grfx') . $wp.';';
        $php_message = __('grfx requires at least PHP version ', 'grfx') . $php.';';
        
        wp_die($wp_message . ' <br /> ' . $php_message .grfx_encourage_fix(), 
                __( $flag. ' Version problem. Please upgrade Wordpress or PHP.', 'grfx'),  
                array( 'response'=>200, 'back_link'=>TRUE ) );        

}

grfx_version_check();

/**
 * Checks that crucial functions are included before setting up system
 * 
 * @package grfx
 * @subpackage Compatability
 * @return boolean|string
 */
function grfx_check_functions(){
    
    if(!function_exists('mcrypt_get_iv_size')){
        
        wp_die(
                __('Please set up php library mcrypt on this server. Ask your host to do it for you, or try adding <strong>extension=mcryp.so</strong> to your <strong>php.ini</strong> file.').grfx_encourage_fix(), 
                __('Missing php library: mcrypt', 'grfx'), 
                array( 'response'=>200, 'back_link'=>TRUE )
            );
        
    }
    
}

/**
 * This constant should be checked by dependent grfx plugins. If it is not defined,
 * the plugin should self-deactivate.
 * 
 * @package grfx
 * @subpackage Compatability
 */
function grfx_core_active() {
    define('grfx_core_active', 1);
}

grfx_core_active();

