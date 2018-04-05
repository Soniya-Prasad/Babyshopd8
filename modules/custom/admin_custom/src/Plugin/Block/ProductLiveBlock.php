<?php
namespace Drupal\admin_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Product Live' Block
 *
 * @Block(
 *   id = "product_live",
 *   admin_label = @Translation("Product Live"),
 * )
 */
class ProductLiveBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

		$publish_count = count($this->countProduct(1));
		$unpublish_count = count($this->countProduct(0));

		return array(
		  '#markup' => '<div class="col-lg-3 col-md-6">
					<div class="panel panel-green">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-folder-open-o fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge">'.$publish_count.'</div>
									<div>'.$this->t('Products LIVE').'</div>
								</div>
							</div>
						</div>
						<a href="/admin/product-listing">
							<div class="panel-footer">
								<span class="pull-left">'.$this->t('@count products unpublished',array('@count'=>$unpublish_count)).'</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>',
		);
	}

  	public function countProduct($status = 1) {

	  	$query = \Drupal::database()->select('node_field_data', 'nfd');
		$query->fields('nfd', ['nid', 'title']);
		$query->condition('nfd.type', 'product');
		$query->condition('nfd.status', $status);
		return $query->execute()->fetchAll();
	}
}