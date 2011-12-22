<?php
$plugin = array(
  'admin form callback' => 'password_policy_past_passwords_count_admin_form',
  'constraint callback' =>  'password_policy_past_passwords_count_constraint',
  'message' => 'Password can not match past_passwords_count past passwords.',
  'default' => 0,
);

function password_policy_past_passwords_count_admin_form($form, &$form_state, $constraint) {
  dpm($constraint);
  $sub_form['past_passwords'] = array(
    '#type' => 'textfield',
    '#title' => 'Check against Previous Passwords',
    '#default_value' => $constraint->config['past_passwords'],
    '#description' => "Password can not match this many privious passwords.",
  );
  return $sub_form;
}

function password_policy_past_passwords_count_constraint($password, $account, $constraint) {
  if(!$account) {
    $account = $user;
  }
  require_once DRUPAL_ROOT . '/' . variable_get('password_inc', 'includes/password.inc');
  $count = $constraint->config['past_passwords'];
  $query = db_select('password_policy2_history', 'p');
  $query
    ->condition('p.uid', $account->uid)
    ->fields('p', array('pass'))
    ->orderBy("created", "DESC")
    ->range(0, $count);
  $result = $query->execute();
  $match = FALSE;
  foreach ($result as $record) {
    $match = $match || user_check_password($password, $record);
  };

  return !$match;
}
