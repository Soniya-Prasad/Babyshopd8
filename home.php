<?php

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
* @file
* The PHP page that serves all page requests on a Drupal installation.
*
* The routines here dispatch control to the appropriate handler, which then
* prints the appropriate page.
*
* All Drupal code is released under the GNU General Public License.
* See COPYRIGHT.txt and LICENSE.txt.
*/

/**
* Root directory of Drupal installation.
*/

$REDIRECT_country = filter_input(INPUT_SERVER, 'REDIRECT_country');
$terr = filter_input(INPUT_COOKIE, 'terrirory');

if ($terr) {
  if ($terr != $REDIRECT_country) {
    $redirect =  isset($_SERVER['REDIRECT_URL']) ?  $_SERVER['REDIRECT_URL'] : '/en';
    $location = 'http://uat.www2.babyshopstores.com/' . strtolower($terr) . $redirect;
    header("Location: $location");
    ob_end_flush();
    exit;
  }
}

require __DIR__ . '/sites/default/settings.php';
ob_start();
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
  $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else {
  $ip_address = $_SERVER['REMOTE_ADDR'];
}

// if(isset($_GET['country']) && $_GET['country'] !='') {
//   $redirects =  isset($_SERVER['REDIRECT_URL']) ?  $_SERVER['REDIRECT_URL'] : '/en';
//   $urls = '//'. $_SERVER['SERVER_NAME'] .'/'. strtolower($_GET['country']) .''. $redirects ;
//   header("Location: $urls",true, 301);
//   ob_end_flush();
//   exit();
// }

$userCountry = get_user_country($ip_address);

if (!in_array(strtoupper($userCountry), array('AE', 'QA', 'KE', 'TZ', 'KZ', 'BH', 'KW', 'SA', 'YE', 'LY', 'EG', 'OM', 'LB', 'PK', 'IQ', 'TH'))) {
  if($_SERVER['SERVER_NAME'] == 'uat.www2.babyshopstores.com'){
    $userCountry = 'AE';
  }
  elseif($_SERVER['SERVER_NAME'] == 'uat.www2.mothercarestores.com'){
    $userCountry = 'BH';
  }
}

if (isset($userCountry) && strtoupper($userCountry) == 'BH') {
  if(isset($_SERVER['REDIRECT_URL']) && strpos($_SERVER['REDIRECT_URL'],'its-your-style')) {
    $url = "http://uat.www2.mothercarestores.com/bh/en/its-your-style";
  }
  else {
    $url = "http://uat.www2.mothercarestores.com/bh/en";
  }
  
  header("Location: $url");
  ob_end_flush();
  exit();
}

if (isset($userCountry) && strtoupper($userCountry) == 'AE') {
  if(isset($_SERVER['REDIRECT_URL']) && strpos($_SERVER['REDIRECT_URL'],'its-your-style')) {
    $url = "http://www.babyshopstores.com/ae/en/its-your-style";
  }
  else {
    $url = "http://www.babyshopstores.com/ae/en";
  }
  
  header("Location: $url");
  ob_end_flush();
  exit();
}


if (isset($userCountry)) {
  $redirect =  isset($_SERVER['REDIRECT_URL']) ?  $_SERVER['REDIRECT_URL'] : '/en';
  $url = '/' . strtolower($userCountry) . ''. $redirect;
  header("Location: $url");
  ob_end_flush();
  exit();
}

function get_user_country($ip) {
  global $databases;
  $username = $databases['default']['default']['username'];
  $password = $databases['default']['default']['password'];
  $host = $databases['default']['default']['host'];
  $ip_num=sprintf("%u",ip2long($ip));
  $con = mysqli_connect($host, $username, $password);
  $db_selected = mysqli_select_db( $con, $databases['default']['default']['database']);
  if (!$db_selected) {
    die ("Can\'t use test_db : " . mysqli_error($con));
  }
  $query = "SELECT country_code FROM ip2c WHERE '" . mysqli_real_escape_string($con, $ip_num) . "' BETWEEN begin_ip_num AND end_ip_num";
  $result = mysqli_query($con, $query);
  if(!$result) {
    die("Database query failed: " . mysqli_error($con));
  }
  while($res = mysqli_fetch_array($result)) {
    $countryIp = strtolower($res['country_code']);
  }
  return $countryIp;
}
