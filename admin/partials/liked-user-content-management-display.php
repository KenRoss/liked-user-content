<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/kenross
 * @since      1.0.0
 *
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/admin/partials
 */
?>

<?php
	$settings = get_option('luc_plugin_settings');
	$create_buckets_for_new_users = $settings['create_buckets_for_new_users'];
	$disable_love_functionality = $settings['disable_love_functionality'];
	$title_format_string = $settings['title_format_string'];
	$limit_likes = $settings['limit_likes'];
?>
<div class="wrap">
	<h2>Liked User Content Settings</h2>
	<div id="auto-notices"></div>
	<form method="post" name="luc_settings" action="">
		<fieldset>
			<legend class="screen-reader-text"><span>Generate bucket pages for all Wordpress users</span></legend>
			<h3>Generate Bucket Pages</h3>
			<p><?php esc_attr_e('Generate bucket pages for all Wordpress users', $this->plugin_name); ?></p>
			<button style="display:inline-block; float:left" type="button" class="button button-primary luc-generate-pages">Generate Bucket Pages</button>
			<div style="float:left" class="spinner generate-bucket-pages-spinner">
		</fieldset>
		<fieldset>
			<hr style="margin:20px 0" />
			<legend class="screen-reader-text"><span>Create bucket pages for new users</span></legend>
			<h3>Settings</h3>
			<ul>
				<li>
					<label for="create-buckets-for-new-users">
						<input type="checkbox" id="create-buckets-for-new-users" name="create-buckets-for-new-users" value="1" <?php checked($create_buckets_for_new_users, 1); ?> />
					</label>
					<span><?php esc_attr_e('Create bucket pages for new users', $this->plugin_name); ?></span>
				</li>
				<li>
					<label for="disable-love-functionality">
						<input type="checkbox" id="disable-love-functionality" name="disable-love-functionality" value="1" <?php checked($disable_love_functionality, 1); ?> />
					</label>
					<span><?php esc_attr_e('Disable the "LOVE" button and its functionality', $this->plugin_name); ?></span>
				</li>
				<li>
					<span><?php esc_attr_e('Limit the amount of LIKES/LOVES per user (Leave as \'0\' for unlimited. Max. 9999999)', $this->plugin_name); ?></span>
					<label for="limit-likes">
						<input type="text" id="limit-likes" name="limit-likes" style="width:70px;" maxlength="7" value="<?php _e(absint(intval($limit_likes))); ?>">
					</label>
				</li>
				<li>
					<span><?php esc_attr_e('Bucket page title format string', $this->plugin_name); ?></span>
					<label for="title-format-string">
						<input type="text" id="title-format-string" name="title-format-string" class="regular-text" maxlength="64" value="<?php _e(htmlspecialchars($title_format_string)); ?>">
					</label>
				</li>
			</ul>
			<?php echo wp_nonce_field('luc_action_handler', '_lucnonce'); ?>
			<button type="button" class="button button-primary luc-save-settings">Save Settings</button>
		</fieldset>
	</form>
</div>
