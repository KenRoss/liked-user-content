<?php

/**
 * The Luc_Filesystem class provides methods for manipulating files and
 * directories under the WordPress uploads directory.
 *
 *
 * @since      1.0.0
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_Filesystem {

	const MEDIA_DIR = 'luc_media';

	function __construct() {
	}

	/** PRIVATE METHODS *******************************************************/

	/** PUBLIC METHODS ********************************************************/

	/**
	 * Generates a new basename for a file using a hash made from a filepath,
	 * user ID, and bucket page ID.
	 *
	 *
	 * @since	1.0.0
	 * @return	string	Returns a new filename.
	 */
	public function generate_unique_basename($file, $user_id, $bucket_page_id) {
		$md5 = md5("{$file}{$user_id}{$bucket_page_id}");
		$new_basename = $md5;
		$path_parts = pathinfo($file);
		if(isset($path_parts['extension'])) {
			$new_basename .= '.' . $path_parts['extension'];
		}
		return $new_basename;
	}

	/**
	 * Creates the directory where the plugin will copy attachments to when they
	 * are LIKED/LOVED by the user.
	 *
	 * @since	1.0.0
	 * @return	bool	Returns true on success, false otherwise.
	 */
	public function setup_media_dir() {
		$upload_dir = wp_upload_dir();
		$media_dir = $upload_dir['basedir'] . '/' . self::MEDIA_DIR;
		if(is_dir($media_dir)) {
			return true;
		}
		$old = umask(0);
		$result = mkdir($media_dir, 0777);
		umask($old);
		return $result;
	}

	/**
	 * Copies the file named in $source_file to the LUC media directory.
	 *
	 * @since	1.0.0
	 * @return	string|bool	Returns the filename and path of the copied file or
	 *						false if the copy was unsuccessful.
	 */
	public function copy_file($source_file, $copy_basename) {
		$upload_dir = wp_upload_dir();
		$media_dir = $upload_dir['basedir'] . '/' . self::MEDIA_DIR;
		if(!is_dir($media_dir)) {
			error_log('Cannot copy file. LUC media directory does not exist.');
			return false;
		}
		if(!file_exists($source_file)) {
			error_log('Cannot copy file. Source file does not exist.');
			return false;
		}
		$new_file = $media_dir . '/' . $copy_basename;
		if(file_exists($new_file)) {
			return false;
		}
		$success = copy($source_file, $new_file);
		if(!$success) {
			return false;
		}
		return $new_file;
	}

}