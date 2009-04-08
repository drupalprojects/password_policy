<?php
// $Id$

/**
 * @file
 * Password policy constraint callback to set a minimum delay between password changes.
 *
 * @link http://drupal.org/node/316765
 * @author David Kent Norman (http://deekayen.net/)
 */

include_once 'constraint.php';

class Delay_Constraint extends Constraint {

  function validate($plaintext_password, $user = NULL) {
    $last_change = db_result(db_query_range("SELECT MAX(created) FROM {password_policy_users} WHERE uid = %d", $user->uid, 0, 1));
    if (!empty($last_change)) {
      // constraint is set in hours, so it gets converted to seconds with *60*60
      return time() - ($this->minimumConstraintValue*60*60) > $last_change;
    }
    return TRUE;
  }

  function getDescription() {
    return t('Minimum number of hours between password changes.');
  }

  function getValidationErrorMessage() {
    return t('Passwords may only be changed every %numHours @charHours.',
      array('%numHours' => $this->minimumConstraintValue,
        '@charHours' => format_plural($this->minimumConstraintValue, t('hour'), t('hours'))));
  }

}
