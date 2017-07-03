<?php

namespace Drupal\dependent_content\Form;


use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The entity revision.
   *
   * @var \Drupal\Core\Entity\EntityInterface|\Drupal\Core\Entity\RevisionLogInterface
   */
  protected $revision;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * DependentContentRevisionDeleteForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityStorageInterface $storage, Connection $connection, DateFormatterInterface $date_formatter) {

    $this->storage = $storage;
    $this->connection = $connection;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {

    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $container->get('entity_type.manager')->getStorage('dependent_content');
    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = $container->get('database');
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $container->get('date.formatter');

    return new static(
      $storage,
      $connection,
      $date_formatter
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {

    return $this->t('Are you sure you want to delete the revision from %date?', array(
      '%date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime(), 'short')
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

    $this->storage->deleteRevision($this->revision->getRevisionId());

    $this->logger('dependent content')->notice('%type: deleted %title revision %revision.', array(
      '%type' => $this->revision->bundle(),
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId()
    ));

    drupal_set_message($this->t('Revision from %revision-date of %type %title has been deleted.', array(
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime(), 'short'),
      '%type' => $this->revision->getEntityType()->getLabel(),
      '%title' => $this->revision->label()
    )));

    $form_state->setRedirect('entity.dependent_content_revision.history', array(
      'dependent_content' => $this->revision->id()
    ));
  }
}