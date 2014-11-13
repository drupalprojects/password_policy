<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class PasswordPolicyResetForm extends FormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'password_policy_reset_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state) {
		//get policy
		$policy_id = '';
		$path_args = explode('/', current_path());
		if(count($path_args)==7) {
			$policy_id = $path_args[6];
			//load the policy
			$policy = db_select('password_policy_reset', 'p')->fields('p')->condition('pid', $policy_id)->execute()->fetchObject();
		}

		$form = array(
			'pid' => array(
				'#type' => 'hidden',
				'#value' => (is_numeric($policy_id))?$policy_id:'',
			),
			'number_of_days' => array(
				'#type' => 'textfield',
				'#title' => t('Number of days'),
				'#default_value' => (is_numeric($policy_id))?$policy->number_of_days:'',
			),
			'submit' => array(
				'#type' => 'submit',
				'#value' => t('Add policy'),
			),
		);
		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if(!is_int($form_state->getValue('number_of_days')) or $form_state->getValue('number_of_days')<=0) {
			$form_state->setErrorByName('number_of_days', $this->t('The number of days must be a positive integer.'));
		}
		//TODO - Add validation for unique number
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {
		if($form_state->getValue('pid')) {
			db_update('password_policy_reset')
				->fields(array('number_of_days' => $form_state->getValue('number_of_days')))
				->condition('pid', $form_state->getValue('pid'))
				->execute();
		} else {
			db_insert('password_policy_reset')
				->fields(array('number_of_days'))
				->values(array('number_of_days' => $form_state->getValue('number_of_days')))
				->execute();
		}
		drupal_set_message('Your policy has been added');
	}
}