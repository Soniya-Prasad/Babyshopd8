<?php

namespace Drupal\import_csv\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
use \Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;

class ImportForm extends FormBase {
  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'fruitform';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['source'] = [
    '#type' => 'managed_file',
    '#title' => $this->t('File'),
    '#description' => $this->t('Select a file from your local system.'),
    '#upload_validators' => [
      'file_validate_extensions' => ['csv'],
      ],
      //'#upload_location' => 'public://',
      '#required' => TRUE,
    ];
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $csv_path = drupal_realpath('public://');
    $fid = $form_state->getValue('source');
    $file = \Drupal\file\Entity\File::load($fid['0'])->toArray();
    $file_name = $file['uri']['0']['value'];
    $i = 0;
    if (($handle = fopen($file_name, "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if($i > 0) {
          if(isset($data[0]) && is_numeric($data[0])) {
            // Update node object with attached file.
            $node = Node::load($data[0]);
            $node->setTitle($data[1]);
            $node->set('langcode', $data[11]);
            $node->set("status", $data[10]);
          }
          else {
            // Create node object with attached file.
            $node = Node::create([
              'type'        => 'product',
              'title'       => $data[1],
              'field_product_validity' => array('value' => $data[8],
                                       'end_value' => $data[9]),
              //The base language
              'langcode' => $data[11],
              'status'   => $data[10],
            ]);
          }

          if(isset($data[6]) && !empty($data[6])) {
            $node->set("field_sku", $data[6]);
          }

          if(isset($data[7]) && !empty($data[7])) {
            if (file_exists('public://JPEG/'.basename($data[7]))) {
              $uri  = file_unmanaged_copy('public://JPEG/'.basename($data[7]), 'public://product/'.basename($data[7]));
              $file = File::Create([
                'uri' => $uri,
                'status' => 1,
              ]);
              $file->save();

              $field_image = array(
                  'target_id' => $file->id(),
                  'title' => $data[1],
              );
              $node->field_image = $field_image;
            }
          }

          if(isset($data[2]) && !empty($data[2])) {
            $properties['name'] = $data[2];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node->field_product_brand->target_id = $term->id();
          }

          if(isset($data[4]) && !empty($data[4])) {
            $properties['name'] = $data[4];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node->field_product_category->target_id = $term->id();
          }

          if(isset($data[5]) && !empty($data[5])) {
            $properties['name'] = $data[5];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node->field_tags->target_id = $term->id();
          }

          $node->save();

          if(isset($data[12]) && !empty($data[12])) {
            $node_ar = $node->hasTranslation('ar') ? $node->getTranslation('ar') : $node->addTranslation('ar');
            $node_ar->title = $data[12];
            $node_ar->langcode = $data[17];

            if(isset($data[16]) && !empty($data[16])) {
              if (file_exists('public://JPEG/'.basename($data[16]))) {
                $uri  = file_unmanaged_copy('public://JPEG/'.basename($data[16]), 'public://product/'.basename($data[16]));
                $file = File::Create([
                  'uri' => $uri,
                  'status' => 1,
                ]);
                $file->save();

                $field_image = array(
                    'target_id' => $file->id(),
                    'title' => $data[12],
                );
                $node_ar->field_image = $field_image;
              }
            }

            $properties['name'] = $data[13];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node_ar->field_product_brand->target_id = $term->id();

            $properties['name'] = $data[14];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node_ar->field_product_category->target_id = $term->id();

            $properties['name'] = $data[15];
            $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
            $term = reset($terms);
            $node_ar->field_tags->target_id = $term->id();

            $node_ar->save();
          }
        }
        $i++;
      }
      fclose($handle);
    }
   
  }
}