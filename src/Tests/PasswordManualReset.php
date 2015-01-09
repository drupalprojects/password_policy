<?php

/**
 * @file
 * Definition of Drupal\password_policy\Tests\PasswordManualReset.
 */

namespace Drupal\password_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests manual password reset.
 *
 * @group password_policy
 */
class PasswordManualReset extends WebTestBase {

	public static $modules = array('password_policy', 'node');

	/**
	 * Test manual password reset.
	 */
	function testManualPasswordReset() {
		// Create user with permission to create policy.
		$user1 = $this->drupalCreateUser(array());

		// Create new role.
		$rid = $this->drupalCreateRole(array());

		// Create new admin user.
		$user2 = $this->drupalCreateUser(array('administer site configuration', 'administer users', 'administer permissions'));
		$this->drupalLogin($user2);

		// Update user 1 by adding role.
		$edit = array();
		$edit['roles['.$rid.']'] = $rid;
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));

		// Force reset users of new role.
		$edit = array();
		$edit['roles['.$rid.']'] = $rid;
		$this->drupalPostForm("admin/config/security/password-policy", $edit, t('Force password reset now'));

		// Verify expiration.
		$user_expiration = db_select("password_policy_user_reset", 'ur')
			->fields('ur', array())
			->condition('ur.uid', $user1->id())
			->execute()
			->fetchObject();
		$this->assertTrue($user_expiration->expired, 'User password is expired after manual reset');
	}

}
