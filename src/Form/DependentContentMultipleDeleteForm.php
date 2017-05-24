<?php

namespace Drupal\dependent_content\Form;


use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\dependent_content\Entity\DependentContent;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentMultipleDeleteForm extends ConfirmFormBase {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $tempStore;

  /**
   * The dependent content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The entities to delete.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, EntityManagerInterface $manager) {

    $this->tempStore = $temp_store_factory->get('dependent_content');
    $this->storage = $manager->getStorage('dependent_content');
    $this->entities = DependentContent::loadMultiple(array_keys($this->tempStore->get('multiple_delete')));
  }

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   *
   * @return static
   *   Returns an instance of this plugin.
   */
  public static function create(ContainerInterface $container) {

    /** @var PrivateTempStoreFactory $private_temp_store */
    $private_temp_store = $container->get('user.private_tempstore');
    /** @var \Drupal\Core\Entity\EntityManagerInterface $manager */
    $manager = $container->get('entity.manager');

    return new static(
      $private_temp_store,
      $manager
    );
  }

  /**
   * Returns the question to ask the user.
   *
   * @return string
   *   The form question. The page title will be set to this value.
   */
  public function getQuestion() {

    return $this->formatPlural(count($this->entities), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
  }

  /**
   * Returns the route to go to if the user cancels the action.
   *
   * @return \Drupal\Core\Url
   *   A URL object.
   */
  public function getCancelUrl() {

    return new Url('entity.dependent_content.collection');
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {

    return 'dependent_content_multiple_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {

    return t('Delete');
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $items = array();

    foreach ($this->entities as $entity) {
      $items[$entity->id()] = $entity->label();
    }

    $form['dependent_contents'] = array(
      '#theme' => 'item_list',
      '#items' => $items
    );

    $form = parent::buildForm($form, $form_state);

    return $form;
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

    if ($form_state->getValue('confirm') && !empty($this->entities)) {
      $count = count($this->entities);
      $this->storage->delete($this->entities);
      $this->logger('dependent_content')->notice("Deleted $count dependent contents.");
      drupal_set_message($this->t("Deleted $count dependent contents."));
    }

    $this->tempStore->delete('multiple_delete');
    $form_state->setRedirect('entity.dependent_content.collection');
  }
}