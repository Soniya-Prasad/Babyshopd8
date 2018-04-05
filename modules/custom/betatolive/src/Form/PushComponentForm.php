<?php
/**
 * @file
 * Contains \Drupal\betatolive\Form\PushComponentForm.
 */

namespace Drupal\betatolive\Form;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\betatolive\Controller\MiscPages;
use Drupal\Core\Url;

/**
 * Contribute form.
 */
class PushComponentForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'push_component_form';
	}

	/********************************** Taking Backup***************************************/

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {

		global $_instance;
		$error = false;

		$url = Url::fromRoute('betatolive.instance_configuration_form');
		$url->setOptions(array('attributes'=>array('target'=>'_blank')));
		$instance_config_link = \Drupal::l('here', $url);

		drupal_set_message(t('Before pushing the content to live confirm the settings @link', array('@link' => $instance_config_link)));
		
		$databases = MiscPages::getLiveDatabase();
		/* Making sure live databaes is reachable */
		if(!MiscPages::_test_connection())
		{
			$form['message_'.MiscPages::getLiveDatabaseKey()]=array(
			'#type'=>'item',
			'#markup'=>t('Cannot establish connection with the database %database',array('%database'=>$databases['default']['database'])),
			);
			$error=true;
		}

		/* Making sure all the enviornment variable are defiend */
		$constants_status = MiscPages::_get_constants_status();
		if($constants_status['error_type'])
		{
			$form['message_'.$constants_status['var']]=array(
			'#type'=>'item',
			'#markup'=>$constants_status['message'],
			);
			$error=true;
		}

		/* If error message display the error message */
		if($error)
			return $form;

		/* Making sure that all table should get involved in operation */
		$available_tables = MiscPages::betatolive_get_database_tables();
		$expire = time() + 1800;
		\Drupal::cache()->set('betatolive_database_tables', $available_tables, $expire);
		$options = array();
		if($_instance=='dev')
		{
			$options=array(
				'source-code' => t('Source-Code'),
			);
		}
		else if($_instance=='beta')
		{
			$options = array(
				'source-code' => t('Source-Code'),
				'content' => t('Content')
			);
			$deployment_path = MiscPages::getDeploymentPath();
	    	shell_exec('chmod -R 0777 '.$deployment_path.'/sites/default/files/* > /dev/null 2>/dev/null &');
	    	shell_exec('chmod -R 0777 '.$deployment_path.'/sites/all/* > /dev/null 2>/dev/null &');
		}
		else if($_instance=='www')
		{
			throw new AccessDeniedHttpException(); exit;
		}

		$form['components']= array(
			'#title'=>($_instance=='dev')?t('Components to publish to beta'):t('Components to publish'),
			'#type'=>'checkboxes',
			'#options' =>$options,
			'#required'=>true,
		);

		$form['data_to_push']=array(
			'#type' => 'textarea', 
			'#title' => t('List all changes you have made'),
			'#required'=>true,
		);

		$form['submit']=array(
			'#type'=>'submit',
			'#value'=>'Backup & Publish',
			//'#attributes'=>array('onclick'=>"this_btn=this;this.value='Please Wait...';setTimeout('this_btn.disabled=true',5)"),
		);

		$items_array = array(
		    '#theme' => 'betatolive_message',
		    '#instance' => $_instance,
		);

		$form['message']=array(
			'#markup' => \Drupal::service('renderer')->render($items_array),
			'#type' => 'item',
		);

		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$values = array_filter($form_state->getValue('components'));
		$data = serialize($form_state->getValue('data_to_push'));
		$_SESSION['form_comment'] = $data;
		$data = unserialize($data);

		// Set data in a variable
		\Drupal::configFactory()->getEditable('betatolive.settings')->set('pusheddata', $data)->save();

		if(isset($values['content']) && !isset($values['source-code'])) 
		{
			 $arg = array('betatolive_backup_database','betatolive_backup_media_files');
		}
		else if(isset($values['source-code']) && !isset($values['content'])) 
		{
			 $arg = array('betatolive_backup_source_code');
		}
		else if(isset($values['source-code']) && isset($values['content'])) 
		{
			 $arg = array('betatolive_backup_database','betatolive_backup_media_files','betatolive_backup_source_code');
		}
		$arg[] = 'betatolive_backup_status';
		$_SESSION['http_request_count'] = 0; // reset counter for debug information.

		//Prepare the batch
		$batch = self::betatolive_backup_component($arg);
		batch_set($batch);
	}

	/* Function that prepare batch as per request */
	public function betatolive_backup_component($callback_operations) {

		$operations = array();
		$time = time();

		foreach($callback_operations as $callback_operation) {
			$operations[] = array($callback_operation, array($time, t('(Operation @operation)', array('@operation' => $callback_operation))));
		}
		$batch = array(
			'operations' => $operations,
			'finished' => 'betatolive_finished_backup_component',
			'file' => drupal_get_path('module', 'betatolive') . '/data.migrate.inc',
		);
		return $batch;
	}
}
?>