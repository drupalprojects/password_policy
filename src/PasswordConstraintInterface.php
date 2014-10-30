<?php

/**
 * @file
 * Contains \Drupal\PasswordPolicy\PasswordConstraintInterface.
 */

namespace Drupal\PasswordPolicy;

/**
 * Defines the interface for pants types.
 */
interface PasswordConstraintInterface {

  /**
   * Returns a render array to display when the pants are on.
   *
   * @return array
   *   A render array.
   */
  public function viewPantsOn();

  /**
   * Returns a render array to display when the pants are off.
   *
   * @return array
   *   A render array.
   */
  public function viewPantsOff();

}
