<?php

//Get the hashing salt from file salt.txt, create a new salt if the file doesn't exist
function getSalt(){
	if(filesize("salt.txt") < 1){
		$file = fopen("salt.txt", "w");
		$salt = random_bytes(64);
		fwrite($file, $salt);
	}
	else{
		$file = fopen("salt.txt", "r");
		$salt = fread($file,filesize("salt.txt"));
		fclose($file);
	}
	return $salt;
}

//Hash something with salted SHA256
function getHash($tohash){
	return hash("sha256", $tohash . getSalt());
}

function checkCookie($cookie){
	if(!is_string($cookie) || strlen($cookie) < 75){
		setcookie('session', '', -1, '/');
		echo 'Removed cookie';
		return array(False, '');
	}
	$username = substr($cookie, 0, -74);
	$expires = substr($cookie, -74, -64);
	$hash = substr($cookie, -64);
	if(is_numeric($expires) && !(time() > intval($expires)) && $hash === getHash($username . $expires)){
		return array(True, $username);
	}
	else{
		setcookie('session', '', -1, '/');
		return array(False, '');
	}
}

function getUsername(){
	if(isset($_COOKIE['session'])){
		$checkedCookie = checkCookie($_COOKIE['session']);
		if($checkedCookie[0]) return $checkedCookie[1];
		else return '';
	}
	else return '';
}

function loggedIn(){
	if(isset($_COOKIE['session'])){
		if(checkCookie($_COOKIE['session'])[0]) return True;
		else return False;
	}
	else return False;
}

//Show error messages, remove this later
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	if(array_key_exists('id', $_GET) && is_numeric($_GET['id'])){
		$pdo = new PDO('sqlite:exploitfinder.sqlite');
		$stm = $pdo->prepare("select name, cve, description, version, filename, owner, id from exploits where id = ?");
		$stm->execute(array($_GET['id']));
		$exploit = $stm->fetchAll();
		if(count($exploit) === 1){
			if($exploit[0][0] != ""){
				echo "<h1><b>{$exploit[0][0]}</b></h1>";
				if($exploit[0][1] != '') echo "<h2><b>{$exploit[0][1]}</b></h2>";
			}
			else if($exploit[0][1] != '') echo "<h1><b>{$exploit[0][1]}</b></h1>";
			if($exploit[0][3] != '') echo "<h2><b>Version: </b>{$exploit[0][3]}</h2>";
			if($exploit[0][2] != '') echo "<p>{$exploit[0][2]}</p>";
			echo "Submitted by <b>{$exploit[0][5]}</b><br>";
			echo "<a href = download.php?id={$exploit[0][6]}>Download</a><br>";
			echo "<a href = raw.php?id={$exploit[0][6]}>View Raw</a><br>";
			if($exploit[0][5] == getUsername()) echo "<a href = 'edit.php?id={$exploit[0][6]}'>Edit</a><br>";
			echo "<iframe width=100% height=1000px src = 'raw.php?id={$_GET['id']}'></iframe><br>";
		}
		else{
			echo "No exploit with id " . $_GET['id'];
		}
	}
	else{
		echo "View raw files by clicking the raw button of the exploit";
	}
}
?>