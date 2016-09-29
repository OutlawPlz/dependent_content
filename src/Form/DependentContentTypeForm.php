<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Form\DependentContentTypeForm
 */

namespace Drupal\dependent_content\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the dependent content type edit forms.
 */
class DependentContentTypeForm extends EntityForm  {

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

    /** @var $entity \Drupal\dependent_content\Entity\DependentContentType */
    $entity = $this->entity;
    $form = parent::form($form, $form_state);

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\dependent_content\Entity\DependentContentType::load',
      ),
      '#disabled' => !$entity->isNew(),
    );

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t('Label for the dependent content type.'),
      '#required' => TRUE,
    );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->getDescription(),
      '#description' => $this->t('Enter a description for this dependent content type.'),
    );

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

    /** @var $entity \Drupal\dependent_content\Entity\DependentContentType */
    $entity = $this->entity;
    $status = parent::save($form, $form_state);
    $label = array(
      '%label' => $entity->label()
    );

    if ($status == SAVED_NEW) {
      drupal_set_message($this->t('Created the %label dependent content type.', $label));
    }
    elseif ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('Saved the %label dependent content type.'), $label);
    }

    $form_state->setRedirect('entity.dependent_content_type.collection');

    return $status;
  }
}