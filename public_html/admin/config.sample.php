<?php
    $urlroot = "https://khabarsetutu.com/";
    $publicroot = "https://khabarsetutu.com/";
    $adminLoginUrl = $urlroot . "manage.php";

if(!isset($_SESSION)){
session_start();
}

	$aemail = "";
	$apwd    = "";
	$errors = array();
    $sucs = array();
	$_SESSION['success'] = "";

    $server = "localhost";
    $user   = "PUT_HOSTINGER_DB_USER";
    $pass   = "PUT_HOSTINGER_DB_PASSWORD";
    $db_name = "PUT_HOSTINGER_DB_NAME";

 $con=mysqli_connect($server,$user,$pass,$db_name) or die("Could not connect DB S");
 mysqli_set_charset($con,"utf8");
if(isset($_SESSION['u_id'])){
    try {
        $uqry = mysqli_query($con,"SELECT `name` FROM `users` WHERE `u_id`='".mysqli_real_escape_string($con, (string)$_SESSION['u_id'])."'");
        $ur = $uqry ? mysqli_fetch_array($uqry) : null;
    } catch (Throwable $e) {
        $ur = null;
    }
}
 date_default_timezone_set("Asia/Kolkata");
 header( 'Content-Type: text/html; charset=utf-8' );
 require_once __DIR__ . '/admin_helpers.php';
 if (isset($con) && $con instanceof mysqli) {
	nm_ensure_admin_accounts($con);
 }
?>
