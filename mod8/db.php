<?php
$host = 'localhost';
$db   = 'phptable';   // or 'galacticpizza'
$user = 'postgres';
$pass = 'Fifi!6972';
$port = '5432';

$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if (!$conn) {
    die('DB connection failed: ' . pg_last_error());
}
