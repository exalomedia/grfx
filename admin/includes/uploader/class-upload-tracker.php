<?php
/**
 * grfx Upload Tracking Class
 *
 * @package grfx
 * @subpackage grfx_Uploader
 */


/*
 * Get wordpress stuff for database if we are calling file directly from uploader
 */

if ( !defined( 'grfx_DOING_UPLOAD_PANEL' ) && !defined( 'grfx_AJAX' ) && !defined('grfx_DOING_CRON') && !defined('grfx_DOING_FTP') ){
	
    /*
     * We need our uploader to get wordpress database info without accessing 
     * the core files. So we have this secure encryption system set up which
     * helps us out. 
     */
    
    define('GRFX_GETTING_INFO', true);		
    
    require_once('../../../class-encrypt.php');
    
    $config_file = '.config';
    $pass_file   = '.check';  
            
    $sitepass = file_get_contents($pass_file);
    
    $decrypt = new grfx_Encryption($sitepass);
    $ini = $decrypt->get_ini_file($config_file);
    
    if(!defined('DB_HOST'))
        define('DB_HOST', $ini['DB_HOST']);
    
    if(!defined('DB_USER'))
        define('DB_USER', $ini['DB_USER']);
    
    if(!defined('DB_PASSWORD'))
        define('DB_PASSWORD', $ini['DB_PASSWORD']);
    
    if(!defined('DB_NAME'))
        define('DB_NAME', $ini['DB_NAME']);        
    
}
	
function grfx_test_writer( $filename, $content ) {
	$fp = fopen( $filename, 'w' );
	fwrite( $fp, $content );
	fclose( $fp );
}

/**
 * grfx upload tracker manages both basic installs and multisite uploads.
 * After uploading process, it adds the uploads to a queue which is taken over by
 * a cron job.
 * 
 * @package    grfx
 */

class grfx_Upload_Tracker {

	/**
	 * User ID as defined by cookie set in admin.
	 * @var int 
	 */
	public $user_id = 0;

	/**
	 * Blog ID as set in admin
	 * @var int 
	 */
	public $blog_id = 0;

	/**
	 * Database table prefix as defined in config
	 * @var type 
	 */
	public $table_prefix = '';

	/**
	 * Path to file being processed
	 * @var type 
	 */
	public $file = '';

	/**
	 * Original name of file (ie, dog running)
	 * @var type 
	 */
	public $original_name = '';

	/**
	 * Name of file in question (a hash)
	 * @var type 
	 */
	public $file_name = '';

	/**
	 * image/jpeg, etc
	 * @var type 
	 */
	public $file_mime = '';

	/**
	 * Extension of file (eps, jpg)
	 * @var type 
	 */
	public $extension = '';

	/**
	 * Size of file in question
	 * @var type 
	 */
	public $file_size = '';

	/**
	 * Last insert ID (for retrieving again from database if needed)
	 * @var type 
	 */
	public $last_insert = 0;

	/**
	 * Sets all variables 
	 */
	function __construct( $file = '' ) {

		/**
		 * Check to see if we are processing a file. If so, set it up.
		 */
		if ( !empty( $file ) ) {
			$this->file = $file;

			$this->set_file_info();

			global $filePath;
			global $table_prefix;

			//get user cookies
			if ( isset( $_COOKIE['grfx-user-id'] ) && isset( $_COOKIE['grfx-blog-id'] ) ) {

				if ( !is_numeric( $_COOKIE['grfx-user-id'] ) && !is_numeric( $_COOKIE['grfx-blog-id'] ) )
					return 0;

				$user_id = trim( $_COOKIE['grfx-user-id'] );
				$this->user_id = $user_id;

				$blog_id = trim( $_COOKIE['grfx-blog-id'] );
				$this->blog_id = $blog_id;

				$this->table_prefix = $table_prefix;
			} else {
				return 0;
			}
		} else { //if(!empty($file))
			//If we are not processing a file, we are probably doing other tasks
		}
	}

	/**
	 * If user has FTP'd anything, this directive is run on an admin page load
	 */
	function prepare_file_from_ftp(){
        
        global $grfx_SITE_ID;
        
        $this->user_id = get_current_user_id();
        $this->site_id = $grfx_SITE_ID;
        
        
		$ftpdir = trailingslashit(grfx_ftp_dir().get_current_user_id());
		
		rename($ftpdir.$this->original_name, grfx_protected_uploads_dir().$this->file_name);
		$this->log_upload();
	}
	
