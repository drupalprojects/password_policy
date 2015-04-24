<?php

/**
 * @file
 * Contains Drupal\password_policy\Form\PasswordConstraintForm.
 */

namespace Drupal\password_policy\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PasswordConstraintForm.
 *
 * @package Drupal\password_policy\Form
 */
class PasswordConstraintForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $password_constraint = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $password_constraint->label(),
      '#description' => $this->t("Label for the PasswordConstraint."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $password_constraint->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\password_policy\Entity\PasswordConstraint::load',
      ),
      '#disabled' => !$password_constraint->isNew(),
    );

    $plugin_id = $password_constraint->getPluginId();
    if(empty($plugin_id)){
      $url = \Drupal\Core\Url::fromRoute('<current>');
      $current_path = $url->toString();
      $path_args = explode('/', $current_path);

      if (count($path_args) != 6) {
        drupal_set_message('Improper parameters', 'error');
        return array();
      }

      $plugin_id = $path_args[5];
      $password_constraint->setPluginId($plugin_id);
    }
    $form['plugin_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $password_constraint->getPluginId(),
    );

    // Load plugin
    $form['settings'] = $password_constraint->getPluginInstance()->buildConfigurationForm(array(), $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $password_constraint = $this->entity;
    $status = $password_constraint->save();

    if ($status) {
      drupal_set_message($this->t('Saved the %label password constraint.', array(
        '%label' => $password_constraint->label(),
      )));
    }
    else {
      drupal_set_message($this->t('The %label password constraint was not saved.', array(
        '%label' => $password_constraint->label(),
      )));
    }
    $form_state->setRedirectUrl($password_constraint->urlInfo('collection'));
  }

}
