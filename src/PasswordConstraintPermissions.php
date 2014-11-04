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
			//$plugin_instance = $plugin_manager::createInstance($plugin->id);

			//dpm($plugin_instance);

			$permission = 'enforce ' . $plugin->id . ' constraint';
			$permissions[$permission] = [
				'title' => 'Enforce constraint: ' . $plugin['title'],
				'description' => $plugin['description'],
			];
		}
		return $permissions;
	}
}