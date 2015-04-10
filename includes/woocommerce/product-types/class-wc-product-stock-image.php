<?php
/**
 * grfx Woocommerce Settings Integration. This file contains most settings directly relevant
 * to the stock image product type.
 *
 * @package grfx
 * @subpackage grfx_Woocommerce
 */

//NOTE THIS FILE IS MOSTLY EXPERIMENTAL IT WILL BE CLEANED UP LATER

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


add_filter('woocommerce_product_data_tabs', function($tabs) {
        
    array_push($tabs['shipping']['class'], 'hide_if_stock_image');
    array_push($tabs['advanced']['class'], 'hide_if_stock_image');

    return $tabs;

}, 10, 1);

add_filter( 'product_type_selector', 'grfx_add_stock_image_product_type' );

function grfx_add_stock_image_product_type( $types ){
    $types[ 'stock_image' ] = __( 'Stock Image', 'grfx' );
    return $types;
}

/*
 * Set up product class 
 */
add_action('plugins_loaded', 'grfx_setup_wc_product_stock_image');
	
/**
 * Sets up the Woocommerce-based stock image product type.
 */
function grfx_setup_wc_product_stock_image() {
	
	
	/**
	 * Stock Image Product Setup
	 */
	class WC_Product_Stock_Image extends WC_Product {
			

		public $_visibility;
		public $_stock_status;
		public $total_sales;
		public $_downloadable;
		public $_virtual;
		public $_regular_price;
		public $_sale_price;
		public $_purchase_note;
		public $_featured;
		public $_sku;
		public $_sold_individually;
		public $_manage_stock;


		public $_upsell_ids;
		public $_crosssell_ids;
		public $_download_limit;
		public $_download_expiry;
		public $_download_type;

		/**
		 * Main width of parent image
		 * @var type 
		 */
		public $_size_x;
		
		/**
		 * Main height of parent image
		 * @var type 
		 */		
		public $_size_y;		
		
		public $_size_name_1;
		public $_size_price_1;
		public $_size_pixels_1;
		public $_size_license_1;
		public $_size_enabled_1;
		public $_size_vector_enabled_1;
		
		public $_size_name_2;
		public $_size_price_2;
		public $_size_pixels_2;
		public $_size_license_2;
		public $_size_enabled_2;
		public $_size_vector_enabled_2;
		
		public $_size_name_3;
		public $_size_price_3;
		public $_size_pixels_3;
		public $_size_license_3;
		public $_size_enabled_3;
		public $_size_vector_enabled_3;
		
		public $_size_name_4;
		public $_size_price_4;
		public $_size_pixels_4;
		public $_size_license_4;
		public $_size_enabled_4;
		public $_size_vector_enabled_4;
		
		public $_size_name_5;
		public $_size_price_5;
		public $_size_pixels_5;
		public $_size_license_5;
		public $_size_enabled_5;
		public $_size_vector_enabled_5;
		
		public $_size_name_6;
		public $_size_price_6;
		public $_size_pixels_6;
		public $_size_license_6;
		public $_size_enabled_6;
		public $_size_vector_enabled_6;
		
		public $_size_name_7;
		public $_size_price_7;
		public $_size_pixels_7;
		public $_size_license_7;
		public $_size_enabled_7;
		public $_size_vector_enabled_7;
		
		public $_size_name_8;
		public $_size_price_8;
		public $_size_pixels_8;
		public $_size_license_8;
		public $_size_enabled_8;
		public $_size_vector_enabled_8;
		
		public $_size_name_9;
		public $_size_price_9;
		public $_size_pixels_9;
		public $_size_license_9;
		public $_size_enabled_9;
		public $_size_vector_enabled_9;
		
		public $_size_name_10;
		public $_size_price_10;
		public $_size_pixels_10;
		public $_size_license_10;
		public $_size_enabled_10;
		public $_size_vector_enabled_10;

		public $_downloadable_files;
		
		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $product
		 */
		public function __construct( $product ) {

			$this->product_type = 'stock_image';

			//$this->virtual = 'yes';
			$this->downloadable = 'yes';
			$this->purchasable = true;
			
			parent::__construct( $product );
			
			$this->get_stock_image_options();	
			
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_custom_prices_before_calculate_totals' ) );
						
		}

		/*
		 * Sets custom prices before calculating totals
		 */
		public function set_custom_prices_before_calculate_totals( $cart_object ) {
			$custom_price = 10; // This will be your custome price  

			foreach ( $cart_object->cart_contents as $key => $value ) {

				$value['data']->price = $custom_price;

				/*
				  // If your target product is a variation
				  if ( $value['variation_id'] == $target_product_id ) {
				  $value['data']->price = $custom_price;
				  }
				 */
			}
		}
		
		/**
		 * Sets up a modal window (at this time only for license info)
		 */
		public function modal(){
			?>

			<div class="grfx-modal" id="grfx-modal">
				<div class="grfx-modal-inner">
					<a class="grfx-modal-close" title="Close">&times;</a>
					
					<?php echo grfx_loading_img() ?>
					
					<h1 id="grfx-license-title"> </h1>

					<div id="grfx-license-text">
						
					</div>
								
				</div>
			</div>
						
			<?php
		}
		
		/**
		 * Gets the general stock image options and assigns them to objects.
		 * 
		 * @global type $wpdb
		 */
		public function get_stock_image_options(){
							
			$post_custom = get_post_meta($this->id);	
						
			foreach($post_custom as $k=>$v){				
				if(empty($this->$k)){
					$att = $post_custom[$k][0];
					$this->$k = $att;										
				}				
			}	
		}		
	}
}

