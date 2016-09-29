<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Entity\DependentContentType
 */

namespace Drupal\dependent_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\dependent_content\DependentContentTypeInterface;

/**
 * Defines the dependent content type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "dependent_content_type",
 *   label = @Translation("Dependent content type"),
 *   handlers = {
 *     "list_builder" = "Drupal\dependent_content\DependentContentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\dependent_content\Form\DependentContentTypeForm",
 *       "edit" = "Drupal\dependent_content\Form\DependentContentTypeForm",
 *       "delete" = "Drupal\dependent_content\Form\DependentContentTypeDeleteForm"
 *     }
 *   },
 *   config_prefix = "dependent_content_type",
 *   config_export = {
 *     "id",
 *     "label",
 *     "description"
 *   },
 *   admin_permission = "administer site configuration",
 *   bundle_of = "dependent_content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/dependent-content/{dependent_content_type}",
 *     "add-form" = "/admin/structure/dependent-content/add",
 *     "edit-form" = "/admin/structure/dependent-content/{dependent_content_type}/edit",
 *     "delete-form" = "/admin/structure/dependent-content/{dependent_content_type}/delete",
 *     "collection" = "/admin/structure/dependent-content"
 *   }
 * )
 */
class DependentContentType extends ConfigEntityBundleBase implements DependentContentTypeInterface {

  /**
   * The machine name of this dependent content type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the dependent content type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this dependent content type.
   *
   * @var string
   */
  protected $description;

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this node type.
   */
  public function getDescription() {
    return $this->description;
  }
}