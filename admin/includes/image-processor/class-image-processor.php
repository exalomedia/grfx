<?php
/**
 * grfx Main Image Processor and Watermarker Class
 */


/**
 * grfx Main Image Processor Class
 * 
 * Having the *perfect* image processor is a bit of an artform when you 
 * are dealing with a system that sells multiple types of images. Some products
 * will require the image itself to be the product, and showcased in various 
 * ways (basic, panoramic, etc) and yet other products will only require images
 * for previews only sake (vector, 3d models, etc)
 * 
 * Thus our base image processor class must exist as a primary way of creating 
 * images and assigning them to product types. Yet for specific needs we must 
 * extend the class based on the context its being used for. Thus the grfx
 * image processor class is created for general purpose, and to be extended in
 * add-on plugins and other situations. 
 *
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
 * @package    grfx_ImageProcessor
 * @author     Leo Blanchette <leo@grfx.com>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       http://www.grfx.com
 */
function grfx_Watermark_Image( $source_path, $destination, $watermark_path ) {
	
	/*
	 * Depending on environment, we load either Imagick functions or GD library.
	 * Its only necessary to load the watermarking scripts in the admin for obvious
	 * reasons.
	 */


	if ( grfx_use_imagick() ) {

		//use Imagick!

		require_once('class-watermark-imagick.php');

		$watermark = new grfx_Imagick_Watermark( $source_path, $destination, $watermark_path );

		$watermark->make_watermark();
	} else {

		//use GD Library...boring...

		require_once('class-watermark-gd.php');



		//if we don't have imagemagic, we fall back to GD and use the attached library/class/    
		$watermark_options = array(
			'watermark' => $watermark_path,
			'halign' => ALIGN_CENTER,
			'valign' => ALIGN_MIDDLE,
			'type' => IMAGETYPE_JPEG,
			'jpeg-quality' => 100
		);

		grfx_GD_Watermark::output( $source_path, $destination, $watermark_options );
	}

}

/**
 * grfx_Image_Processor class. Main image processor for grfx Core, 
 * meant to be extended by other plugins.
 *
 * @package  grfx_ImageProcessor
 * @author  Leo Blanchette <leo@grfx.com>
 */
class grfx_Image_Processor {

	/**
	 * @var string|bool path to image file, 0 if not set.
	 */
	public $filename = 0;

	/**
	 * The metadata object returned from this::get_image_metadata
	 * 
	 * @see grfx_Image_Processor::get_image_metadata
	 * @var object the metadata object.
	 */
	public $metadata = object;

	/**
	 * @var string path to watermark transparent png
	 */
	public $watermark_path = '';

	/**
	 * @var string path to minipic watermark transparent png
	 */
	public $watermark_minipic_path = '';

	/**
	 * @var bool whether or not to protect the minipics via a copyright watermark. 
	 */
	public $protect_minipics = true;

	/**
	 * @var string path to temporary file area (used in image manipulation)
	 */
	public $tmp_dir = '';

	public function __construct() {

		$plugin_dir = trailingslashit( grfx_core_plugin );

		$user_watermark = get_attached_file(get_option('grfx_watermark_image_id', false));		
			
		if(!$user_watermark){		
			$this->watermark_path = $plugin_dir . 'assets/img/watermark/watermark.png';
		} else {
			$this->watermark_path = $user_watermark;
		}
		
		$this->watermark_minipic_path = $plugin_dir . 'assets/img/watermark/watermark-minipic.png';
		$this->tmp_dir = $plugin_dir . 'tmp/';
	}

	/**
	 * Makes watermark preview
	 * @param type $source
	 * @param type $destination
	 */
	public function make_watermark_preview( $source, $destination ) {

		grfx_Watermark_Image( $source, $destination, $this->watermark_path );
	}

