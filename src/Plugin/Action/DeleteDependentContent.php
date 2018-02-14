<?php

namespace Drupal\dependent_content\Plugin\Action;


use Drupal\Core\Action\ActionBase;
use Drupal\Core\Annotation\Action;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Redirects to a dependent content deletion form.
 *
 * @Action(
 *   id = "dependent_content_delete_action",
 *   label = @Translation("Delete dependent content"),
 *   type = "dependent_content",
 *   confirm_form_route_name = "entity.dependent_content.multiple_delete_form"
 * )
 */
class DeleteDependentContent extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $privateTempStore;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PrivateTempStoreFactory $private_temp_store
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $private_temp_store) {

    $this->privateTempStore = $private_temp_store->get('dependent_content');

    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var PrivateTempStoreFactory $private_temp_store */
    $private_temp_store = $container->get('user.private_tempstore');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $private_temp_store
    );
  }

  /**
   * Checks object access.
   *
   * @param mixed $object
   *   The object to execute the action on.
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
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {

    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $object */
    return $object->access('delete', $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {

    // Delete previous data.
    $this->privateTempStore->delete('delete_entities');
    // Set current data.
    $this->privateTempStore->set('delete_entities', $entities);
  }

  /**
   * Executes the plugin.
   */
  public function execute() {}
}