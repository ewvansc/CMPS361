<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


$host = 'localhost';
$db   = 'galacticpizza';
$user = 'postgres';
$pass = 'Fifi!6972';
$port = '5432';


$conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}


$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($username === '' || $password === '') {
    die("Please enter username and password.");
}


$sql    = "SELECT id, username, password FROM users WHERE username = $1";
$result = pg_query_params($conn, $sql, [$username]);
if (!$result) {
    die("Query error: " . pg_last_error($conn));
}
if (pg_num_rows($result) !== 1) {
    echo "Invalid username or password.";
    pg_close($conn);
    exit;
}
$row        = pg_fetch_assoc($result);
$storedHash = $row['password'];


if (hash_equals($storedHash, crypt($password, $storedHash))) {
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int)$row['id'];
    $_SESSION['username'] = $row['username'];

    pg_close($conn);

   
    header("Location: services.php");
    exit;
} else {
    echo "Invalid username or password.";
    pg_close($conn);
    exit;
}
