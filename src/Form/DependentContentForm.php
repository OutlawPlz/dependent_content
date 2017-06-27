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

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['publishing_options'] = array(
      '#type' => 'details',
      '#title' => t('Publishing options'),
      '#group' => 'advanced',
      '#attributes' => array(
        'class' => array('dependent-content-form-publishing-options'),
      ),
      '#weight' => 51,
      '#optional' => TRUE,
    );

    if (isset($form['published'])) {
      $form['published']['#group'] = 'publishing_options';
    }

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

    /** @var $entity \Drupal\dependent_content\Entity\DependentContent */
    $entity = $this->entity;
    $status = parent::save($form, $form_state);
    $label = array(
      '%label' => $entity->label()
    );

    if ($status == SAVED_NEW) {
      drupal_set_message($this->t('Created the %label dependent content.', $label));
    }
    elseif ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('Saved the %label dependent content.'), $label);
    }

    $form_state->setRedirect('entity.dependent_content.collection');

    return $status;
  }
}