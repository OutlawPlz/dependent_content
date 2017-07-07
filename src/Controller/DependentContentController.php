<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Controller\DependentContentController
 */

namespace Drupal\dependent_content\Controller;


use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dependent_content\Entity\DependentContentTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentController extends ControllerBase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * DependentContentController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $manager) {

    $this->storage = $manager->getStorage('dependent_content');
  }

  /**
   * Instantiate a new instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   * @return static
   *   Returns an instance of this class.
   */
  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');

    return new static(
      $manager
    );
  }

  /**
   * Presents the entity creation form of given bundle.
   *
   * @param DependentContentTypeInterface $dependent_content_type
   *   The config bundle entity.
   *
   * @return array
   *   The processed form for the given entity and operation.
   */
  public function addFormPage(DependentContentTypeInterface $dependent_content_type) {

    $entity = $this->storage->create(array(
      'type' => $dependent_content_type->id(),
    ));

    return $this->entityFormBuilder()->getForm($entity);
  }
}
