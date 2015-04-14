<?php

/**
 * @file
 * Contains Drupal\password_policy_length\Constraints\PasswordLength.
 */

//TODO - Add in "tokens" into annotations (see: error message, which should show #chars from config)

namespace Drupal\password_policy_length\Plugin\PasswordConstraint;

use Drupal\password_policy\PasswordConstraintBase;
use Drupal\Core\Config\Config;
use Drupal\password_policy\PasswordPolicyValidation;

/**
 * Enforces a specific character length for passwords.
 *
 * @PasswordConstraint(
 *   id = "password_policy_length_constraint",
 *   title = @Translation("Password character length"),
 *   description = @Translation("Verifying that a password has a minimum character length"),
 *   error_message = @Translation("The length of your password is too short."),
 *   policy_path = "admin/config/security/password-policy/password-length",
 *   policy_update_path = "admin/config/security/password-policy/password-length/@pid",
 *   policy_update_token = "@pid"
 * )
 */
class PasswordLength extends PasswordConstraintBase {

  /**
   * Returns a true/false status as to if the password meets the requirements of the constraint.
   * @param password
   *   The password entered by the end user
   * @return boolean
   *   Whether or not the password meets the constraint
   */
  function validate($constraint_id, $password) {
    $constraint = db_select('password_policy_length_constraints', 'p')
      ->fields('p');

    $constraint = $constraint->condition('cid', $constraint_id)
      ->execute()
      ->fetch();

    $validation = new PasswordPolicyValidation();

    if (strlen($password) < $constraint->character_length) {
      $validation->setErrorMessage('The length of the password is ' . strlen($password) . ' characters, which is less than the ' . $policy->character_length . ' characters of the constraint');
    }
    return $validation;
  }

  /**
   * Returns an array of key value pairs, key is the ID, value is the constraint.
   *
   * @return array
   *   List of constraints.
   */
  function getConstraints() {
    $constraint = db_select('password_policy_length_constraints', 'p')
      ->fields('p');

    $constraints = $constraint->execute()->fetchAll();
    $array = array();
    foreach ($constraints as $constraint) {
      $array[$constraint->cid] = 'Minimum character length ' . $constraint->character_length;
    }
    return $array;
  }

  /**
   * Deletes the specific constraint.
   * @return boolean
   */
  public function deleteConstraint($constraint_id) {

    $result = db_delete('password_policy_length_constraints')
      ->condition('cid', $constraint_id)
      ->execute();

    if ($result) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Check if the specific constraint exists.
   * @return boolean
   */
  public function constraintExists($constraint_id) {

    $result = db_select('password_policy_length_constraints', 'p')
      ->fields('p')
      ->condition('cid', $constraint_id)
      ->execute()
      ->fetchAll();

    if (count($result) > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Return the specific constraint.
   * @return string
   */
  public function getConstraint($constraint_id) {

    $result = db_select('password_policy_length_constraints', 'p')
      ->fields('p')
      ->condition('cid', $constraint_id)
      ->execute()
      ->fetchAll();

    if (count($result) > 0) {
      $obj = $result->fetchObject();

      return 'Minimum character length ' . $obj->character_length;
    }
    return FALSE;
  }
}