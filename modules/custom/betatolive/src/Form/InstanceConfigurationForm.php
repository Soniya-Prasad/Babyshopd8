<?php
/**
 * @file
 * Contains \Drupal\betatolive\Form\StoreForm.
 */

namespace Drupal\betatolive\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\betatolive\Controller\MiscPages;

/**
 * Configure settings for betatolive.
 */
class InstanceConfigurationForm extends ConfigFormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'interface_configuration_form';
	}

	  /** 
   * {@inheritdoc}
   */
	protected function getEditableConfigNames() {
		return [
		  'betatolive.settings',
		];
	}

	public function buildForm(array $form, FormStateInterface $form_state) {

		global $_instance;
		$BETA_DEPLOYMENT_PATH = MiscPages::getBetaDeploymentPath();
		$LIVE_DEPLOYMENT_PATH = MiscPages::getDeploymentPath();
		$LIVE_URL = MiscPages::getLiveUrl();
		$inital_path='/mnt/stor5-wc2-dfw1/495544/';

		if($_instance=='www')
		{
			$form['instance']=array(
				'#type'=>'item',
				'#markup'=>t('Cannot configured the module on live instance'),
			);
			return parent::buildForm($form, $form_state);
		}
		$form['instance']=array(
			'#type'=>'item',
			'#markup'=>t('<b> Beta to live configured on: </b>'. $_instance .' enviornment'),
		);
		$form['beta_path']=array(
				'#type'=>'item',
				'#markup'=>t('<b> Beta Backup Path: </b>'.str_replace($inital_path, '', $BETA_DEPLOYMENT_PATH)),
		);
		$form['live_path']=array(
			'#type'=>'item',
			'#markup'=>t('<b> Live Backup Path: </b>'.str_replace($inital_path, '', $LIVE_DEPLOYMENT_PATH)),
		);
		$form['live_url']=array(
			'#type'=>'item',
			'#markup'=>t('<b> Publish Content on : </b/>'.$LIVE_URL),
		);

		return parent::buildForm($form, $form_state);
	}

	/** 
   * {@inheritdoc}
   */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$config = \Drupal::configFactory()->getEditable('betatolive.settings');

		$instance = $form_state->getValue('instance');
		$config->set('betatolive_instance', $instance)->save();

	    parent::submitForm($form, $form_state);
	}

}
?>