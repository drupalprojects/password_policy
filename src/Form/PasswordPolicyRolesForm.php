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
    $cache_values = $form_state->get('wizard');
    $options = [];
    foreach ($this->storage->loadMultiple() as $role) {
      $options[$role->id()] = $role->label();
    }
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Apply to Roles'),
      '#description' => $this->t('Select Roles to which this policy applies.'),
      '#options' => $options,
      '#default_value' => isset($cache_values['roles']) ? $cache_values['roles'] : [],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cache_values = $form_state->get('wizard');
    $cache_values['roles'] = array_filter($form_state->getValue('roles'));
    $form_state->set('wizard', $cache_values);
  }

}