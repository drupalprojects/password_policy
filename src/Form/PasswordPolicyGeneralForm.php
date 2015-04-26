<?php
/**
 * @file
 * Contains \Drupal\password_policy\Form\PasswordPolicyGeneralForm.
 */

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PasswordPolicyGeneralForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_general_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->get('wizard');

    $form['password_reset'] = [
      '#type' => 'textfield',
      '#title' => t('Number of days'),
      '#default_value' => !empty($cached_values['password_reset']) ? $cached_values['password_reset'] : 30,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->get('wizard');
    $cached_values['password_reset'] = $form_state->getValue('password_reset');
    $form_state->set('wizard', $cached_values);
  }

}