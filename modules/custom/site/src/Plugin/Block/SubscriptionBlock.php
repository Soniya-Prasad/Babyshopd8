<?php
namespace Drupal\site\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Email Subscription' Block
 *
 * @Block(
 *   id = "email_subscribe_block",
 *   admin_label = @Translation("Email Subscription Block"),
 * )
 */
class SubscriptionBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

    $subscribeform = \Drupal::formBuilder()->getForm('Drupal\site\Form\SubscribeForm');

    return array(
        '#theme' => 'email_subscription_block',
        '#subscribe_form' => $subscribeform,
        //'#cache' => array('max-age' => 0),
      );
	}

}