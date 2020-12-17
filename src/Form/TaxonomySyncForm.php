<?php

namespace Drupal\dyniva_content_hub\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class TaxonomySyncForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dyniva_content_hub_taxonomy_sync_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $vocabularies = \Drupal::service('entity.manager')->getStorage('taxonomy_vocabulary')->loadMultiple();
    $options = [];
    foreach($vocabularies as $vocabulary) {
      $options[$vocabulary->id()] = $vocabulary->label();
    }
    $form['vocabularies'] = [
      '#type' => 'checkboxes',
      '#title' => t('Vocabularies'),
      '#required' => true,
      '#options' => $options
    ];

    $options = [];
    $sites = _dyniva_content_receiver_get_sites();
    foreach ($sites as $site) {
      $options[$site['uuid']] = $site['label'];
    }
    $form['sites'] = [
      '#type' => 'checkboxes',
      '#title' => t('Synchronize to'),
      '#required' => true,
      '#options' => $options
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit')
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $vocabularies = $form_state->getValue('vocabularies');
    $sites_enabled = $form_state->getValue('sites');
    $sites_enabled = array_filter($sites_enabled);
    $sites = _dyniva_content_receiver_get_sites();
    $vocStorage = \Drupal::service('entity.manager')->getStorage('taxonomy_vocabulary');
    $termStorage = \Drupal::service('entity.manager')->getStorage('taxonomy_term');
    /* @var \Drupal\dyniva_content_receiver\Client $client */
    $client = \Drupal::service('dyniva_content_receiver.client');

    foreach($vocabularies as $vid) {
      if(!$vid) continue;
      $voc = $vocStorage->load($vid);
      $terms = $termStorage->loadByProperties(['vid' => $vid]);
      foreach($terms as $term) {
        foreach($sites_enabled as $uuid) {
          if(!empty($sites[$uuid])) {
            $client->pushQueue($sites[$uuid]['url'], $term, ['Authorization' => "Uuid " . $uuid]);
          }
        }
      }
    }
    $client->doQueue(65535);
  }

}
