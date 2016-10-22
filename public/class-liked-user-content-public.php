<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/public
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Liked_User_Content_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/** PRIVATE METHODS *******************************************************/

	/**
	 * Retrieve and return the toggle state of the button for the user.
	 *
	 * @since    1.0.0
	 */
	private function get_toggle_state($att_id, $bucket_id, $user_id) {
		// If a bucket page for storing attachments does not exist for this user
		// return 'disable' so the LIKE/LOVE buttons can be deactivated.
		try {
			$bucket_page = new Luc_Bucket_Page($user_id, $bucket_id);
		} catch(Exception $e) {
			error_log("Error creating Luc_Bucket_Page object. Does the user's bucket page exist?");
			error_log($e->getMessage());
			return 'disable';
		}
		if($bucket_page->has_copy_of_attachment($att_id)) {
			$ubc = new Luc_User_Bucket_Coordinator();
			$bucket_types = $ubc->get_user_bucket_types();
			// If the next line evals to true, we are determining the toggle
			// state of a LIKE button. Otherwise, a LOVE button...
			if($bucket_id == $bucket_types[0]) {
				try {
					$love_bucket_page = new Luc_Bucket_Page($user_id, $bucket_types[1]);
				} catch(Exception $e) {
					$msg = "Error creating a Luc_Bucket_Page object for a LOVE "
						 . "button when trying to determine the button toggle state for "
						 . "a LIKE button.";
					error_log($msg);
					error_log($e->getMessage());
					return '';
				}
				if($love_bucket_page->has_copy_of_attachment($att_id)) {
					return 'static';
				} else {
					return 'active';
				}
			} else if($bucket_id == $bucket_types[1]) {
				return 'active';
			}
		} else {
			return 'static';
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/liked-user-content-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/liked-user-content-public.js', array( 'jquery' ), $this->version, false );

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
	 * Adds or removes an attachment to a bucket page. The function's logic
	 * mimics a toggle: If the attachment exists in the bucket page already, the
	 * caller wants it removed. If the attachment does not exist in the bucket
	 * page the caller wants it added.
	 *
	 * @since    1.0.0
	 */
	public function add_remove_bucket_attachment() {
		if(!isset($_POST['att_data'])) {
			return;
		}
		$att_data = json_decode(stripslashes($_POST['att_data']));
		$user_id = get_current_user_id();
		if(!$user_id) {
			return;
		}
		$bucket_id = $att_data->bucket;
		$att_id = $att_data->attid;
		$ubc = new Luc_User_Bucket_Coordinator();
		$bucket_types =  $ubc->get_user_bucket_types();
		$bucket_a = new Luc_Bucket_Page($user_id, $bucket_types[0]);
		$bucket_b = new Luc_Bucket_Page($user_id, $bucket_types[1]);
		$bucket_mgr = new Luc_User_Bucket_Page_Manager($bucket_a, $bucket_b);
		$plugin_settings = get_option('luc_plugin_settings');
		$max_allowed_likes = $plugin_settings['limit_likes'];
		if($max_allowed_likes > 0) {
			$bucket_mgr->set_max_allowed_likes($max_allowed_likes);
		}
		if($bucket_id == $bucket_types[0]) {
			$response = $bucket_mgr->toggle_like($att_id);
		} elseif($bucket_id == $bucket_types[1]) {
			if(!$plugin_settings['disable_love_functionality']) {
				$response = $bucket_mgr->toggle_love($att_id);
			}
		}
		$json = json_encode($response);
		echo $json;
		wp_die();
	}

	/**
	 * Determine the toggle state of the buttons POSTed to the server and return
	 * the state information back to the front end as a JSON string.
	 *
	 * @since    1.0.0
	 */
	public function get_toggle_state_of_buttons() {
		if(!isset($_POST['luc_buttons'])) {
			return;
		}
		// WordPress' wp_magic_quotes adds slashes whether we like it or not...
		$luc_buttons = stripslashes($_POST['luc_buttons']);
		$luc_buttons = json_decode($luc_buttons);
		$user_id = get_current_user_id();
		$plugin_settings = get_option('luc_plugin_settings');
		$ubc = new Luc_User_Bucket_Coordinator();
		$bucket_ids = $ubc->get_user_bucket_types();
		for($i = 0; $i < count($luc_buttons); $i++ ) {
			$att_id = intval($luc_buttons[$i]->attid);
			// $bucket_id is not a class name but this function does the trick.
			$bucket_id = sanitize_html_class($luc_buttons[$i]->bucket);
			if($bucket_id == $bucket_ids[1] && $plugin_settings['disable_love_functionality']) {
				$toggle_state = 'disable';
			} else if(!is_user_logged_in()) {
				$toggle_state = 'disable';
			} else {
				$toggle_state = $this->get_toggle_state($att_id, $bucket_id, $user_id);
			}
			if(!empty($toggle_state)) {
				$luc_buttons[$i]->toggle_state = $toggle_state;
			}
		}
		echo json_encode($luc_buttons);
		wp_die();
	}

	/**
	 * Returns an HTML <IMG> tag for the IDed attachment image along with the
	 * LUC LIKE/LOVE UI markup.
	 *
	 * This method is not intended for production use. It was created for
	 * testing only. Please use the functions in functions.php located in the 
	 * 'public' directory.
	 *
	 * @param int 		$attachment_id	ID of the image attachment.
	 * @param string    $size			Size of the image to return.
	 * @param bool		$icon			Whether or not to treat the image as an icon.
	 * @param array		$attr			Attributes for the image markup.
	 *
	 * @return	string	Return the HTML markup for the image and the LIKE/LOVE button UI.
	 * @since	1.0.0
	 */
	public static function luc_get_attachment_image($attachment_id, $size = 'full', $icon = false, $attr = '') {
		$image_tag = wp_get_attachment_image($attachment_id, $size, $icon, $attr);
		if(empty($image_tag)) {
			return;
		}
		// Determine if $attachment_id identifies a bucket page attachment. If
		// it does change it to be the ID of the source attachment so the plugin
		// is not trying to add bucket attachments into bucket posts.
		$lookup = new Luc_Attachment_Source_To_Copy_Lookup();
		$source_att_id = $lookup->get_source_from_bucket_copy($attachment_id);
		if($source_att_id != 0) {
			$attachment_id = $source_att_id;
		}
		$plugin_settings = get_option('luc_plugin_settings');
		$markup = $image_tag;
		if(is_user_logged_in()) {
			ob_start();
			include('partials/liked-user-content-likelove-controls.php');
			$markup .= ob_get_clean();
		}
		return $markup;
 	}

}