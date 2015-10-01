<?php

/**
 * @file
 * Definition of Drupal\password_policy_history\Tests\PasswordHistoryBehaviors.
 */

namespace Drupal\password_policy_history\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Random;

/**
 * Tests password history
 *
 * @group password_policy_history
 */
class PasswordHistoryTests extends WebTestBase {

  public static $modules = array('password_policy', 'password_policy_history');

  /**
   * Test history constraint.
   */
  public function testHistoryConstraint() {
    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer users',
      'administer permissions',
    ));
    $this->drupalLogin($user1);

    $user2 = $this->drupalCreateUser();

    // Create role.
    $rid = $this->drupalCreateRole(array());

    // Set role for user.
    $edit = [
      'roles[' . $rid . ']' => $rid,
    ];
    $this->drupalPostForm("user/" . $user2->id() . "/edit", $edit, t('Save'));

    // Create new password reset policy for role.
    $this->drupalGet("admin/config/security/password-policy/add");
    $edit = [
      'id' => 'test',
      'label' => 'test',
      'password_reset' => '1',
    ];
    // Set reset and policy info.
    $this->drupalPostForm(NULL, $edit, 'Next');

    $this->assertText('No constraints have been configured.');

    // Fill out length constraint for test policy.
    $edit = [
      'history_repeats' => '1',
    ];
    $this->drupalPostForm('admin/config/system/password_policy/constraint/add/test/password_policy_history_constraint', $edit, 'Save');

    $this->assertText('password_policy_history_constraint');
    $this->assertText('Number of allowed repeated passwords: 1');

    // Go to the next page.
    $this->drupalPostForm(NULL, [], 'Next');
    // Set the roles for the policy.
    $edit = [
      'roles[' . $rid . ']' => $rid,
    ];
    $this->drupalPostForm(NULL, $edit, 'Finish');

    $this->assertText('Saved the test Password Policy.');

    // Change password to the same thing
    $edit = array(
      'pass[pass1]' => $user2->pass_raw,
      'pass[pass2]' => $user2->pass_raw,
    );
    $this->drupalPostAjaxForm("user/" . $user2->id() . "/edit", $edit, 'pass[pass1]');

    $this->assertText('The changes have been saved.');


    // Change password to the same thing again
    $edit = array(
      'pass[pass1]' => $user2->pass_raw,
      'pass[pass2]' => $user2->pass_raw,
    );
    $this->drupalPostAjaxForm("user/" . $user2->id() . "/edit", $edit, 'pass[pass1]');

    $this->assertText('You cannot use the same password more than 2 time(s) and this has been used 2 time(s)');

    $this->drupalLogout();
  }
}
