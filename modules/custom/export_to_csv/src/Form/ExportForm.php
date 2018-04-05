<?php

namespace Drupal\export_to_csv\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use \Drupal\file\Entity\File;
use \Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\user\Entity\User;
use Drupal\Core\Controller\ControllerBase;
use Drupal\field\FieldConfigInterface;
use Drupal\node\Entity\NodeType;

class ExportForm extends FormBase {
  /**
   * {@inheritDoc}
   */
  public function getFormId() {
        return 'exportform';

  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Product Export'),
      '#button_type' => 'primary',
      '#prefix' => '<h3>Export page</h3><p>After clicking the export button products will be exported and is stored in export_products.csv.</p><br><p>To download the file click on download link.</p>',
      '#suffix' => '<br><div class="download_link"><a href="'.$base_url.'/sites/default/files/export_products.csv" download="export_products.csv">Download</a></div>',
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
    $product_file = 'public://export_products.csv';
    if (file_exists($product_file)) {
      unlink($product_file);
    }
    $nids = \Drupal::entityQuery('node')->condition('type','product')->execute();
    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

    $handle = fopen($product_file, 'w') or die('Cannot open file:  '.$product_file); //implicitly creates file
    fputcsv($handle, array('ID', 'Product Name', 'Brand','Department','Category','Tag','SKU_ID','Image Path','Start Date','End Date','Status','Language', 'Product Name Ar', 'Brand Ar','Category Ar','Tag Ar','Image Path Ar','Language Ar'));

    $values = '';
    
    foreach($nodes as $key=>$val) {

      $image_url = "";
      $department = 'Clothing';
      $term_brand_ar = '';
      $term_category_ar = '';
      $term_tag_ar = '';
      $title_ar = '';
      $image_url_ar = '';
      $language_ar = '';

      $term = Term::load($val->get('field_product_brand')->target_id);
      $term_brand = $term->getName();

      $term = Term::load($val->get('field_product_category')->target_id);
      $term_category = $term->getName();

      $term = Term::load($val->get('field_tags')->target_id);
      $term_tag = $term->getName();

      $title = $val->title->value;
      $sku = $val->get('field_sku')->value;
      $status = $val->status->value;
      $language = $val->langcode->value;

      if($val->get('field_image')->target_id) {
        $file = \Drupal\file\Entity\File::load($val->get('field_image')->target_id);
        $path = $file->getFileUri();
        $image_url = file_create_url($path);
      }

      $start_date = $val->get('field_product_validity')->value;
      $end_date = $val->get('field_product_validity')->end_value;

      if ($val->hasTranslation('ar')) {
        $translation = $val->getTranslation('ar');

        $term = Term::load($translation->get('field_product_brand')->target_id);
        $term_brand_ar = $term->getName();

        $term = Term::load($translation->get('field_product_category')->target_id);
        $term_category_ar = $term->getName();

        $term = Term::load($translation->get('field_tags')->target_id);
        $term_tag_ar = $term->getName();

        $title_ar = $translation->title->value;
        $language_ar = $translation->langcode->value;

        if($translation->get('field_image')->target_id) {
          $file = \Drupal\file\Entity\File::load($translation->get('field_image')->target_id);
          $path = $file->getFileUri();
          $image_url_ar = file_create_url($path);
        }
      }

      $values .= $key.','.$title.','. $term_brand.','.$department.','.$term_category.',"'.$term_tag.'",'.$sku.','.$image_url.','.$start_date.','.$end_date.','.$status.','.$language.','.$title_ar.','.$term_brand_ar.','.$term_category_ar.',"'.$term_tag_ar.'",'.$image_url_ar.','.$language_ar."\n";                                
    }
    fwrite($handle, $values); 
    fclose($handle);
  }
}