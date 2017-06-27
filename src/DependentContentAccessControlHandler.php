<?php
/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentAccessControlHandler
 */

namespace Drupal\dependent_content;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

class DependentContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * Performs access checks.
   *
   * This method is supposed to be overwritten by extending classes that
   * do their own custom access checking.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to check access.
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'view label', 'update' or
   *   'delete'.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   * @return \Drupal\Core\Access\AccessResultInterface The access result.
   * The access result.
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $entity */
    $unpublished = !$entity->isPublished();

    switch ($operation) {

      case 'view':
        if ($unpublished) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished dependent content');
        }
        return AccessResult::allowedIfHasPermission($account, 'access dependent content');
        break;

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'administer dependent content');
        break;

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer dependent content');
        break;

      default:
        return AccessResult::neutral();
    }
  }

  /**
   * Performs create access checks.
   *
   * This method is supposed to be overwritten by extending classes that
   * do their own custom access checking.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user for which to check access.
   * @param array $context
   *   An array of key-value pairs to pass additional context when needed.
   * @param string|null $entity_bundle
   *   (optional) The bundle of the entity. Required if the entity supports
   *   bundles, defaults to NULL otherwise.
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {

    return AccessResult::allowedIfHasPermission($account, 'administer dependent content');
  }
}
