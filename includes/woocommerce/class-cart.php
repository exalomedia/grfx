<?php

/**
 * grfx cart manager - complements woocommerce to a small degree in handling 
 * various stock image functions that cannot presently be supported in woocommerce.
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
 * grfx cart class. This class handles some custom functionality of the add-to-cart process, logging
 * a few custom values, plus it handles delivery after sale of various file types as chosen by the customer.
 */
class grfx_Cart {

	/**
	 * Currently browsing user (a cookie hash)
	 * 
	 * @var string cookie hash of current user
	 */
	public $userhash = '';

	/**
	 * Current site
	 * 
	 * @var int  id of site
	 */
	public $site_id = 0;

	/**
	 * Cookie name relative to site
	 * 
	 * @var string name of cookie for given site to track purchases 
	 */
	public $cookie_name = '';

	public function __construct() {

		global $grfx_SITE_ID;

		$this->cookie_name = 'grfx-visitor-' . $grfx_SITE_ID;

		$this->manage_cookie();

		add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 2 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_from_cart' ), 10, 2 );
		add_filter( 'woocommerce_product_file_download_path', array($this, 'get_file_path'), 10, 3);
		add_filter('woocommerce_get_item_downloads', array($this, 'get_item_downloads'), 10, 3);
		add_action('woocommerce_order_item_meta_end', array($this, 'get_item_license'), 10, 3 );
		
	}

	/**
	 * Checks to see if item is in cart. 
	 * @global type $wpdb
	 * @param int $product_id
	 * @return boolean true if in cart, false if not
	 */
	
