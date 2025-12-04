<?php
$host = "localhost";
$user = "root";
$pass = "";
$databasename = "club-management-db";

$connection  = mysqli_connect($host, $user, $pass, $databasename);

if(!$connection){
    die("Connection failed: ". mysqli_connect_error());
}
?>