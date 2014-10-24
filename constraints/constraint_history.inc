<?php
/**
 * @file
 * Password policy constraint callbacks.
 */

/****************************************************************************/
/* Constraint API                                                           */
/****************************************************************************/

/**
 * Description of the constraint.
 */
function password_policy_constraint_history_description() {
  return array('name' => t('History'), 'description' => t('Password must not match any of the user\'s previous X passwords.') . '<br/><b>' . t('Note: ') . '</b>' . t('This constraint can only compare a new password with the previous passwords recorded since the password policy module was enabled.  For example, if the number of previous passwords is set to 3, the module may have only recorded 2 password changes since the module was enabled.  If the recorded password history is not large enough to support the constraint history size, the history size for the constraint will be reduced (temporarily during the constraint check) to match the available recorded history. Also note that a history size of 1 means that the user is unable to change their password to their current password.  This can be useful in certain situations, but a setting of 2+ will likely be more useful.'));
}

/**
 * Error message of the constraint.
 */
function password_policy_constraint_history_error($constraint) {
  return format_plural($constraint, 'Password must not match last password.', 'Password must not match last @count passwords.');
}

/**
 * Password validation.
 */
function password_policy_constraint_history_validate($password, $constraint, $uid) {
  $old_hashes = _password_policy_constraint_history_old_passwords($constraint, $uid);
  if (module_exists('phpass')) {
    module_load_include('inc', 'phpass', 'password');
    $account = new stdClass();
    foreach ($old_hashes as $old_hash) {
      $account->pass = $old_hash;
      if (user_check_password($password, $account)) {
        return FALSE;
      }
    }
    return TRUE;
  }
  else {
    $hash = md5($password);
    return !in_array($hash, $old_hashes);
  }
}

/****************************************************************************/
/* Auxiliary                                                                */
/****************************************************************************/

/**
 * Gets old password hashes.
 */
function _password_policy_constraint_history_old_passwords($constraint, $uid) {
  $pass = array();
  if (!empty($uid)) {
    // note that we specify a limit of the window size, but may not get that if
    // the history isn't there.
    $result = db_query("SELECT pass FROM {password_policy_history} WHERE uid =
      %d ORDER BY created DESC LIMIT %d", $uid, $constraint);
    while ($row = db_fetch_array($result)) {
      $pass[] = $row['pass'];
    }
  }
  return $pass;
}
