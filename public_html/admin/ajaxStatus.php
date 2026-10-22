<?php
include"config.php";
 
	if (!isset($_SESSION['aemail'])) {
		$_SESSION['msg'] = "You must log in first";
		header('location: ../manage.php');
	}

	if (isset($_GET['logout'])) {
		session_destroy();
		unset($_SESSION['aemail']);
		header("location: ../manage.php");
	}

 if(!isset($_SESSION['aemail']))
 {
  echo ("<script language='javascript'>
                   window.location.href='logout.php';
                        </script>");
 }

if(isset($_POST['c_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['c_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `comments` SET `status`='$status' WHERE `c_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['conatct_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['conatct_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `conatct` SET `status`='$status' WHERE `c_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['i_id'])){
    
$c_id = mysqli_real_escape_string($con,$_POST['i_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);
$status_date = date("Y-m-d");
$ex=mysqli_query($con,"UPDATE `idea` SET `status`='$status' WHERE `i_id`='$c_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['j_id'])){
    
$j_id = mysqli_real_escape_string($con,$_POST['j_id']);
$status = mysqli_real_escape_string($con,$_POST['status']);

$ex=mysqli_query($con,"UPDATE `applications` SET `status`='$status' WHERE `a_id`='$j_id'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}

if(isset($_POST['newsid'])){
    
$newsid = mysqli_real_escape_string($con,$_POST['newsid']);
if (!function_exists('nm_can_manage_news')) {
    require_once __DIR__ . '/admin_helpers.php';
}
if (!nm_can_manage_news($con, $newsid)) {
    echo '<div class="alert alert-danger">You can only change status on your own news.</div>';
    exit;
}
$status = mysqli_real_escape_string($con,$_POST['status']);
$cate = mysqli_query($con,"SELECT `category`,`title`,`newsurl`,`hashtags`,`short_description`,`image`,`status` AS `old_status` FROM `news` WHERE `newsid`='$newsid'");
$cat = mysqli_fetch_array($cate);
extract($cat);

// Optional phone alert. A new site has no device list, so a missing table must not block the status change.
if ($status === 'Published' && (!isset($old_status) || $old_status !== 'Published')) {
    try {
        include_once __DIR__ . '/push_news.php';
        naradmuni_send_news_push($con, $title, $short_description, $image, $newsurl);
    } catch (Throwable $e) {
    }
}

$ex=mysqli_query($con,"UPDATE `news` SET `status`='$status' WHERE `newsid`='$newsid'");
if ($ex>0) {
            array_push($sucs, "Status Changed."); }
else{ array_push($errors, "Sorry, there was an error"); }
                        
    include('errors.php');
    include('sucsess.php');
}
?>