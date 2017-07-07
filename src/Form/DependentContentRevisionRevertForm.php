<?php

namespace Drupal\dependent_content\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\dependent_content\Entity\DependentContentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting an entity revision.
 */
class DependentContentRevisionRevertForm extends ConfirmFormBase {

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
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * DependentContentRevisionRevertForm constructor.
   *
   * @param EntityTypeManagerInterface $manager
   *   The entity storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $manager, DateFormatterInterface $date_formatter, TimeInterface $time, LanguageManagerInterface $language_manager) {

    $this->storage = $manager->getStorage('dependent_content');
    $this->dateFormatter = $date_formatter;
    $this->time = $time;
    $this->languageManager = $language_manager;
  }

  public static function create(ContainerInterface $container) {

    /** @var EntityTypeManagerInterface $manager */
    $manager = $container->get('entity_type.manager');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Component\Datetime\TimeInterface $time */
    $time = $container->get('datetime.time');
    /** @var LanguageManagerInterface $language_manager */
    $language_manager = $container->get('language_manager');

    return new static(
      $manager,
      $date_formatter,
      $time,
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
    /** @var DependentContentInterface $revision */
    $revision = $this->storage->loadRevision($dependent_content_revision);

    return $this->t('Are you sure you want to revert to the new revision from %date', array(
      '%date' => $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short')
    ));
  }

  /**
   * Returns a caption for the button that confirms the action.
   *
   * @return string
   *   The form confirmation text.
   */
  public function getConfirmText() {

    return t('Revert');
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

    return 'dependent_content_revision_revert';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int|null $dependent_content_revision
   *   The entity revision ID.
   *
   * @return array The form structure.
   * The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $dependent_content_revision = NULL) {

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    /** @var DependentContentInterface $revision */
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

    /** @var DependentContentInterface $revision */
    $revision = $form_state->get('revision');

    $original_timestamp = $revision->getRevisionCreationTime();
    $log_message = $this->t('Copy of the revision from %date', array(
      '%date' => $this->dateFormatter->format($original_timestamp, 'short')
    ));

    $revision->setNewRevision();
    $revision->isDefaultRevision(TRUE);

    $revision->setRevisionLogMessage($log_message);
    $revision->setRevisionCreationTime($this->time->getRequestTime());
    $revision->setChangedTime($this->time->getRequestTime());

    $revision->save();

    $this->logger('dependent content')->notice('%type: reverted %title revision %revision.', array(
      '%type' => $revision->bundle(),
      '%title' => $revision->label(),
      '%revision' => $revision->getRevisionId()
    ));

    drupal_set_message($this->t('%type %title has been reverted to the revision from %date.', array(
      '%type' => $revision->getEntityType()->getLabel(),
      '%title' => $revision->label(),
      '%date' => $this->dateFormatter->format($original_timestamp)
    )));

    $form_state->setRedirect('entity.dependent_content_revision.history', array(
      'dependent_content' => $this->getRequest()->get('dependent_content')
    ));
  }
}