add_action( 'grfx_image_options', 'render_option_table', 30 );

/**
 * Renders the stock image option table 
 * 
 * @global type $grfx_size_default_names
 * @global object $product
 * @return type
 */
function render_option_table(){

	global $grfx_size_default_names;	
	global $product;

	if($product->product_type != 'stock_image')
		return;

	//have filetypes ready
	$filetypes = grfx_filetypes();
	
	$i=1;
	?>
		<select id="grfx-product-option" name="grfx-product-option">
			<?php while($i <= count($grfx_size_default_names)): ?>
			<?php
			/*
			 * If size not enabled, skip.
			 */
			$size_jpeg_enabled = '_size_enabled_'.$i;
		
			if(! (int) $product->$size_jpeg_enabled){
				$i++;
				continue;
			}				
			$size_name    = get_option('_size_name_'.$i, __('Size '.$i, 'grfx'));
			$size_price  = '_size_price_'.$i;
			$size_pixels = '_size_pixels_'.$i;
			?>
			<option data-summary="<?php echo $i ?>" value="<?php echo $i ?>"><?php echo $size_name ?> (<?php  echo wc_price($product->$size_price);  ?>)</option>	
			<?php $i++; ?>	
			<?php endwhile; ?>	
		</select>
					
		<?php $i=1; while($i <= count($grfx_size_default_names)): ?>
			<?php
			/*
			 * If size not enabled, skip.
			 */
			$size_jpeg_enabled = '_size_enabled_'.$i;
		
			if(! (int) $product->$size_jpeg_enabled){
				$i++;
				continue;
			}
			$size_name    = get_option('_size_name_'.$i, __('Size '.$i, 'grfx'));
			$size_price   = '_size_price_'.$i;
			$size_license = '_size_license_'.$i;	
			$size_pixels  = '_size_pixels_'.$i;			
			$size_calculated = grfx_scaled_image_size($product->_size_x, $product->_size_y, $product->$size_pixels);
			$type         = '_size_type_enabled_'.$i;
			?>
			<div class="grfx-options-descriptions <?php echo $i==1?'show':'' ?>" id="grfx-option-description-<?php echo $i ?>">
				
				<span class="grfx-option-label"><?php _e('License', 'grfx') ?>: </span>
					<span class="grfx-size-license-option">
						<a class="grfx-modal-open" data-license="<?php echo $product->$size_license ?>" href="#grfx-modal"><?php echo get_option('grfx_license_name_'.$product->$size_license, __('License', 'ss')) ?></a>
					</span><br />
			 
				<span class="grfx-option-label"><?php _e('Size', 'grfx') ?>: </span>	
					<span class="grfx-size-name-option"><?php echo get_option('_size_name_'.$i, __('Size '.$i, 'grfx')) ?> </span><br />
			
				<span class="grfx-option-label"><?php _e('Price', 'grfx') ?>: </span>	
					<span class="grfx-size-price-option"><?php echo wc_price($product->$size_price); ?></span><br />
				
				<span class="grfx-option-label"><?php _e('Pixels', 'grfx') ?>: </span>
					<span class="grfx-size-info-option"><?php echo $size_calculated['html']; ?></span><br />					
			
				<span class="grfx-option-label"><?php _e('Filetype', 'grfx') ?>: </span>
					<span class="grfx-size-info-option"><?php echo $filetypes[$product->$type]; ?></span>	
					
			</div>
			<?php $i++; ?>	
			<?php endwhile; ?>
			<?php $product->modal() ?>
		<?php
}

