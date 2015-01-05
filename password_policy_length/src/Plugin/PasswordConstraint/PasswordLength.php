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
 *   policy_path = "admin/config/security/password/constraint/length",
 *   policy_update_path = "admin/config/security/password/constraint/length/@pid",
 *   policy_update_token = "@pid"
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

		$policy = $policy->condition('pid', $policy_id)
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

	/**
	 * Deletes the specific policy.
	 * @return boolean
	 */
	public function deletePolicy($policy_id){

		$result = db_delete('password_policy_length_policies')
			->condition('pid', $policy_id)
			->execute();

		if($result){
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Check the specific policy exists.
	 * @return boolean
	 */
	public function policyExists($policy_id){

		$result = db_select('password_policy_length_policies', 'p')
			->fields('p')
			->condition('pid', $policy_id)
			->execute()
		  ->fetchAll();

		if(count($result)>0){
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Return the specific policy exists.
	 * @return string
	 */
	public function getPolicy($policy_id){

		$result = db_select('password_policy_length_policies', 'p')
			->fields('p')
			->condition('pid', $policy_id)
			->execute()
			->fetchAll();

		if(count($result)>0){
			$obj = $result->fetchObject();

			return 'Minimum character length ' . $obj->character_length;
		}
		return FALSE;
	}
}