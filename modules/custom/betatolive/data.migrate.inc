<?php

use Drupal\betatolive\Controller\MiscPages;
use Drupal\betatolive\Controller\BackupListPage;
use Drupal\Core\Url;

/* Backup Status */
function betatolive_backup_status($time, $operation_details, &$context) {

	$user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

	$insert = \Drupal::database()->insert('betatolive_status')
	->fields(
	  array(
	    'username' => $user->get('name')->value,
	    'pushed_data' => $_SESSION['form_comment'],
	    'ip' => $_SERVER['REMOTE_ADDR'],
	    'time' => $time,
	    'userid' => $user->get('mail')->value,
	  )
	)->execute();
	
	_betatolive_update_http_requests();
}

/* Backup Database */
function betatolive_backup_database($time, $operation_details, &$context) {
	global $skip_tables;
	$live_database = MiscPages::getLiveDatabase();
	$beta_database = MiscPages::getBetaDatabase();
	$BACKUP_SEPARATOR = MiscPages::getBckupSeparator();
	$LIVE_BACKUP_PATH = MiscPages::getBackupPath();
	$BETA_BACKUP_PATH = MiscPages::getBetaBackupPath();

	$admin_skip_tables = \Drupal::config('betatolive.settings')->get('skip_tables');
	if(is_array($admin_skip_tables))
		$skip_tables += $admin_skip_tables;
	foreach($skip_tables as $skip_table) {
		$ignore_table_live[]='--ignore-table='.$live_database['default']['database'].'.'.$skip_table;
		$ignore_table_beta[]='--ignore-table='.$beta_database['default']['database'].'.'.$skip_table;
	}

	$context['results'] = $time;
	$context['message'] = t('Backup database');

	shell_exec('mysqldump -u'. $live_database['default']['username'] .' -p'. $live_database['default']['password'] .' -h'. $live_database['default']['host'] . ' ' . $live_database['default']['database'] .' '.implode(' ',$ignore_table_live).' | gzip -v >'. $LIVE_BACKUP_PATH. 'database'.$BACKUP_SEPARATOR . $time . $live_database['default']['database'].'.tar.gz');
	
	shell_exec('mysqldump -u'. $beta_database['default']['username'] .' -p'. $beta_database['default']['password'] .' -h'. $beta_database['default']['host'] . ' ' . $beta_database['default']['database'] .' '.implode(' ',$ignore_table_beta).' | gzip -v >'. $BETA_BACKUP_PATH. 'database'.$BACKUP_SEPARATOR . $time . $beta_database['default']['database'].'.tar.gz');

	_betatolive_update_http_requests();
}

/* Backup Media Files */
function betatolive_backup_media_files($time, $operation_details, &$context) {

	$BACKUP_SEPARATOR = MiscPages::getBckupSeparator();
	$LIVE_BACKUP_PATH = MiscPages::getBackupPath();
	$BETA_BACKUP_PATH = MiscPages::getBetaBackupPath();
	$BETA_MEDIA_FILE_PATH = MiscPages::getBetaMediaFilePath();
	$LIVE_MEDIA_FILE_PATH = MiscPages::getMediaFilePath();
	$LIVE_DEPLOYMENT_PATH = MiscPages::getDeploymentPath();
	$BETA_DEPLOYMENT_PATH = MiscPages::getBetaDeploymentPath();

	$context['results'] = $time;
	$context['message'] = t('Backup media files');
	$live_back_file_name = $LIVE_BACKUP_PATH . 'media_files' . $BACKUP_SEPARATOR . $time . '.tar.gz';
	$beta_back_file_name = $BETA_BACKUP_PATH . 'media_files' . $BACKUP_SEPARATOR . $time . '.tar.gz';

	shell_exec('tar -zcf ' . $beta_back_file_name . ' ' . $BETA_MEDIA_FILE_PATH.' > /dev/null 2>/dev/null &');
	chdir($LIVE_DEPLOYMENT_PATH);
	shell_exec('tar -zcf ' . $live_back_file_name . ' ' . $LIVE_MEDIA_FILE_PATH.' > /dev/null 2>/dev/null &');
	chdir($BETA_DEPLOYMENT_PATH);
	
	_betatolive_update_http_requests();
}