//SETTINGS


// add the settings under ‘General’ sub-menu
add_action( 'woocommerce_product_options_general_product_data', 'grfx_stock_image_product_settings' );

/**
 * Settings area, stock image meta box.
 *  
 * @see http://docs.woothemes.com/wc-apidocs/source-function-woocommerce_wp_text_input.html#14-75
 * @global type $woocommerce
 * @global type $post
 */

function grfx_stock_image_product_settings() {
	
	global $woocommerce, $post;
	global $grfx_size_default_prices;
	global $grfx_size_default_names;
	global $grfx_file_type_key;
	
	echo '<div class="options_group show_if_stock_image">';

	$i = 1;
	while($i <= count($grfx_size_default_names)){	
		
		// Create a number field, for example for UPC
		$placeholder = get_post_meta($post->ID, '_size_price_'.$i);
	
		$size_name = get_option('_size_name_'.$i,  $grfx_size_default_prices['_size_price_'.$i]);
		woocommerce_wp_text_input(
				array(
					'id'          => '_size_price_'.$i,
					'label'       => $size_name . ' ' .  __('Price', 'grfx'),
					'placeholder' => $placeholder[0],
					'desc_tip'    => 'true',
					'description' => __( 'Stock Image Price '.$i, 'grfx' ),
					'data_type'   => 'price'
		) );
		
		$placeholder = get_post_meta($post->ID, '_size_pixels_'.$i);
		woocommerce_wp_text_input(
				array(
					'id'          => '_size_pixels_'.$i,
					'label'       => $size_name . ' ' .  __('Size', 'grfx'),
					'placeholder' => $placeholder[0],
					'desc_tip'    => 'true',
					'description' => __( 'Longest side in pixels. '.$i, 'grfx' ),
					'data_type'   => 'number'
		) );
		
		$placeholder = get_post_meta($post->ID, '_size_enabled_'.$i);	
		woocommerce_wp_select(
				array(
					'id'          => '_size_license_'.$i,
					'label'       => $size_name . ' ' .  __('License', 'grfx'),			
					'desc_tip'    => 'true',
					'description' => __( 'Which license applies to this option ', 'grfx' ),
					'options'     =>  array(
							'1' => get_option('grfx_license_name_1', __('License One Name', 'grfx')),
							'2' => get_option('grfx_license_name_2', __('License One Name', 'grfx')),
							'3' => get_option('grfx_license_name_3', __('License One Name', 'grfx'))
						)
		) );			
	
		$placeholder = get_post_meta($post->ID, '_size_enabled_'.$i);	
		woocommerce_wp_select(
				array(
					'id'          => '_size_type_enabled_'.$i,
					'label'       => __('Deliver file', 'grfx').':',			
					'desc_tip'    => 'true',
					'description' => __( 'Which file type this applies to.', 'grfx' ),
					'options'     =>  grfx_filetypes()
		) );			
		
		$placeholder = get_post_meta($post->ID, '_size_enabled_'.$i);	
		woocommerce_wp_checkbox(
				array(
					'id'          => '_size_enabled_'.$i,
					'label'       => $size_name . ' ' .  __('Enabled', 'grfx'),			
					'desc_tip'    => 'true',
					'description' => __( 'Whether or not size is enabled. ', 'grfx' ),
					'cbvalue'     =>  $placeholder[0] ? $placeholder[0]: 'yes'
		) );			
		
		$i++;
		echo '<br />';
				
	}

	echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'grfx_save_custom_settings', 100 );

