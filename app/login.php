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

//Show error messages, remove this later
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	//Show login page
	readfile("login.html");
	die();
}
elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pdo = new PDO('sqlite:exploitfinder.sqlite');
	$stm = $pdo->prepare("select password from credentials where username=?");
	$stm->execute(array(trim(htmlspecialchars($_POST['username']))));
	$response = $stm->fetchAll();
	$exists = count($response) === 1 ? True : False;
	if($exists)
	{
		$password = $response[0][0];
		if(getHash(trim($_POST["password"])) === $password){
			$cookievalue = urlencode(trim($_POST['username']) . strval(time() + 60 * 60 * 24));
			$cookievalue = $cookievalue . getHash($cookievalue);
			setcookie("session", $cookievalue, time() + 60 * 60 * 24, "/", httponly: True);
			echo("Logged in!");
			secLog("{$_POST['username']} logged in");
		}
		else{
			echo("Incorrect username or password<br>");
			secLog("Invalid password for user {$_POST['username']}");
        }
	}
	else{
		echo("Incorrect username or password<br>");
		secLog("Invalid username {$_POST['username']}");
	}
}
?>
