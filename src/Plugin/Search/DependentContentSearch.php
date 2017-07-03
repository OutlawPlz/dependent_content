<?php
/**
 * @File
 * Contains \Drupal\dependent_content\Plugin\Search\DependentContentSearch
 */

namespace Drupal\dependent_content\Plugin\Search;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\search\Plugin\SearchPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles searching for dependent content entities using the search module index.
 *
 * @SearchPlugin(
 *   id = "dependent_content_search",
 *   title = @Translation("Dependent content")
 * )
 */
class DependentContentSearch extends SearchPluginBase implements AccessibleInterface {

  /**
   * A database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * An entity manager object.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A module manager object.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The dependent content entity storage.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, EntityStorageInterface $storage, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
    $this->storage = $storage;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
  }

  /**
   * Creates an instance of the plugin.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  static public function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var Connection $database */
    $database = $container->get('database');
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $container->get('entity_type.manager')->getStorage('dependent_content');
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');
    /** @var \Drupal\Core\Session\AccountInterface $current_user */
    $current_user = $container->get('current_user');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $database,
      $storage,
      $module_handler,
      $current_user
    );
  }

  /**
   * Checks data value access.
   *
   * @param string $operation
   *   The operation to be performed.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   * @param bool $return_as_object
   *   (optional) Defaults to FALSE.
   *
   * @return bool|\Drupal\Core\Access\AccessResultInterface
   *   The access result. Returns a boolean if $return_as_object is FALSE (this
   *   is the default) and otherwise an AccessResultInterface object.
   *   When a boolean is returned, the result of AccessInterface::isAllowed() is
   *   returned, i.e. TRUE means access is explicitly allowed, FALSE means
   *   access is either explicitly forbidden or "no opinion".
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {

    $result = AccessResult::allowedIf(!empty($account) && $account->hasPermission('access dependent content'))->cachePerPermissions();

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * Executes the search.
   *
   * @return array
   *   A structured list of search results.
   */
  public function execute() {

    $results = array();

    if (!$this->isSearchExecutable()) {
      return $results;
    }

    $keywords = $this->getKeywords();
    $keywords = $this->database->escapeLike($keywords);
    $keywords = preg_replace('!\*+!', '%', $keywords);

    $query = $this->database->select('dependent_content_field_data', 'dependent_content')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');

    return $results;
  }
}