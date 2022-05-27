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

function printError($message){
	printf("<b style='color:red'>$message</b>");
	readfile('submit.html');
	die();
}

if(loggedIn()) echo "Logged in as " . getUsername() . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	//Show submit page
	readfile("submit.html");
	die();
}
elseif($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = array_key_exists('name', $_POST) ? $_POST['name'] : '';
	$cve = array_key_exists('cve', $_POST) ? $_POST['cve'] : '';
	if(($name === '' && $cve === '') || !array_key_exists("file", $_FILES) || $_FILES['file']['error'] != 0)printError('Your exploit must have at least a Name/CVE and a file');
	if(!str_contains($_FILES['file']['name'], '.')) printError('Your file must be .txt or .pdf');
	$tmp = explode(".", $_FILES['file']['name']);
	$extension = end($tmp);
	if(strtolower($extension) != 'pdf' && strtolower($extension) != 'txt') printError('Your file must be .txt or .pdf');
	$mime = mime_content_type($_FILES['file']['tmp_name']);
	if($mime != 'text/plain' && $mime != 'application/pdf') {
		$username = getUsername();
		secLog("{$username} submitted a file with txt or pdf extension but {$mime} mime type");
		printError('Your file must be .txt or .pdf');
	}
	if($_FILES['file']['size'] > 16000000) {
		$username = getUsername();
		secLog("{$username} submitted a file with a size of {$_FILES['file']['size']} bytes");
		printError('Maximum file size is 16MB');
	}
	$description = array_key_exists('description', $_POST) ? $_POST['description'] : '';
	$version = array_key_exists('version', $_POST) ? $_POST['version'] : '';
	$filename = bin2hex(openssl_random_pseudo_bytes(8)) . "." . $extension;
	move_uploaded_file($_FILES['file']["tmp_name"], './uploads/' . $filename);
	$pdo = new PDO('sqlite:exploitfinder.sqlite');
	$stm = $pdo->prepare("insert into exploits (name, cve, description, version, filename, owner) values (?, ?, ?, ?, ?, ?)");
	$stm->execute(array(htmlspecialchars($name), htmlspecialchars($cve), htmlspecialchars($description), htmlspecialchars($version), $filename, getUsername()));
	echo "Submitted!";
	$username = getUsername();
	secLog("Exploit {$_POST['name']} created by user {$username}");
}
?>
