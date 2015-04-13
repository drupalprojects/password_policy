<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //get policy
    $policy_id = '';
    //get current path
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);
    if (count($path_args) == 7 and is_numeric($path_args[6])) {
      $policy_id = $path_args[6];
      //load the policy
      $policy = db_select('password_policies', 'p')
        ->fields('p')
        ->condition('pid', $policy_id)
        ->execute()
        ->fetchObject();
    }

    $form = array(
      'pid' => array(
        '#type' => 'hidden',
        '#value' => (is_numeric($policy_id)) ? $policy_id : '',
      ),
      'policy_title' => array(
        '#type' => 'text',
        '#value' => (is_numeric($policy_id)) ? $policy->policy_text : '',
        '#required' => TRUE,
      ),
      'constraint_selectors' => array(
        '#type' => 'fieldset',
        '#title' => t('Constraints'),
        '#collapsible' => FALSE,
        '#tree' => TRUE,
      ),
      'constraints' => array(
        '#tree' => TRUE,
      ),
      'plugin_types' => array(
        '#tree' => TRUE,
      ),
      'submit' => array(
        '#type' => 'submit',
        '#value' => (is_numeric($policy_id)) ? t('Update policy') : t('Add policy'),
      ),
    );

    $constraint_count = 0;

    /**
     * PERMISSIONS DEFINED FROM PASSWORD RESET
     */
    $constraints = db_select('password_policy_reset', 'ppr')
      ->fields('ppr', array())
      ->execute()
      ->fetchAll();
    foreach ($constraints as $index => $constraint) {
      $form['constraint_selectors']['selector'.$constraint_count] = array(
        '#type' => 'checkbox',
        '#title' => t('Apply password reset policy: Reset after ' . $constraint->number_of_days . ' days'),
        '#default_value' => '1',
      );
      $form['constraints']['constraint'.$constraint_count] = array(
        '#type' => 'hidden',
        '#value' => $constraint->cid,
      );
      $form['plugin_types']['plugin'.$constraint_count] = array(
        '#type' => 'hidden',
        '#value' => 'password_reset',
      );
      $constraint_count++;
    }

    /**
     * PERMISSIONS DEFINED FROM PLUGINS
     */
    //load plugins
    $plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
    //dpm($plugin_manager);
    $plugins = $plugin_manager->getDefinitions();

    //create a perm per plugin
    foreach ($plugins as $plugin) {
      //dpm($plugin);
      $plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')
        ->createInstance($plugin['id']);

      $constraints = $plugin_instance->getConstraints();

      //dpm($plugin_instance);
      foreach ($constraints as $index => $policy_text) {
        //$form['constraint_selectors']['selector'.$constraint_count] = array(
        $form['constraint_selectors'][$constraint_count] = array(
          '#type' => 'checkbox',
          '#title' => t('Apply password policy: ' . $policy_text),
          '#default_value' => '1',
        );
        //$form['constraints']['constraint'.$constraint_count] = array(
        $form['constraints'][$constraint_count] = array(
          '#type' => 'hidden',
          '#value' => $index,
        );
        //$form['plugin_types']['plugin'.$constraint_count] = array(
        $form['plugin_types'][$constraint_count] = array(
          '#type' => 'hidden',
          '#value' => $plugin['id'],
        );
        $constraint_count++;
      }
    }

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
    $selectors = $form_state->getValue('constraint_selectors');
    $constraints = $form_state->getValue('constraints');
    $plugin_types = $form_state->getValue('plugin_types');
    if ($form_state->getValue('pid')) {
      db_update('password_policies')
        ->fields(array('policy_title' => $form_state->getValue('policy_title')))
        ->condition('pid', $form_state->getValue('pid'))
        ->execute();
      db_delete('password_policy_constraints')->execute();
      foreach($selectors as $index=>$selector){
        if($selector==TRUE) {
          $pid = $form_state->getValue('pid');
          $cid = $constraints[$index];
          $plugin_type = $plugin_types[$index];
          db_insert('password_policy_constraints')
            ->fields(array('pid', 'cid', 'plugin_type'))
            ->values(array(
              'pid' => $pid,
              'cid' => $cid,
              'plugin_type' => $plugin_type
            ))
            ->execute();
        }
      }
      drupal_set_message('Your policy has been updated');
    }
    else {
      db_insert('password_policies')
        ->fields(array('policy_title'))
        ->values(array('policy_title' => $form_state->getValue('policy_title')))
        ->execute();
      foreach($selectors as $index=>$selector){
        if($selector==TRUE) {
          $pid = $form_state->getValue('pid');
          $cid = $constraints[$index];
          $plugin_type = $plugin_types[$index];
          db_insert('password_policy_constraints')
            ->fields(array('pid', 'cid', 'plugin_type'))
            ->values(array(
              'pid' => $pid,
              'cid' => $cid,
              'plugin_type' => $plugin_type
            ))
            ->execute();
        }
      }
      drupal_set_message('Your policy has been added');
    }

    $form_state->setRedirect('password_policy.settings');
  }
}