/**
 * Saves custom settings user inputs in metabox
 * 
 * @global type $grfx_size_default_names
 * @global type $post
 * @param type $post_id
 */
function grfx_save_custom_settings( $post_id ) {
	global $grfx_size_default_names;
	global $post;
	$i = 1;
	

	while($i <= count($grfx_size_default_names)){	
		
		if(isset($_POST['_size_price_'.$i]))
				update_post_meta($post->ID, '_size_price_'.$i, $_POST['_size_price_'.$i]);
		
		if(isset($_POST['_size_pixels_'.$i]))
				update_post_meta($post->ID, '_size_pixels_'.$i, $_POST['_size_pixels_'.$i]);
		
		if(isset($_POST['_size_license_'.$i]))
				update_post_meta($post->ID, '_size_license_'.$i, $_POST['_size_license_'.$i]);		
		
		if(isset($_POST['_size_type_enabled_'.$i]))	
			update_post_meta($post->ID, '_size_type_enabled_'.$i, $_POST['_size_type_enabled_'.$i]);		
		
		if(isset($_POST['_size_enabled_'.$i])){		
			update_post_meta($post->ID, '_size_enabled_'.$i, 1);
			
		} else {
			update_post_meta($post->ID, '_size_enabled_'.$i, 0);
		}
		
		
		$i++;
	}
	

}


add_action('woocommerce_single_product_summary','grfx_add_to_cart_button', 30);

/**
 * The add-to-cart button
 * @global object $product
 */
function grfx_add_to_cart_button(){
	global $product;
	
	if($product->product_type != 'stock_image')
		return;
	?>
	<form class="cart" method="post" enctype='multipart/form-data'>
		<?php do_action( 'grfx_before_stock_image_options' ); ?>
		<?php do_action( 'grfx_image_options' ); ?>
		<?php do_action( 'grfx_after_stock_image_options' ); ?>
	 	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	 	<?php
	 		if ( ! $product->is_sold_individually() )
	 			woocommerce_quantity_input( array(
	 				'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
	 				'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
	 			) );
	 	?>

	 	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->id ); ?>" />
	 	<button type="submit" class="single_add_to_cart_button button alt"><?php echo $product->single_add_to_cart_text(); ?></button>
		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>
	<?php
}



add_action( 'woocommerce_before_calculate_totals', 'grfx_add_custom_stock_image_price', 100);

/**
 * This function changes the cart value according to the custom stock image size chosen. 
 * 
 * @param object $cart_object the cart!
 * @return type $cart_object the modified cart!
 */
