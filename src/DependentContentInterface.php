<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentInterface
 */

namespace Drupal\dependent_content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a dependent content entity.
 */
interface DependentContentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface   {

  /**
   * Gets the dependent content type.
   *
   * @return string
   *   The dependent content type.
   */
  public function getType();

  /**
   * Gets the dependent content label.
   *
   * @return string
   *   The dependent content label.
   */
  public function getLabel();

  /**
   * Sets the dependent content label.
   *
   * @param string $label
   *   The dependent content label.
   * @return \Drupal\dependent_content\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setLabel($label);

  /**
   * Gets the dependent content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the dependent content.
   */
  public function getCreatedTime();

  /**
   * Sets the dependent content creation timestamp.
   *
   * @param int $timestamp
   *   The dependent content creation timestamp.
   * @return \Drupal\dependent_content\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the dependent content published status indicator.
   *
   * Unpublished dependent content are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the dependent content is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a dependent content.
   *
   * @param bool $published
   *   TRUE to set this dependent content to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dependent_content\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setPublished($published);
}