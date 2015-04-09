<?php
/**
 * grfx
 *
 * @package   grfx
 * @author    Leo Blanchette <clipartillustration.com@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.grfx.com
 * @copyright 2014 Leo Blanchette
 */

/**
 * grfx class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to class-grfx-admin.php
 *
 * @package grfx
 * @author  Leo Blanchette <clipartillustration.com@gmail.com>
 */
class grfx {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = grfx_version;

	/**
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'grfx';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		
		//set up image sizes
		add_action( 'init', array( $this, 'add_image_sizes' ) );
		
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		$installed = get_option('grfx_installed_installed', false);
		
		if(!$installed){
			$this->file_system_setup();
			$this->install_db();
			grfx_set_sitepass();
			$this->activate_exiftool();
		}
				
		
		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

	}

	/**
	 * Add image size particular to grfx
	 */
	public function add_image_sizes(){
		add_image_size( 'grfx_minipic', 250, 250, true );
		add_image_size( 'grfx_preview', 550, 550, true );
	}
	
	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
			
		self::file_system_setup();  
		grfx_set_sitepass();
		self::install_db();	
		self::activate_exiftool();
		
		update_option('grfx_installed_installed', true);
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			
			if ( $network_wide  ) {
								
				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();   
		}

	}

	public function activate_exiftool(){
		exec('chmod a+x '.grfx_core_plugin . 'admin/includes/exiftool/exiftool');
	}
	
	public function install_db(){
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		global $wpdb;
		
		$sql = "CREATE TABLE IF NOT EXISTS grfx_upload_tracking (
				upload_id int(6)  NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  user_id int(11) NOT NULL,
				  site_id int(11) NOT NULL,
				  original_name varchar(200) NOT NULL,
				  file_name varchar(200) NOT NULL,
				  file_type varchar(50) NOT NULL,
				  file_size int(12) NOT NULL,
				  extension varchar(10) NOT NULL,
				  enqueued tinyint(1) NOT NULL DEFAULT '0',
				  to_draft tinyint(1) NOT NULL DEFAULT '1',
				  datetime timestamp NULL DEFAULT CURRENT_TIMESTAMP
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=204 ;";
		
		dbDelta($sql);
		dbDelta("ALTER TABLE grfx_upload_tracking CHANGE upload_id upload_id INT(6) NOT NULL AUTO_INCREMENT;");
		
		$sql = "CREATE TABLE IF NOT EXISTS grfx_cron_log (
				cron_id int(11) PRIMARY KEY AUTO_INCREMENT NOT NULL,
				  locked tinyint(1) NOT NULL DEFAULT '0',
				  files_processed int(11) NOT NULL,
				  megabytes_processed int(11) NOT NULL,
				  time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				);";
		dbDelta($sql);
		
		
		$sql = "CREATE TABLE IF NOT EXISTS grfx_product_option (
					product_id int(11) NOT NULL,
					product_option int(11) NOT NULL,
					userhash varchar(64) NOT NULL,
					time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
				  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";		
		dbDelta($sql);	
		
	}
	
	/**
	 * Sets up filesystem on activation
	 */
	public function file_system_setup(){
                                  
		/*
		 * Make special uploads and product directories
		 */

		/*
		 * wp-content/grfx_uploads/
		 */
		wp_mkdir_p( grfx_uploads_dir()); 
		
		/*
		 * wp-content/grfx_uploads/ftp
		 */
		wp_mkdir_p( grfx_ftp_dir() ); 
		
		/*
		 * wp-content/grfx_uploads/ftp/<user_id>
		 */
		wp_mkdir_p( grfx_ftp_dir().get_current_user_id() ); 		
		

		/*
		 * wp-content/grfx_uploads/protected/
		 */
		wp_mkdir_p( grfx_protected_uploads_dir() ); 
		
		
		/*
		 * wp-content/grfx_uploads/content/
		 */                        
		wp_mkdir_p( grfx_content_uploads_dir() );  

		/*
		 * wp-content/grfx_uploads/delivery/
		 */                        
		wp_mkdir_p( grfx_delivery_dir() );  		
		
		/*
		 * grfx/ (root level folder)
		 */                           
		wp_mkdir_p( grfx_product_dir() );

		/*
		 * Make protective .htaccess file (forbids unauthorized access)
		 */
		grfx_write_file( grfx_protected_uploads_dir() . '.htaccess', 'deny from all' );
		grfx_write_file( grfx_delivery_dir() . '.htaccess', 'deny from all' );
		grfx_write_file( grfx_product_dir().'.htaccess', 'deny from all' );		
	}
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {

	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		if ( is_product() ){
			wp_enqueue_script( $this->plugin_slug . '-modal', plugins_url( 'assets/js/jquery.easyModal.js', __FILE__ ), array( 'jquery' ), self::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-product-page', plugins_url( 'assets/js/product-page.js', __FILE__ ), array( 'jquery' ), self::VERSION );
		}
		
		if ( is_cart() ){
			wp_enqueue_script( $this->plugin_slug . '-modal', plugins_url( 'assets/js/jquery.easyModal.js', __FILE__ ), array( 'jquery' ), self::VERSION );	
			wp_enqueue_script( $this->plugin_slug . '-cart-page', plugins_url( 'assets/js/cart-page.js', __FILE__ ), array( 'jquery' ), self::VERSION );		
		}		
		
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

}
