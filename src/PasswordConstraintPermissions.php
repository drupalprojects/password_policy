<?php
namespace Drupal\password_policy;

/**
 * Class PasswordConstraintPermissions
 * @package Drupal\password_policy
 */
class PasswordConstraintPermissions {
	public function permissions() {
		$permissions = [];
		/**
		 * PERMISSIONS DEFINED FROM PASSWORD RESET
		 */
		$policies = db_select('password_policy_reset', 'ppr')
			->fields('ppr', array())
			->execute()
			->fetchAll();
		foreach($policies as $policy) {
			$permission = 'enforce password_reset.'.$policy->pid.' constraint';
			$permissions[$permission] = [
				'title' => 'Apply password reset policy: Reset after '.$policy->number_of_days.' days',
				'description' => 'All passwords will be automatically forced to reset after the specified number of days',
			];
		}

		/**
		 * PERMISSIONS DEFINED FROM PLUGINS
		 */
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