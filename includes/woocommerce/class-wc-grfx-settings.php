<?php
/**
 * grfx Woocommerce Settings Integration
 *
 * @package grfx
 * @subpackage grfx_Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'woocommerce_get_sections_products', 'grfx_add_section' );
function grfx_add_section( $sections ) {
$sections['grfx_stock_image'] = __( 'Stock Images (grfx)', 'grfx' );
return $sections;
} 

add_filter( 'woocommerce_get_settings_products', 'grfx_stock_image_settings', 10, 2 );

/**
 * Stock image product settings
 * 
 * @param type $settings
 * @param type $current_section
 * @return string
 */
function grfx_stock_image_settings( $settings, $current_section ) {

	/*
	 * These are found in includes/globals.php since they are referenced also in 
	 * class-product-creator.php
	 */
	
	global $grfx_size_default_names, 
		   $grfx_size_default_prices, 
		   $grfx_size_default_pixels, 
		   $grfx_size_default_license, 
		   $grfx_size_enabled;
	
	/**
	 * Check the current section is what we want
	 * */
	if ( $current_section == 'grfx_stock_image' ) {

		$settings_stock_image = array();

		// Add Title to the Settings
		$settings_stock_image[] = array( 'name' => __( 'grfx Stock Image Defaults', 'grfx' ), 'type' => 'title', 'desc' => __( 'These settings are applied to new uploads.', 'grfx' ), 'id' => 'grfx_stock_image' );

		
		$settings_stock_image[] = array(
			'name'     => __( 'Download Expiry', 'grfx' ),
			'desc_tip' => __( 'Maximum number of days a customer has to download a purchase', 'grfx' ),
			'id'       => 'grfx_download_expiry',
			'type'     => 'number',
			'desc'     => __( '(Days)', 'grfx' ),
			'default'  => '7'
		);

		$settings_stock_image[] = array(
			'name'     => __( 'Download Limit', 'grfx' ),
			'desc_tip' => __( 'Maximum number of times a customer can download a purchase', 'grfx' ),
			'id'       => 'grfx_download_limit',
			'type'     => 'number',
			'desc'     => __( '(Downloads)', 'grfx' ),
			'default'  => '3',
			'class'    => 'grfx-divider'
		);
		
		$i = 1;

		while($i <= count($grfx_size_default_names)){		
			
			/**
			 * Size ENABLED
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_enabled_'.$i,
				'name'     => $i.' '.__( 'Enabled', 'grfx' ),
				'desc_tip' => __( '', 'grfx' ),				
				'type'     => 'checkbox',							
				'default'  => $grfx_size_enabled['_size_enabled_'.$i]
			);					
			
			/*
			 * Size NAME
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_name_'.$i,
				'name'     => $i.' '.__( 'Title', 'grfx' ),
				'desc_tip' => __( 'Title of this purchase license', 'grfx' ),				
				'type'     => 'text',
				'desc'     => __( '(example: ', 'grfx' ).$grfx_size_default_names['_size_name_'.$i].')',
				'default'  => $grfx_size_default_names['_size_name_'.$i]
			);	
			
			/*
			 * Size PRICE
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_price_'.$i,
				'name'     => $i.' '.__( 'Price', 'grfx' ),
				'desc_tip' => __( 'Default price for this license', 'grfx' ),				
				'type'     => 'text',
				'desc'     => __( '(example: ', 'grfx' ).$grfx_size_default_prices['_size_price_'.$i].'.00'.')',
				'default'  => $grfx_size_default_prices['_size_price_'.$i].'.00'
			);	
			
			/**
			 * Size PIXELS
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_pixels_'.$i,
				'name'     => $i.' '.__( 'Size in Pixels', 'grfx' ),
				'desc_tip' => __( 'Maximum size (pixels) in any dimension for this license. \'0\' for max or N/A.', 'grfx' ),				
				'type'     => 'number',
				'desc'     => __( '(example: ', 'grfx' ).$grfx_size_default_pixels['_size_pixels_'.$i].')',
				'default'  => $grfx_size_default_pixels['_size_pixels_'.$i]
			);
			
			/**
			 * Size LICENSE
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_license_'.$i,
				'name'     => $i.' '.__( 'License', 'grfx' ),
				'desc_tip' => __( 'License that applies to this option.', 'grfx' ),				
				'type'     => 'select',
				'desc'     => '',
				'default'  => '1',
				'options'  => array(
					'1' => get_option('grfx_license_name_1', __('License One Name', 'grfx')),
					'2' => get_option('grfx_license_name_2', __('License One Two', 'grfx')),
					'3' => get_option('grfx_license_name_3', __('License One Three', 'grfx'))
				)
			);		
						
			/**
			 * Deliver file:
			 */
			$settings_stock_image[] = array(
				'id'       => '_size_type_enabled_'.$i,
				'name'     => $i.' '.__('Deliver file', 'grfx').':',	
				'desc_tip' =>  __( 'Which file type this applies to.', 'grfx' ),			
				'type'     => 'select',
				'desc'     => '',
				'default'  => '1',
				'options'  => grfx_filetypes(),
				'class'    => 'grfx-divider grfx-expand',	
			);				
			
			$i++;
		}
		
		$settings_stock_image[] = array(

		);				

		$settings_stock_image[] = array( 'type' => 'sectionend', 'id' => 'grfx_stock_image' );

		return $settings_stock_image;

		/**
		 * If not, return the standard settings
		 * */
	} else {

		return $settings;
	}
}

