<?php

namespace Drupal\dyniva_content_hub\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

class JsonSyncForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dyniva_content_hub_json_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $uppath = 'public://json_sync/';
    $validators = [
      'file_validate_extensions' => ['json'],
    ];
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Content Json'),
      '#field_name' => 'file',
      '#upload_location' => $uppath,
      '#upload_validators' => $validators,
      '#description' => t('Please select a content json file.'),
      '#required' => TRUE,
    ];

    $options = ['' => t('Select All')];
    $sites = _dyniva_content_receiver_get_sites();
    foreach ($sites as $site) {
      $options[$site['uuid']] = $site['label'];
    }
    $form['sites'] = [
      '#type' => 'checkboxes',
      '#title' => t('Synchronize to'),
      '#required' => true,
      '#options' => $options,
      '#attributes' => [
        'data-action' => 'checkboxes_all'
      ]
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit')
    ];
    $form['#attached']['library'][] = 'dyniva_content_hub/checkboxes_all';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $file = $form_state->getValue('file');
    if(empty($file)) {
      \Drupal::messenger()->addError(t('Please upload file.'));
      return;
    }
    $file = File::load($file['0']);
    $uri = $file->getFileUri();
    $json = file_get_contents($uri);

    $sites_enabled = $form_state->getValue('sites');
    $sites_enabled = array_filter($sites_enabled);
    $sites = _dyniva_content_receiver_get_sites();

    $operations = [];

    foreach($sites_enabled as $uuid) {
      if($uuid && !empty($sites[$uuid])) {
        $operations[] = ['\Drupal\dyniva_content_receiver\BulkRequest::syncJson', [
          $sites[$uuid]['label'],
          $sites[$uuid]['url'],
          $json,
          ['Authorization' => "Uuid " . $uuid]
        ]];
      }
    }

    $batch = [
      'title' => t('Synchronizing...'),
      'operations' => $operations,
      'finished' => '\Drupal\dyniva_content_receiver\BulkRequest::finishedCallback',
      'file' => '\Drupal\dyniva_content_receiver\BulkRequest',
    ];
    batch_set($batch);
  }

}
