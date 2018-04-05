<?php

namespace Drupal\site\Controller;

use Drupal\Core\Controller\ControllerBase;

class CountryDetails extends ControllerBase {
  public static function getCountryDetails() {
  	return array(
		'AE'=>array(
			'title'=>'UAE',
			'isd_code'=>'+971',
			'ccode'=>'10',
			'latitude'=>'24.377121',
			'longitude'=>'54.30542',
			'zoom'=>'7',
			),
		'BH'=>array(
			'title'=>'bahrain',
			'isd_code'=>'+973',
			'ccode'=>'1',
			'latitude'=>'26.082688',
			'longitude'=>'50.578308',
			'zoom'=>'10',
			),
		'EG'=>array(
			'title'=>'egypt',
			'isd_code'=>'+20',
			'ccode'=>'3',
			'latitude'=>'26.820553',
			'longitude'=>'30.802498',
			'zoom'=>'6',
			),
		'KW'=>array(
			'title'=>'kuwait',
			'isd_code'=>'+965',
			'ccode'=>'6',
			'latitude'=>'29.31166',
			'longitude'=>'47.481766',
			'zoom'=>'8',
			),
		'OM'=>array(
			'title'=>'oman',
			'isd_code'=>'+968',
			'ccode'=>'7',
			'latitude'=>'21.512583',
			'longitude'=>'55.923255',
			'zoom'=>'6',
			),
		'QA'=>array(
			'title'=>'qatar',
			'isd_code'=>'+974',
			'ccode'=>'8',
			'latitude'=>'25.354826',
			'longitude'=>'51.183884',
			'zoom'=>'8',
			),
		'SA'=>array(
			'title'=>'KSA',
			'isd_code'=>'+966',
			'ccode'=>'9',
			'latitude'=>'23.402765',
			'longitude'=>'45.087891',
			'zoom'=>'5',
			),
		'LB'=>array(
			'title'=>'lebanon',
			'isd_code'=>'+961',
			'ccode'=>'11',
			'latitude'=>'33.854721',
			'longitude'=>'35.862285',
			'zoom'=>'8',
			),
		'KE'=>array(
			'title'=>'kenya',
			'isd_code'=>'+254',
			'ccode'=>'13',
			'latitude'=>'0.219726',
			'longitude'=>'38.38623',
			'zoom'=>'6',
			),
		'YE'=>array(
			'title'=>'yemen',
			'isd_code'=>'+967',
			'ccode'=>'14',
			'latitude'=>'13.624633',
			'longitude'=>'48.098145',
			'zoom'=>'6',
			),
		'PK'=>array(
			'title'=>'pakistan',
			'isd_code'=>'+92',
			'ccode'=>'12',
			'latitude'=>'30.372875',
			'longitude'=>'69.345703',
			'zoom'=>'5',
			),
		'TZ'=>array(
			'title'=>'tanzania',
			'isd_code'=>'+255',
			'ccode'=>'16',
			'latitude'=>'19.081164',
			'longitude'=>'72.861986',
			'zoom'=>'6',
			),
		'LY'=>array(
			'title'=>'libya',
			'isd_code'=>'+218',
			'ccode'=>'50',
			'latitude'=>'26.3351',
			'longitude'=>'17.228331',
			'zoom'=>'6',
			),
		'IQ'=>array(
			'title'=>'Iraq',
			'isd_code'=>'+964',
			'ccode'=>'98',
			'latitude'=>'33.3333',
			'longitude'=>'44.4333',
			'zoom'=>'7',
			),
		'KZ'=>array(
			'title'=>'Kazakhstan',
			'isd_code'=>'+7',
			'ccode'=>'60',
			'latitude'=>'48.0000',
			'longitude'=>'68.0000',
			'zoom'=>'7',
			),
		'TH' => array(
			'title' => 'Thailand',
			'isd_code' => '+66',
			'ccode' => '66',
			'latitude' => '15.8700',
			'longitude' => '100.9925',
			'zoom' => '7',
			),
		);
  }
}