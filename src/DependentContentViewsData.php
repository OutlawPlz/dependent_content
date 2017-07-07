<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentViewsData
 */

namespace Drupal\dependent_content;

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

    $data['dependent_content_field_data']['published_admin'] = array(
      'title' => $this->t('Published status or admin user'),
      'help' => $this->t('Filters out unpublished content if the current user cannot view it.'),
      'filter' => array(
        'field' => 'published',
        'id' => 'dependent_content_published_admin',
        'label' => $this->t('Published status or admin user')
      )
    );

    $data['dependent_content']['dependent_content_bulk_form'] = array(
      'title' => $this->t('Bulk update'),
      'help' => $this->t('Add a form element that lets you run operations on multiple dependent contents.'),
      'field' => array(
        'id' => 'dependent_content_bulk_form'
      )
    );

    $data['dependent_content_field_revision']['revision_operations'] = array(
      'title' => $this->t('Revision operations links'),
      'help' => $this->t('Provides links to perform entity revision operations.'),
      'field' => array(
        'id' => 'dependent_content_revision_operations',
      )
    );

    return $data;
  }
}