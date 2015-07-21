<?php
/**
 * Created by PhpStorm.
 * User: kris
 * Date: 4/24/15
 * Time: 4:14 PM
 */

namespace Drupal\password_policy\Form;


use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConstraintEdit extends FormBase {


  /**
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempstore;

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * @var string
   */
  protected $tempstore_id = 'password_policy.password_policy';

  /**
   * @var string;
   */
  protected $machine_name;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('user.shared_tempstore'), $container->get('plugin.manager.password_policy.password_constraint'));
  }

  function __construct(SharedTempStoreFactory $tempstore, PluginManagerInterface $manager) {
    $this->tempstore = $tempstore;
    $this->manager = $manager;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'password_policy_constraint_edit_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $constraint_id = NULL, $machine_name = NULL) {
    $this->machine_name = $machine_name;
    $cached_values = $this->tempstore->get($this->tempstore_id)->get($this->machine_name);
    /** @var $policy \Drupal\password_policy\Entity\PasswordPolicy */
    $policy = $cached_values['password_policy'];
    if (is_numeric($constraint_id)) {
      $id = $constraint_id;
      $constraint_id = $policy->getConstraint($id);
      $instance = $this->manager->createInstance($constraint_id['id'], $constraint_id);
    }
    else {
      $instance = $this->manager->createInstance($constraint_id, []);
    }
    /** @var $instance \Drupal\password_policy\PasswordConstraintInterface */
    $form = $instance->buildConfigurationForm($form, $form_state);
    if (isset($id)) {
      // Conditionally set this form element so that we can update or add.
      $form['id'] = [
        '#type' => 'value',
        '#value' => $id
      ];
    }
    $form['instance'] = [
      '#type' => 'value',
      '#value' => $instance
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#ajax' => [
        'callback' => [$this, 'ajaxSave'],
      ]
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $this->tempstore->get($this->tempstore_id)->get($this->machine_name);
    /** @var $policy \Drupal\password_policy\Entity\PasswordPolicy */
    $policy = $cached_values['password_policy'];
    $constraints = $policy->getConstraints();
    /** @var $instance \Drupal\password_policy\PasswordConstraintInterface */
    $instance = $form_state->getValue('instance');
    $instance->submitConfigurationForm($form, $form_state);
    if ($form_state->hasValue('id')) {
      $constraints[$form_state->getValue('id')] = $instance->getConfiguration();
    }
    else {
      $constraints[] = $instance->getConfiguration();
    }
    $policy->set('policy_constraints', $constraints);
    $this->tempstore->get($this->tempstore_id)->set($this->machine_name, $cached_values);
    $form_state->setRedirect('entity.password_policy.wizard.edit', ['machine_name' => $this->machine_name, 'step' => 'constraint']);
  }

  public function ajaxSave(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new RedirectCommand($this->url('entity.password_policy.wizard.edit', ['machine_name' => $this->machine_name, 'step' => 'constraint'])));
    $response->addCommand(new CloseModalDialogCommand());
    return $response;
  }

}
