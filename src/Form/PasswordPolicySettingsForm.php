<?php

namespace Drupal\password_policy\Form;


use Drupal\Core\Form\ConfigFormBase;

class PasswordPolicySettingsForm extends ConfigFormBase {


	/**
	 * {@inheritdoc}
	 */
	public function getFormId() {
		return 'password_policy_settings_form';
	}

	public function buildForm(array $form, array &$form_state) {
		$config = $this->config('password_policy.settings');

		$form = array();
		$form['enable'] = array(
			'#type' => 'radios',
			'#title' => t('Enable Secure Pages'),
			'#default_value' => $config->get('enable'),
			'#options' => array(t('Disabled'), t('Enabled')),
			// @TODO '#disabled' => !securepages_test(),
			'#description' => t('To start using secure pages this setting must be enabled. This setting will only be able to changed when the web server has been configured for SSL.<br />If this test has failed then go <a href="!url">here</a>', array('!url' => preg_replace(';^http://;i', 'https://', url($_GET['q'], array('absolute' => TRUE))))),
		);
		$form['switch'] = array(
			'#type' => 'checkbox',
			'#title' => t('Switch back to http pages when there are no matches'),
			'#return_value' => TRUE,
			'#default_value' => $config->get('switch'),
		);

		$form['basepath'] = array(
			'#type' => 'textfield',
			'#title' => t('Non-secure Base URL'),
			'#default_value' => $config->get('basepath'),
		);

		$form['basepath_ssl'] = array(
			'#type' => 'textfield',
			'#title' => t('Secure Base URL'),
			'#default_value' => $config->get('basepath_ssl'),
		);

		$form['secure'] = array(
			'#type' => 'radios',
			'#title' => t('Pages which will be be secure'),
			'#default_value' => $config->get('secure'),
			'#options' => array(t('Make secure every page except the listed pages.'), t('Make secure only the listed pages.')),
		);

		$form['pages'] = array(
			'#type' => 'textarea',
			'#title' => t('Pages'),
			'#default_value' => $config->get('pages'),
			'#cols' => 40,
			'#rows' => 5,
			'#description' => t("Enter one page per line as Drupal paths. The '*' character is a wildcard. Example paths are '<em>blog</em>' for the main blog page and '<em>blog/*</em>' for every personal blog. '<em>&lt;front&gt;</em>' is the front page."),
		);
		$form['ignore'] = array(
			'#type' => 'textarea',
			'#title' => t('Ignore pages'),
			'#default_value' => $config->get('ignore'),
			'#cols' => 40,
			'#rows' => 5,
			'#description' => t("The pages listed here will be ignored and be either returned in http or https. Enter one page per line as Drupal paths. The '*' character is a wildcard. Example paths are '<em>blog</em>' for the blog page and '<em>blog/*</em>' for every personal blog. '<em>&lt;front&gt;</em>' is the front page."),
		);

		$role_options = array();
		$roles = user_roles(TRUE);
		foreach ($roles as $role) {
			$role_options[$role->id()] = $role->label();
		}

		$form['roles'] = array(
			'#type' => 'checkboxes',
			'#title' => 'User roles',
			'#description' => t('Users with the chosen role(s) are always redirected to https, regardless of path rules.'),
			'#options' => $role_options, //array_map('\Drupal\Core\Utility\String::checkPlain', $role_options),
			'#default_value' => $config->get('roles'),
		);
		$form['forms'] = array(
			'#type' => 'textarea',
			'#title' => t('Secure forms'),
			'#default_value' => $config->get('forms'),
			'#cols' => 40,
			'#rows' => 5,
			'#description' => t('List of form ids which will have the https flag set to TRUE.'),
		);
		$form['debug'] = array(
			'#type' => 'checkbox',
			'#title' => t('Enable Debugging'),
			'#default_value' => $config->get('debug'),
			'#description' => t('Turn on debugging to allow easier testing of settings'),
		);

		return parent::buildForm($form, $form_state);
	}

	public function submitForm(array &$form, array &$form_state) {
		$config = $this->config('securepages.settings')
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
		drupal_set_message('foobar');
	}
}