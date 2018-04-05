<?php
namespace Drupal\admin_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Offers' Block
 *
 * @Block(
 *   id = "offer_count",
 *   admin_label = @Translation("Offer count"),
 * )
 */
class OffersBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

		$publish_count = count($this->countOffers(1));
		$unpublish_count = count($this->countOffers(0));

		return array(
		  '#markup' => '<div class="col-lg-3 col-md-6">
					<div class="panel panel-yellow">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-gift fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge">'.$publish_count.'</div>
									<div>'.$this->t('Offers').'</div>
								</div>
							</div>
						</div>
						<a href="/admin/offers-list">
							<div class="panel-footer">
								<span class="pull-left">'.$this->t('@count offers unpublished',array('@count'=>$unpublish_count)).'</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>',
		);
	}

  	public function countOffers($status = 1) {

	  	$query = \Drupal::database()->select('node_field_data', 'nfd');
		$query->fields('nfd', ['nid', 'title']);
		$query->condition('nfd.type', 'offer');
		$query->condition('nfd.status', $status);
		return $query->execute()->fetchAll();
	}
}