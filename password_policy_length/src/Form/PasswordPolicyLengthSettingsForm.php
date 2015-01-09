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
		$form = array();

		//get policy
		$policy_id = '';
		$path_args = explode('/', current_path());
		if(count($path_args)==6) {
			$policy_id = $path_args[5];
			//load the policy
			$policy = db_select('password_policy_length_policies', 'p')->fields('p')->condition('pid', $policy_id)->execute()->fetchObject();
		}

		$form['pid'] = array(
			'#type' => 'hidden',
			'#value' => (is_numeric($policy_id))?$policy_id:'',
		);

		$form['character_length'] = array(
			'#type' => 'textfield',
			'#title' => t('Number of characters'),
			'#default_value' => (is_numeric($policy_id))?$policy->character_length:'',
		);

		$form['submit'] = array(
			'#type'=>'submit',
			'#value'=>(is_numeric($policy_id))?t('Update policy'):t('Add policy'),
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
		//TODO - Add validation for unique number
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		if($form_state->getValue('pid')) {
			db_update('password_policy_length_policies')
				->fields(array('character_length' => $form_state->getValue('character_length')))
				->condition('pid', $form_state->getValue('pid'))
				->execute();
		} else {
			db_insert('password_policy_length_policies')
				->fields(array('character_length'))
				->values(array('character_length' => $form_state->getValue('character_length')))
				->execute();
		}
		drupal_set_message('Password length settings have been stored');
		$form_state->setRedirect('password_policy.settings');
	}
}