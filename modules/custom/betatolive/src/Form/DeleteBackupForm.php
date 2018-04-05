<?php
/**
 * @file
 * Contains \Drupal\betatolive\Form\StoreForm.
 */

namespace Drupal\betatolive\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\betatolive\Controller\MiscPages;
use Drupal\Core\Url;

/**
 * Configure settings for betatolive.
 */
class DeleteBackupForm extends ConfirmFormBase {

	/**
     * The ID of the item to delete.
     *
     * @var string
     */
    protected $backup = NULL;

    /**
     * {@inheritdoc}.
     */
    public function getFormId()
    {
        return 'delete_backup_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        //the question to display to the user.
        return t('Are you sure you want to delete backup of ').date('d M, Y H:m:i',$this->backup->backup_time);
    }

    /**
     * {@inheritdoc}
     */
    public function getCancelUrl() {
        //this needs to be a valid route otherwise the cancel link won't appear
        return new Url('betatolive.'.$this->backup->instance.'_backup_list');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription() {
        //a brief desccription
        return t('You are about to delete <i>Database, Source-code & Media files</i> from backup files');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        return $this->t('Delete Backup');
    }


    /**
     * {@inheritdoc}
     */
    public function getCancelText() {
        return $this->t('Cancel');
    }

	public function buildForm(array $form, FormStateInterface $form_state, $instance = 'beta', $backup = NULL) {

		$backup = MiscPages::get_backup_data($backup);

		$this->backup = $backup;
		$form['database'] = array(
			'#type' => 'hidden',
			'#value' => $backup->database_path,
		);
		$form['source_code'] = array(
			'#type' => 'hidden',
			'#value' =>$backup->source_code,
		);
		$form['media_files'] = array(
			'#type' => 'hidden',
			'#value' =>$backup->media_files,
		);
		$form['backup_time'] = array(
			'#type' => 'hidden',
			'#value' =>$backup->backup_time,
		);
		$form['instance'] = array(
			'#type' => 'hidden',
			'#value' =>$backup->instance,
		);

		return parent::buildForm($form, $form_state);
	}

	/** 
   * {@inheritdoc}
   */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$source_code = $form_state->getValue('source_code');
		$media_files = $form_state->getValue('media_files');
		$database = $form_state->getValue('database');
		$backup_time = $form_state->getValue('backup_time');
		$instance = $form_state->getValue('instance');

		file_unmanaged_delete($source_code);
		file_unmanaged_delete($media_files);
		file_unmanaged_delete($database);

		drupal_set_message(t('Deleted backup for ').date('d M,Y H:m:i',$backup_time), $repeat = TRUE);
		// Redirected to listing page
		$url = Url::fromRoute('betatolive.'.$instance.'_backup_list');
		$form_state->setRedirectUrl($url);
	}

}
?>