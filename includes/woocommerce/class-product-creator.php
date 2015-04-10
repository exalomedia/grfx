<?php

/**
 * grfx Product creator class and related functions. 
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
 * @package grfx
 * @subpackage grfx_Woocommerce
 * @author     Leo Blanchette <leo@grfx.com>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       http://www.grfx.com
 */

/**
 * This is the main product creation class of grfx core. Ideally it creates
 * woocommerce products through supplied parameters. Ideally it can be extended
 * to create different types of products, but at it's root it creates stock images.
  */
class grfx_Product_Creator {

	/**
	 * ID of the post
	 * @var type 
	 */
	public $post_id = 0;
	
	/**
	 * Main product title, usually derived from image meta data
	 * @var string product title
	 */
	public $product_title = '';

	/**
	 * Main product description, usually derived from image meta data
	 * @var string product description
	 */
	public $product_description = '';

	/**
	 * Main product tags, usually derived from image meta data
	 * @var string product tags
	 */
	public $product_tags = array();

	/**
	 * Product meta which will define the product's attributes such as price and downloads
	 * 
	 * @var array product meta which will be applied
	 */
	public $product_meta = array();

	/**
	 * Image meta data (such as in the case of a stock image)
	 * @var type 
	 */
	public $image_metadata;
	
	/**
	 * Whether or not to prepare from upload.
	 * @var bool prepare from upload?
	 */
	public $from_upload = true;

	/**
	 * The file from which product is being created (usually a large scale image)
	 * @var string file from which product is being created
	 */
	public $file_name = '';

	/**
	 * Trailing name of source file (not including path)
	 * @var string
	 */
	public $source_file = '';

	/**
	 * If upload, the ID of the uploaded file.
	 * @var int 
	 */
	public $upload_id = 0;

	/**
	 * User ID the product will be applied to
	 * @var int user ID
	 */
	public $user_id = 0;

	/**
	 * Site ID (or rather blog ID) the upload will be applied to.
	 * @var int site ID the upload will be applied to
	 */
	public $site_id = 0;

	/**
	 * Orginal file name at upload time (ie, "a dog running")
	 * @var string original name 
	 */
	public $original_name = '';

	/**
	 * Which type of file (image/jpeg)
	 * @var string
	 */
	public $file_type = '';

	/**
	 * Size file in bytes
	 * @var int file size 
	 */
	public $file_size = 0;

	/**
	 * Extension of file (ie, .jpg, .eps)
	 * @var type extension of file
	 */
	public $extension = '';

	/**
	 * Whether file was queued by uploader 
	 * @var bool
	 */
	public $enqueued = 0;

	/**
	 * Whether the product will be saved as a draft or final
	 * @var bool whether file is saved as draft or final
	 */
	public $to_draft = 1;

	/**
	 * Date of upload
	 * @var type 
	 */
	public $datetime;

	/**
	 *  Entries to delete after processing
	 * @var array entries to be deleted after processing 
	 */
	public $to_delete = array();
	
	public function __construct() {
		
	}
	
