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
function password_policy_constraint_username_description() {
  return array('name' => t('Username'), 'description' => t('Password must not contain the username (case insensitive). Put any positive number to enforce this policy.'));
}

/**
 * Error message of the constraint.
 */
function password_policy_constraint_username_error($constraint) {
  return t('Password must not contain the username.');
}

/**
 * Password validation.
 */
function password_policy_constraint_username_validate($password, $constraint, $uid) {
  $account = user_load(array('uid' => $uid));
  if ($account->name == '') {
    return TRUE;
  }
  $username_lowercase = drupal_strtolower($account->name);
  $password_lowercase = drupal_strtolower($password);
  if ($constraint && (strpos($password_lowercase, $username_lowercase) !== FALSE)) {
    return FALSE;
  }
  return TRUE;
}

/**
 * Javascript portion.
 */
function password_policy_constraint_username_js($constraint, $uid) {
  // Add username as JavaScript setting.
  $account = user_load(array('uid' => $uid));
  $username = $account->name;
  if ($username == '') {
    return '';
  }
  $data = array(
    'password_policy' => array(
      'username' => $username,
    ),
  );
  drupal_add_js($data, 'setting');

  $s = '';
  if ($constraint) {
    $s .= "  var username=Drupal.settings.password_policy.username;\n";
    $s .= "  var username_lowercase=username.toLowerCase();\n";
    $s .= "  var password_lowercase=value.toLowerCase();\n";
    $s .= "  if (password_lowercase.indexOf(username_lowercase) != -1) {\n";
    $s .= "    strength=\"low\";\n";
    $s .= "    msg.push(translate.constraint_username);\n";
    $s .= "  }\n";
  }
  return $s;
}
