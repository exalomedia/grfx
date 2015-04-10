<?php

/**
 * grfx Cron Job manager class.
 * 
 * Wordpress's cron jobs can be dangerous, especially when doing operations that consume 
 * lots of resources. This is a genuine cron system that is more efficient.
 * 
 * @package    grfx
 * @subpackage grfx_Cron
 * @author     Leo Blanchette <leo@grfx.com>
 * @copyright  2012-2015 grfx
 * @license    http://www.gnu.org/licenses/gpl.html
 * @link       http://www.grfx.com
 */
class grfx_Cron {

	/**
	 * Current cron ID in process
	 * @var int ID of current cron job in database
	 */
	public $current_cron_id = 0;

	/**
	 * Server script time limit. Allows us to know how long we can run before shutting down.
	 * @var int Server script time limit in seconds 
	 */
	public $server_time_limit = 0;

	/**
	 * Max time we allow the script (1 hour intervals)
	 * @var int allowed time for any process to run
	 */
	public $running_time_limit = 3600;

	/**
	 * The time this script started (objective is to terminate before reaching time limit)
	 * @var int the time this script started
	 */
	public $start_time = 0;

	/**
	 *  Directory containing uploads
	 * @var string uploads dir 
	 */
	public $uploads_protected_dir = '';

	/**
	 * A list of uploaded files.
	 * @var array list of uploaded files
	 */
	public $uploads = array();

	/**
	 * Total files processed in this cron job
	 * @var int total files processed in this run
	 */
	public $files_processed = 0;

	/**
	 * Total megabytes processed in this run
	 * @var int total (in megabytes) worth of files processed
	 */
	public $megabytes_processed = 0;

	public function __construct() {

		//Uncomment for testing
		error_reporting( E_ALL );

		//discover time limit on server
		$this->server_time_limit = ini_get( 'max_execution_time' );
		//log start time to avoid running over server script time limits
		$this->start_time = time();

		//verify the call is authentic
		if ( !$this->checkpass() )
			return false;

		$this->uploads_protected_dir = WP_CONTENT_DIR . '/uploads/grfx_uploads/protected/';

		//check for uploads (before database interaction, for efficiency)
		if ( !$this->check_uploads() )
			return false;

		if ( $this->is_cron_locked() )
			return false;

		$this->cron_log();

		//lock cron for this session
		$this->cron_lock( 1 );

		/*
		 * BEGIN PROCESSING SESSION
		 */

		$this->process_upload_queue();

		$this->cron_lock( 0 );

		$this->cleanup();
	}

	/**
	 *  This is the main queue processing method. 
	 * @global type $switched
	 */
	public function process_upload_queue() {

		require_once(dirname( __FILE__ ) . '/../admin/includes/uploader/class-upload-tracker.php');
		require_once('woocommerce/class-product-creator.php');

		$upload_tracker = new grfx_Upload_Tracker();

		$uploads = $upload_tracker->get_uploads( 150 );

		if ( $uploads ) {
			foreach ( $uploads as $upload_info ) {
								
				if($this->reached_time_limit()){			
					return false;
				}

				if(exif_imagetype( grfx_protected_uploads_dir() . $upload_info->file_name ) != IMAGETYPE_JPEG ){
					continue;
				}

				if(!$upload_info->enqueued)
					continue;
			
				//uncomment for testing -- simulates timely operations	
				//sleep(2);
				/*
				 * If multisite, we ensure that we are switching blogs accordingly so that functions
				 * are applied in context of upload's native site.
				 */
				if ( is_multisite() ) {
					global $switched;
					if ( get_current_blog_id() != $upload_info->site_id ) {
						switch_to_blog( $upload_info->site_id );
					}
				}
				
				/*
				 * Create the new product
				 */
				$product = new grfx_Product_Creator();
				
				$product->prepare_from_upload($upload_info);				
				
				$product->make_stock_image_basic($upload_info);
				
				//cleanup
				unset($product);
			}
		}
	}

