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
class ConfigurationForm extends ConfigFormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'configuration_form';
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

		global $skip_tables;
		$config = $this->config('betatolive.settings');

		$form['skip_tables'] = array(
			'#type'=>'textfield',
			'#title'=>t('Table Name'),
			'#description' => t('Enter the name of the table which you want to skip in push to live operation'), 
			'#default_value' =>implode(',',$config->get('skip_tables')), 
			'#size' => 60, 
			'#maxlength' => 300, 
		);

		$form['skip_directory'] = array(
			'#type'=>'textfield',
			'#title'=>t('Directory Name'),
			'#description' => t('Enter the name of the directory which you want to exclude in push to live operation'), 
			'#default_value' =>implode(',',$config->get('skip_directory')), 
			'#size' => 60, 
			'#maxlength' => 128, 
		);

		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => t('Save configuration'),
		);

		$form['global_skip_tables'] = array(
			'#type'=>'item',
			'#markup'=>'<span>List of tables which are common for all the concept: <i>'.implode(', ',$skip_tables).'</i></span>',
		);

		$custom_skip_table = ($config->get('skip_tables') != null) ? $config->get('skip_tables') : array();

		$form['skip_tables_info'] = array(
			'#type' => 'details',
			'#title' => t('Table that are available for beta to live operation'),
			'#description' => implode('<br>',array_diff(MiscPages::betatolive_get_database_tables(),array_merge($skip_tables, $custom_skip_table))),
			'#open' => FALSE,
		);

		$form['#theme'] = 'system_settings_form';

		return parent::buildForm($form, $form_state);
	}

	/** 
   * {@inheritdoc}
   */
	public function validateForm(array &$form, FormStateInterface $form_state) {

		// Validation
		global $skip_tables;
		$error = false;

		$custom_skip_table = $form_state->getValue('skip_tables');
		if(trim($custom_skip_table) == '') return;
		$raw_skip_tables = explode(',',trim($custom_skip_table));
		foreach($raw_skip_tables as $raw_skip_table)
		{
			$table = trim($raw_skip_table);
			if(!db_table_exists($table) || in_array($table,$skip_tables))
			{
				$error=true;
				$invalid_table[] = $table;
			}
		}
		if($error)
			$form_state->setErrorByName('skip_tables', $this->t('Table :table is not a valid table name or it might be included in the commonly skip table list',array(':table'=>implode(',',$invalid_table))));
	}

	/** 
   * {@inheritdoc}
   */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$config = \Drupal::configFactory()->getEditable('betatolive.settings');

		$custom_skip_table = $form_state->getValue('skip_tables');
		$raw_skip_tables = explode(',', $custom_skip_table);

		foreach($raw_skip_tables as $raw_skip_table) {
			$table = trim($raw_skip_table);
			(!empty($table)) ? $skip_tables[$table]=$table : '';
		}
		$config->set('skip_tables', $skip_tables)->save();

		$custom_dir = $form_state->getValue('skip_directory');
		$raw_dirs = explode(',',$custom_dir);
		foreach($raw_dirs as $raw_dir) {
			$dir = trim($raw_dir);
			(!empty($dir)) ? $skip_directory[$dir]=$dir : '';
		}
		$config->set('skip_directory', $skip_directory)->save();

	    parent::submitForm($form, $form_state);
	}

}
?>