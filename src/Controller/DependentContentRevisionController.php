<?php

namespace Drupal\dependent_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\dependent_content\Entity\DependentContentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentRevisionController extends ControllerBase {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The number of entities to list per page, or FALSE to list all entities.
   *
   * For example, set this to FALSE if the list uses client-side filters that
   * require all entities to be listed (like the views overview).
   *
   * @var int|false
   */
  protected $limit = 50;

  /**
   * Construct a new DependentContentRevisionController object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {

    $this->storage = $storage;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {

    /** @var EntityStorageInterface $storage */
    $storage = $container->get('entity_type.manager')->getStorage('dependent_content');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');

    return new static(
      $storage,
      $date_formatter
    );
  }

  /**
   * Loads entity revision IDs using a pager sorted by the entity id.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   *
   * @return array
   *   An array of entity revision IDs.
   */
  public function getEntityRevisionIds(EntityInterface $entity) {

    $result = $this->storage->getQuery()
      ->allRevisions()
      ->condition($entity->getEntityType()->getKey('id'), $entity->id())
      ->sort($entity->getEntityType()->getKey('revision'), 'DESC')
      ->pager($this->limit)
      ->execute();

    return array_keys($result);
  }

  /**
   * Builds the header row for the entity revision listing.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {

    return array(
      'current' => $this->t('Current'),
      'log_message' => $this->t('Log message'),
      'author' => $this->t('Author'),
      'created' => $this->t('Created')
    );
  }

  /**
   * Builds the header row for the entity revision listing.
   *
   * @param \Drupal\Core\Entity\RevisionLogInterface $entity
   *   The entity revision fot this row of the list.
   *
   * @return array
   *   A render array structure of fields for this entity revision.
   */
  public function buildRow(RevisionLogInterface $entity) {

    return array(
      'current' => array(
        'data' => $entity->isDefaultRevision() ? array('#markup' => '<em>Current revision</em>') : ''
      ),
      'log_message' => array(
        'data' => array('#markup' => $entity->getRevisionLogMessage())
      ),
      'author' => array(
        'data' => array(
          '#theme' => 'username',
          '#account' => $entity->getRevisionUser()
        )
      ),
      'created' => $this->dateFormatter->format($entity->getRevisionCreationTime(), 'short')
    );
  }

  /**
   * Builds the revision listing for the given entity. Inspired by EntityListBuilder.
   *
   * @param \Drupal\dependent_content\Entity\DependentContentInterface $dependent_content
   *   The entity object.
   *
   * @return array
   *   A render array for table.html.twig.
   */
  public function listPage(DependentContentInterface $dependent_content) {

    $build['table'] = array(
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#rows' => array(),
      '#empty' => $this->t('There is no revision yet.'),
      '#cache' => array(
        'contexts' => $dependent_content->getEntityType()->getListCacheContexts(),
        'tags' => $dependent_content->getEntityType()->getListCacheTags(),
      )
    );

    $vids = $this->getEntityRevisionIds($dependent_content);

    foreach ($vids as $vid) {
      /** @var RevisionLogInterface $entity_revision */
      $entity_revision = $this->storage->loadRevision($vid);

      if ($row = $this->buildRow($entity_revision)) {
        $build['table']['#rows'][$entity_revision->getRevisionId()] = $row;
      }
    }

    if ($this->limit) {
      $build['pager'] = array(
        '#type' => 'pager'
      );
    }

    return $build;
  }

  /**
   * Display an entity revision.
   *
   * @param int $dependent_content_revision
   *   The entity revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function viewPage($dependent_content_revision) {

    /** @var DependentContentInterface $entity_revision */
    $entity_revision = $this->storage->loadRevision($dependent_content_revision);
    /** @var \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder */
    $view_builder = $this->entityTypeManager()->getViewBuilder('dependent_content');

    return $view_builder->view($entity_revision);
  }

  /**
   * The _title_callback for the entity revision view page.
   *
   * @param int $dependent_content_revision
   *   The entity revision ID.
   *
   * @return string
   *   The page title.
   */
  public function viewPageTitle($dependent_content_revision) {

    /** @var DependentContentInterface $entity_revision */
    $entity_revision = $this->storage->loadRevision($dependent_content_revision);

    return $this->t('Revision of %title from %date', array(
      '%title' => $entity_revision->label(),
      '%date' => $this->dateFormatter->format($entity_revision->getRevisionCreationTime())
    ));
  }
}