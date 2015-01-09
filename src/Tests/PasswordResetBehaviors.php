<?php

/**
 * @file
 * Definition of Drupal\password_policy\Tests\PasswordResetBehaviors.
 */

namespace Drupal\password_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password reset behaviors.
 *
 * @group password_policy
 */
class PasswordResetBehaviors extends WebTestBase {

	public static $modules = array('password_policy', 'node');

	/**
	 * Test password reset behaviors.
	 */
	function testPasswordResetBehaviors() {
		global $base_url;

		// Create user with permission to create policy.
		$user1 = $this->drupalCreateUser(array('administer site configuration'));
		$this->drupalLogin($user1);

		// Assert that user row was created and unexpired
		$user_expiration = db_select("password_policy_user_reset", "ur")
			->fields("ur", array())
			->condition('uid', $user1->id())
			->execute()
			->fetchObject();

		$this->assertNotNull($user_expiration, 'User expiration record was not created after user add');

		// Create new password reset policy.
		$edit = array();
		$edit['number_of_days'] = '10';
		$this->drupalPostForm("admin/config/security/password-policy/reset", $edit, t('Add policy'));

		// Get latest ID to get policy.
		$id = db_select("password_policy_reset", 'p')
			->fields('p', array('pid'))
			->orderBy('p.pid', 'DESC')
			->execute()
			->fetchObject();

		// Create user with permission to create policy.
		$user2 = $this->drupalCreateUser(array('enforce password_reset.'.$id->pid.' constraint'));
		$uid = $user2->id();

		// Debugging.
		//$this->verbose('USER ID =>'.$uid);

		// Assert that user row was created and unexpired
		$user_expiration = db_select("password_policy_user_reset", 'ur')
			->fields('ur', array())
			->condition('ur.uid', $uid)
			->execute()
			->fetchObject();

		$this->assertNotNull($user_expiration, "User expiration record should exist");

		// Run cron to rebuild reset tables.
		$this->cronRun();

		// Debugging.
		//$this->verbose('CHECKING TIMESTAMP => '.$user_expiration->timestamp);

		// Assert that user row was created and unexpired
		$user_expiration = db_select("password_policy_user_reset", 'ur')
			->fields('ur', array())
			->condition('ur.uid', $uid)
			->execute()
			->fetchObject();

		$this->assertFalse($user_expiration->expired, 'User password was improperly expired after CRON and user creation');

		// Verify user can access any page.
		//$this->drupalLogin($user2);


		// Expire password.
		db_update("password_policy_user_reset")
			->fields(array('timestamp'=>strtotime("-15 days")))
			->condition('uid', $uid)
			->execute();

		// Log out.
		//$this->drupalLogout();

		// Run cron to rebuild reset tables.
		$this->cronRun();

		// Ensure table has expired.
		$user_expiration = db_select("password_policy_user_reset", 'ur')
			->fields('ur', array())
			->condition('ur.uid', $uid)
			->execute()
			->fetchObject();

		// Debugging.
		//$this->verbose('CHECKING TIMESTAMP => '.$user_expiration->timestamp);

		$this->assertTrue($user_expiration->expired, 'CRON did not expire the user');

		// Verify user is forced to go to their edit form
		$this->drupalLogin($user2);
		// NOTE: This and other variants did not work as expected. Likely due to forced redirect.
		//$this->assertUrl("user/" . $uid . "/edit");
		$url = str_replace($base_url, '', $this->getUrl());
		$this->assertEqual("/user/" . $uid . "/edit", $url);
		$this->drupalLogout();


		// Create a new node type.
		$type1 = $this->drupalCreateContentType();
		// Create a node of that type.
		$node_title = $this->randomMachineName();
		$node_body = $this->randomMachineName();
		$edit = array(
			'type' => $type1->type,
			'title' => $node_title,
			'body' => array(array('value' => $node_body)),
			'langcode' => 'en',
		);
		$node = $this->drupalCreateNode($edit);

		// Verify if user tries to go to node, they are forced back.
		$this->drupalLogin($user2);
		$this->drupalGet($node->url());
		// NOTE: This and other variants did not work as expected. Not sure why not.
		//$this->assertUrl("user/" . $uid . "/edit");
		$url = str_replace($base_url, '', $this->getUrl());
		$this->assertEqual("/user/" . $uid . "/edit", $url);

		// Change password.
		$edit = array();
		$edit['pass'] = '1';
		$edit['current_pass'] = $user2->pass_raw;
		$this->drupalPostForm("user/" . $uid . "/edit", $edit, t('Save'));

		// Verify expiration is unset.
		$user_expiration = db_select("password_policy_user_reset", 'ur')
			->fields('ur', array())
			->condition('ur.uid', $uid)
			->execute()
			->fetchObject();

		$this->assertFalse($user_expiration->expired, 'User password was still expired after update');


		// Verify if user tries to go to node, they are allowed.
		$this->drupalGet($node->url());
		$this->assertUrl($node->url());
		$this->drupalLogout();
	}
}
