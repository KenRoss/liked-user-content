<?php

/**
 * HTML for composing the LIKE/LOVE buttons which are placed near displayed
 * image attachments.
 *
 * @link		https://github.com/kenross
 * @since		1.0.0
 *
 * @package		Liked_User_Content
 * @subpackage	Liked_User_Content/public/partials
 */
?>

<div>
	<div class="luc-btn-wrap">
		<div class="luc-btn-add like" data-attid="<?php echo $attachment_id; ?>" data-bucket="bucketA">
			<figure class="inactive"></figure>
			<figure class="active"></figure>
		</div>
	</div>
<?php if(!$plugin_settings['disable_love_functionality']) : ?>
	<div class="luc-btn-wrap">
		<div class="luc-btn-add love" data-attid="<?php echo $attachment_id; ?>" data-bucket="bucketB">
			<figure class="inactive"></figure>
			<figure class="active"></figure>
		</div>
	</div>
<?php endif; ?>
</div>