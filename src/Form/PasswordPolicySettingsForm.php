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
		//force password reset

		//show each constraint and table of its policies
		$form['constraints'] = array(
			'#type' => 'vertical_tabs',
		);

		$plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
		$plugin_manager->getDefinitions();
		$all_plugins = $plugin_manager->getDefinitions();
		//loop over each constraint
		$i = 0;
		foreach($all_plugins as $plugin) {
			//show name as item
			$form['constraint'.$i] = array(
				'#type' => 'fieldset',
				'#title' => $plugin['title'],
				'#group' => 'constraints'
			);

			//show link to add policy
			$form['constraint'.$i]['add_policy'] = array(
				'#type' => 'item',
				'#markup' => t('<p>'.$plugin['description'].'<br/> <a href="@pathtopolicy">Add a new policy for this constraint</a></p>', array('@pathtopolicy'=>$base_path.'admin/config/security/password/policy/'.$plugin['id']))
			);

			//show table of policies
			$policy_instance = \Drupal::service('plugin.manager.password_policy.password_constraint')->createInstance($plugin['id']);
			$policy_rows = $policy_instance->getPolicies();

			$table_rows = array();
			foreach($policy_rows as $policy_key => $policy_value){
				$table_rows[] = array(
					'label' => $policy_value,
					'update' => t('<a href="@pathtopolicy">Update policy</a>', array('@pathtopolicy'=>$base_path.'admin/config/security/password/policy/'.$plugin['id'].'/'.$policy_key)),
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
		/*$config = $this->config('securepages.settings')
			->set('enable', $form_state['values']['enable'])
			->set('switch', $form_state['values']['switch'])
			->set('basepath', $form_state['values']['basepath'])
			->set('basepath_ssl', $form_state['values']['basepath_ss;'])
			->set('pages', $form_state['values']['pages'])
			->set('ignore', $form_state['values']['ignore'])
			->set('roles', $form_state['values']['roles'])
			->set('forms', $form_state['values']['forms'])
			->set('debug', $form_state['values']['debug']);

		$config->save();

		parent::submitForm($form, $form_state);
		drupal_set_message('foobar');*/
	}
}