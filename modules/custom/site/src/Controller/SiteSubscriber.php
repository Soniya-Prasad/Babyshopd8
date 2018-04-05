<?php
/**
 * @file
 * Contains \Drupal\site\Controller\SiteSubscriber.
 */

namespace Drupal\site\Controller;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SiteSubscriber implements EventSubscriberInterface  {
	/**
	* // only if KernelEvents::REQUEST !!!
	* @see Symfony\Component\HttpKernel\KernelEvents for details
	*
	* @param Symfony\Component\HttpKernel\Event\GetResponseEvent $event
	*   The Event to process.
	*/
	public function BootLoad(GetResponseEvent $event) {
		// @todo remove this debug code
		// Set global variables for blogs

		global $default_country;

		$server_name = $_SERVER['HTTP_HOST'];

		$default_country = $_SERVER['REDIRECT_country'];

		if($_SERVER['SERVER_NAME'] == 'uat.www2.babyshopstores.com'){
			$country = isset($_SERVER['REDIRECT_country']) ? strtolower($_SERVER['REDIRECT_country']) : 'sa';

			$default_country = 'SA';
		}

		if (!empty($_SERVER['REDIRECT_country'])) {
			$country_name = $_SERVER['REDIRECT_country'];
			if($country_name == 'BH' && $_SERVER['SERVER_NAME'] == 'uat.www2.babyshopstores.com') {
				$location = "http://uat.www2.mothercarestores.com/" . strtolower($country_name) . '/' . $_GET['q'];
				header("Location: $location", true, 301);
				exit;
			}else if($country_name != 'BH' && $_SERVER['SERVER_NAME'] == 'uat.www2.mothercarestores.com') {
				$location = "http://uat.www2.babyshopstores.com/" . strtolower($country_name) . '/' . $_GET['q'];
				header("Location: $location", true, 301);
				exit;
			}

			if($_SESSION['location'] == 'BH' && $_SERVER['SERVER_NAME'] == 'www2.babyshopstores.com') {
				$location = "http://www.mothercarestores.com/" . strtolower($_SESSION['location']) . '/' . $_GET['q'];
				header("Location: $location", true, 301);
				exit;
			}else if($_SESSION['location'] != 'BH' && $_SERVER['SERVER_NAME'] == 'www2.babyshopstores.com') {
				$location = "http://www2.babyshopstores.com/" . strtolower($_SESSION['location']) . '/' . $_GET['q'];
				header("Location: $location", true, 301);
				exit;
			}

		}
		
	}

	/**
	* {@inheritdoc}
	*/
	static function getSubscribedEvents() {
		$events[KernelEvents::REQUEST][] = array('BootLoad', 20);
		return $events;
	}
}