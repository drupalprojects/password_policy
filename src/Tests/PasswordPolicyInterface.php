<?php

/**
 * @file
 * Definition of Drupal\password_policy\Tests\PasswordPolicyInterface.
 */

namespace Drupal\password_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password policy UI.
 *
 * @group password_policy
 */
class PasswordPolicyInterface extends WebTestBase {

  public static $modules = array(
    'password_policy',
    'password_policy_length',
    'node'
  );

  /**
   * Test failing password and verify it fails.
   */
  function testOwnUserPasswords() {
    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer users',
      'administer permissions',
    ));

    $this->drupalLogin($user1);

    // Create role.
    $rid = $this->drupalCreateRole(array());

    // Set role for user.
    $edit = [
      'roles[' . $rid . ']' => $rid,
    ];
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));

    // Create new password reset policy for role.
    $this->drupalGet("admin/config/security/password-policy/add");
    $edit = [
      'id' => 'test',
      'label' => 'test',
      'password_reset' => '1',
    ];
    // Set reset and policy info.
    $this->drupalPostForm(NULL, $edit, 'Next');
    // No constraints needed for reset, continue.
    $edit = [
      'constraint' => 'password_policy_length_constraint',
    ];
    $this->drupalPostForm(NULL, $edit, 'Configure Constraint Settings');
    // Fill out length constraint.
    $edit = [
      'character_length' => '5'
    ];
    $this->drupalPostAjaxForm(NULL, $edit, 'Save', 'admin/config/system/password_policy/constraint/add/test/password_policy_length_constraint');
    // Go to the next page.
    $this->drupalPostForm(NULL, [], 'Next');
    // Set the roles for the policy.
    $edit = [
      'roles[' . $rid . ']' => $rid,
    ];
    $this->drupalPostForm(NULL, $edit, 'Finish');

    // Try failing password on form submit.
    $edit = array();
    $edit['current_pass'] = $user1->pass_raw;
    $edit['pass'] = '111';
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));

    $this->assertText('The password does not satisfy the password policies');

    // Try passing password on form submit.
    $edit = array();
    $edit['current_pass'] = $user1->pass_raw;
    $edit['pass'] = '111111';
    $this->drupalPostForm("user/" . $user1->id() . "/edit", $edit, t('Save'));

    $this->assertNoText('The password does not satisfy the password policies');
  }

}
