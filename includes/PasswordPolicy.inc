<?php
/**
 * @file
 * Contains PasswordPolicy.
 */

/**
 * Class PasswordPolicy
 *
 * This class is used for managing password Policies
 */
class PasswordPolicy {
  public $name;
  public $config;
  public $items;
  public $cache;

  /**
   * PasswordPolicy::__construct
   *
   * @param Object
   *   Database object as returned by ctools_export_crud_load().
   */
  function __construct($policy) {
    ctools_include('export');
    $this->name = $policy->name;
    $this->config = isset($policy->config) ? unserialize($policy->config) : array();

    // Load all constraint objects.
    $this->load_plugins();
  }

  /**
   * PasswordPolicy::admin_form()
   *
   * used to produce administation form for this policy
   *
   * Mostly just asking each plugin for its part of the form
   */
  function admin_form(&$form, &$form_state) {
    // Condition settings.
    $form['condition_title'] = array(
      '#type' => 'item',
      '#title' => t('Conditions'),
    );
    $form['condition'] = array(
      '#type' => 'vertical_tabs',
    );

    // Constraint settings.
    $form['constraint_title'] = array(
      '#type' => 'item',
      '#title' => t('Constraints'),
    );
    $form['constraint'] = array(
      '#type' => 'vertical_tabs',
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'password_policy') . '/password_policy.js'),
      ),
    );

    // Item settings.
    $form['item_title'] = array(
      '#type' => 'item',
      '#title' => t('Other settings'),
    );
    $form['item'] = array(
      '#type' => 'container',
    );

    foreach ($this->items as $item) {
      $parent = $item->is_type('condition') ? 'condition' : ($item->is_type('constraint') ? 'constraint' : 'item');
      $form[$parent]  = $form[$parent] + $item->admin_form($form, $form_state);
    }

    return $form;
  }

  /**
   * PasswordPolicy::admin_from_submit()
   *
   * we need to move our config to $form_state['item']->config;
   * each constraint should parse the form_state and update the config
   */
  function admin_form_submit($form, &$form_state) {
    foreach ($this->items as $item) {
      $item->admin_form_submit($form, $form_state);
    }
    $form_state['item']->config = serialize($this->config);
  }

  function message() {
    $items = array();
    foreach ($this->active_constraints() as $constraint) {
      $items = array_merge($items, $constraint->message());
    }
    return $items;
  }

  /**
   * PasswordPolicy::constraint()
   *
   * Constraint that a password complies with the policy
   */
  function constraint($pass, $account) {
    $errors = array();
    foreach ($this->active_constraints() as $constraint) {
      if (!$constraint->constraint($pass, $account)) {
        $errors = array_merge($errors, $constraint->fail_message($pass, $account));
      }
    }
    return $errors;
  }


  /**
   * PasswordPolicy::active_constraints()
   *
   * Return all constraints that are active.
   */
  function active_constraints() {
    $constraints = array();
    foreach ($this->get_items('constraint') as $constraint) {
      $constraints[] = $constraint;
    }
    return $constraints;
  }

  /**
   * PasswordPolicy::constraint_condition()
   */
  function match($account) {
    $does_match = FALSE;
    foreach ($this->get_items('condition') as $condition) {
      $does_match = $does_match || $condition->match($account);
    }
    return $does_match;
  }

  /**
   * PasswordPolicy::cron()
   *
   * Run cron for each of the items that is a cron item
   */
  function cron() {
    foreach ($this->get_items('cron') as $item) {
      if ($item->is_active()) {
        $item->cron();
      }
    }
  }

  /**
   * PasswordPolicy::init()
   *
   * Run init for each of the items that is a cron item
   */
  function init($account) {
    foreach ($this->get_items('init') as $item) {
      $item->init($account);
    }
  }

  /**
   * PasswordPolicy::load_plugins()
   *
   * Load all constraint and condition plugins and instantiate object
   * for each saving them in to the module.
   */
  function _load_plugin($info) {
    if (!isset($this->config[$info['name']])) {
      $this->config[$info['name']] = array();
    }
    $this->items[$info['name']] = PasswordPolicyConstraint::factory($info, $this);
  }

  /**
   * get all items of a particular type
   */
  function get_items($type) {
    $this->cache[$type] = isset($this->cache[$type]) ? $this->cache[$type] : array();
    if (empty($this->cache[$type])) {
      foreach ($this->items as $item) {
        if ($item->is_type($type)) {
          $this->cache[$type][] = $item;
        }
      }
    }
    return $this->cache[$type];
  }

  function load_plugins() {
    ctools_include('plugins');
    foreach (ctools_get_plugins('password_policy', 'constraint') as $info) {
      $this->_load_plugin($info);
    }

    foreach (ctools_get_plugins('password_policy', 'condition') as $info) {
      $this->_load_plugin($info);
    }
    foreach (ctools_get_plugins('password_policy', 'item') as $info) {
      $this->_load_plugin($info);
    }
  }

  static function policy($name = NULL) {
    if (isset($name)) {
      ctools_include('export');
      $policy = ctools_export_crud_load('password_policy', $name);
    }
    else {
      $policy = ctools_export_crud_new('password_policy');
    }
    return new PasswordPolicy($policy);
  }

  static function all_policies() {
    ctools_include('export');
    $policies = array();
    $infos = ctools_export_crud_load_all('password_policy');
    foreach ($infos as $name => $info) {
      $policies[$name] = new PasswordPolicy($info);
    }
    return $policies;
  }
  static function matched_policies($account) {
    $policies = array();
    foreach (PasswordPolicy::all_policies() as $policy) {
      if ($policy->match($account)) {
        $policies[] = $policy;
      }
    }
    return $policies;
  }
}
