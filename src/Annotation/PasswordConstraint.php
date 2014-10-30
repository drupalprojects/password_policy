<?php

/**
 * @file
 * Contains \Drupal\PasswordPolicy\Annotation\PasswordConstraint.
 */

namespace Drupal\PasswordPolicy\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a password constraint annotation object.
 *
 * @Annotation
 */
class PasswordConstraint extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the constraint type.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $title;

	/**
	 * The description shown to users.
	 *
	 * @ingroup plugin_translatable
	 *
	 * @var \Drupal\Core\Annotation\Translation
	 */
	public $description;


	/**
	 * The error message shown if the constraint fails.
	 *
	 * @ingroup plugin_translatable
	 *
	 * @var \Drupal\Core\Annotation\Translation
	 */
	public $error_message;

	/**
	 * The path to the configuration screen.
	 *
	 * @var string
	 */
	public $config_path;

}
