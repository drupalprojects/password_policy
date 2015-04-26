<?php
/**
 * @file
 * Provides Drupal\password_policy\PasswordConstraintInterface.
 */
namespace Drupal\password_policy;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormInterface;

interface PasswordConstraintInterface extends PluginInspectionInterface, ConfigurablePluginInterface, FormInterface {

  /**
   * Returns a true/false status as to if the password meets the requirements of the constraint.
   *
   * @param password
   *   The password entered by the end user
   *
   * @return boolean
   *   Whether or not the password meets the constraint in the plugin.
   */
  public function validate($constraint_id, $password);

  /**
   * Returns a translated string for the constraint title.
   * @return string
   */
  public function getTitle();

  /**
   * Returns a translated description for the constraint description.
   * @return string
   */
  public function getDescription();

  /**
   * Returns a translated error message for the constraint.
   * @return string
   */
  public function getErrorMessage();

  /**
   * Returns the path for adding constraints.
   * @return string
   */
  public function getConstraintPath();

  /**
   * Returns the path for updating existing constraints.
   * @return string
   */
  public function getConstraintUpdatePath();

  /**
   * Returns the token for the identifier in the update path.
   * @return string
   */
  public function getConstraintUpdateToken();

  /**
   * Returns an array of key value pairs.
   * @return array
   */
  public function getConstraints();

  /**
   * Deletes the specific constraint.
   * @return boolean
   */
  public function deleteConstraint($constraint_id);

  /**
   * Check if the specific constraint id exists.
   * @return boolean
   */
  public function constraintExists($constraint_id);

  /**
   * Returns the title of the constraint.
   * @return string
   */
  public function getConstraint($constraint_id);
}