/* Backup Source code */
function betatolive_backup_source_code($time, $operation_details, &$context) {

	$BACKUP_SEPARATOR = MiscPages::getBckupSeparator();
	$LIVE_BACKUP_PATH = MiscPages::getBackupPath();
	$BETA_BACKUP_PATH = MiscPages::getBetaBackupPath();
	$BETA_SOURCE_CODE_PATH = MiscPages::getBetaSourceCodePath();
	$LIVE_SOURCE_CODE_PATH = MiscPages::getSourceCodePath();
	$LIVE_DEPLOYMENT_PATH = MiscPages::getDeploymentPath();
	$BETA_DEPLOYMENT_PATH = MiscPages::getBetaDeploymentPath();

	$skip_dirs = \Drupal::config('betatolive.settings')->get('skip_directory');
	if(is_array($skip_dirs)) {
		$BETA_SOURCE_CODE_PATH = array_diff($BETA_SOURCE_CODE_PATH, $skip_dirs);
		$LIVE_SOURCE_CODE_PATH = array_diff($LIVE_SOURCE_CODE_PATH, $skip_dirs);
	}

	
	$ignore_beta_dir = array();
	$ignore_live_dir = array();
	foreach($skip_dirs as $skip_dir)
	{
		foreach($BETA_SOURCE_CODE_PATH as $BETA_SOURCE_PATH) {
			$ignore_beta_dir[]='--exclude='.$BETA_SOURCE_PATH.'/'.$skip_dir;
		}
		foreach($LIVE_SOURCE_CODE_PATH as $LIVE_SOURCE_PATH) {
			$ignore_live_dir[]='--exclude='.$LIVE_SOURCE_PATH.'/'.$skip_dir;
		}
	}
	
	$context['results'] = $time;
	$context['message'] = t('Backup source code');
	$live_back_file_name = $LIVE_BACKUP_PATH . 'source_code' . $BACKUP_SEPARATOR. $time . '.tar.gz';
	$beta_back_file_name = $BETA_BACKUP_PATH . 'source_code' . $BACKUP_SEPARATOR. $time . '.tar.gz';

	shell_exec('tar -zcf '.  $beta_back_file_name . ' ' . implode(' ',$BETA_SOURCE_CODE_PATH).' '.implode(' ',$ignore_beta_dir).' > /dev/null 2>/dev/null &');
	chdir($LIVE_DEPLOYMENT_PATH);
	shell_exec('tar -zcf ' . $live_back_file_name . ' ' . implode(' ',$LIVE_SOURCE_CODE_PATH).' '.implode(' ',$ignore_live_dir).' > /dev/null 2>/dev/null &');
	chdir($BETA_DEPLOYMENT_PATH);
	
	_betatolive_update_http_requests();
}

/* Backup Finsh callback */
function betatolive_finished_backup_component($success, $time, $operations) {
  if ($success) {
    drupal_set_message(t('Backup commond has been send to server and the process will run in background, Please keep verifying the backup', array('@requests' => _betatolive_get_http_requests())),$repeat = TRUE);
	BackupListPage::betatolive_list_all_backups();

	$_SESSION['process_url'] = Url::fromRoute('betatolive.processing_backup', ['backup' => $time])->toString();
	return new \Symfony\Component\HttpFoundation\RedirectResponse($_SESSION['process_url']);
  }
  else {
    $error_operation = reset($operations);
    drupal_set_message(t('An error occurred while processing @operation with arguments : @args', array('@operation' => $error_operation[0], '@args' => print_r($error_operation[0], TRUE))));
  }
}

/**
 * Restore Database
 */
