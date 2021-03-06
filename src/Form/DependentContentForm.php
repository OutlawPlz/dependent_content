<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Form\DependentContentForm
 */

namespace Drupal\dependent_content\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the dependent content edit forms.
 */
class DependentContentForm extends ContentEntityForm {

  /**
   * Gets the actual form array to be built.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @return array
   *   The form structure.
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['advanced'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    );

    $form['author'] = array(
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('dependent-content-form-author'),
      ),
      '#weight' => 50,
      '#optional' => TRUE,
    );

    $form['uid']['#group'] = 'author';
    $form['created']['#group'] = 'author';
    $form['revision_log_message']['#group'] = 'revision_information';

    return $form;
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * Returns an array of supported actions for the current entity form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @return array
   *   An array of supported actions.
   */
  protected function actions(array $form, FormStateInterface $form_state) {

    $actions = parent::actions($form, $form_state);
    /** @var \Drupal\dependent_content\Entity\DependentContentInterface $entity */
    $entity = $this->entity;

    $actions['publish'] = $actions['submit'];
    $actions['publish']['#publish'] = TRUE;
    $actions['publish']['#dropbutton'] = 'publishing_options';
    $element['publish']['#weight'] = 0;

    if ($entity->isNew()) {
      $actions['publish']['#value'] = t('Save and publish');
    }
    else {
      $actions['publish']['#value'] = $entity->isPublished() ? t('Save and keep published') : t('Save and publish');
    }

    $actions['unpublish'] = $actions['submit'];
    $actions['unpublish']['#publish'] = FALSE;
    $actions['unpublish']['#dropbutton'] = 'publishing_options';
    $element['unpublish']['#weight'] = 10;

    if ($entity->isNew()) {
      $actions['unpublish']['#value'] = t('Save as unpublished');
    }
    else {
      $actions['unpublish']['#value'] = !$entity->isPublished() ? t('Save and keep unpublished') : t('Save and unpublish');
    }

    if (!$entity->isPublished()) {
      $actions['unpublish']['#weight'] = -10;
    }

    $actions['submit']['#access'] = FALSE;

    return $actions;
  }

  /**
   * Form submission handler for the 'save' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   */
  public function save(array $form, FormStateInterface $form_state) {

    /** @var $entity \Drupal\dependent_content\Entity\DependentContentInterface */
    $entity = $this->entity;
    $action = $form_state->getTriggeringElement();

    if (isset($action['#publish'])) {
      $action['#publish'] ? $entity->setPublished() : $entity->setUnpublished();
    }

    $status = parent::save($form, $form_state);
    $label = array(
      '%label' => $entity->label()
    );

    if ($status == SAVED_NEW) {
      drupal_set_message($this->t('Created the %label dependent content.', $label));
    }
    elseif ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('Saved the %label dependent content.', $label));
    }

    $form_state->setRedirect('entity.dependent_content.collection');

    return $status;
  }
}
