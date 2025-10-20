<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: login8.php'); 
    exit;
}
