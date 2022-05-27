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

$username = getUsername();
secLog("{$username} logged out");
setcookie('session', '', -1, '/');
?>
<meta http-equiv="Refresh" content="0; url='./login.php'" />
Logged out.<br>
You should be automatically redirected to the <a href='./login.php'>login page</a>.