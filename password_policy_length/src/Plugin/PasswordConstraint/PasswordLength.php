<?php

/**
 * @file
 * Contains Drupal\password_policy_length\Constraints\PasswordLength.
 */


namespace Drupal\password_policy_length\Plugin\PasswordConstraint;

use Drupal\Core\Form\FormStateInterface;
use Drupal\password_policy\PasswordConstraintBase;
use Drupal\password_policy\PasswordPolicyValidation;

/**
 * Enforces a specific character length for passwords.
 *
 * @PasswordConstraint(
 *   id = "password_length",
 *   title = @Translation("Password character length"),
 *   description = @Translation("Verifying that a password has a minimum character length"),
 *   error_message = @Translation("The length of your password is too short.")
 * )
 */
class PasswordLength extends PasswordConstraintBase {

  /**
   * {@inheritdoc}
   */
  function validate($password, $user_context) {
    $configuration = $this->getConfiguration();
    $validation = new PasswordPolicyValidation();
    switch($configuration['character_operation']) {
      case 'minimum':
        if (strlen($password) < $configuration['character_length']) {
        $validation->setErrorMessage($this->t('The length of the password is @count characters and needs to be at least @length characters', ['@count' => strlen($password), '@length' => $configuration['character_length']]));
        }
        break;
      case 'maximum':
        if (strlen($password) > $configuration['character_length']) {
          $validation->setErrorMessage($this->t('The length of the password is @count characters and can only be at most @length characters', ['@count' => strlen($password), '@length' => $configuration['character_length']]));
        }
        break;
    }
    return $validation;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'character_length' => 0,
      'character_operation' => 'minimum',
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
    $form['character_operation'] = array(
      '#type' => 'select',
      '#title' => t('Operation'),
      '#options' => array('minimum' => 'Minimum length', 'maximum' => 'Maximum length'),
      '#default_value' => $this->getConfiguration()['character_operation'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!is_numeric($form_state->getValue('character_length')) or $form_state->getValue('character_length') <= 0) {
      $form_state->setErrorByName('character_length', $this->t('The character length must be a positive number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['character_length'] = $form_state->getValue('character_length');
    $this->configuration['character_operation'] = $form_state->getValue('character_operation');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    switch($this->configuration['character_operation']) {
      case 'minimum':
        $operation = 'at least';
        break;
      case 'maximum':
        $operation = 'at most';
        break;
    }
    return $this->t('Password character length of @operation @characters', array('@operation' => $operation,'@characters' => $this->configuration['character_length']));
  }

}
