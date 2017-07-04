<?php

namespace Drupal\dependent_content\Plugin\views\field;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\dependent_content\Controller\DependentContentRevisionController;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders all operations links for a revision.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("dependent_content_revision_operations")
 */
class DependentContentRevisionOperations extends FieldPluginBase {

  use EntityTranslationRenderTrait;
  use RedirectDestinationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The revision controller.
   *
   * @var \Drupal\dependent_content\Controller\DependentContentRevisionController
   */
  protected $revisionController;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * DependentContentRevisionOperations constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\dependent_content\Controller\DependentContentRevisionController $revision_controller
   *   The revision controller.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, DependentContentRevisionController $revision_controller, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->revisionController = $revision_controller;
    $this->entityManager = $entity_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /** @var \Drupal\dependent_content\Controller\DependentContentRevisionController $revision_controller */
    $revision_controller = $container->get('dependent_content.revision_controller');
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $revision_controller,
      $entity_manager,
      $language_manager
    );
  }

  /**
   * Information about options for all kinds of purposes will be held here.
   * @code
   * 'option_name' => array(
   *  - 'default' => default value,
   *  - 'contains' => (optional) array of items this contains, with its own
   *      defaults, etc. If contains is set, the default will be ignored and
   *      assumed to be array().
   *  ),
   * @endcode
   *
   * @return array
   *   Returns the options of this handler/plugin.
   */
  public function defineOptions() {

    return parent::defineOptions();
  }

  /**
   * @inheritdoc
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    return parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function render(ResultRow $values) {

    $entity = $this->getEntity($values);
    $translated_entity = $this->getEntityTranslation($entity, $values);

    return $this->revisionController->buildOperations($translated_entity);
  }

  /**
   * Returns the entity type identifier.
   *
   * @return string
   *   The entity type identifier.
   */
  public function getEntityTypeId() {

    return $this->getEntityType();
  }

  /**
   * Returns the entity manager.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  protected function getEntityManager() {

    return $this->entityManager;
  }

  /**
   * Returns the language manager.
   *
   * @return \Drupal\Core\Language\LanguageManagerInterface
   *   The language manager.
   */
  protected function getLanguageManager() {

    return $this->languageManager;
  }

  /**
   * Returns the top object of a view.
   *
   * @return \Drupal\views\ViewExecutable
   *   The view object.
   */
  protected function getView() {

    return $this->view;
  }

  /**
   * @inheritdoc
   */
  public function clickSortable() {

    return FALSE;
  }

  /**
   * Provides the handler some groupby.
   */
  public function usesGroupBy() {

    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function query() {

    if ($this->languageManager->isMultilingual()) {
      $this->getEntityTranslationRenderer()->query($this->query, $this->relationship);
    }
  }
}