	/**
	 *  This gets uploads in the queue, oldest to new, (with a certain limit applied)
	 * @global object $wpdb
	 * @param  int $limit amount of uploads we wish to process
	 * @return array|bool the uploads or false if none
	 */
	public function get_uploads($limit = 100){
		global $wpdb;		
		$sql = 'SELECT * FROM grfx_upload_tracking WHERE enqueued = 1 ORDER BY datetime ASC LIMIT '.$limit;
		$uploads = $wpdb->get_results($sql);		
		return $uploads;
	}
    
	/**
	 *  This gets uploads whether or not they are queued.
	 * @global object $wpdb
	 * @param  int $limit amount of uploads we wish to process
	 * @return array|bool the uploads or false if none
	 */    
	public function get_uploads_queued_or_not($limit = 100){
		global $wpdb;		
		$sql = 'SELECT * FROM grfx_upload_tracking ORDER BY datetime ASC LIMIT '.$limit;
		$uploads = $wpdb->get_results($sql);		
		return $uploads;
	}    
	
    
	/**
	 * Records upload to database.
	 * @return type
	 */
	public function log_upload() {
        
        global $grfx_SITE_ID;
        
        if(isset($grfx_SITE_ID) && function_exists('get_current_user_id')){
     
            $this->blog_id = $grfx_SITE_ID;
            $this->user_id = get_current_user_id();
        } else {
            
			//get user cookies
			if ( isset( $_COOKIE['grfx-user-id'] ) && isset( $_COOKIE['grfx-blog-id'] ) ) {

				if ( !is_numeric( $_COOKIE['grfx-user-id'] ) && !is_numeric( $_COOKIE['grfx-blog-id'] ) )
					return 0;

				$user_id = trim( $_COOKIE['grfx-user-id'] );
				$this->user_id = $user_id;

				$blog_id = trim( $_COOKIE['grfx-blog-id'] );
				$this->blog_id = $blog_id;

			}            
            
        }
		// Create connection
		$conn = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
		// Check connection
		if ( $conn->connect_error ) {
			die( "Connection failed: " . $conn->connect_error );
		}

		$sql = "INSERT INTO grfx_upload_tracking 
			(user_id, site_id, original_name, file_name, file_type, extension, file_size)
			VALUES (
				'" . $this->user_id . "',
				'" . $this->blog_id . "',
				'" . addslashes( $this->original_name ) . "',
				'" . addslashes( $this->file_name ) . "',
				'" . $this->file_mime . "',
				'" . addslashes( $this->extension ) . "',
				'" . $this->file_size . "');";


		if ( $conn->query( $sql ) === TRUE ) {
			
		} else {
			
		}
		$id = $conn->insert_id;

		$this->last_insert = $id;

		$conn->close();

		return $id;
	}

	public function get_file_info( $upload_id ) {
		global $wpdb;

		$sql = 'SELECT * FROM grfx_upload_tracking WHERE upload_id = ' . $upload_id;

		$results = $wpdb->get_row( $sql );

		return $results;
	}

	/**
	 * Sets up file info for given file before logging to database
	 * Renames file to a hash to avoid name collisions.
	 */
	public function set_file_info() {

		$path_parts = pathinfo( $this->file );

		//set up path parts
		$name = $path_parts['filename'];
		$ext = $path_parts['extension'];
		$dirname = $path_parts['dirname'];
		$basename = $path_parts['basename'];

		//record original file name
		$this->original_name = $basename;

		//generate a random hash for file (to avoid clashes with others)
		$rand_string = time() . mt_rand( 1, 10000 );

		$new_name = $dirname . '/' . md5( $rand_string ) . '.' . $ext;

		//change file name
		rename( $this->file, $new_name );

		//start recording file info into object

		$this->file = $new_name;
		$this->extension = $ext;

		//get filesize
		$this->file_size = filesize( $this->file );

		//get mimi-type		
		$this->file_mime = pathinfo($this->file, PATHINFO_EXTENSION);		
		
		//get filename
		$this->file_name = basename( $this->file );
		
	}

	/**
	 * Gets user uploads
	 */
	public function get_user_uploads( $user_id = false, $blog = false ) {

		global $wpdb;

		if ( $user_id == false && $blog == false ) {
			global $grfx_SITE_ID;

			$user_id = get_current_user_id();
			$blog = $grfx_SITE_ID;
		}

		$uploads = $wpdb->get_results( "SELECT * FROM grfx_upload_tracking WHERE user_id = $user_id AND site_id = $blog", OBJECT );

		if ( $uploads ) {
			return $uploads;
		} else {
			return false;
		}
	}

