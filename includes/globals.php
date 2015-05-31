<?php

/**
 * grfx's Constants, unchanging paths and ... Constants. These are 
 * declared as functions which define constants, that way adjustments can be
 * made for multisite or basic installs. The functions return a constant, or 
 * the constants can be called directly.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package    grfx
 * @subpackage grfx_Core
 * @author     Leo Blanchette <leo@grfx.com>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       http://www.grfx.com
 */


/* ----------------------------------------------------------------------------*
 * FILE PATHS (GENERAL)
 * ---------------------------------------------------------------------------- */

/**
 * File path to the 'grfx_tmp' content directory located in wp-content directory of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_tmp_dir() {
    if (!defined('grfx_tmp_dir'))
        define('grfx_tmp_dir', trailingslashit(grfx_core_plugin).'tmp/');
    return grfx_tmp_dir;
}

grfx_tmp_dir();

/**
 * File path to the 'grfx_uploads' content directory located in wp-content directory of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_uploads_dir() {
    if (!defined('grfx_uploads_dir'))
        define('grfx_uploads_dir', trailingslashit(WP_CONTENT_DIR).'uploads/grfx_uploads/');
    return grfx_uploads_dir;
}

grfx_uploads_dir();

/**
 * File path to the 'grfx_ftp' content directory located in wp-content directory of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_ftp_dir() {
    if (!defined('grfx_ftp_dir'))
        define('grfx_ftp_dir', trailingslashit(WP_CONTENT_DIR).'uploads/grfx_uploads/ftp/');
    return grfx_ftp_dir;
}

grfx_ftp_dir();


/**
 * File path to the 'protected' content directory located in wp-content/grfx directory of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_protected_uploads_dir() {
    if (!defined('grfx_protected_uploads_dir'))
        define('grfx_protected_uploads_dir', grfx_uploads_dir().'protected/');
    return grfx_protected_uploads_dir;
}

grfx_protected_uploads_dir();

/**
 * File path to the 'content' content directory located in wp-content/grfx directory of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_content_uploads_dir() {
    if (!defined('grfx_content_uploads_dir'))
        define('grfx_content_uploads_dir', grfx_uploads_dir().'content/');
    return grfx_content_uploads_dir;
}

grfx_content_uploads_dir();


/**
 * File path to the 'delivery' content directory located in wp-content/grfx directory of site, 
 * used for deliverying files to customer
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_delivery_dir() {
    if (!defined('grfx_delivery_dir'))
        define('grfx_delivery_dir', grfx_uploads_dir().'delivery/');
    return grfx_delivery_dir;
}


/**
 * File path to the 'grfx' content directory located at root of site.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_product_dir() {
    if (!defined('grfx_product_dir'))
        define('grfx_product_dir', trailingslashit(ABSPATH).'grfx/');
    return grfx_product_dir;
}

grfx_product_dir();


/**
 * File path to the download script which performs custom actions on download.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_download_script() {
	
	if(defined('grfx_DOING_CRON'))
		return;	
	
    if (!defined('grfx_download_script'))
        define('grfx_download_script', grfx_core_plugin() . 'includes/download.php');
    return grfx_download_script;
}


/**
 * File path to the 'grfx' assets directory.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_assets_dir() {
	
	if(defined('grfx_DOING_CRON'))
		return;	
	
    if (!defined('grfx_assets_dir'))
        define('grfx_assets_dir', grfx_plugin_url() . 'assets/');
    return grfx_assets_dir;
}

grfx_assets_dir();

/**
 * File path to the 'grfx' includes directory.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_includes_dir() {
	
	if(defined('grfx_DOING_CRON'))
		return;	
	
    if (!defined('grfx_includes_dir'))
        define('grfx_includes_dir', grfx_plugin_url(). 'includes/');
    return grfx_includes_dir;
}

grfx_includes_dir();


if(is_multisite()){
	
	$grfx_SITE_ID = get_current_blog_id();
	
} else {
	
	$grfx_SITE_ID = 0;
	
}


/* ----------------------------------------------------------------------------*
 * Executables - File exec paths to included programs within grfx Core
 * ---------------------------------------------------------------------------- */

/**
 * Path to Exiftool 
 * 
 * @url http://www.sno.phy.queensu.ca/~phil/exiftool/
 * @package grfx
 * @subpackage Constants
 */
function grfx_exiftool() {
    if (!defined('grfx_exiftool'))
        define('grfx_exiftool', grfx_core_plugin . 'admin/includes/exiftool/./exiftool');
    return grfx_exiftool;
}

grfx_exiftool();

/* ----------------------------------------------------------------------------*
 * Booleans - Typical yes or no true or false things regarding state of environment and plugin
 * ---------------------------------------------------------------------------- */

/**
 * Whether or not imagick (php's imagemagick wrapper) is installed
 * 
 * @url http://php.net/manual/en/book.imagick.php
 * @package grfx
 * @subpackage Constants
 */
function grfx_use_imagick() {
	
	if( extension_loaded( 'imagick' ) || class_exists("Imagick") || function_exists("NewMagickWand") ){
		$imagick_installed = true;
	} else {
		$imagick_installed = false;
	}
	
    if (!defined('grfx_use_imagick'))
        define('grfx_use_imagick', $imagick_installed);
    return grfx_use_imagick;
}

