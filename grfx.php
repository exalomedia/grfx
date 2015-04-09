<?php
/**
 * grfx
 *
 * grfx Core - Core functionality of the grfx system.
 *
 * @package   grfx
 * @author    Leo Blanchette <clipartillustration.com@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.grfx.com
 * @copyright 2014 Leo Blanchette
 *
 * @wordpress-plugin
 * Plugin Name:       grfx
 * Plugin URI:        http://www.grfx.com
 * Description:       grfx Core - Core functionality of the grfx system.
 * Version: 1.1.22
 * Author:            Leo Blanchette
 * Author URI:        
 * Text Domain:  grfx
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: 
 */

define('grfx_version', '1.1.22');

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This constant should be checked by dependent grfx plugins. If it is not defined,
 * the plugin should self-deactivate.
 * 
 * @package grfx
 * @subpackage Constants
 */
function grfx_core_active() {
    define('grfx_core_active', 1);
}

grfx_core_active();

/* ----------------------------------------------------------------------------*
 * General Functionality
 * ---------------------------------------------------------------------------- */

/**
 * Main path to plugin.
 *
 * @package grfx
 * @subpackage Constants
 */
function grfx_core_plugin() {
    if (!defined('grfx_core_plugin'))
        define('grfx_core_plugin', trailingslashit(plugin_dir_path(__FILE__)));
	return grfx_core_plugin;
}
grfx_core_plugin();


/**
 * Main basename to plugin.
 *
 * @package grfx
 * @subpackage Constants
 */
function grfx_plugin_basename() {
    if (!defined('grfx_plugin_basename'))
        define('grfx_plugin_basename',plugin_basename( __FILE__ ));
	return grfx_plugin_basename;
}
grfx_plugin_basename();



/**
 * Main url to plugin.
 *
 * @package grfx
 * @subpackage Constants
 */
function grfx_core_url() {
    if (!defined('grfx_core_url'))
        define('grfx_core_url', trailingslashit(plugins_url( )).'grfx/');
	return grfx_core_url;
}
grfx_core_url();

/*----------------------------------------------------------------------------*
 * General Functionality
 *----------------------------------------------------------------------------*/

/**
 * Check if WooCommerce is active -- if not, give notification.
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){

	
/*
 * Get Constants
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/globals.php' );


/*
 * Get main functions file
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/functions.php' );


/*
 * Set up woocommerce / grfx cart integration
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/woocommerce/class-cart.php' );
        
/*
 * Set up woocommerce / grfx integration
 */
require_once( plugin_dir_path( __FILE__ ) . 'includes/woocommerce/class-wc-grfx-settings.php' );

/*
 * Set up custom product: Stock Image
 */
require_once(plugin_dir_path( __FILE__ ) . 'includes/woocommerce/product-types/class-wc-product-stock-image.php' );    


/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-grfx.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'grfx', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'grfx', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'grfx', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin( ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-grfx-admin.php' );
	add_action( 'plugins_loaded', array( 'grfx_Admin', 'get_instance' ) );
	
	require_once ('admin/ajax.php');
	
	/*
	 * PLUGIN MANAGEMENT FUNCTIONS (Setup, updates, stability)
	 */

	/*
	 * If not multisite, we tell site owner to get woocommerce.
	 * 
	 * A multi-site webmaster has no excuse to not know this! :)
	 */
	if(!is_multisite()){
		require_once('admin/includes/plugins/plugins.php');
	}

	require_once('admin/includes/plugins/wp-updates-plugin.php');
		new WPUpdatesPluginUpdater_964( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));
		
	/*
	 * Redirect user to various pages on various events (such as plugin activation)
	 */
	require_once( plugin_dir_path( __FILE__ ) . 'includes/admin-redirect.php' );		
	
}

}else{	
	/**
	 * Gives a notice that Woocommerce should be installed before operating plugin.
	 */
	function grfx_install_plugins_notice() {
		?>
		<div class="error">
			<p><?php _e( 'First install Woocommerce for grfx to work.', 'grfx' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'grfx_install_plugins_notice' );	
	
	if(!is_multisite()){
		require_once('admin/includes/plugins/plugins.php');
	}	
	
	update_option('grfx_installed_installed', false);
}

