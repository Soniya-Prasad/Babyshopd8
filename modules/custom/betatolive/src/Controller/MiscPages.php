<?php

namespace Drupal\betatolive\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

class MiscPages extends ControllerBase {

	// Declare all variables
	static $LIVE_SOURCE_CODE_PATH;
	static $LIVE_DEPLOYMENT_PATH;
	static $LIVE_BACKUP_PATH;
	static $LIVE_MEDIA_FILE_PATH;
	static $LIVE_URL;
	static $LIVE_SITE_NAME;

	static $BETA_SOURCE_CODE_PATH;
	static $BETA_DEPLOYMENT_PATH;
	static $BETA_BACKUP_PATH;
	static $BETA_URL;
	static $BETA_MEDIA_FILE_PATH;

	static $LIVE_DATABASE_KEY;
	static $BACKUP_SEPARATOR;

	public static function getSourceCodePath() {
	    self::$LIVE_SOURCE_CODE_PATH = array('themes','modules');
	    return self::$LIVE_SOURCE_CODE_PATH;
	}

	public static function getDeploymentPath() {
		global $publish_server_name;
	    self::$LIVE_DEPLOYMENT_PATH = '/mnt/stor5-wc2-dfw1/495544/1023193/'. $publish_server_name .'/web/content';
	    return self::$LIVE_DEPLOYMENT_PATH;
	}

	public static function getBackupPath() {
		global $publish_server_name;
	    self::$LIVE_BACKUP_PATH = '/mnt/stor5-wc2-dfw1/495544/1023193/'. $publish_server_name .'/web/content/backup/';
	    return self::$LIVE_BACKUP_PATH;
	}

	public static function getMediaFilePath() {
	    self::$LIVE_MEDIA_FILE_PATH = 'sites/default/files/';
	    return self::$LIVE_MEDIA_FILE_PATH;
	}

	public static function getLiveUrl() {
		global $publish_on;
	    self::$LIVE_URL = $publish_on;
	    return self::$LIVE_URL;
	}

	public static function getLiveSiteName() {
	    self::$LIVE_SITE_NAME = 'Babyshopstores';
	    return self::$LIVE_SITE_NAME;
	}

	public static function getBetaSourceCodePath() {
	    self::$BETA_SOURCE_CODE_PATH = array('themes','modules');
	    return self::$BETA_SOURCE_CODE_PATH;
	}

	public static function getBetaDeploymentPath() {
	    self::$BETA_DEPLOYMENT_PATH = '/mnt/stor5-wc2-dfw1/495544/1023193/'. $_SERVER['SERVER_NAME'] .'/web/content';
	    return self::$BETA_DEPLOYMENT_PATH;
	}

	public static function getBetaUrl() {
	    if(isset($_SERVER['HTTPS'])){
	        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
	    }
	    else{
	        $protocol = 'http';
	    }

	    self::$BETA_URL = $protocol . "://" . $_SERVER['SERVER_NAME'];

	    return self::$BETA_URL;
	}

	public static function getBetaBackupPath() {
	    self::$BETA_BACKUP_PATH = '/mnt/stor5-wc2-dfw1/495544/1023193/'. $_SERVER['SERVER_NAME'] .'/web/content/backup/';
	    return self::$BETA_BACKUP_PATH;
	}

	public static function getBetaMediaFilePath() {
	    self::$BETA_MEDIA_FILE_PATH = 'sites/default/files/';
	    return self::$BETA_MEDIA_FILE_PATH;
	}

	public static function getLiveDatabaseKey() {
	    self::$LIVE_DATABASE_KEY = 'live_database';
	    return self::$LIVE_DATABASE_KEY;
	}

	public static function getLiveDatabase() {
	    $live_database = Database::getConnectionInfo('live_database');
	    if($live_database == '')
	    	return false;
	    else
	     	return $live_database;
	}

	public static function getBetaDatabase() {
	    $beta_database = Database::getConnectionInfo('default');
	    return $beta_database;
	}

	public static function getBckupSeparator() {
	    self::$BACKUP_SEPARATOR = 'sep';
	    return self::$BACKUP_SEPARATOR;
	}

	public function _test_connection() {
		global $_instance;

		if($_instance!='beta') // on dev do not push content and on live do nothing
		{
			return true;
		}

		$live_database = self::getLiveDatabase();
		if($live_database) {
			try 
			{
				Database::addConnectionInfo('TEST_CONNECTION', 'default', $live_database['default']);
				Database::setActiveConnection('TEST_CONNECTION');
				$db1 = Database::getConnection();
			}
			catch (Exception $e) 
			{	
				drupal_set_message(st('Failed to connect to your database server. The server reports the following message: %error.<ul><li>Is the database server running?</li><li>Does the database exist, and have you entered the correct database name?</li><li>Have you entered the correct username and password?</li><li>Have you entered the correct database hostname?</li></ul>', array('%error' => $e->getMessage())),'betatolive : exception');
				Database::setActiveConnection();
				return FALSE;
			}
			Database::setActiveConnection();
			return TRUE;
		}
		
		return FALSE;
	}

