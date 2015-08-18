<?php

/**
 * grfx Imagick Watermark Class
 * 
 * This is a simple class for generating a watermark via PHP's imagick extension.
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
 * @package grfx_ImageProcessor
 * 
 * @author     Leo Blanchette <leo@grfx.co>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       https://www.facebook.com/grfx.co
 */

/**
 * grfx watermarking class which utilizes php's imagick. If the user has
 * imagick installed, this class is used by default.
 * 
 * @author  Leo Blanchette <leo@grfx.co>
 */
class grfx_Imagick_Watermark {

    /**
     * @var string Source image to be watermarked.
     */
    public $source_path = '';

    /**
     * @var string Path to where watermarked image will reside.
     */
    public $destination = '';

    /**
     * @var string Path to the transparent png watermark.
     */
    public $watermark_path = '';

    /**
     * Sets up the watermarking 
     * @param string $source_path Source image to be watermarked.
     * @param string $destination Path to where watermarked image will reside.
     * @param string $watermark_path Path to the transparent png watermark.
     */
    public function __construct($source_path, $destination, $watermark_path) {
        $this->source_path = $source_path;
        $this->destination = $destination;
        $this->watermark_path = $watermark_path;
    }

    public function make_watermark() {
   
        //Creating two Imagick object
        $image     = new Imagick($this->source_path);
        $watermark = new Imagick($this->watermark_path); 

        // how big are the images?
        $iWidth    = $image->getImageWidth();
        $iHeight   = $image->getImageHeight();
        $wWidth    = $watermark->getImageWidth();
        $wHeight   = $watermark->getImageHeight();

        // calculate the position
        $x = ( $iWidth - $wWidth ) / 2;
        $y = ( $iHeight - $wHeight ) / 2;
       
   
        // Set the colorspace to the same value
        $image->setImageColorspace($watermark->getImageColorspace() );

        //Second image is put on top of the first
        $image->compositeImage($watermark, $watermark->getImageCompose(), $x, $y);
              
        //new image is saved as final.jpg
        $image->writeImage($this->destination); 
    }

}