/**
 * grfx general settings (top woocommerce tabs)
 */
class WC_Settings_grfx {

		
    /**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 */
	public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __class__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_grfx', __class__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_grfx', __class__ . '::update_settings' );		
    }
    
    
    /**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
	 * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
	 */
	public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_grfx'] = __( 'grfx', 'grfx' );
        return $settings_tabs;
    }


	/**
	 * Form responsible for setting up the grfx watermark. Defaults existing grfx
	 * watermark if not set. 
	 */
	static function get_watermark_form(){
		
				
		$wm_path = grfx_plugin_url(). 'assets/img/watermark/watermark.png';	
		$wm_id = get_option('grfx_watermark_image_id', false);
		
		if(!$wm_id){
			$wm = $wm_path;
		} else {
			$wm = wp_get_attachment_url($wm_id);
		}
	
		?>		
		<div id="grfx_watermark_image_preview">
			
			<h2><?php _e('Watermark Preview', 'grfx') ?></h2>
			
			<img id="" src="<?php echo $wm; ?>" alt="Watermark Image" />
			
			<label id="upload_watermark_form" for="upload_image">		
				
				<span><?php _e('Upload a watermark for your stock image previews', 'grfx') ?></span><br />
				<input id="grfx_watermark_image_id" type="hidden" name="grfx_watermark_image_id" value="<?php echo $wm_id ?>" />
				<input readonly disabled id="grfx_watermark_location" type="text" size="100" name="ad_image" value="<?php echo $wm ?>" />
				<input id="grfx_upload_watermark_button" class="button" type="button" value="<?php _e('Upload Watermark', 'grfx') ?>" />
				
			</label>	
			
		</div>

        <?php         
        $copyright = get_option('grfx_copyright', false); 
        
        if(!$copyright){
            $current_user = get_userdata(get_current_user_id());
            $copyright = $current_user->display_name;
        }                
        ?>
        <hr />
        <label for="grfx_copyright">
            <strong><?php _e('Copyright text', 'grfx') ?></strong> <span class="description"> <?php _e('Used in the minipic watermark.', 'grfx') ?></span>
            <br /><input id="grfx_copyright" name="grfx_copyright" type="text" value="<?php echo stripslashes($copyright) ?>" />
        </label>
		<?php
		self::license_settings();
	}
	
	/**
	 * Sets form for license settings.
	 */
	public static function license_settings(){
		
		?>
		<br /><hr /><br />
		<h2><?php _e('Stock Image Licenses', 'grfx') ?></h2>
		<div id="license-accordion" class="">
		<?php
		
		$i=1;		
		while($i <= 3){
			
			$name = get_option( 'grfx_license_name_'.$i, false );
			$text = get_option( 'grfx_license_text_'.$i, false );
            
            if($i == 1 && (!$text || empty($text))){
                
                $home = trim(home_url(), '/');
                
                if (!preg_match('#^http(s)?://#', $home)) {
                     $home = 'http://' . $home;
                }  
                
                $urlParts = parse_url($home);
                $home = preg_replace('/^www\./', '', $urlParts['host']);
                
                $text = file_get_contents(grfx_core_plugin.'includes/templates/license.txt');
                
                $text = str_replace('#SITE#', $home, $text);

            }
            
            if($i == 1 && (!$name || empty($name))){
                $name = __('End User License Agreement', 'grfx');
            }
            
			?>
			
			<h3><?php _e('License ', 'grfx') ?> #<?php echo $i; ?></h3>			
			<div class="grfx-license-settings">				
				<?php _e('License Name', 'grfx'); ?> #<?php echo $i; ?><br />				
				<label for="grfx_license_name_<?php echo $i ?>">					
					<input type="text" class="grfx-license-name" id="grfx_license_name_<?php echo $i ?>" name="grfx_license_name_<?php echo $i ?>" value="<?php echo stripslashes($name) ?>" />					
				</label>	
				<br /><br />
				<?php
				wp_editor( stripslashes($text), 'grfx_license_text_'.$i, $settings = array('media_buttons'=>false,'teeny'=>true) );
				?>				
			</div>
			<?php			
			$i++;
		}
		
		?></div><?php
		
	}
	
    /**
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @uses woocommerce_admin_fields()
	 * @uses self::get_settings()
	 */
	public static function settings_tab() {
		
		?><span class="grfx-logo-right"><?php echo grfx_logo_img() ?></span> <h2><?php echo  grfx_plugin_info() ?></h2><?php
		
		//woocommerce_admin_fields( self::get_settings() );
		?>
		<br />
		<?php _e('Do you wish to set up prices and licenses? Go to', 'grfx') ?> <em><a title="<?php _e('configure stock image settings...', 'grfx') ?>" href="<?php echo admin_url('admin.php?page=wc-settings&tab=products&section=grfx_stock_image') ?>"><?php _e('Configure stock image settings...', 'grfx') ?></a></em>
		<br />
		<br /><hr /><br />
		<label for="grfx-uploader-cron">
			<em><?php _e('Uploader Cron Path:', 'grfx') ?></em>
			<input id="grfx-uploader-cron" class="grfx-uploader-cron" type="text" value="<?php echo grfx_cron_path ?>" />
		</label>	
		
		<?php if(!is_multisite()): ?>
			
		<label for="grfx-ftp">
			<em><?php _e('FTP Path:', 'grfx') ?></em>
			<input id="grfx-ftp" class="grfx-ftp" type="text" value="<?php echo trailingslashit(grfx_ftp_dir().get_current_user_id()) ?>" />
		</label>			
		
		<?php endif; ?>
			
        <br />

        <!-- 
        <?php $agency_enabled = grfx_agency_submission_enabled(); ?>
		<br />
		<br />
		<hr />    
        <?php _e('Enable Agency Submission Feature?', 'grfx') ?>
        <label for="grfx_agency_submit_on"> 
            <input <?php echo $agency_enabled?'checked':'' ?> type="radio" name="grfx_agency_submit" id="grfx_agency_submit_on" value="on" />
            <?php _e('On', 'grfx') ?>
        </label>        
        <label for="grfx_agency_submit_off">
            <input <?php echo $agency_enabled?'':'checked' ?> type="radio" name="grfx_agency_submit" id="grfx_agency_submit_off" value="off" />
            <?php _e('Off', 'grfx') ?>
        </label>        
        <br />        
        -->
		<?php self::get_watermark_form() ?>

		<?php
    }


    /**
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @uses woocommerce_update_options()
	 * @uses self::get_settings()
	 */
	public static function update_settings() {	
		
        woocommerce_update_options( self::get_settings() );
		
		if(isset($_POST['grfx_watermark_image_id'])){
			if(is_numeric( $_POST['grfx_watermark_image_id'])){				
                update_option('grfx_watermark_image_id', $_POST['grfx_watermark_image_id']);
            }
		}
		
        if(isset($_POST['grfx_copyright']))
            update_option('grfx_copyright', stripslashes($_POST['grfx_copyright']));
		
		$i=1;		
		while($i <= 3){
	
			$name = 'grfx_license_name_'.$i;
			$text = 'grfx_license_text_'.$i;
			
			if( isset( $_POST[ $text ] ) ){
		
				update_option( 'grfx_license_text_'.$i, $_POST[$text] );
                
			}	
            
			if( isset( $_POST[$name] ) ){
				update_option( 'grfx_license_name_'.$i, $_POST[$name] );
			}	            
			$i++;
		}	
		
    }


    /**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public static function get_settings() {
		
        $settings = array(
            'section_title' => array(
                'name'     => __( grfx_plugin_info(), 'grfx' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_grfx_section_title'
            ),
            'title' => array(
                'name' => __( 'Title', 'woocommerce-settings-tab-demo' ),
                'type' => 'text',
                'desc' => __( 'This is some helper text', 'woocommerce-settings-tab-demo' ),
                'id'   => 'wc_settings_grfx_title'
            ),
            'test' => array(
                'name' => __( 'Title', 'woocommerce-settings-tab-demo' ),
                'type' => 'text',
                'desc' => __( 'This is some helper text', 'woocommerce-settings-tab-demo' ),
                'id'   => 'wc_settings_grfx_title'
            ),			
            'description' => array(
                'name' => __( 'Description', 'grfx' ),
                'type' => 'textarea',
                'desc' => __( 'This is another field...', 'grfx' ),
                'id'   => 'wc_settings_grfx_description', 
				'default' => 'here',
            ),
			
     
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_grfx_section_end'
            ),
			
     
            'section_start1' => array(
                 'type' => 'sectionstart',
                 'id' => 'wc_settings_grfx_section_start2'
            ),
				
            'section_title2' => array(
                'name'     => __( grfx_plugin_info(), 'grfx' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_settings_grfx_section_title2'
            ),			
			
            'description2' => array(
                'name' => __( 'Description', 'grfx' ),
                'type' => 'textarea',
                'desc' => __( 'This is another field...', 'grfx' ),
                'id'   => 'wc_settings_grfx_description2', 
				'default' => 'here',
            ),
						
			
            'section_end1' => array(
                 'type' => 'sectionend',
                 'id' => 'wc_settings_grfx_section_end2'
            ),	
        );

        return apply_filters( 'wc_settings_grfx_settings', $settings );
    }
	
}

WC_Settings_grfx::init();