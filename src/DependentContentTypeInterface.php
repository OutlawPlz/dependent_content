<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentTypeInterface
 */

namespace Drupal\dependent_content;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a dependent content type entity.
 */
interface DependentContentTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this node type.
   */
  public function getDescription();
}