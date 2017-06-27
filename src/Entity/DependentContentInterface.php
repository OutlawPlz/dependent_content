<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentInterface
 */

namespace Drupal\dependent_content\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a dependent content entity.
 */
interface DependentContentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface, EntityPublishedInterface, RevisionLogInterface {

  /**
   * Denotes that the dependent content is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the dependent content is published.
   */
  const PUBLISHED = 1;

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
   * @return \Drupal\dependent_content\Entity\DependentContentInterface
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
   * @return \Drupal\dependent_content\Entity\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setCreatedTime($timestamp);
}