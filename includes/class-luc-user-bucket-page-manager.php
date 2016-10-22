<?php

/**
 * Produces objects that will keep LIKED/LOVED user media synced between
 * bucket pages.
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/public
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_User_Bucket_Page_Manager {

	private $bucket_a;
	private $bucket_b;
	/* The admin can set a limit to the amount of media that can be LIKED/LOVED
	 * to prevent users from abusing the system by adding too many attachments
	 * to their bucket pages. */
	private $max_allowed_likes;

	/** PRIVATE METHODS *******************************************************/

	/**
	 * Returns the total number of items that have been liked or loved by the
	 * current user.
	 *
	 * Since any item that has been LOVED has also been LIKED (added as an
	 * attachment to the user's Bucket A page) we only need to return the number
	 * of attachments in the user's Bucket A page.
	 *
	 * @since	1.0.0
	 */
	private function total_like_count() {
		return $this->bucket_a->count_attachments();
	}

	/** PUBLIC METHODS ********************************************************/

	function __construct($bucket_a, $bucket_b) {
		if(!$bucket_a instanceof Luc_Bucket_Page ||
		   !$bucket_b instanceof Luc_Bucket_Page
		) {
			throw new InvalidArgumentException('At least one of the arguments to the constructor are not of class type Luc_Bucket_Page');
		}
		$this->bucket_a = $bucket_a;
		$this->bucket_b = $bucket_b;
		$this->max_attachment_count = 0;
	}

	/**
	 * Sets a value that is a limit on the amount of items a user is allowed to
	 * like or love.
	 *
	 * @param	int 	The maximum allowed number of liked/loved items a user
	 *                  may have.
	 *
	 * @since	1.0.0
	 */
	public function set_max_allowed_likes($max = 0) {
		if(!is_int($max) || $max < 0) {
			return;
		}
		$this->max_allowed_likes = $max;
	}

	/**
	 * Handles adding or removing media attachments to the LIKE bucket page,
	 * syncronizing the change to the other bucket page if needed, and reporting
	 * the change state information to the caller so the front end can reflect
	 * the change (to update the button states of UI for example.)
	 *
	 * @param	int 	ID of the attachment.
	 *
	 * @since	1.0.0
	 * @return	bool	True if a copy of the attachment exists in the bucket
	 *					page, false otherwise.
	 */
	public function toggle_like($att_id) {
		$response = new stdClass();
		$total_like_count = $this->total_like_count();
		if(!$this->bucket_a->has_copy_of_attachment($att_id)) {
			if($total_like_count >= $this->max_allowed_likes && $this->max_allowed_likes != 0) {
				$response = [
					'status' => 'success',
					'data' => [
						'exceeds_max_allowed_likes' => [
							'max_allowed_likes' => $this->max_allowed_likes
						]
					]
				];
			}
			else if($this->bucket_a->add_attachment($att_id)) {
				$response = [
					'status' => 'success',
					'data' => [
						'button_states' => [
							'bucket_a' => [
								'attid' => $att_id,
								'bucket' => 'bucketA',
								'toggle_state' => 'active'
							]
						]
					]
				];
			}
		} else if($this->bucket_a->has_copy_of_attachment($att_id) &&
		          $this->bucket_b->has_copy_of_attachment($att_id)) {
			$this->bucket_b->remove_attachment($att_id);
			$response = [
				'status' => 'success',
				'data' => [
					'button_states' => [
						'bucket_a' => [
							'attid' => $att_id,
							'bucket' => 'bucketA',
							'toggle_state' => 'active'
						],
						'bucket_b' => [
							'attid' => $att_id,
							'bucket' => 'bucketB',
							'toggle_state' => 'static'
						]
					]
				]
			];
		} else {
			if($this->bucket_a->remove_attachment($att_id)) {
				$response = [
					'status' => 'success',
					'data' => [
						'button_states' => [
							'bucket_a' => [
								'attid' => $att_id,
								'bucket' => 'bucketA',
								'toggle_state' => 'static'
							]
						]
					]
				];
			}
		}
		return $response;
	}

	/**
	 * Handles adding or removing media attachments to the LOVE bucket page,
	 * syncronizing the change to the other bucket page if needed, and reporting
	 * the change state information to the caller so the front end can reflect
	 * the change (to update the button states of UI for example.)
	 *
	 * @param	int 	ID of the attachment.
	 *
	 * @since	1.0.0
	 * @return	bool	True if a copy of the attachment exists in the bucket
	 *					page, false otherwise.
	 */
	public function toggle_love($att_id) {
		$response = new stdClass();
		$total_like_count = $this->total_like_count();
		if(!$this->bucket_b->has_copy_of_attachment($att_id) &&		// MIDDLE
		    $this->bucket_a->has_copy_of_attachment($att_id)) {
			if($this->bucket_b->add_attachment($att_id)) {
				$response = [
					'status' => 'success',
					'data' => [
						'button_states' => [
							'bucket_a' => [
								'attid' => $att_id,
								'bucket' => 'bucketA',
								'toggle_state' => 'static'
							],
							'bucket_b' => [
								'attid' => $att_id,
								'bucket' => 'bucketB',
								'toggle_state' => 'active'
							]
						]
					]
				];
			}
		} else if($this->bucket_a->has_copy_of_attachment($att_id) &&		// TOP
		          $this->bucket_b->has_copy_of_attachment($att_id)) {
			$this->bucket_a->remove_attachment($att_id);
			$this->bucket_b->remove_attachment($att_id);
			$response = [
				'status' => 'success',
				'data' => [
					'button_states' => [
						'bucket_b' => [
							'attid' => $att_id,
							'bucket' => 'bucketB',
							'toggle_state' => 'static'
						]
					]
				]
			];
		} else if(!$this->bucket_b->has_copy_of_attachment($att_id) &&
		          !$this->bucket_a->has_copy_of_attachment($att_id)) {
			if($total_like_count >= $this->max_allowed_likes && $this->max_allowed_likes != 0) {
				$response = [
   					'status' => 'success',
   					'data' => [
						'exceeds_max_allowed_likes' => [
							'max_allowed_likes' => $this->max_allowed_likes
						]
   					]
   				];
			} else {
				$this->bucket_a->add_attachment($att_id);
			  	$this->bucket_b->add_attachment($att_id);
				$response = [
					'status' => 'success',
					'data' => [
						'button_states' => [
							'bucket_b' => [
								'attid' => $att_id,
								'bucket' => 'bucketB',
								'toggle_state' => 'active'
							]
						]
					]
				];
			}
		}
		return $response;
	}

}