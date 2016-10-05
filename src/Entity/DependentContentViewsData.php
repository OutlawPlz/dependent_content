<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Entity\DependentContentViewsData
 */

namespace Drupal\dependent_content\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides views data for dependent content entities.
 */
class DependentContentViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * Returns views data for the entity type.
   *
   * @return array
   *   Views data in the format of hook_views_data().
   */
  public function getViewsData() {

    $data = parent::getViewsData();

    $data['dependent_content']['status_extra'] = array(
      'title' => $this->t('Published status or admin user'),
      'help' => $this->t('Filters out unpublished content if the current user cannot view it.'),
      'filter' => array(
        'field' => 'status',
        'id' => 'dependent_content_status',
        'label' => $this->t('Published status or admin user')
      )
    );

    return $data;
  }
}