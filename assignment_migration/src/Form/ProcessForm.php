<?php

namespace Drupal\assignment_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\assignment_migration\Form
 */
class ProcessForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'assignment_migration_process_records';
  }
  
  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Form constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#type' => 'item',
      '#markup' => t('Click submit button to process data from json to entity'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Process Records'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $config = $this->configFactory->getEditable('assignment_migration.mapping');
    $mapping = $config->getRawData();

    //Get the data from json file.
    $data = file_get_contents(drupal_get_path('module', 'assignment_migration') . '/json_file/cities.json');
    $cities = json_decode($data);
    

    //Delete earlier entities
    $ids = \Drupal::entityQuery('cities_data')->execute();
    $storage_handler = \Drupal::entityTypeManager()->getStorage('cities_data');
    $entities = $storage_handler->loadMultiple($ids);

    $storage_handler->delete($entities);

    $chunks = array_chunk($cities, 5);
    foreach ($chunks as $chunk) {
      $operations[] = [
        '\Drupal\assignment_migration\ProcessDataBatch::process_data',
      [$chunk, $mapping],
      ];
    }
    
    // Setting up batch process.
    $batch = [
      'title' => t('Migrating Data from Json To Entity'),
      'operations' => $operations,
      'finished' => '\Drupal\assignment_migration\ProcessDataBatch::processFinishedCallback',
    ];
    $batch['progress_message'] = FALSE;
    batch_set($batch);
  }
  
}