	/**
	 * Runs through the steps of creating a basic stock image. 
	 * 
	 * EXAMPLE:
	 * <pre> 
	 * object(stdClass)[25]
	 *	public 'upload_id' => string '15' (length=2)
	 *	public 'user_id' => string '1' (length=1)
	 *	public 'site_id' => string '2' (length=1)
	 *	public 'original_name' => string 'Design Mascot Artist Woman Orange-01.jpg' (length=40)
	 *	public 'file_name' => string 'aa6dd1400c788edfb4151404ffe046ac.jpg' (length=36)
	 *	public 'file_type' => string 'jpg' (length=3)
	 *	public 'file_size' => string '1412780' (length=7)
	 *	public 'extension' => string 'jpg' (length=3)
	 *	public 'enqueued' => string '1' (length=1)
	 * 	public 'to_draft' => string '0' (length=1)
	 *	public 'datetime' => string '2015-03-29 13:12:29' (length=19)
	 * </pre>
	 * 
	 * So far the only required info attributes are **upload_id**,  **user_id** and **site_id**. 
	 * 
	 * If @$from_queue is **false**, the @$info object can be blank.
	 * 
	 * @param object $info information about the object. See doc above.
	 * @param bool $from_queue Whether or not this operation is from the queue. If so, it does cleanup.
	 */
	public function make_stock_image_basic($info = false, $from_queue = true){

			//log entry for deletion after process is over.
			array_push($this->to_delete, $info->upload_id);
		
			$this->create_image_previews();	
			$this->create_initial_product();

			$this->set_image('watermarked', 'preview');
			//$this->set_image('tmp-minipic', 'minipic');	

			//create downloadable product attributes
			
			$downloadable = $this->get_downloadable_files();
							
			$this->set_downloadable_files( $downloadable );

			//save
			$this->save_post_meta();		
			
			if(!$from_queue)
				return false;
			
			$upload_manager = new grfx_Upload_Tracker();

			$upload_manager->delete_selected($this->to_delete, $info->user_id, $info->site_id);						
			grfx_clean_tmp_dir();
			
			do_action('grfx_created_product', $this->post_id);
			
	}

	/**
	 * Adds data from the uploaded database object to this class, preparing for the product creation
	 * process.
	 * <pre>
	 * 	object(stdClass)[15]
	 * 	  public 'upload_id' => string '204' (length=3)
	 * 	  public 'user_id' => string '1' (length=1)
	 * 	  public 'site_id' => string '2' (length=1)
	 * 	  public 'original_name' => string 'Dart and Board.jpg' (length=18)
	 * 	  public 'file_name' => string 'ae779d5fd3418ff4c6e753dd594ddc0e.jpg' (length=36)
	 * 	  public 'file_type' => string 'image/jpeg' (length=10)
	 * 	  public 'file_size' => string '915198' (length=6)
	 * 	  public 'extension' => string 'jpg' (length=3)
	 * 	  public 'enqueued' => string '1' (length=1)
	 * 	  public 'to_draft' => string '0' (length=1)
	 * 	  public 'datetime' => string '2015-03-23 18:54:22' (length=19)
	 * </pre>
	 * @param type $upload_info the database object from uploaded file
	 */
	public function prepare_from_upload( $upload_info ) {
		foreach ( $upload_info as $key => $val ) {
			$this->$key = $val;
		}
		$this->source_file = $this->file_name;
		$this->file_name = grfx_protected_uploads_dir() . $this->file_name;
	}

	/**
	 * Function not yet developed. Will be the starting point for items not created from upload queue.
	 * @param type $info
	 */
	public function prepare_from_info( $info ) {
		
	}

	/**
	 * Simply creates an initial product upon which all the other data will be inserted.
	 */
	public function create_initial_product() {

        
        if(!defined('GRFX_CREATING_PRODUCT'))
            define('GRFX_CREATING_PRODUCT', true);
		

		$post = array(
			'post_author' => $this->user_id,
			'post_content' => $this->product_description,
			'post_status' => $this->to_draft == 1?'draft':'publish',
			'post_title' => $this->product_title,
			'post_parent' => '',
			'post_type' => "product",
	
		);
				
		//Create post
		$post_id = wp_insert_post( $post );
		if ( $post_id ) {
			$this->post_id = $post_id;
			//$attach_id = get_post_meta( $product->parent_id, "_thumbnail_id", true );
			//add_post_meta( $post_id, '_thumbnail_id', $attach_id );
		}
        
        if(!taxonomy_exists('product_tag')){
            update_post_meta($post_id, 'grfx_finish_product_tag', $this->product_tags);
        } else {
            wp_set_object_terms($post_id, $this->product_tags, 'product_tag');
            update_post_meta($post_id, 'grfx_finish_product_tag', false);
        }
       
        if(!taxonomy_exists('product_type')){
            update_post_meta($post_id, 'grfx_finish_product_type', 'stock_image');
        } else {        
            wp_set_object_terms($post_id, 'stock_image', 'product_type');
            update_post_meta($post_id, 'grfx_finish_product_type', false);
        }
	}

