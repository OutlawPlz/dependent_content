<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Controller\DependentContentController
 */

namespace Drupal\dependent_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\dependent_content\DependentContentTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentController extends ControllerBase {

  /**
   * The dependent content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dependent_content_storage;

  /**
   * The dependent content type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dependent_content_type_storage;

  /**
   * DependentContentController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $dependent_content_storage
   * @param \Drupal\Core\Entity\EntityStorageInterface $dependent_content_type_storage
   */
  public function __construct(EntityStorageInterface $dependent_content_storage, EntityStorageInterface $dependent_content_type_storage) {

    $this->dependent_content_storage = $dependent_content_storage;
    $this->dependent_content_type_storage = $dependent_content_type_storage;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @return static
   */
  public static function create(ContainerInterface $container) {

    /** @var EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');

    return new static(
      $entity_type_manager->getStorage('dependent_content'),
      $entity_type_manager->getStorage('dependent_content_type')
    );
  }

  /**
   * Displays add custom dependent content links for available types.
   *
   * @return array
   *   A render array for a list of the custom block types that can be added or
   *   if there is only one custom block type defined for the site, the function
   *   returns the custom block add page for that custom block type.
   */
  public function addPage() {

    $types = $this->dependent_content_type_storage->loadMultiple();

    if ($types && count($types) == 1) {
      $type = reset($types);
      return $this->addForm($type);
    }

    if (count($types) === 0) {
      return array(
        '#markup' => $this->t('You have not created any dependent content types yet. Go to the <a href=":url">dependent content type creation page</a> to add a new dependent content type.', [
          ':url' => Url::fromRoute('entity.dependent_content_type.add_form')->toString()
        ])
      );
    }

    return array(
      '#theme' => 'dependent_content_add_list',
      '#content' => $types
    );
  }

  /**
   * Presents the dependent content creation form of given bundle/type.
   *
   * @param \Drupal\dependent_content\DependentContentTypeInterface $dependent_content_type
   * @return array
   */
  public function addForm(DependentContentTypeInterface $dependent_content_type) {

    $entity = $this->dependent_content_storage->create(array(
      'type' => $dependent_content_type->id()
    )); 

    return $this->entityFormBuilder()->getForm($entity);
  }
}
