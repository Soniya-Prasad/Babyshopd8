<?php
/**
 * @file
 * Contains \Drupal\store_locator\Form\StoreForm.
 */

namespace Drupal\store_locator\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\site\Controller\CountryDetails;

/**
 * Contribute form.
 */
class StoreForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'store_locator_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state, $page = '') {
		global $base_url, $default_country;
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();
		$countries = CountryDetails::getCountryDetails();
		if(isset($_SERVER['REDIRECT_country'])) {
			$country = $_SERVER['REDIRECT_country'];
			$Selectedcountry = $countries[$_SERVER['REDIRECT_country']];
		}
		else {
			$country = $default_country;
			$Selectedcountry = $countries[$country];
		}

		$country_name = $this->get_contry_name($Selectedcountry['ccode']);

		$data = $this->load_store_data();
		$data = array_shift($data);
			$sanitizedata = '';
			$splitter = '';
		foreach($data as $country_data) {
		 	$sanitizedata .= $splitter.$country_data->city_name.', '.$country_data->country_name;
		 	$splitter = ':';
		}

		$form['#attributes'] = array('onsubmit'=>'javascript:return searchLocations();','id'=>'store-locator-search');
		if($page == 'home') {
			$form['searchstring'] = array(
				'#type'=>'textfield',
				//'#default_value'=> '',
				'#prefix' => '<div class="input-wrap">',
				'#suffix' => '</div>',
				'#weight' => '1',
				'#attributes'=>array(
					'id'=>'store-locator-search-input',
					'size'=>'25',
					'maxlength'=>'50',
					'placeholder'=>$this->t('Your Location'),
				),
			);
		}
		else {
			$form['searchstring'] = array(
				'#type'=>'textfield',
				//'#default_value'=> '',
				'#weight' => '0',
				'#attributes'=>array(
					'id'=>'store-locator-search-input',
					'class'=>array('sm-text-box-big'),
					'size'=>'25',
					'maxlength'=>'50',
					'placeholder'=>$this->t('Search Location'),
				),
			);
		}
		$form['baseurl']=array(
			'#name'=>'site_url',
			'#type'=>'hidden',
			'#value'=>$base_url,
			'#attributes'=>array('id'=>'site_url'),
		);
		$form['latitude']=array(
			'#name'=>'latitude',
			'#type'=>'hidden',
			'#value'=>$Selectedcountry['latitude'],
			'#attributes'=>array('id'=>'latitude'),
		);
		$form['longitude']=array(
			'#name'=>'longitude',
			'#type'=>'hidden',
			'#value'=>$Selectedcountry['longitude'],
			'#attributes'=>array('id'=>'longitude'),
		);
		$form['zoom']=array(
			'#name'=>'zoomlevel',
			'#type'=>'hidden',
			'#value'=>$Selectedcountry['zoom'],
			'#attributes'=>array('id'=>'zoomlevel'),
		);

		$form['store_data']=array(
			'#name'=>'store_data',
			'#type'=>'hidden',
			'#value'=>$sanitizedata,
			'#attributes'=>array('id'=>'store_data'),
		);

		$form['location']=array(
			'#name'=>'location',
			'#type'=>'hidden',
			'#value'=>$country_name,
			'#attributes'=>array('id'=>'location'),
		);

		$form['language']=array(
			'#name'=>'language',
			'#type'=>'hidden',
			'#value'=>$language,
			'#attributes'=>array('id'=>'language'),
		);

		if($page == 'home') {
			$form['search_button'] = array(
			  '#type' => 'button',
			  '#attributes' => array(
			  	'class' => array('btn-black','btn-large')
			  ),
			  '#weight' => '0'
			);
		}
		else {
			$form['search_button'] = array(
			  '#type' => 'button',
			  '#attributes' => array(
			  	'class' => array('btn-search')
			  ),
			  '#weight' => '1'
			);
		}

		return $form;
	}

  	public function load_store_data() {
  		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();
		// switch database
		\Drupal\Core\Database\Database::setActiveConnection('lmg_common');

		// Get a connection going
		$db = \Drupal\Core\Database\Database::getConnection();

	  	$query = $db->select('store_country_details', 'scd');
	  	$query->distinct();
		$query->join('store_city_details', 'scid', 'scd.country_id = scid.country_id');
		$query->join('store_lang_specific', 'sls', 'sls.city_id = scid.city_id');
		$query->join('store_common_data', 'scmd', 'scmd.store_id = sls.tran_store_id');
		$query->fields('scd', ['country_name']);
		$query->fields('scid', ['city_name']);
		// $query->condition('sls.language', $language);
		// $query->condition('scd.language', $language);
		// $query->condition('scid.language', $language);
		$query->condition('scmd.brand_id', 1);
		$query->condition('sls.status', 1);
		$stores_data = $query->execute()->fetchAll();

		// set default database
		\Drupal\Core\Database\Database::setActiveConnection();

		return $stores=array($stores_data);
	}

	public function get_contry_name($country_id) {
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();
		// switch database
		\Drupal\Core\Database\Database::setActiveConnection('lmg_common');

		// Get a connection going
		$db = \Drupal\Core\Database\Database::getConnection();

	  	if ($language == 'ar') {
			$getCountry = $db->select('store_country_details','c')
				->fields('c',array('country_name'))
				->condition('tran_country_id',$country_id)
				->execute()
				->fetchField();
		}
		else {
			$getCountry = $db->select('store_country_details','c')
				->fields('c',array('country_name'))
				->condition('country_id',$country_id)
				->execute()
				->fetchField();
		}

		// set default database
		\Drupal\Core\Database\Database::setActiveConnection();

		return $getCountry;
	}

	public function checkString($string) { 
	    if ( strlen($string) == 0 ) 
	        return $string; 

		$return = 1;
		$string = preg_split("//", $string, -1, PREG_SPLIT_NO_EMPTY); 
	    $ord = 0; 
	    for ( $i = 0; $i < count($string); $i++ ) { 
	        $ord = ord($string[$i]);
			if ( ($ord > 64 && $ord < 91) || ($ord > 96 && $ord < 123) || $ord == 44 || ($ord > 47 && $ord < 58) || $ord == 32) { 
				// Do Nothing
	        }else{
				$return = 0;
			}
		}
		return ($return == 1)?true:false;
	} 

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
?>