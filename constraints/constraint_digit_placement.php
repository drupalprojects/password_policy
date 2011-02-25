<?php

/**
 * @file
 * Restrict placement of digits in passwords.
 *
 * @link http://drupal.org/node/316768
 * @author David Kent Norman (http://deekayen.net/)
 */

include_once 'constraint_character.php';

class Digit_Placement_Constraint extends Character_Constraint {

  function validate($plaintext_password, $user = NULL) {
    $number_of_digits = 0;
    for ($i=0; $i<10; $i++) {
      $number_of_digits += substr_count($plaintext_password, "$i"); // help string count by sending it a string instead of an int
    }
    if ($number_of_digits < (int)$this->minimumConstraintValue) {
      return preg_match("/(^\d+)|(\d+$)/", $plaintext_password) != 1;
    }
    return TRUE;
  }

  function getDescription() {
    return t('Minimum number of digits in the password to allow a digit in the first or last position in the password (e.g. 2abcdefg and abcdefg4 are unacceptable passwords, while 2qpcxrm3 and 99qpcxrm are allowed passwords when 2 is set here).');
  }

  function getValidationErrorMessage() {
    return t('Password must have a minimum of %numChars %digits to place any digits at the start or end of the password.',
    array('%numChars' => $this->minimumConstraintValue,
        '%digits' => format_plural($this->minimumConstraintValue, t('digit'), t('digits'))));
  }

}
