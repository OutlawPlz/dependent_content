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

  $variables['dependent_content'] = $variables['elements']['#dependent_content'];
  $variables['view_mode'] = $variables['elements']['#view_mode'];

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
function template_preprocess_dependent_content_bundle_list(&$variables) {

  $variables['bundles'] = array();

  if (!empty($variables['content'])) {

    /** @var \Drupal\dependent_content\Entity\DependentContentTypeInterface $bundle */
    foreach ($variables['content'] as $bundle) {

      $variables['types'][$bundle->id()] = array(
        'label' => $bundle->label(),
        'link' => Link::createFromRoute($bundle->label(), 'entity.dependent_content.add_form', array(
          'dependent_content_type' => $bundle->id()
        )),
        'url' => Url::fromRoute('entity.dependent_content.add_form', array(
          'dependent_content_type' => $bundle->id()
        )),
        'description' => array(
          '#markup' => $bundle->get('description'),
        ),
      );
    }
  }
}
