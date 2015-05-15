<?php

/**
 * grfx
 *
 * @package   grfx_Admin
 * @author    Leo Blanchette <clipartillustration.com@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.grfx.com
 * @copyright 2014 Leo Blanchette
 */

/**
 * grfx_Admin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @package grfx_Admin
 * @author  Leo Blanchette <clipartillustration.com@gmail.com>
 */
class grfx_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = grfx::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		add_action( 'init', array( $this, 'set_cookies' ) );

		//runs a very simple directory check - only "crunches" if files must be registered and moved.
		add_action( 'init', array( $this, 'process_ftp_uploads' ) );
	
        //setup_uploader_config
		add_action( 'init', array( $this, 'setup_uploader_config' ) );
        
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
                
		if(!grfx_use_imagick()){
			add_action( 'admin_notices', array($this, 'enable_imagemagick_nag') );	
		}
		
        if(!grfx_use_shell_exec()){
            add_action( 'admin_notices', array($this, 'enable_shell_exec_nag') );
        }
				
		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( '@TODO', array( $this, 'filter_method_name' ) );

		/*
		 * Set up uploader
		 */
		add_action( 'admin_menu', array( $this, 'setup_uploader_admin' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
		  return;
		  } */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function set_cookies() {
		
		if ( isset( $_GET['page'] ) && $_GET['page'] != 'grfx_uploader'  )
            return;
        
        global $grfx_SITE_ID;

		//set grfx user ID cookie        

        setcookie( 'grfx-user-id', null, time() -999999, COOKIEPATH, COOKIE_DOMAIN, false );
		setcookie( 'grfx-user-id', get_current_user_id(), time() + 3600 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN, false );	

        setcookie( 'grfx-blog-id', null, time()  -999999, COOKIEPATH, COOKIE_DOMAIN, false );  
		setcookie( 'grfx-blog-id', $grfx_SITE_ID, time() + 3600 * 24 * 100, COOKIEPATH, COOKIE_DOMAIN, false );
		
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'grfx_uploader' ) {
			wp_enqueue_style( $this->plugin_slug . '-admin-styles-plupload-styles-queue', grfx_plugin_url() . 'admin/includes/uploader/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css', array(), grfx::VERSION );
		}

		wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), grfx::VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( !isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}


		if ( isset($_GET['tab'] ) && $_GET['tab'] == 'settings_grfx' ) {
			
			wp_enqueue_media();			
			//get necessary jquery UI elements
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_script( 'jquery-ui-spinner' );	
			
		}
		
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'grfx_uploader' ) {

			//get plupload                    
			wp_enqueue_script( $this->plugin_slug . '-admin-script-uploader', grfx_plugin_url() . 'admin/includes/uploader/plupload/js/plupload.full.min.js', array( 'jquery' ), grfx::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-admin-script-uploader-queue', grfx_plugin_url() . 'admin/includes/uploader/plupload/js/jquery.plupload.queue/jquery.plupload.queue.min.js', array( 'jquery' ), grfx::VERSION );

			//get necessary jquery UI elements
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-widget ' );
			wp_enqueue_script( 'jquery-ui-button ' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-effects-core ' );

			//get grfx uploader screen js 
			wp_enqueue_script( $this->plugin_slug . '-admin-script-uploader-grfx', grfx_plugin_url() . 'admin/assets/js/uploader.js', array( 'jquery' ), grfx::VERSION );


			add_action( 'admin_footer', array( $this, 'uploader_js' ) );
		}
		wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), grfx::VERSION );
		wp_localize_script( $this->plugin_slug . '-admin-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
				__( 'grfx Settings', $this->plugin_slug ), __( 'grfx', $this->plugin_slug ), 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
				array(
			'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
				), $links
		);
	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function filter_method_name() {
		// @TODO: Define your filter hook callback here
	}

	/**
	 * Sets up stock uploading panel link under woocommerce "products"
	 */
	public function setup_uploader_admin() {
		add_submenu_page( 'edit.php?post_type=product', __( 'Stock Product Uploader', 'grfx' ), __( 'Upload Stock', 'grfx' ), 'manage_options', 'grfx_uploader', array( $this, 'setup_uploader_panel' ) );
	}

	/**
	 * Set up stock uploading panel
	 */
	public function setup_uploader_panel() {
		define( 'grfx_DOING_UPLOAD_PANEL', true );
		require_once('includes/uploader/class-upload-tracker.php');
		require_once('includes/uploader/class-uploader.php');

		$uploader = new grfx_Uploader();
		$uploader->render_html();
	}

	/**
	 * Set up uploader JS in footer
	 */
	public function uploader_js() {
		$uploader = new grfx_Uploader();
		$uploader->render_js();
	}
	
	/*
	 * OTHER METHODS
	 */
	
	/**
	 * Warn user if imagick is not currently active.
	 */
	public function enable_imagemagick_nag(){
		?>
		<div class="error">
			<p><?php _e( '<strong>grfx</strong> Warning: Imagemagick is not installed or enabled. Images delivered to your customers, as well as preview images, will lack professional quality, especially where advanced color models were used in device or image editing software. This must be corrected.', 'grfx' ); ?></p>
			<p><?php _e('In your <strong>php.ini</strong> file (root directory of your site) simply inserting <strong>extension=imagick.so</strong> at the end of the file may be enough, depending on your host.', 'grfx') ?></p>
			<p><a title="<?php _e('See more here.', 'ss') ?>" href="http://php.net/manual/en/imagick.setup.php"><?php _e('Install imagick for PHP', 'ss') ?></a></p>
            <?php echo grfx_encourage_fix(); ?>
        </div>
		<?php
	}
    
	/**
	 * Warn user if shell_exec is not reachable
	 */
	public function enable_shell_exec_nag(){
        if(isset($_GET['shell_nag']) && $_GET['shell_nag']==0){
            update_option('grfx_shell_nag', 'off');
        }
        
        $display_shell_nag = get_option('grfx_shell_nag', 'on');
        
        if($display_shell_nag == 'off')
            return;
        
		?>
		<div class="error">               
			<p><?php _e( '<strong>grfx</strong> Warning: The <strong>shell_exec()</strong> function has been disabled by your host. Please request them to enable it.', 'grfx' ); ?></p>
			<p><?php _e('<strong>grfx</strong> uses <strong>shell_exec()</strong> function to employ the advanced meta-data reading functions for uploaded images. This is not an essential function and you can do without it, but it helps.', 'grfx') ?></p>		
            <strong><a class="welcome-panel-close" href="<?php echo admin_url('options-general.php?page=grfx&shell_nag=0') ?>"><?php _e('Dismiss this notice.', 'grfx') ?></a></strong><br />   
            <?php echo grfx_encourage_fix(); ?>
        </div>
		<?php
	}
	    
	
	
	
	/**
	 * Check to see if there are uploads staged in FTP.
	 * @return boolean true if uploads, false if not
	 */
	public function has_ftp_uploads(){
		
        if(!is_admin())
            return false;
        
		$uploads = array();

		$user_upload_folder = trailingslashit(grfx_ftp_dir().get_current_user_id());
		
		if(!is_dir( $user_upload_folder ) )	{
			wp_mkdir_p( $user_upload_folder );
			grfx_write_file( $user_upload_folder.'.htaccess', 'deny from all' );	
		}
		
		$files = scandir( $user_upload_folder );

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == '.ftpquota' )
					continue;
				array_push( $uploads, $file );
			}
		}		
		
		if(!empty($uploads)){
			return true;
		} else {
			return false;
		}
		
	}
	
	/**
	 * Processes uploads in user upload area.
	 */
	public function process_ftp_uploads(){
		
		if(!$this->has_ftp_uploads())
			return;
		
		define('grfx_DOING_FTP', true);

		require_once(grfx_core_plugin. 'admin/includes/uploader/class-upload-tracker.php');

		
		$uploads = array();

		$files = scandir( trailingslashit(grfx_ftp_dir().get_current_user_id()) );

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == '.ftpquota' )
					continue;
				array_push( $uploads, $file );
			}
		}
		
		/*
         * We only add files over two minutes old (to ensure nothing is swiped during upload)
         */
		foreach($uploads as $file){
			
            if (time()-filemtime($file) > 3 * 60) {        
                //file is over two minutes old...
                $tracker = new grfx_Upload_Tracker($file);

                $tracker->prepare_file_from_ftp();
            } else {
              return;
            }   
			
		}
				
	}
        
    /**
     * Checks to see if database credentials have changed based on a specific 
     * database credentials string
     * 
     * @param type $credentials
     * @return boolean
     */
    public function database_credentials_changed($credentials){
        
        $hash_file = grfx_core_plugin.'admin/includes/uploader/plupload/.hash'; 
        
        if(!file_exists($hash_file)){
            return true;
        } else {
            $hash = file_get_contents($hash_file);
            
            if(md5($credentials) == $hash){
                return false;
            } else {
                return true;
            }
        }
        
    }
    
    /**
     * Sets up uploader configurations and variables to give access to database.
     * Config file is encrypted so that it cannot be accessed by other parties. 
     * Its a dot file as well to avoid direct viewing (as though encryption were 
     * not enough!)
     */
    public function setup_uploader_config(){
                
        require_once('class-encrypt.php');
        
        $config_file = grfx_core_plugin.'admin/includes/uploader/plupload/.config';
        $pass_file = grfx_core_plugin.'admin/includes/uploader/plupload/.check';  
        $hash_file = grfx_core_plugin.'admin/includes/uploader/plupload/.hash';    
        
        
        $sitepass = grfx_get_sitepass();
        $cf = "DB_HOST='".DB_HOST."';\n";
        $cf .= "DB_USER='".DB_USER."';\n";
        $cf .= "DB_PASSWORD='".DB_PASSWORD."';\n";
        $cf .= "DB_NAME='".DB_NAME."';\n";

        $credentials_changed = $this->database_credentials_changed($cf);
        
        if($credentials_changed){     
            
            file_put_contents($hash_file, md5($cf));
            
            $crypt = new grfx_Encryption($sitepass);
                        
            $encrypted_string = $crypt->encrypt($cf);        

            if(file_exists($config_file)){

                $string = file_get_contents($config_file);

                if($string != $encrypted_string){
                    grfx_write_file($config_file, $encrypted_string); 
                    grfx_write_file($pass_file, $sitepass);
                }

            } else {            
                grfx_write_file($config_file, $encrypted_string);  
                grfx_write_file($pass_file, $sitepass);
            }         
        }
 
    }
    /*
     * AGENCY FTP FUNCTIONS
     */
    
    public function set_up_agency_submission_feature(){
        
        if(!grfx_agency_submission_enabled())
            return;
        
        //still to do
        
        
    }
    
    

}