	/**
	 * Creates image previews for the product. 
	 * 
	 * @param string $type which type of product we are creating.
	 * @return boolean
	 */
	public function create_image_previews( $type = 'stock_image' ) {

		require_once( dirname( __FILE__ ) . '/../../admin/includes/image-processor/class-image-processor.php' );
		//this is not being used at present
		//require_once( dirname( __FILE__ ) . '/../../admin/includes/image-processor/class-image-metadata.php' ); 
		
		switch ( $type ) {
			case 'stock_image':

				if ( empty( $this->file_name ) )
					return false;

				//set up info from image data
				$image_processor = new grfx_Image_Processor();
				$metadata = $image_processor->get_image_metadata($this->file_name);
								
				$this->image_metadata = $metadata;
				
				!empty( $metadata->Title )    ? $this->product_title = $metadata->Title         : $this->product_title = '-';
				!empty( $metadata->Description )  ? $this->product_description = $metadata->Description : $this->product_description = '-';
				!empty( $metadata->Keywords ) ? $this->product_tags = $metadata->Keywords       : $this->product_tags = array();

				$this->set_stock_image_data();
				
				//Make various image sizes			

				$image_processor->filename = $this->file_name;
				$image_processor->make_standard_preview();
				$image_processor->make_minipic_preview($this->user_id);
							
				
				break;
		}
	}

