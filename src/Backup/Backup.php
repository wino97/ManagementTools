<?php
namespace CloudTechSolutions\ManagementTools\Backup;

use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use CloudTechSolutions\ManagementTools\Helpers\Helpers;

class Backup {
    public $exclude = array('/public_html/backups', '/public_html/wp-content/uploads');

    public function __construct()
    {
        //  Silent is golden
    }

	//	Get last full backup
	public function lastBackup($type)
    {
		$backupFiles = new RecursiveDirectoryIterator(ABSPATH . '/backups');									//	Path to backups folder
		$backupFiles->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);											//	Skipping dots files
		$backups = new RecursiveIteratorIterator($backupFiles, RecursiveIteratorIterator::SELF_FIRST);
		foreach($backups as $backup) {
			$basename = preg_split('/_|\./', basename($backup));												//	Split backup name into array
			if(($basename[0] === $type) && ($basename[3] === 'zip') && (isset($lastBackup))) {												//	If $lastBackup variable is set
				$lastBackupBasename = preg_split('/_|\./', basename($lastBackup));								//	Split last backup name into array
				if(intval($basename[1]) > intval($lastBackupBasename[1])) {										//	New backup creation date > Last backup creation date
					$lastBackup = $backup;
				}
			}elseif(($basename[0] === $type) && (!isset($lastBackup))) {										//	If $lastBackup isn't set, set first iteration as $lastBackup 
				$lastBackup = $backup;
			}
		}
		return $lastBackup;
	}

    public function fullBackup()
    {
        $helper = new Helpers();
        $name = $helper->backupStamp('FULL', '.zip');
		$zip = new ZipArchive();
		$zip->open($name, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		$iterator = new RecursiveDirectoryIterator(ABSPATH);
		$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($files as $file) {
			if(!$helper->strposa($file, $this->exclude)) {
				if (!$file->isDir()) {
					$filePath = $file->getRealPath();
					$relativePath = substr($filePath, strlen(ABSPATH));
					$zip->addFile($filePath, $relativePath);
				}
			}
		}
		$zip->close();
		// $this->ctsit_backupDB();
		return 'Successfuly made full backup';
    }
    
    public function diffBackup()
    {
        $helper = new Helpers();
        $name = $helper->backupStamp('DIFF', '.zip');
		$lastBackup = $this->lastBackup('FULL');
		$diffBackup = new ZipArchive();
		$diffBackup->open($name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		$iterator = new RecursiveDirectoryIterator(ABSPATH);
		$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($iterator);
		$zip = new ZipArchive();
		//	Open last full backup
		if($zip->open($lastBackup) === TRUE) {																					
			$i = 0;
			foreach($files as $file) {
				$filePath = $zip->getNameIndex($i);
	// 			echo $filePath.'|'.substr($file, strlen($path));
				//	Ignore excluded paths; Directories will be added automatically
				if( (!$helper->strposa($file, $this->exclude)) && (!$file->isDir()) && ($filePath === substr($file, strlen($this->path))) ) {
					//	Backup only files modified after last backup
					if(filemtime($file)-1 > $zip->statname($filePath)['mtime']) {
						$fileContent = file($file);
						$contentArray = [];
						$temp = [];
						$handle = $zip->getStream($filePath);
						if($handle) {
							while (($line = fgets($handle)) !== false) {
							$contentArray[] = $line;
							}
							fclose($handle);
						}
						for($i= 0; $i < count($fileContent); $i++) {
							if($contentArray[$i] !== $fileContent[$i]) {
								$temp[$i] = $fileContent[$i];
							}
						}
						$diffPath = $file->getRealPath();
	// 					return $diffPath;//preg_replace('/\..+$/', '.json', $diffPath);
	// 					$relativePath = substr($diffPath, strlen($path));
						$relativePath = preg_replace('/\..+$/', '.json', substr($diffPath, strlen($this->path)));
						$diffBackup->addFromString($relativePath, json_encode($temp));
	// 					echo date('H:i:s d-m-Y', filemtime($file)).'|'.date('H:i:s d-m-Y', $zip->statname($filePath)['mtime']).'<br>';
					}
				}
				$i++;
			}
		}
		elseif($lastBackup === NULL) {
			$this->fullBackup();
			exit();
		}else {
			return "Couldn't find last backup";
		}
		$zip->close();
		$diffBackup->close();
		$this->dbBackup();
		return'Succesfully made differential backup';
	}

    public function dbBackup($name = false)
    {
        $helper = new Helpers();
		if($name === false) $name = $helper->backupStamp('FULL', '.sql');
		global $wpdb;
		$query = $wpdb->get_results('SHOW TABLES', 'ARRAY_N');
		$tables = [];
		foreach($query as $q){
			$tables[] = $q[0];
		}
		$sqlScript = '';
		$sqlScript .= "-- PHP SQL Dump Compatibile with phpMyAdmin SQL Dump \n";
		$sqlScript .= "-- version 1.0.0 \n";
		$sqlScript .= "-- https://ctsit.pl/ \n";
		$sqlScript .= "--\n";
		$sqlScript .= "-- Host: " . $wpdb->dbhost . " \n";
		$sqlScript .= "-- Creation time: " . date('d M Y, H:i') . " \n";
		$sqlScript .= "-- Server version: " . $wpdb->db_server_info() . " \n";
		$sqlScript .= "-- PHP version: " . phpversion() . " \n\n";
		$sqlScript .= "START TRANSACTION; \n\n";
		$sqlScript .= "-- \n";
		$sqlScript .= "-- Baza danych: `" . $wpdb->dbname . "`\n";
		$sqlScript .= "-- \n";
		$sqlScript .= "CREATE DATABASE IF NOT EXISTS `" . $wpdb->dbname . "` DEFAULT CHARACTER SET " . $wpdb->charset . " COLLATE " . $wpdb->collate . ";\n";
		$sqlScript .= "USE `" . $wpdb->dbname . "`;\n\n";
		file_put_contents($name, $sqlScript, FILE_APPEND);
		$sqlScript = '';
	//=============================================================================================================================================
	//	Schema loop
		foreach ($tables as $table) {
			$row = $wpdb->get_results('SHOW CREATE TABLE ' . $table, 'ARRAY_N');				//	Prepare SQLscript for creating table structure
			$sqlScript .= "-- --------------------------------------------------------\n\n";
			$sqlScript .= "--\n-- Struktura tabeli dla tabeli `" . $table . "`\n--\n\n";
			$sqlScript .= $row[0][1] . ";\n\n";
			$queryAll = $wpdb->get_results('SELECT * FROM ' . $table, 'ARRAY_N');				//	Select data from database
			if($queryAll[0]) $columnCount = count($queryAll[0]);							//	If array
			else $columnCount = 0;															//	If not
			if($queryAll) $rowsCount = count($queryAll);
			else $rowsCount = 0;
	//=============================================================================================================================================
	//	Data loop
			if($rowsCount !== 0) {
				$sqlScript .= "--\n-- Zrzut danych tabeli `". $table . "` \n--\n\n";
				$sqlScript .= "INSERT INTO `" . $table . "` (`" . implode( '`, `', $wpdb->get_col("DESC {$table}", 0) ) . "`)" . " VALUES \n";
			}
				$last = 0;
				$isFirst = true;
				foreach($queryAll as $qAll) {												//	Insert data into $table
					$sqlScript .= "(";
					for($j = 0; $j < $columnCount; $j++) {
						if(isset($qAll[$j])) {												//	Data cells
							if($isFirst === true) {
								$sqlScript .= $wpdb->_real_escape($qAll[$j]);
								$isFirst = false;
							}
							else $sqlScript .= "'" . $wpdb->_real_escape($qAll[$j]) . "'";
						} else {															//	Empty cells
							$sqlScript .= 'NULL';
						}
						if ($j < ($columnCount - 1)) {										//	Column separator
							$sqlScript .= ', ';
						}
					}
					$isFirst = true;
					if(++$last == $rowsCount) $sqlScript .= ");\n";
					else $sqlScript .= "),\n";
					file_put_contents($name, $sqlScript, FILE_APPEND);
					$sqlScript = '';
				}
			$sqlScript .= "\n";
			file_put_contents($name, $sqlScript, FILE_APPEND);
			$sqlScript = '';
		}
		return 'Database backup creation successfull!';

	// 	$backupFile = 'backups/' . strtoupper($type) .'_'. strtotime($date) .'_'. strtoupper(str_replace( '.', '', $_SERVER['SERVER_NAME']));
	// 	exec("mysqldump --host=".DB_HOST." --user".DB_USER." --password=".DB_PASSWORD." ".DB_NAME." | gzip > $backupFile>");
	}

}
