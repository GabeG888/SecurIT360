<?php

//Show error messages, remove this later
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(array_key_exists('id', $_GET) && is_numeric($_GET['id'])){
		$pdo = new PDO('sqlite:exploitfinder.sqlite');
		$stm = $pdo->prepare("select filename from exploits where id = ?");
		$stm->execute(array($_GET['id']));
		$exploit = $stm->fetchAll();
		if(count($exploit) === 1){
			if(strtolower(explode('.', $exploit[0][0])[1]) == "pdf") header('Content-Type: application/pdf');
			else header('Content-Type: text/plain');
			readfile('uploads/' . $exploit[0][0]);
		}
		else{
			echo "No exploit with id " . $_GET['id'];
		}
	}
	else{
		echo "View raw files by clicking the raw button on the exploit page";
	}
}
?>