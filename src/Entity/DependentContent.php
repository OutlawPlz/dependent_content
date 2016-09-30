<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Entity\DependentContent
 */

namespace Drupal\dependent_content\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\dependent_content\DependentContentInterface;
use Drupal\user\UserInterface;

/**
 * Defines the dependent content entity class.
 *
 * @ContentEntityType(
 *   id = "dependent_content",
 *   label = @Translation("Dependent content"),
 *   bundle_label = @Translation("Dependent content type"),
 *   base_table = "dependent_content",
 *   admin_permission = "administer content",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "bundle" = "type",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "uid" = "uid",
 *     "uuid" = "uuid"
 *   },
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\dependent_content\DependentContentListBuilder",
 *     "views_data" = "Drupal\dependent_content\Entity\DependentContentViewsData",
 *     "form" = {
 *       "default" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "add" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "edit" = "Drupal\dependent_content\Form\DependentContentForm",
 *       "delete" = "Drupal\dependent_content\Form\DependentContentDeleteForm"
 *     }
 *   },
 *   links = {
 *     "canonical" = "/dependent-content/{dependent_content}",
 *     "add-form" = "/dependent-content/add/{dependent_content_type}",
 *     "edit-form" = "/dependent-content/{dependent_content}/edit",
 *     "delete-form" = "/dependent-content/{dependent_content}/delete",
 *     "collection" = "/admin/content/dependent-content"
 *   },
 *   bundle_entity_type = "dependent_content_type",
 *   field_ui_base_route = "entity.dependent_content_type.edit_form"
 * )
 */
class DependentContent extends ContentEntityBase implements DependentContentInterface {

  use EntityChangedTrait;

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
   * @return \Drupal\dependent_content\DependentContentInterface
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
   * @return \Drupal\dependent_content\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setCreatedTime($timestamp) {
    return $this->set('created', $timestamp);
  }

  /**
   * Returns the dependent content published status indicator.
   *
   * Unpublished dependent content are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the dependent content is published.
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * Sets the published status of a dependent content.
   *
   * @param bool $published
   *   TRUE to set this dependent content to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\dependent_content\DependentContentInterface
   *   The called dependent content entity.
   */
  public function setPublished($published) {
    return $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
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

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel('ID')
      ->setDescription(t('The ID of the dependent content entity.'))
      ->setReadOnly(TRUE);

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
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The Dependent content type/bundle.'))
      ->setSetting('target_type', 'dependent_content_type')
      ->setRequired(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the dependent content entity.'))
      ->setReadOnly(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the dependent content entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the dependent content is published.'))
      ->setDisplayOptions('form', array(
        'type' => 'boolean_checkbox',
        'settings' => array(
          'display_label' => TRUE,
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Dependent content entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayOptions('form', array(
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }
}