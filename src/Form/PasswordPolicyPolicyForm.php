<?php

/**
 * NOTE - This concept seems unneccessary now, I am going to set up a redirect to a form
 */

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyPolicyForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_policy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //get plugin
    $path_args = explode('/', current_path());
    $policy_plugin = $path_args[5];
    //load the plugin
    $plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
    $all_plugins = $plugin_manager->getDefinitions();
    foreach ($all_plugins as $plugin) {
      if ($plugin['id'] == $policy_plugin) {
        $form_id = $plugin['form_id'];
      }
    }


    //$form = \Drupal::formBuilder()->buildForm($form_id, $form_state);
    $form = \Drupal::formBuilder()->getForm($form_id);


    //add some hidden fields
    //dpm($form);
    //add a new submit handler for password policy

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //get plugin
    $path_args = explode('/', current_path());
    $policy_plugin = $path_args[5];
    //load the plugin
    $plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
    $all_plugins = $plugin_manager->getDefinitions();
    foreach ($all_plugins as $plugin) {
      if ($plugin['id'] == $policy_plugin) {
        $form_id = $plugin['form_id'];
      }
    }

    \Drupal::formBuilder()->submitForm($form_id, $form_state);
    drupal_set_message('Your policy has been added');
  }
}