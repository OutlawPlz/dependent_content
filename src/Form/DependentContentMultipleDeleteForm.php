<?php

namespace Drupal\dependent_content\Form;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DependentContentMultipleDeleteForm extends ConfirmFormBase {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $privateTempStore;

  /**
   * The dependent content storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a DeleteMultiple form object.
   *
   * @param \Drupal\user\PrivateTempStoreFactory $private_temp_store
   *   The tempstore factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity manager.
   */
  public function __construct(PrivateTempStoreFactory $private_temp_store, EntityTypeManagerInterface $manager) {

    $this->privateTempStore = $private_temp_store->get('dependent_content');
    $this->storage = $manager->getStorage('dependent_content');
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
    $manager = $container->get('entity_type.manager');

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

    $entities = $this->privateTempStore->get('delete_entities');

    return $this->formatPlural(count($entities), 'Are you sure you want to delete this item?', 'Are you sure you want to delete these items?');
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

    return 'dependent_content_delete_entities_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {

    return $this->t('Delete');
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

    $form =  parent::buildForm($form, $form_state);
    $entities = $this->privateTempStore->get('delete_entities');

    $form['items'] = array(
      '#theme' => 'item_list',
      '#items' => array(),
    );

    /** @var ContentEntityInterface $entity */
    foreach ($entities as $entity) {
      $form['items']['#items'][$entity->id()] = array(
        '#markup' => $this->buildItem($entity),
      );
    }

    return $form;
  }

  /**
   * Build an item of the list.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   *
   * @return string
   *   The list item content.
   */
  public function buildItem(ContentEntityInterface $entity) {

    if ($entity->isDefaultTranslation()) {
      return $this->t('@label (Original translation) - <em>All translations will be deleted</em>', array(
        '@label' => $entity->label(),
      ));
    }

    return $entity->label();
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

    $entities = $this->privateTempStore->get('delete_entities');
    $entities_groups = array();

    /** @var ContentEntityInterface $entity */
    foreach ($entities as $entity) {
      // Groups entities by ID.
      $entities_groups[$entity->id()][] = $entity;
    }

    foreach ($entities_groups as $id => $entities_group) {
      /** @var ContentEntityInterface $entity_default_translation */
      $entity_default_translation = $this->storage->load($id);

      foreach ($entities_group as $entity) {
        
        if ($entity->isDefaultTranslation()) {
          $entity->delete(); break;
        }

        $entity_default_translation->removeTranslation($entity->language()->getId());
        $entity_default_translation->save();
      }
    }

    $count = count($entities);
    $message = $this->formatPlural($count, $this->t('Deleted 1 content.'), $this->t('Deleted @count contents.', array(
      '@count' => $count
    )));

    drupal_set_message($message);
    $this->logger('dependent content')->notice($message);

    $form_state->setRedirect('entity.dependent_content.collection');
  }
}
