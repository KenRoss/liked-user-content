<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Liked_User_Content_Activator {

	/**
	 * Activate the plugin and do initial setup like create database tables.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$fs = new Luc_Filesystem();
		if(!$fs->setup_media_dir()) {
			error_log('The luc_media directory could not be created.');
			return;
		}
		$db_init = array();
		$db_init[0] = new Luc_User_Bucket_Coordinator();
		$db_init[1] = new Luc_Attachment_Source_To_Copy_Lookup();

		for($i = 0; $i < count($db_init); $i++) {
			if(!$db_init[$i]->datastore_exists()) {
				$db_init[$i]->create_datastore();
			}
		}

		/* Plugin default settings */
		if(!get_option('luc_plugin_settings')) {
			$options = array(
				'create_buckets_for_new_users' => 0,
				'disable_love_functionality' => 0,
				'title_format_string' => "%user%'s %bucketid%d media",
				'limit_likes' => 0
			);
			add_option('luc_plugin_settings', $options);
		}
	}

}