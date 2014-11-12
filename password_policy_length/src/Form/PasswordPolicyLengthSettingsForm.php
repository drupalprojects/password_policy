<?php

namespace Drupal\password_policy_length\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyLengthSettingsForm extends FormBase {


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
		//$config = \Drupal::config('password_policy_length.settings');

		$form = array();
		$form['character_length'] = array(
			'#type' => 'textfield',
			'#title' => t('Number of characters'),
			//'#default_value' => $config->get('character_length'),
		);

		$form['submit'] = array(
			'#type'=>'submit',
			'#value'=>'Add Policy',
		);

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {
		//TODO - Why is this not loading in the admin config page?
		if(!is_numeric($form_state->getValue('character_length')) or $form_state->getValue('character_length')<0) {
			$form_state->setErrorByName('character_length', $this->t('The character length must be a positive number.'));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		db_insert('password_policy_length_policies')
			->fields(array('character_length'))
			->values(array('character_length'=>$form_state->getValue('character_length')))
			->execute();
		drupal_set_message('Password length settings have been stored');
	}
}