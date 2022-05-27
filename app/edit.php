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

function printError($error){
	echo $error;
	die();
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
			if(getUsername() == $exploit[0][5]){
				echo "<h1>Edit exploit:</h1>
					<h2>File won't change if you don't upload a new one.</h2><br>
					<form method='POST' enctype='multipart/form-data'>
					Name: <input name='name' placeholder='Log4Shell' value='{$exploit[0][0]}'><br>
					CVE: <input name='cve' placeholder='CVE-####-####' value='{$exploit[0][1]}'><br>
					Vulnerable service and version: <input name='version' placeholder='Log4j <2.17.1 (Java 8)' value='{$exploit[0][3]}'><br>
					Description: <br><textarea name='description' rows=10 cols=100 style='resize: none;'>{$exploit[0][2]}</textarea><br>
					Exploit file (.txt or .pdf): <input type='file' name='file' accept='.txt,.pdf'><br>
					<input type='hidden' name='id' value='{$exploit[0][6]}'>
					<input type='submit' value='Submit'><br>
					<a href=delete.php?id={$exploit[0][6]}>Delete</a><br>";
			}
			else{
				echo "<meta http-equiv='Refresh' content='0; url=\"login.php\" />You are not logged in as the correct user. You should be redirected to the <a href='login.php'>login page</a> automatically.";
				$username = getUsername();
				secLog("{$username} failed to edit exploit {$_GET['id']}");
			}
		}
		else{
			echo "No exploit with id " . $_GET['id'];
		}
	}
	else{
		echo "Edit files by clicking the edit button on the exploit page";
	}
}
else{
	if(array_key_exists('id', $_POST) && is_numeric($_POST['id'])){
		$pdo = new PDO('sqlite:exploitfinder.sqlite');
		$stm = $pdo->prepare("select owner from exploits where id = ?");
		$stm->execute(array($_POST['id']));
		$exploit = $stm->fetchAll();
		if(count($exploit) === 1){
			if(getUsername() == $exploit[0][0]){
				if(strlen($_FILES['file']['name']) != 0){
					if(!str_contains($_FILES['file']['name'], '.')) printError('Your file must be .txt or .pdf');
					$tmp = explode(".", $_FILES['file']['name']);
					$extension = end($tmp);
					if(strtolower($extension) != 'pdf' && strtolower($extension) != 'txt') printError('Your file must be .txt or .pdf');
					$mime = mime_content_type($_FILES['file']['tmp_name']);
					if($mime != 'text/plain' && $mime != 'application/pdf') printError('You file must be .txt or .pdf');
					if($_FILES['file']['size'] > 16000000) printError('Maximum file size is 16MB');
					$filename = bin2hex(openssl_random_pseudo_bytes(8)) . "." . $extension;
					move_uploaded_file($_FILES['file']["tmp_name"], './uploads/' . $filename);
					$pdo = new PDO('sqlite:exploitfinder.sqlite');
					$stm = $pdo->prepare("update exploits set filename=? where id=?");
					$stm->execute(array(htmlspecialchars($filename), $_POST['id']));
				}
				else $filename = '';
				$name = array_key_exists('name', $_POST) ? $_POST['name'] : '';
				$cve = array_key_exists('cve', $_POST) ? $_POST['cve'] : '';
				if($name === '' && $cve === '') printError('Your exploit must have at least a Name/CVE');
				$description = array_key_exists('description', $_POST) ? $_POST['description'] : '';
				$version = array_key_exists('version', $_POST) ? $_POST['version'] : '';
				$pdo = new PDO('sqlite:exploitfinder.sqlite');

				$stm = $pdo->prepare("update exploits set name=?, cve=?, description=?, version=? where id=?");
				$stm->execute(array(htmlspecialchars($name), htmlspecialchars($cve), htmlspecialchars($description), htmlspecialchars($version), $_POST['id']));
				echo "<meta http-equiv='Refresh' content='0; url=\"view.php?id={$_POST['id']}\"' />";
			}
			else{
				echo "<meta http-equiv='Refresh' content='0; url=\"login.php\" />You are not logged in as the correct user. You should be redirected to the <a href='login.php'>login page</a> automatically.";
			}
		}
		else{
			echo "No exploit with id " . $_POST['id'];
		}
	}
	else{
		echo "Edit files by clicking the edit button on the exploit page";
	}
}
?>