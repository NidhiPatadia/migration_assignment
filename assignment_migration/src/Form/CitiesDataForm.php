<?php

namespace Drupal\assignment_migration\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the cities data entity edit forms.
 */
class CitiesDataForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $entity->save();
    $form_state->setRedirect('entity.cities_data.canonical', ['cities_data' => $entity->id()]);
  }

}
