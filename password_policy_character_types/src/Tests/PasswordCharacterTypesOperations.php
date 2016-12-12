<?php

namespace Drupal\password_policy_character_types\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests password character types operations.
 *
 * @group password_policy_character_types
 */
class PasswordCharacterTypesOperations extends WebTestBase {

  /**
   * Modules to enable at the start of the test.
   *
   * @var array
   */
  public static $modules = [
    'password_policy_character_types',
    'password_policy',
  ];

  /**
   * Administrative user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(['administer site configuration']);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the management of the "character_types" constraint.
   */
  public function testPasswordCharacterTypesManagement() {
    // Create a policy and add a "character_types" constraint.
    $this->drupalPostForm('admin/config/security/password-policy/add', ['label' => 'Test policy', 'id' => 'test_policy'], 'Next');
    $this->drupalGet('admin/config/system/password_policy/constraint/add/test_policy/character_types');
    $this->assertText('Number of character types');

    $this->drupalPostForm(NULL, ['character_types' => 1], 'Save');
    $this->assertText('The number of character types must be between 2 and 4.');

    $this->drupalPostForm(NULL, ['character_types' => 5], 'Save');
    $this->assertText('The number of character types must be between 2 and 4.');

    $this->drupalPostForm(NULL, ['character_types' => 3], 'Save');
    $this->assertText('Minimum password character types: 3');
  }

}
