<?php

namespace Drupal\PasswordPolicyLength\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class PasswordPolicyLengthSettingsForm extends ConfigFormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'password_policy_length_settings_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		$config = $this->config('password_policy_length.settings');

		$form = array();
		$form['character_length'] = array(
			'#type' => 'textfield',
			'#title' => t('Non-secure Base URL'),
			'#default_value' => $config->get('character_length'),
		);

		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		$config = $this->config('password_policy_length.settings')
			->set('character_length', $form_state['values']['character_length']);

		$config->save();

		parent::submitForm($form, $form_state);
		drupal_set_message('Password length settings have been stored');
	}
}