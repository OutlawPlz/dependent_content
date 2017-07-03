<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Controller\DependentContentController
 */

namespace Drupal\dependent_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Drupal\dependent_content\Entity\DependentContentInterface;
use Drupal\dependent_content\Entity\DependentContentTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentController extends ControllerBase {

  /**
   * The dependent content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dependentContentStorage;

  /**
   * The dependent content type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dependentContentTypeStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * DependentContentController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $dependent_content_storage
   *   The dependent content storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $dependent_content_type_storage
   *   The dependent content type storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $dependent_content_storage, EntityStorageInterface $dependent_content_type_storage, DateFormatterInterface $date_formatter) {

    $this->dependentContentStorage = $dependent_content_storage;
    $this->dependentContentTypeStorage = $dependent_content_type_storage;
    $this->dateFormatter = $date_formatter;
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

    /** @var EntityStorageInterface $dependent_content_storage */
    $dependent_content_storage = $container->get('entity_type.manager')->getStorage('dependent_content');
    /** @var EntityStorageInterface $dependent_content_type_storage */
    $dependent_content_type_storage = $container->get('entity_type.manager')->getStorage('dependent_content_type');
    /** @var DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');

    return new static(
      $dependent_content_storage,
      $dependent_content_type_storage,
      $date_formatter
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

    $types = $this->dependentContentTypeStorage->loadMultiple();

    if ($types && count($types) == 1) {
      $type = reset($types);
      return $this->addForm($type);
    }

    if (count($types) === 0) {
      return array(
        '#markup' => $this->t('You have not created any dependent content types yet. Go to the <a href=":url">dependent content type creation page</a> to add a new dependent content type.', [
          ':url' => Url::fromRoute('entity.dependent_content_type.add_form')->toString(),
        ]),
      );
    }

    return array(
      '#theme' => 'dependent_content_add_list',
      '#content' => $types,
    );
  }

  /**
   * Displays a list of revision of the given dependent content.
   *
   * @param \Drupal\dependent_content\Entity\DependentContentInterface $dependent_content
   *   The dependent content entity.
   *
   * @return array
   *   A render array for a list of revision of the given dependent content.
   */
  public function revisionHistoryPage(DependentContentInterface $dependent_content) {

    $revisions = $this->loadRevisions($dependent_content);

    return array(
      '#theme' => 'dependent_content_revision_history',
      '#content' => $revisions
    );
  }

  public function revisionViewPage(DependentContentInterface $dependent_content_revision) {}

  public function revisionViewPageTitle(DependentContentInterface $dependent_content_revision) {}

  /**
   * Presents the dependent content creation form of given bundle/type.
   *
   * @param \Drupal\dependent_content\Entity\DependentContentTypeInterface $dependent_content_type
   *   The dependent content type entity.
   *
   * @return array
   *   The processed form for the given entity and operation.
   */
  public function addForm(DependentContentTypeInterface $dependent_content_type) {

    $entity = $this->dependentContentStorage->create(array(
      'type' => $dependent_content_type->id(),
    ));

    return $this->entityFormBuilder()->getForm($entity);
  }

  /**
   * Load all revisions of a dependent content.
   *
   * @param \Drupal\dependent_content\Entity\DependentContentInterface $dependent_content
   *   The dependent content entity.
   *
   * @return DependentContentInterface[]
   *   The revisions in descending order.
   */
  public function loadRevisions(DependentContentInterface $dependent_content) {

    $revisions = array();

    $vids = array_keys($this->dependentContentStorage->getQuery()
      ->allRevisions()
      ->condition($dependent_content->getEntityType()->getKey('id'), $dependent_content->id())
      ->sort($dependent_content->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute());

    foreach ($vids as $vid) {
      $revisions[] = $this->dependentContentStorage->loadRevision($vid);
    }

    return $revisions;
  }
}
