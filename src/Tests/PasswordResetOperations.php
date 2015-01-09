<?php

/**
 * @file
 * Definition of Drupal\password_policy\Tests\PasswordResetOperations.
 */

namespace Drupal\password_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password reset operations.
 *
 * @group password_policy
 */
class PasswordResetOperations extends WebTestBase {

	public static $modules = array('password_policy', 'node');

	/**
	 * Test password reset policy management.
	 */
	function testPasswordResetPolicyManagement() {
		// Create user with permission to create policy.
		$user1 = $this->drupalCreateUser(array('administer site configuration'));
		$this->drupalLogin($user1);

		// Create new password reset policy.
		$edit = array();
		$edit['number_of_days'] = '20';
		$this->drupalPostForm("admin/config/security/password-policy/reset", $edit, t('Add policy'));

		// Get info for policy.
		$policy = db_select("password_policy_reset", 'p')
			->fields('p', array())
			->orderBy('p.pid', 'DESC')
			->execute()
			->fetchObject();

		$this->assertEqual($policy->number_of_days, '20', 'The number of days must be 20 after insert');

		// Check user interface.
		$this->drupalGet('admin/config/security/password-policy');
		$this->assertText("Password reset after 20 days");

		// Update the policy.
		$edit = array();
		$edit['number_of_days'] = '10';
		$this->drupalPostForm("admin/config/security/password-policy/reset/".$policy->pid, $edit, t('Update policy'));

		// Check user interface.
		$this->drupalGet('admin/config/security/password-policy');
		$this->assertText("Password reset after 10 days");

		// Get info for policy.
		$policy = db_select("password_policy_reset", 'p')
			->fields('p', array())
			->condition('p.pid', $policy->pid)
			->execute()
			->fetchObject();

		$this->assertEqual($policy->number_of_days, '10', 'The number of days must be 10 after update');

		// Delete the policy.
		$edit = array();
		$this->drupalPostForm("admin/config/security/password-policy/reset/delete/".$policy->pid, $edit, t('Confirm deletion of policy'));

		// Get info for policy.
		$policy = db_select("password_policy_reset", 'p')
			->fields('p', array())
			->condition('p.pid', $policy->pid)
			->execute()
			->fetchAll();

		$this->assertEqual(count($policy), 0, 'The policy must be deleted');

		// Check user interface.
		$this->drupalGet('admin/config/security/password-policy');
		$this->assertNoText("Password reset after 10 days");
	}

}
