<?php

/**
 * Used by the plugin to create a link between WordPress post attachments and
 * their copie(s) added to bucket pages (a custom post type).
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 */

/**
 * Interfaces the luc_source_attachment_to_bucket_copy table.
 *
 * Class handles mapping an attachment to its copies made by the plugin and
 * added to bucket pages.
 *
 * @since      1.0.0
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_Attachment_Source_To_Copy_Lookup {

	const TABLE = 'luc_source_attachment_to_bucket_copy';

	function __construct() {
	}

	/**
	 * Checks if the datastore already exists.
	 *
	 * @since    1.0.0
	 */
	public function datastore_exists() {
		global $wpdb;
		if($wpdb->get_var("SHOW TABLES LIKE '" . self::TABLE . "'") == self::TABLE) {
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Creates the plugin's datastore.
	 *
	 * @since    1.0.0
	 */
	public function create_datastore() {
		$sql = "CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
			   `source_att_id` bigint(20) unsigned NOT NULL,
			   `bucket_copy_att_id` bigint(20) unsigned NOT NULL,
			   PRIMARY KEY  (`bucket_copy_att_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql, true);
	}

	/**
	 * Return the ID of the bucket attachment that is a copy of $attachment_id
	 * for $user_id.
	 *
	 * @since    1.0.0
	 *
	 * @param int		$attachment_id			ID of the "original" attachment.
	 * @param int		$user_id				Wordpress User ID.
	 * @param string	$bucket_page_post_id	Post ID of the bucket page that
	 *											contains the cloned attachment.
	 * @return int		Returns the ID of the matching bucket page attachment or
	 *					0 if none are found.
	 *
	 */
	public function get_bucket_copy_from_source($attachment_id, $user_id, $bucket_page_post_id) {
		global $wpdb;
		$sql = $wpdb->prepare(
		   "SELECT
			  bucket_copy_att_id
			FROM " . self::TABLE . "
			INNER JOIN wp_posts AS posts
			  ON " . self::TABLE . ".bucket_copy_att_id = posts.ID
			INNER JOIN wp_posts AS bucketposts
			  ON posts.post_parent = bucketposts.ID
			WHERE " . self::TABLE . ".source_att_id = %d
			AND bucketposts.post_author = %d
			AND bucketposts.ID = %d", $attachment_id, $user_id, $bucket_page_post_id
		);
		$recs = $wpdb->get_results($sql, OBJECT);
		if(!empty($recs)) {
			return $recs[0]->bucket_copy_att_id;
		} else {
			return 0;
		}

	}

	/**
	 * Fetches the ID of an attachment by the ID of its bucket copy.
	 *
	 * @since    1.0.0
	 *
	 * @param	int		$att_id		Attachment ID;
	 * @return	int					The matching attachment ID or 0 is there is
	 *								none.
	 */
	public function get_source_from_bucket_copy($att_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT source_att_id FROM ' . self::TABLE . ' WHERE bucket_copy_att_id = %d LIMIT 1', $att_id);
		$recs = $wpdb->get_results($sql, ARRAY_N);
		if(empty($recs)) {
			return 0;
		}
		return $recs[0][0];
	}

	/**
	 * Inserts a lookup record into the datastore that will map a source
	 * attachment to a bucket page copy.
	 *
	 * @param string    $source_att_id
	 * @param int    	$copy_att_id
	 *
	 * @return int|bool	The number of rows affected or false if the row could
	 *					not be inserted.
	 * @since	1.0.0
	 */
	public function insert($source_att_id, $copy_att_id) {
		global $wpdb;
		$result = $wpdb->insert(
			self::TABLE,
			array(
				'source_att_id' => $source_att_id,
				'bucket_copy_att_id' => $copy_att_id,
			),
			array(
				'%d',
				'%d',
			)
		);
		return $result;
	}

	/**
	 * Retrieves and returns a list of IDs for attachments that are copies of
	 * the non-bucket page attachment matching $att_id.
	 *
	 * @since    1.0.0
	 *
	 * @param	int		$att_id		Attachment ID;
	 * @return	array				Returns a list of IDs.
	 */
	public function get_ids_of_bucket_copies($att_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT bucket_copy_att_id FROM ' . self::TABLE . ' WHERE source_att_id = %d', $att_id);
		$recs = $wpdb->get_results($sql, ARRAY_N);
		$ids = array();
		foreach($recs as $rec) {
			$ids[] = $rec[0];
		}
		return $ids;
	}

	/**
	 * Deletes all records that match the bucket copy attachment ID.
	 *
	 * @since    1.0.0
	 *
	 * @param	int			$att_id	Attachment ID;
	 * @return	int|bool	Returns the # of affected rows or false on error.
	 */
	public function delete_by_bucket_copy_att_id($att_id) {
		global $wpdb;
		$result = $wpdb->delete( self::TABLE, array( 'bucket_copy_att_id' => $att_id ), array( '%d' ) );
		return $result;
	}

	/**
	 * Deletes all records that match the source attachment ID.
	 *
	 * @since    1.0.0
	 *
	 * @param	int			$att_id	Attachment ID;
	 * @return	int|bool	Returns the # of affected rows or false on error.
	 */
	public function delete_by_source_att_id($att_id) {
		global $wpdb;
		$result = $wpdb->delete( self::TABLE, array( 'source_att_id' => $att_id ), array( '%d' ) );
		return $result;
	}

}