function grfx_add_custom_stock_image_price( $cart_object ) {	
	
	/*
	 * If this is just a routine calculation, run through that first.
	 */
	foreach ( $cart_object->cart_contents as $key => $value ) {
		if(!isset($value['grfx-product-option-price']))
			continue;
		$value['data']->price = $value['grfx-product-option-price'];
	}	

	/*
	 * If something new is being posted, proceed to set a custom price via the ['grfx-product-option-price']  key.
	 */
	
	/*
	 * Validate
	 */
	
	if(empty($_POST['grfx-product-option'])  || !is_numeric( $_POST['grfx-product-option']))
		return $cart_object;
	
	if(empty($_POST['add-to-cart'])        || !is_numeric( $_POST['add-to-cart']))
		return $cart_object;	
	
	/*
	 * Get custom grfx-product uption
	 */
	
	$option    = $_POST['grfx-product-option']; 		
	$price_key = '_size_price_'.$option;
	
	$target = $_POST['add-to-cart'];
	
    foreach ( $cart_object->cart_contents as $key => $value ) {
		
		if($value['data']->product_type  != 'stock_image')
			continue;
				
		/*
		 * Search for product being posted and tag the special option price.
		 */
		if(!empty($_POST['grfx-product-option'])){
			if($value['product_id'] == $target){
				$cart_object->cart_contents[$key]['grfx-product-option-price'] = $value['data']->$price_key;
				$cart_object->cart_contents[$key]['grfx-product-option']       = $_POST['grfx-product-option'];
			}					
		}
    }	
	return $cart_object;
}


add_action('woocommerce_cart_item_price', 'grfx_add_custom_stock_image_price_minicart', 100, 3);

/**
 * Overrides the minicart price (without this function, the minicart shows the singular price for a singular product.
 * 
 * @param type $price
 * @param type $cart_item
 * @param type $cart_item_key
 * @return type
 */
function grfx_add_custom_stock_image_price_minicart($price, $cart_item, $cart_item_key ){

	if($cart_item['data']->product_type  != 'stock_image')
		return $price;
	
	$option = $cart_item['grfx-product-option'];
	
	$price_key = '_size_price_'.$option;
	
	$price = $cart_item['data']->$price_key;
			
	return wc_price( $price );
	
}


add_action('woocommerce_payment_complete', 'grfx_custom_process_order', 10, 1);

/**
 * Modify the download filepath after purchase.
 * 
 * @see https://sozot.com/how-to-hook-into-woocommerce-to-trigger-something-after-an-order-is-placed/
 * 
 * @param int  $order_id
 * @return type
 */
function grfx_custom_process_order($order_id) {
	
    $order = new WC_Order( $order_id );
    $user_id = (int)$order->user_id;
    $user_info = get_userdata($user_id);
    $items = $order->get_items();
	
    foreach ($items as $item) {
		
		if($item['data']->product_type  != 'stock_image')
			continue;		
		
		if(isset($item['grfx-product-option']))
			update_user_meta($user_id, 'grfx_image_download_'.$item['product_id'], $item['grfx-product-option']);

    }
	
    return $order_id;
}


add_filter( 'woocommerce_get_price_html', 'grfx_remove_price_html', 100, 2 );

/**
 * Removes the price HTML from the stock image product page so that we can utilize our own price
 * @param type $price
 * @param type $product
 * @return string
 */
function grfx_remove_price_html( $price, $product ){
	if($product->product_type  != 'stock_image')
		return $price;
    return '';
}

/**
 * License modal window currently used on cart page. 
 * 
 * @param int $option which size option was chosen
 * @param type $product
 */
function grfx_info_modal($option, $product){
	
	$option_license = '_size_license_'.$option;

	$license_text   = get_option('grfx_license_text_'.$option, __('License Text', 'grfx'));
	
	$license_title  = get_option('grfx_license_name_'.$product['data']->$option_license, __('License One', 'grfx'));

	
	?>
		<em><?php _e('Option', 'grfx') ?></em>: <strong><?php echo get_option('_size_name_'.$option, __('Size', 'grfx').' '.$option) ?></strong>, 
		<em><?php _e('License', 'grfx') ?>: (<a class="grfx-modal-open" data-license="<?php echo $option ?>" href="#grfx-modal-<?php echo $product['product_id'] ?>"><?php echo $license_title ?></a>)</em>
		&mdash;
		<div class="grfx-modal" id="grfx-modal-<?php echo $product['product_id'] ?>">
			<div class="grfx-modal-inner">
				<a class="grfx-modal-close" title="Close">&times;</a>

				<h1 id=""><?php echo $license_title ?></h1>
				
				<div id="">
					<?php echo nl2br($license_text); ?>
				</div>

			</div>
		</div>
	<?php
}

