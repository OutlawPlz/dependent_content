<?php
/**
 * @file
 * Contains \Drupal\dependent_content\Form\DependentContentTypeForm
 */

namespace Drupal\dependent_content\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\language\Entity\ContentLanguageSettings;

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

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#default_value' => $entity->label(),
      '#description' => $this->t('The human-readable name of this dependent content type. This text will be displayed as part of the list on the <em>Add dependent content</em> page. This name must be unique.'),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'exists' => '\Drupal\dependent_content\Entity\DependentContentType::load',
        'source' => array('label')
      ),
      '#disabled' => !$entity->isNew(),
      '#description' => $this->t('A unique machine-readable name for this dependent content type. It must only contain lowercase letters, numbers, and underscores. This name will be used for constructing the URL of the <em>Add dependent content</em>, in which underscores will be converted into hyphens.')
      );

    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->get('description'),
      '#description' => $this->t('This text will be displayed on the <em>Add dependent content</em> page'),
    );

    $form['additional_settings'] = array(
      '#type' => 'vertical_tabs'
    );

    if ($this->moduleHandler->moduleExists('language')) {

      $form['language'] = array(
        '#type' => 'details',
        '#title' => t('Language settings'),
        '#group' => 'additional_settings'
      );

      $form['language']['language_configuration'] = array(
        '#type' => 'language_configuration',
        '#group' => 'language',
        '#entity_information' => array(
          'entity_type' => 'dependent_content',
          'bundle' => $this->entity->id(),
        ),
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('dependent_content', $this->entity->id())
      );
    }

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