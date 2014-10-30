<?php
/**
 * @file
 * Contains \Drupal\PasswordPolicy\PasswordConstraintManager.
 */

namespace Drupal\PasswordPolicy;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;


class PasswordConstraintPluginManager extends DefaultPluginManager {
	/**
	 * Constructs a new PantsTypeManager.
	 *
	 * @param \Traversable $namespaces
	 *   An object that implements \Traversable which contains the root paths
	 *   keyed by the corresponding namespace to look for plugin implementations.
	 * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
	 *   Cache backend instance to use.
	 * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
	 *   The module handler.
	 */
	public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
		parent::__construct('Plugin/PasswordConstraint', $namespaces, $module_handler, 'Drupal\PasswordPolicy\PasswordConstraintInterface', 'Drupal\PasswordPolicy\Annotation\PasswordConstraint');
		$this->alterInfo('password_policy_constraint_info');
		$this->setCacheBackend($cache_backend, 'password_policy_constraint');
	}

}