	/**
	 * Checks files in upload directory against database. If not in database, they are deleted.
	 */
	public function clean_uploads_dir() {
		global $wpdb;

        global $grfx_SITE_ID;
        
		$uploads = scandir( grfx_protected_uploads_dir(), 1 );

		$files = implode( '%" OR file_name LIKE "%', $uploads );

		$sql = 'SELECT file_name FROM grfx_upload_tracking WHERE file_name LIKE "%' . $files . '%"';

		$contained_uploads = $wpdb->get_results( $sql, ARRAY_A );

		$database_uploads = array();

		foreach ( $contained_uploads as $upload )
			array_push( $database_uploads, $upload['file_name'] );

		foreach ( $uploads as $u ) {
			$file = grfx_protected_uploads_dir() . $u;

			if ( !in_array( $u, $database_uploads ) ) {
				if ( $u == '.' || $u == '..' || $u == '.htaccess' || $u == '.ftpquota' )
					continue;
				unlink( $file );
			}
		}
        
        if(function_exists('get_current_user_id')){
            $this->blog_id = $grfx_SITE_ID;
            $this->user_id = get_current_user_id();
        } else {
            return;
        }
        
        $db_entries = $this->get_uploads_queued_or_not(1000);
                    
        foreach($db_entries as $dbu){
            if(!file_exists(grfx_protected_uploads_dir() . $dbu->file_name)){
                $this->delete_selected( array($dbu->upload_id), $this->user_id, $this->blog_id );
            }
        }
        
	}

	/**
	 * Main function to coordinate processing of uploads
	 * @return boolean
	 */
	public function process_uploads() {

		global $grfx_SITE_ID;

		if ( !isset( $_POST['grfx-process-action'] ) )
			return false;

		$this->user_id = get_current_user_id();
		$this->blog = $grfx_SITE_ID;

		switch ( $_POST['grfx-process-action'] ) {

			case 1: //process to draft
				$this->process_to_draft();
				break;


			case 2: //process to publish
				$this->process_to_publish();
				break;

			case 3: //delete selected

				if ( empty( $_POST['grfx-upload-ids'] ) )
					return;
				$this->delete_selected( $_POST['grfx-upload-ids'], $this->user_id, $this->blog_id );

				break;


			case 4: //delete ALL

				$this->delete_all();

				break;
		}
	}
	
	/**
	 * Enqueues an upload to be transformed to a product.
	 * 
	 * @param type $id id of upload
	 * @param type $draft whether or not resulting product should be a draft or published.
	 */
	public function enqueue($id, $draft = 1){
		global $wpdb;
		
		$wpdb->update( 
			'grfx_upload_tracking', 
			array( 
				'enqueued' => 1,	
				'to_draft' => $draft	
			), 
			array( 'upload_id' => $id ), 
			array( 
				'%d',	
				'%d'	
			)
		);
		
        $tmp = grfx_tmp_dir();
        file_put_contents($tmp.'filesum', '0');
		//grfx_test_writer('test.txt', $wpdb->last_query);
		
	}

	/**
	 * Processes an image and enqueues it to draft form.
	 * @return string json encoded response.
	 */
	public function process_to_draft() {


		if ( empty( $_POST['grfx-upload-ids'] ) )
			return false;


		$id = $_POST['grfx-upload-ids'];

		$results['processed_id'] = $id;
		
		$info = $this->get_file_info( $id );

		if($info->extension != 'jpeg' && $info->extension != 'jpg'){
			sleep( 1 );
			$message = '<span class="grfx-success">' . __( 'This is a complementary file.', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );	
			echo $return;
			return 0;
		}
		
		if(!is_multisite() && isset($_POST['grfx-process-now']) && $_POST['grfx-process-now'] == 1){
			
			/*
			 * This is a convenience for non-multisite owners to process on demand. Its not provided
			 * in multisite because users  processing on demand could melt down the system.
			 */
			
			require_once(grfx_core_plugin.'includes/woocommerce/class-product-creator.php');
			
			/*
			 * Create the new product
			 */
			$product = new grfx_Product_Creator();
			$product->prepare_from_upload($info);
			$product->make_stock_image_basic($info, true);

			//cleanup
			unset($product);			
			
			$message = '<span class="grfx-success">' . __( 'Processed! (publish)', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );			
			
		} else {
		
			$this->enqueue($id);

			$message = '<span class="grfx-success">' . __( 'Enqueued (to draft)', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );

			sleep( 1 );
		}
		echo $return;
	}

