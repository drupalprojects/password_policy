<?php

/**
 * @file
 * Contains Drupal\password_policy_length\Constraints\PasswordLength.
 */

//TODO - Add in "tokens" into annotations (see: error message, which should show #chars from config)

namespace Drupal\password_policy_length\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\password_policy\PasswordConstraintBase;
use Drupal\password_policy\PasswordPolicyValidation;

/**
 * Enforces a specific character length for passwords.
 *
 * @PasswordConstraint(
 *   id = "password_policy_length_constraint",
 *   title = @Translation("Password character length"),
 *   description = @Translation("Verifying that a password has a minimum character length"),
 *   error_message = @Translation("The length of your password is too short.")
 * )
 */
class PasswordLength extends PasswordConstraintBase {

  /**
   * {@inheritdoc}
   */
  function validate($password) {
    $configuration = $this->getConfiguration();
    $validation = new PasswordPolicyValidation();

    if (strlen($password) < $configuration['character_length']) {
      $validation->setErrorMessage($this->t('The length of the password is !count characters, which is less than the @length characters of the constraint', ['!count' => strlen($password), '@length' => $configuration['character_length']]));
    }
    return $validation;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'character_length' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['character_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of characters'),
      '#default_value' => $this->getConfiguration()['character_length'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('character_length')) or $form_state->getValue('character_length') < 0) {
      $form_state->setErrorByName('character_length', $this->t('The character length must be a positive number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['character_length'] = $form_state->getValue('character_length');
  }

  /**
   * Returns a human-readable summary of the constraint.
   * @return string
   */
  public function getSummary() {
    return $this->t('Password character length of at least @characters', array('@characters' => $this->configuration['character_length']));
  }


}
