<?php

/**
 * @file
 * Contains \Drupal\password_policy\PasswordPolicyConstraintCollection.
 */

namespace Drupal\password_policy;
use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * Provides a collection of password policy constraints.
 */
class PasswordPolicyConstraintCollection extends DefaultLazyPluginCollection {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\password_policy\PasswordConstraintInterface
   */
  public function &get($instance_id) {
    return parent::get($instance_id);
  }

}
