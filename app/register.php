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
	//Show register page
	readfile("register.html");
}
elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
	$pdo = new PDO('sqlite:exploitfinder.sqlite');

	//Check if user exists already
	$stm = $pdo->prepare("select password from credentials where username=?");
	$stm->execute(array(trim($_POST['username'])));
	$response = $stm->fetchAll();
	$exists = count($response) === 1 ? True : False;
	if(!$exists)
	{
		//Add user if password meets requirements
		if(strlen(trim($_POST['password'])) >= 10 && strlen(trim($_POST['password'])) <= 50){
		    $stm = $pdo->prepare("insert into credentials (username, password) values (?, ?)");
			$stm->execute(array(trim(htmlspecialchars($_POST['username'])), getHash(trim($_POST['password']))));
			echo("Registered<br><a href='./login.php'>Login</a><br>");
			secLog("User {$_POST['username']} created");
		}
		else{
			echo("<b style = 'color:red'>Your password must be between 10 and 50 characters</b><br>");
			readfile("register.html");
		}
	}
	else
	{
		echo("<b style = 'color:red'>User already exists</b><br>");
		readfile("register.html");
		secLog("User {$_POST['username']} already exists");
	}
}
?>