add_filter('woocommerce_cart_item_name', 'grfx_append_license', 30, 3);

/**
 * Append license to cart page listings. 
 * 
 * @param type $title
 * @param type $cart_item
 * @param type $cart_item_key
 * @return type
 */
function grfx_append_license($title, $cart_item, $cart_item_key ){
	
	if(!is_cart())
		return $title;
	
	if($cart_item['data']->product_type  != 'stock_image')
		return $title;	
	
	$option = $cart_item['grfx-product-option'];	
	
	return $title. grfx_info_modal($option, $cart_item);
}



add_action('the_post', 'grfx_correct_object_terms');


/**
 * Repairs incomplete object terms, such as assigning the product type as a stock image, or the product
 * tags. This happens during cron jobs due to limitations in wordpress, where taxonomies are not loaded
 * properly in a multisite environment. If taxonomies are not available, they are staged in post meta
 * and retrieved and fixed on first load. Post meta is deleted, and performance restored.
 * 
 * @global type $post
 */
function grfx_correct_object_terms(){
    global $post;
    
    if(!is_multisite())
        return;
    
    $post_id = get_the_id();
    
    $tags = get_post_meta($post_id, 'grfx_finish_product_tag', false);
    
    if($tags){
        wp_set_object_terms($post_id, $tags[0], 'product_tag');
        delete_post_meta($post_id, 'grfx_finish_product_tag');
    }
    
    $type = get_post_meta($post_id, 'grfx_finish_product_type', false);
    
    if($type){
        wp_set_object_terms($post_id, $type[0], 'product_type');
        delete_post_meta($post_id, 'grfx_finish_product_type');
    }    
    
    
    
}











//TESTING


add_filter('woocommerce_add_cart_item', 'grfx_test_add_item');

function grfx_test_add_item($data){

	//do stuff...

	return $data;
}



//add_action( 'woocommerce_before_calculate_totals', 'grfx_add_custom_stock_image_price_2' );

function grfx_add_custom_stock_image_price_2( $cart_object ) {

    $target_product_id = $_POST[''];
    foreach ( $cart_object->cart_contents as $key => $value ) {
		var_dump($value);
        if ( $value['product_id'] == $target_product_id ) {
            $value['data']->price = $custom_price;
        }
    }
	var_dump($cart_object->cart_contents );
}


//add_filter('woocommerce_get_price', 'grfx_filter_test', 10, 2);

function grfx_filter_test($price, $product){
    global $woocommerce;
	//filters
	// 'woocommerce_add_cart_item_data'
	// 'woocommerce_add_to_cart_sold_individually_quantity'
	
	// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item
	// 'woocommerce_add_cart_item'
	
	foreach ( $woocommerce->cart->cart_contents as $key => $value ) {
		//var_dump($key, $value);
	}
	

	return $price;
}


add_action('woocommerce_before_main_content', 'grfx_test');
function grfx_test(){
    //var_dump(the_post());
    $id = get_the_id();
	
		
	return;

	global $woocommerce;
	//var_dump($price, $woocommerce->cart);
	
    
    $product = wc_get_product($id);    
    var_dump($product);
	return;
    $meta = get_post_meta($id);

	
	var_dump($product);
	
    return;
	$downloadable = get_post_meta($id, '_downloadable_files');
	var_dump($downloadable);
	var_dump(get_attached_media( $id ));
	
	foreach($meta as $key => $val){		
		//echo '$this->set_to_meta(\''.$key.'\', \''.$val[0].'\');<br />';		
	}
	
	var_dump($product->get_price());
	
}
