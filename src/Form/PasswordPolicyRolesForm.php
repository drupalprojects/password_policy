<?php
/**
 * Created by PhpStorm.
 * User: kris
 * Date: 4/26/15
 * Time: 4:55 PM
 */

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordPolicyRolesForm extends FormBase {

  /**
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $storage;

  public static function create(ContainerInterface $container) {
    /** @var $entity_manager \Drupal\Core\Entity\EntityManagerInterface */
    $entity_manager = $container->get('entity.manager');
    return new static($entity_manager->getStorage('user_role'));
  }

  function __construct(RoleStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_roles_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    /** @var $policy \Drupal\password_policy\Entity\PasswordPolicy */
    $policy = $cached_values['password_policy'];
    $options = [];
    foreach ($this->storage->loadMultiple() as $role) {
      $options[$role->id()] = $role->label();
    }
    unset($options[AccountInterface::ANONYMOUS_ROLE]);
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Apply to Roles'),
      '#description' => $this->t('Select Roles to which this policy applies.'),
      '#options' => $options,
      '#default_value' => $policy->getRoles(),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    /** @var $policy \Drupal\password_policy\Entity\PasswordPolicy */
    $policy = $cached_values['password_policy'];
    $policy->set('roles', array_filter($form_state->getValue('roles')));
  }

}
