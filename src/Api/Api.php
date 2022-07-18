<?php
namespace CloudTechSolutions\ManagementTools\Api;

use ManagementTools\Backup\Backup;
use ManagementTools\Config\Config;
use ManagementTools\Helpers\Helpers;

class Api{
    public function apiConnection(array $request)
    {
		$config = new Config();
		$helper = new Helpers();
		$helper->backupsDir();
		if( ( $request['apiKey'] !== $config->get('apiKey') ) || ( $request['apiSecret'] !== $config->get('apiSecret') ) ) return 'Connection failed!';
		$backup = new Backup();
		switch($request) {
			case($request['cmd'] === 'test'):
				return 'Connection succesfull';
				break;
			case($request['cmd'] === 'fullBackup'):
				$backup->fullBackup();
				break;
			case($request['cmd'] === 'diffBackup'):
				$backup->diffBackup();
				break;
			case($request['cmd'] === 'dbBackup'):
				$backup->dbBackup();
				break;
			case($request['cmd'] === 'scheduled'):
				if( date('w') < 7 ) $backup->diffBackup();
				else $backup->fullBackup();
				break;
			default:
				return 'Invalid command!';
		}
	}
}
// $test = new Config();
// echo $test->get('apiKey');