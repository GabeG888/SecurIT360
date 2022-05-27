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

if(loggedIn()) echo "Logged in as " . getUsername() . "<br><br>";

$pdo = new PDO('sqlite:exploitfinder.sqlite');
$stm = $pdo->prepare("select name, cve, id from exploits");
$stm->execute();
$exploits = $stm->fetchAll();

foreach ($exploits as $key => $value) {
	if($value[0] == "") $value[0] = $value[1];
	printf("<div class='exploit'><b><a href=view.php?id=$value[2]>$value[0]</a></b></div>");
}
?>
