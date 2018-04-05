<?php
namespace Drupal\store_locator\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\site\Controller\CountryDetails;

/**
 * Provides a 'Store locator' Block
 *
 * @Block(
 *   id = "store_locator",
 *   admin_label = @Translation("Store Locator"),
 * )
 */
class StoreLocatorBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

    $storeform = \Drupal::formBuilder()->getForm('Drupal\store_locator\Form\StoreForm','home');

    return array(
        '#theme' => 'store_locator_block',
        '#store_form' => $storeform,
        '#attached' => array(
          'library' => array('store_locator/store-map'),
        ),
        '#cache' => ['max-age' => 0 ,],
      );
	}

}