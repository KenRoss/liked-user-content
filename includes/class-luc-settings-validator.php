<?php

/**
 * Class responsible for validating user input sent from the Liked User Content
 * Manage Settings page to the server.
 *
 * @since      1.0.0
 * @package    Liked_User_Content
 * @subpackage Liked_User_Content/includes
 * @author     Kenneth Ross <kenn.ross@gmail.com>
 */
class Luc_Settings_Validator {

	function __construct() {
	}

	/** PRIVATE METHODS *******************************************************/

	private function valid_checkbox_value($value) {
		if((string) $value === '1' || (string) $value === '0') {
			return true;
		}
		return false;
	}

	private function valid_integer_range($int, $min, $max) {
		if(is_string($int) && !ctype_digit($int)) {
			return false;
		}
		if(!is_int((int) $int)) {
			return false;
		}
		return ($int >= $min && $int <= $max);
	}

	/** PUBLIC METHODS ********************************************************/

	/**
	 * Validates the settings that are sent from the Manage Settings page to
	 * the server by the user when the settings are saved/updated.
	 *
	 * @since	1.0.0
	 * @param	int		$raw_settings	Unfiltered user input
	 * @return	bool	Returns true on success.
	 */
	public function validate_save_settings($settings) {
		if(!is_array($settings)) {
			throw new Exception('Invalid list of settings');
		}
		if(!$this->valid_checkbox_value($settings['create_buckets_for_new_users']) ||
		   !$this->valid_checkbox_value($settings['disable_love_functionality'])) {
			throw new Exception('Invalid checkbox value');
		}
		if(!$this->valid_integer_range($settings['limit_likes'], 0, 9999999)) {
			throw new Exception('Invalid limit likes value');
		}
		if(strlen($settings['title_format_string']) < 1) {
			throw new Exception('Title format string is too short');
		} else if(strlen($settings['title_format_string']) > 64) {
			throw new Exception('Title format string is too long');
		}
		return true;
	}

}