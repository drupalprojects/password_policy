<?php

/**
 * @file
 * Contains Drupal\password_policy\Entity\PasswordConstraint.
 */

namespace Drupal\password_policy\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\password_policy\PasswordConstraintInterface;

/**
 * Defines the PasswordConstraint entity.
 *
 * @ConfigEntityType(
 *   id = "password_constraint",
 *   label = @Translation("PasswordConstraint"),
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\password_policy\Form\PasswordConstraintForm",
 *       "edit" = "Drupal\password_policy\Form\PasswordConstraintForm",
 *       "delete" = "Drupal\password_policy\Form\PasswordConstraintDeleteForm"
 *     }
 *   },
 *   config_prefix = "password_constraint",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" = "entity.password_constraint.edit_form",
 *     "delete-form" = "entity.password_constraint.delete_form",
 *     "collection" = "entity.password_constraint.collection"
 *   }
 * )
 */
class PasswordConstraint extends ConfigEntityBase {
  /**
   * The PasswordConstraint ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The PasswordConstraint label.
   *
   * @var string
   */
  protected $label;

  /**
   * The PasswordConstraint plugin identifier.
   *
   * @var string
   */
  protected $plugin_id;

  /**
   * Get the plugin ID.
   *
   * return @var string
   */
  public function getPluginId(){
    return $this->plugin_id;
  }

  /**
   * Set the plugin ID.
   *
   * params @var string plugin_id
   */
  public function setPluginId($plugin_id){
    $this->plugin_id = $plugin_id;
  }


  /*
   * Load the plugin instance.
   *
   * return @var PasswordConstraintBase
   */
  public function getPluginInstance(){
    if($this->plugin_id){
      return \Drupal::service('plugin.manager.password_policy.password_constraint')
        ->createInstance($this->plugin_id);
    }
    return NULL;
  }
}
