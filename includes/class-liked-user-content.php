<?php

/**
* The file that defines the core plugin class
*
* A class definition that includes attributes and functions used across both the
* public-facing side of the site and the admin area.
*
* @link       https://github.com/kenross
* @since      1.0.0
*
* @package    Liked_User_Content
* @subpackage Liked_User_Content/includes
*/

/**
* The core plugin class.
*
* This is used to define internationalization, admin-specific hooks, and
* public-facing site hooks.
*
* Also maintains the unique identifier of this plugin as well as the current
* version of the plugin.
*
* @since      1.0.0
* @package    Liked_User_Content
* @subpackage Liked_User_Content/includes
* @author     Kenneth Ross <kenn.ross@gmail.com>
*/
class Liked_User_Content {

   /**
	* The loader that's responsible for maintaining and registering all hooks that power
	* the plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      Liked_User_Content_Loader    $loader    Maintains and registers all hooks for the plugin.
	*/
   protected $loader;

   /**
	* The unique identifier of this plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      string    $plugin_name    The string used to uniquely identify this plugin.
	*/
   protected $plugin_name;

   /**
	* The current version of the plugin.
	*
	* @since    1.0.0
	* @access   protected
	* @var      string    $version    The current version of the plugin.
	*/
   protected $version;

   /**
	* Define the core functionality of the plugin.
	*
	* Set the plugin name and the plugin version that can be used throughout the plugin.
	* Load the dependencies, define the locale, and set the hooks for the admin area and
	* the public-facing side of the site.
	*
	* @since    1.0.0
	*/
   public function __construct() {

	   spl_autoload_register(array(__CLASS__, 'autoload'));
	   $this->plugin_name = 'liked-user-content';
	   $this->version = '1.0.0';

	   $this->load_dependencies();
	   $this->define_admin_hooks();
	   $this->define_public_hooks();

   }

   /**
	* Load the required dependencies for this plugin.
	*
	* Include the following files that make up the plugin:
	*
	* - Liked_User_Content_Loader. Orchestrates the hooks of the plugin.
	* - Liked_User_Content_Admin. Defines all hooks for the admin area.
	* - Liked_User_Content_Public. Defines all hooks for the public side of the site.
	*
	* Create an instance of the loader which will be used to register the hooks
	* with WordPress.
	*
	* @since    1.0.0
	* @access   private
	*/
   private function load_dependencies() {

	   /**
		* The class responsible for orchestrating the actions and filters of the
		* core plugin.
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-liked-user-content-loader.php';

	   /**
		* The class responsible for defining all actions that occur in the admin area.
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-liked-user-content-admin.php';

	   /**
		* The class responsible for defining all actions that occur in the public-facing
		* side of the site.
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-liked-user-content-public.php';

	   $this->loader = new Liked_User_Content_Loader();

   }

   /**
	* Register all of the hooks related to the admin area functionality
	* of the plugin.
	*
	* @since    1.0.0
	* @access   private
	*/
   private function define_admin_hooks() {

	   $plugin_admin = new Liked_User_Content_Admin( $this->get_plugin_name(), $this->get_version() );

	   $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	   $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	   $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'localize_scripts' );
	   $this->loader->add_action( 'init', $plugin_admin, 'register_bucket_post_type' );
	   $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_luc_settings_menu' );
	   $this->loader->add_action( 'delete_post', $plugin_admin, 'bucket_sync', 10, 1 );
	   $this->loader->add_action( 'delete_user', $plugin_admin, 'delete_user_sync', 10, 1 );
	   $this->loader->add_action( 'delete_attachment', $plugin_admin, 'delete_attachment_sync', 10, 1 );
	   $this->loader->add_action( 'user_register', $plugin_admin, 'setup_new_user_for_plugin', 10, 1 );

	   // AJAX
	   $this->loader->add_action( 'wp_ajax_create_buckets_for_all_users', $plugin_admin, 'create_buckets_for_all_users' );
	   $this->loader->add_action( 'wp_ajax_save_settings', $plugin_admin, 'save_settings' );

   }

   /**
	* Register all of the hooks related to the public-facing functionality
	* of the plugin.
	*
	* @since    1.0.0
	* @access   private
	*/
   private function define_public_hooks() {

	   $plugin_public = new Liked_User_Content_Public( $this->get_plugin_name(), $this->get_version() );

	   $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
	   $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	   $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'localize_scripts' );

	   // AJAX
	   $this->loader->add_action( 'wp_ajax_get_toggle_state_of_buttons', $plugin_public, 'get_toggle_state_of_buttons' );
	   $this->loader->add_action( 'wp_ajax_add_remove_bucket_attachment', $plugin_public, 'add_remove_bucket_attachment' );
	   /* For non-logged in users so the "like" buttons will display as "disabled". */
	   $this->loader->add_action( 'wp_ajax_nopriv_get_toggle_state_of_buttons', $plugin_public, 'get_toggle_state_of_buttons' );

   }

   /**
	* Run the loader to execute all of the hooks with WordPress.
	*
	* @since    1.0.0
	*/
   public function run() {
	   $this->loader->run();
   }

   /**
	* The name of the plugin used to uniquely identify it within the context of
	* WordPress and to define internationalization functionality.
	*
	* @since     1.0.0
	* @return    string    The name of the plugin.
	*/
   public function get_plugin_name() {
	   return $this->plugin_name;
   }

   /**
	* The reference to the class that orchestrates the hooks with the plugin.
	*
	* @since     1.0.0
	* @return    Liked_User_Content_Loader    Orchestrates the hooks of the plugin.
	*/
   public function get_loader() {
	   return $this->loader;
   }

   /**
	* Retrieve the version number of the plugin.
	*
	* @since     1.0.0
	* @return    string    The version number of the plugin.
	*/
   public function get_version() {
	   return $this->version;
   }

   /**
	* Callback to handle autoloading classes.
	*
	* @since     1.0.0
	*/
   public static function autoload($class) {
	   if(strpos($class, 'Luc_') !== 0) {
		   return;
	   }
	   $class = strtolower($class);
	   $class = str_replace('_', '-', $class);
	   $file = 'class-' . $class . '.php';
	   include $file;
   }

}