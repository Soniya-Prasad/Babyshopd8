<?php

/**
 * @file
 * Contains \Drupal\admin_custom\Controller\AdminPage.
 */
namespace Drupal\admin_custom\Controller;

use Drupal\Core\Controller\ControllerBase;

class AdminPage extends ControllerBase {
  public function dashboard() {

  	$blocks = '';

  	$block = \Drupal\block\Entity\Block::load('productlive');
	$block_product = \Drupal::entityManager()
	  ->getViewBuilder('block')
	  ->view($block);

	$block = \Drupal\block\Entity\Block::load('offercount');
	$block_offer = \Drupal::entityManager()
	  ->getViewBuilder('block')
	  ->view($block);

	$blocks .= drupal_render($block_product);
	$blocks .= drupal_render($block_offer);
 
    return array(
        '#markup' => $blocks,
    );
  }
}