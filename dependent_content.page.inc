<?php
/**
 * @file
 * Contains dependent_content.page.inc
 */

use \Drupal\Core\Link;
use \Drupal\Core\Render\Element;
use Drupal\Core\Url;

/**
 * Prepare variables for dependent content templates.
 *
 * Default template: dependent_content.html.twig
 *
 * @param array $variables
 *   An associative array containing:
 */
function template_preprocess_dependent_content(&$variables) {

  // kint($variables); kint($variables['elements']);

  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

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
        'label' => $type->label(),
        'link' => Link::createFromRoute($type->label(), 'entity.dependent_content.add_form', ['dependent_content_type' => $type->id()]),
        'url' => Url::fromRoute('entity.dependent_content.add_form', ['dependent_content_type' => $type->id()]),
        'description' => array(
          '#markup' => $type->getDescription()
        )
      );
    }
  }
}
