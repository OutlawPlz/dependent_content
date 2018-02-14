<?php

namespace Drupal\dependent_content\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Url;
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
   * @param EntityTypeManagerInterface $manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeManagerInterface $manager, DateFormatterInterface $date_formatter) {

    $this->storage = $manager->getStorage('dependent_content');
    $this->dateFormatter = $date_formatter;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');

    return new static(
      $manager,
      $date_formatter
    );
  }

  /**
   * Loads entity revision IDs using a pager sorted by the entity id.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   *
   * @return array
   *   An array of entity revision IDs.
   */
  public function getEntityRevisionIds(ContentEntityInterface $entity) {

    $entity_type = $entity->getEntityType();

    $result = $this->storage->getQuery()
      ->allRevisions()
      ->condition($entity_type->getKey('id'), $entity->id())
      ->sort($entity_type->getKey('revision'), 'DESC')
      ->pager($this->limit)
      ->execute();

    return array_keys($result);
  }

  /**
   * Provides an array of information to build a list of operation links.
   *
   * @param RevisionLogInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   An associative array of operation link data for this list, keyed by
   *   operation name, containing the following key-value pairs:
   */
  public function getOperations(RevisionLogInterface $entity) {

    $operations = array();

    /** @var ContentEntityInterface $entity */
    if ($entity->access('update')) {
      $operations['revert'] = array(
        'title' => $this->t('Revert'),
        'weight' => 10,
        'url' => Url::fromRoute('entity.dependent_content_revision.revert_form', array(
          'dependent_content' => $entity->id(),
          'dependent_content_revision' => $entity->getRevisionId()
        ))
      );
    }

    if ($entity->access('view')) {
      $operations['view'] = array(
        'title' => $this->t('View'),
        'weight' => 50,
        'url' => Url::fromRoute('entity.dependent_content_revision.canonical', array(
          'dependent_content' => $entity->id(),
          'dependent_content_revision' => $entity->getRevisionId()
        ))
      );
    }

    if ($entity->access('delete')) {
      $operations['delete'] = array(
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => Url::fromRoute('entity.dependent_content_revision.delete_form', array(
          'dependent_content' => $entity->id(),
          'dependent_content_revision' => $entity->getRevisionId()
        ))
      );
    }

    return $operations;
  }

  /**
   * Builds the header row for the entity revision listing.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {

    return array(
      'log_message' => $this->t('Log message'),
      'author' => $this->t('Author'),
      'created' => $this->t('Created'),
      'operations' => $this->t('Operations')
    );
  }

  /**
   * Builds the header row for the entity revision listing.
   *
   * @param RevisionLogInterface $entity
   *   The entity revision fot this row of the list.
   *
   * @return array
   *   A render array structure of fields for this entity revision.
   */
  public function buildRow(RevisionLogInterface $entity) {

    return array(
      'log_message' => array(
        'data' => array('#markup' => $entity->getRevisionLogMessage())
      ),
      'author' => array(
        'data' => array(
          '#theme' => 'username',
          '#account' => $entity->getRevisionUser()
        )
      ),
      'created' => $this->dateFormatter->format($entity->getRevisionCreationTime(), 'short'),
      'operations' => array(
        'data' => $this->buildOperations($entity)
      )
    );
  }


  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param RevisionLogInterface $entity
   *   The entity for this row of the list.
   *
   * @return array
   *   A render array structure of fields for this entity.
   */
  public function buildOperations(RevisionLogInterface $entity) {

    if ($entity->isDefaultRevision()) {
      return array(
        '#markup' => '<em>Current revision</em>'
      );
    }

    return array(
      '#type' => 'operations',
      '#links' => $this->getOperations($entity)
    );
  }

  /**
   * Builds the revision listing for the given entity.
   *
   * @param \Drupal\dependent_content\Entity\DependentContentInterface $dependent_content
   *   The entity object.
   *
   * @return array
   *   A render array for table.html.twig.
   */
  public function historyPage(DependentContentInterface $dependent_content) {

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
    $langcode = $dependent_content->language()->getId();

    foreach ($vids as $vid) {
      /** @var RevisionLogInterface|ContentEntityInterface $entity_revision */
      $entity_revision = $this->storage->loadRevision($vid);

      if (!$dependent_content->isDefaultTranslation() && $entity_revision->hasTranslation($langcode)) {
        $entity_revision = $entity_revision->getTranslation($langcode);
      }

      if ($entity_revision->isRevisionTranslationAffected() || $entity_revision->isDefaultRevision()) {
        $build['table']['#rows'][$entity_revision->getRevisionId()] = $this->buildRow($entity_revision);
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

    /** @var ContentEntityInterface $entity_revision */
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

    /** @var ContentEntityInterface|RevisionLogInterface $entity_revision */
    $entity_revision = $this->storage->loadRevision($dependent_content_revision);
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    if ($entity_revision->hasTranslation($langcode)) {
      $entity_revision->getTranslation($langcode);
    }

    return $this->t('Revision of %title from %date', array(
      '%title' => $entity_revision->label(),
      '%date' => $this->dateFormatter->format($entity_revision->getRevisionCreationTime())
    ));
  }
}