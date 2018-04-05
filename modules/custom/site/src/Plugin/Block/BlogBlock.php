<?php
namespace Drupal\site\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Blog' Block
 *
 * @Block(
 *   id = "blog_list",
 *   admin_label = @Translation("Find Inspiration"),
 * )
 */
class BlogBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
	public function build() {

		$output = '';
		$style = '';
		$blogs = $this->getBlogs(3);

		//To get the lanuage code:
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();

		$country = strtolower($_SERVER['REDIRECT_country']);

		foreach ($blogs as $key => $blog) {

			$image_uri = isset($blog->uri) ? file_create_url($blog->uri) : '';

			if($key == 0) {
				$image_uri = !empty($blog->fmi_uri) ? $blog->fmi_uri : $blog->uri;
				$image_uri = file_create_url($image_uri);
			}

			if($key == 1) {
				$output .= '<div class="twocolumns">';
			}

			if($key == 1 || $key == 2) {
				$output .= '<div class="column">';
				$style = 'style0'.$key;
			}

			$url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$blog->nid);

			$output .= '<div class="visual-container '.$style.'">
					        <div class="wrap">
					          <h1>'.$blog->title.'</h1>
					          <a href="/'.$country.'/'.$language.$url.'" class="btn">'.$this->t('READ MORE').'</a>
					        </div>
					        <div class="bg-stretch">
					          <span data-srcset="'.$image_uri.'"></span>
					        </div>
					    </div>';

			if($key == 1 || $key == 2) {
				$output .= '</div>';
			}
			
		}

		$output .= '</div>';

		return array(
		  '#markup' => $output,
		  '#cache' => ['max-age' => 0 ,],
		);
	}

	public function getBlogs($limit = 10) {

		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();

	  	$query = \Drupal::database()->select('node_field_data', 'nfd');
	  	$query->distinct();
		$query->join('node__field_blog_image', 'nfbi', 'nfbi.entity_id = nfd.nid');
		$query->leftjoin('node__field_homepage', 'nfh', 'nfh.entity_id = nfd.nid');
		$query->join('file_managed', 'fm', 'fm.fid = nfbi.field_blog_image_target_id');
		$query->leftjoin('file_managed', 'fmi', 'fmi.fid = nfh.field_homepage_target_id');
		$query->leftjoin('node__field_posted_on_', 'nfpo', 'nfpo.entity_id = nfd.nid');
		$query->fields('nfd', ['nid', 'title']);
		$query->fields('fm', ['uri']);
		$query->fields('fmi', ['uri']);
		$query->condition('nfd.type', 'blogs');
		$query->condition('nfd.langcode', $language);
		$query->condition('nfd.status', 1);
		$query->condition('fm.status', 1);
		$query->condition('nfbi.deleted', 0);
		$query->orderBy('nfpo.field_posted_on__value', 'DESC');
		$query->range(0, $limit);
		$output = $query->execute()->fetchAll();

		return $output;
	}

}