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
		add_action( 'init', array( $this, 'add_image_sizes' ), 999 );
		
		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

        
        //check and do cron
		add_action( 'init', array( $this, 'do_cron' ), 1 );
        //check and do upload
		add_action( 'init', array( $this, 'doing_upload' ), 1 );        
        
        
		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		$installed = get_option('grfx_installed_installed', false);
		
		if(!$installed){
			$this->file_system_setup();
			$this->install_db();
			grfx_set_sitepass();
			$this->activate_exiftool();
            $this->set_defaults();
		}
        
        add_action('wp_head', array($this, 'preview_image_open_graph_data'));
        
        add_filter('woocommerce_single_product_image_html', array($this, 'image_schema'));
        
		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

	}

    /**
     * Handles open graph data for stock image product
     * 
     * @global type $product
     * @return type
     */
    public function preview_image_open_graph_data(){
                       
        if(!is_single())
            return;        
        
        global $post;
        
        if(!is_product())
            return;
        
        $product = get_product($post->ID);        
        
        if( !$product->is_type( 'stock_image' ) )
            return;
        
        $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) );               
        
        $og = "\n<!-- grfx Open Graph Data -->";
        
        //IMAGE
        $og .= "\n<meta property='og:image' content='".$url."' />";
        
        //TITLE
        $og .= "\n<meta property='og:title' content='" . $post->post_title. ' ' . __(' - Stock Image:', 'grfx')."' />";
        
        //URL
        $og .= "\n<meta property='og:url' content='" . get_the_permalink() ."' />";
        
        //TYPE
        $og .= "\n<meta property='og:type' content='product.item' />";        
        
        //DESCRIPTION
        $og .= "\n<meta property='og:description' content='".__('Stock image: ', 'grfx'). $post->post_title."' />";            
        
        $og .= "\n<!-- /grfx Open Graph Data -->\n";
        echo $og;
        
    }
    
    /**
     * Generates a special product image for grfx
     * @global type $post
     * @global type $woocommerce
     * @global type $product
     * @return type
     */
    public function generate_product_image(){
        global $post, $woocommerce, $product;    
        
        $image_caption 	= get_post( get_post_thumbnail_id() )->post_excerpt;
        $image_link  	= wp_get_attachment_url( get_post_thumbnail_id() );
        $image       	= get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'full' ), array(
            'title'	=> $post->post_title,
            'alt'	=> $post->post_title
            ) 
        );  

        $attachment_count = count( $product->get_gallery_attachment_ids() );

        if ( $attachment_count > 0 ) {
            $gallery = '[product-gallery]';
        } else {
            $gallery = '';
        }            
        
        $image_html = sprintf( '<a href="%s" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s</a>', $image_link, $image_caption, $image );
        
        return $image_html;
        
    }
    
    /**
     * Sets up a much-improved image and schema for visuals and SEO
     * @global type $product
     * @param type $image_html
     * @return string
     */
    public function image_schema($image_html){
        
        $image_html = $this->generate_product_image();
        
        global $product;
        
        /**
                  *  IMAGE
                  */
        $post_thumbnail_id = get_post_thumbnail_id();
        $post_thumbnail_url = wp_get_attachment_url( $post_thumbnail_id );           
        $this->image_url = $post_thumbnail_url;       
        
        
        /**
                  *  TAGS
                  */        
        $tags = strip_tags($product->get_tags( ', ', '', '' ));        

        
        $schema.='  <div itemscope itemtype="http://schema.org/ImageObject">'           ."\n";
        
        $schema.='      <meta itemprop="caption" content="'.get_the_title().'" />'      ."\n";
        
        $schema.='      <meta itemprop="contentUrl" content="'.$this->image_url.'" />'  ."\n";
        
        $schema.='      <meta itemprop="description" content="'.get_the_title().'" />'  ."\n";
        
        $schema.='      <meta itemprop="keywords" content="'.$tags.'" />'               ."\n";
        
        $schema.='      <meta itemprop="author" content="'.get_the_author().'" />'      ."\n";
        
        $schema.='      <meta itemprop="contributor" content="'.get_the_author().'" />' ."\n";
        
        $schema.='      <meta itemprop="creator" content="'.get_the_author().'" />'     ."\n";
        
        $schema.='      <meta itemprop="datePublished" content="'.get_the_date().'" />' ."\n";
        
        $schema.='      <meta itemprop="representativeOfPage" content="1" />'           ."\n";
        
        $schema.= str_replace('itemprop="image"', '', $image_html)."\n";
        
        $schema.='  </div>'."\n";
        
        
        return $schema;
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
		self::set_defaults();
        
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
        if(grfx_use_shell_exec())
            shell_exec('chmod a+x '.grfx_core_plugin . 'admin/includes/exiftool/exiftool');
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
     * Sets up certain default values on activation
     */
    public function set_defaults(){        
        
        global $grfx_size_default_names, 
                $grfx_size_default_prices,
                $grfx_size_default_pixels,
                $grfx_size_default_license,
                $delivery_defaults,
                $grfx_size_enabled ;
        
        $defaults = array(
                $grfx_size_default_names, 
                $grfx_size_default_prices,
                $grfx_size_default_pixels,
                $grfx_size_default_license,
                $delivery_defaults,
                $grfx_size_enabled 
        );
                
        foreach($defaults as $d){
            foreach($d as $k=>$v){
                $set = get_option($k, '-');

                if($set == '-'){
                    update_option($k, $v);
                }
            }
        }
        
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
    
    /**
     * Starts up the cron sequence to check uploads and other things
     * @return type
     */
    public function do_cron(){
             
        if(isset($_GET['grfx_crontype']) && isset($_GET['grfx_cronpass'])){
                  
            if(!defined('SHORTINIT'))
                define('SHORTINIT', true);
            
            define('DONOTCACHEPAGE', true);
            define('DONOTCACHEDB', true);
            define('DONOTMINIFY', true);
            define('DONOTCDN', true);
            define('DONOTCACHCEOBJECT', true);
            
            define('grfx_DOING_CRON', true);

            require_once(grfx_core_plugin.'includes/class-cron.php');

            require_once(grfx_core_plugin.'cron.php');
                     
            die();
            
        } else {
            
            return;
            
        }
    }
    
    /**
     * IF doing upload, abort wordpress (not needed)
     */
    public function doing_upload(){
            
        
    }

}
