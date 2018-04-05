<?php

/**
 * @file
 * Contains \Drupal\faq\Controller\FaqPage.
 */
namespace Drupal\faq\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class FaqPage extends ControllerBase {
	public function faq_listing() {
		$faq_content = array();
		$faq_categories = array();
		$country_url = $_SERVER['REDIRECT_country'];
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();

		$query = db_select('taxonomy_term_field_data', 'ttfd');
		$query->fields('ttfd', array('name','tid'));
		$query->condition('vid', "faq");
		$query->condition('langcode', $language);
		$query->orderBy('weight', 'ASC');
		$tids = $query->execute()->fetchAll();

		foreach($tids as $tid) {
			$faq_categories[$tid->tid] = $tid->name;
		}

		foreach ($faq_categories as $tid => $term) {
			$query = db_select('node_field_data', 'nfd');
			$query->distinct();
		    $query->join('node__field_faq_category', 'nffc', 'nfd.nid = nffc.entity_id');
		    $query->join('node__body', 'nb', 'nfd.nid = nb.entity_id');
		    $query->join('node__field_country', 'nfc', 'nfd.nid = nfc.entity_id');
		    $query->fields('nfd', array('title'));
		    $query->fields('nb', array('body_value'));
		    $query->condition('nfd.type','faq','=');
		    $query->condition('nfd.status',1,'=');
		    $query->condition('nffc.deleted',0,'=');
		    $query->condition('nb.deleted',0,'=');
		    $query->condition('nffc.field_faq_category_target_id',$tid,'=');
		     if($country_url == "BH") {
		    	$query->condition('nfc.field_country_value',$country_url);
			}
			else {
		    	$query->condition('nfc.field_country_value',array('BH'),'NOT IN');
			}
			$query->condition('nfd.langcode', $language);
			$query->condition('nb.langcode', $language);
		    $query->orderBy('nfd.created', 'DESC');
		    $result = $query->execute()->fetchAll();

		    foreach ($result as $node) {
		    	$node->body_value = strip_tags($node->body_value,"<a>");
		    	$faq_content[$tid][] = $node;
		    }
		}

		return array(
			'#theme' => 'faq_page',
			'#faq_categories' => $faq_categories,
			'#faq_content' => $faq_content,
			'#country' => $country_url,
		);
	}
	
}