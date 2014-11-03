<?php

/**
 * @file
 * Contains Drupal\password_policy_length\Constraints\PasswordLength.
 */

//TODO - Add in "tokens" into annotations (see: error message, which should show #chars from config)

namespace Drupal\password_policy_length\Plugin\PasswordConstraint;

use Drupal\password_policy\PasswordConstraintInterface;

/**
 * Enforces a specific character length for passwords.
 *
 * @PasswordConstraint(
 *   id = "password_policy_length_constraint",
 *   title = @Translation("Password character length"),
 *   description = @Translation("Password character length"),
 *   error_message = @Translation("The length of your password is too short."),
 *   config_path = "admin/config/security/password/length"
 * )
 */
class PasswordLength implements PasswordConstraintInterface {

	/**
	 * Returns a true/false status as to if the password meets the requirements of the constraint.
	 * @param password
	 *   The password entered by the end user
	 * @return boolean
	 *   Whether or not the password meets the constraint in the plugin.
	 */
	function validate($password) {
		$config = $this->config('password_policy_length.settings');
		if(strlen($password) < $config->get('character_length')) {
			return FALSE;
		}
		return TRUE;
	}

}