<?php
/**
 * @file
 * Contains dependent_content.page.inc
 */

Use \Drupal\Core\Link;

/**
 * Prepare variables for list of available dependent content type templates.
 *
 * @param $variables
 *   An associative array containing:
 *   - content: An array of dependent content types.
 */
function template_preprocess_dependent_content_add_list(&$variables) {

  $variables['types'] = array();

  if (!empty($variables['content'])) {
    /** @var $type \Drupal\dependent_content\Entity\DependentContentType */
    foreach ($variables['content'] as $type) {
      $variables['types'][$type->id()] = array(
        'type' => $type->id(),
        'add_link' => Link::createFromRoute($type->label(), 'entity.dependent_content.add_form', ['dependent_content_type' => $type->id()]),
        'description' => array(
          '#markup' => $type->getDescription()
        )
      );
    }
  }
}
