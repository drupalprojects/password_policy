<?php

/**
 * @file
 * Contains \Drupal\PasswordPolicy\PasswordConstraintInterface.
 */

namespace Drupal\PasswordPolicy;

/**
 * Defines the interface for pants types.
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

}