	/**
	 * Processes an image and enqueues it to be published.
	 * @return string json encoded response.
	 */
	public function process_to_publish() {

		if ( empty( $_POST['grfx-upload-ids'] ) )
			return false;
		
		$id = $_POST['grfx-upload-ids'];

		$results['processed_id'] = $id;

		$info = $this->get_file_info( $id );
		
		if($info->extension != 'jpeg' && $info->extension != 'jpg'){
			sleep( 1 );
			$message = '<span class="grfx-success">' . __( 'This is a complementary file.', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );	
			echo $return;
			return 0;
		}
		
				

		if(!is_multisite() && isset($_POST['grfx-process-now']) && $_POST['grfx-process-now'] == 1){
			
			/*
			 * This is a convenience for non-multisite owners to process on demand. Its not provided
			 * in multisite because users  processing on demand could melt down the system.
			 */			
			
			require_once(grfx_core_plugin.'includes/woocommerce/class-product-creator.php');
			
			/*
			 * Create the new product
			 */
			$product = new grfx_Product_Creator();	
			$product->prepare_from_upload($info);
			$product->to_draft = 0;
			$product->make_stock_image_basic($info, true);

			//cleanup
			unset($product);			
			
			$message = '<span class="grfx-success">' . __( 'Processed! (publish)', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );			
			
		} else {
		
			$this->enqueue($id, 0);

			$message = '<span class="grfx-success">' . __( 'Enqueued (to publish)', 'grfx' ) . '</span>';

			$results['message'] = $message;

			$return = json_encode( $results );

			sleep( 1 );
		
		}
		echo $return;
	}

	/**
	 * Deletes selected items
	 * @global type $wpdb
	 * @param type $ids
	 */
	public function delete_selected( $ids, $user_id, $blog_id ) {
		global $wpdb;
        global $grfx_SITE_ID;
        
        $cleaned_ids = array();
        
        foreach($ids as $id){ 
   
            if(empty($id))
                continue;
            if(is_numeric($id))
                array_push($cleaned_ids, $id);            
        }                  
        
		$ids = implode( ', ', $cleaned_ids );
        
		$sql = "SELECT * FROM grfx_upload_tracking WHERE user_id = $user_id AND site_id = $grfx_SITE_ID AND upload_id IN ($ids)";
		        
		$uploads = $wpdb->get_results( $sql, OBJECT );
        
		foreach ( $uploads as $upload ) {

			$file = $upload->file_name;
			$db_id = $upload->upload_id;

			$sql = "DELETE FROM grfx_upload_tracking WHERE upload_id = $db_id;";


            
			$wpdb->query( $sql );

			if ( file_exists( grfx_protected_uploads_dir() . $file ) && !is_dir( grfx_protected_uploads_dir() . $file ) ) {
				unlink( grfx_protected_uploads_dir() . $file );
			}
		}
	}

	/**
	 * Deletes all images in a person's upload record.
	 * 
	 * @global type $wpdb
	 */
	public function delete_all() {

		global $wpdb;

		$ids = implode( ', ', array_filter( $ids ) );

		$sql = "SELECT * FROM grfx_upload_tracking WHERE user_id = $this->user_id AND site_id = $this->blog";

		$uploads = $wpdb->get_results( $sql, OBJECT );

		foreach ( $uploads as $upload ) {

			$file = $upload->file_name;
			$db_id = $upload->upload_id;

			$sql = "DELETE FROM grfx_upload_tracking WHERE upload_id = $db_id;";

			$wpdb->query( $sql );

			if ( file_exists( grfx_protected_uploads_dir() . $file ) && !is_dir( grfx_protected_uploads_dir() . $file ) ) {
				unlink( grfx_protected_uploads_dir() . $file );
			}
		}
	}

	/**
	 * Processes a given upload (by upload ID) to a product
	 * @return type
	 */
	public function process_to_product( $id ) {
		/*
		 * Get image processor 
		 */
		require_once(grfx_core_plugin() . 'admin/includes/image-processor/class-image-processor.php');

		$info = $this->get_file_info( $id );

		// Image processing
		$image = new grfx_Image_Processor();
		$image->filename = grfx_protected_uploads_dir() . $info->file_name;
		$image->make_standard_preview();

		return true;
	}

}
