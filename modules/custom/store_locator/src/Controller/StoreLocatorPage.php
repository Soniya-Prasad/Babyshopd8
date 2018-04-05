<?php

/**
 * @file
 * Contains \Drupal\store_locator\Controller\AdminPage.
 */
namespace Drupal\store_locator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\site\Controller\CountryDetails;

class StoreLocatorPage extends ControllerBase {
	public function store_locator() {

		$storeform = \Drupal::formBuilder()->getForm('Drupal\store_locator\Form\StoreForm','store_locator');

		return array(
			'#theme' => 'store_locator_page',
			'#store_form' => $storeform,
			'#attached' => array(
				'library' => array('store_locator/store-map'),
				),
			);
	}

	public function store_callback($location) {
	  // Get parameters from URL
		global $base_url;
		$language = \Drupal::languageManager()->getCurrentLanguage()->getId();

	  //Collect parameter
		$searchlocation = $location;
	  // Start XML file, create parent node
		$dom = new \DOMDocument();
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node);

		if($searchlocation){

	  	// switch database
			\Drupal\Core\Database\Database::setActiveConnection('lmg_common');
		// Get a connection going
			$db = \Drupal\Core\Database\Database::getConnection();

		//$query = 'select sl.*,sc.* from  store_common_data sc INNER JOIN store_lang_specific sl ON sl.tran_store_id = sc.store_id where sc.brand_id = 1 AND status = 1';

			$query = $db->select('store_common_data', 'sc');
			$query->join('store_lang_specific', 'sl', 'sl.tran_store_id = sc.store_id');
			$query->fields('sc');
			$query->fields('sl');
			//$query->condition('sl.language', $language);
			$query->condition('sc.brand_id', 1);
			$query->condition('sl.status', 1);

		//print "<pre>"; print_r($stores_data); die;

			$pos="";
			$pos = strpos($searchlocation, ',');
			if($pos!==false)
			{
				$searchlocationArray = explode(',',$searchlocation);
			}
			else
			{
				$searchlocationArray[0] = '';
				$searchlocationArray[1] = $searchlocation;
			}
			$havecity = 0;
			$havecountry = 0;

			if($searchlocationArray[1] != "")
			{

				$getCountry = $db->select('store_country_details','c')
				->fields('c',array('country_id','country_name'))
				->condition('country_name',trim($searchlocationArray[1]),'Like')
				->execute()
				->fetchAssoc();

				if($getCountry['country_id']!="")
				{
			  //$query.= ' AND country_id = '.$getCountry['country_id'];
					$query->condition('sl.country_id', $getCountry['country_id']);
					$havecountry = 1;
				}
			}
			if($searchlocationArray[0]!="")
			{
				$getCity = $db->select('store_city_details','ci')
				->fields('ci',array('city_id','city_name'))
				->condition('city_name',trim($searchlocationArray[0]),'Like')
				->execute()
				->fetchAssoc();
				if($getCity['city_id'] != "")
				{
			  //$query.= ' AND city_id = '.$getCity['city_id'];
					$query->condition('sl.city_id', $getCity['city_id']);
					$havecity = 1;
				}
			}

			if($havecity == 1 || $havecountry == 1)
			{
				$result = $query->execute()->fetchAll();

			// $result_count = count($result);

			// // Paging
			// $PageNo = (isset($page) && !empty($page)) ? $page : 1;

			// $KeyCount = 0;

			// $RowsPerPage = 5;
			// $LastPage      = ceil($result_count/$RowsPerPage);
			// $PageNo = (int)$PageNo;

			// if ($PageNo <= 1 || $PageNo == "")
			// {
			//    $PageNo = 1;
			// }
			// elseif ($PageNo > $LastPage)
			// {
			//    $PageNo = $LastPage;
			// }


			// $LIMIT = ' limit ' .($PageNo - 1) * $RowsPerPage .',' .$RowsPerPage;



			// $query.=$LIMIT;
	  //       #echo $query;exit;
			// $result = $db->query($query);

			// Search the rows in the markers table
				if (count($result) > 0) {
				// Iterate through the rows, adding XML nodes for each
					foreach($result as $record) {
						$address = "";
						if(trim($record->store_address1)) {
							$address = $record->store_address1.", ";
						}
						if(trim($record->store_address2)) {
							$address.= $record->store_address2.", ";
						}
						if(isset($record->store_zip) && trim($record->store_zip)) {
							$address.= $record->store_zip.", ";
						}
						if(isset($getCity['city_name']) && trim($getCity['city_name'])) {
							if(trim($getCountry['country_name'])) {
								$address.= $getCity['city_name'].", ";
							}
							else {
								$address.= $getCity['city_name']." ";
							}
						}
						if(trim($getCountry['country_name'])) {
							$address.= $getCountry['country_name']." ";
						}

						$address = substr($address,0,-1);

						$node = $dom->createElement("marker");
						$newnode = $parnode->appendChild($node);
						$newnode->setAttribute("name", $record->store_title);

						$newnode->setAttribute("timings", $record->store_timing);
						$newnode->setAttribute("size", $record->store_size);
						$newnode->setAttribute("phone", $record->phone_no);

						$newnode->setAttribute("address",wordwrap($address, 80, "<br />"));
						$address_new = $record->store_address1.",";
						$address_new = $address_new.$address;
						$newnode->setAttribute("getaddress", $address_new);

						$newnode->setAttribute("lat", $record->lattitude);
						$newnode->setAttribute("lng", $record->longitude);

					}
					$noresult = $dom->createElement("haveresult");
					$nonode = $parnode->appendChild($noresult);
					$nonode->setAttribute("status",'yes');

				// $node_page = $dom->createElement("pager");
				//$newnode = $parnode->appendChild($node_page);
				// $newnode->setAttribute("lastpage", $LastPage);
				// $newnode->setAttribute("curpage", $PageNo);
				}
				else {
					$noresult = $dom->createElement("haveresult");
					$nonode = $parnode->appendChild($noresult);
					$nonode->setAttribute("status",'no');
				}

			}
			else {
				$noresult = $dom->createElement("haveresult");
				$nonode = $parnode->appendChild($noresult);
				$nonode->setAttribute("status",'no');
			}
		}
		else
		{
			$noresult = $dom->createElement("haveresult");
			$nonode = $parnode->appendChild($noresult);
			$nonode->setAttribute("status",'no');
		}
		\Drupal\Core\Database\Database::setActiveConnection();
		header("Content-type: text/xml");
		echo $dom->saveXML(); exit;
	}

	
}