<?php

namespace Drupal\export_to_csv\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\field\FieldConfigInterface;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity;
use Drupal\taxonomy\Entity\Vocabulary;


/**
 * Provides route responses for the Example module.
 */
class ExportPage extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
        

    $element = array(
     '#markup' => '<a href="/modules/export_to_csv/export_products.csv" download="export_products.csv">Download</a>',
    );
    return $element;
    }

}
