<?php
namespace Drupal\site\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides a 'Facebook feeds' Block
 *
 * @Block(
 *   id = "facebook_feeds",
 *   admin_label = @Translation("Facebook Feeds"),
 * )
 */
class FacebookFeedsBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

		//To get the lanuage code:
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();

		$output = '<article class="block">
		              <header class="heading-holder">
		                <h2>'.$this->t('Like us on Facebook').'</h2>
		                <a href="https://www.facebook.com/BabyshopArabia" class="btn btn-blue" target="_blank">'.$this->t('LIKE US').'</a>
		              </header>';
		              
		// To generate access token, hit this url
		//https://graph.facebook.com/oauth/access_token?client_id=188675091526461&client_secret=50453d152cf7b0a9166445c3813f5f1f&grant_type=client_credentials
		//Get the contents of the Facebook page
		// if($language == 'ar') {
		// 	$FBurl = file_get_contents('https://graph.facebook.com/BabyshopArabia/posts?fields=full_picture,message,link&limit=2&locale=ar_AR&access_token=188675091526461|3raJcTupGxH-DXlNM8hC_Si_oQ4');
		// }
		// else {
		// 	$FBurl = file_get_contents('https://graph.facebook.com/BabyshopArabia/posts?fields=full_picture,message,link&limit=2&access_token=188675091526461|3raJcTupGxH-DXlNM8hC_Si_oQ4');
		// }

		$FBurl = file_get_contents('https://graph.facebook.com/BabyshopArabia/posts?fields=full_picture,message,link&limit=2&access_token=188675091526461|3raJcTupGxH-DXlNM8hC_Si_oQ4');

		//Interpret data with JSON
		$fb_posts = json_decode($FBurl);
		//Loop through data for each news item
		foreach ($fb_posts->data as $fb_post) {
			$path = $fb_post->full_picture;
			$fname = strstr(basename($path),'?', true);

			$query = \Drupal::database()->select('file_managed', 'fm');
			$query->fields('fm', ['uri']);
			$query->condition('fm.filename', $fname);
			$query->condition('fm.status', 1);
			$file_uri = $query->execute()->fetchField();

			if(empty($file_uri)) {
				$handle = fopen($path, 'r');
				$file = file_save_data($handle, 'public://facebook/'.$fname,FILE_EXISTS_REPLACE);
				fclose($handle);

				$file_uri = $file->getFileUri();
			}

			$url = ImageStyle::load('facebook');
			$path = $url->buildUri($file_uri);
			$url->createDerivative($file_uri, $path);
			$path = file_create_url($path);
			if(isset($_SERVER['HTTP_REFERER']) && preg_match("/http:/i", $_SERVER['HTTP_REFERER'])) {
				$path = str_replace('http:','https:', $path);
			}

			$message = '';
			if(isset($fb_post->message)) {
				$message = substr($fb_post->message, 0, 130) .((strlen($fb_post->message) > 130) ? '...' : '');
			}

			$output .= '<div class="img-holder"><a href="'.$fb_post->link.'" target="_blank"><img src="'.$path.'" height="180" width="533" alt="image description"></a></div>
              <div class="row-holder">
                <div class="row">
                  <div class="post-box">
                    <div class="text-holder">
                      <p>'.$message.'</p>
                    </div>
                  </div>
                </div>
              </div>';
		}

		$output .= '</article>';

		return array(
		  '#markup' => $output,
		);
	}

}