	/**
	 * Sets preview images according to wordpress 
	 * 
	 * @param string $tmp_img name of the image in temp directory
	 * @param string $size size minipic | preview
	 */
	public function set_image($tmp_img, $size='minipic') {
		$content_dir = wp_upload_dir();
		$tmp_dir     = grfx_tmp_dir();

		if(file_exists($tmp_dir.$tmp_img.'.jpg')){
			$tmp_img = $tmp_img.'.jpg';			
		} elseif (file_exists($tmp_dir.$tmp_img.'.jpeg')){
			$tmp_img = $tmp_img.'.jpeg';	
		}
		
		$substitute = __('image', 'grfx'); //in case the file did not have meta data title
		$file_title = empty($this->product_title) ? $substitute : $this->product_title;
		
		//sets up new name with a fairly SEO'd title
		$new_name = $this->post_id.'-'.sanitize_title($file_title).'-'.$size;
		
		//establish file path of new file
		$file = trailingslashit($content_dir['path']).$new_name.'.jpg';		

		rename($tmp_dir.$tmp_img, $file);
	
		
		//proceed to create the attachment
		$wp_filetype = wp_check_filetype( $file, null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' =>  $this->product_title,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $file, $this->post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		
		if($size=='preview')
			set_post_thumbnail( $this->post_id, $attach_id );
		
		
	}
	
	/**
	 * Sets all info relevant to a stock image. An important function in properly setting up a 
	 * grfx product.
	 */
	public function set_stock_image_data() {
		
		//set post type
		$this->set_to_meta( 'post_type', 'product' );

		$this->set_to_meta( '_edit_lock', '' );
		$this->set_to_meta( '_edit_last', '1' );
		$this->set_to_meta( '_visibility', 'visible' );
		$this->set_to_meta( '_stock_status', 'instock' );
		$this->set_to_meta( 'total_sales', '0' );

		//is downloadable		
		$this->set_to_meta( '_downloadable', 'yes' );
		//is virtual product
		$this->set_to_meta( '_virtual', 'yes' );

		//the price
		$this->set_to_meta( '_regular_price', get_option( 'grfx_regular_price', '15.00' ) );
		$this->set_to_meta( '_sale_price', '' );
		$this->set_to_meta( '_purchase_note', '' );
		$this->set_to_meta( '_featured', 'no' );
		$this->set_to_meta( '_weight', '' );
		$this->set_to_meta( '_length', '' );
		$this->set_to_meta( '_width', '' );
		$this->set_to_meta( '_height', '' );
		$this->set_to_meta( '_sku', '' );
		$this->set_to_meta( '_product_attributes', '' );
		$this->set_to_meta( '_sale_price_dates_from', '' );
		$this->set_to_meta( '_sale_price_dates_to', '' );
		$this->set_to_meta( '_price', get_option( 'grfx_regular_price', '15.00' ) );
		$this->set_to_meta( '_sold_individually', 'yes' );
		$this->set_to_meta( '_manage_stock', 'no' );
		$this->set_to_meta( '_backorders', 'no' );
		$this->set_to_meta( '_stock', '' );
		$this->set_to_meta( '_upsell_ids', '' );
		$this->set_to_meta( '_crosssell_ids', '' );
		$this->set_to_meta( '_download_limit', get_option( 'grfx_download_limit', '3' ) );
		$this->set_to_meta( '_download_expiry', get_option( 'grfx_download_expiry', '7' ) );
		$this->set_to_meta( '_download_type', '' );
		$this->set_to_meta( '_product_image_gallery', '' );
		
		/*
		 * For stock images
		 */
		if(isset($this->image_metadata->ImageWidth))
			$this->set_to_meta( '_size_x', $this->image_metadata->ImageWidth );
		
		if(isset($this->image_metadata->ImageHeight))
			$this->set_to_meta( '_size_y', $this->image_metadata->ImageHeight );
		
		/*
		 * Handle user set global vars now...
		 */
		global $grfx_size_default_names, 
			   $grfx_size_default_prices, 
			   $grfx_size_default_pixels, 
			   $grfx_size_default_license, 
			   $grfx_size_enabled_,
			   $delivery_defaults;

		/*
		 * Default options for sizes
		 */
		$i=1;
		while($i <= count($grfx_size_default_names)){
			
			$this->set_to_meta('_size_name_'.$i,    get_option('_size_name_'.$i,    $grfx_size_default_names['_size_name_'.$i]));
			$this->set_to_meta('_size_price_'.$i,   get_option('_size_price_'.$i,   $grfx_size_default_prices['_size_price_'.$i]));
			$this->set_to_meta('_size_pixels_'.$i,  get_option('_size_pixels_'.$i,  $grfx_size_default_pixels['_size_pixels_'.$i]));
			
			$this->set_to_meta('_size_license_'.$i, get_option('_size_license_'.$i, $grfx_size_default_license['_size_license_'.$i]));
			
			//This part is important (do not mess up the yes/no) its finicky between woocommerce and grfx
			$enabled =  get_option('_size_enabled_'.$i, $grfx_size_enabled_['_size_enabled_'.$i]);	
			$this->set_to_meta('_size_enabled_'.$i, $enabled=='yes'?'1':0);
		
			$this->set_to_meta('_size_type_enabled_'.$i, get_option('_size_type_enabled_'.$i, $delivery_defaults['_size_type_enabled_'.$i]));
						
			$i++;
		}		
	
	
		
	}

	/**
	 * Sets up downloadable files based on matched filenames 
	 * 
	 * @global type $wpdb
	 * @global type $grfx_file_type_key
	 * @return array
	 */
	public function get_downloadable_files(){
		global $wpdb;		
		global $grfx_file_type_key;
		
		//a list of types that forms a dictionary
		
		/* EXAMPLE: 
		 * 
		array (size=7)
		  'jpg jpeg' => string 'Jpeg Image (jpg jpeg)' (length=21)
		  'png' => string 'PNG Image (png)' (length=15)
		  'psd' => string 'Photoshop File (psd)' (length=20)
		  'ai' => string 'Adobe Illustrator File (ai)' (length=27)
		  'eps' => string 'Encapsulated Post Script (eps)' (length=30)
		  'svg' => string 'Scalable Vector Graphic (svg)' (length=29)
		  'zip tar gz tar.gz tar.bz2"' => string 'Zip Archive (zip tar gz tar.gz tar.bz2")' (length=40)
		 */
		
		$types = array();
		
		foreach($grfx_file_type_key as $type){
			$types[$type['extension']] = $type['name'] . ' ('.$type['extension'].')';
		}

		
		//original filename
		$filename = $this->original_name;
		
		//split into parts (to get the base name)
		$parts = pathinfo($filename);
		
		//get basic name
		$name = $parts['filename'];
		
		//prepare SQL to get related filenames
		$sql = "SELECT * FROM grfx_upload_tracking WHERE original_name LIKE '%".$name."%' AND user_id = ".$this->user_id." ORDER BY upload_id DESC ";
		//get related files
		$files = $wpdb->get_results($sql);
		
		//set up downloadable items
		$downloadable = array(
			array(
				'file' => $this->file_name,
				'name' => $this->product_title
			)
		);	
		
		//this sequence matches file types to the above declared dictionary. If a match, a downloadable file entry is created. 
		if($files){
			foreach($files as $f){
				//this extension doesn't count. Its required and chosen by default. 
				if($f->extension == 'jpeg' || $f->extension == 'jpg')
					continue;
				//log entry for deletion after process is over.
				array_push($this->to_delete, $f->upload_id);				
				foreach($types as $key => $type){
					
					//this check to see if a match occurs between file extension
					if (strpos($key,$f->extension) !== false) {
						
						$download = array(
							'file' => grfx_protected_uploads_dir().$f->file_name,
							'name' => $types[$key].', '.$this->product_title
						);	
						
						array_push($downloadable, $download);						
					}
				}
								
			}
		}
		
		return $downloadable;
		
	}
	
	/**
	 * Save downloadable files
	 * 
	 * This is derived from the class-wc-api-products.php class (woocommerce)
	 *
	 * @param array $downloads which downloadable items to be added to product.
	 */
	public function set_downloadable_files( $downloads ) {
		$files = array();
	
		
		// File paths will be stored in an array keyed off md5(file path)
		foreach ( $downloads as $key => $file ) {
			if ( isset( $file['url'] ) ) {
				$file['file'] = $file['url'];
			}

			if ( !isset( $file['file'] ) ) {
				continue;
			}
			
			//change directory for downloadable file
			
			//file name convention: <site id>-<user id>-<post number> ( 2-3-9999.png )
			$file_info    = pathinfo($file['file']);
			$new_location = grfx_product_dir().$this->site_id.'-'.$this->user_id.'-'.$this->post_id.'.'.$file_info['extension'];
			rename($file['file'], $new_location);
			
			//change the location to be logged
			$file['file']=$new_location;
			
			$file_name = isset( $file['name'] ) ? sanitize_text_field( $file['name'] ) : '';
			$file_url = sanitize_text_field( $file['file'] );

			$files[md5( $file_url )] = array(
				'name' => $file_name,
				'file' => $file_url
			);
		}

		$this->set_to_meta( '_downloadable_files', $files );

		//update_post_meta( $product_id, '_downloadable_files', $files );
	}

	/**
	 *  Prepares post meta values ahead of time
	 * @param string $meta_name
	 * @param string|array|bool|int $meta_value
	 */
	public function set_to_meta( $meta_name, $meta_value ) {
		array_push( $this->product_meta, array( $meta_name => $meta_value ) );
	}

	/**
	 * Saves post meta values
	 */
	public function save_post_meta(){
		if(!$this->post_id)
			return false;
		
		foreach($this->product_meta as $meta){
			update_post_meta($this->post_id, key($meta), $meta[key($meta)]);
		}
	}
	
}
