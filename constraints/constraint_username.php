<?php
// $Id$

/**
 * @file
 * Password policy constraint callbacks.
 */

include_once 'constraint.php';

class Username_Constraint extends Constraint {

  function validate($plaintext_password, $user = NULL) {
    return drupal_strtolower($user->name) != drupal_strtolower($password);
  }

  function getDescription() {
    return t('Password must differ from the username. Put any positive number to enforce this policy.');
  }

  function getValidationErrorMessage() {
    return t('Password must differ from the username.');
  }

  function _charIsValid($character) {
    return TRUE;
  }

}
