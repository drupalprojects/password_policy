<?php

namespace Drupal\password_policy\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;


class PasswordPolicyForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $password_policy = $this->entity;

    // Change page title for the edit operation
    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Update policy @name', array('@name' => $password_policy->label()));
    }

    /*
    //get policy
    $policy_id = '';
    //get current path
    $url = \Drupal\Core\Url::fromRoute('<current>');
    $current_path = $url->toString();
    $path_args = explode('/', $current_path);

    if (count($path_args) == 8 and is_numeric($path_args[7])) {
      $policy_id = $path_args[7];
      //load the policy
      $policy = db_select('password_policies', 'p')
        ->fields('p')
        ->condition('pid', $policy_id)
        ->execute()
        ->fetchObject();

      $policy_constraints = db_select('password_policy_constraints', 'p')
        ->fields('p')
        ->condition('pid', $policy_id)
        ->execute()
        ->fetchAll();
    }
    */

    $form += array(
      'policy_title' => array(
        '#type' => 'textfield',
        '#title' => $this->t('Policy Title'),
        '#default_value' => $password_policy->label(),
        '#required' => TRUE,
      ),
      'pid' => array(
        '#type' => 'machine_name',
        '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
        '#default_value' => $password_policy->id(),
        '#disabled' => !$password_policy->isNew(),
        '#machine_name' => array(
          'source' => array('policy_title'),
          'exists' => 'password_policy_load'
        ),
      ),
      'password_reset' => array(
        '#type' => 'select',
        '#title' => t('Password Reset Option'),
        '#options' => array('none'=>'None'),
        '#default_value' => $password_policy->password_reset,
        '#required' => FALSE,
      ),
      'policy_constraints' => array(
        '#type' => 'fieldset',
        '#title' => t('Constraints'),
        '#collapsible' => FALSE,
        '#tree' => TRUE,
      ),
    );

    /**
     * CONSTRAINTS DEFINED FROM PASSWORD RESET
     */
    $constraints = db_select('password_policy_reset', 'ppr')
    ->fields('ppr', array())
    ->execute()
    ->fetchAll();
    $reset_options = array();
    foreach ($constraints as $index => $constraint) {
      $key = $constraint->cid;
      $form['password_reset']['#options'][$key] = 'Expire after ' . $constraint->number_of_days . ' days';
    }

    /**
     * CONSTRAINTS DEFINED FROM PLUGINS
     */
    $plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint');
    $constraint_plugins = $plugin_instance->getDefinitions();

    $collection = $password_policy->getConstraintPlugins();

    $plugin_count = 0;
    foreach ($constraint_plugins as $constraint_plugin) {
      $plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')
        ->createInstance($constraint_plugin['id']);
      $constraints = $plugin_instance->getConstraints();
      $key = $constraint_plugin['id'];
      $form['policy_constraints'][$plugin_count] = array(
        '#type' => 'fieldset',
        '#title' => t($constraint_plugin['title']),
        '#collapsible' => FALSE,
        '#tree' => TRUE,
      );

      $form['policy_constraints'][$plugin_count]['key'] = array(
        '#type' => 'hidden',
        '#value' => $key,
      );

      $constraint_count = 0;
      foreach($constraints as $constraint_id => $constraint_text){
        $selected = FALSE;
        $form['policy_constraints'][$plugin_count]['constraints'][$constraint_count] = array(
          '#type' => 'checkbox',
          '#title' => t($constraint_text),
          '#default_value' => $selected,
          '#return_value' => $constraint_id,
        );
        $constraint_count++;
      }
      $plugin_count++;
    }







/*
    $constraint_count = 0;

    /**
     * PERMISSIONS DEFINED FROM PASSWORD RESET
     * /
    $constraints = db_select('password_policy_reset', 'ppr')
      ->fields('ppr', array())
      ->execute()
      ->fetchAll();
    foreach ($constraints as $index => $constraint) {
      $selected = FALSE;
      foreach($policy_constraints as $existing){
        if($existing->cid == $constraint->cid and $existing->plugin_type=='password_reset'){
          $selected = TRUE;
        }
      }
      $form['constraint_selectors']['selector'.$constraint_count] = array(
        '#type' => 'checkbox',
        '#title' => t('Apply password reset policy: Reset after ' . $constraint->number_of_days . ' days'),
        '#default_value' => $selected,
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
     * /
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
        $selected = FALSE;
        foreach($policy_constraints as $existing){
          if($existing->cid == $index and $existing->plugin_type==$plugin['id']){
            $selected = TRUE;
          }
        }
        $form['constraint_selectors']['selector'.$constraint_count] = array(
          '#type' => 'checkbox',
          '#title' => t('Apply password policy: ' . $policy_text),
          '#default_value' => $selected,
        );
        $form['constraints']['constraint'.$constraint_count] = array(
          '#type' => 'hidden',
          '#value' => $index,
        );
        $form['plugin_types']['plugin'.$constraint_count] = array(
          '#type' => 'hidden',
          '#value' => $plugin['id'],
        );
        $constraint_count++;
      }
    }*/

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    dpm($values);

    $password_policy = $this->entity;

    $status = $password_policy->save();

    if ($status) {
      // Setting the success message.
      drupal_set_message($this->t('Password policy @name has been saved', array(
        '@name' => $password_policy->label(),
      )));
    }
    else {
      drupal_set_message($this->t('There was an issue saving the password policy @name. Please try again', array(
        '@name' => $password_policy->label(),
      )));
    }


/*
    $selectors = $form_state->getValue('constraint_selectors');
    $constraints = $form_state->getValue('constraints');
    $plugin_types = $form_state->getValue('plugin_types');

    if ($form_state->getValue('pid')) {

    }


    $config = \Drupal::service('config.factory')->getEditable('password_policy.policy');




    if ($form_state->getValue('pid')) {
      $pid = $form_state->getValue('pid');
      db_update('password_policies')
        ->fields(array('policy_title' => $form_state->getValue('policy_title')))
        ->condition('pid', $pid)
        ->execute();
      db_delete('password_policy_constraints')->condition('pid', $pid)->execute();
      $c=0;
      foreach($selectors as $index=>$selector){
        if($selector==TRUE) {
          $cid = $constraints['constraint'.$c];
          $plugin_type = $plugin_types['plugin'.$c];
          db_insert('password_policy_constraints')
            ->fields(array('pid', 'cid', 'plugin_type'))
            ->values(array(
              'pid' => $pid,
              'cid' => $cid,
              'plugin_type' => $plugin_type
            ))
            ->execute();
        }
        $c++;
      }
      drupal_set_message('Your policy has been updated');
    }
    else {
      $pid = db_insert('password_policies')
        ->fields(array('policy_title'))
        ->values(array('policy_title' => $form_state->getValue('policy_title')))
        ->execute();
      $c=0;
      foreach($selectors as $index=>$selector){
        if($selector==TRUE) {
          $cid = $constraints['constraint'.$c];
          $plugin_type = $plugin_types['plugin'.$c];
          db_insert('password_policy_constraints')
            ->fields(array('pid', 'cid', 'plugin_type'))
            ->values(array(
              'pid' => $pid,
              'cid' => $cid,
              'plugin_type' => $plugin_type
            ))
            ->execute();
        }
        $c++;
      }
      drupal_set_message('Your policy has been added');
    }
*/

    $form_state->setRedirect('password_policy.settings');
  }
}