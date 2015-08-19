<?php
/**
 * Created by PhpStorm.
 * User: kris
 * Date: 4/24/15
 * Time: 4:14 PM
 */

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConstraintDelete extends ConfirmFormBase {

  /**
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempstore;

  /**
   * @var string
   */
  protected $tempstore_id = 'password_policy.password_policy';

  /**
   * @var string;
   */
  protected $machine_name;

  /**
   * @var int;
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('user.shared_tempstore'));
  }

  /**
   * @param \Drupal\user\SharedTempStoreFactory $tempstore
   */
  function __construct(SharedTempStoreFactory $tempstore) {
    $this->tempstore = $tempstore;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'password_policy_constraint_delete_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $machine_name = NULL, $id = NULL) {
    $this->machine_name = $machine_name;
    $this->id = $id;

    $cached_values = $this->tempstore->get($this->tempstore_id)->get($this->machine_name);
    $form ['#title'] = $this->getQuestion($id, $cached_values);

    $form ['#attributes']['class'][] = 'confirmation';
    $form ['description'] = array('#markup' => $this->getDescription());
    $form [$this->getFormName()] = array('#type' => 'hidden', '#value' => 1);

    // By default, render the form using theme_confirm_form().
    if (!isset($form ['#theme'])) {
      $form ['#theme'] = 'confirm_form';
    }
    $form['actions'] = array('#type' => 'actions');
    $form['actions'] += $this->actions($form, $form_state);
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
    unset($constraints[$this->id]);
    $policy->set('policy_constraints', $constraints);
    $this->tempstore->get($this->tempstore_id)->set($this->machine_name, $cached_values);
    $form_state->setRedirect('entity.password_policy.wizard.edit', ['machine_name' => $this->machine_name, 'step' => 'constraint']);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion($id = NULL, $cached_values = NULL) {
    /** @var \Drupal\password_policy\Entity\PasswordPolicy $password_policy */
    $password_policy = $cached_values['password_policy'];
    $context = $password_policy->getConstraint($id);
    return $this->t('Are you sure you want to delete the @label constraint?', array(
      '@label' => $context['id'],
    ));
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {
    return new Url('entity.password_policy.wizard.edit', ['machine_name' => $this->machine_name, 'step' => 'constraint']);
  }

  /**
   * Provides the action buttons for submitting this form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    return array(
      'submit' => array(
        '#type' => 'submit',
        '#value' => $this->getConfirmText(),
        '#submit' => array(
          array($this, 'submitForm'),
        ),
      ),
      'cancel' => ConfirmFormHelper::buildCancelLink($this, $this->getRequest()),
    );
  }

}
