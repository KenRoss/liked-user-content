<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/admin
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Liked_User_Content_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/** PRIVATE METHODS *******************************************************/

	/**
	 * Finds a record in the array $bucket_recs that matches $user_id and $bucket_id.
	 *
	 * @param int 		$user_id		WP user ID.
	 * @param string    $bucket_id		String representing the type of bucket.
	 * @param array		$bucket_recs	Array of objects representing bucket
	 *									records.
	 *
	 * @return	bool	True if a match is found, false otherwise.
	 * @since	1.0.0
	 */
	private function find_bucket($user_id, $bucket_id, $bucket_recs) {
		foreach($bucket_recs as $rec) {
			if($rec->user_id == $user_id && $rec->bucket_id == $bucket_id) {
				return true;
			}
		}
		return false;
 	}

	/**
	 * Receives a post title template string that may have placeholder values
	 * that will be interpolated with values and returned to the caller.
	 *
	 * @param	string	$title_format_string	Post title template.
	 * @param	int		$user_id				User ID
	 * @param	string	$bucket_id				Bucket ID
	 *
	 * @return	string	Interpolated title string.
	 * @since	1.0.0
	 */
	private function interpolate_title_format_string($title_format_string, $user_id, $bucket_id) {
		// Convert the bucket ID into a human-friendly name.
		$ubc = new Luc_User_Bucket_Coordinator();
		$bucket_types =  $ubc->get_user_bucket_types();
		$bucket_type_nice_names = array(
			$bucket_types[0] => 'like',
			$bucket_types[1] => 'love'
		);
		// Get the user's name.
		$user = get_user_by('ID', $user_id);
		$username = $user->display_name;
		// Interpolate it.
		$placeholders = array(
			'%user%'     => $username,
			'%bucketid%' => $bucket_type_nice_names[$bucket_id]
		);
		$post_title = $title_format_string;
		foreach($placeholders as $find => $replace) {
			$post_title = str_replace($find, $replace, $post_title);
		}
		return $post_title;
	}

	/**
	 * Creates a 'bucket' post.
	 *
	 * @param int 		$user_id		WP user ID.
	 * @param string    $bucket_id		String representing the type of bucket.
	 *
	 * @return int		The post ID on success. 0 on failure.
	 * @since	1.0.0
	 */
	private function create_bucket_post($user_id, $bucket_id) {
		$ubc = new Luc_User_Bucket_Coordinator();
		if(!is_int($user_id) || $user_id < 1) {
			return 0;
		} elseif(!in_array($bucket_id, $ubc->get_user_bucket_types())) {
			return 0;
		}
		$udata = get_userdata($user_id);
		$user_name = $udata->user_login;
		$plugin_settings = get_option('luc_plugin_settings');
		$title_format_string = $plugin_settings['title_format_string'];
		if($title_format_string) {
			$post_title = $this->interpolate_title_format_string($title_format_string, $user_id, $bucket_id);
		} else {
			$post_title = "$user_name's $bucket_id";
		}
		$args = array(
			'post_author' => $udata->ID,
			'post_type' => 'bucket',
			'post_title' => $post_title,
			'post_status' => 'publish'
		);
		$post_id = wp_insert_post($args, false);
		$ubc->insert($user_id, $bucket_id, $post_id);
		return $post_id;
	}

	/**
	 * Delete all bucket pages belonging to the deleted user.
	 *
	 * @param	int 	$user_id		User ID of the deleted user.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	private function delete_bucket_pages_for_user($user_id) {
		$args = array(
			"author" => $user_id,
			"post_type" => "bucket"
		);
		$posts = get_posts($args);
		foreach($posts as $post) {
			wp_delete_post($post->ID, true);
		}
	}

	/**
	 * Takes the current setting of a checkbox and the previous setting and
	 * returns true if the checkbox changed.
	 *
	 * @return	bool	Returns true if the checkbox setting was changed,
	 *                  false otherwise.
	 * @since	1.0.0
	 */
	private function checkbox_has_changed($new_setting, $old_setting) {
		if($new_setting != $old_setting) {
			if($new_setting == 0 ||
			   $new_setting == 1) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Process the settings submitted from the LUC Settings page when the user
	 * saves the settings.
	 *
	 * @return	bool	Returns true if any settings are changed, false otherwise.
	 * @since	1.0.0
	 */
	private function process_save_settings($settings) {
		$settings_updated = false;
		$create_buckets_for_new_users = absint(intval($settings['create_buckets_for_new_users']));
		$disable_love_functionality = absint(intval($settings['disable_love_functionality']));
		$limit_likes = absint(intval($settings['limit_likes']));
		$title_format_string = stripslashes($settings['title_format_string']);
		$luc_plugin_settings = get_option('luc_plugin_settings');

		$changed = $this->checkbox_has_changed($create_buckets_for_new_users, $luc_plugin_settings['create_buckets_for_new_users']);
		if($changed) {
			$luc_plugin_settings['create_buckets_for_new_users'] = $create_buckets_for_new_users;
			$settings_updated = true;
		}
		$changed = $this->checkbox_has_changed($disable_love_functionality, $luc_plugin_settings['disable_love_functionality']);
		if($changed) {
			$luc_plugin_settings['disable_love_functionality'] = $disable_love_functionality;
			$settings_updated = true;
		}
		$changed = $limit_likes != $luc_plugin_settings['limit_likes'] ? true : false;
		if($changed) {
			$luc_plugin_settings['limit_likes'] = $limit_likes;
			$settings_updated = true;
		}
		$changed = $title_format_string != $luc_plugin_settings['title_format_string'] ? true : false;
		if($changed) {
			$luc_plugin_settings['title_format_string'] = $title_format_string;
			$settings_updated = true;
		}

		if(!$settings_updated) {
			return false;
		}
		update_option('luc_plugin_settings', $luc_plugin_settings);
		return true;
	}

	/** PUBLIC METHODS ********************************************************/

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Liked_User_Content_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Liked_User_Content_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/liked-user-content-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Liked_User_Content_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Liked_User_Content_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/liked-user-content-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Makes the URL of the WordPress ajax script available to the caller (to
	 * use in JavaScript).
	 *
	 * @since    1.0.0
	 */
	public function localize_scripts() {

		wp_localize_script( $this->plugin_name, 'ajax_o', array('ajax_url' => admin_url('admin-ajax.php')));

	}

	/**
	 * Register the bucket post type.
	 *
	 * @since    1.0.0
	 */
	public function register_bucket_post_type() {
		$args = array(
			'public' => true,
			'label' => 'Buckets',
		);
		register_post_type('Bucket', $args);
	}

	/**
	 * Dsplay LUC settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_luc_settings_page() {
		include_once('partials/liked-user-content-management-display.php');
	}

	/**
	 * Add the LUC settings menu to the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_luc_settings_menu() {
		 add_submenu_page('edit.php?post_type=bucket', 'Liked User Content Settings', 'Manage Settings', 'manage_options', $this->plugin_name, array($this, 'display_luc_settings_page'));
	}

	/**
	 * Delete the bucket record associated with the bucket post that has been
	 * deleted. This function will delete all records that match $post_id.
	 *
	 * @param	int 	$user_id		Post ID of the deleted bucket post.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	public function bucket_sync($post_id) {
		$ubc = new Luc_User_Bucket_Coordinator();
		if('bucket' != get_post_type($post_id)) {
			return;
		}
		$ubc->delete_user_bucket_by_post_id($post_id);
	}

	/**
	 * Do any clean-up in Wordpress and in the plugin for deleted users.
	 *
	 * @param	int 	$user_id		User ID of the user.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	public function delete_user_sync($user_id) {
		$this->delete_bucket_pages_for_user($user_id);
	}

	/**
	 * Fired when an attachment is deleted.
	 *
	 * @param	int 	$att_id		Post ID of the deleted attachment.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	public function delete_attachment_sync($att_id) {
		$attachment = get_post($att_id, OBJECT);
		$parent = get_post($attachment->post_parent, OBJECT);
		$lookup = new Luc_Attachment_Source_To_Copy_Lookup();
		if($parent->post_type == 'bucket') {
			$lookup->delete_by_bucket_copy_att_id($att_id);
		} else {
			$bucket_copies = $lookup->get_ids_of_bucket_copies($att_id);
			foreach($bucket_copies as $att_id) {
				wp_delete_post($att_id, true);
			}
			$lookup->delete_by_source_att_id($att_id);
		}
 	}

	/**
	 * Checks to see if the nonce has been set and kills WordPress execution
	 * if it is not.
	 *
	 * @param	int 	$nonce		WordPress nonce to validate.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	public function check_nonce($nonce) {
		if(!wp_verify_nonce($nonce, 'luc_action_handler')) {
			wp_die('Nonce verification failed.');
		}
	}

	/**
	 * Create a pair of bucket pages for each WP user.
	 *
	 * @since    1.0.0
	 */
	public function create_buckets_for_all_users() {
		if(isset($_POST['_lucnonce'])) {
			$this->check_nonce($_POST['_lucnonce']);
		} else {
			wp_die('Missing nonce.');
		}
		$ubc = new Luc_User_Bucket_Coordinator();
		$user_count = 0;
		$page_count = 0;
		$wp_users = get_users();
		$recs = $ubc->get_all();
		foreach($wp_users as $user) {
			foreach($ubc->get_user_bucket_types() as $bt) {
				$result = $this->find_bucket($user->ID, $bt, $recs);
				if($result == false) {
					$post_id = $this->create_bucket_post($user->ID, $bt);
					if($post_id) {
						$ubc->insert($user->ID, $bt, $post_id);
					}
					$page_count++;
				}
			}
			$user_count++;
		}
		$response = array(
			'msg' => $page_count . ' bucket pages were created for ' . $user_count . ' users.'
		);
		echo (json_encode($response));
		wp_die();
 	}

	/**
	 * Setup a new WP user for plugin-specific features.
	 *
	 * @param	int 	$user_id		User ID of the new user.
	 *
	 * @return	void
	 * @since	1.0.0
	 */
	public function setup_new_user_for_plugin($user_id) {
		$settings = get_option('luc_plugin_settings');
		$create_buckets_for_new_users = $settings['create_buckets_for_new_users'];

		if($create_buckets_for_new_users == 1) {
			$ubc = new Luc_User_Bucket_Coordinator();
			$bucket_types = $ubc->get_user_bucket_types();
			$this->create_bucket_post($user_id, $bucket_types[0]);
			$this->create_bucket_post($user_id, $bucket_types[1]);
		}
	}

	/**
	 * Save the settings modified by the user on the Manage Settings page.
	 *
	 * @since	1.0.0
	 */
	public function save_settings() {
		if(isset($_POST['_lucnonce'])) {
			$this->check_nonce($_POST['_lucnonce']);
		} else {
			wp_die('Missing nonce.');
		}
		$response = array();
		unset($_POST['_lucnonce']);
		$settings = $_POST;
		$validator = new Luc_Settings_Validator();
		try {
			$success = $validator->validate_save_settings($settings);
		} catch(Exception $e) {
			$response['msg'] = $e->getMessage();
			$success = false;
		}
		if($success) {
			if($this->process_save_settings($settings)) {
				$response['msg'] = 'Settings saved.';
			} else {
				$response['msg'] = 'No changes made.';
			}
		}
		echo (json_encode($response));
		wp_die();
	}

}
