<?php
namespace CloudTechSolutions\ManagementTools\Helpers;

class Helpers{

    public function __construct()
    {
        //  Silent is golden
    }

    	//	Function to look for $array of needles inside $haystack
	public function strposa(string $haystack, array $needles, int $offset = 0): bool 
	{
		foreach($needles as $needle) {
			if(strpos($haystack, $needle, $offset) !== false) {
				return true; // stop on first true result
			}
		}

		return false;
	}

    public function backupStamp($type, $extension) {
		date_default_timezone_set('Europe/Warsaw');
		return 'backups/' . $type . '_' . strtotime(date('H:i:s d-m-Y')) .'_'. strtoupper(str_replace( '.', '', $_SERVER['SERVER_NAME']) ) . $extension;
	}

    //	Create backup directory
	public function backupsDir()
    {
		if (!is_dir(ABSPATH . 'backups/')) {
			mkdir(ABSPATH . 'backups/', 0777, true);
		}
	}
}