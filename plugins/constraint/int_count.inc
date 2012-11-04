<?php
/**
 * @file
 * Numeric count constraint for Password Policy module.
 */

$plugin = array(
  'admin form callback' => 'password_policy_int_count_admin_form',
  'constraint callback' =>  'password_policy_int_count_constraint',
  'message' => 'Password must have int_count integers',
  'default' => 0,
);

/**
 * Admin form callback for numeric count constraint.
 */
function password_policy_int_count_admin_form($form, &$form_state, $constraint) {
  $sub_form['int_count_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Number count'),
  );
  $sub_form['int_count_fieldset']['int_count'] = array(
    '#type' => 'textfield',
    '#title' => t('Minimum integers'),
    '#default_value' => $constraint->config['int_count'],
    '#description' => t('Password will be required to have this many integers.'),
  );
  return $sub_form;
}

/**
 * Admin form callback for numeric count constraint.
 */
function password_policy_int_count_constraint($password, $account, $constraint) {
  $count = $constraint->config['int_count'];
  $regex_string = "/.*";
  if ($count && $count > 0) {
    for ($i = 1 ; $i<=$count; $i++) {
      $regex_string .='[0-9].*';
    }
  }

  $regex_string .= '/';
  return preg_match($regex_string, $password);
}
