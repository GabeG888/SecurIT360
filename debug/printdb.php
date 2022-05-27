<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO('sqlite:exploitfinder.sqlite');
$stm = $pdo->prepare("select username, password from credentials");
$stm->execute();
$response = $stm->fetchAll();
foreach ($response as $key => $value) {
	echo(implode("||||", $value) . "<br>");
}

echo "<br><br><br>";

$stm = $pdo->prepare("select * from exploits");
$stm->execute();
$response = $stm->fetchAll();
foreach ($response as $key => $value) {
	echo(implode("||||", $value) . "<br>");
}
?>
