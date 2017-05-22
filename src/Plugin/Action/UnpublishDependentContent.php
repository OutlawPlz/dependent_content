<?php

namespace Drupal\dependent_content\Plugin\Action;


use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Unpublishes a dependent content.
 *
 * @Action(
 *   id = "dependent_content_unpublish_action",
 *   label = @Translation("Unpublish selected content"),
 *   type = "dependent_content"
 * )
 */
class UnpublishDependentContent extends ActionBase {

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

    /** @var \Drupal\dependent_content\DependentContentInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * Executes the plugin.
   */
  public function execute($entity = NULL) {

    /** @var \Drupal\dependent_content\DependentContentInterface */
    $entity->setPublished(FALSE)->save();
  }
}