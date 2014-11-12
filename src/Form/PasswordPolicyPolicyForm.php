<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyPolicyForm extends FormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'password_policy_policy_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		//get plugin
		$user_input = $form_state->getUserInput();
		$policy_plugin = $user_input['plugin'];
		//load the plugin
		$plugin_manager = \Drupal::service('plugin.manager.password_policy.password_constraint');
		$all_plugins = $plugin_manager->getDefinitions();
		foreach($all_plugins as $plugin) {
			if($plugin->id == $policy_plugin){
				$form_id = $plugin->form_id;
			}
		}

		$form = \Drupal::formBuilder()->getForm($form_id);
		//add some hidden fields
		dpm($form);
		//add a new submit handler for password policy

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {

		/*if(!is_numeric($form_state->getValue('character_length')) or $form_state->getValue('character_length')<0) {
			$form_state->setErrorByName('character_length', $this->t('The character length must be a positive number.'));
		}*/
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		/*$config = \Drupal::config('password_policy_length.settings')
			->set('character_length', $form_state->getValue('character_length'));

		$config->save();

		parent::submitForm($form, $form_state);
		drupal_set_message('Password length settings have been stored');*/
	}
}