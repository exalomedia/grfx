<?php
/**
 * Main functions file for grfx.
 * 
 * This is a collection of functions which are used in the core plugin and 
 * can be used among other grfx plugins.
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
 * @subpackage grfx_Functions
 * @author     Leo Blanchette <leo@grfx.com>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       http://www.grfx.com
 */
  

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/*----------------------------------------------------------------------------*
 * VALIDATION AND SECURITY FUNCTIONS
*----------------------------------------------------------------------------*/

//...
  
/*----------------------------------------------------------------------------*
 * FILE MANIPULATION FUNCTIONS
 *----------------------------------------------------------------------------*/

/**
 * Removes directory recursively.
 *
 * @param string $dir Directory to be removed
 * @package grfx
 * @subpackage FileHandling
 */
function grfx_rrmdir($dir) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
        $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
    }
    rmdir($dir);
}

/**
 * A simple function for writing generic files easily. Keeps redundancy down.
 * 
 * @package grfx
 * @subpackage FileHandling
 * @param string $file_name path / name of file. 
 * @param string $content the content to be written in the file.
 */
function grfx_write_file($file_name, $content = ''){
    $fp = fopen($file_name, 'w');
    fwrite($fp, $content);
    fclose($fp);
}

/**
 * Recursively copy files from one directory to another.
 *
 * This function is used primarily for moving plugin
 * files and folders around during install.
 *
 * @link http://ben.lobaugh.net/blog/864/php-5-recursively-move-or-copy-files
 *
 * @param String $src - Source of files being moved
 * @param String $dest - Destination of files being moved
 * @param Bool $recreate - Whether to recreate the directory.
 *
 * @package grfx
 * @subpackage FileHandling
 */
function grfx_rcopy($src, $dest) {
    // If source is not a directory stop processing
    if (!is_dir($src))
        return false;

    // If the destination directory does not exist create it
    if (!is_dir($dest)) {
        if (!mkdir($dest)) {
            // If the destination directory could not be created stop processing
            return false;
        }
    }

    // Open the source directory to read in files
    $i = new DirectoryIterator($src);
    foreach ($i as $f) {
        if ($f->isFile()) {

            $file = str_replace('//', '/', "$dest/" . $f->getFilename());
            copy($f->getRealPath(), $file);
        } else if (!$f->isDot() && $f->isDir()) {

            $file = str_replace('//', '/', "$dest/$f");
            grfx_rcopy($f->getRealPath(), str_replace('//', '/', "$dest/$f"));
        }
    }
}

/**
 * Cleans up temporary directory after file processing
 * 
 * @package grfx
 * @subpackage FileHandling
 */
function grfx_clean_tmp_dir(){
	$files = glob(grfx_tmp_dir().'*'); // get all file names
	foreach($files as $file){ // iterate files
	  if(is_file($file))
		unlink($file); // delete file
	}
}


/**
 * Determins which files are available for download on a given product. This is primarily used 
 * on the product page when setting up the price/product table. 
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * 
 * @param int $site which site is in question   
 * @param int $user which user owns the file
 * @param int $fileid id of the file
 * @return array file extensions available
 */
function grfx_downloads_available($site, $user, $fileid){
    //get available file types
    global $grfx_file_type_key;
    
    //establish base name of download file (without extension)
    $filename_base = grfx_product_dir().$site.'-'.$user.'-'.$fileid;
    
    $extensions_to_check = array();
        
    $types = array();
    
    
    foreach($grfx_file_type_key as $key=>$type)
        $types[$key] = $type['extension'];
    
    foreach($types as $ext=>$type){
        $extensions = explode(' ', $type);
        foreach($extensions as $filetype){
            array_push($extensions_to_check, array($ext,$filetype));
        }
    }
             
    $available = array();

    foreach($extensions_to_check as $extension){
        if(file_exists($filename_base.'.'.$extension[1])){
                array_push($available, $extension[0]);
        }
    }

    return $available;
}

/**
 * Scales image to desired size based on longest dimension. This is only for preview purposes - 
 * it does not actually scale an image. Since images are resized on delivery (saving storage) 
 * this function is merely a preview to the customer of what they are buying.
 * 
 * @package grfx
 * 
 * @subpackage HelperFunctions
 * @param int $o_size_x original size X
 * @param int $o_size_y original size Y
 * @param int $f_size Final (desired) size
 */
function grfx_scaled_image_size( $o_size_x, $o_size_y, $f_size){
    
    if(!$o_size_x || !$o_size_x)
        return;
    
	if($o_size_x <= $o_size_y){
		$r = $f_size/$o_size_y;
		$w = round($o_size_x*$r);
		$h = round($o_size_y*$r);		
	} else {
		$r = $f_size/$o_size_x;
		$w = round($o_size_x*$r);
		$h = round($o_size_y*$r);			
	}

	/*
	 * We do not upsize. Upsizing is bad! Therefor if user stipulates a size larger than 
	 * native file, we will correct it here in the next few lines.
	 */
	if($o_size_x >= $o_size_y){
		$lw = $o_size_x;
	} else {
		$lw = $o_size_y;
	}
	
	if($f_size > $lw){
		$w = $o_size_x;
		$h = $o_size_y;
	}
	
	/*
	 * We return a number of formats for convenience, saving lots of on-page stuff.
	 * D R Y: DO NOT REPEAT YOURSELF
	 */
	$atts = array(
		'size_x'         => $w,
		'size_y'         => $h,
		'human_readable' => $w.__('px', 'grfx').'×'.$h.__('px', 'grfx'),
		'html' => '<span class="grfx-size-px">'
		. '<span class="grfx-dimension">'.$w.'</span>'
		. '<span class="grfx-by"> × </span>'
		. '<span class="grfx-dimension">'.$h.'</span>'
		. '<span>'.__('px', 'grfx').'</span>'
		. '</span>'
	);
		
	return $atts;
	
}

