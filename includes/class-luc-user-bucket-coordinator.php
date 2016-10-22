<?php

/**
 * This class facilitates creating objects that manage the relations between WP
 * users and their bucket pages.
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 */

/**
 * Interfaces the luc_user_buckets table.
 *
 * Class handles associating WP users to their bucket pages.
 *
 * @since      1.0.0
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_User_Bucket_Coordinator {

	const TABLE = 'luc_user_buckets';
	private $bucket_types = array('bucketA', 'bucketB');

	function __construct() {
	}

	/**
	 * Checks if the table already exists.
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
	 * Creates the table.
	 *
	 * @since    1.0.0
	 */
	public function create_datastore() {
		$sql = "CREATE TABLE IF NOT EXISTS " . self::TABLE . " (
			   user_id bigint(20) unsigned NOT NULL,
			   post_id bigint(20) unsigned NOT NULL,
			   bucket_id varchar(20) NOT NULL,
			   PRIMARY KEY  (user_id, bucket_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql, true);
	}

	/**
	 * Returns a list of records matching the WP user id.
	 *
	 * @since    1.0.0
	 *
	 * @param int			$id		Wordpress User ID
	 * @return array|null	Returns matching records. Returns null if none are
	 * 						found.
	 *
	 */
	public function get_user_buckets_by_user_id($user_id) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM ' . self::TABLE . ' WHERE user_id = %d', $user_id);
		$recs = $wpdb->get_results($sql, OBJECT);
		return $recs;
	}

	/**
	 * Returns all the bucket records.
	 *
	 * @since    1.0.0
	 *
	 * @return array|null	Returns matching records. Returns an empty array
	 *						if none are found.
	 *
	 */
	public function get_all() {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM ' . self::TABLE . ' WHERE %d', 1);
		$recs = $wpdb->get_results($sql, OBJECT);
		return $recs;
	}

	/**
	 * Inserts a bucket record into the datastore.
	 *
	 * @param int 		$id				WP user ID.
	 * @param string    $bucket			String representing the type of bucket.
	 * @param int    	$post_id		Bucket post ID.
	 *
	 * @return int|bool		The number of rows affected or false if the row could
	 *						not be inserted.
	 * @since	1.0.0
	 */
	public function insert($user_id, $bucket_id, $post_id) {
		global $wpdb;
		$result = $wpdb->insert(
			self::TABLE,
			array(
				'user_id' => $user_id,
				'post_id' => $post_id,
				'bucket_id' => $bucket_id
			),
			array(
				'%d',
				'%d',
				'%s'
			)
		);
		return $result;
	}

	/**
	 * Deletes a user bucket record by its post_id.
	 *
	 * @since    1.0.0
	 *
	 * @param	int			$post_id		WP post ID;
	 * @return	int|bool					Returns the # of affected rows or
	 *										false on error.
	 *
	 *
	 */
	public function delete_user_bucket_by_post_id($post_id) {
		global $wpdb;
		$result = $wpdb->delete( self::TABLE, array( 'post_id' => $post_id ), array( '%d' ) );
		return $result;
	}

	/**
	 * Returns the name of the datastore's table.
	 *
	 * @since    1.0.0
	 *
	 * @return string	Returns the table name.
	 *
	 */
	public function get_table_name() {
		return self::TABLE;
	}

	/**
	 * Returns the private $bucket_types array.
	 *
	 * @since    1.0.0
	 *
	 * @return array	Returns the private $bucket_types array.
	 *
	 */
	public function get_user_bucket_types() {
		return $this->bucket_types;
	}

}