<?php
/**
 * @file
 * Contains \Drupal\password_policy\Form\PasswordPolicyPluginSettingsForm.
 */

namespace Drupal\password_policy\Form;


use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordPolicyPluginSettingsForm extends FormBase {

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.password_policy.password_constrain'));
  }

  function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    // TODO: Implement getFormId() method.
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
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_id = NULL) {
    /** @var $plugin \Drupal\password_policy\PasswordConstraintInterface */
    $plugin = $this->manager->createInstance($plugin_id);
    $form = $plugin->buildForm($form, $form_state);
    $form['plugin_instance'] = [
      '#type' => 'value',
      '#value' => $plugin,
    ];
    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    /** @var $plugin \Drupal\password_policy\PasswordConstraintInterface */
    $plugin = $form_state->getValue('plugin_instance');
    $plugin->submitForm($form, $form_state);
    $values += $plugin->getConfiguration();

  }

}