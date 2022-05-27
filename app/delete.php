<?php

//Log a message to the security log file
function secLog($message){
	$currentTime = time();
	$thisFile = __FILE__;
	file_put_contents('security.log', "[{$currentTime}] in {$thisFile}: {$message}" . PHP_EOL, FILE_APPEND);
}

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') { if(array_key_exists
('id', $_GET) && is_numeric($_GET['id'])){ $pdo = new PDO
('sqlite:exploitfinder.sqlite'); $stm = $pdo->prepare("select name, cve,
description, version, filename, owner, id from exploits where id = ?");
$stm->execute(array($_GET['id'])); $exploit = $stm->fetchAll(); if(count
($exploit) === 1){ if(getUsername() == $exploit[0][5]){ $stm = $pdo->prepare
("delete from exploits where id=?"); $stm->execute(array($_GET
['id'])); echo "<meta http-equiv='Refresh' content='0;
url=\"browse.php\"' />"; } else{ echo "<meta http-equiv='Refresh' content='0;
url=\"login.php\" />You are not logged in as the correct user. You should be
redirected to the <a href='login.php'>login page</a> automatically.";
$username = getUsername(); secLog("{$username} failed to delete exploit{$_GET
['id']}"); } } else{ echo "No exploit with id " . $_GET['id']; } } else
{ echo "Delete files by clicking the delete button on the edit page"; } }
?>