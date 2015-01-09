<?php
namespace Drupal\password_policy;

/**
 * Class PasswordPolicyValidation
 * @package Drupal\password_policy
 */
class PasswordPolicyValidation {

  protected $error = NULL;
  protected $valid = TRUE;

  /*
   * Set error message and mark as invalid
   */
  public function setErrorMessage($error) {
    $this->valid = FALSE;
    $this->error = $error;
  }

  /*
   * Output error message
   * @return string
   */
  public function getErrorMessage() {
    return $this->error;
  }

  /*
   * Output validation state
   * @return boolean
   */
  public function isValid() {
    return $this->valid;
  }
}