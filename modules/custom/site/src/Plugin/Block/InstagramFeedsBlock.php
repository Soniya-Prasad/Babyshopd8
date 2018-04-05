<?php
namespace Drupal\site\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Instagram feeds' Block
 *
 * @Block(
 *   id = "instagram_feeds",
 *   admin_label = @Translation("Instagram Feeds"),
 * )
 */
class InstagramFeedsBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

		$output = '';
		$REDIRECT_country = filter_input(INPUT_SERVER, 'REDIRECT_country');

		$result = $this->fetchData("https://api.instagram.com/v1/users/223818477/media/recent/?access_token=223818477.6158c6d.28d9c6eb2a814992bc3be7a43d40bb05&count=11");
		$result = json_decode($result);

		$output .= '<div class="text-container">';
		if($REDIRECT_country == 'BH') {
			$output .= '<h2>'.$this->t('#itsyourstyle').'</h2>';
		}
		else {
			$output .= '<h2>'.$this->t('#babyshop on Instagram').'</h2>';
		}
		
		$output .= '<a href="https://www.instagram.com/babyshoparabia" class="btn btn-blue" target="_blank">'.$this->t('FOLLOW US').'</a>
			      	</div>
			      	<div class="js-masonry" data-masonry-options=\'{"columnWidth": 1, "itemSelector": ".grid-item" }\'>';

		foreach ($result->data as $key => $value) {
			$classes = '';
			$width = $height = 195;
			$thumbnail = str_replace('s150x150/', 's200x200/', $value->images->thumbnail->url);

			if($key == 0) {
				$classes = 'mobile-hidden';
			}
			if($key == 1) {
				$classes = 'wide';
				$width = $height = 400;
				if($value->images->standard_resolution->height == $value->images->standard_resolution->width) {
					$thumbnail = $value->images->standard_resolution->url;
				}
				else if($value->images->low_resolution->height == $value->images->low_resolution->width) {
					$thumbnail = $value->images->low_resolution->url;
				}
				
			}
			if($key > 5) {
				$classes = 'mobile-hidden';
			}

			$output .= '<div class="grid-item '.$classes.'">
							<a href="'.$value->link.'" target="_blank"><img src="'.$thumbnail.'" height="'.$width.'" width="'.$height.'"></a>
						</div>';
		}

		$output .= '</div';

		return array(
		  '#markup' => $output,
		  '#cache' => ['max-age' => 0 ,],
		);
	}

	public function fetchData($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

}