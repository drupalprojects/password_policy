<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PasswordPolicySettingsForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_path;

    $form['introduction'] = array(
      '#type' => 'item',
      '#markup' => '<p>Password policies are a collection of constraints. Define constraints and then add the constraints to one or more policies.</p><p>To apply the policies, go to the permissions page and select the roles to apply to the policy.</p>',
    );


    //show each policy
    $form['policies_container'] = array(
      '#type' => 'fieldset',
      '#title' => 'Policies',
      '#description' => t('<p><a href="@policyaddpath">Add new password policy</a></p>', array('@policyaddpath' => $base_path . 'admin/config/security/password-policy/policy/add')),
    );

    //show each constraint
    $form['constraints_container'] = array(
      '#type' => 'fieldset',
      '#title' => 'Constraints',
    );

    $form['constraints_container']['constraints'] = array(
      '#type' => 'vertical_tabs',
      '#title' => '',
    );


    /**
     * PASSWORD RESET
     */
    $form['password_reset'] = array(
      '#type' => 'details',
      '#title' => 'Password reset',
      '#description' => '',
      '#group' => 'constraints'
    );

    //fieldset 1 will be for the policies
    $form['password_reset']['fs1'] = array(
      '#type' => 'fieldset',
      '#title' => 'Constraints',
    );
    //add a new reset policy
    $form['password_reset']['fs1']['add_link'] = array(
      '#type' => 'item',
      '#markup' => t('<p><a href="@resetaddpath">Add password reset constraint</a></p>', array('@resetaddpath' => $base_path . 'admin/config/security/password-policy/reset')),
    );

    //list reset policies
    $table_rows = array();
    $policy_rows = db_select("password_policy_reset", 'p')
      ->fields('p')
      ->execute()
      ->fetchAll();
    foreach ($policy_rows as $policy_object) {
      $table_rows[] = array(
        'label' => t('Password reset after @days days', array('@days' => $policy_object->number_of_days)),
        'update' => t('<a href="@resetupdatepath">Update constraint</a>', array('@resetupdatepath' => $base_path . 'admin/config/security/password-policy/reset/' . $policy_object->pid)),
        'delete' => t('<a href="@resetdeletepath">Delete constraint</a>', array('@resetdeletepath' => $base_path . 'admin/config/security/password-policy/reset/delete/' . $policy_object->pid)),
      );
    }

    $form['password_reset']['fs1']['policy_rows'] = array(
      '#title' => 'Available Constraints',
      '#type' => 'table',
      '#header' => array(t('Constraint Definition'), t(''), t('')),
      '#empty' => t('There are no constraints defined'),
      '#weight' => '4',
      '#rows' => $table_rows,
    );

    //manual password reset form
    $form['password_reset']['fs2'] = array(
      '#type' => 'fieldset',
      '#title' => 'Manual Password Reset',
    );

    $role_options = array();
    $roles = user_roles(TRUE);
    foreach ($roles as $role) {
      $role_options[$role->id()] = $role->label();
    }

    //TODO - Make this a confirm form
    $form['password_reset']['fs2']['roles'] = array(
      '#type' => 'checkboxes',
      '#title' => 'User roles',
      '#required' => TRUE,
      '#options' => $role_options,
    );

    $form['password_reset']['fs2']['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Force password reset now'
    );

    /**
     * CONSTRAINTS / PLUGINS
     */

    //constraint plugins
    $plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
    $plugin_manager->getDefinitions();
    $all_plugins = $plugin_manager->getDefinitions();
    //loop over each constraint
    $i = 0;
    foreach ($all_plugins as $plugin) {
      //show name as item
      $form['constraint' . $i] = array(
        '#type' => 'details',
        '#title' => $plugin['title'],
        '#description' => $plugin['description'],
        '#group' => 'constraints'
      );

      //show link to add policy
      $form['constraint' . $i]['add_policy'] = array(
        '#type' => 'item',
        //NOTE: The implementation below assumes use of PasswordPolicyPolicyForm. I am going in a new direction due to issues with the form API
        //'#markup' => t('<p>'.$plugin['description'].'<br/> <a href="@pathtopolicy">Add a new policy for this constraint</a></p>', array('@pathtopolicy'=>$base_path.'admin/config/security/password/policy/'.$plugin['id']))
        '#markup' => t('<a href="@pathtopolicy">Add a new constraint</a>', array('@pathtopolicy' => $base_path . $plugin['policy_path'] ))
      );

      //show table of policies
      $policy_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')
        ->createInstance($plugin['id']);
      $policy_rows = $policy_instance->getPolicies();

      $table_rows = array();
      foreach ($policy_rows as $policy_key => $policy_value) {
        $table_rows[] = array(
          'label' => $policy_value,
          'update' => t('<a href="' . $base_path . $plugin['policy_update_path'] . '">Update constraint</a>', array($plugin['policy_update_token'] => $policy_key)),
          'delete' => t('<a href="@deletepolicy">Delete constraint</a>', array('@deletepolicy' => $base_path . 'admin/config/security/password-policy/delete-constraint/' . $plugin['id'] . '/' . $policy_key)),
        );
      }

      $form['constraint' . $i]['available_policies'] = array(
        '#title' => 'Available Constraints',
        '#type' => 'table',
        '#header' => array(t('Constraint Definition'), t(''), t('')),
        '#empty' => t('There are no constraints defined for '.$plugin['title']),
        '#weight' => '4',
        '#rows' => $table_rows,
      );

      $i++;
    }
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $roles = $form_state->getValue('roles');
    foreach ($roles as $role_key => $role_value) {
      if ($role_value) {
        //get role users, gotta be a cleaner way to do this
        $users = array();
        if ($role_value == 'authenticated') { //get all users
          $user_rows = db_select('users', 'u')
            ->fields('u', array('uid'))
            ->execute()
            ->fetchAll();
        }
        else { //role specific
          $user_rows = db_select('users_roles', 'ur')
            ->fields('ur', array('uid'))
            ->condition('rid', $role_key)
            ->execute()
            ->fetchAll();
        }

        foreach ($user_rows as $user_row) {
          $users[] = $user_row->uid;
        }

        if (count($users)) {
          //run db update
          db_update('password_policy_user_reset')
            ->fields(array('expired' => '1', 'timestamp' => time()))
            ->condition('uid', $users, 'IN')
            ->execute();
        }
      }
    }

    drupal_set_message('The users of the selected roles will be forced to reset their passwords');
  }
}