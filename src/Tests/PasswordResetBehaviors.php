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

  public static $modules = array(
    'user',
    'node',
    'dblog',
    'ctools',
    'config',
    'field',
    'datetime',
    'text',
    'field_ui',
    'password_policy');

  /**
   * Test password reset behaviors.
   */
  function testPasswordResetBehaviors() {
    global $base_url;

    // Create user with permission to create policy.
    $user1 = $this->drupalCreateUser(array(
      'administer site configuration',
      'administer users',
      'administer permissions',
      'manage password reset',
      'administer account settings',
      'administer user fields'));
    $this->drupalLogin($user1);

    // Assert that user attributes were created and unexpired
    $user_instance = entity_load('user', $user1->id());
    $this->assertNotNull($user_instance->get('field_last_password_reset')[0]->value, 'Last password reset was not set on user add');
    $this->assertEqual($user_instance->get('field_password_expiration')[0]->value, '0', 'Password expiration field is not set to zero on user add');

    // Create a new role.
    $rid = $this->drupalCreateRole(array());

    // Create user with test role.
    $user2 = $this->drupalCreateUser();
    $this->drupalGet("user/" . $user2->id() . "/edit");
    $edit = array();
    $edit['roles[' . $rid . ']'] = $rid;
    $this->drupalPostForm(NULL, $edit, t('Save'));

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
    $this->drupalPostForm(NULL, [], 'Next');
    // Set the roles for the policy.
    $edit = [
      'roles[' . $rid . ']' => $rid,
    ];
    $this->drupalPostForm(NULL, $edit, 'Finish');

    // Stage an expired date a few days late.
    $this->drupalGet("user/" . $user2->id() . "/edit");
    $edit = [
      'field_last_password_reset[0][value][date]' => date('Y-m-d', strtotime('-90 days'))
    ];
    $this->drupalPostForm(NULL, $edit, 'Save');

    $this->drupalLogout();

    // Run cron to trigger expiration.
    $this->cronRun();

    // This is currently bombing. username not found.
    $this->drupalPostForm('user/login', ['name'=>$user2->getUsername(), 'pass'=>$user2->getPassword()], 'Log in');
    $this->drupalGet('admin');
    $this->assertEqual($this->getAbsoluteUrl("user/" . $user2->id() . "/edit"), $this->getUrl(), "User should be sent to their account form after expiration -- ".$this->getUrl());
    $this->drupalLogout();


    // Create a new node type.
    $type1 = $this->drupalCreateContentType();
    // Create a node of that type.
    $node_title = $this->randomMachineName();
    $node_body = $this->randomMachineName();
    $edit = array(
      'type' => $type1->get('type'),
      'title' => $node_title,
      'body' => array(array('value' => $node_body)),
      'langcode' => 'en',
    );
    $node = $this->drupalCreateNode($edit);

    // Verify if user tries to go to node, they are forced back.
    $this->drupalGet('user/login');
    $this->drupalPostForm(NULL, ['name'=>$user2->getUsername(), 'pass'=>$user2->getPassword()], 'Log in');
    $this->drupalGet($node->url());
    $this->assertEqual($this->getAbsoluteUrl("user/" . $user2->id() . "/edit"), $this->getUrl(), "User should be sent back to their account form instead of the node");

    // Change password.
    $this->drupalGet("user/" . $user2->id() . "/edit");
    $edit = array();
    $edit['pass[pass1]'] = '1';
    $edit['pass[pass2]'] = '1';
    $edit['current_pass'] = $user2->pass_raw;
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Verify expiration is unset.
    $user_instance = entity_load('user', $user2->id());
    $this->assertEqual($user_instance->get('field_password_expiration')[0]->value, '0', 'Password expiration field should be empty after changing password');


    // Verify if user tries to go to node, they are allowed.
    $this->drupalGet($node->url());
    $this->assertEqual($this->getUrl(), $this->getAbsoluteUrl($node->url()), "User should have access to the node now");
    $this->drupalLogout();
  }
}
