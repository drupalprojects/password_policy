<?php
/**
 * @file
 * Symbol count constraint for Password Policy module.
 */

$plugin = array(
  'admin form callback' => 'password_policy_symbol_count_admin_form',
  'constraint callback' =>  'password_policy_symbol_count_constraint',
  'message' => 'Password must have at least symbol_count symbols.',
  'prime value' => 'symbol_count',
  'config' => array(
    'symbol_count' => NULL,
    'symbol_count_symbols' => "!@#$%^&*()_+=-|}{\"?:><,./;'\[]",
  ),
);

/**
 * Admin form callback for symbol count constraint.
 */
function password_policy_symbol_count_admin_form($form, &$form_state, $constraint) {
  $sub_form['symbol_count_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Symbols'),
  );
  $sub_form['symbol_count_fieldset']['symbol_count'] = array(
    '#type' => 'textfield',
    '#title' => t('Minimum symbols'),
    '#default_value' => $constraint->config['symbol_count'],
    '#description' => t('Password will be required to have this many symbols.'),
  );
  $sub_form['symbol_count_fieldset']['symbol_count_symbols'] = array(
    '#type' => 'textfield',
    '#title' => t('Symbol characters'),
    '#default_value' => $constraint->config['symbol_count_symbols'],
    '#description' => t('Deteremine which charaters are symbols.'),
  );

  return $sub_form;
}

/**
 * Admin form callback for symbol count constraint.
 */
function password_policy_symbol_count_constraint($password, $account, $constraint) {
  $count = $constraint->config['symbol_count'];
  $symbols = addcslashes($constraint->config['symbol_count_symbols'], ']^\-/');
  $regex_string = "/.*";
  if ($count && $count > 0) {
    for ($i = 1 ; $i<=$count; $i++) {
      $regex_string .='[' . $symbols . '].*';
    }
  }

  $regex_string .= '/';
  return preg_match($regex_string, $password);
}