/**
 * Formats bites to megabytes, etc.
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * 
 * @param type $bytes
 * @return string
 */
function grfx_format_size($bytes)
{
	if ($bytes >= 1073741824)
	{
		$bytes = number_format($bytes / 1073741824, 2) . ' GB';
	}
	elseif ($bytes >= 1048576)
	{
		$bytes = number_format($bytes / 1048576, 2) . ' MB';
	}
	elseif ($bytes >= 1024)
	{
		$bytes = number_format($bytes / 1024, 2) . ' KB';
	}
	elseif ($bytes > 1)
	{
		$bytes = $bytes . ' bytes';
	}
	elseif ($bytes == 1)
	{
		$bytes = $bytes . ' byte';
	}
	else
	{
		$bytes = '0 bytes';
	}

	return $bytes;
}

/**
 * A testing function to see how much memory is used at a given point.
 * 
 * @package grfx
 * @subpackage HelperFunctions
 */
function grfx_get_memory_use(){
	echo "MEMORY USED:";
	echo '<br />'.grfx_format_size(memory_get_usage()).'<br />';
}

/**
 * Loading gif generator function
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @return string image loading gif
 */
function grfx_loading_img(){
	return "<img style='display:none;' class='grfx-loader-gif' src='".grfx_assets_dir()."img/loading-small.gif'>";
}

/**
 * grfx logo simple img
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @return string image grfx logo
 */
function grfx_logo_img($size = 1){
	
	if($size ==1 )	
		return "<img style='' class='' src='".grfx_assets_dir()."img/logo-x-small.png'>";
	
	if($size ==2 )	
		return "<img style='' class='' src='".grfx_assets_dir()."img/logo-large.png'>";
	
}


/**
 * Gets the php binary path (for cron job)
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @return boolean|string
 */
function grfx_get_php_binary() {
  $paths = explode(PATH_SEPARATOR, getenv('PATH'));
  foreach ($paths as $path) {
    // we need this for XAMPP (Windows)
    if (strstr($path, 'php.exe') && isset($_SERVER["WINDIR"]) && file_exists($path) && is_file($path)) {
        return $path;
    }
    else {
        $php_executable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
        if (file_exists($php_executable) && is_file($php_executable)) {
           return $php_executable;
        }
    }
  }
  return FALSE; // not found
}

/**
 * Gets the sitepass (usually used for cron)
 * 
 * @package grfx
 * @subpackage grfx_Cron
 * @return string site pass
 */
function grfx_get_sitepass(){
	if(is_multisite()){
		$sitepass = get_site_option('grfx_sitepass', false);
	} else {
		$sitepass = get_option('grfx_sitepass', false);
	}
	return $sitepass;
}

/**
 * Sets the sitepass (sually used for cron)
 * 
 * @package grfx
 * @subpackage grfx_Cron
 */
function grfx_set_sitepass(){
	
	$sitepass = grfx_get_sitepass();
	
	if(!$sitepass){
		$pass = md5(uniqid(mt_rand(), true));
		if(is_multisite()){
			update_site_option( 'grfx_sitepass', $pass );
		} else {
			update_option( 'grfx_sitepass', $pass );
		}
	}
}

/**
 * Path to cron job 
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_cron_path() {
	$cron_path =  "* * * * * curl --silent " . "'".trailingslashit(home_url())."?grfx_crontype=1&grfx_cronpass=".grfx_get_sitepass()."'";
    if (!defined('grfx_cron_path'))
        define('grfx_cron_path', $cron_path);
    return grfx_cron_path;
}
grfx_cron_path();

/**
 * Gives a human readable summary of plugin information
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @return string summary of plugin
 */
function grfx_plugin_info(){
		
	if (grfx_use_imagick()) {
        $v = Imagick::getVersion();
		$image_system = '('.__('Using ', 'grfx').$v['versionString'].')';
	} else {
		$image_system = __('(Using GD Library)', 'grfx');
	}
	
	$summary = '';
	$summary .= __('grfx ');
	$summary .= ' '.grfx_version;
	$summary .= ' '.$image_system;
	$summary .= '';
	
	return $summary;
}

/**
 *  Simply converts a string to a slug
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @param type $string
 * @return string converted string
 */
function grfx_to_slug($string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

/**
 * Determines if agency submission tool is enabled.
 * 
 * @package grfx
 * @subpackage HelperFunctions
 * @return boolean
 */
function grfx_agency_submission_enabled(){    
    
    if(isset($_POST['grfx_agency_submit'])){
        if(is_string($_POST['grfx_agency_submit'])){  
            $submit = stripslashes($_POST['grfx_agency_submit']);
            update_option('grfx_agency_submit', $submit);
        }
    }
    
    $submit = get_option('grfx_agency_submit', 'off');
    
    if($submit == 'on')
        return true;
    
    if($submit == 'off')
        return false;   
    
    return false;       
    
}

function grfx_set_image_full_size_value($product, $size_setting){

    $size = $product->_size_x >= $product->_size_y ? $product->_size_x : $product->_size_y;
    
    update_post_meta($product->id, $size_setting, $size);
    
    $size_calculated = grfx_scaled_image_size($product->_size_x, $product->_size_y, $size);
		    
    return $size_calculated;
}