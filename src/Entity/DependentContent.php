<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Entity\DependentContent
 */

namespace Drupal\dependent_content\Entity;


use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogEntityTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the dependent content entity class.
 *
 * @ContentEntityType(
 *   id = "dependent_content",
 *   label = @Translation("Dependent content"),
 *   bundle_label = @Translation("Dependent content type"),
 *   base_table = "dependent_content",
 *   data_table = "dependent_content_field_data",
 *   revision_table = "dependent_content_revision",
 *   revision_data_table = "dependent_content_field_revision",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   show_revision_ui = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *     "revision" = "vid",
 *     "published" = "published",
 *     "uid" = "uid",
 *     "uuid" = "uuid"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\dependent_content\DependentContentViewBuilder",
 *     "list_builder" = "Drupal\dependent_content\DependentContentListBuilder",
 *     "views_data" = "Drupal\dependent_content\DependentContentViewsData",
 *     "access" = "Drupal\dependent_content\DependentContentAccessControlHandler",
 *     "moderation" = "Drupal\dependent_content\DependentContentModerationHandler",
 *     "form" = {
 *       "default" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "add" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "edit" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "delete" = "Drupal\dependent_content\Form\DependentContentDeleteForm"
 *     },
 *   },
 *   links = {
 *     "canonical" = "/dependent-content/{dependent_content}",
 *     "add-form" = "/dependent-content/add/{dependent_content_type}",
 *     "edit-form" = "/dependent-content/{dependent_content}",
 *     "delete-form" = "/dependent-content/{dependent_content}/delete",
 *     "revision" = "/dependent-content/{dependent_content}/revision/{dependent_content_revision}",
 *     "revision-history" = "/dependent-content/{dependent_content}/revision",
 *     "collection" = "/admin/content/dependent-content"
 *   },
 *   bundle_entity_type = "dependent_content_type",
 *   field_ui_base_route = "entity.dependent_content_type.canonical"
 * )
 */
class DependentContent extends ContentEntityBase implements DependentContentInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use RevisionLogEntityTrait;

  /**
   * Gets the dependent content type.
   *
   * @return string
   *   The dependent content type.
   */
  public function getType() {

    return $this->bundle();
  }

  /**
   * Gets the dependent content label.
   *
   * @return string
   *   The dependent content label.
   */
  public function getLabel() {

    return $this->get('label')->value;
  }

  /**
   * Sets the dependent content label.
   *
   * @param string $label
   *   The dependent content label.
   * @return \Drupal\dependent_content\Entity\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setLabel($label) {

    return $this->set('label', $label);
  }

  /**
   * Gets the dependent content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the dependent content.
   */
  public function getCreatedTime() {

    return $this->get('created')->value;
  }

  /**
   * Sets the dependent content creation timestamp.
   *
   * @param int $timestamp
   *   The dependent content creation timestamp.
   * @return \Drupal\dependent_content\Entity\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setCreatedTime($timestamp) {

    return $this->set('created', $timestamp);
  }

  /**
   * Returns the entity owner's user entity.
   *
   * @return \Drupal\user\UserInterface
   *   The owner user entity.
   */
  public function getOwner() {

    return $this->get('uid')->entity;
  }

  /**
   * Sets the entity owner's user entity.
   *
   * @param \Drupal\user\UserInterface $account
   *   The owner user entity.
   *
   * @return $this
   */
  public function setOwner(UserInterface $account) {

    return $this->set('uid', $account->id());
  }

  /**
   * Returns the entity owner's user ID.
   *
   * @return int|null
   *   The owner user ID, or NULL in case the user ID field has not been set on
   *   the entity.
   */
  public function getOwnerId() {

    return $this->get('uid')->target_id;
  }

  /**
   * Sets the entity owner's user ID.
   *
   * @param int $uid
   *   The owner user id.
   *
   * @return $this
   */
  public function setOwnerId($uid) {

    return $this->set('uid', $uid);
  }

  /**
   * Provides base field definitions for an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition. Useful when a single class is used for multiple,
   *   possibly dynamic entity types.
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    $fields += static::revisionLogBaseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Content description'))
      ->setDescription(t('A brief description of your content. Useful to identify this content.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the dependent content entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {

    parent::preSave($storage);

    if (!$this->getRevisionUserId()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }
}