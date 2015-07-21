<?php
/**
 * @file
 * Contains \Drupal\password_policy\Form\PasswordPolicyConstraintForm.php
 */

namespace Drupal\password_policy\Form;


use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PasswordPolicyConstraintForm extends FormBase {

  /**
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $manager;

  /**
   * @var string
   */
  protected $machine_name;

  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.password_policy.password_constraint'));
  }

  function __construct(PluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'password_policy_constraint_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $this->machine_name = $cached_values['id'];
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $constraints = [];
    foreach ($this->manager->getDefinitions() as $plugin_id => $definition) {
      $constraints[$plugin_id] = (string) $definition['title'];
    }

    $form['add_constraint_title'] = [
      '#markup' => '<h2>Add Constraint</h2>',
    ];

    $form['constraint'] = [
      '#type' => 'select',
      '#options' => $constraints,
      '#prefix' => '<table style="width=100%"><tr><td>',
      '#suffix' => '</td>',
    ];
    $form['add'] = [
      '#type' => 'submit',
      '#name' => 'add',
      '#value' => t('Configure Constraint Settings'),
      '#ajax' => [
        'callback' => [$this, 'add'],
        'event' => 'click',
      ],
      '#prefix' => '<td>',
      '#suffix' => '</td></tr></table>',
    ];

    $form['constraint_list'] = [
      '#markup' => '<h2>Policy Constraints</h2>',
    ];

    $form['items'] = array(
      '#type' => 'markup',
      '#prefix' => '<div id="configured-constraints">',
      '#suffix' => '</div>',
      '#theme' => 'table',
      '#header' => array($this->t('Plugin Id'), $this->t('Summary'), $this->t('Operations')),
      '#rows' => $this->renderRows($cached_values),
      '#empty' => t('No constraints have been configured.')
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  public function add(array &$form, FormStateInterface $form_state) {
    $constraint = $form_state->getValue('constraint');
    $content = \Drupal::formBuilder()->getForm('\Drupal\password_policy\Form\ConstraintEdit', $constraint, $this->machine_name);
    $content['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $content['submit']['#attached']['drupalSettings']['ajax'][$content['submit']['#id']]['url'] = $this->url('entity.password_policy.constraint.add', ['machine_name' => $this->machine_name, 'constraint_id' => $constraint], ['query' => [FormBuilderInterface::AJAX_FORM_REQUEST => TRUE]]);
    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($this->t('Configure Required Context'), $content, array('width' => '700')));
    return $response;
  }

  /**
   * @param $cached_values
   *
   * @return array
   */
  public function renderRows($cached_values) {
    /** @var $policy \Drupal\password_policy\Entity\PasswordPolicy */
    $policy = $cached_values['password_policy'];
    $configured_conditions = array();
    foreach ($policy->getConstraints() as $row => $constraint) {
      /** @var $instance \Drupal\password_policy\PasswordConstraintInterface */
      $instance = $this->manager->createInstance($constraint['id'], $constraint);
      $build = array(
        '#type' => 'operations',
        '#links' => $this->getOperations('entity.password_policy.constraint', ['machine_name' => $cached_values['id'], 'constraint_id' => $row]),
      );
      $configured_conditions[] = array(
        $instance->getPluginId(),
        $instance->getSummary(),
        'operations' => [
          'data' => $build,
        ],
      );
    }
    return $configured_conditions;
  }

  protected function getOperations($route_name_base, array $route_parameters = array()) {
    $operations['edit'] = array(
      'title' => t('Edit'),
      'url' => new Url($route_name_base . '.edit', $route_parameters),
      'weight' => 10,
      'attributes' => array(
        'class' => array('use-ajax'),
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ),
    );
    $route_parameters['id'] = $route_parameters['constraint_id'];
    unset($route_parameters['constraint_id']);
    $operations['delete'] = array(
      'title' => t('Delete'),
      'url' => new Url($route_name_base . '.delete', $route_parameters),
      'weight' => 100,
      'attributes' => array(
        'class' => array('use-ajax'),
        'data-dialog-type' => 'modal',
        'data-dialog-options' => Json::encode([
          'width' => 700,
        ]),
      ),
    );
    return $operations;
  }

}
