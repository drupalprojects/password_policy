<?php

/**
 * @file
 * Contains Drupal\password_policy\PasswordConstraintInterface.
 */

namespace Drupal\password_policy;

/**
 * Defines the interface for password policy constraints.
 */
interface PasswordConstraintInterface {

  /**
   * Returns a true/false status as to if the password meets the requirements of the constraint.
   * @param password
	 *   The password entered by the end user
   * @return boolean
   *   Whether or not the password meets the constraint in the plugin.
   */
  public function validate($password);

	/**
	 * Returns a translated string for the constraint title.
	 * @return string
	 */
	public function getTitle();

	/**
	 * Returns a translated description for the constraint description.
	 * @return string
	 */
	public function getDescription();

	/**
	 * Returns a translated error message for the constraint.
	 * @return string
	 */
	public function getErrorMessage();

	/**
	 * Returns the configuration path for the constraint settings.
	 * @return string
	 */
	public function getConfigPath();

}
