<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PasswordPolicySettingsForm extends FormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'password_policy_settings_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state) {
		global $base_path;

		$form['introduction'] = array(
			'#type' => 'item',
			'#markup' => '<p>Password policies are defined through each constraint. A policy is an instance of the constraint. Click on the tab for the constraint below to create policies.</p><p>To apply the policies, go to the permissions page and select the roles to apply to the policy.</p>',
		);

		//show each constraint and table of its policies
		$form['constraints'] = array(
			'#type' => 'vertical_tabs',
			'#title' => 'Constraints & Policies',
		);


		//force password reset
		$form['password_reset'] = array(
			'#type' => 'details',
			'#title' => 'Password reset',
			'#description' => '',
			'#group' => 'constraints'
		);

		$form['password_reset']['fs1'] = array(
			'#type' => 'fieldset',
			'#title' => 'Policies',
			'#description' => 'Configure password reset policies',
		);

		$form['password_reset']['fs1']['add_link'] = array(
			'#type' => 'item',
			'#markup' => t('<p><a href="@resetpath">Add password reset policy</a></p>', array('@resetpath'=>$base_path.'admin/config/security/password/policy/reset')),
		);

		$form['password_reset']['fs2'] = array(
			'#type' => 'fieldset',
			'#title' => 'Force Password Reset',
			'#description' => 'Manually reset passwords',
		);

		//constraint plugins
		$plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
		$plugin_manager->getDefinitions();
		$all_plugins = $plugin_manager->getDefinitions();
		//loop over each constraint
		$i = 0;
		foreach($all_plugins as $plugin) {
			//show name as item
			$form['constraint'.$i] = array(
				'#type' => 'details',
				'#title' => $plugin['title'],
				'#description' => $plugin['description'],
				'#group' => 'constraints'
			);

			//show link to add policy
			$form['constraint'.$i]['add_policy'] = array(
				'#type' => 'item',
				//NOTE: The implementation below assumes use of PasswordPolicyPolicyForm. I am going in a new direction due to issues with the form API
				//'#markup' => t('<p>'.$plugin['description'].'<br/> <a href="@pathtopolicy">Add a new policy for this constraint</a></p>', array('@pathtopolicy'=>$base_path.'admin/config/security/password/policy/'.$plugin['id']))
				'#markup' => t('<a href="@pathtopolicy">Add a new policy for this constraint</a>', array('@pathtopolicy'=>$base_path.$plugin['policy_path']))
			);

			//show table of policies
			$policy_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')->createInstance($plugin['id']);
			$policy_rows = $policy_instance->getPolicies();

			$table_rows = array();
			foreach($policy_rows as $policy_key => $policy_value){
				$table_rows[] = array(
					'label' => $policy_value,
					'update' => t('<a href="'.$base_path.$plugin['policy_update_path'].'">Update policy</a>', array($plugin['policy_update_token']=>$policy_key)),
				);
			}

			$form['constraint'.$i]['available_policies'] = array(
				'#title' => 'Available Policies',
				'#type' => 'table',
				'#header' => array(t('Policy Definition'), t('')),
				'#empty' => t('There are no constraints for the selected user roles'),
				'#weight' => '4',
				'#rows' => $table_rows,
			);

			$i++;
		}
		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state) {
	}
}