function admin_betatolive_restore_database($time,$form_state, $operation_details, &$context) {

	$live_database = MiscPages::getLiveDatabase();

	$database = $form_state->getValue('database');
	$context['results'] = $time;
	$context['message'] = t('Restoring database');
	/* Restore the Database */
	if(!empty($database)) {
		try
		{
			$cmd = 'zcat '. $database . ' | mysql -u'.$live_database['default']['username'].' -p'.$live_database['default']['password'].' -h' .$live_database['default']['host']. ' '.$live_database['default']['database'].' > /dev/null 2>/dev/null &';
			\Drupal::logger('betolive-message')->notice('Execution admin_betatolive_restore_database %cmd',array('%cmd'=>$cmd));
			//watchdog('betolive-message','Execution admin_betatolive_restore_database :cmd',array(':cmd'=>$cmd));
			shell_exec($cmd);
		}
		catch (Exception $e) 
		{
			\Drupal::logger('betatolive : exception')->error('Problem in importing database on live instance, %cmd ',array('%cmd'=>$cmd));
			//watchdog('betatolive : exception','Problem in importing database on live instance, :cmd ',array(':cmd'=>$cmd), WATCHDOG_CRITICAL, $link = NULL);
		}
		drupal_set_message(t('Push operation for database has been sucessfully completed'), $type = 'status', $repeat = TRUE);
	}
	_betatolive_update_http_requests();
}
/**
 * Restore Media Files
 */
function admin_betatolive_restore_media_files($time,$form_state, $operation_details, &$context) {
	/* Restore the Media Files */
	$LIVE_DEPLOYMENT_PATH = MiscPages::getDeploymentPath();
	$context['results'] = $time;
	$context['message'] = t('Restoring media files');
	$media_files = $form_state->getValue('media_files');
    $push_operation = $form_state->getValue('push_operation');
	if(!empty($media_files)) {
		try 
		{
			$cmd = 'tar -xzvf  '.$media_files.' -C '.$LIVE_DEPLOYMENT_PATH.' > /dev/null 2>/dev/null &';
			\Drupal::logger('betolive-message')->notice('Execution admin_betatolive_restore_media_files %cmd',array('%cmd'=>$cmd));
			shell_exec($cmd);
		}

		catch (Exception $e) 
		{
			\Drupal::logger('betatolive : exception')->error('Problem in extracting the backup at %beta_back_file_name	%cmd ',array('%beta_back_file_name'=>$media_files,'%cmd'=>$cmd));
		}
		drupal_set_message(t('Push operation for media files has been sucessfully completed'), $type = 'status', $repeat = TRUE);
	}
	_betatolive_update_http_requests();
}

/**
 * Restore Media Files
 */
function admin_betatolive_restore_source_code($time,$form_state, $operation_details, &$context) {

	$LIVE_DEPLOYMENT_PATH = MiscPages::getDeploymentPath();
	$LIVE_DATABASE_KEY = MiscPages::getLiveDatabaseKey();
	$source_code = $form_state->getValue('source_code');
	$context['results'] = $time;
	$context['message'] = t('Restoring source code');
	/* Restore the Source Code */
	if(!empty($source_code)) {
		try
		{
			$cmd = 'tar -xzvf  '.$source_code.' -C '.$LIVE_DEPLOYMENT_PATH.' > /dev/null 2>/dev/null &';
			\Drupal::logger('betolive-message')->notice('Execution admin_betatolive_restore_source_code %cmd',array('%cmd'=>$cmd));
			shell_exec($cmd);
		} 
		catch (Exception $e) 
		{
			\Drupal::logger('betatolive : exception')->error('Problem in extracting the backup at %beta_back_file_name	%cmd ',array('%beta_back_file_name'=>$source_code,'%cmd'=>$cmd));
		}
		drupal_set_message(t('Push operation of source code has been sucessfully completed'), $type = 'status');
		/* Running database architecute updates */
		if(false) {
			//$previously_active_database=db_set_active(LIVE_DATABASE_KEY);
			// switch database
			\Drupal\Core\Database\Database::setActiveConnection($LIVE_DATABASE_KEY);
			// Get a connection going
			$db = \Drupal\Core\Database\Database::getConnection();

			drupal_flush_all_caches();
			drupal_load_updates();
			$peding_updates = update_get_update_list();
			if(count($peding_updates) > 0) {
				foreach($peding_updates as $module => $update) {
					if(isset($update['start']))
					{
						//update available
						foreach($update['pending'] as $update_no => $update_info) {
							$callback=$module.'_update_'.$update_no;
							if(function_exists($callback)) {
									try
									{
										$callback();
									}
									catch(Exception $e)
									{
										\Drupal::logger('betatolive : exception')->error('Problem calling function %function',array('%function'=>$callback));
									}
							}
						}
					}		
				}
				/* Mark the above update as stale so that it dose not get run on next operation */
				//db_query('update {system} set schema_version = :schema_version where name =:module',array(':schema_version'=>$update_no,':module'=>$module));
				/* Set the previously active database */
				//db_set_active($previously_active_database);
				\Drupal\Core\Database\Database::setActiveConnection();
				drupal_set_message(t('Database structure has been rebuild sucessfully'), $type = 'status');
			}
		}
	}
	_betatolive_update_http_requests();
}
/**
 * Restore Finish Callback
 */
