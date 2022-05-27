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

if(isset($_COOKIE['session'])){
	$session = $_COOKIE['session'];
	$username = substr($session, 0, -74);
	$expires = substr($session, -74, -64);
	$hash = substr($session, -64);
	echo($username);
	echo("<br>");
	echo($expires);
	echo("<br>");
	echo($hash);
	echo("<br>");
	echo(getHash($username . $expires));
	echo "<br>";
	if(!(time() > intval($expires)) && $hash === getHash($username . $expires)){
		echo("Valid");
	}
	else{
		echo("Invalid");
	}
}
?>