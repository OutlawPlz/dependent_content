<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Plugin\views\filter\Status
 */

namespace Drupal\dependent_content\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter by published status.
 *
 * @ViewsFilter("dependent_content_status")
 */
class Status extends FilterPluginBase {

  /**
   * Display the filter on the administrative summary
   */
  public function adminSummary() {

    return parent::adminSummary();
  }

  /**
   * Options form subform for setting the operator.
   *
   * This may be overridden by child classes, and it must
   * define $form['operator'];
   *
   * @see buildOptionsForm()
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {

    parent::operatorForm($form, $form_state);
  }

  /**
   * Determine if a filter can be exposed.
   */
  public function canExpose() {

    return FALSE;
  }

  /**
   * Add this filter to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  public function query() {

    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $table = $this->ensureMyTable();

    $query->addWhereExpression($this->options['group'], "$table.status = 1 OR (***CURRENT_USER*** <> 0 AND ***VIEW_UNPUBLISHED_DEPENDENT_CONTENT*** = 1)");
  }

  /**
   * The cache contexts associated with this object.
   *
   * These identify a specific variation/representation of the object.
   *
   * Cache contexts are tokens: placeholders that are converted to cache keys by
   * the @cache_contexts_manager service. The replacement value depends on the
   * request context (the current URL, language, and so on). They're converted
   * before storing an object in cache.
   *
   * @return string[]
   *   An array of cache context tokens, used to generate a cache ID.
   *
   * @see \Drupal\Core\Cache\Context\CacheContextsManager::convertTokensToKeys()
   */
  public function getCacheContexts() {

    $contexts = parent::getCacheContexts();
    $contexts[] = 'user';

    return $contexts;
  }
}
