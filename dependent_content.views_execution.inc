<?php

/**
 * @file
 * Provide views runtime hooks for dependent_content.module.
 */

use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_query_substitutions().
 *
 * @param \Drupal\views\ViewExecutable $view
 * @return array
 */
function dependent_content_views_query_substitutions(ViewExecutable $view) {

  $account = \Drupal::currentUser();

  return array(
    '***VIEW_UNPUBLISHED_DEPENDENT_CONTENT***' => intval($account->hasPermission('view unpublished dependent content'))
  );
}
