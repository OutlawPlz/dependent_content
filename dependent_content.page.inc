<?php
/**
 * @file
 * Contains dependent_content.page.inc
 */

use \Drupal\Core\Render\Element;
use \Drupal\Core\Url;

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
  // Foreach element in side elements, copy it inside content array.
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
  $variables['create_bundle_url'] = Url::fromRoute('entity.dependent_content_type.add_form');
  // If there are no bundles, return.
  if (empty($variables['elements']['#bundles'])) {
    return;
  }
  // Otherwise, for each bundle build variables.
  foreach ($variables['elements']['#bundles'] as $bundle_id => $bundle) {
    $variables['bundles'][$bundle_id] = array(
      'label' => $bundle->label(),
      'url' => Url::fromRoute('entity.dependent_content.add_form', array(
        'dependent_content_type' => $bundle_id
      )),
      'description' => $bundle->get('description')
    );
  }
}
