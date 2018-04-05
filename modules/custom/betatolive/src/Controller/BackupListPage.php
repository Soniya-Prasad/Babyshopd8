<?php

namespace Drupal\betatolive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\betatolive\Controller\MiscPages;
use Drupal\Core\Url;

class BackupListPage extends ControllerBase {

	/**
	 * Listing of all available backups.
	 */
	public function betatolive_list_backups() {

		$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
		$_SESSION['pusheddata'] = \Drupal::config('betatolive.settings')->get('pusheddata');

		$LIVE_BACKUP_PATH = MiscPages::getBackupPath();
		$LIVE_DATABASE = MiscPages::getLiveDatabase();
		$BETA_BACKUP_PATH = MiscPages::getBetaBackupPath();
		$BETA_DATABASE = MiscPages::getBetaDatabase();
		$LIVE_URL = MiscPages::getLiveUrl();
		$BETA_URL = MiscPages::getBetaUrl();
		$BACKUP_SEPARATOR = MiscPages::getBckupSeparator();

		MiscPages::betatolive_clean_backup();
		$current_path = \Drupal::service('path.current')->getPath();
		$arg = explode('/', $current_path);

		!in_array($arg[5],array('beta','live')) ? $instance = 'beta' : $instance = $arg[5];

		$header = array('Backup Date','Pushed By','Author Comments','Source Code','Database','Media Files','Remove','Push to Live');
		$backup_dir = $LIVE_BACKUP_PATH;

		$removestring_database = $LIVE_DATABASE['default']['database'];
		$beta_listing = false;
		if($instance == 'beta')
		{
			$backup_dir = $BETA_BACKUP_PATH;
			$removestring_database = $BETA_DATABASE['default']['database'];
			$beta_listing = true;
			
		}

		if(file_prepare_directory($backup_dir)) 
		{
			$available_backup = file_scan_directory($backup_dir, '([^\s]+(\.(?i)(tar|gz|tar.gz|sql))$)');
		}

		if($available_backup !==FALSE)
		{
			foreach($available_backup as $backup)
			{
				list($filename,$extension)=explode('.',$backup->filename);
				list($backup_type,$raw_backup_time)=explode($BACKUP_SEPARATOR,$filename);
				$backup_time=str_replace($removestring_database,'',$raw_backup_time);
				$backups[$backup_time][$backup_type]=$backup;
			}

			db_delete('betatolive_backups')->condition('instance',$instance)->execute();
			if($instance=='beta')
			{
				foreach($backups as $backup_time=>$backup)
				{
					$result = \Drupal::database()->select('betatolive_status', 's')
						->fields('s')
						->condition('time',$backup_time,'=')
						->execute()
						->fetchAll();

					if(count($result) > 0) {

						$pushedby=$comments=$source_code=$media_files=$database=$source_code_path=$media_files_path=$database_path=$date='';
						$date=date('d M, Y H:m:i',$backup_time);

						isset($backup['source_code']->filename) ? $source_code_path = $BETA_BACKUP_PATH.$backup['source_code']->filename : '';
						isset($backup['media_files']->filename) ? $media_files_path = $BETA_BACKUP_PATH.$backup['media_files']->filename : '';
						isset($backup['database']->filename) ? $database_path = $BETA_BACKUP_PATH.$backup['database']->filename : '';

						isset($backup['source_code']->filename) ? $source_code=$BETA_URL.'/backup/'.$backup['source_code']->filename:'';
						isset($backup['media_files']->filename) ? $media_files=$BETA_URL.'/backup/'.$backup['media_files']->filename:'';
						isset($backup['database']->filename) ? $database=$BETA_URL.'/backup/'.$backup['database']->filename:'';

						$source_code_size=$media_files_size=$database_size='';
						$source_code_size=(($s_size=filesize($source_code_path)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)';
						$media_files_size=(($m_size=filesize($media_files_path)/1000)<1000)?($m_size>0)?'('.round($m_size,2).' KB)':'':'('.round($m_size/1000,2).' MB)';
						$database_size=(($d_size=filesize($database_path)/1000)<1000)?($d_size>0)?'('.round($d_size,2).' KB)':'':'('.round($d_size/1000,2).' MB)';

						$data[$backup_time]=array(
						$date,
						$pushedby = $result[0]->username,
						$comments = unserialize($result[0]->pushed_data),
						!empty($source_code_size) ? \Drupal::l($source_code_size,Url::fromUri($source_code)) : '--',
						!empty($database_size) ? \Drupal::l($database_size,Url::fromUri($database)) : '--',
						!empty($media_files_size) ? \Drupal::l($media_files_size,Url::fromUri($media_files)) : '--',
						\Drupal::l('Delete', Url::fromUri('internal:/admin/backup/delete/'.$instance.'/'.$backup_time.'/confirm')),
						\Drupal::l('Push', Url::fromUri('internal:/admin/backup/restore/'.$instance.'/'.$backup_time.'/confirm')));
						krsort($data);

						$insert = \Drupal::database()->insert('betatolive_backups')
						->fields(
						  array(
						    'backup_time' => $backup_time,
						    'pushedby' => $pushedby,
						    'authorcomments' => $comments,
						    'source_code' => $source_code_path,
						    'database_path' => $database_path,
						    'media_files' => $media_files_path,
						    'instance' => $instance,
						  )
						)->execute();
					}
				}
			}
			else if($instance=='live')
			{
				foreach($backups as $backup_time=>$backup)
				{
					$result = \Drupal::database()->select('betatolive_status', 's')
						->fields('s')
						->condition('time',$backup_time,'=')
						->execute()
						->fetchAll();

					if(count($result) > 0) {

						$pushedby=$comments=$source_code=$media_files=$database=$source_code_path=$media_files_path=$database_path=$date='';
						$date=date('d M, Y H:m:i',$backup_time);

						isset($backup['source_code']->filename) ? $source_code_path = $LIVE_BACKUP_PATH.$backup['source_code']->filename : '';
						isset($backup['media_files']->filename) ? $media_files_path = $LIVE_BACKUP_PATH.$backup['media_files']->filename : '';
						isset($backup['database']->filename) ? $database_path = $LIVE_BACKUP_PATH.$backup['database']->filename : '';

						isset($backup['source_code']->filename) ? $source_code=$LIVE_URL.'/backup/'.$backup['source_code']->filename:'';
						isset($backup['media_files']->filename) ? $media_files=$LIVE_URL.'/backup/'.$backup['media_files']->filename:'';
						isset($backup['database']->filename) ? $database=$LIVE_URL.'/backup/'.$backup['database']->filename:'';

						$source_code_size=$media_files_size=$database_size='';
						$source_code_size=(($s_size=MiscPages::getSizeFile($source_code_path)/1000)<1000)?($s_size>0)?'('.round($s_size,2).' KB)':'':'('.round($s_size/1000,2).' MB)';
						$media_files_size=(($m_size=MiscPages::getSizeFile($media_files_path)/1000)<1000)?($m_size>0)?'('.round($m_size,2).' KB)':'':'('.round($m_size/1000,2).' MB)';
						$database_size=(($d_size=MiscPages::getSizeFile($database_path)/1000)<1000)?($d_size>0)?'('.round($d_size,2).' KB)':'':'('.round($d_size/1000,2).' MB)';

						$data[$backup_time]=array(
						$date,
						$pushedby = $result[0]->username,
						$comments = unserialize($result[0]->pushed_data),
						!empty($source_code_size) ? \Drupal::l($source_code_size,Url::fromUri($source_code)) : '--',
						!empty($database_size) ? \Drupal::l($database_size,Url::fromUri($database)) : '--',
						!empty($media_files_size) ? \Drupal::l($media_files_size,Url::fromUri($media_files)) : '--',
						\Drupal::l('Delete', Url::fromUri('internal:/admin/backup/delete/'.$instance.'/'.$backup_time.'/confirm')),
						\Drupal::l('Push', Url::fromUri('internal:/admin/backup/restore/'.$instance.'/'.$backup_time.'/confirm')));
						krsort($data);

						$insert = \Drupal::database()->insert('betatolive_backups')
						->fields(
						  array(
						    'backup_time' => $backup_time,
						    'pushedby' => $pushedby,
						    'authorcomments' => $comments,
						    'source_code' => $source_code_path,
						    'database_path' => $database_path,
						    'media_files' => $media_files_path,
						    'instance' => $instance,
						  )
						)->execute();
					}
				}
			}
		}

		return [
		  '#type' => 'table',
		  '#header' => $header,
		  '#rows' => $data,
		  '#empty' => t('No Backup Available')
		];
	}

	public function betatolive_list_all_backups() {
		$_SESSION['pusheddata'] = \Drupal::config('betatolive.settings')->get('pusheddata');

		$LIVE_BACKUP_PATH = MiscPages::getBackupPath();
		$LIVE_DATABASE = MiscPages::getLiveDatabase();
		$BETA_BACKUP_PATH = MiscPages::getBetaBackupPath();
		$BETA_DATABASE = MiscPages::getBetaDatabase();
		$LIVE_URL = MiscPages::getLiveUrl();
		$BACKUP_SEPARATOR = MiscPages::getBckupSeparator();

		$removestring_database = $BETA_DATABASE['default']['database'];
		$backup_dir = $BETA_BACKUP_PATH;
		$instance = 'beta';
		if(file_prepare_directory($backup_dir))  {
			$available_backup = file_scan_directory($backup_dir, '([^\s]+(\.(?i)(tar|gz|tar.gz|sql))$)');
		}
		if($available_backup !==FALSE) {
			foreach($available_backup as $backup) {
				list($filename,$extension)=explode('.',$backup->filename);
				list($backup_type,$raw_backup_time)=explode($BACKUP_SEPARATOR,$filename);
				$backup_time=str_replace($removestring_database,'',$raw_backup_time);
				$backups[$backup_time][$backup_type]=$backup;
			}
			db_delete('betatolive_backups')->condition('instance',$instance)->execute();

			foreach($backups as $backup_time=>$backup) {

				$result = \Drupal::database()->select('betatolive_status', 's')
						->fields('s')
						->condition('time',$backup_time,'=')
						->execute()
						->fetchAll();

				if(count($result) > 0) {

					$pushedby=$comments=$source_code_path=$media_files_path=$database_path='';

					$pushedby = $result[0]->username;
					$comments = unserialize($result[0]->pushed_data);


					isset($backup['source_code']->filename) ? $source_code_path = $BETA_BACKUP_PATH.$backup['source_code']->filename : '';
					isset($backup['media_files']->filename) ? $media_files_path = $BETA_BACKUP_PATH.$backup['media_files']->filename : '';
					isset($backup['database']->filename) ? $database_path = $BETA_BACKUP_PATH.$backup['database']->filename : '';
					
					$insert = \Drupal::database()->insert('betatolive_backups')
						->fields(
						  array(
						    'backup_time' => $backup_time,
						    'pushedby' => $pushedby,
						    'authorcomments' => $comments,
						    'source_code' => $source_code_path,
						    'database_path' => $database_path,
						    'media_files' => $media_files_path,
						    'instance' => $instance,
						  )
						)->execute();
				}
			}
	    }

        $removestring_database = $LIVE_DATABASE['default']['database'];
        $backup_dir = $LIVE_BACKUP_PATH;
        $instance = 'live';

        if(file_prepare_directory($backup_dir))  {
			$available_backup = file_scan_directory($backup_dir, '([^\s]+(\.(?i)(tar|gz|tar.gz|sql))$)');
		}
		if($available_backup !==FALSE) {
			foreach($available_backup as $backup) {
				list($filename,$extension)=explode('.',$backup->filename);
				list($backup_type,$raw_backup_time)=explode($BACKUP_SEPARATOR,$filename);
				$backup_time=str_replace($removestring_database,'',$raw_backup_time);
				$backups[$backup_time][$backup_type]=$backup;
			}
			db_delete('betatolive_backups')->condition('instance',$instance)->execute();
			foreach($backups as $backup_time=>$backup) {

				$result = \Drupal::database()->select('betatolive_status', 's')
						->fields('s')
						->condition('time',$backup_time,'=')
						->execute()
						->fetchAll();

				if(count($result) > 0) {

					$pushedby=$comments=$source_code_path=$media_files_path=$database_path='';

					$pushedby = $result[0]->username;
					$comments = unserialize($result[0]->pushed_data);

					isset($backup['source_code']->filename) ? $source_code_path = $LIVE_BACKUP_PATH.$backup['source_code']->filename : '';
					isset($backup['media_files']->filename) ? $media_files_path = $LIVE_BACKUP_PATH.$backup['media_files']->filename : '';
					isset($backup['database']->filename) ? $database_path = $LIVE_BACKUP_PATH.$backup['database']->filename : '';
					
					$insert = \Drupal::database()->insert('betatolive_backups')
						->fields(
						  array(
						    'backup_time' => $backup_time,
						    'pushedby' => $pushedby,
						    'authorcomments' => $comments,
						    'source_code' => $source_code_path,
						    'database_path' => $database_path,
						    'media_files' => $media_files_path,
						    'instance' => $instance,
						  )
						)->execute();
				}

			}
	    }
	}

	public function betatolive_processing_backup_operation($backup) {

		$output = array();
		$time = $backup;
	    $backup = \Drupal::database()->select('betatolive_backups', 'bb')
						->fields('bb')
						->condition('backup_time',$backup,'=')
						->execute()
						->fetchAllAssoc('instance');

	    if(empty($backup) || count($backup) <=0) {
	        return new \Symfony\Component\HttpFoundation\RedirectResponse(Url::fromRoute('betatolive.beta_backup_list')->toString());
	    }

	    $beta_component = $backup['beta'];
	    $live_component = $backup['live'];
	    $prev_beta_backup_status = $_SESSION['backup_status']['beta'];
	    $prev_live_backup_status = $_SESSION['backup_status']['live'];

	    if(isset($beta_component->source_code) && !empty($beta_component->source_code)) {
	        $beta_source_code_backup_size_status=filesize($beta_component->source_code);
	        if($beta_source_code_backup_size_status == $prev_beta_backup_status['source_code']) {
	            $beta_source_code_backup_status = t('Completed').' '.round($beta_source_code_backup_size_status/1000000,5).' MB';
	            $completed_beta_source_code_backup = true;
	        } else {
	            $_SESSION['backup_status']['beta']['source_code'] =$beta_source_code_backup_size_status; 
	            $beta_source_code_backup_status = 'Processing.... ( '.round($beta_source_code_backup_size_status/1000000,5).' MB )';
	             $completed_beta_source_code_backup = false;
	        }
	    } else {
	        $beta_source_code_backup_status=t('N/A');
	        $completed_beta_source_code_backup = true;
	    }
	    
	    if(isset($beta_component->database_path) && !empty($beta_component->database_path)) {
	        $beta_database_backup_size_status=filesize($beta_component->database_path);
	        if($beta_database_backup_size_status == $prev_beta_backup_status['database']) {
	            $beta_database_backup_status = t('Completed').' '.round($beta_database_backup_size_status/1000000,5).' MB';
	            $completed_beta_database_backup = true;
	        } else {
	            $_SESSION['backup_status']['beta']['database'] =$beta_database_backup_size_status; 
	             $beta_database_backup_status = 'Processing.... ( '.round($beta_database_backup_size_status/1000000,5).' MB )';
	             $completed_beta_database_backup = false;
	        }
	    } else {
	        $beta_database_backup_status=t('N/A');
	        $completed_beta_database_backup = true;
	    }
	    
	    if(isset($beta_component->media_files) && !empty($beta_component->media_files)) {
	        $beta_media_files_backup_size_status=filesize($beta_component->media_files);
	        if($beta_media_files_backup_size_status == $prev_beta_backup_status['media_files']) {
	            $beta_media_files_backup_status = t('Completed').' '.round($beta_media_files_backup_size_status/1000000,5).' MB';
	            $completed_beta_media_files_backup = true;
	        } else {
	            $_SESSION['backup_status']['beta']['media_files'] =$beta_media_files_backup_size_status; 
	            $beta_media_files_backup_status = 'Processing.... ( '.round($beta_media_files_backup_size_status/1000000,5).' MB )';
	            $completed_beta_media_files_backup = false;
	        }
	    } else {
	        $beta_media_files_backup_status=t('N/A');
	        $completed_beta_media_files_backup = true;
	    }
	     if(isset($live_component->source_code) && !empty($live_component->source_code)) {
	        $live_source_code_backup_size_status=filesize($live_component->source_code);
	        if($live_source_code_backup_size_status == $prev_live_backup_status['source_code']) {
	            $live_source_code_backup_status = t('Completed').' '.round($live_source_code_backup_size_status/1000000,5).' MB';
	            
	            $completed_live_source_code_backup = true;
	        } else {
	            $_SESSION['backup_status']['live']['source_code'] =$live_source_code_backup_size_status; 
	            $live_source_code_backup_status = 'Processing.... ( '.round($live_source_code_backup_size_status/1000000,5).' MB )';
	            $completed_live_source_code_backup = false;
	        }
	    } else {
	        $live_source_code_backup_status=t('N/A');
	        $completed_live_source_code_backup = true;
	    }
	    if(isset($live_component->database_path) && !empty($live_component->database_path)) {
	        $live_database_backup_size_status=filesize($live_component->database_path);
	        if($live_database_backup_size_status == $prev_live_backup_status['database']) {
	            $live_database_backup_status = t('Completed').' '.round($live_database_backup_size_status/1000000,5).' MB';
	            $completed_live_database_backup = true;
	        } else {
	            $_SESSION['backup_status']['live']['database'] =$live_database_backup_size_status; 
	             $live_database_backup_status = 'Processing.... ( '.round($live_database_backup_size_status/1000000,5).' MB )';
	             $completed_live_database_backup = false;
	        }
	    } else {
	        $live_database_backup_status=t('N/A');
	        $completed_live_database_backup = true;
	    }
	    
	    if(isset($live_component->media_files) && !empty($live_component->media_files)) {
	        $live_media_files_backup_size_status=filesize($live_component->media_files);
	        if($live_media_files_backup_size_status == $prev_live_backup_status['media_files']) {
	            $live_media_files_backup_status = t('Completed').' '.round($live_media_files_backup_size_status/1000000,5).' MB';
	            $completed_live_media_files_backup = true;
	        } else {
	            $_SESSION['backup_status']['live']['media_files'] =$live_media_files_backup_size_status; 
	            $live_media_files_backup_status = 'Processing.... ( '.round($live_media_files_backup_size_status/1000000,5).' MB )';
	            $completed_live_media_files_backup = false;
	        }
	    } else {
	        $live_media_files_backup_status=t('N/A');
	        $completed_live_media_files_backup = true;
	    }
	    if($completed_beta_media_files_backup && $completed_beta_source_code_backup && $completed_beta_database_backup && $completed_live_media_files_backup && $completed_live_source_code_backup && $completed_live_database_backup) {

	    	unset($_SESSION['process_url']);
	        $url = Url::fromUri('internal:/admin/backup/restore/beta'.'/'.$time.'/confirm');
			$instance_config_link = \Drupal::l('here', $url);
			drupal_set_message(t('Backup operation has been completed, Please verify the backup and click @link to proceed to next step', array('@link' => $instance_config_link)));
	    }

	    $beta_backup_status = array(array('component'=>t('Source Code'),'status'=>$beta_source_code_backup_status),array('component'=>t('Database'),'status'=>$beta_database_backup_status),array('component'=>t('Media Files'),'status'=>$beta_media_files_backup_status));
	    
	    $live_backup_status = array(array('component'=>t('Source Code'),'status'=>$live_source_code_backup_status),array('component'=>t('Database'),'status'=>$live_database_backup_status),array('component'=>t('Media Files'),'status'=>$live_media_files_backup_status));

	    $header_beta = array('component'=>t('Beta Component'),
	                    'status'=>t('Beta Status')
	    );
	    $header_live = array('component'=>t('Live Component'),
	                    'status'=>t('Live Status')
	    );

	    $output[] = [
		  '#type' => 'table',
		  '#header' => $header_beta,
		  '#rows' => $beta_backup_status,
		];

	    $output[] = [
		  '#type' => 'table',
		  '#header' => $header_live,
		  '#rows' => $live_backup_status,
		];

	    return $output;

	}

}