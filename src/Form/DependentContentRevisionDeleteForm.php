<?php

namespace Drupal\dependent_content\Form;


use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The entity storage.
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
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * DependentContentRevisionDeleteForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  public function __construct(EntityTypeManagerInterface $manager, DateFormatterInterface $date_formatter, LanguageManagerInterface $language_manager) {

    $this->storage = $manager->getStorage('dependent_content');
    $this->dateFormatter = $date_formatter;
    $this->languageManager = $language_manager;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');

    return new static(
      $manager,
      $date_formatter,
      $language_manager
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {

    $dependent_content_revision = $this->getRequest()->get('dependent_content_revision');
    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $revision */
    $revision = $this->storage->loadRevision($dependent_content_revision);

    return $this->t('Are you sure you want to delete the revision from %date?', array(
      '%date' => $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short')
    ));
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {

    return new Url('entity.dependent_content_revision.history', array(
      'dependent_content' => $this->getRequest()->get('dependent_content')
    ));
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {

    return 'dependent_content_revision_delete';
  }

  /**
   * @inheritdoc
   */
  public function getConfirmText() {

    return $this->t('Delete');
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, $dependent_content_revision = NULL) {

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $revision */
    $revision = $this->storage->loadRevision($dependent_content_revision);

    if ($revision->hasTranslation($langcode)) {
      $revision = $revision->getTranslation($langcode);
    }

    $form_state->set('revision', $revision);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $revision */
    $revision = $form_state->get('revision');

    $this->storage->deleteRevision($revision->getRevisionId());

    $this->logger('dependent content')->notice('%type: deleted %title revision %revision.', array(
      '%type' => $revision->bundle(),
      '%title' => $revision->label(),
      '%revision' => $revision->getRevisionId()
    ));

    drupal_set_message($this->t('Revision from %revision-date of %type %title has been deleted.', array(
      '%revision-date' => $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short'),
      '%type' => $revision->getEntityType()->getLabel(),
      '%title' => $revision->label()
    )));

    $form_state->setRedirect('entity.dependent_content_revision.history', array(
      'dependent_content' => $revision->id()
    ));
  }
}