	public function is_in_cart( $product_id ) {
		global $wpdb;

		$sql = "SELECT product_option FROM grfx_product_option WHERE product_id=" . $product_id . " AND userhash LIKE '" . $this->userhash . "'";

		$results = $wpdb->query( $sql );

		if ( $results ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Gets license for reference on sold product (this is shown on the details page after order)
	 * 
	 * @param type $item_id
	 * @param type $item
	 * @param type $order
	 */
	public function get_item_license($item_id, $item, $order){
				
		$option = $this->get_product_option( $item['product_id'] );
		
		$license = get_post_meta( $item['product_id'], '_size_license_'.$option, false );
		
		if(!$license)
			return false;
		
		$name = get_option( 'grfx_license_name_'.$license, false );
		$text = get_option( 'grfx_license_text_'.$license, false );
		
		?>
		<div class="grfx-license-purchased">
		<br /><br /><h3><?php echo stripslashes($name); ?></h3>
		<?php
		
		?>
		<div class="grfx-license-reminder"><?php echo stripslashes($text) ?></div>
		</div>
		<?php		
	
	}
	
	/**
	 * Modifies the downloads list to give the proper download.
	 * 
	 * @param type $files
	 * @param type $item
	 * @param type $object
	 * @return type
	 */
	public function get_item_downloads( $files, $item, $object ) {
	
		global $grfx_file_type_key;
		
		$type = $this->get_product_type( $item['product_id'] );
		
		if(!$type)
			return $files;
		
		$type_key = $grfx_file_type_key[$type]['extension'];
	
		$mod_files = array();
	
		foreach ( $files as $key => $file ) {
						
			$ext = pathinfo($file['file'], PATHINFO_EXTENSION);	
			
			if (strpos($type_key, $ext) !== false) {
			    $mod_files[$key] = $file;
			}				
		
		}

		return $mod_files;
	}

	/**
	 * Sets a custom file path for a given product. Generates product size first if necessary.
	 * 
	 * @param string $file_path path to original file (will be changed
	 * @param int $product_id ID of product
	 */
	public function get_file_path( $file_path, $product, $download_id ){
		
		/**
		 * This should only apply to stock images at present.
		 */
		if($product->product_type != 'stock_image')
			return $file_path;		

		global $grfx_SITE_ID; 
				
		$product = wc_get_product($product->id);  
		
		$product_dir = grfx_product_dir();
		
		//remove extension
		$path = $product_dir . pathinfo($file_path, PATHINFO_FILENAME);		
		
		$user_id = $product->post->post_author;		
				
		$type = $this->get_product_type( $product->id );
		
		switch($type){
			
			/*
			 * 'extension' => 'jpg jpeg'
			 * 'extension' => 'png'
			 */
			case ( $type == '1' ): 
						
				$px = $this->get_product_size( $product->id );

				if($px == 0)
					return $file_path;		

				/*
				 * NOTICE: Filepath does not have extension attached to the end of it. That is determined by image processor.
				 */
				$dst = grfx_delivery_dir().$this->userhash.'-'.$product->id.'-'.$px.'px';

				if(  file_exists( $dst.'.jpg' )){
					$file_path = $dst.'.jpg';
				} elseif (file_exists( $dst.'.jpeg' )){
					$file_path = $dst.'.jpeg';
				} else {		
					$file_path = $this->image_resize_to_order($file_path, $dst, $px);
				}				
				
				break;			
			
			case $type == '2':
				
				$px = $this->get_product_size( $product->id );
										
				if($px == 0)
					return $file_path;		

				/*
				 * NOTICE: Filepath does not have extension attached to the end of it. That is determined by image processor.
				 */
				$dst = grfx_delivery_dir().$this->userhash.'-'.$product->id.'-'.$px.'px';

				if( file_exists( $dst.'.png' )){				
					$file_path = $dst.'.png';
				} else {
				
					$file_path = $this->image_resize_to_order($path.'.png', $dst, $px);
				}
				
				break;
				
			/*
			 * 'extension' => 'psd'
			 */
			case $type == '3':
				
				if(file_exists($path.'.psd'))
						$file_path = $path.'.psd';
				
				break;
			
			/*
			 * 'extension' => 'ai'
			 */
			case $type == '4':
				
				if(file_exists($path.'.ai'))
						$file_path = $path.'.ai';				
				
				break;
			
			/*
			 * 'extension' => 'eps'
			 */
			case $type == '5':
				
				if(file_exists($path.'.eps'))
						$file_path = $path.'.eps';				
				
				break;
			
			/*
			 * 'extension' => 'svg'
			 */
			case $type == '6':
				
				if(file_exists($path.'.svg'))
						$file_path = $path.'.svg';				
				
				break;	
			
			/*
			 * 'extension' => 'zip tar gz tar.gz tar.bz2"'
			 */
			case $type == '7':
				
				if(file_exists($path.'.zip'))
						$file_path = $path.'.zip';	
				
				if(file_exists($path.'.tar'))
						$file_path = $path.'.tar';					
				
				if(file_exists($path.'.tar.gz'))
						$file_path = $path.'.tar.gz';	
				
				if(file_exists($path.'.tar.bz2'))
						$file_path = $path.'.tar.bz2';					
				
				break;				
			
		}
			
		return $file_path;
	}
	
	public function image_resize_to_order($src, $dst, $px){		
		
		/*
		 * Get the image processor
		 */
		require_once(dirname( __FILE__ ) . '/../../admin/includes/image-processor/class-image-processor.php');
		
		$processor = new grfx_Image_Processor();
		
		$file_path = $processor->make_custom_size_image($src, $dst, $px);
		
		return $file_path;
	}
	
	/**
	 * Gets the product option for a given product (1, 2, 3, etc)
	 * 
	 * @global type $wpdb
	 * @param int $product_id ID of product
	 * @return type
	 */
	public function get_product_option( $product_id ) {

		global $wpdb;

		$sql = "SELECT product_option FROM grfx_product_option WHERE product_id=" . $product_id . " AND userhash LIKE '" . $this->userhash . "'";

		$option = $wpdb->get_var( $sql );
		return $option;
	}
	
	/**
	 * Get the product type (ie, png, eps, ai, jpeg)
	 * @param type $product_id
	 * @return boolean
	 */
	public function get_product_type( $product_id ){
	
		$option = $this->get_product_option( $product_id );

		if(!$option)
			return false;
		
		$type = get_post_meta( $product_id, '_size_type_enabled_'.$option, false );
		
		if($type)
			return $type[0];		
		
	}
	
	/**
	 * Gets the size (in pixels) of a given product (stock image) 
	 * 
	 * @param int $product_id ID of product
	 * @return boolean
	 */
	public function get_product_size( $product_id ) {

		$option = $this->get_product_option( $product_id );

		if(!$option)
			return false;
		
		$size = get_post_meta( $product_id, '_size_pixels_'.$option, false );
		
		if($size)
			return $size[0];
		
	}

	/**
	 * Inserts a product option into grfx_product_option table.
	 * 
	 * @global type $wpdb
	 * @param int $product_id ID of product
	 * @param int $product_option product option number (1, 2, 3, etc) for given product
	 */
	public function insert_product_option( $product_id, $product_option ) {

		global $wpdb;

		$sql = "INSERT INTO grfx_product_option (product_id, product_option, userhash) VALUES ('" . $product_id . "', '" . $product_option . "', '" . $this->userhash . "');";
		$wpdb->query( $sql );
	}

	/**
	 * Updates a product option in grfx_product_option table.
	 * 
	 * @global type $wpdb
	 * @param int $product_id ID of product
	 * @param int $product_option product option number (1, 2, 3, etc) for given product
	 */
	public function update_product_option( $product_id, $product_option ) {

		global $wpdb;

		$sql = "UPDATE grfx_product_option SET product_option=" . $product_option . " WHERE product_id=" . $product_id . " AND userhash LIKE '" . $this->userhash . "'";
		$wpdb->query( $sql );
	}

	/**
	 * Hooked onto by woocommerce_add_to_cart action. Runs add-to-cart specifics of plugin.
	 * 
	 * @param type $cart_item_key
	 * @param int $product_id ID of product
	 * @return type
	 */
	public function add_to_cart( $cart_item_key, $product_id ) {
		//var_dump($_POST);
		//var_dump($cart_item_key, $product_id);
		//$order = new WC_Order( $product_id );
		//echo $order->get_order_number();
		//var_dump($order);

		$this->manage_cookie();

		if ( isset( $_POST['grfx-product-option'] ) ) {
			$product_option = (int) $_POST['grfx-product-option'];
		} else {
			return;
		}

		if ( $this->is_in_cart( $product_id ) ) {
			$this->update_product_option( $product_id, $product_option );
		} else {
			$this->insert_product_option( $product_id, $product_option );
		}
	}

	/**
	 * Remove from cart functionality, triggered by woocommerce_cart_item_removed action
	 * 
	 * @global type $wpdb
	 * @param type $cart_item_key
	 * @param type $cart
	 */
	public function remove_from_cart( $cart_item_key, $cart ) {
		global $wpdb;

		$this->manage_cookie();

		/*
		 * Get ID of removed item
		 */
		$product_id = $cart->removed_cart_contents[$cart_item_key]['product_id'];

		$sql = "DELETE FROM grfx_product_option WHERE product_id=" . $product_id . " AND userhash LIKE '" . $this->userhash . "'";
		$wpdb->query( $sql );
	}

	/**
	 * Sets up a cookie when user engages site. Cookie is used for unique product option tracking
	 */
	public function manage_cookie() {

		if ( !isset( $_COOKIE[$this->cookie_name] ) ) {
			setcookie( $this->cookie_name, $this->set_cookie_value(), time() + 60 * 60 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN );
		}
		$this->userhash = $_COOKIE[$this->cookie_name];
	}

	/**
	 * Simple method for setting a cookie value using some php magic. 
	 * 
	 * @return type
	 */
	public function set_cookie_value() {

		return sha1( uniqid( time() . mt_rand(), true ) );
	}
	
}

$grfx_cart = new grfx_Cart();
