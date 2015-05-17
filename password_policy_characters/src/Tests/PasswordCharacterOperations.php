<?php

/**
 * @file
 * Definition of Drupal\password_policy_characters\Tests\PasswordCharacterOperations.
 */

namespace Drupal\password_policy_characters\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password character type operations.
 *
 * @group password_policy_characters
 */
class PasswordCharacterOperations extends WebTestBase {

  public static $modules = array('password_policy_characters', 'password_policy');

  /**
   * Test password length policy management.
   */
  function testPasswordLengthPolicyManagement() {
    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array('administer site configuration'));
    $this->drupalLogin($user1);

    // Create new password reset policy.
    $edit = array();
    $edit['character_length'] = '5';
    $this->drupalPostForm('admin/config/security/password-policy/password-length', $edit, t('Add policy'));

    // Get info for policy.
    $policy = db_select("password_policy_length_policies", 'p')
      ->fields('p', array())
      ->orderBy('p.pid', 'DESC')
      ->execute()
      ->fetchObject();

    $this->assertEqual($policy->character_length, '5', 'The character length must be 5 after insert');

    // Check user interface.
    $this->drupalGet('admin/config/security/password-policy');
    $this->assertText("Minimum character length 5");

    // Update the policy.
    $edit = array();
    $edit['character_length'] = '10';
    $this->drupalPostForm("admin/config/security/password-policy/password-length/" . $policy->pid, $edit, t('Update policy'));

    // Check user interface.
    $this->drupalGet('admin/config/security/password-policy');
    $this->assertText("Minimum character length 10");

    // Get info for policy.
    $policy = db_select("password_policy_length_policies", 'p')
      ->fields('p', array())
      ->condition('p.pid', $policy->pid)
      ->execute()
      ->fetchObject();

    $this->assertEqual($policy->character_length, '10', 'The character length must be 10 after update');

    // Delete the policy.
    $edit = array();
    $this->drupalPostForm("admin/config/security/password-policy/delete-policy/password_policy_length_constraint/" . $policy->pid, $edit, t('Confirm deletion of policy'));

    // Get info for policy.
    $policy = db_select("password_policy_length_policies", 'p')
      ->fields('p', array())
      ->condition('p.pid', $policy->pid)
      ->execute()
      ->fetchAll();

    $this->assertEqual(count($policy), 0, 'The policy must be deleted');

    // Check user interface.
    $this->drupalGet('admin/config/security/password-policy');
    $this->assertNoText("Minimum character length 10");
  }

}
