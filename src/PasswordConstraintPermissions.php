<?php
namespace Drupal\password_policy;

/**
 * Class PasswordConstraintPermissions
 * @package Drupal\password_policy
 */
class PasswordConstraintPermissions {
	public function permissions() {
		$permissions = [];
		//load plugins
		$plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
		//dpm($plugin_manager);
		$plugins = $plugin_manager->getDefinitions();

		//create a perm per plugin
		foreach ($plugins as $plugin) {
			//dpm($plugin);
			$plugin_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')->createInstance($plugin['id']);

			$policies = $plugin_instance->getPolicies();

			//dpm($plugin_instance);
			foreach($policies as $policy_key => $policy_text) {
				$permission = 'enforce ' . $plugin['id'] . '.'.$policy_key.' constraint';
				$permissions[$permission] = [
					'title' => 'Apply password policy: '.$policy_text,
					'description' => $plugin['description'],
				];
			}
		}
		return $permissions;
	}
}