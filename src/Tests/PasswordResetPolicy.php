<?php

/**
 * @file
 * Definition of Drupal\password_policy\Tests\PasswordResetPolicy.
 */

namespace Drupal\password_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password reset.
 *
 * @group password_policy
 */
class PasswordResetPolicy extends WebTestBase {
	function setUp(){
		parent::setUp();

		$this->drupalModuleEnable('password_policy');
	}

	/**
	 * Test password reset behaviors.
	 */
	function testPasswordResetBehaviors() {

		// Create user with permission to create policy.
		$user1 = $this->drupalCreateUser(array('administer site configuration'));
		$this->drupalLogin($user1);

		// Create new password reset policy.
		$edit = array();
		$edit['number_of_days'] = '10';
		$this->drupalPostForm("admin/config/security/password-policy/reset", $edit, t('Add policy'));

		// Get latest ID to get policy.
		$id = db_select("password_policy_reset", 'p')
			->fields('p', array('pid'))
			->orderBy('p.pid', 'DESC')
			->range(1, 1)
			->execute()
			->fetchObject();

		// Create user with permission to create policy.
		$user1 = $this->drupalCreateUser(array('enforce password_reset.'.$id->pid.' constraint'));

		// Assert that user row was created and unexpired
		$user_expiration = db_select("password_policy_user_reset")
			->condition('uid', $user1->id())
			->execute()
			->fetchObject();

		$this->assertNotNull($user_expiration, 'User expiration record was not created after user add');

		// Run cron to rebuild reset tables.
		$this->cronRun();

		// Assert that user row was created and unexpired
		$user_expiration = db_select("password_policy_user_reset")
			->condition('uid', $user1->id())
			->execute()
			->fetchObject();

		$this->assertFalse($user_expiration->expired, 'User password was improperly expired after CRON and user creation');

		// Verify user can access any page.
		$this->drupalLogin($user1);

		// Expire password.
		db_update("password_policy_user_reset")
			->fields(array('timestamp'=>strtotime("-5 days")))
			->condition('uid', $user1->id())
			->execute();

		// Log out.
		$this->drupalLogout();

		// Run cron to rebuild reset tables.
		$this->cronRun();

		// Ensure table has expired.
		$user_expiration = db_select("password_policy_user_reset")
			->condition('uid', $user1->id())
			->execute()
			->fetchObject();

		$this->assertTrue($user_expiration->expired, 'CRON did not expire the user');

		// Verify user is forced to go to their edit form
		$this->drupalLogin($user1);
		$this->assertPath("user/" . $user1->id() . "/edit");

		// Create a new node
		$this->drupalCreateContentType(array('type'=>'foo'));
		$this->drupalCreateNode(array('type'=>'foo', 'path'=>'foo/bar'));

		// Verify if user tries to go to node, they are forced back
		$this->drupalGet('foo/bar');
		$this->assertUrl("user/" . $user1->id() . "/edit");

		// Change password.
		$edit = array();
		$edit['pass'] = '1';
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));

		// Verify expiration is unset.
		$user_expiration = db_select("password_policy_user_reset")
			->condition('uid', $user1->id())
			->execute()
			->fetchObject();

		$this->assertFalse($user_expiration->expired, 'User password was still expired after update');









		/*
		// Test that password fails for testuser1.
		$edit = array();
		$edit['pass'] = '1';
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t('The username %name is already taken.', array('%name' => $edit['name'])));

		// Check that filling out a single password field does not validate.
		$edit = array();
		$edit['pass[pass1]'] = '';
		$edit['pass[pass2]'] = $this->randomMachineName();
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertText(t("The specified passwords do not match."), 'Typing mismatched passwords displays an error message.');

		$edit['pass[pass1]'] = $this->randomMachineName();
		$edit['pass[pass2]'] = '';
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertText(t("The specified passwords do not match."), 'Typing mismatched passwords displays an error message.');

		// Test that the error message appears when attempting to change the mail or
		// pass without the current password.
		$edit = array();
		$edit['mail'] = $this->randomMachineName() . '@new.example.com';
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t("Your current password is missing or incorrect; it's required to change the %name.", array('%name' => t('Email address'))));

		$edit['current_pass'] = $user1->pass_raw;
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t("The changes have been saved."));

		// Test that the user must enter current password before changing passwords.
		$edit = array();
		$edit['pass[pass1]'] = $new_pass = $this->randomMachineName();
		$edit['pass[pass2]'] = $new_pass;
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t("Your current password is missing or incorrect; it's required to change the %name.", array('%name' => t('Password'))));

		// Try again with the current password.
		$edit['current_pass'] = $user1->pass_raw;
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t("The changes have been saved."));

		// Make sure the changed timestamp is updated.
		$this->assertEqual($user1->getChangedTime(), REQUEST_TIME, 'Changing a user sets "changed" timestamp.');

		// Make sure the user can log in with their new password.
		$this->drupalLogout();
		$user1->pass_raw = $new_pass;
		$this->drupalLogin($user1);
		$this->drupalLogout();

		// Test that the password strength indicator displays.
		$config = \Drupal::config('user.settings');
		$this->drupalLogin($user1);

		$config->set('password_strength', TRUE)->save();
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertRaw(t('Password strength:'), 'The password strength indicator is displayed.');

		$config->set('password_strength', FALSE)->save();
		$this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));
		$this->assertNoRaw(t('Password strength:'), 'The password strength indicator is not displayed.');
		*/
	}

}
