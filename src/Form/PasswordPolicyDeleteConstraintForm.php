<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyDeleteConstraintForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_delete_constraint_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //get policy and plugin
    //get current path
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);
    $policy_instance = FALSE;
    $plugin_instance = FALSE;
    if (count($path_args) == 8) {
      $plugin_id = $path_args[6];
      $plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')
        ->createInstance($plugin_id);
    }

    //validate params
    if ($plugin_instance) {

      $policy_id = $path_args[7];
      if (!$plugin_instance->policyExists($policy_id)) {
        drupal_set_message('No policy found', 'error');
        return array();
      }
      else {
        //TODO - Clean this up with a Policy interface and "getPolicy($id)"
        $all_policies = $plugin_instance->getPolicies();
        if (array_key_exists($policy_id, $all_policies)) {
          $policy_instance = $all_policies[$policy_id];
        }
      }
    }

    if (!$policy_instance or !$plugin_instance) {
      drupal_set_message('No plugin or policy found', 'error');
      return array();
    }

    $form = array(
      'policy_id' => array(
        '#type' => 'hidden',
        '#value' => (is_numeric($policy_id)) ? $policy_id : '',
      ),
      'plugin_id' => array(
        '#type' => 'hidden',
        '#value' => (!empty($plugin_id)) ? $plugin_id : '',
      ),
      'description' => array(
        '#markup' => 'Are you sure you wish to delete this policy?'
      ),
      'submit' => array(
        '#type' => 'submit',
        '#value' => t('Confirm deletion of policy'),
      ),
    );
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
    $plugin_id = $form_state->getValue('plugin_id');
    $policy_id = $form_state->getValue('policy_id');
    $plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')
      ->createInstance($plugin_id);
    if ($plugin_instance->deleteConstraint($policy_id)) {
      //TODO - Consider removing permissions here?
      drupal_set_message('Your constraint has been deleted');
    }
    else {
      drupal_set_message('There was an issue deleting your constraint, please try again');
    }
    $form_state->setRedirect('password_policy.settings');
  }
}