<?php
/**
 * @file
 * Provides Drupal\password_policy\PasswordConstraintBase.
 */

namespace Drupal\password_policy;

use Drupal\Component\Plugin\PluginBase;

class PasswordConstraintBase extends PluginBase implements PasswordConstraintInterface {
	/**
	 * Returns a true/false status as to if the password meets the requirements of the constraint.
	 * @param password
	 *   The password entered by the end user
	 * @return boolean
	 *   Whether or not the password meets the constraint in the plugin.
	 */
	public function validate($policy_id, $password){
		//all classes should plan to override this specific function, for now, just assume TRUE
		return TRUE;
	}

	/**
	 * Returns a translated string for the constraint title.
	 * @return string
	 */
	public function getTitle(){
		return $this->pluginDefinition['title'];
	}

	/**
	 * Returns a translated description for the constraint description.
	 * @return string
	 */
	public function getDescription(){
		return $this->pluginDefinition['description'];
	}

	/**
	 * Returns a translated error message for the constraint.
	 * @return string
	 */
	public function getErrorMessage(){
		return $this->pluginDefinition['error_message'];
	}

	/**
	 * Returns the configuration path for the constraint settings.
	 * @return string
	 */
	public function getFormId(){
		return $this->pluginDefinition['form_id'];
	}

	/**
	 * Returns an array of key value pairs.
	 * @return array
	 */
	public function getPolicies(){
		return array();
	}
}