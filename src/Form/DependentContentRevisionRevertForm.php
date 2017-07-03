<?php

namespace Drupal\dependent_content\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting an entity revision.
 */
class DependentContentRevisionRevertForm extends ConfirmFormBase {

  /**
   * The entity revision
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\RevisionLogInterface|\Drupal\Core\Entity\EntityChangedInterface $revision
   */
  protected $revision;

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
   * DependentContentRevisionRevertForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityStorageInterface $storage, DateFormatterInterface $date_formatter, TimeInterface $time) {

    $this->storage = $storage;
    $this->dateFormatter = $date_formatter;
    $this->time = $time;
  }

  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $container->get('entity_type.manager')->getStorage('dependent_content');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');
    /** @var \Drupal\Component\Datetime\TimeInterface $time */
    $time = $container->get('datetime.time');

    return new static(
      $storage,
      $date_formatter,
      $time
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {

    return $this->t('Are you sure you want to revert to the new revision from %date', array(
      '%date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime(), 'short')
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
      'dependent_content' => $this->revision->id()
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

    $this->revision = $this->storage->loadRevision($dependent_content_revision);

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

    $original_timestamp = $this->revision->getRevisionCreationTime();
    $log_message = $this->t('Copy of the revision from %date', array(
      '%date' => $this->dateFormatter->format($original_timestamp, 'short')
    ));

    $this->revision->setNewRevision();
    $this->revision->isDefaultRevision(TRUE);
    $this->revision->setRevisionLogMessage($log_message);
    $this->revision->setRevisionCreationTime($this->time->getRequestTime());
    $this->revision->setChangedTime($this->time->getRequestTime());

    $this->revision->save();

    $this->logger('dependent content')->notice('%type: reverted %title revision %revision.', array(
      '%type' => $this->revision->bundle(),
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId()
    ));

    drupal_set_message($this->t('%type %title has been reverted to the revision from %date.', array(
      '%type' => $this->revision->getEntityType()->getLabel(),
      '%title' => $this->revision->label(),
      '%date' => $this->dateFormatter->format($original_timestamp)
    )));

    $form_state->setRedirect('entity.dependent_content_revision.history', array(
      'dependent_content' => $this->revision->id()
    ));
  }
}