	/**
	 * Checks to see if script is reaching time limit.
	 * @return boolean true if time limit reached, false if not.
	 */
	public function reached_time_limit() {
		$elapsed_time = time() - $this->start_time;
		
		//echo $elapsed_time.'<br />';
		
		/*
		 * NOTE: Minusing 10 seconds from the time limits ensures that a process (such as an image
		 * resize) does not run over time. Its a time safety strategy.
		 */
		
		if ( $elapsed_time > $this->server_time_limit - 10 )
			return true;

		if ( $elapsed_time > $this->running_time_limit - 10 )
			return true;
		
		return false;
	}

	/**
	 * Checks the password in the cron pass to make sure its authentic. This helps stops
	 * any exploitive activity where someone loads the cron php file without authorization
	 * 
	 * @return boolean true if pass, false if not
	 */
	function checkpass() {

		if ( empty( $_GET['grfx_cronpass'] ) )
			return false;

		if ( $_GET['grfx_cronpass'] == grfx_get_sitepass() ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Checks whether or not there are user uploads ready to process. If they exist, it populates 
	 * the $this->uploads array.
	 * 
	 * @return boolean false if empty, true if not.
	 */
	function check_uploads() {

		$uploads = array();

		$files = scandir( $this->uploads_protected_dir );

		if ( $files ) {
			foreach ( $files as $file ) {
				if ( $file == '.' || $file == '..' || $file == '.htaccess' || $file == '.ftpquota' )
					continue;
				array_push( $uploads, $file );
			}
		}

		if ( empty( $uploads ) )
			return false;

		$this->uploads = $uploads;

		return true;
	}

	/**
	 *  Sets up a new cron job record which will be referenced throughout job.
	 * @global type $wpdb
	 */
	public function cron_log() {

		global $wpdb;

		$wpdb->insert(
				'grfx_cron_log', array(
			'cron_id' => 'null'
				), array(
			'%s'
				)
		);

		$current_cron_id = $wpdb->insert_id;
		$this->current_cron_id = $current_cron_id;
	}

	/**
	 *  A lock to ensure that no double-cron-processing occurs. 
	 * @global type $wpdb
	 * @param bool $locked 1 to lock, 0 to release. Ensures no double-processing occurs.
	 */
	public function cron_lock( $locked ) {
		global $wpdb;

		$sql = "UPDATE grfx_cron_log SET locked = " . $locked . " WHERE cron_id = " . $this->current_cron_id . ";";

		$wpdb->query( $sql );
		return true;
	}

	/**
	 * Checks if the cron system is in use (locked). 
	 * 
	 * @global type $wpdb
	 * @return boolean true of locked, false if not.
	 */
	public function is_cron_locked() {
		global $wpdb;

		$sql = "SELECT * FROM grfx_cron_log ORDER BY cron_id DESC LIMIT 0, 1";
		$last_cron = $wpdb->get_row( $sql );

		//get current time in mysql specifically to weigh against age of logged cron job
		$current_mysql_time = $wpdb->get_var( "SELECT NOW()" );

		$locked = $last_cron->locked;
		$last_cron_time = strtotime( $last_cron->time );

		//determine age of last cron (in seconds)
		$age = strtotime( $current_mysql_time ) - $last_cron_time;

		/*
		 * Over one hour and still locked simply means that something crashed and left process
		 * without unlocking itself. This simply reverses the state and allows process to continue 
		 * in that case. We also check against server time limit (be it over an hour or less) so that 
		 * users don't have to wait a full access before the cron attempts to go again.
		 */
		if ( $age > $this->running_time_limit && $locked == 1 || $age > $this->server_time_limit && $locked == 1  ) {
			$sql = "UPDATE grfx_cron_log SET locked = 0 WHERE cron_id = " . $last_cron->cron_id . ";";
			$wpdb->query( $sql );
			return true;
		}

		if ( $locked == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public function cleanup() {
		global $wpdb;
		$sql = "DELETE FROM grfx_cron_log WHERE time < now() - interval 1 day";
		$wpdb->query( $sql );
	}

}
