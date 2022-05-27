<?php

//Show error messages, remove this later
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(array_key_exists('id', $_GET) && is_numeric($_GET['id'])){
		$pdo = new PDO('sqlite:exploitfinder.sqlite');
		$stm = $pdo->prepare("select filename, name, cve from exploits where id = ?");
		$stm->execute(array($_GET['id']));
		$exploit = $stm->fetchAll();
		if(count($exploit) === 1){
			$download_name = $exploit[0][1];
			if(strlen($download_name) === 0) $download_name = $exploit[0][2];
			$extension = substr($exploit[0][0], -4);
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary"); 
			header("Content-disposition: attachment; filename=\"" . $download_name . $extension . "\""); 
			readfile('uploads/' . $exploit[0][0]);
		}
		else{
			echo "No exploit with id " . $_GET['id'];
		}
	}
	else{
		echo "Download files by clicking the download button of the exploit";
	}
}
?>