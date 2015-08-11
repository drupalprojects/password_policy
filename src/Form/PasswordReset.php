<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\RoleStorageInterface;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordReset extends FormBase {

  /**
   * @var \Drupal\user\RoleStorageInterface
   */
  protected $role_storage;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  protected $user_storage;

  public static function create(ContainerInterface $container) {
    /** @var $entity_manager \Drupal\Core\Entity\EntityManagerInterface */
    $entity_manager = $container->get('entity.manager');
    return new static ($entity_manager->getStorage('user_role'), $entity_manager->getStorage('user'));
  }

  function __construct(RoleStorageInterface $role_storage, UserStorageInterface $user_storage) {
    $this->role_storage = $role_storage;
    $this->user_storage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_reset_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    foreach ($this->role_storage->loadMultiple() as $role) {
      $options[$role->id()] = $role->label();
    }
    unset($options[AccountInterface::ANONYMOUS_ROLE]);
    $form['roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#description' => $this->t('Force password reset of selected roles.'),
      '#options' => $options,
    ];
    $form['exclude_myself'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude Myself'),
      '#description' => $this->t('Exclude your account if you are included in the roles.'),
      '#default_value' => '1',
    ];
    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $roles = $form_state->getValue('roles');
    $role_names = [];
    foreach ($roles as $role) {
      if ($role_obj = $this->role_storage->load($role)) {
        $role_names[] = $role_obj->label();
      } else {
        $role_names[] = $role;
      }

      $users = $this->user_storage->loadByProperties(['roles'=>$role]);
      foreach ($users as $user) {
        if ($form_state->getValue('exclude_myself')=='1' and $user->id()==\Drupal::currentUser()->id()) {
          continue;
        }
        $user->set('field_password_expiration', '1');
        $user->save();
      }
    }
    drupal_set_message($this->t('Reset the %roles roles.', array(
      '%roles' => implode(', ', $role_names),
    )));
    $form_state->setRedirectUrl(new Url('entity.password_policy.collection'));
  }

}
