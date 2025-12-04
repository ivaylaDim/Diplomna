<?php

#connect db

$host = 'localhost';
$db_name = 'archive_bg';
$db_user = 'root'; #testing ONLYY
$db_password = '';

$conn = new mysqli($host, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}