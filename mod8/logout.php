<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: login8.php');
exit;