	public function getSizeFile($url) {

		if (substr($url,0,4)=='http') { 
			$x = array_change_key_case(get_headers($url, 1),CASE_LOWER); 
			if ( strcasecmp($x[0], 'HTTP/1.1 200 OK') != 0 ) { 
				$x = $x['content-length'][1]; 
			} 
			else { 
				$x = $x['content-length']; 
			} 
		} 
		else { 
			$x = @filesize($url); 
		} 
		return $x; 
	}

	public function _get_constants_status() {
		$constants_status = array('error_type'=>false);
		$env_dir_all_vars = get_class_methods(__CLASS__);
		$env_dir_defined_vars = array_combine($env_dir_all_vars, $env_dir_all_vars);
		$env_dir_vars = array('BETA_SOURCE_CODE_PATH'=>'getBetaSourceCodePath','BETA_BACKUP_PATH'=>'getBetaBackupPath','BETA_MEDIA_FILE_PATH'=>'getBetaMediaFilePath', 'LIVE_SOURCE_CODE_PATH'=>'getSourceCodePath','LIVE_DEPLOYMENT_PATH'=>'getDeploymentPath','LIVE_BACKUP_PATH'=>'getBackupPath','LIVE_MEDIA_FILE_PATH'=>'getMediaFilePath');

		foreach($env_dir_vars as $env_dir_method => $env_dir_var)
		{
			if(!array_key_exists($env_dir_var,$env_dir_defined_vars))
			{
				$constants_status = array('error_type'=>'not_define','var'=>$env_dir_method,'message'=>t('Please define the :var',array(':var'=>$env_dir_method)));
				break;
			}

			$curr_func = call_user_func(array(self, $env_dir_defined_vars[$env_dir_var]));
			if(is_array($curr_func)) {
				foreach ($curr_func as $curr_funcs) {
					if(!file_prepare_directory($curr_funcs)) {
						$constants_status = array('error_type'=>'not_configure','var'=>$curr_funcs,'message'=>t('Directory :var is not properly configured',array(':var'=>$curr_funcs)));
						break;
					}
				}
				
			}
			else {
				if(!file_prepare_directory($curr_func)) {
					$constants_status=array('error_type'=>'not_configure','var'=>$curr_func,'message'=>t('Directory :var is not properly configured',array(':var'=>$curr_func)));
					break;
				}
			}
			
		}
		return $constants_status;
	}

	/**
	 * Deleting extra backup
	 */
	public function betatolive_clean_backup() {
		/* Rebuilt the backup index */
		$time_before_seven_days = time()-60*60*24*7;
		$beta_skip_backup = $live_skip_backup = 1;

		$extra_available_backup = db_query('select * from betatolive_backups order by backup_time desc')->fetchAllAssoc('backup_time');

		if($extra_available_backup !==FALSE) {
			/* Preserve Latest 6 backup */
			foreach($extra_available_backup as $backup_time=>$backup) {
				if($backup->instance=='beta') {
					$beta_skip_backup++;
					if($beta_skip_backup>7) {
						isset($backup->source_code)?file_unmanaged_delete($backup->source_code):'';
						isset($backup->database_path)?file_unmanaged_delete($backup->database_path):'';
						isset($backup->media_files)?file_unmanaged_delete($backup->media_files):'';
					}
				}
				else if($backup->instance=='live') {
					$live_skip_backup++;
					if($live_skip_backup>7) {
						isset($backup->source_code)?file_unmanaged_delete($backup->source_code):'';
						isset($backup->database_path)?file_unmanaged_delete($backup->database_path):'';
						isset($backup->media_files)?file_unmanaged_delete($backup->media_files):'';
					}
				}
			}
		}
	}

	public function betatolive_get_database_tables() {
		$database_schema = db_query('SHOW TABLES')->fetchAllKeyed(0,0);
		
		return $database_schema;
	}

	/**
	 * Loading backups
	 */
	public function get_backup_data($backup_time)
	{
		$current_path = \Drupal::service('path.current')->getPath();
		$arg = explode('/', $current_path);
		
		$instance=$arg[4];

		$backup = \Drupal::database()->select('betatolive_backups', 's')
				->fields('s')
				->condition('backup_time',$backup_time,'=')
				->condition('instance',$instance,'=')
				->execute()
				->fetchObject();

		if($backup === false)
		{
			return false;
		}
		return $backup;
	}

}