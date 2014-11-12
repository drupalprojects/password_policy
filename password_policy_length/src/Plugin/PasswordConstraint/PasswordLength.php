<?php

/**
 * @file
 * Contains Drupal\password_policy_length\Constraints\PasswordLength.
 */

//TODO - Add in "tokens" into annotations (see: error message, which should show #chars from config)

namespace Drupal\password_policy_length\Plugin\PasswordConstraint;

use Drupal\password_policy\PasswordConstraintBase;
use Drupal\Core\Config\Config;

/**
 * Enforces a specific character length for passwords.
 *
 * @PasswordConstraint(
 *   id = "password_policy_length_constraint",
 *   title = @Translation("Password character length"),
 *   description = @Translation("Verifying that a password has a minimum character length"),
 *   error_message = @Translation("The length of your password is too short."),
 *   form_id = "Drupal\password_policy_length\Form\PasswordPolicyLengthSettingsForm"
 * )
 */
class PasswordLength extends PasswordConstraintBase {

	/**
	 * Returns a true/false status as to if the password meets the requirements of the constraint.
	 * @param password
	 *   The password entered by the end user
	 * @return boolean
	 *   Whether or not the password meets the constraint in the plugin.
	 */
	function validate($policy_id, $password) {
		$policy = db_select('password_policy_length_policies', 'p')
			->fields('p');

		$policy = $policy->condition('', $policy_id)
			->execute()
			->fetch();

		if(strlen($password) < $policy->character_length) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Returns an array of key value pairs, key is the ID, value is the policy.
	 *
	 * @return array
	 *   List of policies.
	 */
	function getPolicies() {
		$policy = db_select('password_policy_length_policies', 'p')
			->fields('p');

		$policies = $policy->execute()->fetchAll();
		$array = array();
		foreach($policies as $policy){
			$array[$policy->pid] = 'Minimum character length ' . $policy->character_length;
		}
		return $array;
	}
}