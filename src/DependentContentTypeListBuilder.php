<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentTypeListBuilder
 */

namespace Drupal\dependent_content;

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
    $header['description'] = t('Description');

    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\dependent_content\DependentContentTypeInterface $entity
   *   The entity for this row of the list.
   * @return array
   *   A render array structure of fields for this entity.
   */
  public function buildRow(DependentContentTypeInterface $entity) {

    $row['label'] = $entity->label();
    $row['description'] = $entity->getDescription();

    return $row + parent::buildRow($entity);
  }
}