function betatolive_finished_restore_component($success, $time, $operations)
{
	$LIVE_SITE_NAME = MiscPages::getLiveSiteName();
	//global $user, $base_url, $language;
	if (!$success) {
		$error_operation = reset($operations);
		#drupal_set_message(t('An error occurred while processing'));
		/* Some problem with source code restore */
  	}
	// $previously_active_database=db_set_active(LIVE_DATABASE_KEY);
	// drupal_flush_all_caches();
	// db_set_active($previously_active_database);
	$url = Url::fromRoute('betatolive.live_backup_list');
	$url->setOptions(array('attributes'=>array('target'=>'_blank')));
	$instance_config_link = \Drupal::l($LIVE_SITE_NAME.' Backups', $url);
 	drupal_set_message(t('Please verify the update on live website. In case of any errors please visit @live_site to restore the previous version of the website on the live server',array('@live_site'=>$instance_config_link)));
	
	// $result = db_select('users', 'u')
	// 	->fields('u')
	// 	->condition('mail','%landmarkgroup%','LIKE')
	// 	->condition('mail','derrick.Pereira@landmarkgroup.com','<>')
	// 	->condition('status',1,'=')
	// 	->execute()
	// 	->fetchAll();

	// foreach($result as $userid){
	// $email .= $userid->mail.',';
	// }
	// $explodemail = explode(',',$email);
	// $implodemail = implode(',',$explodemail);
	// // $implodemail .= 'hiren.parmar@intelliswift.co.in,gopi.acharya@intelliswift.co.in';

	// $body = 'User<b> '. $user->name . '</b> has pushed to live  <a href="blog.babyshopstores.com"><b>blog.babyshopstores.com</b></a> <br>Pushed live date ' . format_date($user->timestamp) . '<br><br> Changes included in this push (these are changes manually introduced by ' . $user->name . ') : <br><i>' . variable_get('pusheddata') . '</i><br><br>If you want to push to live an older version of the website, <a href="' . $base_url . '/admin/operation/list/backup/live"> see all the available backups. </a>';
	// $subject = $_SERVER['SERVER_NAME'] . ' -- ' . t('pushed to live');
	// $key = 'pushtolive_mail';
	// $to = $implodemail;
	// $params = array(
	// 'subject' => $subject,
	// 'body' => $body
	// );
	// $mail_sent = drupal_mail('betatolive', $key, $to, $language, $params, $from, TRUE);
	
	return new \Symfony\Component\HttpFoundation\RedirectResponse(Url::fromRoute('betatolive.live_backup_list')->toString());
}

function _betatolive_update_http_requests() {
  $_SESSION['http_request_count']++;
}

function _betatolive_get_http_requests() {
  return !empty($_SESSION['http_request_count']) ? $_SESSION['http_request_count'] : 0;
}