<?php

namespace Drupal\assignment_migration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Class MappingForm.
 */
class MappingForm extends ConfigFormBase {
  
  /**
   * Entity field type manager service.
   *
   * @var Drupal\Core\Entity\EntityFieldManager
   */
  protected $fieldManager;


  /**
   * Construct function for this controller.
   */
  public function __construct(EntityFieldManagerInterface $fieldManager) {
    $this->fieldManager = $fieldManager;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'assignment_migration.mapping',
    ];
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mapping_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $config = $this->config('assignment_migration.mapping');

    $entity_fields = $this->fieldManager->getFieldDefinitions('cities_data','cities_data');
    unset($entity_fields["id"]);
    unset($entity_fields["uuid"]);
    unset($entity_fields["uid"]);
    unset($entity_fields["metatag"]);
    foreach ($entity_fields as $key => $value) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => ($value instanceof BaseFieldDefinition) ? $value->getLabel()->getUntranslatedString() : $value->getLabel(),
        '#default_value' => $config->get($key),
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $entity_fields = $this->fieldManager->getFieldDefinitions('cities_data','cities_data');
    unset($entity_fields["id"]);
    unset($entity_fields["uuid"]);
    unset($entity_fields["uid"]);
    unset($entity_fields["metatag"]); 
    foreach ($entity_fields as $key => $value) {
      $this->config('assignment_migration.mapping')->set($key, $form_state->getValue($key))->save();
    }
  }

}
