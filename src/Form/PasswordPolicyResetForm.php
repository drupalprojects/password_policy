<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyResetForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //get constraint
    $constraint_id = '';
    //get current path
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);
    if (count($path_args) == 7 and is_numeric($path_args[6])) {
      $constraint_id = $path_args[6];
      //load the policy
      $constraint = db_select('password_policy_reset', 'p')
        ->fields('p')
        ->condition('cid', $constraint_id)
        ->execute()
        ->fetchObject();
    }

    $form = array(
      'cid' => array(
        '#type' => 'hidden',
        '#value' => (is_numeric($constraint_id)) ? $constraint_id : '',
      ),
      'number_of_days' => array(
        '#type' => 'textfield',
        '#title' => t('Number of days'),
        '#default_value' => (is_numeric($constraint_id)) ? $constraint->number_of_days : '',
      ),
      'submit' => array(
        '#type' => 'submit',
        '#value' => (is_numeric($constraint_id)) ? t('Update constraint') : t('Add constraint'),
      ),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $days = $form_state->getValue('number_of_days');
    if (!is_numeric($days) or $days <= 0) {
      $form_state->setErrorByName('number_of_days', $this->t('The number of days must be a positive integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('cid')) {
      db_update('password_policy_reset')
        ->fields(array('number_of_days' => $form_state->getValue('number_of_days')))
        ->condition('cid', $form_state->getValue('cid'))
        ->execute();
    }
    else {
      db_insert('password_policy_reset')
        ->fields(array('number_of_days'))
        ->values(array('number_of_days' => $form_state->getValue('number_of_days')))
        ->execute();
    }
    drupal_set_message('Your constraint has been added');
    $form_state->setRedirect('password_policy.settings');
  }
}