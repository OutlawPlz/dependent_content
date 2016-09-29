<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentTypeListBuilder
 */

namespace Drupal\dependent_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

class DependentContentTypeListBuilder extends EntityListBuilder {

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {

    $header['label'] = t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for this row of the list.
   * @return array
   *   A render array structure of fields for this entity.
   */
  public function buildRow(EntityInterface $entity) {

    $row['label'] = $entity->label();

    return $row + parent::buildRow($entity);
  }
}