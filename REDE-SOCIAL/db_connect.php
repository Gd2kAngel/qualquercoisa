<?php
//db_connecty.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rede_social";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed:". $conn->connect_error);
}

?>