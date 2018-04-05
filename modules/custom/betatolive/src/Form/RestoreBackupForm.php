<?php
/**
 * @file
 * Contains \Drupal\betatolive\Form\RestoreBackupForm.
 */

namespace Drupal\betatolive\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\betatolive\Controller\MiscPages;
use Drupal\Core\Url;

/**
 * Configure settings for betatolive.
 */
class RestoreBackupForm extends ConfirmFormBase {

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
        return 'restore_backup_form';
    }

    /**
     * {@inheritdoc}
     */
    public function getQuestion() {
        global $_instance;
        //the question to display to the user.
        //return t('Are you sure you want to push the backup of ').date('d M, Y H:m:i',$this->backup->backup_time);
        return t('Are you sure you want to push the backup of @backup_time to @instance', array('@instance'=>($_instance=='dev')?'beta':'live','@backup_time' => date('d M, Y H:m:i',$this->backup->backup_time)));
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
        $LIVE_URL = MiscPages::getLiveUrl();
        //a brief desccription
        return t('Pressing this button would publish the website to @live',array('@live'=>$LIVE_URL));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
        global $_instance;
        return ($_instance=='dev') ? t('Publish to beta') : t('Publish to live');
    }


    /**
     * {@inheritdoc}
     */
    public function getCancelText() {
        return $this->t('Cancel');
    }

	public function buildForm(array $form, FormStateInterface $form_state, $instance = 'beta', $backup = NULL) {
        global $_instance;
        $LIVE_URL = MiscPages::getLiveUrl();
		$backup = MiscPages::get_backup_data($backup);
        $this->backup = $backup;

        $header =array('Component','Size','Timestamp');
        $backup_date = date('d/m/Y | h:m:i',$backup->backup_time);

        $form['pre_heading'] = array(
            '#type' => 'item',
            '#markup' =>t('@instance has been backed up, The details are mention below:',array('@instance'=>$LIVE_URL)),
        );

        if($backup->instance == 'beta') {
            $push_operation = 'beta_to_live';

            $data = array();
            $data[0]=array('Source Code',(($s_size=filesize($backup->source_code)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)',$backup_date);
            if($_instance=='beta')
            {
                $data[1]=array('Database',(($s_size=filesize($backup->database_path)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)',$backup_date);

                $data[2]=array('Media Files',(($s_size=filesize($backup->media_files)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)',$backup_date);
            }
        }
        else if($backup->instance=='live') {
            $push_operation = 'live_to_live';

            $data = array();
            $data[0]=array('Source Code',(($s_size=MiscPages::getSizeFile($backup->source_code)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)',$backup_date);
            if($_instance=='beta')
            {
                $data[1]=array('Database',(($m_size=MiscPages::getSizeFile($backup->database_path)/1000)<1000)?($m_size>0)?'('.round($m_size,2).' KB)':'':'('.round($m_size/1000,2).' MB)',$backup_date);
                $data[2]=array('Media Files',(($d_size=MiscPages::getSizeFile($backup->media_files)/1000)<1000)?($d_size>0)?'('.round($d_size,2).' KB)':'':'('.round($d_size/1000,2).' MB)',$backup_date);
            }
        }

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
        $form['push_operation'] = array(
            '#type' => 'hidden',
            '#value' =>$push_operation,
        );
        $form['instance'] = array(
            '#type' => 'hidden',
            '#value' =>$backup->instance,
        );

        $form['view_backup'] = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $data,
            '#empty' => t('No data found'),
        ];

		return parent::buildForm($form, $form_state);
	}

	/** 
   * {@inheritdoc}
   */
	public function submitForm(array &$form, FormStateInterface $form_state) {

        $callback_operations_content = array('admin_betatolive_restore_database','admin_betatolive_restore_media_files');
        $callback_operations_sourcecode = array('admin_betatolive_restore_source_code');
        $callback_operations = array_merge($callback_operations_content,$callback_operations_sourcecode);

        $_SESSION['http_request_count'] = 0; // reset counter for debug information.

        //Prepare the batch
        $batch = self::betatolive_restore_component($callback_operations,$form_state);
        batch_set($batch);
	}

    /**
     * Preparing batch for each component
     */
    public function betatolive_restore_component($callback_operations,$form_state)
    {
        $operations = array();
        $time = time();
        foreach($callback_operations as $callback_operation) 
        {
            // Logs a notice
            \Drupal::logger('betolive-message')->notice('Appending %function in batch queue',
            array(
                '%function' => $callback_operation,
            ));
            //watchdog('betolive-message','Appending :function in batch queue',array(':function'=>$callback_operation));
            $operations[] = array($callback_operation, array($time,$form_state,t('(Operation @operation)', array('@operation' => $callback_operation))));
        }
        $batch = array(
            'operations' => $operations,
            'finished' => 'betatolive_finished_restore_component',
            'file' => drupal_get_path('module', 'betatolive') . '/data.migrate.inc',
        );
        return $batch;
    }

}
?>