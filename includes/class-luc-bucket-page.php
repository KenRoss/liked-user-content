<?php

/**
 * Class for creating objects that will handle managing attachments in Bucket
 * Pages.
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/public
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_Bucket_Page {

	/* Post ID of the bucket page that this object manages. */
	private $post_id;

	function __construct($user_id, $bucket_id) {
		$post_id = $this->get_bucket_page_id($user_id, $bucket_id);
		if(!$post_id) {
			throw new Exception("Invalid post ID value: $post_id");
		}
		$this->post_id = $post_id;
	}

	/** PRIVATE METHODS *******************************************************/

	/**
	 * Use the user_id and bucket_id provided to the constructor to get the
	 * post_id of the relevent bucket page.
	 *
	 * @since	1.0.0
	 * @return	int		The post_id of the bucket page or 0 on failure.
	 */
	private function get_bucket_page_id($user_id, $bucket_id) {
		$ubc = new Luc_User_Bucket_Coordinator();
		$recs = $ubc->get_user_buckets_by_user_id($user_id);
		foreach($recs as $rec) {
			if($rec->bucket_id == $bucket_id) {
				return $rec->post_id;
			}
		}
		return 0;
	}

	/**
	 * Checks whether or not $att_id identifies a valid media attachment stored
	 * in WordPress.
	 *
	 * In the future this method may validate attachments other than images.
	 * For now it only validates image attachments.
	 *
	 * @since	1.0.0
	 * @return	bool	Returns true if $att_id IDs a valid media attachment,
	 *					false otherwise.
	 */
	private function valid_media_att_id($att_id) {
		$mime_type = get_post_mime_type($att_id);
		if(preg_match('/^image\//', $mime_type)) {
			return true;
		}
		return false;
	}

	/** PUBLIC METHODS ********************************************************/

	/**
	 * Return a count of the attachments in the bucket page.
	 *
	 * @since	1.0.0
	 * @return	int		Returns the number of attachments in the bucket page.
	 */
	public function count_attachments() {
		$args = array(
			'post_parent' => $this->post_id,
			'post_type'   => 'attachment',
			'numberposts' => -1,
			'post_status' => 'any'
		);
		$attachments = get_children($args);
		return count($attachments);
	}

	/**
	 * Return true if a copy of the media attachment ID'd by $att_id has been
	 * copied and added to the bucket page previously.
	 *
	 * @param	int 	ID of the attachment.
	 *
	 * @since	1.0.0
	 * @return	bool	True if a copy of the attachment exists in the bucket
	 *					page, false otherwise.
	 */
	public function has_copy_of_attachment($att_id) {
		$lookup = new Luc_Attachment_Source_To_Copy_Lookup();
		$bucket_post = get_post($this->post_id, OBJECT);
		$copy_att_id = $lookup->get_bucket_copy_from_source($att_id, intval($bucket_post->post_author), $this->post_id);
		return $copy_att_id ? true : false;
	}

	/**
	 * Make a copy of an existing attachment (as long as it is not a bucket page
	 * attachment) and insert it into the object's bucket page.
	 *
	 * @param	int 	ID of the attachment.
	 *
	 * @since	1.0.0
	 * @return	int		Returns the ID of the newly created bucket attachment or
	 *					0 if it was not created.
	 */
	public function add_attachment($att_id) {
		if(!$this->valid_media_att_id($att_id)) {
			return 0;
		}
		$attachment = get_post($att_id, OBJECT);
		// Do not add bucket page attachments to other bucket pages.
		$parent = get_post($attachment->post_parent, OBJECT);
		if($parent->post_type == 'bucket') {
			return 0;
		}
		$attachment_file = get_attached_file($att_id);
		$fs = new Luc_Filesystem();
		$copy_basename = $fs->generate_unique_basename($attachment_file, get_current_user_id(), $this->post_id);
		$new_file = $fs->copy_file($attachment_file, $copy_basename);
		if(!$new_file) {
			return 0;
		}
		$upload_dir = wp_upload_dir();
		$file_type = wp_check_filetype(basename($new_file), null);
		$bucket_attachment = array(
			'guid'				=> $upload_dir['url'] . '/' . basename($new_file),
			'post_mime_type'	=> $file_type['type'],
			'post_title'		=> 'bucket copy',
			'post_content'		=> '',
			'post_status'		=> 'inherit'
		);
		$bucket_att_id = wp_insert_attachment($bucket_attachment, $new_file, $this->post_id);
		if(!$bucket_att_id) {
			return 0;
		}
		// Required for wp_generate_attachment_metadata().
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$bucket_att_data = wp_generate_attachment_metadata($bucket_att_id, $new_file);
		wp_update_attachment_metadata($bucket_att_id, $bucket_att_data);
		set_post_thumbnail($this->post_id, $bucket_att_id);
		$relationship = new Luc_Attachment_Source_To_Copy_Lookup();
		$relationship->insert($att_id, $bucket_att_id);
		return $bucket_att_id;
	}

	/**
	 * Deletes an attachment from the object's bucket page.
	 *
	 * @param	int 	ID of the attachment.
	 *
	 * @since	1.0.0
	 * @return	int		A copy of the post (attachment) that was deleted or false on failure.
	 */
	public function remove_attachment($att_id) {
		if(!$this->valid_media_att_id($att_id)) {
			return 0;
		}
		$lookup = new Luc_Attachment_Source_To_Copy_Lookup();
		$bucket_page = get_post($this->post_id, OBJECT);
		$user_id = $bucket_page->post_author;
		$bucket_att_id = $lookup->get_bucket_copy_from_source($att_id, $user_id, $this->post_id);
		$result = wp_delete_post($bucket_att_id, true);
		if($result) {
			$lookup->delete_by_bucket_copy_att_id($bucket_att_id);
		}
		return $result ? $bucket_att_id : 0;
	}

}