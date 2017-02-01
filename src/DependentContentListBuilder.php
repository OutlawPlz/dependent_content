<?php

/**
 * @file
 * Contains \Drupal\dependent_content\DependentContentListBuilder
 */

namespace Drupal\dependent_content;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * DependentContentListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Datetime\DateFormatInterface|\Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter) {

    parent::__construct($entity_type, $storage);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * Instantiates a new instance of this entity handler.
   *
   * This is a factory method that returns a new instance of this object. The
   * factory should pass any needed dependencies into the constructor of this
   * object, but not the container itself. Every call to this method must return
   * a new instance of this object; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this object should use.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return static
   *   A new instance of the entity handler.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {

    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter')
    );
  }

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   */
  public function buildHeader() {

    $header = array(
      'label' => $this->t('Content description'),

      'type' => array(
        'data' => $this->t('Type'),
        'class' => array(
          RESPONSIVE_PRIORITY_MEDIUM
        )
      ),

      'author' => array(
        'data' => $this->t('Author'),
        'class' => array(
          RESPONSIVE_PRIORITY_LOW
        )
      ),

      'status' => $this->t('Status'),

      'changed' => array(
        'data' => $this->t('Updated'),
        'class' => array(
          RESPONSIVE_PRIORITY_LOW
        )
      )
    );

    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for this row of the list.
   *
   * @return array
   *   A render array structure of fields for this entity.
   */
  public function buildRow(EntityInterface $entity) {

    /** @var DependentContentInterface $entity */
    $row['label'] = $entity->label();
    $row['type'] = $entity->bundle();
    $row['author']['data'] = array(
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    );
    $row['status'] = $entity->isPublished() ? t('published') : t('not published');
    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');

    return $row + parent::buildRow($entity);
  }
}