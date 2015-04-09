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
   * @param constraint_id
   *   The constraint ID for the specific constraint to verify
   * @param password
   *   The password entered by the end user
   * @return boolean
   *   Whether or not the password meets the constraint in the plugin.
   */
  //TODO - Remove this, put in policy
  public function validate($constraint_id, $password);

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
  //TODO - Remove this, put in policy
  public function getErrorMessage();

  /**
   * Returns the constraint's path to create a policy.
   * @return string
   */
  //TODO - Rename this to "getPolicyCreatePath"
  public function getConstraintPath();

  /**
   * Returns the constraint's path to update a policy.
   * @return string
   */
  public function getConstraintUpdatePath();

  /**
   * Returns the token for the identifier found in the update path.
   * @return string
   */
  public function getConstraintUpdateToken();

  /**
   * Returns the constraints.
   * @return array
   */
  public function getConstraints();

  /**
   * Returns the constraint.
   * @return string
   */
  //TODO - This should be a PasswordPolicyBase object returned
  public function getConstraint($constraint_id);

  /**
   * Deletes the specific constraint.
   * @return boolean
   */
  public function deleteConstraint($constraint_id);

  /**
   * Check the specific constraint id exists.
   * @return boolean
   */
  public function constraintExists($constraint_id);

}