	/**
	 * Creates a size version of src image
	 */
	public function make_custom_size_image($src, $dst, $px) {
		
		$mime = $this->get_image_type( $src );

		/*
		 * If we are using imagick, we use an imagick scaling function, if not 
		 * we bank on Wordpress which is slightly less efficient...but we must 
		 * make due :D 
		 */
		if ( grfx_use_imagick() ) {
			
			$image = new Imagick();
			if(!file_exists($this->filename))
				return false;            
			$image->readImage( $src );
			$image->scaleImage( $px, $px, true );

			if ( $mime == '.png' ){
				
				$image->setImageFormat('png');
				$mime = '.png';
			}
			$image->writeImage( $dst . $mime );
			$image->clear();
			$image->destroy();
		} else {


			if ( $mime == '.png' ) {
				/*
				 * If this is a png, we must ensure the transparency does not
				 * flatten to black.
				 */
				$png = imagecreatefrompng( $src );

				// Transform to white-background JPEG
				$png = $this->image_translate_to_white( $png );

				imagealphablending( $png, true );
				imagesavealpha( $png, true );
				// Save new image
				imagepng( $png, $src, 1 );
				unset( $png );
			}

			$image = wp_get_image_editor( $src ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

			if ( !is_wp_error( $image ) ) {
				$image->resize( $px, $px, false );
				$image->save( $dst . $mime );
			}
		}
		
		return $dst . $mime;
		
	}
	
	/**
	 * Creates a standard grfx product watermarked preview.
	 * 
	 * We would expect specialized product plugins to create a number of different 
	 * preview types with their own user interactions, but for consistency
	 * grfx requires at least one standard type of preview, which is 
	 * produced here.
	 */
	public function make_standard_preview() {

		$mime = $this->get_image_type( $this->filename );

		/*
		 * If we are using imagick, we use an imagick scaling function, if not 
		 * we bank on Wordpress which is slightly less efficient...but we must 
		 * make due :D 
		 */
		if ( grfx_use_imagick() ) {

			$image = new Imagick();
			if(!file_exists($this->filename))
				return false;
			$image->readImage( $this->filename );
			$image->scaleImage( 800, 800, true );

			/*
			  $w = $image->getImageWidth();
			  $h = $image->getImageHeight();

			  if (($w / $h) > 2){
			  $image->scaleImage(900, 500, true);
			  } elseif (($h / $w) > 2) {
			  $image->scaleImage(500, 900, true);
			  } else {
			  $image->scaleImage(590, 590, true);
			  }
			 */

			/*
			 * If this is a png, we must ensure the transparency does not
			 * flatten to black.
			 */
			if ( $mime == '.png' ){
				$image = $image->flattenImages();
				$image->setImageFormat('jpeg');
				$mime = '.jpg';
			}
			$image->writeImage( $this->tmp_dir . 'tmp' . $mime );
			$image->clear();
			$image->destroy();
		} else {


			if ( $mime == '.png' ) {
				/*
				 * If this is a png, we must ensure the transparency does not
				 * flatten to black.
				 */
				$png = imagecreatefrompng( $this->filename );

				// Transform to white-background JPEG
				$png = $this->image_translate_to_white( $png );

				imagealphablending( $png, true );
				imagesavealpha( $png, true );
				// Save new image
				imagepng( $png, $this->filename, 1 );
				unset( $png );
			}

			$image = wp_get_image_editor( $this->filename ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

			if ( !is_wp_error( $image ) ) {
				$image->resize( 590, 590, false );
				$image->save( $this->tmp_dir . 'tmp' . $mime );
			}
		}


		/*
		 * Check to see that our tmp file exists. If so, proceed to watermark
		 */
		
		if ( file_exists( $this->tmp_dir . 'tmp' . $mime ) ) {

			$tmp_image = $this->tmp_dir . 'watermarked.jpg';

			$this->make_watermark_preview( $this->tmp_dir . 'tmp' . $mime, $tmp_image );

			return $tmp_image;
		} else {
			return 0;
		}
	}

	/**
	 * Creates a small minipic preview, slightly watermarked.
	 */
	public function make_minipic_preview($user, $s = 200 ) {
		
		$current_user = get_userdata($user);
		
		$mime = $this->get_image_type( $this->filename );
		
		/*
		 * If we are using imagick, we use an imagick scaling function, if not 
		 * we bank on Wordpress which is slightly less efficient...but we must 
		 * make due :D 
		 */
		if ( grfx_use_imagick() ) {

			$image = new Imagick();
			if(!file_exists($this->filename))
				return false;            
			$image->readImage( $this->filename );
			$image->scaleImage( $s, $s, true );

			/*
			 * If this is a png, we must ensure the transparency does not
			 * flatten to black.
			 */
			if ( $mime == '.png' ){
				$image = $image->flattenImages();
				$image->setImageFormat('jpeg');
				$mime = '.jpg';
			}
			/*
			 * Minipic watermark function taken from here:
			 */

			if ( $this->protect_minipics == true ) {

				// Watermark text
				$text = '© ' . $current_user->display_name;

				// Create a new drawing palette
				$draw = new ImagickDraw();

				// Set font properties
				$draw->setFont( 'Bookman' );
				$draw->setFontSize( 14 );
				$draw->setFillColor( '#CCC' );
				$draw->setfillopacity( 0.50 );

				// Position text at the bottom-right of the image
				$draw->setGravity( Imagick::GRAVITY_SOUTH );

				$y = 5;
				$o = 0.25;
				while ( $y < $s ) {
					// Draw text on the image
					$draw->setfillopacity( $o );
					$image->annotateImage( $draw, 0, $y, 0, $text );
					$y = $y + 20;
					$o = $o - .1;
				}
			}

			$image->writeImage( $this->tmp_dir . 'tmp-minipic' . $mime );
			$image->clear();
			$image->destroy();
		} else {


			if ( $mime == '.png' ) {
				/*
				 * If this is a png, we must ensure the transparency does not
				 * flatten to black.
				 */
				$png = imagecreatefrompng( $this->filename );

				// Transform to white-background JPEG
				$png = $this->image_translate_to_white( $png );

				imagealphablending( $png, true );
				imagesavealpha( $png, true );
				// Save new image
				imagepng( $png, $this->filename, 1 );
				unset( $png );
			}

			$image = wp_get_image_editor( $this->filename ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

			if ( !is_wp_error( $image ) ) {
				$image->resize( $s, $s, false );
				$image->save( $this->tmp_dir . 'tmp' . $mime );
			}
		}
		return $this->tmp_dir . 'tmp' . $mime;
	}

	/**
	 * Transforms a transparent png's background to white.
	 * @param type $trans
	 * @return type
	 */
	public function image_translate_to_white( $trans ) {
		// Create a new true color image with the same size
		$w = imagesx( $trans );
		$h = imagesy( $trans );
		$white = imagecreatetruecolor( $w, $h );

		// Fill the new image with white background
		$bg = imagecolorallocate( $white, 255, 255, 255 );
		imagefill( $white, 0, 0, $bg );

		// Copy original transparent image onto the new image
		imagecopy( $white, $trans, 0, 0, 0, 0, $w, $h );
		return $white;
	}

	/**
	 * Gets the file type of an image.
	 *
	 * @param string $file_name path to file
	 * @return string type of image - ie: gif, jpg, png ... etc
	 */
	public function get_image_type( $file_name ) {
		return image_type_to_extension( exif_imagetype( $file_name ) );
	}

    /**
     * Gets image data from a given image.
     * 
     *  
     * @param string $file_name path to image file.
     * @return object|number All meta-data on success, false on failure.
     */
    public function get_image_metadata($file_name) {

        $command = grfx_exiftool . ' -json ';
        $command .= $file_name;

        $results = shell_exec($command);
		
        if ($results && is_string($results)) {
            $meta_object = json_decode($results, false);
            $this->metadata = $meta_object[0];
            return $meta_object[0];
        } else {
            return 0;
        }
    }    

}
