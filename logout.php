<?php
//session_cache_limiter(false);
session_start();
if(!isset($_SESSION['uID'])) {
    header("Location: login.php");
    die;
}

if(isset($_SESSION['uID'])) {
	session_destroy();
    header("Location: login.php");
    die;
}

?>