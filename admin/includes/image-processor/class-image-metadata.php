<?php
/**
 * grfx  Image Metadata Class (php-based)
 */

/**
 * grfx Image Meta Data Reading Class
 * 
 * Extracts image meta data. 
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

class grfx_Image_Metadata{

	/**
	 * Image file in question
	 * @var type 
	 */
	public $file = '';
	

	
	public function __construct($file) {
		$this->file = $file;
				
		return $this->read_image_metadata();
	}

	/**
	 * Get extended image metadata, exif or iptc as available.
	 * 
	 * This is derived from the wordpress version of the meta-data extractor. We've improved it
	 * slightly.
	 *
	 * Retrieves the EXIF metadata aperture, credit, camera, caption, copyright, iso
	 * created_timestamp, focal_length, shutter_speed, and title.
	 *
	 * The IPTC metadata that is retrieved is APP13,  keywords, credit, byline, created date
	 * and time, caption, copyright, and title. Also includes FNumber, Model,
	 * DateTimeDigitized, FocalLength, ISOSpeedRatings, and ExposureTime.
	 *
	 * @todo Try other exif libraries if available.
	 *
	 * @return bool|array False on failure. Image metadata array on success.
	 */
	public function read_image_metadata( ) {

		$file = $this->file;

		list(,, $sourceImageType ) = getimagesize( $file );

		/*
		 * EXIF contains a bunch of data we'll probably never need formatted in ways
		 * that are difficult to use. We'll normalize it and just extract the fields
		 * that are likely to be useful. Fractions and numbers are converted to
		 * floats, dates to unix timestamps, and everything else to strings.
		 */
		$meta = array(
			'keywords' => '',
			'aperture' => 0,
			'credit' => '',
			'camera' => '',
			'caption' => '',
			'created_timestamp' => 0,
			'copyright' => '',
			'focal_length' => 0,
			'iso' => 0,
			'shutter_speed' => 0,
			'title' => '',
			'orientation' => 0,
		);

		/*
		 * Read IPTC first, since it might contain data not available in exif such
		 * as caption, description etc.
		 */
		if ( is_callable( 'iptcparse' ) ) {
			getimagesize( $file, $info );

			if ( !empty( $info['APP13'] ) ) {
				$iptc = iptcparse( $info['APP13'] );
				
				// Keywords
				if ( !empty( $iptc["2#025"] && is_array($iptc["2#025"]) ) ) {					
					$keywords = array();
					foreach($iptc["2#025"] as $keyword)
						array_push($keywords, $keyword);							
					$meta['keywords'] = implode(',',$keywords);					
				} 						
						
				// Headline, "A brief synopsis of the caption."
				if ( !empty( $iptc['2#105'][0] ) ) {
					$meta['title'] = trim( $iptc['2#105'][0] );
					/*
					 * Title, "Many use the Title field to store the filename of the image,
					 * though the field may be used in many ways."
					 */
				} elseif ( !empty( $iptc['2#005'][0] ) ) {
					$meta['title'] = trim( $iptc['2#005'][0] );
				}

				if ( !empty( $iptc['2#120'][0] ) ) { // description / legacy caption
					$caption = trim( $iptc['2#120'][0] );
					if ( empty( $meta['title'] ) ) {
						mbstring_binary_safe_encoding();
						$caption_length = strlen( $caption );
						reset_mbstring_encoding();

						// Assume the title is stored in 2:120 if it's short.
						if ( $caption_length < 80 ) {
							$meta['title'] = $caption;
						} else {
							$meta['caption'] = $caption;
						}
					} elseif ( $caption != $meta['title'] ) {
						$meta['caption'] = $caption;
					}
				}

				if ( !empty( $iptc['2#110'][0] ) ) // credit
					$meta['credit'] = trim( $iptc['2#110'][0] );
				elseif ( !empty( $iptc['2#080'][0] ) ) // creator / legacy byline
					$meta['credit'] = trim( $iptc['2#080'][0] );

				if ( !empty( $iptc['2#055'][0] ) and ! empty( $iptc['2#060'][0] ) ) // created date and time
					$meta['created_timestamp'] = strtotime( $iptc['2#055'][0] . ' ' . $iptc['2#060'][0] );

				if ( !empty( $iptc['2#116'][0] ) ) // copyright
					$meta['copyright'] = trim( $iptc['2#116'][0] );
			}
		}

		/**
		 * Filter the image types to check for exif data.
		 *
		 * @since 2.5.0
		 *
		 * @param array $image_types Image types to check for exif data.
		 */
		if ( is_callable( 'exif_read_data' ) && in_array( $sourceImageType, apply_filters( 'wp_read_image_metadata_types', array( IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ) ) ) ) {
			$exif = @exif_read_data( $file );

			if ( empty( $meta['title'] ) && !empty( $exif['Title'] ) ) {
				$meta['title'] = trim( $exif['Title'] );
			}

			if ( !empty( $exif['ImageDescription'] ) ) {
				mbstring_binary_safe_encoding();
				$description_length = strlen( $exif['ImageDescription'] );
				reset_mbstring_encoding();

				if ( empty( $meta['title'] ) && $description_length < 80 ) {
					// Assume the title is stored in ImageDescription
					$meta['title'] = trim( $exif['ImageDescription'] );
					if ( empty( $meta['caption'] ) && !empty( $exif['COMPUTED']['UserComment'] ) && trim( $exif['COMPUTED']['UserComment'] ) != $meta['title'] ) {
						$meta['caption'] = trim( $exif['COMPUTED']['UserComment'] );
					}
				} elseif ( empty( $meta['caption'] ) && trim( $exif['ImageDescription'] ) != $meta['title'] ) {
					$meta['caption'] = trim( $exif['ImageDescription'] );
				}
			} elseif ( empty( $meta['caption'] ) && !empty( $exif['Comments'] ) && trim( $exif['Comments'] ) != $meta['title'] ) {
				$meta['caption'] = trim( $exif['Comments'] );
			}

			if ( empty( $meta['credit'] ) ) {
				if ( !empty( $exif['Artist'] ) ) {
					$meta['credit'] = trim( $exif['Artist'] );
				} elseif ( !empty( $exif['Author'] ) ) {
					$meta['credit'] = trim( $exif['Author'] );
				}
			}

			if ( empty( $meta['copyright'] ) && !empty( $exif['Copyright'] ) ) {
				$meta['copyright'] = trim( $exif['Copyright'] );
			}
			if ( !empty( $exif['FNumber'] ) ) {
				$meta['aperture'] = round( wp_exif_frac2dec( $exif['FNumber'] ), 2 );
			}
			if ( !empty( $exif['Model'] ) ) {
				$meta['camera'] = trim( $exif['Model'] );
			}
			if ( empty( $meta['created_timestamp'] ) && !empty( $exif['DateTimeDigitized'] ) ) {
				$meta['created_timestamp'] = wp_exif_date2ts( $exif['DateTimeDigitized'] );
			}
			if ( !empty( $exif['FocalLength'] ) ) {
				$meta['focal_length'] = (string) wp_exif_frac2dec( $exif['FocalLength'] );
			}
			if ( !empty( $exif['ISOSpeedRatings'] ) ) {
				$meta['iso'] = is_array( $exif['ISOSpeedRatings'] ) ? reset( $exif['ISOSpeedRatings'] ) : $exif['ISOSpeedRatings'];
				$meta['iso'] = trim( $meta['iso'] );
			}
			if ( !empty( $exif['ExposureTime'] ) ) {
				$meta['shutter_speed'] = (string) wp_exif_frac2dec( $exif['ExposureTime'] );
			}
			if ( !empty( $exif['Orientation'] ) ) {
				$meta['orientation'] = $exif['Orientation'];
			}
		}

		foreach ( array( 'title', 'caption', 'credit', 'copyright', 'camera', 'iso' ) as $key ) {
			if ( $meta[$key] && !seems_utf8( $meta[$key] ) ) {
				$meta[$key] = utf8_encode( $meta[$key] );
			}
		}

		foreach($meta as $key => $val)
			$this->$key = $val;
	}

}