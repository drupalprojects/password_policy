<?php
/**
* @file
* Contains \Drupal\password_policy\Entity\PasswordPolicy.
*/

namespace Drupal\password_policy\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\password_policy\PasswordPolicyConstraintCollection;
use Drupal\password_policy\PasswordPolicyInterface;

/**
 * Defines a Password Policy configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "password_policy",
 *   label = @Translation("Password Policy"),
 *   handlers = {
 *     "list_builder" = "Drupal\password_policy\Controller\PasswordPolicyListBuilder",
 *     "form" = {
 *       "delete" = "Drupal\password_policy\Form\PasswordPolicyDeleteForm"
 *     },
 *     "wizard" = {
 *       "add" = "Drupal\password_policy\Wizard\PasswordPolicyWizard",
 *       "edit" = "Drupal\password_policy\Wizard\PasswordPolicyWizard"
 *     }
 *   },
 *   config_prefix = "password_policy",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/security/password-policy/{machine_name}/{step}",
 *     "delete-form" = "/admin/config/security/password-policy/policy/delete/{password_policy}",
 *     "collection" = "/admin/config/security/password-policy"
 *   }
 * )
 */
class PasswordPolicy extends ConfigEntityBase implements PasswordPolicyInterface, EntityWithPluginCollectionInterface {

  /**
  * The ID of the password policy.
  *
  * @var int
  */
  protected $id;

  /**
  * The policy title.
  *
  * @var string
  */
  protected $label;

  /**
   * The ID of the password reset option.
   *
   * @var int
   */
  protected $password_reset;

  /**
   * Constraint instance IDs.
   *
   * @var array
   */
  protected $policy_constraints;

  /**
   * Roles to which this policy applies.
   *
   * @var array
   */
  protected $roles;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraintPlugin($constraint_id) {
    return $this->getPluginCollection()->get($constraint_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraintPlugins() {
    if (!$this->constraintCollection) {
      $this->constraintCollection = new PasswordPolicyConstraintCollection(\Drupal::service('plugin.manager.password_policy.password_constraint'));
    }
    return $this->constraintCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array('policy_constraints' => $this->getConstraintPlugins());
  }
}