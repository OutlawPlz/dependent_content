<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Controller\DependentContentTypeController
 */

namespace Drupal\dependent_content\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentTypeController extends ControllerBase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface $storage
   */
  protected $storage;

  /**
   * DependentContentTypeController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   */
  public function __construct(EntityTypeManagerInterface $manager) {

    $this->storage = $manager->getStorage('dependent_content_type');
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');

    return new static(
      $manager
    );
  }

  /**
   * Display a list of available bundles.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   Render array or redirect response.
   */
  public function listPage() {

    $bundles = $this->storage->loadMultiple();
    // If only one bundle, redirect to that bundle.
    if (count($bundles) === 1) {
      $bundle = array_shift($bundles);
      return $this->redirect('entity.dependent_content.add_form', array(
        'dependent_content_type' => $bundle->id()
      ));
    }
    // Otherwise list all available bundle.
    return array(
      '#theme' => 'dependent_content_bundle_list',
      '#bundles' => $bundles
    );
  }
}
