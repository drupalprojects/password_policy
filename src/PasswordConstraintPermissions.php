<?php
namespace Drupal\password_policy;

/**
 * Class PasswordConstraintPermissions
 * @package Drupal\password_policy
 */
class PasswordConstraintPermissions {
  public function permissions() {
    $permissions = [];

    $policies = db_select('password_policies', 'p')
      ->fields('p', array())
      ->execute()
      ->fetchAll();

    foreach ($policies as $policy) {
      $permission = 'enforce password policy ' . $policy->pid;
      $permissions[$permission] = [
        'title' => 'Enforce policy: '.$policy->policy_title,
      ];
    }
    return $permissions;
  }
}