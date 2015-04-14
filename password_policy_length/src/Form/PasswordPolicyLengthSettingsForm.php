<?php

namespace Drupal\password_policy_length\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyLengthSettingsForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_length_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = array();

    //get policy
    $constraint_id = '';
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);
    if (count($path_args) == 7) {
      $constraint_id = $path_args[6];
      //load the policy
      $constraint = db_select('password_policy_length_constraints', 'p')
        ->fields('p')
        ->condition('cid', $constraint_id)
        ->execute()
        ->fetchObject();
    }

    $form['cid'] = array(
      '#type' => 'hidden',
      '#value' => (is_numeric($constraint_id)) ? $constraint_id : '',
    );

    $form['character_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of characters'),
      '#default_value' => (is_numeric($constraint_id)) ? $constraint->character_length : '',
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => (is_numeric($constraint_id)) ? t('Update constraint') : t('Add constraint'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('character_length')) or $form_state->getValue('character_length') < 0) {
      $form_state->setErrorByName('character_length', $this->t('The character length must be a positive number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('cid')) {
      db_update('password_policy_length_constraints')
        ->fields(array('character_length' => $form_state->getValue('character_length')))
        ->condition('cid', $form_state->getValue('cid'))
        ->execute();
    }
    else {
      db_insert('password_policy_length_constraints')
        ->fields(array('character_length'))
        ->values(array('character_length' => $form_state->getValue('character_length')))
        ->execute();
    }
    drupal_set_message('Password length settings have been stored');
    $form_state->setRedirect('password_policy.settings');
  }
}