grfx_use_imagick();

/**
 * Whether or not host has enabled shell_exec
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_use_shell_exec() {
    $has_shell_exec = is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec');
    
    if (!defined('grfx_use_shell_exec'))
        define('grfx_use_shell_exec', $has_shell_exec);  
    
    return $has_shell_exec;
    
}

grfx_use_shell_exec();

/* ----------------------------------------------------------------------------*
 * GLOBAL variable names
 * ---------------------------------------------------------------------------- */

$grfx_size_default_names = array(
	'_size_name_1' => __('Small JPEG', 'grfx'),
	'_size_name_2' => __('Medium JPEG', 'grfx'),	
	'_size_name_3' => __('Large JPEG', 'grfx'),	
	'_size_name_4' => __('Vector', 'grfx'),	
	'_size_name_5' => __('Supporting File', 'grfx'),	
	'_size_name_6' => __('Extended', 'grfx'),	
	'_size_name_7' => __('', 'grfx'),	
	'_size_name_8' => __('', 'grfx')
	);

$grfx_size_default_prices = array(
	'_size_price_1' => __('1', 'grfx'),
	'_size_price_2' => __('3', 'grfx'),	
	'_size_price_3' => __('5', 'grfx'),	
	'_size_price_4' => __('8', 'grfx'),	
	'_size_price_5' => __('16', 'grfx'),	
	'_size_price_6' => __('100', 'grfx'),
	'_size_price_7' => __('500', 'grfx'),	
	'_size_price_8' => __('1000', 'grfx')
	);

$grfx_size_default_pixels = array(
	'_size_pixels_1' => __('500', 'grfx'),
	'_size_pixels_2' => __('1000', 'grfx'),	
	'_size_pixels_3' => __('3000', 'grfx'),	
	'_size_pixels_4' => __('0', 'grfx'),	
	'_size_pixels_5' => __('0', 'grfx'),	
	'_size_pixels_6' => __('0', 'grfx'),	
	'_size_pixels_7' => __('0', 'grfx'),	
	'_size_pixels_8' => __('0', 'grfx')
	);

$grfx_size_default_license = array(
	'_size_license_1' => __('Royalty-free', 'grfx'),
	'_size_license_2' => __('Royalty-free', 'grfx'),	
	'_size_license_3' => __('Royalty-free', 'grfx'),	
	'_size_license_4' => __('Royalty-free', 'grfx'),	
	'_size_license_5' => __('Extended Royalty-free', 'grfx'),	
	'_size_license_6' => __('Buyout Royalty-free', 'grfx'),	
	'_size_license_7' => __('', 'grfx'),	
	'_size_license_8' => __('', 'grfx')
	);

$grfx_size_enabled = array(
	'_size_enabled_1' => __('yes', 'grfx'),
	'_size_enabled_2' => __('yes', 'grfx'),	
	'_size_enabled_3' => __('yes', 'grfx'),	
	'_size_enabled_4' => __('yes', 'grfx'),	
	'_size_enabled_5' => __('yes', 'grfx'),	
	'_size_enabled_6' => __('yes', 'grfx'),	
	'_size_enabled_7' => __('yes', 'grfx'),	
	'_size_enabled_8' => __('yes', 'grfx')
	);

$delivery_defaults = array(
	'_size_type_enabled_1' => '1',	
	'_size_type_enabled_2' => '1',	
	'_size_type_enabled_3' => '1',	
	'_size_type_enabled_4' => '5',	
	'_size_type_enabled_5' => '7',	
	'_size_type_enabled_6' => '1',	
	'_size_type_enabled_7' => '1',	
	'_size_type_enabled_8' => '1',
);

$grfx_file_type_key = array(
	
	'1' => array(
		'name' => __('Jpeg Image', 'grfx'),
		'extension' => 'jpg jpeg'
		),
	
	'2' => array(
		'name' => __('PNG Image', 'grfx'),
		'extension' => 'png'
			),			
	
	'3' => array(
		'name' => __('Photoshop File', 'grfx'),
		'extension' => 'psd'
			),		
	
	'4' => array(
		'name' => __('Adobe Illustrator File', 'grfx'),
		'extension' => 'ai'
			),	
		
	'5' => array(
		'name' => __('Encapsulated Post Script', 'grfx'),
		'extension' => 'eps'
			),	
	'6' => 	array(
		'name' => __('Scalable Vector Graphic', 'grfx'),
		'extension' => 'svg'
			),	
	
	'7' => array(
		'name' => __('Zip Archive', 'grfx'),
		'extension' => 'zip tar gz tar.gz tar.bz2"'
			),	
);

/**
 * Returns an array of types key values. Technically not a constant but listed among referencable 
 * values that do not change.
 * 
 * @package grfx
 * @subpackage Constants 
 * @global array $grfx_file_type_key
 * @return string
 */
function grfx_filetypes(){
	
	global $grfx_file_type_key;
	
	$types = array();
	
	foreach($grfx_file_type_key as $key=>$type){
		$types[$key] = $type['extension'].' &mdash; '.$type['name'];
	}
	
	return $types;
}
