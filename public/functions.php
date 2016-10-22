<?php

/**
 * User functions for plugin authors to utilize LUC.
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/public
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */

 /**
  * Returns the markup for the LUC toggle buttons to be echoed and displayed as
  * HTML. It accepts the post ID of the attachment that will be LIKED or LOVED
  * by the user.
  *
  * Usage: echo luc_display_toggle_buttons($post_id);
  * $post_id will be the ID of an image attachment.
  *
  * Normally you will want to place the toggle buttons beneath the image.
  * This function can be used to generate multiple toggle buttons on the same
  * page for liking several media attachments.
  *
  * @param	int		$attachment_id	ID of the attachment.
  *
  * @since	1.0.0
  * @return	string	The HTML markup for displaying the toggle buttons.
  */
function luc_display_toggle_buttons($attachment_id) {
	/* Determine if $attachment_id identifies a bucket page attachment. If
	 * it does, change it to be the ID of its assocated source attachment so the
	 * plugin is not trying to add bucket attachments into bucket posts. */
	$lookup = new Luc_Attachment_Source_To_Copy_Lookup();
	$source_att_id = $lookup->get_source_from_bucket_copy($attachment_id);
	if($source_att_id != 0) {
		$attachment_id = $source_att_id;
	}
	$plugin_settings = get_option('luc_plugin_settings');
	ob_start();
	include('partials/liked-user-content-likelove-controls.php');
	return ob_get_clean();
}

/**
 * Returns an array containing the post titles and URLs to a WP user's bucket
 * pages.
 *
 * [
 *     0 => [
 *              'post_title' => 'Claire\'s liked stuff',
 *              'url'        => 'http://123.whatever.xyz/wordpress/claires-liked-stuff'
 *         ],
 *     1 => [
 *              'post_title' => 'Claire\'s liked stuff',
 *              'url'        => 'http://123.whatever.xyz/wordpress/claires-loved-stuff'
 *         ],
 * ]
 *
 * @param	int		$user_id	User ID.
 *
 * @since	1.0.0
 * @return	string	An array containing the post titles and URLs of the user's
 *                  bucket pages or an empty array if the function was called
 *                  with an invalid user ID or no bucket pages exist for the
 *                  user.
 */
function luc_get_user_bucket_links($user_id = 0) {
	if($user_id == 0) {
		$user_id = get_current_user_id();
		if($user_id == 0) {
			return array();
		}
	}
	$ubc = new Luc_User_Bucket_Coordinator();
	$buckets = $ubc->get_user_buckets_by_user_id($user_id);
	if(!$buckets) {
		return array();
	}
	$bucket_links = array();
	foreach($buckets as $bucket) {
		$page = get_post($bucket->post_id, OBJECT);
		$bucket_links[] = array(
			'post_title' => $page->post_title,
			'url' => get_permalink($bucket->post_id),
		);
	}
	return $bucket_links;
}
