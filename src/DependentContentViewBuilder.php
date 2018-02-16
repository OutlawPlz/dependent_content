<?php

namespace Drupal\dependent_content;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

class DependentContentViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $entity */
    parent::alterBuild($build, $entity, $display, $view_mode);
    // TODO Manage revisions as NodeViewBuilder does.
    $build['#contextual_links'] = array(
      $entity->getEntityTypeId() => array(
        'route_parameters' => array(
          $entity->getEntityTypeId() => $entity->id()
        )
      )
    );
  }
}