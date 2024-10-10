<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

//
