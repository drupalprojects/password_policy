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
*   fieldable = FALSE,
*   controllers = {
*     "list_builder" = "Drupal\flower\FlowerListBuilder",
*     "form" = {
*       "add" = "Drupal\password_policy\Form\PasswordPolicyForm",
*       "edit" = "Drupal\password_policy\Form\PasswordPolicyForm",
*       "delete" = "Drupal\password_policy\Form\PasswordPolicyDeleteForm"
*     }
*   },
*   config_prefix = "password_policy",
*   admin_permission = "administer site configuration",
*   entity_keys = {
*     "id" = "pid",
*     "label" = "policy_title"
*   },
*   links = {
*     "edit-form" = "password_policy.update_policy",
*     "delete-form" = "password_policy.delete_policy"
*   }
* )
*/
class PasswordPolicy extends ConfigEntityBase implements PasswordPolicyInterface, EntityWithPluginCollectionInterface {

  /**
  * The ID of the password policy.
  *
  * @var int
  */
  protected $pid;

  /**
  * The policy title.
  *
  * @var string
  */
  protected $policy_title;

  /**
   * The collection that holds the constraints for this entity.
   *
   * @var \Drupal\password_policy\PasswordPolicyConstraintCollection
   */
  protected $constraintCollection;

  /**
   * Constraint instance IDs.
   *
   * @var array
   */
  protected $constraints = array();

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->pid;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->policy_title;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraint($constraint_id) {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    if (!$this->constraintCollection) {
      $this->constraintCollection = new PasswordPolicyConstraintCollection(\Drupal::service('plugin.manager.password_policy.password_constraint'), $this->constraints);
    }
    return $this->constraintCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return array('constraints' => $this->getEffects());
  }
}